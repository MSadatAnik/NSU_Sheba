<?php
// rate_food.php
require_once 'db_connection.php'; // Include DB connection file

// Get data from the request
$data = json_decode(file_get_contents('php://input'), true);
$sellerId = $data['sellerId'];
$rating = $data['rating'];

// Insert rating into the ratings table
$query = "INSERT INTO ratings (seller_id, rating) VALUES ('$sellerId', '$rating')";
if (mysqli_query($conn, $query)) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit rating']);
}

mysqli_close($conn);
?>
