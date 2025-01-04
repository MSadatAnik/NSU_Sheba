<?php
session_start();
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);


// Check connection
if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $conn->connect_error]);
    exit();
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

$token = isset($data['token']) ? $data['token'] : null;
$foodName = isset($data['foodName']) ? $data['foodName'] : null;
$price = isset($data['price']) ? $data['price'] : null;
$sellerId = isset($data['sellerId']) ? $data['sellerId'] : null;

// Validate input
if (!$token || !$foodName || !$price || !$sellerId) {
    echo json_encode(["status" => "error", "message" => "Missing required fields"]);
    exit();
}

// Prepare the SQL query
$sql = "INSERT INTO ordered_foods (token, food_name, price, seller_id) VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    echo json_encode(["status" => "error", "message" => "Failed to prepare the SQL statement: " . $conn->error]);
    exit();
}

$stmt->bind_param("isdi", $token, $foodName, $price, $sellerId);

if ($stmt->execute()) {
    echo json_encode(["status" => "success"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to execute the SQL query: " . $stmt->error]);
}

// Close the connection
$stmt->close();
$conn->close();
?>
