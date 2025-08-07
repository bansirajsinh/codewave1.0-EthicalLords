<?php
session_start();

// Check if we have trip data
if (!isset($_SESSION['trip_data'])) {
    header('Location: index.php');
    exit();
}

$trip_data = $_SESSION['trip_data'];

// Initialize session array for selected places if not exists
if (!isset($_SESSION['selected_places'])) {
    $_SESSION['selected_places'] = [];
}

// Handle adding places to itinerary
if (isset($_POST['action']) && $_POST['action'] === 'add_to_itinerary') {
    $place_data = [
        'id' => $_POST['place_id'],
        'name' => $_POST['place_name'],
        'type' => $_POST['place_type'],
        'description' => $_POST['place_description'],
        'image_url' => $_POST['place_image'] ?? ''
    ];
    $_SESSION['selected_places'][] = $place_data;
    
    // Return JSON response for AJAX
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'message' => 'Added to itinerary!']);
        exit();
    }
}

// Sample data - in real implementation, this would come from database/API
$sample_places = [
    // Attractions
    [
        'id' => 1,
        'name' => 'Historic Downtown District',
        'type' => 'attraction',
        'description' => 'Explore centuries-old architecture and charming cobblestone streets filled with local artisan shops.',
        'category' => 'history',
        'image_url' => 'https://images.unsplash.com/photo-1533929736458-ca588d08c8be?w=400&h=300&fit=crop'
    ],
    [
        'id' => 2,
        'name' => 'City Art Museum',
        'type' => 'attraction',
        'description' => 'World-class collection featuring local and international artists spanning multiple centuries.',
        'category' => 'history',
        'image_url' => 'https://images.unsplash.com/photo-1518998053901-5348d3961a04?w=400&h=300&fit=crop'
    ],
    [
        'id' => 3,
        'name' => 'Riverside Park',
        'type' => 'attraction',
        'description' => 'Beautiful waterfront park with walking trails, picnic areas, and stunning sunset views.',
        'category' => 'nature',
        'image_url' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400&h=300&fit=crop'
    ],
    // Restaurants
    [
        'id' => 4,
        'name' => 'The Green Table',
        'type' => 'restaurant',
        'description' => 'Farm-to-table restaurant serving locally sourced, organic cuisine in a cozy atmosphere.',
        'category' => 'food',
        'image_url' => 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=400&h=300&fit=crop'
    ],
    [
        'id' => 5,
        'name' => 'Sunset Rooftop Bistro',
        'type' => 'restaurant',
        'description' => 'Elevated dining experience with panoramic city views and innovative fusion cuisine.',
        'category' => 'food',
        'image_url' => 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=400&h=300&fit=crop'
    ],
    // Hidden Gems
    [
        'id' => 6,
        'name' => 'Secret Garden Café',
        'type' => 'hidden_gem',
        'description' => 'Tucked-away café in a beautiful courtyard garden, known only to locals and serving amazing pastries.',
        'category' => 'food',
        'image_url' => 'https://images.unsplash.com/photo-1554118811-1e0d58224f24?w=400&h=300&fit=crop'
    ],
    [
        'id' => 7,
        'name' => 'Underground Jazz Club',
        'type' => 'hidden_gem',
        'description' => 'Intimate basement venue featuring live jazz performances by local musicians every weekend.',
        'category' => 'nightlife',
        'image_url' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400&h=300&fit=crop'
    ]
];

// Filter places based on user interests
$filtered_places = array_filter($sample_places, function($place) use ($trip_data) {
    if (empty($trip_data['interests'])) {
        return true;
    }
    return in_array($place['category'], $trip_data['interests']);
});

