<?php
require_once '../configs/config.php';
include('../include/links.inc.php');
global $cnx;

function insertLog($cnx, $sqlQuery) {
    try {
        $stmt = $cnx->prepare("INSERT INTO logs (description, date) VALUES (:description, NOW())");
        $stmt->bindParam(':description', $sqlQuery);
        $stmt->execute();
    } catch (Exception $e) {
        echo 'Error: ' . $e->getMessage();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departurePlanet = trim($_POST['departurePlanet'] ?? '');
    $arrivalPlanet = trim($_POST['arrivalPlanet'] ?? '');
    $legion = trim($_POST['legion'] ?? '');
    $capacity = trim($_POST['capacity'] ?? '');

    if (!empty($departurePlanet) && !empty($arrivalPlanet)) {
        if (strtolower($departurePlanet) === strtolower($arrivalPlanet)) {
            // Cas où les deux planètes sont identiques
            $errorMessage = "The departure planet and the destination planet cannot be the same.";
        } else {
            try {
                // Check if the planets exists
                $stmt = $cnx->prepare("SELECT id FROM planet WHERE name = :name");
                $stmt->bindParam(':name', $departurePlanet);
                $stmt->execute();
                $departurePlanetId = $stmt->fetchColumn();

                $stmt->bindParam(':name', $arrivalPlanet);
                $stmt->execute();
                $arrivalPlanetId = $stmt->fetchColumn();

                if ($departurePlanetId && $arrivalPlanetId) {
                    $sqlQuery = "SELECT * FROM trip WHERE planet_id = $departurePlanetId AND destination_planet_id = $arrivalPlanetId";
                    insertLog($cnx, $sqlQuery);

                    echo "
                    <form id='redirectForm' action='../exec.php' method='POST'>
                        <input type='hidden' name='departurePlanetId' value='$departurePlanetId'>
                        <input type='hidden' name='arrivalPlanetId' value='$arrivalPlanetId'>
                        <input type='hidden' name='departurePlanet' value='" . htmlspecialchars($departurePlanet) . "'>
                        <input type='hidden' name='arrivalPlanet' value='" . htmlspecialchars($arrivalPlanet) . "'>
                        <input type='hidden' name='legion'  value='" . htmlspecialchars($legion) . "'>
                        <input type='hidden' name='capacity'  value='" . htmlspecialchars($capacity) . "'>
                    </form>
                    <script>document.getElementById('redirectForm').submit();</script>";
                    exit();
                } else {
                    $errorMessage = "One or both of the planets do not exist in the database.";
                }
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
            }
        }
    } else {
        $errorMessage = "Please type a departure planet and a destination planet.";
    }
}
?>

<?php if (isset($errorMessage)): ?>
    <!-- Modal -->
    <div class="modal fade" id="errorModal" tabindex="-1" aria-labelledby="errorModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger" id="errorModalLabel">Error</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?= htmlspecialchars($errorMessage) ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Show the modal after the page loads
        var myModal = new bootstrap.Modal(document.getElementById('errorModal'), {
            keyboard: false
        });
        myModal.show();

        // Redirect to index.php after the modal is closed
        document.getElementById('errorModal').addEventListener('hidden.bs.modal', function () {
            window.location.href = '../src/index.php';
        });
    </script>
<?php endif; ?>
