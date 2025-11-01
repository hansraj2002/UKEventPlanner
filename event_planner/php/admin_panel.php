<?php
session_start();
include __DIR__ . '/config.php';

// Handle event update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_event'])) {
        $id = $_POST['id'];
        $title = $_POST['title'];
        $location = $_POST['location'];
        $category = $_POST['category'];
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        $event_url = $_POST['event_url'];
        $price = $_POST['price'];

        $stmt = $conn->prepare("UPDATE events SET title=?, location=?, category=?, start_time=?, end_time=?, event_url=?, price=? WHERE id=?");
        $stmt->bind_param("ssssssdi", $title, $location, $category, $start_time, $end_time, $event_url, $price, $id);
        $message = $stmt->execute() ? "Event updated successfully." : "Error updating event: " . $stmt->error;
        $stmt->close();
    }

    // Handle delete
    if (isset($_POST['delete_event'])) {
        $id = $_POST['id'];
        $stmt = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt->bind_param("i", $id);
        $message = $stmt->execute() ? "Event deleted successfully." : "Error deleting event: " . $stmt->error;
        $stmt->close();
    }
}

// Handle filters
$search = $_GET['search'] ?? '';
$filter_location = $_GET['location'] ?? '';
$filter_date = $_GET['start_time'] ?? '';

// Pagination setup
$limit = 10;
$page = $_GET['page'] ?? 1;
$page = max(1, (int)$page);
$offset = ($page - 1) * $limit;

$where = [];
$params = [];
$types = '';

// Search and filter conditions
if ($search) {
    $where[] = "(title LIKE ? OR location LIKE ? OR category LIKE ?)";
    $params[] = $params[] = $params[] = '%' . $search . '%';
    $types .= 'sss';
}
if ($filter_location) {
    $where[] = "location LIKE ?";
    $params[] = '%' . $filter_location . '%';
    $types .= 's';
}
if ($filter_date) {
    $where[] = "DATE(start_time) = ?";
    $params[] = $filter_date;
    $types .= 's';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// Get total events count
$count_sql = "SELECT COUNT(*) AS total FROM events $whereSQL";
$count_stmt = $conn->prepare($count_sql);
if ($types) $count_stmt->bind_param($types, ...$params);
$count_stmt->execute();
$total = $count_stmt->get_result()->fetch_assoc()['total'] ?? 0;
$count_stmt->close();
$total_pages = ceil($total / $limit);

// Fetch events
$sql = "SELECT * FROM events $whereSQL ORDER BY id DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - Manage Events</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        h1 { text-align: center; }

        .search-form {
            text-align: center;
            margin-bottom: 20px;
        }
        .search-form input, .search-form button {
            padding: 8px;
            margin: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
        }
        input[type="text"], input[type="datetime-local"], input[type="url"], input[type="number"] {
            width: 100%;
        }
        .message {
            text-align: center;
            color: green;
            font-weight: bold;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            padding: 8px 12px;
            margin: 0 4px;
            background: #eee;
            text-decoration: none;
            border-radius: 4px;
        }
        .pagination a.active {
            background: #007bff;
            color: white;
        }
    </style>
</head>
<body>

<h1>Admin Panel - Manage Events</h1>

<?php if (isset($message)) echo "<p class='message'>$message</p>"; ?>

<div class="search-form">
    <form method="GET">
        <input type="text" name="search" placeholder="Search title, location, category" value="<?= htmlspecialchars($search) ?>">
        <input type="text" name="location" placeholder="Filter by location" value="<?= htmlspecialchars($filter_location) ?>">
        <input type="date" name="start_time" value="<?= htmlspecialchars($filter_date) ?>">
        <button type="submit">Filter</button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Location</th>
            <th>Category</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Event URL</th>
            <th>Price (£)</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <form method="POST">
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><input type="text" name="title" value="<?= htmlspecialchars($row['title']) ?>"></td>
                    <td><input type="text" name="location" value="<?= htmlspecialchars($row['location']) ?>"></td>
                    <td><input type="text" name="category" value="<?= htmlspecialchars($row['category']) ?>"></td>
                    <td><input type="datetime-local" name="start_time" value="<?= date('Y-m-d\TH:i', strtotime($row['start_time'])) ?>"></td>
                    <td><input type="datetime-local" name="end_time" value="<?= date('Y-m-d\TH:i', strtotime($row['end_time'])) ?>"></td>
                    <td><input type="url" name="event_url" value="<?= htmlspecialchars($row['event_url']) ?>"></td>
                    <td><input type="number" step="0.01" name="price" value="<?= htmlspecialchars($row['price']) ?>"></td>
                    <td>
                        <input type="hidden" name="id" value="<?= $row['id'] ?>">
                        <button type="submit" name="update_event">Update</button>
                        <button type="submit" name="delete_event" onclick="return confirm('Are you sure?')">Delete</button>
                    </td>
                </form>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<div class="pagination">
    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
        <a href="?search=<?= urlencode($search) ?>&location=<?= urlencode($filter_location) ?>&start_time=<?= urlencode($filter_date) ?>&page=<?= $p ?>" class="<?= $p == $page ? 'active' : '' ?>"><?= $p ?></a>
    <?php endfor; ?>
</div>

</body>
</html>
