<?php
session_start();
require 'config.php';

$step = $_GET['step'] ?? 1;
$category = $_GET['category'] ?? '';
$budget_min = $_GET['budget_min'] ?? '';
$budget_max = $_GET['budget_max'] ?? '';
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
$location = $_GET['location'] ?? '';
$description = $_GET['description'] ?? '';

$categories = [];
$categories_result = $conn->query("SELECT DISTINCT category FROM events");
while ($row = $categories_result->fetch_assoc()) {
    $categories[] = $row['category'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Event Recommendation</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .event {
            border: 1px solid #ccc;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            background-color: #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        .event-thumbnail {
            width: 100%;
            max-height: 180px;
            object-fit: cover;
        }

        .event-content {
            padding: 15px;
        }

        .event-content h2 {
            margin-top: 0;
            font-size: 1.3rem;
        }

        .event-content p {
            margin: 6px 0;
        }

        .event form {
            margin-top: 10px;
        }

        form label, form input, form select {
            display: block;
            margin-bottom: 10px;
        }

        form button {
            padding: 6px 12px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #0056b3;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .event {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            margin: 15px auto;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
        }
        .event h2 {
            margin-top: 10px;
            font-size: 20px;
            color: #333;
        }
        .event p {
            margin: 5px 0;
        }
        .event form {
            margin-top: 10px;
        }
        form label, form input, form select {
            display: block;
            margin-bottom: 10px;
        }
        form button {
            padding: 6px 12px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 5px;
            cursor: pointer;
        }
        form button:hover {
            background-color: #0056b3;
        }
        .thumbnail {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 960px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        h1, h2 {
            color: #333;
        }

        .event {
            display: flex;
            flex-direction: column;
            gap: 8px;
            border: 1px solid #ddd;
            border-left: 6px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background-color: #fdfdfd;
        }

        .event p {
            margin: 0;
            color: #444;
        }

        .event strong {
            color: #000;
        }

        form label {
            font-weight: bold;
        }

        form input[type="text"],
        form input[type="number"],
        form input[type="date"],
        form select {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            margin-bottom: 15px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }

        form button {
            padding: 10px 20px;
            border: none;
            background-color: #007bff;
            color: white;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
    <h1>Find Your Perfect Event</h1>

    <?php if ($step == 1): ?>
        <form method="GET" action="recommend.php">
            <input type="hidden" name="step" value="2">
            <label>Select Category:</label>
            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Next</button>
        </form>

    <?php elseif ($step == 2): ?>
        <form method="GET" action="recommend.php">
            <input type="hidden" name="step" value="3">
            <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">

            <label>Enter Budget Range:</label>
            <input type="number" name="budget_min" placeholder="Min Budget" value="<?= htmlspecialchars($budget_min) ?>">
            <input type="number" name="budget_max" placeholder="Max Budget" value="<?= htmlspecialchars($budget_max) ?>">

            <label>Select Start Date:</label>
            <input type="date" name="start_date" value="<?= htmlspecialchars($start_date) ?>">

            <label>Select End Date:</label>
            <input type="date" name="end_date" value="<?= htmlspecialchars($end_date) ?>">

            <label>Enter Location:</label>
            <input type="text" name="location" placeholder="City or Venue" value="<?= htmlspecialchars($location) ?>">

            <label>Keyword in Description:</label>
            <input type="text" name="description" placeholder="Keyword" value="<?= htmlspecialchars($description) ?>">

            <button type="submit">Find Events</button>
        </form>

    <?php elseif ($step == 3): ?>
        <?php
        $where = "1=1";
        $params = [];
        $types = "";

        if (!empty($category)) {
            $where .= " AND category = ?";
            $params[] = $category;
            $types .= "s";
        }
        if (!empty($budget_min)) {
            $where .= " AND price >= ?";
            $params[] = floatval($budget_min);
            $types .= "d";
        }
        if (!empty($budget_max)) {
            $where .= " AND price <= ?";
            $params[] = floatval($budget_max);
            $types .= "d";
        }
        if (!empty($start_date)) {
            $where .= " AND start_time >= ?";
            $params[] = $start_date . " 00:00:00";
            $types .= "s";
        }
        if (!empty($end_date)) {
            $where .= " AND start_time <= ?";
            $params[] = $end_date . " 23:59:59";
            $types .= "s";
        }
        if (!empty($location)) {
            $where .= " AND location LIKE ?";
            $params[] = "%" . $location . "%";
            $types .= "s";
        }
        if (!empty($description)) {
            $where .= " AND description LIKE ?";
            $params[] = "%" . $description . "%";
            $types .= "s";
        }

        $sql = "SELECT * FROM events WHERE $where ORDER BY start_time ASC LIMIT 10";
        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        ?>

        <h2>Recommended Events</h2>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $category_key = strtolower(str_replace(' ', '-', $row['category']));
                $image_path = "../images/default-$category_key.jpg";
                if (!file_exists($image_path)) {
                    $image_path = "../images/default-other.jpg";
                }
                $price_display = isset($row['price']) && $row['price'] !== '' ? "£" . htmlspecialchars($row['price']) : "N/A";
                ?>
                <div class="event">
                    <img src="<?= $image_path ?>" alt="Event thumbnail" class="event-thumbnail">
                    <div class="event-content">
                        <h2><?= htmlspecialchars($row['title']) ?></h2>
                        <p><strong>📍 Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
                        <p><strong>📅 Date:</strong> <?= date("d M Y, H:i", strtotime($row['start_time'])) ?></p>
                        <p><strong>💰 Price:</strong> <?= $price_display ?></p>
                        <p><strong>📂 Category:</strong> <?= htmlspecialchars($row['category']) ?></p>
                        <p><?= htmlspecialchars($row['description']) ?></p>
                        <p><a href="<?= htmlspecialchars($row['event_url'] ?? '#') ?>" target="_blank">🔗 View Event Details</a></p>

                        <form method="POST" action="add_to_basket.php">
                            <input type="hidden" name="event_id" value="<?= htmlspecialchars($row['id']) ?>">
                            <input type="hidden" name="title" value="<?= htmlspecialchars($row['title']) ?>">
                            <input type="hidden" name="location" value="<?= htmlspecialchars($row['location']) ?>">
                            <input type="hidden" name="start_time" value="<?= htmlspecialchars($row['start_time']) ?>">
                            <input type="hidden" name="event_url" value="<?= htmlspecialchars($row['event_url']) ?>">
                            <input type="hidden" name="category" value="<?= htmlspecialchars($row['category']) ?>">
                            <input type="hidden" name="price" value="<?= htmlspecialchars($row['price']) ?>">
                            <button type="submit">Add to Basket</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No events found matching your criteria.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>
