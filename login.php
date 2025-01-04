<?php
session_start();
include('db_connection.php');

// Check if the user is logging out
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();
    session_destroy();
    header("Location:index.php");
    exit();
}

// Handle user login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $password = $_POST['password'];

    // Check if student_id exists
    $check_student_id_query = "SELECT * FROM users WHERE student_id = ?";
    $stmt = mysqli_prepare($conn, $check_student_id_query);
    mysqli_stmt_bind_param($stmt, "s", $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $hashed_password = $row['password'];

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['student_id'] = $row['student_id'];
            $_SESSION['student_name'] = $row['student_name'];
            $_SESSION['loggedin'] = true; // Set loggedin to true
            header('Location: dashboard.php'); // Redirect to dashboard page
            exit(); // Make sure to exit after redirect
        } else {
            echo "<script>alert('Incorrect password!');</script>";
        }
    } else {
        echo "<script>alert('Student ID does not exist!');</script>";
    }
    mysqli_stmt_close($stmt);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
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
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
            background-color: #f4f4f4;
            padding: 60px;
        }

        .hero h1 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #3a6186;
        }

        .options {
            display: flex;
            justify-content: space-around;
            margin-top: 40px;
        }

        .option {
            background-color: #fff;
            border: 2px solid #3a6186;
            border-radius: 10px;
            padding: 30px;
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
            text-align: center;
        }

        .container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card {
            border: 2px solid #3a6186;
            border-radius: 10px;
            padding: 30px;
            background-color: #fff;
            width: 100%;
            max-width: 400px;
        }

        .form-outline {
            margin-bottom: 16px;
        }

        .form-outline label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #3a6186;
        }

        .form-outline input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn-primary {
            background-color: #3a6186;
            border: none;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .text-center {
            text-align: center;
        }

        .small {
            font-size: 14px;
        }

        .fw-bold {
            font-weight: bold;
        }

        .text-danger {
            color: #ff4757;
            text-decoration: none;
        }

        .text-danger:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 class="text-center mb-4">Student Login</h2>
            <form action="login.php" method="post">
                <div class="form-outline">
                    <label for="student_id" class="form-label">Student ID</label>
                    <input type="text" id="student_id" class="form-control" placeholder="Enter your Student ID" required="required" name="student_id" />
                </div>
                <div class="form-outline">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" class="form-control" placeholder="Enter your password" required="required" name="password" />
                </div>
                <div class="text-center">
                    <input type="submit" value="Login" class="btn-primary" />
                    <p class="small fw-bold mt-2 pt-1">Don't have an account? <a class="text-danger" href="signup.php"> Register</a></p>
                    <p class="small fw-bold mt-2 pt-1">Reset Password? <a class="text-danger" href="forgot password.php"> Reset</a></p>
                </div>

                
            </form>
        </div>
    </div>
</body>
</html>
