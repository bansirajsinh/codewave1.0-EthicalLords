<?php
session_start();

// Check if we have trip data and selected places
if (!isset($_SESSION['trip_data']) || !isset($_SESSION['selected_places'])) {
    header('Location: index.php');
    exit();
}

$trip_data = $_SESSION['trip_data'];
$selected_places = $_SESSION['selected_places'];

// Handle removing places from itinerary
if (isset($_POST['action']) && $_POST['action'] === 'remove_place') {
    $place_id = (int)$_POST['place_id'];
    $_SESSION['selected_places'] = array_filter($_SESSION['selected_places'], function($place) use ($place_id) {
        return $place['id'] != $place_id;
    });
    $_SESSION['selected_places'] = array_values($_SESSION['selected_places']); // Re-index array
    
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Place removed']);
        exit();
    }
}

// Handle adding custom activity
if (isset($_POST['action']) && $_POST['action'] === 'add_custom') {
    $custom_activity = [
        'id' => 'custom_' . time(),
        'name' => $_POST['activity_name'],
        'type' => 'custom',
        'description' => $_POST['activity_description'] ?? '',
        'image_url' => ''
    ];
    $_SESSION['selected_places'][] = $custom_activity;
    
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Custom activity added', 'activity' => $custom_activity]);
        exit();
    }
}

// Calculate trip duration
$start_date = new DateTime($trip_data['start_date']);
$end_date = new DateTime($trip_data['end_date']);
$duration = $start_date->diff($end_date)->days;

// Organize places by day (simple distribution)
function distributeByDays($places, $days) {
    if ($days <= 0 || empty($places)) return [];
    
    $daily_schedule = [];
    $places_per_day = ceil(count($places) / $days);
    
    for ($day = 0; $day < $days; $day++) {
        $daily_schedule[$day] = array_slice($places, $day * $places_per_day, $places_per_day);
    }
    
    return $daily_schedule;
}

