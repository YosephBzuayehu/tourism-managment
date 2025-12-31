<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "tourism_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch attractions from the database
$sql = "SELECT * FROM attractions";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Content Detail View</title>
    <link rel="stylesheet" href="../Design-Content-Listing-Page/style.css">
</head>
<body>

<h1>Tourism Management System – Detailed View</h1>

<div class="container">
    <?php if ($result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card">
                <img src="<?php echo $row['image_url']; ?>" alt="<?php echo $row['name']; ?>">
                <div class="card-content">
                    <h3><?php echo $row['name']; ?></h3>
                    <p class="short"><?php echo $row['short_description']; ?></p>
                    <p class="full"><?php echo $row['full_description']; ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No attractions found.</p>
    <?php endif; ?>
</div>

<script src="../Design-Content-Listing-Page/script.js"></script>
</body>
</html>

<?php
$conn->close();
?>