<?php
session_start(); // Start the session

include('db_connection.php');

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Fetch tutor ID from the session
$tutor_id = $_SESSION['student_id'] ?? null; // Use null if not set

if ($tutor_id === null) {
    // Handle the case where the tutor_id is not set
    $error = urlencode("Tutor ID not found in session.");
    header("Location: tutoring.php?error=$error");
    exit();
}

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $topics = trim($_POST["topics"]);
    $interests = explode(",", $topics); // Split topics by commas

    foreach ($interests as $interest) {
        $interest = trim($interest);

        // Check if the same interest already exists for this tutor
        $checkInterestSql = "SELECT * FROM tutors WHERE id = ? AND interest = ?";
        $stmt = $conn->prepare($checkInterestSql);
        $stmt->bind_param("is", $tutor_id, $interest);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            // Insert the interest into the tutoring table
            $insertSql = "INSERT INTO tutors (id, interest) VALUES (?, ?)";
            $stmt = $conn->prepare($insertSql);
            $stmt->bind_param("is", $tutor_id, $interest);

            if ($stmt->execute()) {
                // Redirect with success message
                $message = urlencode("$interest added successfully.");
                header("Location: dashboard.php?message=$message");
            } else {
                // Redirect with error message
                $error = urlencode("Error: " . $stmt->error);
                header("Location: tutoring.php?error=$error");
            }
        } else {
            // Redirect with message if interest already exists
            $error = urlencode("$interest is already listed.");
            header("Location: tutoring.php?error=$error");
        }
    }

    $stmt->close();
}

$conn->close();
?>

