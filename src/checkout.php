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
    $cnx->beginTransaction();

    // Get cart items for current user
    $stmt = $cnx->prepare("
        SELECT c.*, 
               p1.name as departure_name,
               p2.name as arrival_name,
               s.name as ship_name
        FROM cart c
        JOIN planet p1 ON c.departure_planet_id = p1.id
        JOIN planet p2 ON c.arrival_planet_id = p2.id
        JOIN ship s ON c.ship_id = s.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$_SESSION['user']['id']]);
    $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate total
    $stmt = $cnx->prepare("SELECT SUM(price) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user']['id']]);
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

    if ($total === 0) {
        header('location: cart.php');
        exit;
    }

    // Transfer cart items to tickets
    foreach ($cartItems as $item) {
        $stmt = $cnx->prepare("
            INSERT INTO ticket (
                user_id,
                departure_planet_id,
                arrival_planet_id,
                ship_id,
                departure_time,
                arrival_time,
                price,
                created_at
            ) VALUES (
                :user_id,
                :departure_planet_id,
                :arrival_planet_id,
                :ship_id,
                :departure_time,
                :arrival_time,
                :price,
                NOW()
            )
        ");

        $stmt->execute([
            'user_id' => $_SESSION['user']['id'],
            'departure_planet_id' => $item['departure_planet_id'],
            'arrival_planet_id' => $item['arrival_planet_id'],
            'ship_id' => $item['ship_id'],
            'departure_time' => $item['departure_time'],
            'arrival_time' => $item['arrival_time'],
            'price' => $item['price']
        ]);
    }

    // Clear user's cart
    $stmt = $cnx->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user']['id']]);

    $cnx->commit();
} catch (PDOException $e) {
    $cnx->rollBack();
    $_SESSION['error_message'] = "Erreur lors de la confirmation : " . $e->getMessage();
    header('Location: cart.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmation</title>
</head>
<body class="bg-dark text-light">
<div class="container mt-5">
    <div class="card bg-dark text-light border-success">
        <div class="card-body text-center">
            <div class="mb-4">
                <i class="bi bi-check-circle-fill text-success display-1"></i>
            </div>
            <h2 class="card-title mb-4">Booking Confirmed!</h2>
            <p class="lead mb-4">
                Thank you <span class="fw-bold"><?= htmlspecialchars($_SESSION['user']['username']) ?></span> for your booking. The total amount is
                <span class="text-success fw-bold"><?= number_format($total, 2) ?> Credits</span>
            </p>
            <hr class="border-light my-4">
            <div class="d-flex justify-content-center gap-3">
                <a href="index.php" class="btn btn-primary">
                    <i class="bi bi-cart-plus me-2"></i>
                    Make Another Purchase
                </a>
                <a href="bookings.php" class="btn btn-outline-light">
                    <i class="bi bi-file-text me-2"></i>
                    View My Bookings
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>