<?php
global $cnx;
require_once '../configs/config.php';
require_once '../class/planet.php';
include '../include/links.inc.php';
include '../include/navbar.inc.php';

$departurePlanetId = $_GET['departurePlanetId'];
$arrivalPlanetId = $_GET['arrivalPlanetId'];

if ($departurePlanetId && $arrivalPlanetId) {
    $departure = Planet::getPlanetById($departurePlanetId, $cnx);
    $arrival = Planet::getPlanetById($arrivalPlanetId, $cnx);

    if ($departure && $arrival) {
        [$departureX, $departureY] = Planet::calculatePosition($departure['x'], $departure['sub_grid_x'], $departure['y'], $departure['sub_grid_y']);
        [$arrivalX, $arrivalY] = Planet::calculatePosition($arrival['x'], $arrival['sub_grid_x'], $arrival['y'], $arrival['sub_grid_y']);
    } else {
        die("One or both planets not found.");
    }
} else {
    die("Invalid planet IDs.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interplanetary Map</title>
    <style>
        #map {
            height: 600px;
            width: 100%;
            margin: 0 auto;
            border: 2px solid #fff;
        }
    </style>
</head>
<body class="bg-dark text-light">

<div class="container mt-5">
    <div class="d-flex justify-content-between mb-4">
        <a href="../src/index.php" class="btn btn-outline-light">
            <i class="bi bi-arrow-left"></i> Back to Search
        </a>
    </div>

    <div class="text-center">
        <h1 class="fw-bold mb-4">Map: <?= htmlspecialchars($departure['name']) ?>
            → <?= htmlspecialchars($arrival['name']) ?></h1>
    </div>

    <div id="map" class="rounded shadow"></div>
</div>

<script>
    const mapData = {
        departure: {
            name: "<?= htmlspecialchars($departure['name']) ?>",
            coordinates: [<?= $departureX ?>, <?= $departureY ?>]
        },
        arrival: {
            name: "<?= htmlspecialchars($arrival['name']) ?>",
            coordinates: [<?= $arrivalX ?>, <?= $arrivalY ?>]
        }
    };
</script>
<script src="../js/map.js"></script>
</body>
</html>
