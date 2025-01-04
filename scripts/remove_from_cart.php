<?php
session_start();
require_once '../configs/config.php';
global $cnx;

if (!isset($_SESSION['user'])) {
    header('Location: ../src/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_id'])) {
    try {
        // Préparer et exécuter la requête de suppression
        $stmt = $cnx->prepare("
            DELETE FROM cart 
            WHERE id = ? 
            AND user_id = ?
        ");
        
        $stmt->execute([
            $_POST['cart_id'],
            $_SESSION['user']['id']
        ]);

        $_SESSION['success_message'] = "Item removed from cart successfully.";
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error removing item from cart: " . $e->getMessage();
    }
}

header('Location: ../src/cart.php');
exit(); 