// Group places by type
$attractions = array_filter($filtered_places, fn($p) => $p['type'] === 'attraction');
$restaurants = array_filter($filtered_places, fn($p) => $p['type'] === 'restaurant');
$hidden_gems = array_filter($filtered_places, fn($p) => $p['type'] === 'hidden_gem');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Trip Recommendations - Virtual Path Pilot</title>
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
            padding-bottom: 100px;
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

        .back-btn {
            background: var(--accent-green);
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .back-btn:hover {
            background: var(--medium-green);
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .trip-summary {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
            margin-bottom: 2rem;
        }

        .trip-summary h1 {
            color: var(--dark-green);
            margin-bottom: 1rem;
        }

        .trip-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            font-size: 0.9rem;
        }

        .section {
            margin-bottom: 3rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 3px solid var(--accent-green);
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark-green);
        }

        .section-count {
            background: var(--accent-green);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .places-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        .place-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .place-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(11, 97, 56, 0.15);
        }

        .place-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: var(--light-green);
        }

        .place-content {
            padding: 1.5rem;
        }

        .place-name {
            font-size: 1.3rem;
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 0.5rem;
        }

        .place-description {
            color: var(--medium-green);
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .add-btn {
            width: 100%;
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .add-btn:hover {
            background: var(--medium-green);
            transform: translateY(-2px);
        }

        .add-btn.added {
            background: var(--dark-green);
            cursor: default;
        }

        .add-btn.added:hover {
            transform: none;
        }

        .bottom-actions {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            padding: 1rem 2rem;
            box-shadow: 0 -4px 20px rgba(11, 97, 56, 0.1);
            border-top: 2px solid var(--light-green);
        }

        .action-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .selected-count {
            font-weight: 600;
            color: var(--dark-green);
        }

        .review-btn {
            background: var(--dark-green);
            color: white;
            padding: 1rem 2rem;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .review-btn:hover {
            background: var(--medium-green);
        }

        .empty-section {
            text-align: center;
            padding: 2rem;
            color: var(--medium-green);
            background: white;
            border-radius: 12px;
            border: 2px dashed var(--light-green);
        }

        @media (max-width: 768px) {
            .places-grid {
                grid-template-columns: 1fr;
            }
            
            .action-container {
                flex-direction: column;
                gap: 1rem;
            }
            
            .review-btn {
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
            <a href="index.php" class="back-btn">← Plan New Trip</a>
        </div>
    </header>

    <main class="main-container">
        <div class="trip-summary">
            <h1>🗺️ Your Trip to <?php echo htmlspecialchars($trip_data['destination']); ?></h1>
            <div class="trip-details">
                <div><strong>📅 Dates:</strong> <?php echo date('M j', strtotime($trip_data['start_date'])); ?> - <?php echo date('M j, Y', strtotime($trip_data['end_date'])); ?></div>
                <div><strong>💰 Budget:</strong> <?php echo ucfirst($trip_data['budget']); ?></div>
                <div><strong>🎯 Interests:</strong> <?php echo implode(', ', array_map('ucfirst', $trip_data['interests'])); ?></div>
                <div><strong>⏱️ Duration:</strong> <?php echo (strtotime($trip_data['end_date']) - strtotime($trip_data['start_date'])) / (60*60*24); ?> days</div>
            </div>
        </div>

        <?php if (!empty($attractions)): ?>
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">🏛️ Top Attractions</h2>
                <span class="section-count"><?php echo count($attractions); ?></span>
            </div>
            <div class="places-grid">
                <?php foreach ($attractions as $place): ?>
                <div class="place-card">
                    <img src="<?php echo $place['image_url']; ?>" alt="<?php echo htmlspecialchars($place['name']); ?>" class="place-image">
                    <div class="place-content">
                        <h3 class="place-name"><?php echo htmlspecialchars($place['name']); ?></h3>
                        <p class="place-description"><?php echo htmlspecialchars($place['description']); ?></p>
                        <button class="add-btn" onclick="addToItinerary(<?php echo $place['id']; ?>, '<?php echo addslashes($place['name']); ?>', '<?php echo $place['type']; ?>', '<?php echo addslashes($place['description']); ?>', '<?php echo $place['image_url']; ?>')">
                            ➕ Add to Itinerary
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($restaurants)): ?>
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">🍽️ Recommended Restaurants</h2>
                <span class="section-count"><?php echo count($restaurants); ?></span>
            </div>
            <div class="places-grid">
                <?php foreach ($restaurants as $place): ?>
                <div class="place-card">
                    <img src="<?php echo $place['image_url']; ?>" alt="<?php echo htmlspecialchars($place['name']); ?>" class="place-image">
                    <div class="place-content">
                        <h3 class="place-name"><?php echo htmlspecialchars($place['name']); ?></h3>
                        <p class="place-description"><?php echo htmlspecialchars($place['description']); ?></p>
                        <button class="add-btn" onclick="addToItinerary(<?php echo $place['id']; ?>, '<?php echo addslashes($place['name']); ?>', '<?php echo $place['type']; ?>', '<?php echo addslashes($place['description']); ?>', '<?php echo $place['image_url']; ?>')">
                            ➕ Add to Itinerary
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (!empty($hidden_gems)): ?>
        <section class="section">
            <div class="section-header">
                <h2 class="section-title">💎 Local Hidden Gems</h2>
                <span class="section-count"><?php echo count($hidden_gems); ?></span>
            </div>
            <div class="places-grid">
                <?php foreach ($hidden_gems as $place): ?>
                <div class="place-card">
                    <img src="<?php echo $place['image_url']; ?>" alt="<?php echo htmlspecialchars($place['name']); ?>" class="place-image">
                    <div class="place-content">
                        <h3 class="place-name"><?php echo htmlspecialchars($place['name']); ?></h3>
                        <p class="place-description"><?php echo htmlspecialchars($place['description']); ?></p>
                        <button class="add-btn" onclick="addToItinerary(<?php echo $place['id']; ?>, '<?php echo addslashes($place['name']); ?>', '<?php echo $place['type']; ?>', '<?php echo addslashes($place['description']); ?>', '<?php echo $place['image_url']; ?>')">
                            ➕ Add to Itinerary
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <?php if (empty($attractions) && empty($restaurants) && empty($hidden_gems)): ?>
        <div class="empty-section">
            <h2>🔍 No recommendations found</h2>
            <p>We couldn't find places matching your interests. Please <a href="index.php">go back</a> and try different interests or destinations.</p>
        </div>
        <?php endif; ?>
    </main>

    <div class="bottom-actions">
        <div class="action-container">
            <div class="selected-count">
                <span id="selectedCount"><?php echo count($_SESSION['selected_places']); ?></span> places selected
            </div>
            <a href="itinerary.php" class="review-btn">
                📋 Review Itinerary →
            </a>
        </div>
    </div>

    <script>
        let selectedCount = <?php echo count($_SESSION['selected_places']); ?>;

        function addToItinerary(id, name, type, description, imageUrl) {
            const button = event.target;
            
            // Prevent double-clicking
            if (button.classList.contains('added')) {
                return;
            }

            // Send AJAX request to add place
            const formData = new FormData();
            formData.append('action', 'add_to_itinerary');
            formData.append('place_id', id);
            formData.append('place_name', name);
            formData.append('place_type', type);
            formData.append('place_description', description);
            formData.append('place_image', imageUrl);
            formData.append('ajax', '1');

            fetch('plan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update button state
                    button.classList.add('added');
                    button.innerHTML = '✅ Added';
                    
                    // Update counter
                    selectedCount++;
                    document.getElementById('selectedCount').textContent = selectedCount;
                    
                    // Show success message
                    showNotification('Added to itinerary!', 'success');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Failed to add place. Please try again.', 'error');
            });
        }

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
                    document.body.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Mark already selected places
        document.addEventListener('DOMContentLoaded', function() {
            // This would be populated from PHP session data
            const selectedPlaces = <?php echo json_encode(array_column($_SESSION['selected_places'], 'id')); ?>;
            
            selectedPlaces.forEach(placeId => {
                const buttons = document.querySelectorAll(`button[onclick*="addToItinerary(${placeId},"]`);
                buttons.forEach(button => {
                    button.classList.add('added');
                    button.innerHTML = '✅ Added';
                });
            });
        });
    </script>
</body>
</html>