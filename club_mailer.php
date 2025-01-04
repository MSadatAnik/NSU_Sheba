<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

require_once('db_connection.php');
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Get the president's student_id from session
$student_id = $_SESSION['student_id']; 

// Fetch the club name and president's name associated with the president's student_id
$sql_club = "SELECT cm.club_name, u.student_name FROM club_members cm JOIN users u ON cm.id = u.student_id WHERE cm.id = ? AND cm.designation = 'president'";
$stmt_club = $conn->prepare($sql_club);
$stmt_club->bind_param("s", $student_id);
$stmt_club->execute();
$result_club = $stmt_club->get_result();

$club_name = '';
$president_name = '';
if ($result_club->num_rows > 0) {
    $club_row = $result_club->fetch_assoc();
    $club_name = $club_row['club_name'];
    $president_name = $club_row['student_name'];
} else {
    echo "No club found for the president.";
    exit();
}

// Fetch members' email addresses for the club
$sql_members = "SELECT u.email FROM club_members cm JOIN users u ON cm.id = u.student_id WHERE cm.club_name = ?";
$stmt_members = $conn->prepare($sql_members);
$stmt_members->bind_param("s", $club_name);
$stmt_members->execute();
$result_members = $stmt_members->get_result();

$emails = [];
while ($row = $result_members->fetch_assoc()) {
    $emails[] = $row['email'];
}

$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = $_POST['subject'];
    $message = $_POST['message'];

    // Initialize PHPMailer
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'hasanemamrabby6@gmail.com'; 
    $mail->Password = 'kvky zvwy qkoh ftfq'; // Use App password here
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    // Set sender's name to include the president's title
    $mail->setFrom('hasanemamrabby6@gmail.com', $president_name .' ' .$club_name. ' (Club President)');

    $sendSuccess = true;

    foreach ($emails as $email) {
        $mail->addAddress($email); // Add a recipient

        // Email subject and body
        $mail->Subject = $subject;

        // Modify the message to include the sender's information
        $fullMessage = "<p>Dear Club Member,</p>";
        $fullMessage .= "<p>" . nl2br(htmlspecialchars($message)) . "</p>";
        $fullMessage .= "<p>Best regards,</p>";
        $fullMessage .= "<p>" . htmlspecialchars($president_name) . "<br>Club President</p>";
        $mail->Body = $fullMessage;
        $mail->isHTML(true); 

        if (!$mail->send()) {
            $error_message = 'Message could not be sent to ' . htmlspecialchars($email) . '. Mailer Error: ' . $mail->ErrorInfo;
            $sendSuccess = false; 
            break; 
        }

        // Clear all recipients for the next iteration
        $mail->clearAddresses();
    }

    // Close statement and connection
    $stmt_members->close();
    $conn->close();

    if ($sendSuccess) {
        // Redirect to event dashboard on success
        header('Location: events_dashboard.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Email to Club Members</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 600px;
            margin: 50px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

        .error-message {
            color: red;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        button {
            background-color: #5cb85c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            display: block;
            width: 100%;
        }

        button:hover {
            background-color: #4cae4c;
        }

        .back-button {
            background-color: red;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            width: 95%;
            text-align: center;
            margin-top: 15px;
            transition: background-color 0.3s;
        }

        .back-button:hover {
            background-color: darkred;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Send Email to Members of <?php echo htmlspecialchars($club_name); ?> Club</h1>
        <?php if ($error_message): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
        <form action="" method="POST">
            <label for="subject">Subject:</label>
            <input type="text" id="subject" name="subject" required>

            <label for="message">Message:</label>
            <textarea id="message" name="message" rows="5" required></textarea>

            <button type="submit">Send Email</button>
        </form>
        <a href="events_dashboard.php" class="back-button">Back to Event Dashboard</a>
    </div>
</body>
</html>
