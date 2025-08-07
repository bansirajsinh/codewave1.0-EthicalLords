<?php
session_start();

// Initialize variables
$success_message = '';
$error_message = '';
$form_data = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'subject' => '',
    'message' => ''
];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_message') {
    
    // Sanitize and validate input
    $name = trim(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING));
    $email = trim(filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL));
    $phone = trim(filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING));
    $subject = trim(filter_input(INPUT_POST, 'subject', FILTER_SANITIZE_STRING));
    $message = trim(filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING));
    
    // Store form data for repopulation if there are errors
    $form_data = [
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'subject' => $subject,
        'message' => $message
    ];
    
    // Validation
    $errors = [];
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Please enter a valid full name (at least 2 characters).';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    
    if (empty($subject)) {
        $errors[] = 'Please select a subject.';
    }
    
    if (empty($message) || strlen($message) < 10) {
        $errors[] = 'Please enter a message (at least 10 characters).';
    }
    
    if (!empty($phone)) {
        if (!preg_match('/^[\+]?[\d\s\-\(\)]+$/', $phone)) {
            $errors[] = 'Please enter a valid phone number.';
        }
    }
    
    // If no errors, process the form
    if (empty($errors)) {
        
        // Prepare email content
        $to = 'hello@travelassistant.com'; // Change to your email
        $email_subject = 'Contact Form Submission: ' . ucfirst(str_replace('-', ' ', $subject));
        
        $email_body = "New contact form submission:\n\n";
        $email_body .= "Name: " . $name . "\n";
        $email_body .= "Email: " . $email . "\n";
        $email_body .= "Phone: " . (!empty($phone) ? $phone : 'Not provided') . "\n";
        $email_body .= "Subject: " . ucfirst(str_replace('-', ' ', $subject)) . "\n";
        $email_body .= "Submitted: " . date('Y-m-d H:i:s') . "\n\n";
        $email_body .= "Message:\n" . $message . "\n\n";
        $email_body .= "---\nThis message was sent from the Path pilot contact form.";
        
        $headers = "From: " . $email . "\r\n";
        $headers .= "Reply-To: " . $email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        // Try to send email
        if (mail($to, $email_subject, $email_body, $headers)) {
            $success_message = 'Thank you for your message! We\'ll get back to you within 24 hours.';
            
            // Store in database (optional - uncomment and configure database)
            /*
            try {
                $pdo = new PDO('mysql:host=localhost;dbname=travel_assistant', $username, $password);
                $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
                $stmt->execute([$name, $email, $phone, $subject, $message]);
            } catch (PDOException $e) {
                error_log("Database error: " . $e->getMessage());
            }
            */
            
            // Clear form data on success
            $form_data = [
                'name' => '',
                'email' => '',
                'phone' => '',
                'subject' => '',
                'message' => ''
            ];
            
        } else {
            $error_message = 'Sorry, there was an error sending your message. Please try again or contact us directly.';
        }
        
    } else {
        $error_message = implode('<br>', $errors);
    }
}

// Function to get selected option
function isSelected($value, $current) {
    return $value === $current ? 'selected' : '';
}

