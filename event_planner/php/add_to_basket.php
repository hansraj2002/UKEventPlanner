<?php
session_start();

// Ensure event basket exists
if (!isset($_SESSION['event_basket'])) {
    $_SESSION['event_basket'] = [];
}

// Extract data
$event = [
    'event_id'   => $_POST['event_id'] ?? '',
    'title'      => $_POST['title'] ?? '',
    'location'   => $_POST['location'] ?? '',
    'start_time' => $_POST['start_time'] ?? '',
    'category'   => $_POST['category'] ?? '',
    'description'=> $_POST['description'] ?? '',
    'event_url'  => $_POST['event_url'] ?? '',
    'price'      => $_POST['price'] ?? ''
];

// Add or update event in session
$_SESSION['event_basket'][$event['event_id']] = $event;

header("Location: ../php/event_basket.php");
exit;
