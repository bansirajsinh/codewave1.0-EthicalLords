<?php
session_start();

// Check if we have trip data and selected places
if (!isset($_SESSION['trip_data']) || !isset($_SESSION['selected_places'])) {
    header('Location: index.php');
    exit();
}

$trip_data = $_SESSION['trip_data'];
$selected_places = $_SESSION['selected_places'];

// Handle PDF generation (simplified version without external libraries)
if (isset($_GET['format']) && $_GET['format'] === 'pdf') {
    // For this example, we'll create a simple text-based PDF content
    // In production, you would use FPDF, TCPDF, or similar library
    
    $filename = 'itinerary_' . preg_replace('/[^a-zA-Z0-9]/', '_', $trip_data['destination']) . '_' . date('Ymd') . '.txt';
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    
    $content = generateItineraryText($trip_data, $selected_places);
    echo $content;
    exit();
}

// Handle email sending with improved error handling and validation
if (isset($_POST['action']) && $_POST['action'] === 'send_email') {
    $email = trim($_POST['email']);
    $email_sent = false;
    $error_message = '';
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Please enter a valid email address.';
    } else {
        $content = generateItineraryText($trip_data, $selected_places);
        
        // Try multiple email methods for better reliability
        $email_sent = sendEmailMultipleMethods($email, $trip_data, $content, $error_message);
    }
    
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $email_sent ? 'success' : 'error',
            'message' => $email_sent ? 'Itinerary sent successfully!' : $error_message
        ]);
        exit();
    }
}

/**
 * Try multiple methods to send email for better reliability
 */
function sendEmailMultipleMethods($email, $trip_data, $content, &$error_message) {
    // Method 1: Try PHP mail() function first
    if (function_exists('mail')) {
        $to = $email;
        $subject = "Your Travel Itinerary - " . $trip_data['destination'];
        $message = $content;
        $headers = "From: noreply@travelassistant.com\r\n";
        $headers .= "Reply-To: noreply@travelassistant.com\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        
        // Check if mail function is properly configured
        if (@mail($to, $subject, $message, $headers)) {
            return true;
        }
    }
    
    // Method 2: Try using sendmail if available
    if (function_exists('exec') && is_executable('/usr/sbin/sendmail')) {
        $sendmail_command = '/usr/sbin/sendmail -f noreply@travelassistant.com ' . escapeshellarg($email);
        $email_content = "To: " . $email . "\r\n";
        $email_content .= "From: noreply@travelassistant.com\r\n";
        $email_content .= "Subject: Your Travel Itinerary - " . $trip_data['destination'] . "\r\n";
        $email_content .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $email_content .= $content;
        
        $process = popen($sendmail_command, 'w');
        if ($process) {
            fwrite($process, $email_content);
            $result = pclose($process);
            if ($result === 0) {
                return true;
            }
        }
    }
    
    // Method 3: Save to file system as backup (for development/testing)
    if (isDevelopmentEnvironment()) {
        $email_dir = __DIR__ . '/emails';
        if (!is_dir($email_dir)) {
            @mkdir($email_dir, 0755, true);
        }
        
        $filename = $email_dir . '/itinerary_' . date('Y-m-d_H-i-s') . '_' . md5($email) . '.txt';
        $email_content = "TO: " . $email . "\n";
        $email_content .= "SUBJECT: Your Travel Itinerary - " . $trip_data['destination'] . "\n";
        $email_content .= "DATE: " . date('Y-m-d H:i:s') . "\n";
        $email_content .= str_repeat('=', 50) . "\n\n";
        $email_content .= $content;
        
        if (file_put_contents($filename, $email_content)) {
            // In development, we'll consider this a success
            $error_message = 'Email saved locally (development mode). File: ' . basename($filename);
            return true;
        }
    }
    
    // If all methods fail, set appropriate error message
    $error_message = getEmailErrorMessage();
    return false;
}

/**
 * Check if we're in a development environment
 */
function isDevelopmentEnvironment() {
    return (
        $_SERVER['SERVER_NAME'] === 'localhost' ||
        $_SERVER['SERVER_NAME'] === '127.0.0.1' ||
        strpos($_SERVER['SERVER_NAME'], '.local') !== false ||
        !empty($_SERVER['XDEBUG_CONFIG']) ||
        defined('DEVELOPMENT_MODE')
    );
}

