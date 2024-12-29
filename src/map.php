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
    header("Location: index.php" );
}

// Fetch all planets
$query = $cnx->prepare("SELECT * FROM planet");
$query->execute();
$planets = $query->fetchAll(PDO::FETCH_ASSOC);

$maxX = $maxY = PHP_INT_MIN;
$minX = $minY = PHP_INT_MAX;

$planetData = [];

foreach ($planets as $planet) {
    [$x, $y] = Planet::calculatePosition($planet['x'], $planet['sub_grid_x'], $planet['y'], $planet['sub_grid_y']);
    $planetData[] = [
        'id' => $planet['id'],
        'name' => htmlspecialchars($planet['name']),
        'coordinates' => [$x, $y],
        'diameter' => $planet['diameter'],
        'region' => $planet['region'],
        'imageHtml' => Planet::getHtmlImage($planet['id'])
    ];
    $maxX = max($maxX, $x);
    $maxY = max($maxY, $y);
    $minX = min($minX, $x);
    $minY = min($minY, $y);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Interplanetary Map</title>
    <link rel="stylesheet" href="../css/map.css">
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
            id: <?= $departure['id'] ?>,
            name: "<?= htmlspecialchars($departure['name']) ?>",
            coordinates: [<?= $departureX ?>, <?= $departureY ?>],
            diameter: <?= $departure['diameter'] ?>,
            region: "<?= htmlspecialchars($departure['region']) ?>",
            imageHtml: `<?= Planet::getHtmlImage($departure['id']) ?>`
        },
        arrival: {
            id: <?= $arrival['id'] ?>,
            name: "<?= htmlspecialchars($arrival['name']) ?>",
            coordinates: [<?= $arrivalX ?>, <?= $arrivalY ?>],
            diameter: <?= $arrival['diameter'] ?>,
            region: "<?= htmlspecialchars($arrival['region']) ?>",
            imageHtml: `<?= Planet::getHtmlImage($arrival['id']) ?>`
        },
        allPlanets: <?= json_encode($planetData) ?>
    };
</script>
<script src="../js/map.js"></script>
</body>
</html>
