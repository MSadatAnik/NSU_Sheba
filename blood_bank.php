<?php
session_start();
include 'db_connection.php'; // Include your database configuration

// Check if student_id is set in session
if (!isset($_SESSION['student_id'])) {
    echo "<script>alert('Please log in to access the blood bank features.');</script>";
    exit; // Stop further execution if student_id is not set
}

// Assign student_id from session
$student_id = $_SESSION['student_id'];

// Include PHPMailer files
require '/xampp/htdocs/nsu_sheba/PHPMailer/PHPMailer.php';
require '/xampp/htdocs/nsu_sheba/PHPMailer/SMTP.php';
require '/xampp/htdocs/nsu_sheba/PHPMailer/Exception.php';

// Use the PHPMailer namespace
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Donor Registration
if (isset($_POST['register_donor'])) {
    $donor_blood_group = $_POST['blood_group'];
    $age = $_POST['age'];
    $phone_number = $_POST['phone_number'];
    $health_condition = $_POST['health_condition'];

    // Check if the user is already registered as a donor
    $checkStmt = $conn->prepare("SELECT * FROM donors WHERE student_id = ?");
    $checkStmt->bind_param("s", $student_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // User is already registered
        echo "<script>alert('You are already registered as a blood donor.');</script>";
    } else {
        // User is not registered, proceed with insertion
        $stmt = $conn->prepare("INSERT INTO donors (student_id, donor_blood_group, age, phone_number, health_condition) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $student_id, $donor_blood_group, $age, $phone_number, $health_condition);
        if ($stmt->execute()) {
            echo "<script>alert('You have successfully registered as a blood donor.');</script>";
        } else {
            echo "<script>alert('Error registering as donor: " . $conn->error . "');</script>";
        }
        $stmt->close();
    }

    $checkStmt->close();
}

// Blood Request Submission
if (isset($_POST['submit_request'])) {
    $requester_id = $_SESSION['student_id'];
    $patient_name = $_POST['patient_name'];
    $patient_age = $_POST['patient_age'];
    $requested_blood_group = $_POST['blood_group_request'];
    $address = $_POST['address'];
    $date_of_need = $_POST['date_of_need'];
    $bags_needed = $_POST['bags_needed'];
    $additional_notes = $_POST['additional_notes'];

    // Insert into blood_requests table
    $stmt = $conn->prepare("INSERT INTO blood_requests (requester_student_id, patient_name, patient_age, requested_blood_group, address, date_of_need, bags_needed, additional_notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssisssis", $requester_id, $patient_name, $patient_age, $requested_blood_group, $address, $date_of_need, $bags_needed, $additional_notes);
    if ($stmt->execute()) {
        echo "<script>alert('Blood request submitted successfully.');</script>";
    } else {
        echo "<script>alert('Error submitting request: " . $conn->error . "');</script>";
    }
    $stmt->close();
}

// Display Matching Requests
function displayMatchingRequests($conn)
{
    $student_id = $_SESSION['student_id'];

    // Retrieve blood group of the current donor
    $stmt = $conn->prepare("SELECT donor_blood_group FROM donors WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $donorData = $result->fetch_assoc();
        $donor_blood_group = $donorData['donor_blood_group'];
    } else {
        echo "<p>You are not registered as a blood donor. Please register first to view requests.</p>";
        return;
    }
    $stmt->close();

    // Fetch matching blood requests
    $query = "
        SELECT br.*
        FROM blood_requests AS br
        WHERE br.requested_blood_group = ? 
          AND br.bags_needed > br.bags_fulfilled 
          AND br.status = 'pending' 
          AND br.requester_student_id != ?
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $donor_blood_group, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Display each request
        while ($row = $result->fetch_assoc()) {
            echo "<div class='blood-request'>";
            echo "<p>Patient Name: " . htmlspecialchars($row['patient_name']) . "</p>";
            echo "<p>Patient Age: " . htmlspecialchars($row['patient_age']) . "</p>";
            echo "<p>Blood Group: " . htmlspecialchars($row['requested_blood_group']) . "</p>";
            echo "<p>Address: " . htmlspecialchars($row['address']) . "</p>";
            echo "<p>Date of Need: " . htmlspecialchars($row['date_of_need']) . "</p>";
            echo "<p>Bags Needed: " . htmlspecialchars($row['bags_needed'] - $row['bags_fulfilled']) . "</p>";
            echo "<form method='POST' action=''>
                    <input type='hidden' name='request_id' value='" . htmlspecialchars($row['request_id']) . "'>
                    <button type='submit' name='accept_request'>Accept Request</button>
                  </form>";
            echo "</div><hr>";
        }
    } else {
        echo "<p>No matching blood requests found at the moment.</p>";
    }
    $stmt->close();
}