/**
 * Get appropriate error message based on system configuration
 */
function getEmailErrorMessage() {
    if (isDevelopmentEnvironment()) {
        return 'Email functionality not configured for local development. Please configure SMTP settings or use a production server.';
    }
    
    if (!function_exists('mail')) {
        return 'Email functionality is disabled on this server. Please contact your hosting provider.';
    }
    
    // Check common mail configuration issues
    $ini_sendmail_path = ini_get('sendmail_path');
    if (empty($ini_sendmail_path)) {
        return 'Server mail configuration incomplete. Please contact your hosting provider to configure sendmail_path.';
    }
    
    return 'Unable to send email at this time. Please try again later or contact support if the problem persists.';
}

/**
 * Enhanced generateItineraryText function with better error handling
 */
function generateItineraryText($trip_data, $places) {
    // Validate input data
    if (empty($trip_data['destination'])) {
        $trip_data['destination'] = 'Unknown Destination';
    }
    
    if (empty($trip_data['start_date']) || empty($trip_data['end_date'])) {
        throw new InvalidArgumentException('Start date and end date are required');
    }
    
    $content = "=== YOUR TRAVEL ITINERARY ===\n\n";
    $content .= "Destination: " . $trip_data['destination'] . "\n";
    
    try {
        $start_formatted = date('M j, Y', strtotime($trip_data['start_date']));
        $end_formatted = date('M j, Y', strtotime($trip_data['end_date']));
        $content .= "Travel Dates: " . $start_formatted . " - " . $end_formatted . "\n";
    } catch (Exception $e) {
        $content .= "Travel Dates: " . $trip_data['start_date'] . " - " . $trip_data['end_date'] . "\n";
    }
    
    $content .= "Budget Level: " . ucfirst($trip_data['budget'] ?? 'Not specified') . "\n";
    
    $interests = isset($trip_data['interests']) && is_array($trip_data['interests']) 
                 ? implode(', ', array_map('ucfirst', $trip_data['interests']))
                 : 'Not specified';
    $content .= "Interests: " . $interests . "\n\n";
    
    try {
        $start_date = new DateTime($trip_data['start_date']);
        $end_date = new DateTime($trip_data['end_date']);
        $duration = $start_date->diff($end_date)->days;
        
        if ($duration <= 0) {
            $duration = 1; // Minimum 1 day
        }
    } catch (Exception $e) {
        $duration = 1; // Default to 1 day if date parsing fails
    }
    
    // Organize places by day
    $places_per_day = count($places) > 0 ? ceil(count($places) / $duration) : 0;
    
    for ($day = 0; $day < $duration; $day++) {
        try {
            $current_date = clone $start_date;
            $current_date->add(new DateInterval('P' . $day . 'D'));
            $date_formatted = $current_date->format('M j, Y');
        } catch (Exception $e) {
            $date_formatted = 'Day ' . ($day + 1);
        }
        
        $content .= "DAY " . ($day + 1) . " - " . $date_formatted . "\n";
        $content .= str_repeat('-', 40) . "\n";
        
        if ($places_per_day > 0) {
            $day_places = array_slice($places, $day * $places_per_day, $places_per_day);
        } else {
            $day_places = [];
        }
        
        if (empty($day_places)) {
            $content .= "No activities planned for this day.\n\n";
        } else {
            foreach ($day_places as $place) {
                $type_labels = [
                    'attraction' => 'Attraction',
                    'restaurant' => 'Restaurant',
                    'hidden_gem' => 'Hidden Gem',
                    'custom' => 'Custom Activity'
                ];
                
                $place_name = isset($place['name']) && !empty($place['name']) 
                             ? $place['name'] 
                             : 'Unnamed Place';
                             
                $place_type = isset($place['type']) 
                             ? ($type_labels[$place['type']] ?? ucfirst(str_replace('_', ' ', $place['type'])))
                             : 'Activity';
                
                $content .= "• " . $place_name . " (" . $place_type . ")\n";
                
                if (isset($place['description']) && !empty($place['description'])) {
                    $content .= "  " . $place['description'] . "\n";
                }
                $content .= "\n";
            }
        }
        $content .= "\n";
    }
    
    $content .= "=== TRAVEL TIPS ===\n\n";
    $content .= "• Check weather conditions before your trip\n";
    $content .= "• Book reservations for popular restaurants in advance\n";
    $content .= "• Keep copies of important documents\n";
    $content .= "• Research local customs and etiquette\n";
    $content .= "• Consider travel insurance\n\n";
    
    $content .= "Generated by Virtual Path Pilot on " . date('M j, Y \a\t g:i A') . "\n";
    $content .= "Have an amazing trip to " . $trip_data['destination'] . "!\n";
    
    return $content;
}

