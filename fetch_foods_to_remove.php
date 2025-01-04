<?php
session_start();

// Ensure session contains the seller ID
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['error' => 'Student ID not set in session']);
    exit();
}

$seller_id = $_SESSION['student_id'];

// Database connection details
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

try {
    // Create PDO connection
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch orders matching the seller_id
    $stmt = $pdo->prepare("SELECT name, price, seller_name, seller_id, id FROM foods WHERE seller_id = :seller_id");
    $stmt->bindParam(':seller_id', $seller_id, PDO::PARAM_STR);
    $stmt->execute();

    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return orders in JSON format
    echo json_encode($orders);
} catch (PDOException $e) {
    // Return database error in JSON format
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>