$daily_schedule = distributeByDays($selected_places, $duration);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Your Itinerary - Virtual Path Pilot</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-green: #0B6138;
            --medium-green: #2E8B57;
            --light-green: #A8D5BA;
            --accent-green: #4CAF50;
            --bg-light: #F9FFF9;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, var(--bg-light) 0%, var(--light-green) 100%);
            min-height: 100vh;
            color: var(--dark-green);
            padding-bottom: 120px;
        }

        .header {
            background: var(--dark-green);
            color: white;
            padding: 1rem 0;
            box-shadow: 0 2px 10px rgba(11, 97, 56, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 2rem;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--light-green);
        }

        .nav-links {
            display: flex;
            gap: 1rem;
        }

        .nav-btn {
            background: var(--accent-green);
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .nav-btn:hover {
            background: var(--medium-green);
        }

        .nav-btn.secondary {
            background: transparent;
            border: 2px solid var(--light-green);
        }

        .nav-btn.secondary:hover {
            background: var(--light-green);
            color: var(--dark-green);
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .trip-header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
            margin-bottom: 2rem;
            text-align: center;
        }

        .trip-title {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--dark-green);
            margin-bottom: 1rem;
        }

        .trip-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .stat-item {
            background: var(--bg-light);
            padding: 1rem;
            border-radius: 10px;
            border: 2px solid var(--light-green);
            text-align: center;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-green);
        }

        .stat-label {
            color: var(--medium-green);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .itinerary-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 3px solid var(--accent-green);
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark-green);
        }

        .add-custom-btn {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .add-custom-btn:hover {
            background: var(--medium-green);
        }

        .day-container {
            margin-bottom: 2.5rem;
        }

        .day-header {
            background: var(--accent-green);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .day-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .day-count {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.3rem 0.8rem;
            border-radius: 15px;
            font-size: 0.9rem;
        }

        .activities-list {
            background: var(--bg-light);
            border: 2px solid var(--light-green);
            border-top: none;
            border-radius: 0 0 10px 10px;
            padding: 1.5rem;
        }

        .activity-item {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 4px 15px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            transition: transform 0.2s ease;
        }

        .activity-item:hover {
            transform: translateY(-2px);
        }

        .activity-item:last-child {
            margin-bottom: 0;
        }

        .activity-content {
            flex: 1;
        }

        .activity-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 0.5rem;
        }

        .activity-type {
            display: inline-block;
            background: var(--light-green);
            color: var(--dark-green);
            padding: 0.2rem 0.8rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .activity-description {
            color: var(--medium-green);
            line-height: 1.5;
        }

        .activity-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .remove-btn:hover {
            background: #c0392b;
        }

        .empty-day {
            text-align: center;
            padding: 2rem;
            color: var(--medium-green);
            font-style: italic;
        }

        .custom-form {
            display: none;
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            border: 2px solid var(--accent-green);
            margin-bottom: 1.5rem;
        }

        .custom-form.show {
            display: block;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 0.5rem;
        }

        .form-input {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid var(--light-green);
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-green);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-save {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
        }

        .bottom-actions {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1.5rem 2rem;
            box-shadow: 0 -4px 20px rgba(11, 97, 56, 0.1);
            border-top: 2px solid var(--light-green);
        }

        .action-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .export-btn {
            background: var(--dark-green);
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: background 0.3s ease;
        }

        .export-btn:hover {
            background: var(--medium-green);
        }

        @media (max-width: 768px) {
            .trip-stats {
                grid-template-columns: 1fr 1fr;
            }
            
            .section-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .day-header {
                flex-direction: column;
                gap: 0.5rem;
                text-align: center;
            }
            
            .activity-item {
                flex-direction: column;
                gap: 1rem;
            }
            
            .activity-actions {
                flex-direction: row;
                justify-content: flex-end;
            }
            
            .action-container {
                flex-direction: column;
            }
            
            .export-btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">🌿 Path Pilot</div>
            <div class="nav-links">
                <a href="plan.php" class="nav-btn secondary">← Add More Places</a>
                <a href="index.php" class="nav-btn">Plan New Trip</a>
            </div>
        </div>
    </header>

    <main class="main-container">
        <div class="trip-header">
            <h1 class="trip-title">📋 Your Trip to <?php echo htmlspecialchars($trip_data['destination']); ?></h1>
            
            <div class="trip-stats">
                <div class="stat-item">
                    <div class="stat-value"><?php echo $duration; ?></div>
                    <div class="stat-label">Days</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo count($selected_places); ?></div>
                    <div class="stat-label">Places Selected</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo ucfirst($trip_data['budget']); ?></div>
                    <div class="stat-label">Budget Level</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value"><?php echo date('M j', strtotime($trip_data['start_date'])); ?> - <?php echo date('M j', strtotime($trip_data['end_date'])); ?></div>
                    <div class="stat-label">Travel Dates</div>
                </div>
            </div>
        </div>

        <?php if (empty($selected_places)): ?>
        <div class="itinerary-section">
            <div class="empty-day">
                <h2>No places selected yet</h2>
                <p>Go back to <a href="plan.php" style="color: var(--accent-green); font-weight: 600;">recommendations</a> to add places to your itinerary.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="itinerary-section">
            <div class="section-header">
                <h2 class="section-title">🗓️ Day-by-Day Itinerary</h2>
                <button class="add-custom-btn" onclick="toggleCustomForm()">
                    ➕ Add Custom Activity
                </button>
            </div>

            <div id="customForm" class="custom-form">
                <h3 style="margin-bottom: 1rem; color: var(--dark-green);">Add Custom Activity</h3>
                <form id="customActivityForm">
                    <div class="form-group">
                        <label for="activity_name">Activity Name</label>
                        <input type="text" id="activity_name" name="activity_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label for="activity_description">Description (optional)</label>
                        <textarea id="activity_description" name="activity_description" class="form-input" rows="3"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-save">Save Activity</button>
                        <button type="button" class="btn-cancel" onclick="toggleCustomForm()">Cancel</button>
                    </div>
                </form>
            </div>

            <?php 
            $current_date = clone $start_date;
            for ($day = 0; $day < $duration; $day++): 
                $day_places = $daily_schedule[$day] ?? [];
            ?>
            <div class="day-container">
                <div class="day-header">
                    <div class="day-title">
                        Day <?php echo ($day + 1); ?> - <?php echo $current_date->format('M j, Y'); ?>
                    </div>
                    <div class="day-count"><?php echo count($day_places); ?> activities</div>
                </div>
                
                <div class="activities-list">
                    <?php if (empty($day_places)): ?>
                    <div class="empty-day">
                        No activities planned for this day yet.
                    </div>
                    <?php else: ?>
                        <?php foreach ($day_places as $place): ?>
                        <div class="activity-item" id="activity-<?php echo $place['id']; ?>">
                            <div class="activity-content">
                                <h3 class="activity-name"><?php echo htmlspecialchars($place['name']); ?></h3>
                                <span class="activity-type">
                                    <?php 
                                    $type_icons = [
                                        'attraction' => '🏛️ Attraction',
                                        'restaurant' => '🍽️ Restaurant', 
                                        'hidden_gem' => '💎 Hidden Gem',
                                        'custom' => '📝 Custom Activity'
                                    ];
                                    echo $type_icons[$place['type']] ?? ucfirst($place['type']); 
                                    ?>
                                </span>
                                <?php if (!empty($place['description'])): ?>
                                <p class="activity-description"><?php echo htmlspecialchars($place['description']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="activity-actions">
                                <button class="remove-btn" onclick="removePlace(<?php echo $place['id']; ?>)">
                                    🗑️ Remove
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php 
            $current_date->add(new DateInterval('P1D'));
            endfor; 
            ?>
        </div>
        <?php endif; ?>
    </main>

    <div class="bottom-actions">
        <div class="action-container">
            <div style="color: var(--medium-green); font-weight: 600;">
                Ready to travel? Export your complete itinerary!
            </div>
            <a href="export.php" class="export-btn">
                📄 Export as PDF
            </a>
        </div>
    </div>

    <script>
        function toggleCustomForm() {
            const form = document.getElementById('customForm');
            form.classList.toggle('show');
            
            if (form.classList.contains('show')) {
                document.getElementById('activity_name').focus();
            } else {
                document.getElementById('customActivityForm').reset();
            }
        }

        function removePlace(placeId) {
            if (!confirm('Are you sure you want to remove this activity?')) {
                return;
            }

            fetch('itinerary.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'remove_place',
                    place_id: placeId,
                    ajax: '1'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Remove the activity element
                    const activityElement = document.getElementById('activity-' + placeId);
                    activityElement.remove();
                    
                    // Show success message
                    showNotification('Activity removed successfully', 'success');
                    
                    // Reload page after a short delay to update day counts
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification('Failed to remove activity', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to remove activity', 'error');
            });
        }

        // Handle custom activity form submission
        document.getElementById('customActivityForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('action', 'add_custom');
            formData.append('activity_name', document.getElementById('activity_name').value);
            formData.append('activity_description', document.getElementById('activity_description').value);
            formData.append('ajax', '1');

            fetch('itinerary.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    showNotification('Custom activity added!', 'success');
                    toggleCustomForm();
                    
                    // Reload page to show new activity
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    showNotification('Failed to add activity', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to add activity', 'error');
            });
        });

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                padding: 1rem 1.5rem;
                background: ${type === 'success' ? 'var(--accent-green)' : '#e74c3c'};
                color: white;
                border-radius: 8px;
                font-weight: 600;
                z-index: 1000;
                opacity: 0;
                transform: translateX(100%);
                transition: all 0.3s ease;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 100);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>