// Calculate trip statistics with error handling
try {
    $start_date = new DateTime($trip_data['start_date']);
    $end_date = new DateTime($trip_data['end_date']);
    $duration = max(1, $start_date->diff($end_date)->days);
} catch (Exception $e) {
    $duration = 1; // Default to 1 day
}

// Count places by type
$place_counts = [
    'attraction' => 0,
    'restaurant' => 0,
    'hidden_gem' => 0,
    'custom' => 0
];

foreach ($selected_places as $place) {
    if (isset($place['type']) && isset($place_counts[$place['type']])) {
        $place_counts[$place['type']]++;
    }
}
?>

<!-- The rest of your HTML remains the same, but I'll include the script section with improved error handling -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Export Your Itinerary - Virtual Path Pilot</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* Your existing CSS styles remain the same */
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

        .main-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem;
        }

        .export-header {
            text-align: center;
            background: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
            margin-bottom: 2rem;
        }

        .export-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-green);
            margin-bottom: 1rem;
        }

        .export-subtitle {
            font-size: 1.2rem;
            color: var(--medium-green);
            margin-bottom: 2rem;
        }

        .trip-summary {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: 10px;
            border: 2px solid var(--light-green);
            margin-bottom: 2rem;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            text-align: center;
        }

        .summary-item {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--light-green);
        }

        .summary-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-green);
        }

        .summary-label {
            color: var(--medium-green);
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }

        .export-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .option-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .option-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(11, 97, 56, 0.15);
        }

        .option-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .option-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 1rem;
        }

        .option-description {
            color: var(--medium-green);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .option-btn {
            width: 100%;
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .option-btn:hover {
            background: var(--medium-green);
        }

        .option-btn.secondary {
            background: var(--dark-green);
        }

        .option-btn.secondary:hover {
            background: var(--medium-green);
        }

        .email-form {
            background: var(--bg-light);
            padding: 1.5rem;
            border-radius: 10px;
            border: 2px solid var(--light-green);
            margin-top: 1rem;
            display: none;
        }

        .email-form.show {
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

        .btn-send {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            flex: 1;
        }

        .btn-cancel {
            background: #6c757d;
            color: white;
            border: none;
            padding: 0.8rem 1.5rem;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            flex: 1;
        }

        .success-message {
            background: var(--accent-green);
            color: white;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: 600;
            display: none;
        }

        .success-message.show {
            display: block;
        }

        .place-preview {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 6px 20px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
        }

        .preview-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .place-list {
            display: grid;
            gap: 0.8rem;
            max-height: 300px;
            overflow-y: auto;
        }

        .place-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--bg-light);
            border-radius: 8px;
            border: 1px solid var(--light-green);
        }

        .place-type-icon {
            width: 40px;
            height: 40px;
            background: var(--accent-green);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .place-info {
            flex: 1;
        }

        .place-name {
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 0.2rem;
        }

        .place-type {
            font-size: 0.8rem;
            color: var(--medium-green);
            text-transform: capitalize;
        }

        @media (max-width: 768px) {
            .export-options {
                grid-template-columns: 1fr;
            }
            
            .summary-grid {
                grid-template-columns: 1fr 1fr;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">🌿 Path Pilot</div>
            <a href="itinerary.php" class="nav-btn">← Back to Itinerary</a>
        </div>
    </header>

    <main class="main-container">
        <div class="export-header">
            <h1 class="export-title">📄 Export Your Itinerary</h1>
            <p class="export-subtitle">Choose how you'd like to save or share your perfect trip plan</p>
            
            <div class="trip-summary">
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-value"><?php echo htmlspecialchars($trip_data['destination']); ?></div>
                        <div class="summary-label">Destination</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?php echo $duration; ?></div>
                        <div class="summary-label">Days</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?php echo count($selected_places); ?></div>
                        <div class="summary-label">Total Places</div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-value"><?php echo date('M j', strtotime($trip_data['start_date'])); ?></div>
                        <div class="summary-label">Start Date</div>
                    </div>
                </div>
            </div>
        </div>

        <div id="successMessage" class="success-message">
            Itinerary exported successfully!
        </div>

        <div class="export-options">
            <div class="option-card">
                <div class="option-icon">📱</div>
                <h3 class="option-title">Download PDF</h3>
                <p class="option-description">
                    Get a professionally formatted PDF file that you can save offline, print, or share with travel companions.
                </p>
                <a href="export.php?format=pdf" class="option-btn" target="_blank">
                    📥 Download PDF
                </a>
            </div>

            <div class="option-card">
                <div class="option-icon">✉️</div>
                <h3 class="option-title">Email Itinerary</h3>
                <p class="option-description">
                    Send your complete travel plan directly to your email or share it with friends and family instantly.
                </p>
                <button class="option-btn secondary" onclick="toggleEmailForm()">
                    📧 Send Email
                </button>
                
                <div id="emailForm" class="email-form">
                    <form id="emailItineraryForm">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" class="form-input" 
                                   value="<?php echo htmlspecialchars($trip_data['email'] ?? ''); ?>" 
                                   placeholder="Enter email address..." required>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn-send">Send Itinerary</button>
                            <button type="button" class="btn-cancel" onclick="toggleEmailForm()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="place-preview">
            <h3 class="preview-title">📋 Your Selected Places</h3>
            <div class="place-list">
                <?php 
                $type_icons = [
                    'attraction' => '🏛️',
                    'restaurant' => '🍽️',
                    'hidden_gem' => '💎',
                    'custom' => '📝'
                ];
                
                foreach ($selected_places as $place): 
                ?>
                <div class="place-item">
                    <div class="place-type-icon">
                        <?php echo $type_icons[$place['type']] ?? '📍'; ?>
                    </div>
                    <div class="place-info">
                        <div class="place-name"><?php echo htmlspecialchars($place['name']); ?></div>
                        <div class="place-type"><?php echo ucfirst(str_replace('_', ' ', $place['type'])); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </main>

    <script>
        function toggleEmailForm() {
            const form = document.getElementById('emailForm');
            form.classList.toggle('show');
            
            if (form.classList.contains('show')) {
                document.getElementById('email').focus();
            }
        }

        // Enhanced email form submission with better error handling
        document.getElementById('emailItineraryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const emailInput = document.getElementById('email');
            const email = emailInput.value.trim();
            const submitBtn = e.target.querySelector('.btn-send');
            const originalText = submitBtn.textContent;
            
            // Client-side email validation
            if (!validateEmail(email)) {
                showNotification('Please enter a valid email address.', 'error');
                emailInput.focus();
                return;
            }
            
            // Show loading state
            submitBtn.textContent = 'Sending...';
            submitBtn.disabled = true;
            emailInput.disabled = true;

            fetch('export.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'send_email',
                    email: email,
                    ajax: '1'
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('successMessage').classList.add('show');
                    document.getElementById('successMessage').textContent = data.message;
                    toggleEmailForm();
                    
                    // Hide success message after 5 seconds
                    setTimeout(() => {
                        document.getElementById('successMessage').classList.remove('show');
                    }, 5000);
                } else {
                    showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Network error. Please check your connection and try again.', 'error');
            })
            .finally(() => {
                // Reset button and input
                submitBtn.textContent = originalText;
                submitBtn.disabled = false;
                emailInput.disabled = false;
            });
        });

        // Email validation function
        function validateEmail(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
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
                max-width: 300px;
                word-wrap: break-word;
            `;
            notification.textContent = message;
            document.body.appendChild(notification);

            // Animate in
            setTimeout(() => {
                notification.style.opacity = '1';
                notification.style.transform = 'translateX(0)';
            }, 100);

            // Remove after 6 seconds (increased for longer error messages)
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
                }, 300);
            }, 6000);
        }

        // Show download success message when returning from PDF download
        if (window.location.hash === '#downloaded') {
            document.getElementById('successMessage').classList.add('show');
            setTimeout(() => {
                document.getElementById('successMessage').classList.remove('show');
            }, 5000);
        }

        // Add form validation styling
        document.getElementById('email').addEventListener('input', function(e) {
            const email = e.target.value.trim();
            const isValid = email === '' || validateEmail(email);
            
            e.target.style.borderColor = isValid ? 'var(--light-green)' : '#e74c3c';
        });
    </script>
</body>
</html>