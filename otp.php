<?php 
session_start();
include('db_connection.php'); // Ensure this file correctly connects to your database

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['verify'])) {
    // Sanitize and retrieve entered OTP and email from session
    $entered_otp = $_POST['otp'];
    $email = $_SESSION['mail'] ?? ''; // Safely access session variable

    // Check if session email is set
    if (empty($email)) {
        echo "<script>alert('Session expired. Please try signing up again.'); window.location.replace('signup.php');</script>";
        exit();
    }

    // Prepare and execute a query to verify the OTP
    $stmt = $conn->prepare("SELECT * FROM temp_users WHERE email = ? AND otp = ?");
    $stmt->bind_param("si", $email, $entered_otp); // Change to "si" if otp is an integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Insert user into the 'users' table
        $stmt_insert = $conn->prepare("INSERT INTO users (student_id, student_name, email, password, gender) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("sssss", $row['student_id'], $row['student_name'], $row['email'], $row['password'], $row['gender']);
        $insert_user = $stmt_insert->execute();

        // Remove user from 'temp_users' table after successful registration
        $stmt_delete = $conn->prepare("DELETE FROM temp_users WHERE email = ?");
        $stmt_delete->bind_param("s", $email);
        $delete_temp_user = $stmt_delete->execute();

        if ($insert_user && $delete_temp_user) {
            echo "<script>
                    alert('OTP verified successfully! Registration complete.');
                    window.location.replace('login.php');
                  </script>";
        } else {
            echo "<script>alert('Registration failed. Please try again.');</script>";
        }
    } else {
        echo "<script>alert('Invalid OTP, please try again.');</script>";
    }

    // Close all prepared statements and connection
    $stmt->close();
    $stmt_insert->close();
    $stmt_delete->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
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

        .container {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 100px; /* Space for the fixed navbar */
        }

        h2 {
            margin-bottom: 20px;
            color: #3a6186;
        }

        form {
            background-color: #f4f4f4;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 400px; /* Fixed width for the form */
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #3a6186;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            background-color: #3a6186;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%; /* Full width button */
        }

        button:hover {
            background-color: #ff4757;
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

    <div class="container">
        <h2>OTP Verification</h2>
        <form action="otp.php" method="post">
            <label for="otp">Enter OTP:</label>
            <input type="text" id="otp" name="otp" required>

            <button type="submit" name="verify">Verify OTP</button>
        </form>
        <p>Didn't receive an OTP? <a href="#">Resend OTP</a></p>
    </div>
</body>
</html>