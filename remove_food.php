<?php
// Establishing the database connection
session_start();
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the removal of food item if the 'food_id' is sent via POST
$data = json_decode(file_get_contents('php://input'), true);  // Decode JSON input

if (isset($data['food_id'])) {
    $food_id = $data['food_id']; // Get the food ID from the JSON data

    // Prepare the SQL query to delete the food item
    $sql = "DELETE FROM foods WHERE id = ?"; // Assuming your food table is called 'foods' and has a column 'id'

    if ($stmt = $conn->prepare($sql = "DELETE FROM foods WHERE id = ?")) {
        $stmt->bind_param("i", $food_id); // Bind the food_id as an integer parameter
        $stmt->execute(); // Execute the query

        if ($stmt->affected_rows > 0) {
            echo json_encode(["success" => true, "message" => "Food item removed successfully."]);
        } else {
            echo json_encode(["success" => false, "message" => "Food item not found or couldn't be removed."]);
        }
        $stmt->close(); // Close the prepared statement
    } else {
        echo json_encode(["success" => false, "message" => "Error preparing SQL statement."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No food ID provided."]);
}

// Close the database connection
$conn->close();
?>
