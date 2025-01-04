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

// Get club name from query parameter
$club_name = isset($_GET['club_name']) ? $_GET['club_name'] : '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $student_id = $_SESSION['student_id']; 
    $designation = $_POST['designation']; 

    // Check if the member already exists in club_members
    $sql = "SELECT * FROM club_members WHERE club_name = ? AND id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $club_name, $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<p style='color: red;'>You are already a member of this club.</p>";
    } else {
        // Insert into the temporary members table
        $sql = "INSERT INTO temporary_members (id, club_name, designation) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $student_id, $club_name, $designation);
        
        if ($stmt->execute()) {
            echo "<p style='color: green;'>Your request to join the club has been submitted.</p>";
        } else {
            echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Club</title>
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
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        form {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"] {
            padding: 10px;
            width: 80%;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button[type="submit"], .return-button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            width: 84%; /* Full width */
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        .return-button {
            width: 40%;
            background-color: #28a745;
            margin-top: 15px;
            text-decoration: none;
            display: inline-block;
        }

        .return-button:hover {
            background-color: #218838;
        }

        .message {
            margin: 10px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Register for <?php echo htmlspecialchars($club_name); ?></h1>
        <form action="" method="post">
            <input type="hidden" name="club_name" value="<?php echo htmlspecialchars($club_name); ?>">
            
            <label for="designation">Designation:</label>
            <input type="text" id="designation" name="designation" required placeholder="Enter your designation">
            
            <button type="submit">Submit Request</button>
        </form>

        <!-- Return button now below the submit button -->
        <a href="events_dashboard.php" class="return-button">Return to Events Dashboard</a>
    </div>
</body>
</html>

<?php
$conn->close();
?>
