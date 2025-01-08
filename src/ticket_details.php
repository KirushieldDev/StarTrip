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

// Vérifier si l'ID du ticket est fourni
if (!isset($_GET['id'])) {
    header('Location: bookings.php');
    exit();
}

try {
    // Récupérer les détails du ticket
    $stmt = $cnx->prepare("
        SELECT t.*,
               p1.name as departure_name,
               p1.region as departure_region,
               p2.name as arrival_name,
               p2.region as arrival_region
        FROM ticket t
        JOIN planet p1 ON t.departure_planet_id = p1.id
        JOIN planet p2 ON t.arrival_planet_id = p2.id
        WHERE t.id = :ticket_id AND t.user_id = :user_id
    ");
    $stmt->execute([
        ':ticket_id' => $_GET['id'],
        ':user_id' => $_SESSION['user']['id']
    ]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$ticket) {
        $_SESSION['error'] = "Ticket not found.";
        header('Location: bookings.php');
        exit();
    }

    // Vérifier si le voyage est terminé
    $isCompletedJourney = strtotime($ticket['arrival_time']) < time();
    $status = $isCompletedJourney ? 'Completed' : 'Confirmed';
    $statusClass = $isCompletedJourney ? 'bg-secondary' : 'bg-success';

    // Récupérer tous les segments du ticket
    $segments_stmt = $cnx->prepare("
        SELECT dt.*,
               p1.name as dep_name,
               p1.region as dep_region,
               p2.name as arr_name,
               p2.region as arr_region,
               s.name as ship_name,
               s.capacity
        FROM detailsticket dt
        JOIN planet p1 ON dt.departure_planet_id = p1.id
        JOIN planet p2 ON dt.arrival_planet_id = p2.id
        JOIN ship s ON dt.ship_id = s.id
        WHERE dt.id_ticket = :ticket_id
        ORDER BY dt.departure_time ASC
    ");
    $segments_stmt->execute([':ticket_id' => $ticket['id']]);
    $segments = $segments_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['error_message'] = "Error: " . $e->getMessage();
    header('Location: bookings.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket Details</title>
</head>
<body class="bg-dark text-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">
            <i class="bi bi-ticket-detailed me-2"></i>
            Ticket Details
        </h1>
        <a href="bookings.php" class="btn btn-outline-light">
            <i class="bi bi-arrow-left me-2"></i>Back to Bookings
        </a>
    </div>

    <!-- Main Ticket Information -->
    <div class="card bg-dark border-light mb-4">
        <div class="card-header bg-dark border-light">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white">Booking Reference: #<?= str_pad($ticket['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                <div>
                    <span class="badge <?= $statusClass ?> me-2"><?= $status ?></span>
                    <?php if (!$isCompletedJourney): ?>
                        <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash me-2"></i>Cancel Booking
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-white-50 mb-2">Journey</h6>
                    <p class="h5 mb-3 text-white">
                        <?= htmlspecialchars($ticket['departure_name']) ?>
                        <span class="text-white-50">(<?= htmlspecialchars($ticket['departure_region']) ?>)</span>
                        <i class="bi bi-arrow-right mx-2"></i>
                        <?= htmlspecialchars($ticket['arrival_name']) ?>
                        <span class="text-white-50">(<?= htmlspecialchars($ticket['arrival_region']) ?>)</span>
                    </p>

                    <h6 class="text-white-50 mb-2">Travel Period</h6>
                    <p class="mb-3 text-white">
                        <i class="bi bi-calendar-range me-2"></i>
                        <?= date('d/m/Y H:i', strtotime($ticket['departure_time'])) ?>
                        <i class="bi bi-arrow-right mx-2"></i>
                        <?= date('d/m/Y H:i', strtotime($ticket['arrival_time'])) ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-white-50 mb-2">Booking Details</h6>
                    <p class="mb-2 text-white">
                        <i class="bi bi-people me-2"></i>
                        Passengers: <?= $ticket['passengers'] ?>
                    </p>
                    <p class="mb-2 text-white">
                        <i class="bi bi-calendar-check me-2"></i>
                        Booked on: <?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?>
                    </p>
                    <p class="mb-0 text-white">
                        <i class="bi bi-credit-card me-2"></i>
                        Total Price: <span class="text-success"><?= number_format($ticket['price'], 2) ?> Credits</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Segments -->
    <h4 class="mb-3">Trip Path</h4>
    <div class="card bg-dark border-light">
        <div class="card-body">
            <?php foreach ($segments as $index => $segment): ?>
                <div class="d-flex gap-3">
                    <div class="text-primary">
                        <i class="bi bi-rocket-takeoff fs-4"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h5 class="mb-1 text-white">
                                    <?= htmlspecialchars($segment['dep_name']) ?>
                                    <span class="text-white-50">(<?= htmlspecialchars($segment['dep_region']) ?>)</span>
                                    <i class="bi bi-arrow-right mx-2"></i>
                                    <?= htmlspecialchars($segment['arr_name']) ?>
                                    <span class="text-white-50">(<?= htmlspecialchars($segment['arr_region']) ?>)</span>
                                </h5>
                                <p class="text-white-50 mb-0">
                                    Ship: <?= htmlspecialchars($segment['ship_name']) ?>
                                </p>
                            </div>
                            <span class="text-success h5 mb-0">
                                <?= number_format($segment['price'], 2) ?> Credits
                            </span>
                        </div>
                        <div class="d-flex justify-content-between text-white-50">
                            <div>
                                <small>
                                    <i class="bi bi-calendar-event me-1"></i>
                                    Departure: <?= date('d/m/Y H:i', strtotime($segment['departure_time'])) ?>
                                </small>
                            </div>
                            <div>
                                <small>
                                    <i class="bi bi-calendar-check me-1"></i>
                                    Arrival: <?= date('d/m/Y H:i', strtotime($segment['arrival_time'])) ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($index < count($segments) - 1): ?>
                    <hr class="border-secondary my-3">
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Modal de suppression -->
<?php if (!$isCompletedJourney): ?>
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content bg-dark text-light">
                <div class="modal-header border-secondary">
                    <h5 class="modal-title">Cancel Booking #<?= str_pad($ticket['id'], 6, '0', STR_PAD_LEFT) ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to cancel this booking ?</p>
                    <div class="d-flex justify-content-between border-top border-secondary pt-3 mt-3">
                        <div>
                            <strong><?= htmlspecialchars($ticket['departure_name']) ?></strong>
                            <i class="bi bi-arrow-right mx-2"></i>
                            <strong><?= htmlspecialchars($ticket['arrival_name']) ?></strong>
                        </div>
                        <strong class="text-success"><?= number_format($ticket['price'], 2) ?> Credits</strong>
                    </div>
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
<?php endif; ?>
</body>
</html>