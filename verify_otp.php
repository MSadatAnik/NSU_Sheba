<?php
session_start();
include('db_connection.php');

// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['verify'])) {
    $entered_otp = $_POST['otp'];
    $email = $_SESSION['mail'] ?? '';

    // Check if session email is set
    if (empty($email)) {
        echo "<script>alert('Session expired. Please try again.'); window.location.replace('forgot_password.php');</script>";
        exit();
    }

    // Verify OTP
    if ($entered_otp == $_SESSION['otp']) {
        echo "<script>alert('OTP verified successfully!'); window.location.replace('reset_password.php');</script>";
    } else {
        echo "<script>alert('Invalid OTP. Please try again.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f0f0; /* Light background for better contrast */
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            padding: 20px;
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
        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
        }
        h2 {
            margin-bottom: 20px;
            color: #3a6186;
        }
        form {
            background-color: #ffffff; /* White background for the form */
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px; /* Max width to prevent stretching */
            transition: box-shadow 0.3s ease; /* Add transition for hover effect */
        }
        form:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); /* Slightly enhance shadow on hover */
        }
        label {
            display: block;
            margin-bottom: 10px;
            color: #3a6186;
            font-weight: bold; /* Make label text bold */
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px; /* Increased font size for input */
        }
        button {
            background-color: #3a6186;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.3s ease; /* Add transform transition */
            width: 100%;
        }
        button:hover {
            background-color: #ff4757;
            transform: translateY(-2px); /* Slightly raise button on hover */
        }
        p {
            margin-top: 20px;
        }
        a {
            color: #3a6186;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify OTP</h2>
        <form action="verify_otp.php" method="post">
            <label for="otp">Enter OTP:</label>
            <input type="text" name="otp" required>
            <button type="submit" name="verify">Verify OTP</button>
        </form>
    </div>
</body>
</html>
