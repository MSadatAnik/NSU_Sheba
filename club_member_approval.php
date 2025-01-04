<?php
// Start the session
session_start();

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

// Ensure the user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); 
    exit();
}

$student_id = $_SESSION['student_id'];

// Check if the user is a president of any club
$sql = "SELECT club_name FROM club_members WHERE id = ? AND designation = 'president'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Access denied. Only presidents can view this page.");
}

$president_clubs = [];
while ($row = $result->fetch_assoc()) {
    $president_clubs[] = $row['club_name'];
}
$stmt->close();

// Handle approval or rejection based on form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'], $_POST['club_name'], $_POST['action'])) {
    $applicant_id = $_POST['id'];
    $club_name = $_POST['club_name'];
    $action = $_POST['action'];

    if ($action === "approve") {
        // Begin transaction
        $conn->begin_transaction();

        try {
            // Fetch designation from temporary_members
            $sql = "SELECT designation FROM temporary_members WHERE id = ? AND club_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $applicant_id, $club_name);
            $stmt->execute();
            $designation = $stmt->get_result()->fetch_assoc()['designation'];
            $stmt->close();

            // Insert into club_members
            $sql = "INSERT INTO club_members (id, club_name, designation) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sss", $applicant_id, $club_name, $designation);
            $stmt->execute();
            $stmt->close();

            // Remove from temporary_members
            $sql = "DELETE FROM temporary_members WHERE id = ? AND club_name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $applicant_id, $club_name);
            $stmt->execute();
            $stmt->close();

            // Commit transaction
            $conn->commit();
            echo "<script>alert('Membership approved successfully.');</script>";
        } catch (Exception $e) {
            // Rollback transaction if any error occurs
            $conn->rollback();
            echo "<script>alert('Error approving membership: " . $e->getMessage() . "');</script>";
        }
    } elseif ($action === "reject") {
        // Delete from temporary_members
        $sql = "DELETE FROM temporary_members WHERE id = ? AND club_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $applicant_id, $club_name);

        if ($stmt->execute()) {
            echo "<script>alert('Membership application rejected successfully.');</script>";
        } else {
            echo "<script>alert('Error rejecting membership: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }
}

// Fetch membership applications for the president's clubs
$placeholders = implode(',', array_fill(0, count($president_clubs), '?'));
$sql = "SELECT * FROM temporary_members WHERE club_name IN ($placeholders)";
$stmt = $conn->prepare($sql);
$stmt->bind_param(str_repeat('s', count($president_clubs)), ...$president_clubs);
$stmt->execute();
$applications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Membership Applications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #4a90e2;
            text-align: center;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #4a90e2;
            color: white;
        }
        button {
            background-color: #4a90e2;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background-color: #357ab7;
        }
        .return-button {
            background-color: #5cb85c;
            text-decoration: none;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            display: inline-block;
            margin: 20px auto;
            text-align: center;
        }
        .return-button:hover {
            background-color: #4cae4c;
        }
        .no-applications {
            text-align: center;
            margin: 20px;
            font-size: 1.2em;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Club Membership Applications</h1>

        <?php if ($applications->num_rows > 0): ?>
            <table>
                <tr>
                    <th>Student ID</th>
                    <th>Club Name</th>
                    <th>Designation</th>
                    <th>Action</th>
                </tr>
                <?php while ($application = $applications->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($application['id']); ?></td>
                        <td><?php echo htmlspecialchars($application['club_name']); ?></td>
                        <td><?php echo htmlspecialchars($application['designation']); ?></td>
                        <td>
                            <form action="" method="post" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($application['id']); ?>">
                                <input type="hidden" name="club_name" value="<?php echo htmlspecialchars($application['club_name']); ?>">
                                <button type="submit" name="action" value="approve">Approve</button>
                                <button type="submit" name="action" value="reject">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        <?php else: ?>
            <p class="no-applications">No membership applications found for your clubs.</p>
        <?php endif; ?>

        <?php
        $stmt->close();
        $conn->close();
        ?>

        <div style="text-align: center;">
            <a href="events_dashboard.php" class="return-button">Return to Events Dashboard</a>
        </div>
    </div>
</body>
</html>