$response_message = ""; // Initialize an empty message

// Accept Blood Request and Send Notification
if (isset($_POST['accept_request'])) {
    $request_id = $_POST['request_id'];
    $accepter_id = $_SESSION['student_id'];
    $current_date = date('Y-m-d');  // Current date

    // Get request details, including the last accepted date
    $stmt = $conn->prepare("SELECT requester_student_id, bags_fulfilled, bags_needed, last_accepted_date FROM blood_requests WHERE request_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $stmt->bind_result($requester_id, $bags_fulfilled, $bags_needed, $last_accepted_date);
    $stmt->fetch();
    $stmt->close();

    // Check if the user is the requester
    if ($requester_id == $accepter_id) {
        $response_message = "<p>You cannot accept your own request.</p>";
    } else {
        // Calculate the difference between the current date and last accepted date
        $last_accepted_time = strtotime($last_accepted_date);
        $current_time = strtotime($current_date);
        $days_difference = ($current_time - $last_accepted_time) / (60 * 60 * 24); // Difference in days

        if ($days_difference < 90) {
            $response_message = "<p>You cannot accept this request again within 90 days.</p>";
        } elseif ($bags_fulfilled < $bags_needed) {
            $bags_fulfilled++;
            $status = $bags_fulfilled >= $bags_needed ? 'fulfilled' : 'pending';

            // Update blood_requests table with the new fulfilled count, status, and last_accepted_date
            $stmt = $conn->prepare("UPDATE blood_requests SET bags_fulfilled = ?, status = ?, last_accepted_date = ? WHERE request_id = ?");
            $stmt->bind_param("issi", $bags_fulfilled, $status, $current_date, $request_id);
            $stmt->execute();
            $stmt->close();

            // Get requester email and phone number
            $stmt = $conn->prepare("SELECT email, phone_number FROM users JOIN donors ON users.student_id = donors.student_id WHERE donors.student_id = ?");
            $stmt->bind_param("s", $requester_id);
            $stmt->execute();
            $stmt->bind_result($requester_email, $requester_phone);
            $stmt->fetch();
            $stmt->close();

            // Send email notification
            $mail = new PHPMailer(true); // Enable exceptions
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Gmail SMTP server
                $mail->SMTPAuth = true;
                $mail->Username = 'hasanemamrabby6@gmail.com'; // Your email
                $mail->Password = 'kvky zvwy qkoh ftfq'; // Use an App password if 2FA is enabled
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // Recipients
                $mail->setFrom('hasanemamrabby6@gmail.com', 'NSU Sheba Blood Bank');
                $mail->addAddress($requester_email);  // Add the requester's email

                // Content
                $mail->isHTML(true);
                $mail->Subject = 'Blood Donation Accepted';
                $mail->Body = "A donor has accepted your blood request. Contact them at: <br>Email: $requester_email<br>Phone: $requester_phone";

                // Send the email
                $mail->send();
                $response_message = "<p>Request accepted, and the requester has been notified via email.</p>";
            } catch (Exception $e) {
                $response_message = "<p>Failed to send email: {$mail->ErrorInfo}</p>";
            }
        } else {
            $response_message = "<p>The request is already fulfilled.</p>";
        }
    }
}

