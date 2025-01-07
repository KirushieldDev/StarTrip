<?php
global $cnx;
require_once '../configs/config.php';
 /* Delete all tickets from cart */
try {
    $stmt = $cnx->prepare("
        DELETE FROM cart 
        WHERE created_at < DATE_SUB(NOW(), INTERVAL 2 MINUTE)
    ");
    $stmt->execute();
} catch (PDOException $e) {
    error_log("Error cleaning cart: " . $e->getMessage());
}