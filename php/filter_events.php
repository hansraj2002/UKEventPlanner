<?php
require 'config.php';

$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

$sql = "SELECT * FROM events WHERE title LIKE '%$query%' ORDER BY start_time ASC";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    echo "<p><b>{$row['title']}</b> - {$row['category']} at {$row['location']}. 
          <a href='https://www.eventbrite.com/e/{$row['link']}' target='_blank'>View</a></p>";
}
$conn->close();
?>
