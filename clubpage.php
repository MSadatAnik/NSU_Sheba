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

// Fetch clubs that are actively recruiting
$clubs_sql = "SELECT club_name FROM club WHERE status = 'accepted'";
$clubs_result = $conn->query($clubs_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>University Club Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center; 
        }

        h1 {
            color: #333;
            text-align: center; 
            margin-bottom: 20px; 
        }

        .section {
            margin-bottom: 30px;
        }

        .club {
            margin: 20px auto;     
            padding: 10px;
            width: 60%;            
            border-radius: 4px;
            background-color: #e8f0fe;
            text-align: center;
        }

        .club h3 {
            margin: 0 0 10px;
            color: #333;
        }

        .join-button {
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: inline-block; 
        }

        .join-button:hover {
            background-color: #0056b3;
        }

        .return-button {
            width: 200px;
            text-align: center;
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
            display: inline-block; 
        }

        .return-button:hover {
            background-color: #218838;
        }

        .no-clubs-message {
            color: #555;
        }
    </style>
    <script>
        function joinClub(clubName) {
            // Redirect to the registration page with the club name as a query parameter
            window.location.href = 'club_member_request.php?club_name=' + encodeURIComponent(clubName);
        }
    </script>
</head>
<body>
    <h1>University Club Page</h1>
    <div class="container">
        <div class="section">
            <h2>Club Recruitment</h2>
            <p>Join one of our exciting clubs! Here are the clubs currently recruiting:</p>

            <?php if ($clubs_result->num_rows > 0): ?>
                <?php while ($club = $clubs_result->fetch_assoc()): ?>
                    <div class="club">
                        <h3><?php echo htmlspecialchars($club['club_name']); ?></h3>
                        <button class="join-button" onclick="joinClub('<?php echo htmlspecialchars($club['club_name']); ?>')">
                            Join <?php echo htmlspecialchars($club['club_name']); ?>
                        </button>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="no-clubs-message">No clubs currently recruiting.</p>
            <?php endif; ?>
        </div>

        <a href="events_dashboard.php" class="return-button">Return to Dashboard</a>
    </div>

    <?php
    $conn->close(); 
    ?>
</body>
</html>
