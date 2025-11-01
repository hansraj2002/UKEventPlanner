<?php
session_start();
header('Content-Type: application/json');

// Check if the event basket exists and is not empty
if (!isset($_SESSION['event_basket']) || empty($_SESSION['event_basket'])) {
    echo json_encode([]);
    exit;
}

$categories = [];

// Loop through each event in the basket and collect categories
foreach ($_SESSION['event_basket'] as $event) {
    if (!empty($event['category'])) {
        $categories[] = $event['category'];
    }
}

// Return unique categories
$uniqueCategories = array_values(array_unique($categories));
echo json_encode($uniqueCategories);
