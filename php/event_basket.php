<?php
session_start();

// Remove a specific event
if (isset($_POST['remove_event'], $_POST['event_id'])) {
    unset($_SESSION['event_basket'][$_POST['event_id']]);
    header("Location: event_basket.php");
    exit;
}

// Clear entire basket
if (isset($_POST['clear_basket'])) {
    $_SESSION['event_basket'] = [];
    header("Location: event_basket.php");
    exit;
}

$event_basket = $_SESSION['event_basket'] ?? [];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Event Basket</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .basket-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .basket-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .basket-event {
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            background-color: #f7f7f7;
            position: relative;
        }
        .basket-event h3 {
            margin-top: 0;
        }
        .remove-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
        }
        .remove-btn:hover {
            background-color: #c82333;
        }
        .clear-btn {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            float: right;
        }
        .clear-btn:hover {
            background-color: #0056b3;
        }
        .total-price {
            text-align: right;
            font-size: 1.3rem;
            font-weight: bold;
            margin-top: 20px;
            clear: both;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="basket-container">
    <h1 class="basket-title">Your Event Basket</h1>

    <?php if (empty($event_basket)): ?>
        <p>You haven't added any events yet.</p>
    <?php else: ?>
        <form method="POST">
            <button type="submit" name="clear_basket" class="clear-btn">Clear Basket</button>
        </form>

        <?php 
        $total_price = 0;
        foreach ($event_basket as $event): 
            $price = is_numeric($event['price']) ? floatval($event['price']) : 0.00;
            $total_price += $price;
        ?>
            <div class="basket-event">
                <form method="POST" style="display:inline;">
                    <input type="hidden" name="event_id" value="<?= htmlspecialchars($event['event_id']) ?>">
                    <button type="submit" name="remove_event" class="remove-btn">Remove</button>
                </form>
                <h3><?= htmlspecialchars($event['title']) ?></h3>
                <p><strong>📍 Location:</strong> <?= htmlspecialchars($event['location']) ?></p>
                <p><strong>📅 Date:</strong> <?= date("d M Y, H:i", strtotime($event['start_time'])) ?></p>
                <p><strong>🏷 Category:</strong> <?= htmlspecialchars($event['category']) ?></p>
                <p><strong>💷 Price:</strong> <?= $price > 0 ? '£' . number_format($price, 2) : 'N/A' ?></p>
                <a href="<?= htmlspecialchars($event['event_url']) ?>" target="_blank">View Event</a>
            </div>
        <?php endforeach; ?>

        <div class="total-price">Total: £<?= number_format($total_price, 2) ?></div>
    <?php endif; ?>
</div>
</body>
</html>
