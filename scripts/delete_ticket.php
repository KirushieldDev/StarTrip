<?php
session_start();
require_once '../configs/config.php';
global $cnx;

if (!isset($_SESSION['user'])) {
    header('Location: ../src/login.php');
    exit();
}

if (!isset($_POST['ticket_id'])) {
    header('Location: ../src/bookings.php');
    exit();
}

try {
    $cnx->beginTransaction();

    $stmt = $cnx->prepare("
        SELECT id FROM ticket 
        WHERE id = :ticket_id AND user_id = :user_id
    ");
    $stmt->execute([
        ':ticket_id' => $_POST['ticket_id'],
        ':user_id' => $_SESSION['user']['id']
    ]);

    if (!$stmt->fetch()) {
        throw new Exception("Unauthorized access to this ticket.");
    }

    $stmt = $cnx->prepare("
        DELETE FROM detailsticket 
        WHERE id_ticket = :ticket_id
    ");
    $stmt->execute([':ticket_id' => $_POST['ticket_id']]);

    $stmt = $cnx->prepare("
        DELETE FROM ticket 
        WHERE id = :ticket_id AND user_id = :user_id
    ");
    $stmt->execute([
        ':ticket_id' => $_POST['ticket_id'],
        ':user_id' => $_SESSION['user']['id']
    ]);

    $cnx->commit();
    header('Location: ../src/bookings.php');
    exit();

} catch (Exception $e) {
    $cnx->rollBack();
    $_SESSION['error'] = "Error cancelling booking: " . $e->getMessage();
    header('Location: ../src/ticket_details.php?id=' . $_POST['ticket_id']);
    exit();
} 