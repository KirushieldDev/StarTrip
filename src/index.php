<?php
require_once '../configs/config.php';
include('../include/links.inc.php');
include('../include/navbar.inc.php');
global $cnx;

try {
    $stmt = $cnx->prepare("SELECT id, name FROM planet");
    $stmt->execute();
    $planets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Error : " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search for a Trip</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/home.css">
</head>
<body class="bg-dark">

<div class="container mt-5">
    <!-- Title Section -->
    <div class="text-center mb-5">
        <h1 class="fw-bold text-white mb-3">
            <i class="bi bi-rocket-takeoff-fill text-primary"></i> Search for a Trip
            <i class="bi bi-rocket-takeoff-fill text-primary"></i>
        </h1>
        <p class="text-light">Find your next intergalactic journey</p>
    </div>

    <!-- Form Section -->
    <div class="card shadow-sm bg-body-secondary">
        <div class="card-body">
            <form id="searchForm" class="row g-4" method="post" action="../scripts/insert_logs.php">
                <!-- Departure Planet -->
                <div class="col-md-6">
                    <label for="departurePlanet" class="form-label fw-semibold">
                        <i class="bi bi-geo-alt-fill text-success"></i> Departure Planet
                    </label>
                    <div class="input-group">
                        <input type="text" id="departurePlanet" name="departurePlanet"
                               class="form-control" placeholder="Type a planet name"
                               required autocomplete="off">
                        <ul id="departureSuggestions" class="dropdown-menu w-100"></ul>
                    </div>
                </div>

                <!-- Arrival Planet -->
                <div class="col-md-6">
                    <label for="arrivalPlanet" class="form-label fw-semibold">
                        <i class="bi bi-geo-fill text-danger"></i> Destination Planet
                    </label>
                    <div class="input-group">
                        <input type="text" id="arrivalPlanet" name="arrivalPlanet"
                               class="form-control" placeholder="Type a planet name"
                               required autocomplete="off">
                        <ul id="arrivalSuggestions" class="dropdown-menu w-100"></ul>
                    </div>
                </div>

                <!-- Day of the Week -->
                <div class="col-md-6">
                    <label for="dayOfWeek" class="form-label fw-semibold">
                        <i class="bi bi-calendar3 text-info"></i> Day of the Week
                    </label>
                    <select id="dayOfWeek" name="dayOfWeek" class="form-select">
                        <option value="no-preference" selected>No Preference</option>
                        <option value="Primeday">Primeday</option>
                        <option value="Centaxday">Centaxday</option>
                        <option value="Taungsday">Taungsday</option>
                        <option value="Zhellday">Zhellday</option>
                        <option value="Benduday">Benduday</option>
                    </select>
                </div>

                <!-- Ship -->
                <div class="col-md-6">
                    <label for="ship" class="form-label fw-semibold">
                        <i class="bi bi-rocket-fill text-warning"></i> Ship
                    </label>
                    <select id="ship" name="ship" class="form-select">

                        <?php
                        try {
                            $stmt = $cnx->prepare("SELECT name FROM ship");
                            $stmt->execute();
                            $ships = $stmt->fetchAll(PDO::FETCH_ASSOC);
                        } catch (Exception $e) {
                            echo "Error: " . $e->getMessage();
                        }
                        ?>
                        <option value="no-preference" selected>No Preference</option>
                        <?php foreach ($ships as $ship): ?>
                            <option value="<?= htmlspecialchars($ship['name']) ?>"><?= htmlspecialchars($ship['name']) ?></option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <!-- Planet Alignment -->
                <div class="col-md-6">
                    <label for="planetAlignment" class="form-label fw-semibold">
                        <i class="bi bi-flag-fill text-primary"></i> Planet Alignment
                    </label>
                    <select id="planetAlignment" name="planetAlignment" class="form-select">
                        <option value="no-preference" selected>No Preference</option>
                        <option value="Republic">Republic</option>
                        <option value="Empire">Empire</option>
                        <option value="Neutral">Neutral</option>
                    </select>
                </div>

                <!-- Travel Type -->
                <div class="col-md-6">
                    <label for="travelType" class="form-label fw-semibold">
                        <i class="bi bi-arrow-right-circle-fill text-success"></i> Travel Type
                    </label>
                    <select id="travelType" name="travelType" class="form-select">
                        <option value="direct" selected>Direct Flight</option>
                        <option value="nearest-neighbor">Nearest Neighbor</option>
                        <option value="shortest-path">Shortest Path</option>
                    </select>
                </div>

                <!-- Submit Button -->
                <div class="col-12 text-center">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="mt-5 text-center text-muted">
    <p class="text-light"><i class="bi bi-star-fill text-warning"></i> Powered by ELANKEETHAN Kirushikesan <i class="bi bi-star-fill text-warning"></i></p>
</footer>

<script>
    const planetNames = <?php echo json_encode(array_column($planets, 'name')); ?>;
</script>
<script src="../js/autocomplete.js"></script>

</body>
</html>
