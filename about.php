<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Virtual Path Pilot</title>
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
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
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

        .nav-links a:hover, .nav-links a.active {
            color: var(--light-green);
        }

        .main-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 100px 2rem 3rem;
        }

        .page-header {
            text-align: center;
            margin-bottom: 4rem;
        }

        .page-title {
            font-size: 3rem;
            font-weight: 700;
            color: var(--dark-green);
            margin-bottom: 1rem;
        }

        .page-subtitle {
            font-size: 1.3rem;
            color: var(--medium-green);
            line-height: 1.6;
        }

        .content-section {
            background: white;
            border-radius: 15px;
            padding: 3rem;
            margin-bottom: 3rem;
            box-shadow: 0 10px 30px rgba(11, 97, 56, 0.1);
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: var(--dark-green);
            margin-bottom: 1.5rem;
            text-align: center;
        }

        .section-content {
            color: var(--medium-green);
            line-height: 1.8;
            font-size: 1.1rem;
        }

        .section-content p {
            margin-bottom: 1.5rem;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .feature {
            text-align: center;
            padding: 2rem;
            background: var(--bg-light);
            border-radius: 10px;
            border: 2px solid var(--light-green);
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .feature-title {
            font-size: 1.4rem;
            font-weight: 600;
            color: var(--dark-green);
            margin-bottom: 1rem;
        }

        .feature-description {
            color: var(--medium-green);
            line-height: 1.6;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .stat {
            text-align: center;
            padding: 1.5rem;
            background: var(--accent-green);
            color: white;
            border-radius: 10px;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1rem;
            opacity: 0.9;
        }

        .team-section {
            text-align: center;
        }

        .team-member {
            max-width: 400px;
            margin: 0 auto;
            background: var(--bg-light);
            border-radius: 10px;
            padding: 2rem;
            border: 2px solid var(--light-green);
        }

        .team-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: var(--accent-green);
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
        }

        .team-name {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-green);
            margin-bottom: 0.5rem;
        }

        .team-role {
            font-size: 1.1rem;
            color: var(--medium-green);
            margin-bottom: 1rem;
        }

        .team-bio {
            color: var(--medium-green);
            line-height: 1.6;
        }

        .cta-section {
            background: linear-gradient(135deg, var(--accent-green), var(--medium-green));
            color: white;
            text-align: center;
            padding: 3rem;
            border-radius: 15px;
        }

        .cta-title {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .cta-description {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.95;
        }

        .cta-button {
            display: inline-block;
            padding: 1rem 2rem;
            background: white;
            color: var(--dark-green);
            text-decoration: none;
            border-radius: 8px;
            font-size: 1.2rem;
            font-weight: 600;
            transition: transform 0.2s ease;
        }

        .cta-button:hover {
            transform: translateY(-2px);
        }

        .footer {
            background: var(--dark-green);
            color: white;
            text-align: center;
            padding: 2rem 0;
        }

        @media (max-width: 768px) {
            .page-title {
                font-size: 2.2rem;
            }
            
            .nav-links {
                display: none;
            }
            
            .features-grid, .stats-grid {
                grid-template-columns: 1fr;
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
                    <li><a href="#about" class="active">About</a></li>
                    <li><a href="contact.php">Contact</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="main-container">
        <section class="page-header">
            <h1 class="page-title">About Virtual Path Pilot</h1>
            <p class="page-subtitle">Revolutionizing travel planning with intelligent, personalized itineraries</p>
        </section>

        <section class="content-section">
            <h2 class="section-title">Our Mission</h2>
            <div class="section-content">
                <p>We believe that every traveler deserves a perfectly crafted adventure tailored to their unique interests, budget, and dreams. Travel planning shouldn't be overwhelming or time-consuming – it should be exciting and effortless.</p>
                <p>Our mission is to democratize expert travel planning by making personalized, detailed itineraries accessible to everyone, regardless of their travel experience or planning skills.</p>
            </div>
        </section>

        <section class="content-section">
            <h2 class="section-title">How Our AI Works</h2>
            <div class="section-content">
                <p>Our advanced artificial intelligence system analyzes millions of travel data points, reviews, and preferences to understand what makes each destination special. When you share your interests and requirements, our AI doesn't just suggest popular attractions – it crafts a personalized journey that matches your travel style.</p>
                <p>The system considers factors like travel pace, budget constraints, seasonal variations, local events, and even weather patterns to ensure your itinerary is not just personalized, but also practical and enjoyable.</p>
            </div>

            <div class="features-grid">
                <div class="feature">
                    <div class="feature-icon">🧠</div>
                    <h3 class="feature-title">Smart Personalization</h3>
                    <p class="feature-description">Learns your preferences and creates unique experiences tailored to your interests, budget, and travel style.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">⚡</div>
                    <h3 class="feature-title">Instant Planning</h3>
                    <p class="feature-description">Generate comprehensive day-by-day itineraries in seconds, not hours of manual research.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">🌍</div>
                    <h3 class="feature-title">Local Insights</h3>
                    <p class="feature-description">Access hidden gems and local recommendations that you won't find in typical guidebooks.</p>
                </div>
                <div class="feature">
                    <div class="feature-icon">💰</div>
                    <h3 class="feature-title">Budget Optimization</h3>
                    <p class="feature-description">Maximize your travel experience within your budget with smart spending recommendations.</p>
                </div>
            </div>
        </section>

        <section class="content-section">
            <h2 class="section-title">By the Numbers</h2>
            <div class="stats-grid">
                <div class="stat">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Happy Travelers</div>
                </div>
                <div class="stat">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Destinations Covered</div>
                </div>
                <div class="stat">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Recommendations Made</div>
                </div>
                <div class="stat">
                    <div class="stat-number">0/5</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </section>

        <section class="content-section">
            <h2 class="section-title">What Makes Us Different</h2>
            <div class="section-content">
                <p><strong>Truly Personalized:</strong> We don't believe in one-size-fits-all travel. Every itinerary is uniquely crafted based on your specific interests, preferences, and constraints.</p>
                <p><strong>Real-Time Intelligence:</strong> Our AI continuously learns from traveler feedback and real-world conditions to provide the most current and relevant recommendations.</p>
                <p><strong>Comprehensive Planning:</strong> From major attractions to local eateries, transportation tips to cultural insights – we cover every aspect of your journey.</p>
                <p><strong>Flexible and Adaptable:</strong> Your itinerary serves as a smart guide, not a rigid schedule. Easily adapt and modify plans as your trip unfolds.</p>
            </div>
        </section>

        <section class="content-section team-section">
            <h2 class="section-title">Meet the Team</h2>
            <div class="team-member">
                <div class="team-avatar">👨‍💻</div>
                <h3 class="team-name">Ethical Lords</h3>
                <p class="team-role">Founder & Lead Developer</p>
                <p class="team-bio">A passionate traveler and tech enthusiast who visited 45 countries and got frustrated with generic travel advice.Ethical Lords created Virtual Path Pilot to solve the problem of impersonal, time-consuming travel planning that every traveler