<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login page
    exit;
}

// Assuming the student's ID is stored in the session
$student_id = $_SESSION['student_id']; // Replace with your session variable for student ID

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

// Fetch accepted tutors
$accepted_sql = "
    SELECT u.student_name, u.email, tr.interest 
    FROM tutoring_requests tr 
    JOIN users u ON tr.tutor_id = u.student_id 
    WHERE tr.student_id = ? AND tr.status = 'accepted'
";
$accepted_stmt = $conn->prepare($accepted_sql);
$accepted_stmt->bind_param("s", $student_id);
$accepted_stmt->execute();
$accepted_result = $accepted_stmt->get_result();

// Fetch pending tutors
$pending_sql = "
    SELECT u.student_name, tr.interest 
    FROM tutoring_requests tr 
    JOIN users u ON tr.tutor_id = u.student_id 
    WHERE tr.student_id = ? AND tr.status = 'pending'
";
$pending_stmt = $conn->prepare($pending_sql);
$pending_stmt->bind_param("s", $student_id);
$pending_stmt->execute();
$pending_result = $pending_stmt->get_result();

$accepted_stmt->close();
$pending_stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tutors</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .tutor-container {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
        }
        .tutor {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .tutor h3 {
            margin: 0 0 10px;
            color: black;
        }
        .tutor p {
            margin: 0 0 10px;
            color: #555;
        }
        .no-match {
            text-align: center;
            color: #d9534f;
            font-weight: bold;
        }
        .button-container {
            display: flex;
            justify-content: center;
            margin: 20px 0;
        }
        .back-button, .search-button, .video-call-button {
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
            margin: 0 10px; /* Margin for spacing between buttons */
        }
        .back-button {
            background-color: #dc3545; /* Red for Back button */
        }
        .back-button:hover {
            background-color: #c82333; /* Darker red on hover */
        }
        .search-button {
            background-color: #007bff; /* Blue for Search button */
        }
        .search-button:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }
        .video-call-button {
            background-color: #28a745; /* Green for Video Call button */
        }
        .video-call-button:hover {
            background-color: #218838; /* Darker green on hover */
        }
    </style>
</head>
<body>
<h1>My Tutors</h1>
<div class="tutor-container">
    <h2>Accepted Tutors</h2>
    <?php if ($accepted_result->num_rows > 0): ?>
        <?php while ($tutor = $accepted_result->fetch_assoc()): ?>
            <div class="tutor">
                <h3><?= htmlspecialchars($tutor['student_name']); ?></h3>
                <p>Interest: <?= htmlspecialchars($tutor['interest']); ?></p>
                <form method="post" action="tutor_video_call.php"> <!-- Link to your video call handling page -->
                    <input type="hidden" name="tutor_id" value="<?= htmlspecialchars($tutor['student_name']); ?>"> <!-- Pass the tutor's name -->
                    <input type="hidden" name="tutor_email" value="<?= htmlspecialchars($tutor['email']); ?>"> <!-- Pass the tutor's email -->
                    <button type="submit" name="action" value="video_call" class="video-call-button">Video Call</button>
                </form>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-match">No accepted tutors found.</p>
    <?php endif; ?>
    
    <h2>Pending Tutors</h2>
    <?php if ($pending_result->num_rows > 0): ?>
        <?php while ($tutor = $pending_result->fetch_assoc()): ?>
            <div class="tutor">
                <h3><?= htmlspecialchars($tutor['student_name']); ?></h3>
                <p>Interest: <?= htmlspecialchars($tutor['interest']); ?></p>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="no-match">No pending tutors found.</p>
    <?php endif; ?>
</div>

<form method="post" action="">
    <div class="button-container">
        <button type="submit" name="action" value="dashboard" class="back-button">Back to Dashboard</button>
        <button type="submit" name="action" value="search" class="search-button">Search for Tutors</button>
    </div>
</form>

<?php
// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'dashboard') {
            header('Location: dashboard.php');
            exit;
        } elseif ($_POST['action'] === 'search') {
            header('Location: tutorsearch.php');
            exit;
        }
    }
}
?>
</body>
</html>
