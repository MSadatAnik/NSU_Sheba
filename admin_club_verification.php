<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nsu_sheba";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); // Redirect to login page if not logged in
    exit();
}

// Handle acceptance or rejection of a club request
if (isset($_GET['action']) && isset($_GET['club_name'])) {
    $action = $_GET['action'];
    $club_name = $_GET['club_name'];

    if ($action == 'accept') {
        $status = 'accepted';
        
        // Get the opener's ID for the accepted club
        $sql = "SELECT id FROM club WHERE club_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $club_name);
        $stmt->execute();
        $stmt->bind_result($opener_id);
        $stmt->fetch();
        $stmt->close();

        // Update the club status
        $sql = "UPDATE club SET status = ? WHERE club_name = ?";
        $stmt = $conn->prepare($sql); // Prepare a new statement
        $stmt->bind_param("ss", $status, $club_name);
        $stmt->execute();
        $stmt->close();

        // Add the opener as a member with designation 'president'
        $sql = "INSERT INTO club_members (id, club_name, designation) VALUES (?, ?, 'president')";
        $stmt = $conn->prepare($sql); // Prepare a new statement
        $stmt->bind_param("ss", $opener_id, $club_name);
        $stmt->execute();
        $stmt->close();

    } elseif ($action == 'reject') {
        $status = 'rejected';
        // Update the club status
        $sql = "UPDATE club SET status = ? WHERE club_name = ?";
        $stmt = $conn->prepare($sql); // Prepare a new statement
        $stmt->bind_param("ss", $status, $club_name);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch requested clubs
$sql = "SELECT id, club_name, status FROM club WHERE status = 'pending'";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requested Clubs</title>
</head>
<body>
    <h1>Requested Clubs</h1>

    <?php if ($result->num_rows > 0): ?>
        <table border="1">
            <tr>
                <th>ID</th>
                <th>Club Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo $row['club_name']; ?></td>
                    <td><?php echo $row['status']; ?></td>
                    <td>
                        <a href="?action=accept&club_name=<?php echo urlencode($row['club_name']); ?>">Accept</a>
                        <a href="?action=reject&club_name=<?php echo urlencode($row['club_name']); ?>">Reject</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No pending club requests found.</p>
    <?php endif; ?>

    <?php
    $conn->close();
    ?>
</body>
</html>
