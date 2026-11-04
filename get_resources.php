<?php
ini_set("display_errors", "1");
error_reporting(E_ALL);

header("Content-Type: application/json; charset=UTF-8");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    require_once "db.php";

    $sql = "SELECT id, title, category, description, image_url
            FROM resources
            ORDER BY created_at DESC";

    $result = $conn->query($sql);
    $resources = [];

    while ($row = $result->fetch_assoc()) {
        $resources[] = $row;
    }

    echo json_encode([
        "success" => true,
        "count" => count($resources),
        "resources" => $resources
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    $conn->close();

} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
