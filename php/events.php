<?php
session_start();
require 'config.php';

// Fetch categories with event count
$category_sql = "SELECT category, COUNT(*) as count FROM events GROUP BY category ORDER BY category ASC";
$category_result = $conn->query($category_sql);
$category_counts = [];
while ($row = $category_result->fetch_assoc()) {
    $category_counts[$row['category']] = $row['count'];
}

// Filter logic
$selected_category = $_GET['category'] ?? '';
$where_clause = '';
$params = [];
$types = '';

if ($selected_category && $selected_category !== 'All') {
    $where_clause = "WHERE category = ?";
    $params[] = $selected_category;
    $types .= 's';
}

// Pagination logic
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Count total filtered events
$count_sql = "SELECT COUNT(*) as total FROM events $where_clause";
$count_stmt = $conn->prepare($count_sql);
if ($where_clause) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_events = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_events / $limit);

// Fetch filtered + paginated events
$sql = "SELECT * FROM events $where_clause ORDER BY start_time ASC LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= 'ii';
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Events by Category</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { font-family: Arial, sans-serif; background: #f0f2f5; margin: 0; padding: 0; }
        .container { max-width: 1100px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: white; background: dodgerblue; padding: 20px; border-radius: 10px 10px 0 0; margin-top: 0; }
        .categories { display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; margin: 20px 0; }
        .categories button { background: #0084ff; color: white; padding: 12px 20px; border: none; border-radius: 10px; font-weight: bold; cursor: pointer; }
        .categories button.active { background: #004a99; }
        .event { border: 1px solid #ddd; border-left: 6px solid #007bff; border-radius: 8px; padding: 20px; margin-bottom: 20px; background-color: #fdfdfd; }
        .event-thumbnail { width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; margin-bottom: 10px; }
        .pagination { text-align: center; margin-top: 20px; }
        .pagination a { margin: 0 5px; padding: 8px 12px; border: 1px solid #007bff; border-radius: 5px; text-decoration: none; color: #007bff; }
        .pagination a.active { background: #007bff; color: white; }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container">
    <h1>Events by Category</h1>

    <div class="categories">
        <button class="category-btn <?= ($selected_category == '' || $selected_category == 'All') ? 'active' : '' ?>" data-category="All">All (<?= array_sum($category_counts) ?>)</button>
        <?php foreach ($category_counts as $cat => $count): ?>
            <button class="category-btn <?= ($selected_category == $cat) ? 'active' : '' ?>" data-category="<?= htmlspecialchars($cat) ?>">
                <?= htmlspecialchars($cat) ?> (<?= $count ?>)
            </button>
        <?php endforeach; ?>
    </div>

    <div id="events-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                $category_key = strtolower(str_replace(' ', '-', $row['category']));
                $image_path = "../images/default-$category_key.jpg";
                if (!file_exists($image_path)) $image_path = "../images/default-other.jpg";
                $price_display = isset($row['price']) && $row['price'] !== '' ? "£" . htmlspecialchars($row['price']) : "N/A";
                ?>
                <div class="event" data-category="<?= htmlspecialchars($row['category']) ?>">
                    <img src="<?= $image_path ?>" class="event-thumbnail" alt="Event thumbnail">
                    <h2><?= htmlspecialchars($row['title']) ?></h2>
                    <p><strong>📍 Location:</strong> <?= htmlspecialchars($row['location']) ?></p>
                    <p><strong>🗓 Date:</strong> <?= date("d M Y, H:i", strtotime($row['start_time'])) ?></p>
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
            <?php endwhile; ?>
        <?php else: ?>
            <p>No events found for this category.</p>
        <?php endif; ?>
    </div>

    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="?category=<?= urlencode($selected_category) ?>&page=<?= $i ?>" class="<?= ($i == $page) ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>

<script>
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const selected = btn.getAttribute('data-category');
            window.location.href = '?category=' + encodeURIComponent(selected);
        });
    });
</script>

</body>
</html>
