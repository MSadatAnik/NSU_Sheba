<?php
session_start(); // Start session to access session variables

// Check if the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit;
}

// Fetch tutor's student ID from session
$tutor_id = $_SESSION['student_id'];

// Database connection credentials
$servername = "localhost";  
$username = "root";         
$password = "";             
$dbname = "nsu_sheba";      

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to update request status
function updateRequestStatus($conn, $request_id, $status) {
    $updateSql = "UPDATE tutoring_requests SET status = ? WHERE request_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("ss", $status, $request_id);
    return $stmt->execute();
}

// Handle accept or reject request actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $request_id = intval($_POST['request_id']);
    $action = $_POST['action'];

    if ($action === 'accept') {
        updateRequestStatus($conn, $request_id, 'accepted');
    } elseif ($action === 'reject') {
        updateRequestStatus($conn, $request_id, 'rejected');
    }

    // Redirect to refresh the page after updating
    header('Location: tutor_dashboard.php');
    exit;
}

// Fetch the tutor's interested topics
$interestsSql = "SELECT interest FROM tutors WHERE id = ?";
$interestsStmt = $conn->prepare($interestsSql);
$interestsStmt->bind_param("s", $tutor_id);
$interestsStmt->execute();
$interestsResult = $interestsStmt->get_result();

// Fetch pending requests for the tutor
$pendingRequestsSql = "SELECT * FROM tutoring_requests WHERE tutor_id = ? AND status = 'pending'";
$pendingStmt = $conn->prepare($pendingRequestsSql);
$pendingStmt->bind_param("s", $tutor_id);
$pendingStmt->execute();
$pendingRequestsResult = $pendingStmt->get_result();

// Fetch accepted requests for the tutor
$acceptedRequestsSql = "SELECT * FROM tutoring_requests WHERE tutor_id = ? AND status = 'accepted'";
$acceptedStmt = $conn->prepare($acceptedRequestsSql);
$acceptedStmt->bind_param("s", $tutor_id);
$acceptedStmt->execute();
$acceptedRequestsResult = $acceptedStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutor Dashboard - NSU Sheba</title>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Arial', sans-serif;
            
        }
        body {
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            height: 100vh;
            padding: 20px;
        }
        .dashboard-container {
            width: 80%;
            max-width: 1200px;
            background-color: lightblue;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px;
            margin-top: 30px;
        }
        h1 {
            font-size: 2.5em;
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .card {
            background-color: #f9f9f9;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        .card h2 {
            font-size: 1.5em;
            color: #4CAF50;
            margin-bottom: 15px;
        }
        ul {
            list-style: none;
            padding: 0;
        }
        li {
            padding: 10px;
            margin-bottom: 10px;
            background-color: #e7f3ff;
            border-left: 5px solid #007bff;
            border-radius: 4px;
        }
        form {
            display: inline;
        }
        button {
            padding: 8px 15px;
            margin-left: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button.accept {
            background-color: #4CAF50;
            color: white;
        }
        button.reject {
            background-color: #f44336;
            color: white;
        }
        a {
            text-decoration: none;
            color: #007bff;
        }
        a:hover {
            text-decoration: underline;
        }
        .nav-links {
            margin-top: 20px;
            text-align: center;
        }
        .nav-links a {
            margin: 0 10px;
            padding: 8px 15px;
            background-color: #4CAF50;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        .nav-links a.back {
            background-color: #007bff;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <h1>Tutor Dashboard</h1>

        <!-- Interested Topics Card -->
        <div class="card">
            <h2>Your Interested Topics</h2>
            <ul>
                <?php if ($interestsResult->num_rows > 0): ?>
                    <?php while ($row = $interestsResult->fetch_assoc()): ?>
                        <li><?php echo htmlspecialchars($row['interest']); ?></li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No interested topics found.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Pending Requests Card -->
        <div class="card">
            <h2>Pending Requests</h2>
            <ul>
                <?php if ($pendingRequestsResult->num_rows > 0): ?>
                    <?php while ($row = $pendingRequestsResult->fetch_assoc()): ?>
                        <li>
                            Request from Student ID: <?php echo htmlspecialchars($row['student_id']); ?> for "<?php echo htmlspecialchars($row['interest']); ?>"
                            <form method="POST">
                                <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($row['request_id']); ?>">
                                <button type="submit" name="action" value="accept" class="accept">Accept</button>
                                <button type="submit" name="action" value="reject" class="reject">Reject</button>
                            </form>
                        </li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No pending requests.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Accepted Requests Card -->
        <div class="card">
            <h2>Accepted Requests</h2>
            <ul>
                <?php if ($acceptedRequestsResult->num_rows > 0): ?>
                    <?php while ($row = $acceptedRequestsResult->fetch_assoc()): ?>
                        <li>Request from Student ID: <?php echo htmlspecialchars($row['student_id']); ?> for "<?php echo htmlspecialchars($row['interest']); ?>"</li>
                    <?php endwhile; ?>
                <?php else: ?>
                    <li>No accepted requests.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Navigation Links -->
        <div class="nav-links">
            <a href="tutoring.php">Become Tutor</a>
            <a href="dashboard.php" class="back">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>

<?php
// Close the statements and connection
$interestsStmt->close();
$pendingStmt->close();
$acceptedStmt->close();
$conn->close();
?>
