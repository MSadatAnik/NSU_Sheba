<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

// Check if tutor_id is passed in the query string
if (!isset($_GET['tutor_id']) || !is_numeric($_GET['tutor_id'])) {
    echo 'No valid tutor selected.';
    exit;
}

$tutor_id = intval($_GET['tutor_id']);

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

// Fetch tutor details from the tutors table
$fetchTutorSql = "
    SELECT u.student_name 
    FROM tutors t 
    JOIN users u ON t.id = u.student_id 
    WHERE t.id = ?
";
$stmt = $conn->prepare($fetchTutorSql);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$tutor_result = $stmt->get_result();
$tutor = $tutor_result->fetch_assoc();

if (!$tutor) {
    echo 'Tutor not found.';
    exit;
}

// Fetch all interests for the tutor
$fetchInterestsSql = "
    SELECT interest 
    FROM tutors 
    WHERE id = ?
";
$stmt = $conn->prepare($fetchInterestsSql);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$interests_result = $stmt->get_result();
$interests = $interests_result->fetch_all(MYSQLI_ASSOC); // Fetch all interests

// Fetch only accepted tutoring requests for this tutor
$fetchAcceptedSql = "
    SELECT u.student_name, tr.interest 
    FROM tutoring_requests tr
    JOIN users u ON tr.student_id = u.student_id
    WHERE tr.tutor_id = ? AND tr.status = 'accepted'
";
$stmt = $conn->prepare($fetchAcceptedSql);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$accepted_result = $stmt->get_result();
$accepted_students = $accepted_result->fetch_all(MYSQLI_ASSOC);

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($tutor['student_name']); ?>'s Profile</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
        }
        body {
            background-color: lightgray;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
            padding: 20px;
        }
        .profile-container {
            width: 80%;
            max-width: 900px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-top: 30px;
        }
        h1 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 20px;
            text-align: center;
        }
        .card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .card h2 {
            font-size: 1.5em;
            color: #4CAF50;
            margin-bottom: 15px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #e7f3ff;
            border-left: 5px solid #007bff;
            border-radius: 4px;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: underline;
        }
        .back-link {
            margin-top: 20px;
            display: block;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h1><?= htmlspecialchars($tutor['student_name']); ?>'s Profile</h1>

        <!-- Tutor Interests Card -->
        <div class="card">
            <h2>Subjects of Interest</h2>
            <?php if (!empty($interests)): ?>
                <ul>
                    <?php foreach ($interests as $interest): ?>
                        <li><?= htmlspecialchars($interest['interest']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No subjects of interest found.</p>
            <?php endif; ?>
        </div>

        <!-- Accepted Students Card -->
        <div class="card">
            <h2>Accepted Students</h2>
            <?php if (!empty($accepted_students)): ?>
                <ul>
                    <?php foreach ($accepted_students as $student): ?>
                        <li><?= htmlspecialchars($student['student_name']); ?> - <?= htmlspecialchars($student['interest']); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No students have been accepted yet.</p>
            <?php endif; ?>
        </div>

        <!-- Back link -->
        <p class="back-link"><a href="tutormatching.php">Back to Tutor Matching</a></p>
    </div>
</body>
</html>
