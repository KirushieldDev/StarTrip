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
$selectedShipId = $_POST['shipId'] ?? null;

try {
    $stmt = $cnx->prepare("SELECT * FROM planet WHERE id = :id");

    $stmt->bindParam(':id', $departurePlanetId);
    $stmt->execute();
    $departurePlanetDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    $stmt->bindParam(':id', $arrivalPlanetId);
    $stmt->execute();
    $arrivalPlanetDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($departurePlanetDetails && $arrivalPlanetDetails) {
        $departureX = ($departurePlanetDetails['x'] + $departurePlanetDetails['sub_grid_x']) * 6;
        $departureY = ($departurePlanetDetails['y'] + $departurePlanetDetails['sub_grid_y']) * 6;
        $arrivalX = ($arrivalPlanetDetails['x'] + $arrivalPlanetDetails['sub_grid_x']) * 6;
        $arrivalY = ($arrivalPlanetDetails['y'] + $arrivalPlanetDetails['sub_grid_y']) * 6;

        $distance = sqrt(pow($arrivalX - $departureX, 2) + pow($arrivalY - $departureY, 2));
        $distance_km = $distance * pow(10, 9);

        $ship_stmt = $cnx->prepare("SELECT id, name, speed_kmh FROM ship");
        $ship_stmt->execute();
        $ships = $ship_stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($selectedShipId) {
            $ship_stmt = $cnx->prepare("SELECT name, speed_kmh FROM ship WHERE id = :id");
            $ship_stmt->bindParam(':id', $selectedShipId);
            $ship_stmt->execute();
            $selectedShip = $ship_stmt->fetch(PDO::FETCH_ASSOC);

            if ($selectedShip) {
                $speed_kmh = $selectedShip['speed_kmh'];
                $duration_hours = $distance_km / $speed_kmh;
                $hours = floor($duration_hours);
                $minutes = round(($duration_hours - $hours) * 60);
            }
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results</title>
    <link rel="stylesheet" href="../css/result.css">
    <script>
        function submitForm() {
            document.getElementById("shipForm").submit();
        }
    </script>
</head>
<body class="bg-dark text-light">
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <a href="../src/index.php" class="btn btn-outline-light">
            <i class="bi bi-arrow-left"></i> Back to Search
        </a>

        <?php if ($departurePlanetDetails && $arrivalPlanetDetails): ?>
            <form action="map.php" method="GET" class="m-0">
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
            <div class="col-12 mb-3">
                <div class="alert alert-success text-center">
                    <h4>Distance :</h4>
                    <p class="fs-5"><?= number_format($distance_km, 2) ?> km</p>
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
        </div>

        <!-- Ship Selector -->
        <div class="col-12 mb-4">
            <form action="" method="POST" id="shipForm" class="text-center">
                <input type="hidden" name="departurePlanetId" value="<?= htmlspecialchars($departurePlanetId) ?>">
                <input type="hidden" name="arrivalPlanetId" value="<?= htmlspecialchars($arrivalPlanetId) ?>">

                <select name="shipId" id="shipId" class="form-select d-inline-block w-auto" onchange="submitForm()">
                    <option value="" selected disabled>Select a ship</option>
                    <?php foreach ($ships as $ship): ?>
                        <option value="<?= $ship['id'] ?>" <?= $selectedShipId == $ship['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($ship['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <!-- Display Selected Ship and Duration -->
        <?php if (!empty($selectedShip)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center mb-3">
                    <h4><?= htmlspecialchars($selectedShip['name']) ?></h4>
                    <p class="fs-5">
                        Travel Time: <?= $hours ?> hours <?= $minutes ?> minutes
                    </p>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-danger text-center">
            <strong>Planets not found in the database.</strong>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
