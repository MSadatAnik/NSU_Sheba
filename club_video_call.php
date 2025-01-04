<?php
session_start(); 

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
require_once('db_connection.php');

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the president's student ID
$president_id = $_SESSION['student_id'];

// Fetch the club name where the user is a president
$club_name_query = "SELECT club_name FROM club_members WHERE id = ? AND designation = 'President'";
$stmt = $conn->prepare($club_name_query);
$stmt->bind_param("s", $president_id);
$stmt->execute();
$stmt->bind_result($club_name);
$stmt->fetch();
$stmt->close();

// Generate a unique room name for the video call
$room_name = "call_{$club_name}_Club_President"; 

// Prepare the Jitsi video call link
$call_url = "https://meet.jit.si/{$room_name}"; 

// Fetch all members' emails from the users table for the specific club
$emails = [];
$members_query = "SELECT u.email FROM users u 
                  JOIN club_members cm ON u.student_id = cm.id 
                  WHERE cm.club_name = ?";
$stmt = $conn->prepare($members_query);
$stmt->bind_param("s", $club_name);
$stmt->execute();
$stmt->bind_result($email);

while ($stmt->fetch()) {
    $emails[] = $email; 
}
$stmt->close();

// Create a new PHPMailer instance
$mail = new PHPMailer(true);

foreach ($emails as $tutor_email) {
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hasanemamrabby6@gmail.com'; 
        $mail->Password = 'kvky zvwy qkoh ftfq'; 
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('your-email@gmail.com', 'NSU Sheba');
        $mail->addAddress($tutor_email);

        // Content
        $mail->isHTML(true); 
        $mail->Subject = "Video Call Invitation from {$president_id}";
        $mail->Body = "You have received a video call invitation from the president of {$club_name}. Join the call using the link: <a href='$call_url'>Join Call</a>";
        $mail->AltBody = "You have received a video call invitation from the president of {$club_name}. Join the call using the link: $call_url"; // Plain text version

        // Send the email
        $mail->send();
        $mail->clearAddresses(); 
    } catch (Exception $e) {
        echo "Failed to send invitation to $tutor_email. Mailer Error: {$mail->ErrorInfo}<br>";
    }
}


header("Location: $call_url");
exit;

$conn->close();
?>
