
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once "db.php";

$sql = "SELECT id, title, description, category
        FROM tips
        ORDER BY id DESC";

$result = $conn->query($sql);

$tips = [];

while ($row = $result->fetch_assoc()) {
    $tips[] = $row;
}

echo json_encode([
    "success" => true,
    "count" => count($tips),
    "tips" => $tips
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

$conn->close();
?>
