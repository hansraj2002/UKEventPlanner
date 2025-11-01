<?php
require 'config.php'; // DB connection
$apiKey = 'faef519d538a4f8ab61e7b4d7141603c';

function getAddressFromCoordinates($lat, $lng, $apiKey) {
    $url = "https://api.opencagedata.com/geocode/v1/json?q=$lat+$lng&key=$apiKey";
    $response = file_get_contents($url);
    if ($response === false) return null;

    $data = json_decode($response, true);
    return $data['results'][0]['formatted'] ?? null;
}

// Only select events where location is not a proper address
$sql = "SELECT event_id, latitude, longitude, location 
        FROM events 
        WHERE latitude IS NOT NULL 
          AND longitude IS NOT NULL 
          AND (
              location IS NULL OR 
              location = '' OR 
              location = 'Location not specified' OR 
              location NOT LIKE '%,%'
          )";

$result = $conn->query($sql);
echo "Found " . $result->num_rows . " events to update.<br>";

while ($row = $result->fetch_assoc()) {
    $eventId = $row['event_id'];
    $lat = $row['latitude'];
    $lng = $row['longitude'];
    $currentLocation = $row['location'];

    echo "Processing event ID $eventId (current location: $currentLocation)<br>";

    $address = getAddressFromCoordinates($lat, $lng, $apiKey);
    if ($address) {
        $update = $conn->prepare("UPDATE events SET location = ? WHERE event_id = ?");
        $update->bind_param("ss", $address, $eventId);
        $update->execute();
        echo "✅ Updated event ID $eventId with address: $address<br>";
    } else {
        echo "❌ Failed to get address for event ID $eventId<br>";
    }
}

$conn->close();
?>
