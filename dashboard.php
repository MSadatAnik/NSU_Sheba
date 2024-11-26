<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Fetch user details from session
$student_name = $_SESSION['student_name'];
$student_id = $_SESSION['student_id'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - NSU Sheba</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .container {
            max-width: 1000px;
            margin: 120px auto;
            padding: 30px;
            text-align: center;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 36px;
            color: #3a6186;
            margin-bottom: 20px;
        }

        p {
            font-size: 20px;
            color: #555;
            margin-bottom: 40px;
        }

        .options {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-top: 40px;
        }

        .option {
            background-color: #fff;
            border: 2px solid #3a6186;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            width: 200px;
            transition: transform 0.3s ease, background-color 0.3s ease, color 0.3s ease;
        }

        .option:hover {
            background-color: #3a6186;
            color: #fff;
            transform: scale(1.05);
        }

        .option h3 a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .option:hover h3 a {
            color: #fff;
        }

        .option img {
            width: 80px;
            height: 80px;
            margin-bottom: 10px;
        }

        .btn-danger {
            display: inline-block;
            background-color: #ff4757;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            text-decoration: none;
            cursor: pointer;
            margin-top: 40px;
            transition: background-color 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #e63946;
        }

        @media (max-width: 600px) {
            .option {
                width: 100%;
            }

            nav ul {
                flex-direction: column;
                padding-top: 20px;
            }
        }
    </style>
</head>

<body>
    <nav>
        <img src="nsu-logo.png" alt="NSU Logo" class="logo">
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="#">Services</a></li>
            <li><a href="#">Events</a></li>
            <li><a href="#">Contact</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h1>
        <p>Your Student ID: <?php echo htmlspecialchars($student_id); ?></p>

        <div class="options">
            <div class="option">
                <img src="food.png" alt="Food Vendor">
                <h3>Food Vendor</h3>
                <p>Order food on campus easily</p>
            </div>
            <div class="option">
                <img src="book.png" alt="Book Vendor">
                <h3>Book Vendor</h3>
                <p>Buy or sell books</p>
            </div>
            <div class="option">
                <img src="event.png" alt="Event Management">
                <h3>Event Management</h3>
                <p>Manage university events</p>
            </div>
            <div class="option">
                <img src="Course Help.png" alt="Course Help">
                <h3>Course Help</h3>
                <p>Find help for your courses</p>
            </div>
            <div class="option">
                <img src="voting.png" alt="Voting">
                <h3><a href="vote.php">Vote</a></h3>
                <p>Participate in polls and make your voice heard</p>
            </div>

              <div class="option">
                <img src="blood.png" alt="Student Blood Bank">
                <h3><a href="blood_bank.php">Student Blood Bank</a></h3>
                <p>Donate or request blood from fellow students</p>
            </div>  
            
           <!-- <div class="option">
    <img src="field_slot_booking.png" alt="Field Slot Booking">
    <h3><a href="book_slot.php">Field Slot Booking</a></h3>
    <p>Reserve slots for indoor and outdoor sports fields dynamically</p>
</div>-->


        </div>

        <a href="login.php?action=logout" class="btn-danger">Logout</a>
    </div>
</body>

</html>