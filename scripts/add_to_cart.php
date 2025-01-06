<?php
session_start();
require_once '../configs/config.php';
global $cnx;

if (!isset($_SESSION['user'])) {
    header('Location: ../src/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user']['id'];
    $departurePlanetId = $_POST['departure_planet_id'];
    $arrivalPlanetId = $_POST['arrival_planet_id'];
    $departureTime = date('Y-m-d H:i:s'); // Temps actuel pour le départ
    $arrivalTime = date('Y-m-d H:i:s'); // Sera mis à jour plus tard
    $passengers = intval($_POST['passengers']);
    $fullPath = json_decode($_POST['full_path'], true);
    
    try {
        $cnx->beginTransaction();

        // Créer d'abord l'entrée dans cart
        $cart_stmt = $cnx->prepare("
            INSERT INTO cart (
                user_id, departure_planet_id, arrival_planet_id, 
                departure_time, arrival_time, price, passengers
            ) VALUES (
                :user_id, :departure_id, :arrival_id, 
                :departure_time, :arrival_time, :price, :passengers
            )
        ");
        
        $cart_stmt->execute([
            ':user_id' => $userId,
            ':departure_id' => $departurePlanetId,
            ':arrival_id' => $arrivalPlanetId,
            ':departure_time' => $departureTime,
            ':arrival_time' => $arrivalTime,
            ':price' => 0, // Prix initial à 0, sera mis à jour après
            ':passengers' => $passengers
        ]);
        
        $cartId = $cnx->lastInsertId();

        // Créer les entrées dans detailscart pour chaque segment
        for ($i = 0; $i < count($fullPath) - 1; $i++) {
            $currentPlanetId = $fullPath[$i];
            $nextPlanetId = $fullPath[$i + 1];

            // Récupérer les détails du trajet
            $trip_stmt = $cnx->prepare("
                SELECT t.*, s.speed_kmh 
                FROM trip t 
                JOIN ship s ON t.ship_id = s.id 
                WHERE t.planet_id = :departure_id 
                AND t.destination_planet_id = :arrival_id
                ORDER BY s.speed_kmh ASC
                LIMIT 1
            ");
            
            $trip_stmt->execute([
                ':departure_id' => $currentPlanetId,
                ':arrival_id' => $nextPlanetId
            ]);
            
            $tripDetails = $trip_stmt->fetch(PDO::FETCH_ASSOC);

            if ($tripDetails) {
                // Calculer le prix pour ce segment
                $distance = isset($_POST['total_distance']) ? floatval($_POST['total_distance']) / (count($fullPath) - 1) : 0;
                $segmentPrice = ($distance / 1e9) * 100; // Prix de base
                $speedFactor = ($tripDetails['speed_kmh'] / 1.08e9); // Comparaison avec vitesse lumière
                $segmentPrice *= (1 + max(0, $speedFactor - 1)); // Ajustement selon la vitesse

                $detail_stmt = $cnx->prepare("
                    INSERT INTO detailscart (
                        id_cart, user_id, departure_planet_id, arrival_planet_id,
                        departure_time, arrival_time, ship_id, price, passengers
                    ) VALUES (
                        :cart_id, :user_id, :departure_id, :arrival_id,
                        :departure_time, :arrival_time, :ship_id, :price, :passengers
                    )
                ");

                $duration = $distance / $tripDetails['speed_kmh'];
                $segmentArrivalTime = date('Y-m-d H:i:s', strtotime($departureTime . ' + ' . ceil($duration) . ' hours'));

                $detail_stmt->execute([
                    ':cart_id' => $cartId,
                    ':user_id' => $userId,
                    ':departure_id' => $currentPlanetId,
                    ':arrival_id' => $nextPlanetId,
                    ':departure_time' => $departureTime,
                    ':arrival_time' => $segmentArrivalTime,
                    ':ship_id' => $tripDetails['ship_id'],
                    ':price' => $segmentPrice * $passengers,
                    ':passengers' => $passengers
                ]);

                // Mettre à jour le temps de départ pour le prochain segment
                $departureTime = $segmentArrivalTime;
            }
        }

        // Calculer le total et mettre à jour cart
        $total_stmt = $cnx->prepare("
            SELECT SUM(price) as total_price 
            FROM detailscart 
            WHERE id_cart = :cart_id
        ");

        $total_stmt->execute([':cart_id' => $cartId]);
        $totalResult = $total_stmt->fetch(PDO::FETCH_ASSOC);
        $totalPrice = $totalResult['total_price'];

        // Mettre à jour le prix total et les temps dans cart
        $update_cart_stmt = $cnx->prepare("
            UPDATE cart 
            SET price = :total_price,
                arrival_time = :arrival_time
            WHERE id = :cart_id
        ");

        $update_cart_stmt->execute([
            ':total_price' => $totalPrice,
            ':arrival_time' => $segmentArrivalTime,
            ':cart_id' => $cartId
        ]);

        $cnx->commit();
        $_SESSION['success'] = "Trip added to cart successfully!";
        header('Location: ../src/cart.php');
        exit();

    } catch (Exception $e) {
        $cnx->rollBack();
        $_SESSION['error'] = "Error adding trip to cart: " . $e->getMessage();
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit();
    }
}