<?php
require_once __DIR__ . '/db.php';

$items = [
    ['title' => 'New Tour: Blue Lake', 'body' => 'A scenic one-day tour to Blue Lake.'],
    ['title' => 'Guide Request: Mountain Trek', 'body' => 'Requesting approval for Mountain Trek guide page.'],
    ['title' => 'Event: Cultural Night', 'body' => 'Small local event listing.']
];

$stmt = $mysqli->prepare("INSERT INTO contents (title, body, status, created_by) VALUES (?, ?, 'pending', NULL)");
if (!$stmt) { die('Prepare failed: ' . $mysqli->error); }

foreach ($items as $it) {
    $stmt->bind_param('ss', $it['title'], $it['body']);
    $stmt->execute();
}

echo "Inserted sample data (" . count($items) . " rows).";

?>
