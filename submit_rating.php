<?php
header('Content-Type: application/json');
session_start();
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
}

$data = json_decode(file_get_contents("php://input"));
$sellerId = $data->sellerId;
$rating = $data->rating;

$stmt = $conn->prepare("INSERT INTO ratings (seller_id, rating) VALUES (?, ?)");
$stmt->bind_param("si", $sellerId, $rating);
$success = $stmt->execute();

if ($success) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit rating.']);
}

$stmt->close();
$conn->close();
?>
