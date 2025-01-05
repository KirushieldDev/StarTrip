<?php
require_once '../configs/config.php';
include('../include/links.inc.php');
include('../class/planet.php');
include('../include/navbar.inc.php');
global $cnx;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../src/index.php");
    exit();
}

$departurePlanetId = $_POST['departurePlanetId'];
$arrivalPlanetId = $_POST['arrivalPlanetId'];
$legion = isset($_POST['legion']) ? $_POST['legion'] : 'Empty';
$selectedShipId = $_POST['shipId'] ?? null;
$capacity = trim($_POST['capacity'] ?? '');

$timePreference = $_POST['timePreference'] ?? null;
$selectedTime = $_POST['selectedTime'] ?? null;

try {
    // Read the output JSON file
    $jsonContent = file_get_contents('../output.json');
    $outputData = json_decode($jsonContent, true);

    $stmt = $cnx->prepare("SELECT * FROM planet WHERE id = :id");

    $stmt->bindParam(':id', $departurePlanetId);
    $stmt->execute();
    $departurePlanetDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt->bindParam(':id', $arrivalPlanetId);
    $stmt->execute();
    $arrivalPlanetDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($departurePlanetDetails && $arrivalPlanetDetails) {
        $distance_km = $outputData['distance'] ?? 0;

        $ship_stmt = $cnx->prepare("SELECT id, name, speed_kmh FROM ship");
        $ship_stmt->execute();
        $ships = $ship_stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($selectedShipId) {
            $ship_stmt = $cnx->prepare("SELECT * FROM ship WHERE id = :id");
            $ship_stmt->bindParam(':id', $selectedShipId);
            $ship_stmt->execute();
            $selectedShip = $ship_stmt->fetch(PDO::FETCH_ASSOC);

            if ($selectedShip) {
                $speed_kmh = $selectedShip['speed_kmh'];
                $duration_hours = $distance_km / $speed_kmh;

                $days = floor($duration_hours / 24);
                $hours = floor($duration_hours % 24);
                $minutes = round(($duration_hours - floor($duration_hours)) * 60);
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

try {
    $total_travel_time_in_hours = 0;
    $segment_distances = $outputData['segment_distances'];

    for ($i = 0; $i < count($outputData['path']) - 1; $i++) {
        $currentPlanetId = $outputData['path'][$i];
        $nextPlanetId = $outputData['path'][$i + 1];

        $ship_stmt = $cnx->prepare("
            SELECT s.speed_kmh
            FROM ship s
            INNER JOIN trip t ON s.id = t.ship_id
            WHERE t.planet_id = :departure_id AND t.destination_planet_id = :arrival_id
        ");
        $ship_stmt->execute([':departure_id' => $currentPlanetId, ':arrival_id' => $nextPlanetId]);
        $available_ships = $ship_stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($available_ships)) {
            throw new Exception("No available ships for the segment from $currentPlanetId to $nextPlanetId");
        }
        $ship = $available_ships[0];
        $speed = $ship['speed_kmh'];
        $segment_distance = $segment_distances[$i];
        $segment_time_in_hours = $segment_distance / $speed;
        $total_travel_time_in_hours += $segment_time_in_hours;
    }
    $total_travel_time_in_minutes = $total_travel_time_in_hours * 60;
    $days = floor($total_travel_time_in_minutes / 1440);
    $remaining_minutes = $total_travel_time_in_minutes % 1440;
    $hours = floor($remaining_minutes / 60);
    $minutes = $remaining_minutes % 60;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

if (!empty($timePreference) && !empty($selectedTime)) {
    $selectedDateTime = new DateTime($selectedTime);
    $total_travel_time_in_seconds = ($days * 24 * 3600) + ($hours * 3600) + ($minutes * 60);
    if ($timePreference === 'departure') {
        $selectedDateTime->add(new DateInterval('PT' . $total_travel_time_in_seconds . 'S'));
    } elseif ($timePreference === 'arrival') {
        $selectedDateTime->sub(new DateInterval('PT' . $total_travel_time_in_seconds . 'S'));

    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="../css/result.css">
</head>
<body class="bg-dark text-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="../src/index.php" class="btn btn-outline-light">
            <i class="bi bi-arrow-left"></i> Back to Search
        </a>

        <?php if ($departurePlanetDetails && $arrivalPlanetDetails): ?>
            <form action="map.php" method="POST" class="m-0">
                <input type="hidden" name="departurePlanetId" value="<?= htmlspecialchars($departurePlanetId) ?>">
                <input type="hidden" name="arrivalPlanetId" value="<?= htmlspecialchars($arrivalPlanetId) ?>">
                <button type="submit" class="btn btn-outline-light">
                    <i class="bi bi-map"></i> View on Map
                </button>
            </form>
        <?php endif; ?>
    </div>

    <h1 class="fw-bold text-white mb-4 text-center">Search Results</h1>

    <?php if ($departurePlanetDetails && $arrivalPlanetDetails): ?>
        <div class="row">
            <?php if (!$outputData['success']): ?>
                <!-- Si success est false, afficher uniquement le message et les planètes -->
                <div class="col-12 mb-3">
                    <div class="alert alert-danger text-center">
                        <h4><i class="bi bi-exclamation-triangle-fill"></i> No Available Trip</h4>
                        <p class="mb-0">Sorry, we couldn't find any available trip between these planets with your
                            current criteria.</p>
                    </div>
                </div>

                <!-- Departure Planet -->
                <div class="col-md-6 mb-4">
                    <div class="card border-success shadow-lg">
                        <div class="card-header text-center text-uppercase text-white bg-success fw-bold">
                            Departure Planet <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center bg-light rounded">
                            <div class="mb-3">
                                <?= planet::getHtmlImage($departurePlanetDetails['id'], 'img-fluid rounded shadow', 150, 150); ?>
                            </div>
                            <h5 class="card-title text-success fw-bold">
                                <?= htmlspecialchars($departurePlanetDetails['name']) ?>
                            </h5>
                            <p class="card-text">
                                <i class="bi bi-geo-alt"></i>
                                Region: <?= htmlspecialchars($departurePlanetDetails['region']) ?>
                            </p>
                            <p class="card-text">
                                <i class="bi bi-diagram-3"></i>
                                Sector: <?= htmlspecialchars($departurePlanetDetails['sector']) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Arrival Planet -->
                <div class="col-md-6 mb-4">
                    <div class="card border-danger shadow-lg">
                        <div class="card-header text-center text-uppercase text-white bg-danger fw-bold">
                            Destination Planet <i class="bi bi-geo-fill"></i>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center bg-light rounded">
                            <div class="mb-3">
                                <?= planet::getHtmlImage($arrivalPlanetDetails['id'], 'img-fluid rounded shadow', 150, 150); ?>
                            </div>
                            <h5 class="card-title text-danger fw-bold">
                                <?= htmlspecialchars($arrivalPlanetDetails['name']) ?>
                            </h5>
                            <p class="card-text">
                                <i class="bi bi-geo-alt"></i>
                                Region: <?= htmlspecialchars($arrivalPlanetDetails['region']) ?>
                            </p>
                            <p class="card-text">
                                <i class="bi bi-diagram-3"></i>
                                Sector: <?= htmlspecialchars($arrivalPlanetDetails['sector']) ?>
                            </p>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- If success is true, display the distance -->
                <div class="col-12 mb-4">
                    <div class="bg-dark border border-secondary rounded p-4">
                        <div class="row text-center">
                            <div class="col-md-4 border-end border-secondary">
                                <div class="px-3">
                                    <i class="bi bi-rulers text-primary mb-2"></i>
                                    <h6 class="text-white-50 mb-2">Total Distance</h6>
                                    <p class="h5 text-white mb-0"><?= number_format($outputData['distance'], 2) ?> km</p>
                                </div>
                            </div>
                            
                            <div class="col-md-4 border-end border-secondary">
                                <div class="px-3">
                                    <i class="bi bi-clock text-warning mb-2"></i>
                                    <h6 class="text-white-50 mb-2">Travel Duration</h6>
                                    <p class="h5 text-white mb-0">
                                        <?= $days ?>d <?= $hours ?>h <?= $minutes ?>m
                                    </p>
                                </div>
                            </div>

                            <?php if (!empty($timePreference) && !empty($selectedTime)): ?>
                                <div class="col-md-4">
                                    <div class="px-3">
                                        <i class="bi bi-calendar-event text-success mb-2"></i>
                                        <?php if ($timePreference === 'departure'): ?>
                                            <h6 class="text-white-50 mb-2">Departure → Arrival</h6>
                                            <p class="text-white mb-0">
                                                <?= (new DateTime($selectedTime))->format('d/m/Y, H:i'); ?>
                                                <i class="bi bi-arrow-right mx-2 text"></i>
                                                <?= $selectedDateTime->format('d/m/Y, H:i'); ?>
                                            </p>
                                        <?php elseif ($timePreference === 'arrival'): ?>
                                            <h6 class="text-white-50 mb-2">Departure → Arrival</h6>
                                            <p class="text-white mb-0">
                                                <?= $selectedDateTime->format('d/m/Y, H:i'); ?>
                                                <i class="bi bi-arrow-right mx-2 text"></i>
                                                <?= (new DateTime($selectedTime))->format('d/m/Y, H:i'); ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>


                <!-- Departure Planet -->
                <div class="col-md-6 mb-4">
                    <div class="card border-success shadow-lg">
                        <div class="card-header text-center text-uppercase text-white bg-success fw-bold">
                            Departure Planet <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center bg-light rounded">
                            <div class="mb-3">
                                <?= planet::getHtmlImage($departurePlanetDetails['id'], 'img-fluid rounded shadow', 150, 150); ?>
                            </div>
                            <h5 class="card-title text-success fw-bold">
                                <?= htmlspecialchars($departurePlanetDetails['name']) ?>
                            </h5>
                            <p class="card-text">
                                <i class="bi bi-geo-alt"></i>
                                Region: <?= htmlspecialchars($departurePlanetDetails['region']) ?>
                            </p>
                            <p class="card-text">
                                <i class="bi bi-diagram-3"></i>
                                Sector: <?= htmlspecialchars($departurePlanetDetails['sector']) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Arrival Planet -->
                <div class="col-md-6 mb-4">
                    <div class="card border-danger shadow-lg">
                        <div class="card-header text-center text-uppercase text-white bg-danger fw-bold">
                            Destination Planet <i class="bi bi-geo-fill"></i>
                        </div>
                        <div class="card-body d-flex flex-column align-items-center bg-light rounded">
                            <div class="mb-3">
                                <?= planet::getHtmlImage($arrivalPlanetDetails['id'], 'img-fluid rounded shadow', 150, 150); ?>
                            </div>
                            <h5 class="card-title text-danger fw-bold">
                                <?= htmlspecialchars($arrivalPlanetDetails['name']) ?>
                            </h5>
                            <p class="card-text">
                                <i class="bi bi-geo-alt"></i>
                                Region: <?= htmlspecialchars($arrivalPlanetDetails['region']) ?>
                            </p>
                            <p class="card-text">
                                <i class="bi bi-diagram-3"></i>
                                Sector: <?= htmlspecialchars($arrivalPlanetDetails['sector']) ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Trip Path -->
                <div class="col-12 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="text-white mb-0">Trip Path</h4>
                        <form action="../scripts/add_to_cart.php" method="POST">
                            <input type="hidden" name="departure_planet_id" value="<?= $departurePlanetId ?>">
                            <input type="hidden" name="arrival_planet_id" value="<?= $arrivalPlanetId ?>">
                            <input type="hidden" name="full_path" value="<?= htmlspecialchars(json_encode($outputData['path'])) ?>">
                            <input type="hidden" name="total_distance" value="<?= $outputData['distance'] ?>">
                            <input type="hidden" name="passengers" value="<?= htmlspecialchars($capacity) ?>">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-cart-plus"></i> Add to Cart
                            </button>
                        </form>
                    </div>
                    <div class="card bg-dark border-secondary">
                        <div class="card-body">
                            <?php
                            for ($i = 0; $i < count($outputData['path']) - 1; $i++):
                                $currentPlanetId = $outputData['path'][$i];
                                $nextPlanetId = $outputData['path'][$i + 1];

                                $stmt->execute([':id' => $currentPlanetId]);
                                $currentPlanet = $stmt->fetch(PDO::FETCH_ASSOC);

                                $stmt->execute([':id' => $nextPlanetId]);
                                $nextPlanet = $stmt->fetch(PDO::FETCH_ASSOC);

                                // Get ship fot this trip
                                $ship_stmt = $cnx->prepare("
                                    SELECT s.* 
                                    FROM ship s 
                                    INNER JOIN trip t ON s.id = t.ship_id 
                                    WHERE t.planet_id = :departure_id 
                                    AND t.destination_planet_id = :arrival_id
                                ");
                                $ship_stmt->execute([
                                    ':departure_id' => $currentPlanetId,
                                    ':arrival_id' => $nextPlanetId
                                ]);
                                $available_ships = $ship_stmt->fetchAll(PDO::FETCH_ASSOC);
                                ?>
                                <div class="row align-items-center mb-4">
                                    <!-- Current Planet -->
                                    <div class="col-5">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-dark p-2 rounded-circle">
                                                <?= planet::getHtmlImage($currentPlanet['id'], 'rounded-circle', 60, 60) ?>
                                            </div>
                                            <div class="ms-3">
                                                <h5 class="mb-1 text-white"><?= htmlspecialchars($currentPlanet['name']) ?></h5>
                                                <small class="text-white-50">
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    <?= htmlspecialchars($currentPlanet['region']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Arrow and Ships -->
                                    <div class="col-2 text-center">
                                        <i class="bi bi-arrow-right text-white fs-4"></i>
                                        <?php if (!empty($available_ships)): ?>
                                            <div class="mt-2">
                                                <?php foreach ($available_ships as $ship): ?>
                                                    <div class="badge bg-secondary mb-1"
                                                         title="Speed: <?= number_format($ship['speed_kmh']) ?> km/h">
                                                        <i class="bi bi-rocket"></i>
                                                        <?= htmlspecialchars($ship['name']) ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Next Planet -->
                                    <div class="col-5">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-dark p-2 rounded-circle">
                                                <?= planet::getHtmlImage($nextPlanet['id'], 'rounded-circle', 60, 60) ?>
                                            </div>
                                            <div class="ms-3">
                                                <h5 class="mb-1 text-white"><?= htmlspecialchars($nextPlanet['name']) ?></h5>
                                                <small class="text-white-50">
                                                    <i class="bi bi-geo-alt me-1"></i>
                                                    <?= htmlspecialchars($nextPlanet['region']) ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($i < count($outputData['path']) - 2): ?>
                                <hr class="border-secondary my-3">
                            <?php endif; ?>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-danger text-center">
            <strong>Planets not found in the database.</strong>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
