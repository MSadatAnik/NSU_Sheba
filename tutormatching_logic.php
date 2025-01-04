<?php
session_start(); // Start the session

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // User is not logged in, redirect to login page
    header('Location: login.php'); 
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

// Initialize matched tutors array
$matched_tutors = [];

// Get the student interest from the POST request
if (isset($_POST['interest'])) {
    $student_interest = trim($_POST['interest']);

    // Query the tutors table to find matching interests
    $stmt = $conn->prepare("
        SELECT tutors.id, tutors.interest, users.student_name 
        FROM tutors 
        JOIN users ON tutors.id = users.student_id 
        WHERE tutors.interest LIKE ?
    ");
    $search_interest = "%{$student_interest}%";  // SQL wildcard search
    $stmt->bind_param("s", $search_interest);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Store the matched tutor details in an array
            $matched_tutors[] = [
                'name' => $row['student_name'],
                'interest' => $row['interest'],
                'id' => $row['id']
            ];
        }
    }

    $stmt->close();
}

$conn->close();

// Store matched tutors in the session for display
$_SESSION['matched_tutors'] = $matched_tutors;

// Redirect to the matching results page
header('Location: tutormatching.php');
exit;
?>
