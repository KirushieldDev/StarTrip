<?php
session_start();
require_once '../configs/config.php';
global $cnx;

if (!isset($_SESSION['user'])) {
    header('Location: ../src/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $cnx->prepare("
            INSERT INTO cart (
                user_id, 
                departure_planet_id, 
                arrival_planet_id, 
                ship_id, 
                price,
                departure_time,
                arrival_time
            ) VALUES (?, ?, ?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL ? HOUR))
        ");

        // Calculate duration
        $ship_stmt = $cnx->prepare("SELECT speed_kmh FROM ship WHERE id = ?");
        $ship_stmt->execute([$_POST['ship_id']]);
        $ship = $ship_stmt->fetch(PDO::FETCH_ASSOC);

        // Calculate distance
        $planet_stmt = $cnx->prepare("
            SELECT 
                p1.x as dep_x, p1.y as dep_y, 
                p1.sub_grid_x as dep_sub_x, p1.sub_grid_y as dep_sub_y,
                p2.x as arr_x, p2.y as arr_y,
                p2.sub_grid_x as arr_sub_x, p2.sub_grid_y as arr_sub_y
            FROM planet p1, planet p2 
            WHERE p1.id = ? AND p2.id = ?
        ");
        $planet_stmt->execute([$_POST['departure_planet_id'], $_POST['arrival_planet_id']]);
        $planets = $planet_stmt->fetch(PDO::FETCH_ASSOC);

        $distance_km = sqrt(
                pow(($planets['arr_x'] - $planets['dep_x']) * 1000 +
                    ($planets['arr_sub_x'] - $planets['dep_sub_x']), 2) +
                pow(($planets['arr_y'] - $planets['dep_y']) * 1000 +
                    ($planets['arr_sub_y'] - $planets['dep_sub_y']), 2)
            ) * 100000;

        $duration_hours = $distance_km / $ship['speed_kmh'];

        // Execute the query
        $stmt->execute([
            $_SESSION['user']['id'],
            $_POST['departure_planet_id'],
            $_POST['arrival_planet_id'],
            $_POST['ship_id'],
            $_POST['price'],
            $duration_hours
        ]);

        header('Location: ../src/cart.php');
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error adding to cart: " . $e->getMessage();
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: ../src/index.php');
    exit();
}