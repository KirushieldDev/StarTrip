<?php
session_start();
require_once '../configs/config.php';
global $cnx;

// Check if the user is logged in; if not, redirect to the login page
if (!isset($_SESSION['user'])) {
    header('Location: ../src/login.php');
    exit();
}

// Check if the ticket ID is provided in the POST request; if not, redirect to the bookings page
if (!isset($_POST['ticket_id'])) {
    header('Location: ../src/bookings.php');
    exit();
}

try {
    $cnx->beginTransaction(); // Start a database transaction

    // Verify that the ticket exists and belongs to the current user
    $stmt = $cnx->prepare("
        SELECT id FROM ticket 
        WHERE id = :ticket_id AND user_id = :user_id
    ");
    $stmt->execute([
        ':ticket_id' => $_POST['ticket_id'],
        ':user_id' => $_SESSION['user']['id']
    ]);

    // If no matching ticket is found, throw an exception
    if (!$stmt->fetch()) {
        throw new Exception("Unauthorized access to this ticket.");
    }

    // Delete all related entries in the `detailsticket` table for the given ticket
    $stmt = $cnx->prepare("
        DELETE FROM detailsticket 
        WHERE id_ticket = :ticket_id
    ");
    $stmt->execute([':ticket_id' => $_POST['ticket_id']]);

    // Delete the ticket itself from the `ticket` table
    $stmt = $cnx->prepare("
        DELETE FROM ticket 
        WHERE id = :ticket_id AND user_id = :user_id
    ");
    $stmt->execute([
        ':ticket_id' => $_POST['ticket_id'],
        ':user_id' => $_SESSION['user']['id']
    ]);

    $cnx->commit(); // Commit the transaction
    header('Location: ../src/bookings.php'); // Redirect to the bookings page
    exit();

} catch (Exception $e) {
    $cnx->rollBack(); // Rollback the transaction in case of an error
    // Store the error message in the session and redirect back to the ticket details page
    $_SESSION['error'] = "Error cancelling booking: " . $e->getMessage();
    header('Location: ../src/ticket_details.php?id=' . $_POST['ticket_id']);
    exit();
}
