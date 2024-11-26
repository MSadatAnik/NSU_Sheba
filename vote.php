<?php
session_start();
include 'db_connection.php'; // Include your database connection file

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    die('You must be logged in to access this page.');
}

// Handle agenda creation
if (isset($_POST['create_agenda'])) {
    $student_id = $_SESSION['student_id']; // Logged-in user ID
    $title = $_POST['title'];
    $description = $_POST['description'];
    $expire_at = $_POST['expire_at']; // Time limit set by the creator

    // Use prepared statements to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO agendas (student_id, title, description, expire_at) 
                            VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $student_id, $title, $description, $expire_at);
    
    if ($stmt->execute()) {
        echo "<p>Agenda created successfully!</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Handle voting
if (isset($_POST['vote'])) {
    $agenda_id = $_POST['agenda_id'];
    $voter_id = $_SESSION['student_id']; // Logged-in user ID
    $vote_type = $_POST['vote_type']; // 'for' or 'against'

    // Check if the user has already voted
    $check_vote = $conn->prepare("SELECT * FROM votes WHERE agenda_id = ? AND student_id = ?");
    $check_vote->bind_param("is", $agenda_id, $voter_id);
    $check_vote->execute();
    $result = $check_vote->get_result();

    if ($result->num_rows == 0) {
        // User hasn't voted, proceed with recording the vote
        $stmt = $conn->prepare("INSERT INTO votes (agenda_id, student_id, vote_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $agenda_id, $voter_id, $vote_type);

        if ($stmt->execute()) {
            // Update the vote count in the agendas table
            if ($vote_type == 'for') {
                $update_vote = $conn->prepare("UPDATE agendas SET vote_for = vote_for + 1 WHERE agenda_id = ?");
            } else {
                $update_vote = $conn->prepare("UPDATE agendas SET vote_against = vote_against + 1 WHERE agenda_id = ?");
            }
            $update_vote->bind_param("i", $agenda_id);
            $update_vote->execute();
            echo "<p>Vote recorded successfully!</p>";
        } else {
            echo "<p>Error: " . $stmt->error . "</p>";
        }
        $stmt->close();
    } else {
        echo "<p>You have already voted on this agenda.</p>";
    }
    $check_vote->close();
}

// Fetch agendas that are still active (expire_at > NOW()) or have at least 5 votes in favor
$agendas_query = "SELECT * FROM agendas WHERE expire_at > NOW() OR vote_for >= 5";
$agendas = $conn->query($agendas_query);

// Check for query errors
if (!$agendas) {
    die("Database query failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f0f4f8;
            color: #333;
            padding-top: 80px; /* Adjust for fixed navbar */
        }

        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 40px;
            background-color: #3a6186;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        nav .logo img {
            width: 50px;
            height: auto;
        }

        nav ul {
            display: flex;
            list-style-type: none;
        }

        nav ul li {
            margin: 0 15px;
        }

        nav ul li a {
            color: #fff;
            text-decoration: none;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        nav ul li a:hover {
            color: #ff4757;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 60px;
        }

        h1 {
            font-size: 36px;
            color: #3a6186;
            text-align: center;
            margin-bottom: 30px;
        }

        h2 {
            font-size: 28px;
            color: #3a6186;
            margin-bottom: 20px;
            text-align: center;
        }

        .agenda {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        .agenda h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .agenda p {
            font-size: 18px;
            color: #555;
        }

        form input[type="text"], form textarea, form input[type="datetime-local"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f0f4f8;
        }

        .btn {
            background-color: #3a6186;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-right: 10px;
        }

        .btn:hover {
            background-color: #2c4d66;
        }

        hr {
            border: 1px solid #e0e0e0;
            margin: 40px 0;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }

            nav {
                padding: 10px 20px;
            }

            nav ul li {
                margin: 0 10px;
            }

            h1 {
                font-size: 28px;
            }

            h2 {
                font-size: 24px;
            }

            form input[type="text"], form textarea, form input[type="datetime-local"] {
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="logo"><img src="nsu-logo.png" alt="Logo"></div> <!-- Adjust the logo source -->
        <ul>
            <li><a href="dashboard.php">Home</a></li>
            <li><a href="#">Profile</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>NSU Sheba Voting System</h1>

        <!-- Agenda Creation Form -->
        <h2>Create a New Agenda</h2>
        <form action="" method="POST">
            <input type="text" name="title" placeholder="Agenda Title" required><br><br>
            <textarea name="description" placeholder="Agenda Description" required></textarea><br><br>
            <label for="expire_at">Set Time Limit:</label>
            <input type="datetime-local" name="expire_at" required><br><br>
            <button type="submit" name="create_agenda" class="btn">Create Agenda</button>
        </form>

        <hr>

        <!-- Display Active Agendas -->
        <h2>Active Agendas</h2>
        <?php while ($agenda = $agendas->fetch_assoc()) : ?>
            <div class="agenda">
                <h3><?php echo htmlspecialchars($agenda['title']); ?></h3>
                <p><?php echo htmlspecialchars($agenda['description']); ?></p>
                <p>Total Votes in Favor: <?php echo htmlspecialchars($agenda['vote_for']); ?> | Total Votes Against: <?php echo htmlspecialchars($agenda['vote_against']); ?></p>
                <p>Expires At: <?php echo htmlspecialchars($agenda['expire_at']); ?></p>

                <!-- Voting Form -->
                <form action="" method="POST">
                    <input type="hidden" name="agenda_id" value="<?php echo htmlspecialchars($agenda['agenda_id']); ?>">
                    <input type="hidden" name="vote" value="true">
                    <button type="submit" name="vote_type" value="for" class="btn">Vote For</button>
                    <button type="submit" name="vote_type" value="against" class="btn">Vote Against</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</body>
</html>

<?php
// Automatically expire agendas based on minimum votes
$required_votes = 5; // Set the minimum votes required for an agenda
$now = date('Y-m-d H:i:s');
$expire_agendas = $conn->prepare("UPDATE agendas SET expire_at = NOW() WHERE expire_at < ? AND vote_for < ?");
$expire_agendas->bind_param("si", $now, $required_votes);
$expire_agendas->execute();
$expire_agendas->close();

$conn->close(); // Close the database connection at the end
?>
