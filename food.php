<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

// Database configuration
session_start();
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Connection failed: ' . $conn->connect_error]));
}

// Handle GET request to fetch food items
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $sql = "SELECT name, price, seller_name, seller_id FROM foods"; 
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['status' => 'error', 'message' => 'Error fetching data: ' . $conn->error]);
        exit;
    }

    $foods = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $foods[] = $row;
        }
    }
    echo json_encode($foods);
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $conn->real_escape_string($data['name']);
    $price = $conn->real_escape_string($data['price']);
    $sellerName = $conn->real_escape_string($data['sellerName']);
    $sellerId = $conn->real_escape_string($data['sellerId']);
    
    $sql = "INSERT INTO foods (name, price, seller_name, seller_id) VALUES ('$name', '$price', '$sellerName', '$sellerId')";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['status' => 'success', 'message' => 'Food item added successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
