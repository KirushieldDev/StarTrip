<?php
// Paramètres de connexion
$host = '127.0.0.1';
$db = 'travia';
$user = 'root'; // Par défaut, l'utilisateur est 'root' sans mot de passe
$pass = ''; // Si vous avez un mot de passe, remplacez-le ici
$charset = 'utf8mb4';

// DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // Création de la connexion PDO
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Lire le fichier JSON
    $jsonData = file_get_contents('planets_details.json');
    $data = json_decode($jsonData, true); // Décoder le JSON en tableau associatif

    // Insérer la planète
    $stmtPlanet = $pdo->prepare("INSERT INTO planets (id, name, image, coord, x, y, SubGridCoord, SubGridX, SubGridY, region, sector, suns, moons, position, distance, LengthDay,LengthYear, diameter, gravity) VALUES (:id, :name, :image, :coord, :x, :y, :subgrid_coord, :subgrid_x, :subgrid_y, :region, :sector, :suns, :moons, :position, :distance, :length_day, :length_year, :diameter, :gravity)");

    $stmtPlanet->execute([
        'id' => $data['Id'],
        'name' => $data['Name'],
        'image' => $data['Image'],
        'coord' => $data['Coord'],
        'x' => $data['X'],
        'y' => $data['Y'],
        'subgrid_coord' => $data['SubGridCoord'],
        'subgrid_x' => $data['SubGridX'],
        'subgrid_y' => $data['SubGridY'],
        'region' => $data['Region'],
        'sector' => $data['Sector'],
        'suns' => $data['Suns'],
        'moons' => $data['Moons'],
        'position' => $data['Position'],
        'distance' => $data['Distance'],
        'length_day' => $data['LengthDay'],
        'length_year' => $data['LengthYear'],
        'diameter' => $data['Diameter'],
        'gravity' => $data['Gravity']
    ]);

    // Insérer les voyages
    foreach ($data['trips'] as $dayType => $trips) {
        foreach ($trips as $trip) {
            $stmtVoyage = $pdo->prepare("INSERT INTO trips (planet_id, destination_planet_id, departure_time, ship_id, day_type) VALUES (:planet_id, :destination_planet_id, :departure_time, :ship_id, :day_type)");
            $stmtVoyage->execute([
                'planet_id' => $data['Id'],
                'destination_planet_id' => $trip['destination_planet_id'][0], // Supposons qu'il y ait un seul ID
                'departure_time' => $trip['departure_time'][0], // Supposons qu'il y ait une seule heure
                'ship_id' => $trip['ship_id'][0], // Supposons qu'il y ait un seul ID de vaisseau
                'day_type' => $dayType
            ]);
        }
    }

    echo "Données importées avec succès!";
} catch (PDOException $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
