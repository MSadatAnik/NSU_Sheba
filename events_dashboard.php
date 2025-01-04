<?php
session_start(); // Start the session at the beginning
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

require_once('db_connection.php');

// Assuming you have a way to get the user's ID from the session
$user_id = $_SESSION['student_id']; // Adjust this based on your session structure

// Query to check if the user is a president
$sql = "SELECT designation FROM club_members WHERE id = ?"; // Assuming 'id' is the user_id
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id); // Use the correct type for your ID
$stmt->execute();
$result = $stmt->get_result();

$is_president = false; // Default to false

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if ($row['designation'] === 'president') {
        $is_president = true; // Set to true if the user is a president
    }
}

// Query to check if the user is a member of any club
$sql_club = "SELECT club_name FROM club_members WHERE id = ?"; // Assuming 'id' is the user_id
$stmt_club = $conn->prepare($sql_club);
$stmt_club->bind_param("s", $user_id); // Use the correct type for your ID
$stmt_club->execute();
$result_club = $stmt_club->get_result();

$my_club = null; // Initialize to null

if ($result_club->num_rows > 0) {
    $my_club = $result_club->fetch_assoc()['club_name']; // Get the club name
}

$stmt->close();
$stmt_club->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 800px; /* Set a maximum width for the container */
            margin: 0 auto; /* Center the container */
            padding: 20px; /* Padding for the container */
            background-color: white; /* Background color for the container */
            border-radius: 8px; /* Rounded corners */
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1); /* Shadow for the container */
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px; /* Increased space between cards */
        }

        .card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            width: 200px; /* Fixed width for the card */
            text-align: center;
            padding: 15px;
            transition: transform 0.3s; /* Animation on hover */
        }

        .card:hover {
            transform: scale(1.05); /* Slightly enlarge on hover */
        }

        .card a {
            text-decoration: none;
            color: #333; /* Dark text color */
            display: block; /* Block display for entire card */
            height: 100%; /* Full height */
        }

        .back-button {
            display: inline-block;
            background-color: red; /* Red background */
            color: white; /* White text */
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            margin: 20px 36%; /* Center the button */
            font-weight: bold;
        }

        .back-button:hover {
            background-color: darkred; /* Darker red on hover */
        }

        footer {
            text-align: center;
            margin-top: 20px;
            color: #777; /* Light gray text */
        }

        /* Responsive styling */
        @media (max-width: 600px) {
            .card {
                width: 90%; /* Full width on small screens */
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Event Dashboard</h1>
        <div class="card-container">
            <?php if ($my_club): // Show My Club card if the user is a member of a club ?>
            <div class="card">
                <a href="club_my.php?club_name=<?php echo urlencode($my_club); ?>">
                    <h2>My Club</h2>
                    <p>View your club's details, members, and events.</p>
                </a>
            </div>
            <?php endif; ?>
            <?php if ($user_id == 'admin'): // Show My Club card if the user is a member of a club ?>
                <div class="card">
                <a href="admin_club_verification.php">
                    <h2>Admin Club Verification</h2>
                    <p>Verify club applications.</p>
                </a>
            </div>
            <?php endif; ?>
            <div class="card">
                <a href="club_app.php">
                    <h2>Club Application</h2>
                    <p>Submit your club applications.</p>
                </a>
            </div>

            <?php if ($is_president): // Only show this card if the user is a president ?>
            <div class="card">
                <a href="club_member_approval.php">
                    <h2>Club Member Approval</h2>
                    <p>Approve new club members.</p>
                </a>
            </div>
            <div class="card">
                <a href="club_events.php">
                    <h2>Add Events</h2>
                    <p>Add new events for your club.</p>
                </a>
            </div>
            <div class="card">
                <a href="club_mailer.php">
                    <h2>Send Email</h2>
                    <p>Send Email to members.</p>
                </a>
            </div>
            <div class="card">
                <a href="club_video_call.php">
                    <h2>Video Conference</h2>
                    <p>Start Video Conference with members.</p>
                </a>
            </div>
            <?php endif; ?>
            <div class="card">
                <a href="clubpage.php">
                    <h2>Join Club</h2>
                    <p>Join exciting clubs.</p>
                </a>
            </div>
            <div class="card">
                <a href="club_info.php">
                    <h2>Clubs Info</h2>
                    <p>View Details of clubs.</p>
                </a>
            </div>
            <div class="card">
                <a href="club_event_app.php">
                    <h2>Apply for Events</h2>
                    <p>Apply for events</p>
                </a>
            </div>
        </div>
        <a href="dashboard.php" class="back-button">Back to Dashboard</a>
    </div>
</body>
</html>
