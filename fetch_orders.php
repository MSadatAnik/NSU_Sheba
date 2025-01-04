<?php
session_start();

// Database credentials
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

// Check if session contains the student ID
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['error' => 'Student ID not set in session']);
    exit();
}

$seller_id = $_SESSION['student_id'];

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch orders for the specific seller
    $stmt = $pdo->prepare("SELECT token, food_name, price, seller_id, order_time FROM ordered_foods WHERE seller_id = :seller_id");
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_STR);
    $stmt->execute();

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the orders in JSON format
    echo json_encode($orders);
} catch (PDOException $e) {
    // Return error in JSON format
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>