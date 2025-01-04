<?php
global $cnx;
require_once '../configs/config.php';
include('../include/links.inc.php');
include('../include/navbar.inc.php');

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

try {
    // Delete cart items older than 2 minutes
    $stmt = $cnx->prepare("
        DELETE FROM cart 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 MINUTE)
        AND user_id = ?
    ");
    $stmt->execute([$_SESSION['user']['id']]);

    // Get cart items for the current user
    $stmt = $cnx->prepare("
        SELECT c.*, 
               p1.name as departure_name,
               p2.name as arrival_name,
               s.name as ship_name,
               TIMESTAMPDIFF(SECOND, NOW(), DATE_ADD(c.created_at, INTERVAL 2 MINUTE)) as seconds_remaining
        FROM cart c
        JOIN planet p1 ON c.departure_planet_id = p1.id
        JOIN planet p2 ON c.arrival_planet_id = p2.id
        JOIN ship s ON c.ship_id = s.id
        WHERE c.user_id = ?
    ");

    $stmt->execute([$_SESSION['user']['id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total for current user
    $stmt = $cnx->prepare("SELECT SUM(price) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    $cartItems = [];
    $total = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <h1 class="text-center mb-4">Your Cart</h1>
        
        <?php if (empty($cartItems)): ?>
            <div class="alert alert-info text-center">
                <p><span class="fw-bold"><?= htmlspecialchars($_SESSION['user']['username']) ?></span>, your cart is empty!</p>
                <a href="../src/index.php" class="btn btn-primary mt-3">
                    <i class="bi bi-cart"></i> Start Booking
                </a>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($cartItems as $item): ?>
                    <div class="list-group-item bg-dark text-light border-light mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="me-4">
                                    <i class="bi bi-rocket display-6"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="mb-0">
                                            <?= htmlspecialchars($item['departure_name']) ?>
                                            <i class="bi bi-arrow-right mx-2"></i>
                                            <?= htmlspecialchars($item['arrival_name']) ?>
                                        </h5>
                                    </div>
                                    <p class="mb-0 text">
                                        <small>Ship : <?= htmlspecialchars($item['ship_name']) ?></small>
                                    </p>
                                    <p class="mb-0">
                                        <small>Departure: <?= htmlspecialchars($item['departure_time']) ?></small>
                                    </p>
                                    <p class="mb-0">
                                        <small>Arrival: <?= htmlspecialchars($item['arrival_time']) ?></small>
                                    </p>
                                </div>
                                <div class="mx-4 text-success">
                                    <i class="bi bi-credit-card"></i>
                                    <span><?= number_format($item['price'], 2) ?> Credits</span>
                                </div>
                                <div class="mx-4 text-warning">
                                    <i class="bi bi-clock"></i>
                                    <span class="countdown" data-timestamp="<?= $item['seconds_remaining'] ?>">
                                        <?= floor($item['seconds_remaining'] / 60) ?>:<?= str_pad($item['seconds_remaining'] % 60, 2, '0', STR_PAD_LEFT) ?>
                                    </span>
                                </div>
                                <form action="../scripts/remove_from_cart.php" method="POST" class="mb-0">
                                    <input type="hidden" name="cart_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="card bg-dark text-light border-light mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Order Summary</h5>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-light fw-bold">Total</span>
                        <div class="mb-0 text-success">
                            <i class="bi bi-credit-card me-2"></i>
                            <?= number_format($total, 2) ?> Credits
                        </div>
                    </div>
                    <hr class="border-light my-3">
                    <div class="text-center">
                        <a href="checkout.php" class="btn btn-success btn-lg px-5">
                            <i class="bi bi-rocket-takeoff me-2"></i>
                            Book my trip
                        </a>
                        <small class="text d-block mt-2">
                            <i class="bi bi-shield-check me-1"></i>
                            Secure payment
                        </small>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../js/cart.js"></script>
</body>
</html> 