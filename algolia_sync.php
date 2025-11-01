<?php

error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

require 'vendor/autoload.php';

use Algolia\AlgoliaSearch\SearchClient;

// ✅ Replace with your real Algolia Admin API Key
$client = SearchClient::create('OBC54GU5RH', 'b81d3bb8afb878494e2dade0ea0825e4');
$index = $client->initIndex('events');

// 🔌 Connect to MySQL database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=event_planner_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// 📦 Fetch events from MySQL
try {
    $query = $pdo->query("SELECT event_id, title, location, start_time, category, description, event_url FROM events");
    $events = $query->fetchAll(PDO::FETCH_ASSOC);

    if (empty($events)) {
        die("⚠️ No events found in the database.");
    }

    // 🛠 Add objectID for Algolia
    foreach ($events as &$event) {
        $event['objectID'] = $event['event_id'];
    }

    // 🚀 Push to Algolia
    $index->saveObjects($events);

    echo "✅ Events synced to Algolia successfully! Synced: " . count($events);
} catch (Exception $e) {
    die("❌ Error syncing to Algolia: " . $e->getMessage());
}
