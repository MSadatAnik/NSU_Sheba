<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - NSU Sheba</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #fff;
            color: #333;
        }

        nav {
            display: flex;
            justify-content: space-between;
            padding: 20px 40px;
            background-color: #3a6186;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 100;
        }

        nav .logo {
            width: 60px;
        }

        nav ul {
            display: flex;
            list-style-type: none;
        }

        nav ul li {
            margin: 0 20px;
        }

        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: #ff4757;
        }

        .hero {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
            background-color: #f4f4f4;
            padding: 60px;
            margin-top: 60px; /* Prevent overlap with the fixed nav */
        }

        .hero h1 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #3a6186;
        }

        .options {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px; /* Add gap for better spacing */
            margin-top: 40px;
        }

        .option {
            background-color: #fff;
            border: 2px solid #3a6186;
            border-radius: 10px;
            padding: 20px; /* Reduced padding for a cleaner look */
            text-align: center;
            width: 180px;
            transition: transform 0.3s ease, background-color 0.3s ease;
        }

        .option:hover {
            background-color: #3a6186;
            color: #fff;
            transform: scale(1.05);
        }

        .option h3 {
            margin-bottom: 15px;
            font-size: 18px;
        }

        .option img {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
        }

        .content-section {
            padding: 60px 40px;
            background-color: #fff;
            text-align: center;
        }

        .content-section h2 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #3a6186;
        }

        .content-section p {
            font-size: 18px;
            max-width: 800px;
            margin: 0 auto;
        }

        @media (max-width: 600px) {
            nav ul {
                flex-direction: column;
                align-items: center;
            }
            .option {
                width: 90%; /* Make options wider on small screens */
            }
        }
    </style>
</head>
<body>
    <nav>
        <img src="nsu-logo.png" alt="NSU Logo" class="logo">
        <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Events</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="login.php">Login</a></li>
        </ul>
    </nav>

    <div class="hero">
        <h1>Welcome to NSU Sheba</h1>
        <div class="options">
            <div class="option">
                <img src="food.png" alt="Food Vendor">
                <h3><a href="login.php">Food Vendor</a></h3>
                <p>Order food on campus easily</p>
            </div>
            <div class="option">
                <img src="book.png" alt="Book Vendor">
                <h3><a href="login.php">Book Vendor</a></h3>
                <p>Buy or sell books</p>
            </div>
            <div class="option">
                <img src="event.png" alt="Event Management">
                <h3><a href="login.php">Event Management</a></h3>
                <p>Manage university events</p>
            </div>
            <div class="option">
                <img src="Course Help.png" alt="Course Help">
                <h3><a href="login.php">Course Help</a></h3>
                <p>Find help for your courses</p>
            </div>
            <div class="option">
                <img src="voting.png" alt="Voting">
                <h3><a href="login.php">Vote</a></h3>
                <p>Participate in polls and make your voice heard</p>
            </div>
            <div class="option">
                <img src="blood.png" alt="Student Blood Bank">
                <h3><a href="login.php">Student Blood Bank</a></h3>
                <p>Donate or request blood from fellow students</p>
            </div>  
        </div>
    </div>

    <div class="content-section">
        <h2>About NSU Sheba</h2>
        <p>NSU Sheba is a platform designed to help students with everything they need on campus, from food vendors to book sales, event management, and course assistance. We're here to simplify your university life!</p>
    </div>
</body>
</html>
