<?php
// Start session and include required files
session_start();
require '/xampp/htdocs/nsu_sheba/PHPMailer/PHPMailer.php';
require '/xampp/htdocs/nsu_sheba/PHPMailer/SMTP.php';
require '/xampp/htdocs/nsu_sheba/PHPMailer/Exception.php';
include 'db_connection.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Ensure user is logged in
if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

// Function to send email notifications using PHPMailer
function sendEmailNotification($email, $subject, $message) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hasanemamrabby6@gmail.com'; 
        $mail->Password = 'kvky zvwy qkoh ftfq'; // Use App password here
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('hasanemamrabby6@gmail.com', 'NSU Sheba');
        $mail->addAddress($email);
        $mail->isHTML(true);

        $mail->Subject = $subject;
        $mail->Body = $message;

        $mail->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Function to generate available time slots for a given field and date
function getAvailableSlots($field_id, $booking_date, $conn) {
    $all_slots = ["08:00", "09:00", "10:00", "11:00", "12:00", "13:00", "14:00", "15:00", "16:00", "17:00"];
    $booked_slots = [];

    $query = "SELECT s.start_time FROM bookings b JOIN slots s ON b.slot_id = s.slot_id
              WHERE b.field_id = ? AND b.booking_date = ? AND b.booking_status = 'Confirmed'";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $field_id, $booking_date);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $booked_slots[] = $row['start_time'];
    }

    return array_diff($all_slots, $booked_slots);
}

// Handling slot booking submission
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['book_slot'])) {
    $field_id = $_POST['field_id'];
    $game = $_POST['game'];
    $slot_id = $_POST['slot_id'];
    $player_ids = $_POST['player_ids'];
    $additional_info = $_POST['additional_info'];
    $student_id = $_SESSION['student_id'];
    $booking_date = date('Y-m-d');

    // Validate required fields
    if ($field_id && $game && $slot_id) {
        // Insert booking
        $query = "INSERT INTO bookings (student_id, field_id, slot_id, game, player_ids, additional_info, booking_date, booking_status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("siissss", $student_id, $field_id, $slot_id, $game, $player_ids, $additional_info, $booking_date);

        if ($stmt->execute()) {
            echo "Booking request submitted successfully. You will receive an email confirmation shortly.";

            // Send confirmation email
            $email = $_SESSION['email']; // Assuming the email is stored in the session
            $message = "Dear Student,<br><br>Your booking request for $game on $booking_date has been received and is pending confirmation.<br>NSU Sheba Team";
            sendEmailNotification($email, 'Slot Booking Confirmation', $message);
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Error: Missing required fields.";
    }
}

// Handling booking cancellation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cancel_booking'])) {
    $booking_id = $_POST['booking_id'];
    $query = "UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ? AND student_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $booking_id, $_SESSION['student_id']);

    if ($stmt->execute()) {
        echo "Booking cancelled successfully.";

        // Send cancellation email
        $email = $_SESSION['email'];
        $message = "Dear Student,<br><br>Your booking has been cancelled as requested.<br>NSU Sheba Team";
        sendEmailNotification($email, 'Slot Booking Cancellation', $message);
    } else {
        echo "Error cancelling booking: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NSU Sheba Slot Booking</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Add basic styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
        }
        .booking-form {
            width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        label {
            display: block;
            margin: 15px 0 5px;
        }
        input[type="text"], select, textarea, button {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
            margin-top: 15px;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="booking-form">
        <h2>NSU Sports Field Slot Booking</h2>
        <form action="book_slot.php" method="post">
            <label for="field">Select Field:</label>
            <select name="field_id" id="field" required>
                <option value="1">Indoor Field - Racket</option>
                <option value="2">Indoor Field - Basketball</option>
                <option value="3">Outdoor Field - Cricket/Football</option>
            </select>

            <label for="game">Game:</label>
            <select name="game" id="game" required>
                <option value="Racket">Racket</option>
                <option value="Basketball">Basketball</option>
                <option value="Cricket">Cricket</option>
                <option value="Football">Football</option>
            </select>

            <label for="slot">Select Slot:</label>
            <select name="slot_id" id="slot" required>
                <?php
                $field_id = 1; // Sample field_id for example
                $booking_date = date('Y-m-d');
                $available_slots = getAvailableSlots($field_id, $booking_date, $conn);

                foreach ($available_slots as $slot) {
                    echo "<option value='$slot'>$slot</option>";
                }
                ?>
            </select>

            <label for="player_ids">Player IDs (comma separated):</label>
            <input type="text" name="player_ids" id="player_ids" required>

            <label for="additional_info">Additional Information:</label>
            <textarea name="additional_info" id="additional_info" placeholder="Any additional details"></textarea>

            <button type="submit" name="book_slot">Book Slot</button>
        </form>

        <h2>Cancel Booking</h2>
        <form action="book_slot.php" method="post">
            <label for="booking_id">Booking ID:</label>
            <input type="text" name="booking_id" id="booking_id" required>
            <button type="submit" name="cancel_booking">Cancel Booking</button>
        </form>
    </div>
</body>
</html>
