<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirect to the login page if not logged in
    header('Location: login.php'); 
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tutor Matching</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 20px;
        }

        h1 {
            text-align: center;
            color: #333;
        }

        form {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            text-align: center;
        }

        label {
            font-weight: bold;
            margin-bottom: 10px;
            display: block;
        }

        input[type="text"] {
            width: 100%;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        input[type="submit"], .back-button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            box-sizing: border-box;
            transition: background-color 0.3s;
            margin-bottom: 10px;
            display: block;
            text-decoration: none;
            text-align: center;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .back-button {
            background-color: #ff4d4d;
        }

        .back-button:hover {
            background-color: #cc0000;
        }
    </style>
</head>
<body>
    <h1>Find Your Tutor</h1>
    <form action="tutormatching_logic.php" method="POST">
        <label for="interest">Enter your topic of interest:</label>
        <input
            type="text"
            id="interest"
            name="interest"
            placeholder="e.g., php"
            required
        />
        <input type="submit" value="Search" />
        <a href="tutor_my.php" class="back-button">Back to Dashboard</a>
    </form>
</body>
</html>
