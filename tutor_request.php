<?php

require __DIR__ . '/vendor/autoload.php';


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start(); // Start session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

// Database connection
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve the requester's information from the session
    $student_id = $_SESSION['student_id']; // Assuming student_id is stored in session
    $tutor_id = intval($_POST["tutor_id"]); // Tutor ID from the form
    $interest = trim($_POST["interest"]); // Interest from the form

    // Check if the student is trying to request themselves
    if ($student_id == $tutor_id) {
        // Redirect to the dashboard with an error message
        header("Location: dashboard.php?request_status=error&message=" . urlencode("You cannot request yourself as a tutor."));
        exit();
    }

    // Check if the same request already exists
    $checkRequestSql = "SELECT * FROM tutoring_requests WHERE student_id = ? AND tutor_id = ? AND interest = ?";
    $stmt = $conn->prepare($checkRequestSql);
    $stmt->bind_param("iis", $student_id, $tutor_id, $interest);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Insert the request into the tutoring_requests table with default status 'pending'
        $insertSql = "INSERT INTO tutoring_requests (student_id, tutor_id, interest, status) VALUES (?, ?, ?, 'pending')";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("iis", $student_id, $tutor_id, $interest);

        if ($stmt->execute()) {
            // Retrieve the tutor's email from the users table
            $tutorEmailSql = "SELECT email FROM users WHERE student_id = ?";
            $stmt = $conn->prepare($tutorEmailSql);
            $stmt->bind_param("i", $tutor_id);
            $stmt->execute();
            $emailResult = $stmt->get_result();

            if ($emailResult->num_rows > 0) {
                $tutorEmailRow = $emailResult->fetch_assoc();
                $tutorEmail = $tutorEmailRow['email'];

                // Send email notification to the tutor
                $mail = new PHPMailer(true);
                try {
                    // SMTP server configuration
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = ''; 
                    $mail->Password = ''; // Use App password here
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    // Email settings
                    $mail->setFrom('', 'NSU Sheba');
                    $mail->addAddress($tutorEmail);

                    // Email content
                    $mail->isHTML(true);
                    $mail->Subject = 'New Tutoring Request';
                    $mail->Body = "You have received a new tutoring request from Student ID: $student_id for interest: $interest.";

                    // Send email
                    $mail->send();
                } catch (Exception $e) {
                    // Handle email sending errors
                    header("Location: dashboard.php?request_status=error&message=" . urlencode("Mailer Error: " . $mail->ErrorInfo));
                    exit();
                }
            }

            // Redirect to the dashboard after successful request and email notification
            header("Location: dashboard.php?request_status=success");
            exit();
        } else {
            // Redirect to the dashboard with error status
            header("Location: dashboard.php?request_status=error&message=" . urlencode("Error: " . $stmt->error));
            exit();
        }
    } else {
        // Redirect to the dashboard with error status for duplicate request
        header("Location: dashboard.php?request_status=error&message=" . urlencode("You have already requested this tutor for the same interest."));
        exit();
    }
    $stmt->close();
}

$conn->close();
?>
