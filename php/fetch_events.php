<?php
ini_set('max_execution_time', 1000); // Sets the execution time limit to 5 minutes

require 'config.php';

$api_key = "xAoVH6KgBoGe1r3CZbZAD8FAXmZPhXqzh49_pGnL";
$major_cities = [
    "London", "Manchester", "Birmingham", "Liverpool", "Leeds", "Glasgow", "Edinburgh", "Bristol", "Cardiff", "Belfast"
];
$today = date('Y-m-d');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Define a mapping for category short names to full category names
$category_full_names = [
    "academic" => "Academic",
    "airport-delays" => "Airport Delays",
    "community" => "Community",
    "concerts" => "Concerts",
    "conferences" => "Conferences",
    "daylight-savings" => "Daylight Savings",
    "disasters" => "Disasters",
    "expos" => "Expos",
    "festivals" => "Festivals",
    "health-warnings" => "Health Warnings",
    "observances" => "Observances",
    "performing-arts" => "Performing Arts",
    "politics" => "Politics",
    "public-holidays" => "Public Holidays",
    "school-holidays" => "School Holidays",
    "severe-weather" => "Severe Weather",
    "sports" => "Sports",
    "terror" => "Terror"
];


foreach ($major_cities as $city) {
    $city_encoded = urlencode($city);
    foreach (array_keys($category_full_names) as $category) {
        $category_encoded = urlencode($category);
        $endpoint = "https://api.predicthq.com/v1/events/?country=GB&city=$city_encoded&category=$category_encoded&start.gte=$today&limit=1000";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $api_key",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);
        if(curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }
        curl_close($ch);

        // Log the full response for debugging purposes
        error_log("Response for category $category: " . print_r($response, true));

        $events = json_decode($response, true);


        if (!empty($events['results'])) {
            echo "Found " . count($events['results']) . " events in $city - $category<br>";

            foreach ($events['results'] as $event) {
                $event_id = $conn->real_escape_string($event['id']);
                $title = $conn->real_escape_string($event['title']);
                $description = isset($event['description']) ? $conn->real_escape_string($event['description']) : "No description";
                $start_time = date("Y-m-d H:i:s", strtotime($event['start']));
                $end_time = isset($event['end']) ? date("Y-m-d H:i:s", strtotime($event['end'])) : null;
                $location = isset($event['location']) ? implode(", ", $event['location']) : "Location not specified";
                $latitude = $event['location'][1] ?? null;
                $longitude = $event['location'][0] ?? null;
                $rank = $event['rank'] ?? null;
                
                // Check if category is being correctly assigned
                $event_category = $event['category'] ?? 'Unknown';
                error_log("Category data for event '$title': " . print_r($event_category, true));

                // Assuming $event['category'] could be an array, we should process it accordingly.
                $category_code = is_array($event_category) ? $event_category[0] : $event_category;

                if (array_key_exists($category_code, $category_full_names)) {
                    $category = $category_full_names[$category_code];
                } else {
                    $category = "Uncategorized";
                    // Log an error if category is not found in the mapping
                    error_log("Category not found for event: " . $title . " (Category code: $category_code)");
                }

                $link = $event['entities'][0]['url'] ?? null;
                $event_url = $link ? $conn->real_escape_string($link) : "#";

                // Insert event into database using full category names to avoid any duplication based on initials
                $sql = "INSERT INTO events (event_id, title, category, description, start_time, end_time, location, latitude, longitude, rank, link, event_url)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE title=?, category=?, description=?, start_time=?, end_time=?, location=?, latitude=?, longitude=?, rank=?, link=?, event_url=?";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssssddissssssssddisss",
                    $event_id, $title, $category, $description, $start_time, $end_time, $location, $latitude, $longitude, $rank, $link, $event_url,
                    $title, $category, $description, $start_time, $end_time, $location, $latitude, $longitude, $rank, $link, $event_url
                );

                $stmt->execute();

                if ($stmt->error) {
                    echo "Error inserting event: " . $stmt->error . "<br>";
                }
            }
        } else {
            // If no events are found for the category, log the response
            error_log("No events found for category $category in city $city");
        }
    }
}

echo "✅ Events imported successfully!";
$conn->close();
?>
