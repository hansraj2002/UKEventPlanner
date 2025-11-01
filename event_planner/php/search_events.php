<?php
require_once 'config.php';
header('Content-Type: application/json');

$q = $_GET['q'] ?? '';
if (!$q) {
    echo json_encode([]);
    exit;
}

// ✅ Make sure these two columns are selected: event_url and price
$sql = "SELECT event_id, title, location, start_time, category, description, event_url, price 
        FROM events 
        WHERE title LIKE ? OR description LIKE ? OR location LIKE ? OR category LIKE ? 
        ORDER BY start_time ASC 
        LIMIT 20";

$stmt = $conn->prepare($sql);
$search = "%$q%";
$stmt->bind_param("ssss", $search, $search, $search, $search);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    // Optional: Fallback to empty string for missing price/url
    $row['price'] = $row['price'] ?? '';
    $row['event_url'] = $row['event_url'] ?? '';
    $events[] = $row;
}

echo json_encode($events);
$conn->close();
?>
