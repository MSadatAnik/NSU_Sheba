<?php
header('Content-Type: application/json');

// Database connection
session_start();
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);


// Check connection
if ($conn->connect_error) {
    die(json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]));
}

// Read and decode input
$data = json_decode(file_get_contents("php://input"));

// Log the received data for debugging
error_log("Received Data: " . print_r($data, true));

if (isset($data->seller_id) && isset($data->rating)) {
    $seller_id = (int)$data->seller_id; 
    $rating = (int)$data->rating;       // Explicitly cast to integer

    // Log parsed values
    error_log("Parsed Seller ID: $seller_id, Rating: $rating");

    // Validate rating value
    if ($rating < 1 || $rating > 5) {
        echo json_encode(["status" => "error", "message" => "Invalid rating value"]);
        exit();
    }

    // Insert rating into the database
    $stmt = $conn->prepare("INSERT INTO ratings (seller_id, rating, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $seller_id, $rating);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Rating submitted successfully"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(["status" => "error", "message" => "Invalid input"]);
}

$conn->close();
?> 

