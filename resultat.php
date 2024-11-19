<?php
global $pdo;
include "config.php"; // Inclusion de la configuration de la base de données
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
          integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY="
          crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
            crossorigin=""></script>
    <title>Trivia</title>
</head>
<body>
<?php include "header.php";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['planet_search']) && isset($_POST['planet_search2'])) {
        $planet_depart = $_POST['planet_search'];
        $planet_arrivee = $_POST['planet_search2'];

        // Préparer la requête pour récupérer les coordonnées de la planète de départ
        $stmt_depart = $pdo->prepare("SELECT SubgridX, SubgridY, Image FROM planets WHERE Name = ?");
        $stmt_depart->execute([$planet_depart]);
        $depart_coordinates = $stmt_depart->fetch();

        // Préparer la requête pour récupérer les coordonnées de la planète d'arrivée
        $stmt_arrivee = $pdo->prepare("SELECT SubgridX, SubgridY,Image FROM planets WHERE Name = ?");
        $stmt_arrivee->execute([$planet_arrivee]);
        $arrivee_coordinates = $stmt_arrivee->fetch();

        if ($depart_coordinates && $arrivee_coordinates) {
            // Extraire SubgridX et SubgridY
            $subgridX_depart = (float) $depart_coordinates['SubgridX'];
            $subgridY_depart = (float) $depart_coordinates['SubgridY'];
            $image_depart = $depart_coordinates['Image'];

            $subgridX_arrivee = (float) $arrivee_coordinates['SubgridX'];
            $subgridY_arrivee = (float) $arrivee_coordinates['SubgridY'];
            $image_arrivee = $arrivee_coordinates['Image'];

            // Calculer la distance à vol d'oiseau
            $distance = sqrt(pow($subgridX_arrivee - $subgridX_depart, 2) + pow($subgridY_arrivee - $subgridY_depart, 2));


            $distance_km = $distance * pow(10, 9); // Conversion en km
            // Préparer la requête pour récupérer les vaisseaux et leur vitesse
            $ship_stmt = $pdo->prepare("SELECT id, name, speed_kmh FROM ships");
            $ship_stmt->execute(); // Execute la requête
            $ships_info = $ship_stmt->fetchAll();

            echo "<div class='list'> ";
            echo "<p>La distance est de " . number_format($distance, 2) . " Milliards de Km</p>";


// Récupérer les coordonnées des planètes depuis la base de données
            $planet_stmt = $pdo->prepare("SELECT Name, SubGridX, SubGridY,X, Y, Region FROM planets");
            $planet_stmt->execute();
            $planets = $planet_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

            <div class="présentation">
                <div class="planet-images">
                    <!-- Image de la planète de départ -->
                    <div class="planet-image" id="planet-depart">
                        <h3>Planète de départ</h3>
                        <img src="<?php echo htmlspecialchars($image_depart ?? 'images/default_planet.png'); ?>"
                             alt="Planète de départ" id="img-depart">
                    </div>

                    <div id="map"></div>

                    <!-- Image de la planète d'arrivée -->
                    <div class="planet-image" id="planet-arrivee">
                        <h3>Planète d'arrivée</h3>
                        <img src="<?php echo htmlspecialchars($image_arrivee ?? 'images/default_planet.png'); ?>"
                             alt="Planète d'arrivée" id="img-arrivee">
                    </div>
                </div>
            </div>

<?php
            // Ajouter les données des planètes en JSON pour JavaScript
            echo '<script>';
            echo 'var planets = ' . json_encode($planets) . ';';
            include "map.js";
            echo '</script>';


            foreach ($ships_info as $ship) {
                $speed_kmh = $ship['speed_kmh'];

                // Calculer la durée en heures
                $duration_hours = (float) $distance_km / $speed_kmh; // distance en km / vitesse en km/h
                $hours = floor($duration_hours);
                $minutes = round(($duration_hours - $hours) * 60);

                echo '<div class="ship"> 
            <h2>Vaisseau : ' . htmlspecialchars($ship['name']) . '</h2>
            <p>- Durée : ' . $hours . 'h ' . $minutes . 'm</p>
          </div>';
            }
        echo "</div>";
        } else {
            echo "Erreur : Une ou plusieurs planètes non trouvées.";
        }
    } else {
        echo "Erreur : Les planètes de départ et d'arrivée doivent être spécifiées.";
    }
} else {
    echo "Erreur : Méthode de requête invalide.";
}
?>

</body>
</html>