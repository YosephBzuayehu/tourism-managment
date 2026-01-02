<?php
require_once __DIR__ . '/db.php';

$res = $mysqli->query("SELECT id, title, body, status, created_at FROM contents ORDER BY created_at DESC");
if (!$res) { die('Query failed: ' . $mysqli->error); }

?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Admin - Content Approval</title>
  <style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    .item { border: 1px solid #ddd; padding: 12px; margin-bottom: 10px; }
    .meta { color: #666; font-size: 0.9em; }
    .actions { margin-top: 8px; }
    .btn { padding: 6px 10px; text-decoration: none; border-radius: 4px; }
    .approve { background:#2d9a3f; color:#fff; }
    .reject { background:#d9534f; color:#fff; }
  </style>
</head>
<body>
  <h1>Content Approval</h1>
  <p>Approve or reject content. (This example has no authentication.)</p>

  <?php while ($row = $res->fetch_assoc()): ?>
    <div class="item">
      <strong><?php echo htmlspecialchars($row['title']); ?></strong>
      <div class="meta">ID: <?php echo $row['id']; ?> — Status: <?php echo $row['status']; ?> — <?php echo $row['created_at']; ?></div>
      <p><?php echo nl2br(htmlspecialchars($row['body'])); ?></p>
      <div class="actions">
        <form style="display:inline" method="post" action="action.php">
          <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
          <input type="hidden" name="action" value="approve">
          <button class="btn approve" type="submit">Approve</button>
        </form>

        <form style="display:inline" method="post" action="action.php" onsubmit="return confirm('Reject this item?');">
          <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
          <input type="hidden" name="action" value="reject">
          <button class="btn reject" type="submit">Reject</button>
        </form>
      </div>
    </div>
  <?php endwhile; ?>

</body>
</html>