// Function to escape output
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Virtual Path Pilot</title>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-green: #0B6138;
            --medium-green: #2E8B57;
            --light-green: #A8D5BA;
            --accent-green: #4CAF50;
            --bg-light: #F9FFF9;
            --error-color: #dc3545;
            --success-color: #28a745;
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

        .nav-links {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            font-weight: 400;
            transition: color 0.3s ease;
        }

        .nav-links a:hover {
            color: var(--light-green);
        }

        .main-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .hero-section {
            text-align: center;
            margin-bottom: 4rem;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-green);
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--medium-green);
            line-height: 1.6;
            max-width: 600px;
            margin: 0 auto;
        }

        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 4rem;
        }

        .contact-form {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
        }

        .contact-info {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
        }

        .form-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 1.5rem;
        }

        .form-step {
            margin-bottom: 1.5rem;
        }

        .form-step label {
            display: block;
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--light-green);
            border-radius: 8px;
            font-size: 1rem;
            font-family: 'Open Sans', sans-serif;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .form-input.error {
            border-color: var(--error-color);
        }

        textarea.form-input {
            resize: vertical;
            min-height: 120px;
        }

        .submit-btn {
            width: 100%;
            padding: 1.2rem;
            background: var(--accent-green);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .submit-btn:hover {
            background: var(--medium-green);
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1rem;
            background: var(--bg-light);
            border-radius: 8px;
            border-left: 4px solid var(--accent-green);
        }

        .contact-icon {
            font-size: 1.5rem;
            color: var(--accent-green);
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .contact-details h3 {
            color: var(--dark-green);
            margin-bottom: 0.3rem;
            font-size: 1.1rem;
        }

        .contact-details p {
            color: var(--medium-green);
            margin: 0;
        }

        .social-links {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .social-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: var(--accent-green);
            color: white;
            border-radius: 50%;
            text-decoration: none;
            font-size: 1.3rem;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .social-link:hover {
            background: var(--dark-green);
            transform: scale(1.1);
        }

        .faq-section {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
            margin-bottom: 3rem;
        }

        .faq-item {
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--light-green);
            padding-bottom: 1.5rem;
        }

        .faq-question {
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 0.8rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1.1rem;
        }

        .faq-answer {
            color: var(--medium-green);
            line-height: 1.6;
            display: none;
            padding-top: 0.5rem;
        }

        .faq-answer.active {
            display: block;
        }

        .faq-toggle {
            color: var(--accent-green);
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .faq-toggle.rotated {
            transform: rotate(180deg);
        }

        .footer {
            background: var(--dark-green);
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .nav-links {
                display: none;
            }

            .social-links {
                flex-wrap: wrap;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">🌿 Path Pilot</div>
            <nav>
                <ul class="nav-links">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <section class="hero-section">
            <h1 class="hero-title">Get in Touch</h1>
            <p class="hero-subtitle">Have questions about your trip? Need assistance with planning? We're here to help make your travel dreams come true!</p>
        </section>

        <div class="contact-grid">
            <div class="contact-form">
                <h2 class="form-title">Send us a Message</h2>
                
                <?php if ($success_message): ?>
                    <div class="message success-message">
                        <?php echo e($success_message); ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error_message): ?>
                    <div class="message error-message">
                        <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" id="contactForm">
                    <input type="hidden" name="action" value="send_message">
                    
                    <div class="form-step">
                        <label for="name">Full Name *</label>
                        <input type="text" id="name" name="name" class="form-input" 
                               value="<?php echo e($form_data['name']); ?>" required>
                    </div>

                    <div class="form-step">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" class="form-input" 
                               value="<?php echo e($form_data['email']); ?>" required>
                    </div>

                    <div class="form-step">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-input" 
                               placeholder="+1 (555) 123-4567" value="<?php echo e($form_data['phone']); ?>">
                    </div>

                    <div class="form-step">
                        <label for="subject">Subject *</label>
                        <select id="subject" name="subject" class="form-input" required>
                            <option value="">Select a topic...</option>
                            <option value="trip-planning" <?php echo isSelected('trip-planning', $form_data['subject']); ?>>
                                Trip Planning Assistance
                            </option>
                            <option value="technical-support" <?php echo isSelected('technical-support', $form_data['subject']); ?>>
                                Technical Support
                            </option>
                            <option value="feedback" <?php echo isSelected('feedback', $form_data['subject']); ?>>
                                Feedback & Suggestions
                            </option>
                            <option value="partnership" <?php echo isSelected('partnership', $form_data['subject']); ?>>
                                Partnership Inquiry
                            </option>
                            <option value="other" <?php echo isSelected('other', $form_data['subject']); ?>>
                                Other
                            </option>
                        </select>
                    </div>

                    <div class="form-step">
                        <label for="message">Message *</label>
                        <textarea id="message" name="message" class="form-input" 
                                  placeholder="Tell us how we can help you..." required><?php echo e($form_data['message']); ?></textarea>
                    </div>

                    <button type="submit" class="submit-btn">
                        📧 Send Message
                    </button>
                </form>
            </div>

            <div class="contact-info">
                <h2 class="form-title">Contact Information</h2>
                
                <div class="contact-item">
                    <div class="contact-icon">📍</div>
                    <div class="contact-details">
                        <h3>Our Office</h3>
                        <p>Shantilal Shah Engineering College<br>Sidsar-Budhel Highway<br>BHavnagar - 364060</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">📞</div>
                    <div class="contact-details">
                        <h3>Phone</h3>
                        <p>+91 8866473131<br>Mon-Fri: 9AM - 6PM IST</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">✉️</div>
                    <div class="contact-details">
                        <h3>Email</h3>
                        <p>ethicalLORDS@gmail.com<br>support@travelassistant.com</p>
                    </div>
                </div>

                <div class="contact-item">
                    <div class="contact-icon">🕐</div>
                    <div class="contact-details">
                        <h3>Response Time</h3>
                        <p>We typically respond within<br>24 hours on business days</p>
                    </div>
                </div>

                
            </div>
        </div>

        <section class="faq-section">
            <h2 class="form-title">Frequently Asked Questions</h2>
            
            <div class="faq-item">
                <div class="faq-question">
                    How does the trip planning work?
                    <span class="faq-toggle">▼</span>
                </div>
                <div class="faq-answer">
                    Simply fill out our trip planning form with your destination, dates, interests, and budget. Our AI-powered system will generate a personalized itinerary with daily activities, restaurant recommendations, and travel tips tailored to your preferences.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    Is the service free to use?
                    <span class="faq-toggle">▼</span>
                </div>
                <div class="faq-answer">
                    Yes! Our basic trip planning service is completely free. We generate personalized itineraries at no cost to help you plan your perfect adventure.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    Can I modify my itinerary after it's generated?
                    <span class="faq-toggle">▼</span>
                </div>
                <div class="faq-answer">
                    Absolutely! You can always contact us to make adjustments to your itinerary. We're here to ensure your trip plan perfectly matches your preferences and needs.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    Do you help with bookings?
                    <span class="faq-toggle">▼</span>
                </div>
                <div class="faq-answer">
                    Currently, we provide detailed recommendations and links to booking platforms. We're working on integrated booking features to make the process even smoother in the future.
                </div>
            </div>

            <div class="faq-item">
                <div class="faq-question">
                    What destinations do you cover?
                    <span class="faq-toggle">▼</span>
                </div>
                <div class="faq-answer">
                    We cover destinations worldwide! From major cities to hidden gems, our database includes attractions, restaurants, and activities from countries across the globe.
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> Virtual Path Pilot. Crafted with 💚 for amazing adventures.</p>
    </footer>

    <script>
        // FAQ Toggle Functionality
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', () => {
                const answer = question.nextElementSibling;
                const toggle = question.querySelector('.faq-toggle');
                
                answer.classList.toggle('active');
                toggle.classList.toggle('rotated');
            });
        });

        // Form validation enhancements
        document.getElementById('contactForm').addEventListener('input', function(e) {
            const field = e.target;
            
            // Remove any existing error styling
            field.classList.remove('error');
            
            // Validate email format
            if (field.type === 'email' && field.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    field.classList.add('error');
                }
            }
            
            // Validate phone format
            if (field.type === 'tel' && field.value) {
                const phoneRegex = /^[\+]?[\d\s\-\(\)]+$/;
                if (!phoneRegex.test(field.value)) {
                    field.classList.add('error');
                }
            }
        });

        // Form submission loading state
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            const submitBtn = this.querySelector('.submit-btn');
            submitBtn.disabled = true;
            submitBtn.textContent = '📧 Sending...';
        });
    </script>
</body>
</html>