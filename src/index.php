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

// Get the unique legions from the ship table
try {
    $stmt = $cnx->prepare("SELECT DISTINCT camp FROM ship ORDER BY camp");
    $stmt->execute();
    $legions = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    echo "Error : " . $e->getMessage();
}

// Get the max capacity of ship
try {
    $stmt = $cnx->prepare("SELECT MAX(capacity) AS max_capacity FROM `ship`");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $max_capacity = $result['max_capacity'] ?? 0;
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
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
        <?php if(isset($_SESSION['user'])): ?>
            <p class="text-light">
                Hello <span class="fw-bold"><?= htmlspecialchars($_SESSION['user']['username']) ?></span>, 
                find your next intergalactic journey
            </p>
        <?php else: ?>
            <p class="text-light">Find your next intergalactic journey</p>
        <?php endif; ?>
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

                <!-- Time Selection -->
                <div class="col-md-6">
                    <label for="timePreference" class="form-label fw-semibold">
                        <i class="bi bi-clock-fill text-warning"></i> Time Preference
                    </label>
                    <select id="timePreference" name="timePreference" class="form-select mb-2">
                        <option value="departure">Departure Time</option>
                        <option value="arrival">Arrival Time</option>
                    </select>
                    
                    <div class="input-group">
                        <input type="datetime-local" 
                               id="selectedTime" 
                               name="selectedTime" 
                               class="form-control"
                               min="<?php echo date('Y-m-d\TH:i'); ?>"
                               required>
                    </div>
                </div>

                <!-- Legion Selection -->
                <div class="col-md-6">
                    <label for="legion" class="form-label fw-semibold">
                        <i class="bi bi-shield-fill text-info"></i> Legion
                    </label>
                    <select id="legion" name="legion" class="form-select">
                        <option value="Empty">Not important</option>
                        <?php foreach ($legions as $legion): ?>
                            <option value="<?php echo htmlspecialchars($legion); ?>">
                                <?php echo htmlspecialchars($legion); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Capacity Selection -->
                <div class="col-md-6">
                    <label for="capacity" class="form-label fw-semibold">
                        <i class="bi bi-shield-fill text-info"></i> Number of persons
                    </label>
                    <input type="number" id="capacity" name="capacity" class="form-control" min="1" max="<?php echo $max_capacity; ?>" value="1" placeholder="Enter capacity" required>
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
    <p class="text-light"><i class="bi bi-star-fill text-warning"></i> Powered by K.ELANKEETHAN, V.SANTOS, A.CHAMPAULT, S.COLO <i class="bi bi-star-fill text-warning"></i></p>
</footer>

<script>
    const planetNames = <?php echo json_encode(array_column($planets, 'name')); ?>;
</script>
<script src="../js/autocomplete.js"></script>

</body>
</html>
