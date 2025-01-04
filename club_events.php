<?php
// Start the session and database connection
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nsu_sheba";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); 
    exit();
}

// Get the president's ID from the session
$president_id = $_SESSION['student_id']; 

// Fetch the club name associated with the president
$sql = "SELECT cm.club_name FROM club_members cm WHERE cm.id = ? AND cm.designation = 'president'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $president_id);
$stmt->execute();
$result = $stmt->get_result();

$club_name = '';
if ($result->num_rows > 0) {
    $club = $result->fetch_assoc();
    $club_name = $club['club_name'];
} else {
    echo "You are not a president of any club.";
    exit;
}

// Initialize error message variable
$error_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_name = $_POST['event_name'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date']; 

    // Validate the event date
    $current_date = date('Y-m-d');
    if ($event_date < $current_date || $event_date == $current_date) {
        $error_message = "Error: Event date cannot be today or in the past.";
    } else {
        // Insert the event into the club_events table
        $sql = "INSERT INTO club_events (club_name, event_name, description, event_date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("ssss", $club_name, $event_name, $description, $event_date);
            
            if ($stmt->execute()) {
                echo "Event added successfully.";
            } else {
                $error_message = "Error: " . $stmt->error;
            }
            
            $stmt->close(); 
        } else {
            $error_message = "Error preparing statement: " . $conn->error; 
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Program for <?php echo htmlspecialchars($club_name); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            color: #333;
        }

        label {
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"],
        input[type="date"],
        textarea {
            width: 100%;
            padding: 8px;
            margin: 5px 0 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box; 
        }

        button {
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
        }

        button:hover {
            background-color: #45a049;
        }

        .back-button {
            background-color: #dc3545; 
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 100%;
            margin-top: 20px; 
        }

        .back-button:hover {
            background-color: #c82333;
        }

        .error-message {
            color: red; 
            margin-bottom: 15px; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add Program for <?php echo htmlspecialchars($club_name); ?></h1>
        <form action="" method="post">
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <label for="event_name">Event Name:</label>
            <input type="text" id="event_name" name="event_name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="event_date">Event Date:</label>
            <input type="date" id="event_date" name="event_date" required>

            <button type="submit">Add Event</button>
        </form>
        <button class="back-button" onclick="window.location.href='events_dashboard.php'">Back to Events Dashboard</button>
    </div>

    <?php
    // Close the connection only if it was successfully established
    if ($conn) {
        $conn->close();
    }
    ?>
</body>
</html>
