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

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Initialize error and success messages
$error_message = "";
$success_message = "";

// Check for success message in URL
if (isset($_GET['success'])) {
    $success_message = "Application submitted successfully!";
}

// Handle event application form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_SESSION['student_id']; // Assuming the student ID is stored in the session
    
    // Check if this is an "Apply" request
    if (isset($_POST['event_id'])) {
        $event_id = $_POST['event_id'];

        // Check if the student has already applied for this event
        $check_sql = "SELECT * FROM event_application WHERE event_id = ? AND student_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ss", $event_id, $student_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            $error_message = "You have already applied for this event.";
        } else {
            // Insert application into the event_application table
            $apply_sql = "INSERT INTO event_application (event_id, student_id) VALUES (?, ?)";
            $apply_stmt = $conn->prepare($apply_sql);
            $apply_stmt->bind_param("ss", $event_id, $student_id);

            if ($apply_stmt->execute()) {
                // Redirect with a success message
                header("Location: " . $_SERVER['PHP_SELF'] . "?success=1");
                exit();
            } else {
                $error_message = "Failed to submit application.";
            }
        }
    }
    
    // Check if this is a "Cancel" request
    if (isset($_POST['cancel_event_id'])) {
        $cancel_event_id = $_POST['cancel_event_id'];

        // Delete the event from the club_events table
        $delete_event_sql = "DELETE FROM club_events WHERE event_id = ?";
        $delete_event_stmt = $conn->prepare($delete_event_sql);
        $delete_event_stmt->bind_param("s", $cancel_event_id);

        if ($delete_event_stmt->execute()) {
            // Clear applicants for the canceled event
            $clear_applicants_sql = "DELETE FROM event_application WHERE event_id = ?";
            $clear_applicants_stmt = $conn->prepare($clear_applicants_sql);
            $clear_applicants_stmt->bind_param("s", $cancel_event_id);
            $clear_applicants_stmt->execute();

            $success_message = "Event canceled successfully!";
        } else {
            $error_message = "Failed to cancel the event.";
        }
    }
}

// Fetch events from the club_events table and the number of applicants
$events_sql = "
    SELECT ce.event_id, ce.event_name, ce.description, ce.event_date, 
           COUNT(ea.student_id) AS applicant_count,
           cm.designation
    FROM club_events ce
    LEFT JOIN event_application ea ON ce.event_id = ea.event_id
    LEFT JOIN club_members cm ON ce.club_name = cm.club_name
    WHERE cm.id = ? AND ce.event_date >= NOW()  -- Show only future events
    GROUP BY ce.event_id
    ORDER BY ce.event_date ASC, applicant_count DESC";

$events_stmt = $conn->prepare($events_sql);
$student_id = $_SESSION['student_id']; // Assuming student ID is in session
$events_stmt->bind_param("s", $student_id);
$events_stmt->execute();
$events_result = $events_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events List</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            color: #343a40;
            text-align: center;
            margin-bottom: 20px;
        }

        .event-info {
            background-color: lightcyan;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 150px;
            transition: transform 0.2s;
        }

        .event-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .apply-button, .cancel-button, .return-button {
            background-color: #007bff;
            width: 30%;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .apply-button:hover, .cancel-button:hover {
            background-color: #0056b3;
        }

        .cancel-button {
            background-color: red;
        }

        .cancel-button:hover {
            background-color: darkred;
        }

        .return-button {
            background-color: red;
        }

        .return-button:hover {
            background-color: darkred;
        }

        .applicant-count {
            font-weight: bold;
            color: #6c757d;
            margin-top: 5px;
        }

        .description {
            margin: 10px 0;
            color: #495057;
            flex-grow: 1;
        }

        .button-container {
            text-align: center;
        }

        .error-message {
            color: red;
            margin: 15px 0;
            text-align: center;
        }

        .success-message {
            color: green;
            margin: 15px 0;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Available Events</h1>

        <?php if ($success_message): ?>
            <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($events_result && $events_result->num_rows > 0): ?>
            <?php if ($error_message): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>
            <?php while ($event = $events_result->fetch_assoc()): ?>
                <div class="event-info">
                    <div>
                        <strong>Event Name:</strong> <?php echo htmlspecialchars($event['event_name']); ?><br>
                        <strong>Description:</strong> <span class="description"><?php echo htmlspecialchars($event['description']); ?></span><br>
                        <strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?><br>
                        <strong>Number of Applicants:</strong> <span class="applicant-count"><?php echo htmlspecialchars($event['applicant_count']); ?></span>
                    </div>

                    <div class="button-container">
                        <!-- Apply Form -->
                        <form method="POST" action="">
                            <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                            <button class="apply-button" type="submit">Apply</button>
                        </form>

                        <?php if ($event['designation'] === 'president'): ?>
                            <!-- Cancel Form -->
                            <form method="POST" action="">
                                <input type="hidden" name="cancel_event_id" value="<?php echo htmlspecialchars($event['event_id']); ?>">
                                <button class="cancel-button" type="submit">Cancel Event</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No events available at the moment.</p>
        <?php endif; ?>

        <form action="events_dashboard.php" method="GET">
            <button class="return-button" type="submit">Return to Dashboard</button>
        </form>
    </div>

    <?php $conn->close(); ?>
</body>
</html>
