<?php
session_start();

// Handle form submission
if (isset($_POST['action']) && $_POST['action'] === 'plan_trip') {
    // Store form data in session
    $_SESSION['trip_data'] = [
        'destination' => $_POST['destination'],
        'start_date' => $_POST['start_date'],
        'end_date' => $_POST['end_date'],
        'interests' => $_POST['interests'] ?? [],
        'budget' => $_POST['budget'],
        'email' => $_POST['email'] ?? ''
    ];
    
    // Redirect to plan.php
    header('Location: plan.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virtual Path Pilot - Plan Your Perfect Trip</title>
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
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 2rem;
        }

        .hero-section {
            text-align: center;
            margin-bottom: 3rem;
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
        }

        .form-card {
            background: white;
            border-radius: 15px;
            padding: 2.5rem;
            box-shadow: 0 10px 30px rgba(11, 97, 56, 0.1);
            border: 2px solid var(--light-green);
        }

        .form-step {
            margin-bottom: 2rem;
        }

        .form-step label {
            display: block;
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .form-input {
            width: 100%;
            padding: 1rem;
            border: 2px solid var(--light-green);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
        }

        .date-inputs {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .interests-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .interest-checkbox {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.8rem;
            background: var(--bg-light);
            border: 2px solid var(--light-green);
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .interest-checkbox:hover {
            background: var(--light-green);
        }

        .interest-checkbox input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: var(--accent-green);
        }

        .budget-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .budget-option {
            text-align: center;
            padding: 1rem;
            background: var(--bg-light);
            border: 2px solid var(--light-green);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .budget-option:hover,
        .budget-option.selected {
            background: var(--accent-green);
            color: white;
            border-color: var(--dark-green);
        }

        .budget-option input[type="radio"] {
            display: none;
        }

        .submit-btn {
            width: 100%;
            padding: 1.2rem;
            background: var(--accent-green);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .submit-btn:hover {
            background: var(--medium-green);
            transform: translateY(-2px);
        }

        .footer {
            background: var(--dark-green);
            color: white;
            text-align: center;
            padding: 2rem 0;
            margin-top: 4rem;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }
            
            .date-inputs {
                grid-template-columns: 1fr;
            }
            
            .budget-options {
                grid-template-columns: 1fr;
            }
            
            .nav-links {
                display: none;
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
                    <li><a href="#home">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <section class="hero-section">
            <h1 class="hero-title">Plan Your Perfect Trip</h1>
            <p class="hero-subtitle">Create personalized, day-by-day travel itineraries instantly. Just tell us where you want to go and what you love, and we'll craft your dream adventure!</p>
        </section>

        <div class="form-card">
            <form method="POST" id="tripForm">
                <input type="hidden" name="action" value="plan_trip">
                
                <div class="form-step">
                    <label for="destination">Where would you like to go?</label>
                    <input type="text" id="destination" name="destination" class="form-input" 
                           placeholder="Enter city or destination..." required>
                </div>

                <div class="form-step">
                    <label>Travel Dates</label>
                    <div class="date-inputs">
                        <input type="date" name="start_date" class="form-input" required>
                        <input type="date" name="end_date" class="form-input" required>
                    </div>
                </div>

                <div class="form-step">
                    <label>What interests you? (Select all that apply)</label>
                    <div class="interests-grid">
                        <label class="interest-checkbox">
                            <input type="checkbox" name="interests[]" value="food">
                            <span>🍽️ Food & Dining</span>
                        </label>
                        <label class="interest-checkbox">
                            <input type="checkbox" name="interests[]" value="nature">
                            <span>🌲 Nature & Parks</span>
                        </label>
                        <label class="interest-checkbox">
                            <input type="checkbox" name="interests[]" value="history">
                            <span>🏛️ History & Culture</span>
                        </label>
                        <label class="interest-checkbox">
                            <input type="checkbox" name="interests[]" value="shopping">
                            <span>🛍️ Shopping</span>
                        </label>
                        <label class="interest-checkbox">
                            <input type="checkbox" name="interests[]" value="nightlife">
                            <span>🌙 Nightlife</span>
                        </label>
                        <label class="interest-checkbox">
                            <input type="checkbox" name="interests[]" value="adventure">
                            <span>⛰️ Adventure</span>
                        </label>
                    </div>
                </div>

                <div class="form-step">
                    <label>Budget Range</label>
                    <div class="budget-options">
                        <label class="budget-option">
                            <input type="radio" name="budget" value="low" required>
                            <div>
                                <strong>Budget</strong><br>
                                <small>$50-100/day</small>
                            </div>
                        </label>
                        <label class="budget-option">
                            <input type="radio" name="budget" value="medium" required>
                            <div>
                                <strong>Moderate</strong><br>
                                <small>$100-200/day</small>
                            </div>
                        </label>
                        <label class="budget-option">
                            <input type="radio" name="budget" value="high" required>
                            <div>
                                <strong>Luxury</strong><br>
                                <small>$200+/day</small>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="form-step">
                    <label for="email">Email (optional - for itinerary delivery)</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           placeholder="your.email@example.com">
                </div>

                <button type="submit" class="submit-btn">
                    🗺️ Plan My Trip
                </button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; 2025 Virtual Path Pilot for amazing adventures.</p>
    </footer>

    <script>
        // Budget option selection
        document.querySelectorAll('.budget-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.budget-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                this.querySelector('input[type="radio"]').checked = true;
            });
        });

        // Form validation
        document.getElementById('tripForm').addEventListener('submit', function(e) {
            const startDate = document.querySelector('input[name="start_date"]').value;
            const endDate = document.querySelector('input[name="end_date"]').value;
            
            if (new Date(startDate) >= new Date(endDate)) {
                e.preventDefault();
                alert('End date must be after start date');
                return false;
            }
            
            if (new Date(startDate) < new Date()) {
                e.preventDefault();
                alert('Start date cannot be in the past');
                return false;
            }
        });

        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        document.querySelector('input[name="start_date"]').setAttribute('min', today);
        document.querySelector('input[name="end_date"]').setAttribute('min', today);
    </script>
</body>
</html>