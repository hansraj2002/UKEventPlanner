<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $event_id = $_POST['event_id'];

    // Ensure $_SESSION['event_basket'] is an array before modifying it
    if (!isset($_SESSION['event_basket']) || !is_array($_SESSION['event_basket'])) {
        $_SESSION['event_basket'] = []; // Reset to an empty array to avoid errors
    }

    // Remove event from session basket safely
    $_SESSION['event_basket'] = array_values(array_filter($_SESSION['event_basket'], function ($event) use ($event_id) {
        return is_array($event) && isset($event['event_id']) && $event['event_id'] != $event_id;
    }));

    header("Location: event_basket.php");
    exit;
}