// Display matching requests again to reflect the updated status
displayMatchingRequests($conn);
$conn->close(); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Blood Bank - NSU Sheba</title>
    <style>
        /* NSU Sheba Blood Bank Styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            /* Light background for better readability */
            color: #333;
            /* Dark color for readability */
            margin: 0;
            padding: 0;
        }

        .container {
            width: 60%;
            margin: auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
        }

        h2 {
            color: #0056b3;
            /* NSU's blue color tone for headers */
            text-align: center;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            display: block;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        button {
            background-color: #0056b3;
            /* NSU Sheba main theme color */
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #004494;
            /* Darker shade for hover effect */
        }

        .blood-request {
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #fafafa;
        }

        .blood-request p {
            margin: 5px 0;
            color: #333;
        }

        .blood-request button {
            background-color: #28a745;
            /* Green button for accept request */
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .blood-request button:hover {
            background-color: #218838;
            /* Darker green on hover */
        }

        /* Additional Styles for Responsive Design */
        @media (max-width: 768px) {
            .container {
                width: 90%;
            }
        }
    </style>

</head>

<body>

    <div class="container">
        <!-- Blood Donor Registration Form -->
        <form id="donorForm" method="POST" action="blood_bank.php">
            <h2>Register as a Blood Donor</h2>
            <div class="form-group">
                <label for="blood_group">Blood Group:</label>
                <select name="blood_group" id="blood_group" required>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>
            <div class="form-group">
                <label for="age">Age:</label>
                <input type="number" name="age" id="age" required>
            </div>
            <div class="form-group">
                <label for="phone_number">Phone Number:</label>
                <input type="text" name="phone_number" id="phone_number" required>
            </div>
            <div class="form-group">
                <label for="health_condition">Health Condition (optional):</label>
                <textarea name="health_condition" id="health_condition"></textarea>
            </div>
            <button type="submit" name="register_donor">Register as Donor</button>
        </form>

        <!-- Blood Request Form -->
        <form id="requestForm" method="POST" action="blood_bank.php">
            <h2>Request Blood</h2>
            <div class="form-group">
                <label for="patient_name">Patient Name:</label>
                <input type="text" name="patient_name" id="patient_name" required>
            </div>
            <div class="form-group">
                <label for="patient_age">Patient Age:</label>
                <input type="number" name="patient_age" id="patient_age" required>
            </div>
            <div class="form-group">
                <label for="blood_group_request">Blood Group:</label>
                <select name="blood_group_request" id="blood_group_request" required>
                    <option value="A+">A+</option>
                    <option value="A-">A-</option>
                    <option value="B+">B+</option>
                    <option value="B-">B-</option>
                    <option value="AB+">AB+</option>
                    <option value="AB-">AB-</option>
                    <option value="O+">O+</option>
                    <option value="O-">O-</option>
                </select>
            </div>
            <div class="form-group">
                <label for="address">Address:</label>
                <textarea name="address" id="address" required></textarea>
            </div>
            <div class="form-group">
                <label for="date_of_need">Date of Need:</label>
                <input type="date" name="date_of_need" id="date_of_need" required>
            </div>
            <div class="form-group">
                <label for="bags_needed">Bags Needed:</label>
                <input type="number" name="bags_needed" id="bags_needed" required>
            </div>
            <div class="form-group">
                <label for="additional_notes">Additional Notes (optional):</label>
                <textarea name="additional_notes" id="additional_notes"></textarea>
            </div>
            <button type="submit" name="submit_request">Submit Request</button>
        </form>

        <!-- Display Requests and Accept Button -->
        <!-- <div id="available-requests">
    <h2>Available Blood Requests</h2>-->

        <!-- Show response message here -->
        <?php //if (!empty($response_message)) echo $response_message; 
        ?>

        <!-- Display matching requests -->
        <?php //displayMatchingRequests($conn); 
        ?>
    </div>
    </div>

</body>

</html> 

