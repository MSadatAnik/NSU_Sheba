<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit();
}

require_once('db_connection.php');

$club_name = isset($_GET['club_name']) ? $_GET['club_name'] : '';

if ($club_name === '') {
    echo "No club selected.";
    exit();
}

// Query to get president's details
$sql_president = "SELECT u.student_name, u.email 
                  FROM club_members cm 
                  JOIN users u ON cm.id = u.student_id 
                  WHERE cm.club_name = ? AND cm.designation = 'president'";

// Query to get other members' details
$sql_members = "SELECT u.student_name, u.email 
                FROM club_members cm 
                JOIN users u ON cm.id = u.student_id 
                WHERE cm.club_name = ? AND cm.designation != 'president'";

// Query to get club events
$sql_events = "SELECT event_name, description FROM club_events WHERE club_name = ? AND event_date >= CURDATE()";

// Prepare and execute president query
$stmt_president = $conn->prepare($sql_president);
$stmt_president->bind_param("s", $club_name);
$stmt_president->execute();
$result_president = $stmt_president->get_result();

// Prepare and execute members query
$stmt_members = $conn->prepare($sql_members);
$stmt_members->bind_param("s", $club_name);
$stmt_members->execute();
$result_members = $stmt_members->get_result();

// Prepare and execute events query
$stmt_events = $conn->prepare($sql_events);
$stmt_events->bind_param("s", $club_name);
$stmt_events->execute();
$result_events = $stmt_events->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($club_name); ?> Club Details</title>
    <link rel="stylesheet" href="styles.css"> <!-- Add your CSS file -->
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px; 
            margin: 0 auto; 
            padding: 20px; 
            background-color: white; 
            border-radius: 8px; 
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); 
        }

        h1, h2 {
            color: #333;
            margin-bottom: 10px;
            font-weight: 600; 
        }

        .president {
            padding: 10px;
            margin-bottom: 20px;
        }

        .members {
            margin-bottom: 20px;
        }

        ul {
            list-style-type: none;
            padding-left: 0;
        }

        .back-button {
            display: inline-block;
            background-color: #f44336; 
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin-top: 20px;
            transition: background-color 0.3s; 
        }

        .back-button:hover {
            background-color: #d32f2f; 
        }

        .footer {
            margin-top: 40px; 
            text-align: center; 
            font-size: 14px; 
            color: #777; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($club_name); ?> Club Details</h1>

        <h2>President:</h2>
        <div class="president">
            <?php 
            if ($result_president->num_rows > 0) {
                $president_row = $result_president->fetch_assoc();
                echo htmlspecialchars($president_row['student_name']) . " - " . htmlspecialchars($president_row['email']); 
            } else {
                echo "No president assigned.";
            }
            ?>
        </div>

        <h2>Members:</h2>
        <div class="members">
            <ul>
                <?php while ($row = $result_members->fetch_assoc()): ?>
                    <li><?php echo htmlspecialchars($row['student_name']); ?> - <?php echo htmlspecialchars($row['email']); ?></li>
                <?php endwhile; ?>
            </ul>
        </div>

        <h2>Events:</h2>
        <ul>
            <?php while ($row = $result_events->fetch_assoc()): ?>
                <li><?php echo htmlspecialchars($row['event_name']); ?>: <?php echo htmlspecialchars($row['description']); ?></li>
            <?php endwhile; ?>
        </ul>

        <a href="events_dashboard.php" class="back-button">Back to Event Dashboard</a>
    </div>

</body>
</html>

<?php
$stmt_president->close();
$stmt_members->close();
$stmt_events->close();
$conn->close();
?>
