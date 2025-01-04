<?php
require_once '../configs/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialiser le panier s'il n'existe pas
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    $departurePlanetId = $_POST['departure_planet_id'];
    $arrivalPlanetId = $_POST['arrival_planet_id'];
    $shipId = $_POST['ship_id'];
    $price = $_POST['price'];
    $timestamp = time();

    // Vérifier si un billet similaire existe déjà dans le panier
    $ticketExists = false;
    foreach ($_SESSION['cart'] as $item) {
        if ($item['departure_planet_id'] == $departurePlanetId &&
            $item['arrival_planet_id'] == $arrivalPlanetId &&
            $item['ship_id'] == $shipId) {
            $ticketExists = true;
            break;
        }
    }

    if (!$ticketExists) {
        // Ajouter au panier avec timestamp et prix
        $_SESSION['cart'][] = [
            'departure_planet_id' => $departurePlanetId,
            'arrival_planet_id' => $arrivalPlanetId,
            'ship_id' => $shipId,
            'price' => $price,
            'timestamp' => $timestamp
        ];

        $_SESSION['success_message'] = "Ticket added to cart! You have 2 minutes to complete your purchase.";
    } else {
        $_SESSION['error_message'] = "This ticket is already in your cart!";
    }

    // Rediriger vers la page précédente
    header('Location: ../src/cart.php');
    exit;
}