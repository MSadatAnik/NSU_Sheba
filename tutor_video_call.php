<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login page
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the necessary POST data is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tutor_email'])) {
    $tutor_email = $_POST['tutor_email']; // Get the tutor's email
    $tutor_id = $_POST['tutor_id']; // Get the tutor's ID or name
    $student_id = $_SESSION['student_id']; // Student ID from session

    // Generate a unique room name for the video call
    $room_name = "call_{$student_id}_{$tutor_id}"; // Customize as needed

    // Prepare the Jitsi video call link
    $call_url = "https://meet.jit.si/{$room_name}"; // Use Jitsi's public server

    // Create a new PHPMailer instance
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = ''; 
        $mail->Password = ''; // Use App password here
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('', 'NSU Sheba');
        $mail->addAddress($tutor_email);

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = "Video Call Invitation from {$student_id}";
        $mail->Body = "You have received a video call invitation from {$student_id}. Join the call using the link: <a href='$call_url'>Join Call</a>";
        $mail->AltBody = "You have received a video call invitation from {$student_id}. Join the call using the link: $call_url"; // Plain text version

        // Send the email
        $mail->send();
        header("Location: $call_url");
    } catch (Exception $e) {
        echo "Failed to send invitation to $tutor_email. Mailer Error: {$mail->ErrorInfo}";
    }


} else {

    header('Location: my_tutors.php');
    exit;
}
?>
