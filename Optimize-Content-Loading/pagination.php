<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'tourism';
$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination logic
$limit = 10; // Number of items per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$sql = "SELECT * FROM content LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<div class='content-item'>";
        echo "<h3>" . $row['title'] . "</h3>";
        echo "<p>" . $row['description'] . "</p>";
        echo "</div>";
    }
}

// Pagination links
$sql_total = "SELECT COUNT(*) AS total FROM content";
$result_total = $conn->query($sql_total);
$total_items = $result_total->fetch_assoc()['total'];
$total_pages = ceil($total_items / $limit);

for ($i = 1; $i <= $total_pages; $i++) {
    echo "<a href='pagination.php?page=$i'>$i</a> ";
}

$conn->close();
?>