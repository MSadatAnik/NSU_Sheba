<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$student_id = $_SESSION['student_id'];

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nsu_sheba";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_name = $_POST['club_name'];
    $file = $_FILES['doc'];

    if (empty($club_name) || $file['error'] !== UPLOAD_ERR_OK) {
        header('Location: club_app.php?fail=invalid_input');
        exit;
    }

    $allowed_mimes = ['image/jpeg', 'image/png', 'image/gif'];
    $max_size = 2 * 1024 * 1024; //2mb

    if (!in_array($file['type'], $allowed_mimes) || $file['size'] > $max_size) {
        header('Location: club_app.php?fail=invalid_file_type_or_size');
        exit;
    }

    $sql_check = "SELECT id FROM club WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $student_id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        header('Location: club_app.php?fail=existing_request');
        $stmt_check->close();
        exit;
    }
    $stmt_check->close();

    $target_dir = "C:/xampp/htdocs/Cse299/";
    $target_file = $target_dir . basename($file["name"]);

    if (!move_uploaded_file($file["tmp_name"], $target_file)) {
        header('Location: club_app.php?fail=upload_error');
        exit;
    }

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'hasanemamrabby6@gmail.com';
        $mail->Password = 'kvky zvwy qkoh ftfq';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('hasanemamrabby6@gmail.com', 'NSU Sheba');
        $mail->addAddress('mubasshirsadat25@gmail.com');

        $mail->addAttachment($target_file);
        $mail->isHTML(false);
        $mail->Subject = "New Club Registration: " . $club_name;
        $mail->Body = "A new club registration request.\n\nClub Name: " . $club_name . "\n\nPlease find the document attached.";

        $mail->send();

        // Only insert into database if email was successfully sent
        $sql = "INSERT INTO club (id, club_name, status, doc) VALUES (?, ?, 'pending', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iss", $student_id, $club_name, $target_file);
        if ($stmt->execute()) {
            header('Location: events_dashboard.php?success');
        } else {
            header('Location: club_app.php?fail=insert_error');
        }
        $stmt->close();

    } catch (Exception $e) {
        header('Location: club_app.php?fail=email_error&message=' . urlencode($e->getMessage()));
        exit;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register a Club</title>
    <style>
        * {
            box-sizing: border-box; 
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            text-align: center;
            margin: 0 20px;
        }

        h1 {
            color: #4a90e2;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin: 15px 0 5px;
            font-weight: bold;
            text-align: left;
        }

        input[type="text"], input[type="file"] {
            width: calc(100% - 20px);
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }

        button {
            background-color: #4a90e2;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }

        button:hover {
            background-color: #357ab7;
        }

        .message {
            color: #d9534f;
            font-weight: bold;
            margin-top: 10px;
            text-align: center;
        }

        .success {
            color: #5cb85c;
        }

        .back-button {
            background-color: #f44336; 
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .back-button:hover {
            background-color: #c62828; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register Your Club</h1>

        <!-- Display error or success messages in the center of the container -->
        <div class="message">
            <?php if (isset($_GET['fail'])): ?>
                <p class="error">
                    <?php
                        switch ($_GET['fail']) {
                            case 'invalid_input':
                                echo "Please provide a valid club name and upload the document.";
                                break;
                            case 'existing_request':
                                echo "You have already submitted a club registration request.";
                                break;
                            case 'upload_error':
                                echo "Failed to upload the document.";
                                break;
                            case 'insert_error':
                                echo "An error occurred while registering the club. Please try again.";
                                break;
                            case 'email_error':
                                // Display the specific email error message if available
                                if (isset($_GET['message'])) {
                                    echo "Email not sent: " . htmlspecialchars($_GET['message']);
                                } else {
                                    echo "An unknown error occurred while sending the email.";
                                }
                                break;
                            default:
                                echo "An unknown error occurred.";
                        }
                    ?>
                </p>
            <?php endif; ?>
        </div>

        <form action="club_app.php" method="post" enctype="multipart/form-data">
            <label for="club_name">Club Name:</label>
            <input type="text" id="club_name" name="club_name" required>

            <label for="doc">Upload Document (Proof of Club's Existence):</label>
            <input type="file" id="doc" name="doc" accept="image/*" required>

            <button type="submit">Register Club</button>
        </form>

        <!-- Back Button -->
        <a href="events_dashboard.php">
            <button class="back-button">Back to Events Dashboard</button>
        </a>
    </div>
</body>
</html>
