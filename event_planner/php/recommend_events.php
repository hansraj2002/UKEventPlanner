<?php

require '../vendor/autoload.php';

use Algolia\AlgoliaSearch\SearchClient;

// Initialize Algolia Client
$client = SearchClient::create('OBC54GU5RH', '56b476462484d34ca9c63183fcb3f2cb');
$index = $client->initIndex('events');

// Get a random event from the database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=event_planner_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $query = $pdo->query("SELECT * FROM events ORDER BY RAND() LIMIT 1");
    $randomEvent = $query->fetch(PDO::FETCH_ASSOC);

    if (!$randomEvent) {
        die(json_encode([])); // No events in database
    }

    // Search for similar events in Algolia (same category)
    $searchResults = $index->search('', [
        'filters' => "category:'{$randomEvent['category']}'",  // Filter by category of the random event
        'hitsPerPage' => 5  // Limit to 5 recommendations
    ]);

    // Return the results as JSON
    echo json_encode($searchResults['hits']);
} catch (Exception $e) {
    // Handle errors and provide a meaningful message
    die(json_encode(['error' => $e->getMessage()]));
}
?>
