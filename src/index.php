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
                               class="form-control" placeholder="Search for a planet"
                               required autocomplete="off" data-bs-toggle="dropdown">
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
                               class="form-control" placeholder="Search for a planet"
                               required autocomplete="off" data-bs-toggle="dropdown">
                        <ul id="arrivalSuggestions" class="dropdown-menu w-100"></ul>
                    </div>
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
    const planetNames = <?php echo json_encode($planets); ?>.map(planet => planet.name);
</script>

<script src="../js/autocomplete.js"></script>
</body>
</html>
