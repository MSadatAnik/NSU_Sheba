<?php
session_start();
include('db_connection.php');

// Turn on error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include PHPMailer files
require '/xampp/htdocs/nsu_sheba/PHPMailer/PHPMailer.php';
require '/xampp/htdocs/nsu_sheba/PHPMailer/SMTP.php';
require '/xampp/htdocs/nsu_sheba/PHPMailer/Exception.php';

// Use the PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (isset($_POST["register"])) {
    $student_id = $_POST["student_id"];
    $student_name = $_POST["student_name"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $re_password = $_POST["re_password"];
    $gender = $_POST["gender"];

    // Check if email is valid for NSU domain
    if (strpos($email, '@northsouth.edu') === false) {
        echo "<script>alert('Please use your NSU email address.');</script>";
        exit();
    }

    // Check if passwords match
    if ($password !== $re_password) {
        echo "<script>alert('Passwords do not match. Please try again.');</script>";
        exit();
    }
  // Check if email already exists
  $check_query = $conn->query("SELECT * FROM users WHERE email ='$email'");

  if (!$check_query) {
      die("Query Failed: " . $conn->error);
  }

  $rowCount = $check_query->num_rows;
    if (!empty($email) && !empty($password) && !empty($student_id) && !empty($student_name) && !empty($gender)) {
        if ($rowCount > 0) {
            echo "<script>alert('User with this email already exists!');</script>";
        } else {
            // Generate OTP and hash the password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $otp = rand(100000, 999999);

           // Insert data into temp_users table
$result = mysqli_query($conn, "INSERT INTO temp_users (student_id, student_name, email, password, gender, otp) VALUES ('$student_id', '$student_name', '$email', '$password_hash', '$gender', $otp)");

            if ($result) {
                $_SESSION['otp'] = $otp;
                $_SESSION['mail'] = $email;

                $mail = new PHPMailer(true); // Enable PHPMailer exceptions

              // Configure PHPMailer and send the email with notification details
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'hasanemamrabby6@gmail.com';
    $mail->Password = 'kvky zvwy qkoh ftfq';  // App password
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Recipient and message content
    $mail->setFrom('hasanemamrabby6@gmail.com', 'NSU Sheba Blood Bank');
    $mail->addAddress($requester_email);
    $mail->isHTML(true);
    $mail->Subject = 'Blood Donation Accepted';
    $mail->Body = "<p>Dear {$student_name}, </p> <h3>A donor has accepted your blood request.</h3>
                   <p>Contact them at: <br>Email: $accepter_email<br>Phone: $accepter_phone</p>
                   <p>With regards,<br>NSU Sheba Team</p>";

    $mail->send();
    echo "<p>Request accepted, and the requester has been notified via email.</p>";
} catch (Exception $e) {
    echo "<p>Message could not be sent. Mailer Error: {$mail->ErrorInfo}</p>";
}

            } else {
                echo "<script>alert('There was an error during registration. Please try again later.');</script>";
            }
        }
    } else {
        echo "<script>alert('Please fill in all fields.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - NSU Sheba</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Arial', sans-serif; background-color: #fff; color: #333; }
        nav { display: flex; justify-content: space-between; padding: 20px 40px; background-color: #3a6186; position: fixed; width: 100%; top: 0; z-index: 100; }
        nav .logo { width: 60px; }
        nav ul { display: flex; list-style-type: none; }
        nav ul li { margin: 0 20px; }
        nav ul li a { color: #fff; text-decoration: none; font-size: 16px; transition: color 0.3s ease; }
        nav ul li a:hover { color: #ff4757; }
        .container { display: flex; flex-direction: column; align-items: center; padding-top: 100px; }
        h2 { margin-bottom: 20px; color: #3a6186; }
        form { background-color: #f4f4f4; border-radius: 10px; padding: 40px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); width: 400px; }
        label { display: block; margin-bottom: 10px; color: #3a6186; }
        input[type="text"], input[type="email"], input[type="password"], select { width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; }
        button { background-color: #3a6186; color: #fff; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; transition: background-color 0.3s ease; width: 100%; }
        button:hover { background-color: #ff4757; }
        p { margin-top: 20px; }
        a { color: #3a6186; text-decoration: none; }
        a:hover { text-decoration: underline; }
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
        <h2>Sign Up</h2>
        <form action="signup.php" method="post" id="signupForm">
            <label for="student_id">Student ID:</label>
            <input type="text" id="student_id" name="student_id" required>

            <label for="student_name">Student Name:</label>
            <input type="text" id="student_name" name="student_name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <label for="re_password">Confirm Password:</label>
            <input type="password" id="re_password" name="re_password" required>

            <label for="gender">Gender:</label>
            <select id="gender" name="gender" required>
                <option value="" disabled selected>Select your gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>

            <button type="submit" name="register">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>

</html>
