<?php
session_start();

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);


$student_name = $_SESSION['student_name'];
$student_id = $_SESSION['student_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find a Ride Mate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 20px;
        }
        h1 {
            text-align: center;
        }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #218838;
        }
        .card {
            background: #fff;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
            cursor: pointer; /* Change cursor to indicate clickable */
        }
        /* Modal styles */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1; 
            left: 0;
            top: 0;
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
            padding-top: 60px;
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }
        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Find a Ride Mate</h1>
    
    <div class="form-container">
        <form action="ride.php" method="POST">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="user_id">ID:</label>
                <input type="text" id="user_id" name="user_id" required>
            </div>
            <div class="form-group">
                <label for="contact_number">Contact Number:</label>
                <input type="text" id="contact_number" name="contact_number" required>
            </div>
            <div class="form-group">
                <label for="destination">Destination:</label>
                <input type="text" id="destination" name="destination" required>
            </div>
            <div class="form-group">
                <label for="meetup_place">Meetup Place:</label>
                <input type="text" id="meetup_place" name="meetup_place" required>
            </div>
            <button type="submit">Search Ride</button>
        </form>
    </div>

    <div class="ride-details">
        <?php
        // Database connection parameters
        
        include('db_connection.php');
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if the form is submitted
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Retrieve and sanitize input data
            $name = $conn->real_escape_string($_POST["name"]);
            $user_id = $conn->real_escape_string($_POST["user_id"]);
            $contact_number = $conn->real_escape_string($_POST["contact_number"]);
            $destination = $conn->real_escape_string($_POST["destination"]);
            $meetup_place = $conn->real_escape_string($_POST["meetup_place"]);

            // Insert data into the `ride` table
            $sql = "INSERT INTO ride (name, user_id, contact_number, destination, meetup_place) VALUES ('$name', '$user_id', '$contact_number', '$destination', '$meetup_place')";
            if ($conn->query($sql) === TRUE) {
                echo "<p style='color: green;'>Ride details saved successfully!</p>";
            } else {
                echo "<p style='color: red;'>Error saving ride details: " . $conn->error . "</p>";
            }
        }

        // Retrieve and display ride details
        $sql = "SELECT name, user_id, contact_number, destination, meetup_place FROM ride";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='card' onclick='openModal(\"" . htmlspecialchars($row["name"]) . "\", \"" . htmlspecialchars($row["contact_number"]) . "\")'>
                        <h3>" . htmlspecialchars($row["name"]) . " (ID: " . htmlspecialchars($row["user_id"]) . ")</h3>
                        <p><strong>Destination:</strong> " . htmlspecialchars($row["destination"]) . "</p>
                        <p><strong>Meetup Place:</strong> " . htmlspecialchars($row["meetup_place"]) . "</p>
                      </div>";
            }
        } else {
            echo "<p>No rides available yet.</p>";
        }

        // Close the connection
        $conn->close();
        ?>
    </div>

    <!-- Modal for showing ride details -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Contact Details</h2>
            <p id="modal-name"></p>
            <p id="modal-contact_number"></p>
        </div>
    </div>

    <script>
        // Function to open modal and show details
        function openModal(name, contact_number) {
            document.getElementById('modal-name').innerText = "Name: " + name;
            document.getElementById('modal-contact_number').innerText = "Contact Number: " + contact_number;
            document.getElementById('myModal').style.display = "block";
        }

        // Function to close modal
        function closeModal() {
            document.getElementById('myModal').style.display = "none";
        }

        // Close modal when user clicks outside of the modal content
        window.onclick = function(event) {
            var modal = document.getElementById('myModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
