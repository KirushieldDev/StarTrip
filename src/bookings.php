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
    // Get tickets for current user
    $stmt = $cnx->prepare("
        SELECT t.*,
               p1.name as departure_name,
               p2.name as arrival_name
        FROM ticket t
        JOIN planet p1 ON t.departure_planet_id = p1.id
        JOIN planet p2 ON t.arrival_planet_id = p2.id
        WHERE t.user_id = ?
        ORDER BY t.created_at DESC
    ");
    $stmt->execute([$_SESSION['user']['id']]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    $tickets = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My bookings</title>
</head>
<body class="bg-dark text-light">
<div class="container mt-5">
    <h1 class="text-center fw-bold mb-4">My bookings</h1>

    <?php if (empty($tickets)): ?>
        <div class="alert alert-info text-center">
            <p><span class="fw-bold"><?= htmlspecialchars($_SESSION['user']['username']) ?></span>, you don't have any booked trips yet!</p>
            <a href="index.php" class="btn btn-primary mt-3">
                <i class="bi bi-rocket"></i> Book a trip
            </a>
        </div>
    <?php else: ?>
        <div class="mb-4">
            <a href="../src/index.php" class="btn btn-primary text-light">
                <i class="bi bi-arrow-left"></i> Continue Purchasing
            </a>
        </div>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php foreach ($tickets as $ticket): ?>
                <div class="col">
                    <div class="card h-100 bg-dark text-light border-light">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <i class="bi bi-rocket-takeoff display-6 me-3"></i>
                                <div>
                                    <h5 class="card-title mb-1">
                                        <?= htmlspecialchars($ticket['departure_name']) ?>
                                        <i class="bi bi-arrow-right mx-2"></i>
                                        <?= htmlspecialchars($ticket['arrival_name']) ?>
                                    </h5>
                                    <p class="card-text text mb-0">
                                        <i class="bi bi-calendar me-2"></i>
                                        <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
                                    </p>
                                </div>
                            </div>

                            <div class="card-text">
                                <p class="mb-2">
                                    <i class="bi bi-credit-card me-2"></i>
                                    Price : <span
                                            class="text-success"><?= number_format($ticket['price'], 2) ?> Credits</span>
                                </p>
                            </div>
                        </div>
                        <div class="card-footer bg-dark border-light">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-calendar-range me-2"></i>
                                    <?= date('d/m/Y H:i', strtotime($ticket['departure_time'])) ?>
                                    <i class="bi bi-arrow-right mx-2"></i>
                                    <?= date('d/m/Y H:i', strtotime($ticket['arrival_time'])) ?>
                                </div>
                                <div>
                                    <button type="button" class="btn btn-danger btn-sm me-2" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $ticket['id'] ?>">
                                        <i class="bi bi-trash me-2"></i>Cancel
                                    </button>
                                    <a href="ticket_details.php?id=<?= $ticket['id'] ?>" class="btn btn-primary btn-sm me-2">
                                        <i class="bi bi-info-circle me-2"></i>Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php foreach ($tickets as $ticket): ?>
    <div class="modal fade" id="deleteModal<?= $ticket['id'] ?>" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Cancel Booking #<?= str_pad($ticket['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking ?</p>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <form action="../scripts/delete_ticket.php" method="POST">
                        <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-2"></i>Cancel Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>
</body>
</html>