<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Database connection failed.']));
}

// Get seller ID from the request
$seller_id = isset($_GET['seller_id']) ? $_GET['seller_id'] : null;

if (!$seller_id) {
    echo json_encode(['status' => 'error', 'message' => 'Seller ID is required.']);
    exit;
}

// Prepare SQL query to fetch seller info
$sql = "SELECT f.seller_name, f.seller_id, AVG(r.rating) AS average_rating
        FROM foods f
        LEFT JOIN ratings r ON f.seller_id = r.seller_id
        WHERE f.seller_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
        'status' => 'success',
        'data' => [
            'seller_name' => $row['seller_name'],
            'seller_id' => $row['seller_id'],
            'average_rating' => $row['average_rating'] !== null ? round($row['average_rating'], 1) : null
        ]
    ]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No seller found with the provided ID.']);
}

$stmt->close();
$conn->close();
?>
