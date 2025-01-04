<?php
global $cnx;
require_once '../configs/config.php';
include('../include/links.inc.php');
include('../include/navbar.inc.php');

// Nettoyer les billets expirés
$currentTime = time();
if (isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array_filter($_SESSION['cart'], function($item) use ($currentTime) {
        return ($currentTime - $item['timestamp']) < 120;
    });
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
        
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="alert alert-info text-center">
                <p>Your cart is empty</p>
                <a href="../src/index.php" class="btn btn-primary mt-3">
                    <i class="bi bi-cart"></i> Start Booking
                </a>
            </div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($_SESSION['cart'] as $index => $item): 
                    // Récupérer les détails des planètes et du vaisseau
                    $stmt = $cnx->prepare("SELECT name, x, y, sub_grid_x, sub_grid_y FROM planet WHERE id = ?");
                    $stmt->execute([$item['departure_planet_id']]);
                    $departurePlanet = $stmt->fetch();
                    
                    $stmt->execute([$item['arrival_planet_id']]);
                    $arrivalPlanet = $stmt->fetch();
                    
                    $stmt = $cnx->prepare("SELECT name, speed_kmh FROM ship WHERE id = ?");
                    $stmt->execute([$item['ship_id']]);
                    $ship = $stmt->fetch();
                    
                    // Calcul de la distance
                    $distance_km = sqrt(
                        pow(($arrivalPlanet['x'] - $departurePlanet['x']) * 1000 + 
                            ($arrivalPlanet['sub_grid_x'] - $departurePlanet['sub_grid_x']), 2) +
                        pow(($arrivalPlanet['y'] - $departurePlanet['y']) * 1000 + 
                            ($arrivalPlanet['sub_grid_y'] - $departurePlanet['sub_grid_y']), 2)
                    ) * 100000;

                    // Calcul du prix
                    $lightSpeed = 1.08e9; // 1,08 milliard km/h
                    $baseCost = ($distance_km / 1e9) * 100;
                    $speedDifferencePercent = (($ship['speed_kmh'] - $lightSpeed) / $lightSpeed) * 100;
                    $finalPrice = $baseCost;
                    if ($speedDifferencePercent > 0) {
                        $finalPrice += ($baseCost * $speedDifferencePercent / 100);
                    }
                    
                    $timeRemaining = 120 - ($currentTime - $item['timestamp']);
                ?>
                    <div class="list-group-item bg-dark text-light border-light mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="me-4">
                                    <i class="bi bi-rocket display-6"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="mb-0">
                                            <?= htmlspecialchars($departurePlanet['name']) ?> 
                                            <i class="bi bi-arrow-right mx-2"></i> 
                                            <?= htmlspecialchars($arrivalPlanet['name']) ?>
                                        </h5>
                                    </div>
                                    <p class="mb-0 text">
                                        <small>Ship: <?= htmlspecialchars($ship['name']) ?></small>
                                    </p>
                                </div>
                                <div class="mx-4 text-success">
                                    <i class="bi bi-credit-card"></i>
                                    <span><?= number_format($item['price'], 2) ?> Credits</span>
                                </div>
                                <div class="mx-4 text-warning">
                                    <i class="bi bi-clock"></i>
                                    <span data-timestamp="<?= $item['timestamp'] ?>">
                                        <?= floor($timeRemaining / 60) ?>:<?= str_pad($timeRemaining % 60, 2, '0', STR_PAD_LEFT) ?>
                                    </span>
                                </div>
                                <form action="../scripts/remove_from_cart.php" method="POST" class="mb-0">
                                    <input type="hidden" name="index" value="<?= $index ?>">
                                    <button type="submit" class="btn btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <div class="d-flex justify-content-center mt-4">
                <a href="checkout.php" class="btn btn-success btn-lg">
                    <i class="bi bi-check-circle"></i> Proceed to Checkout
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <script src="../js/cart.js"></script>
</body>
</html> 