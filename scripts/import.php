<?php

use class\Planet;
use class\Trip;

require 'DatabaseHandler.php'; // Assurez-vous que le DatabaseHandler est bien requis
require 'class/Planet.php'; // Chemin vers la classe Planet
require 'class/Trip.php'; // Chemin vers la classe Trip

// Paramètres de connexion à la base de données
$host = '127.0.0.1';
$db = 'travia';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Créer une instance du gestionnaire de base de données
$databaseHandler = new DatabaseHandler($host, $db, $user, $pass);
$pdo = $databaseHandler->getConnection();

// Vider les tables avant l'insertion
$databaseHandler->clearTables();
ini_set('memory_limit', '256M'); // Ou une valeur supérieure si nécessaire

// Lire le fichier JSON
$jsonData = file_get_contents(__DIR__ . '/planets_details.json');
$data = json_decode($jsonData, true); // Décoder le JSON en tableau associatif

// Vérifiez si le JSON est valide
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "Erreur lors de la lecture du fichier JSON: " . json_last_error_msg();
    exit();
}

// Insérer chaque planète
foreach ($data as $planetData) {
    $planet = new Planet($planetData);

    // Préparez l'instruction d'insertion pour la table Planets
    $stmtPlanet = $pdo->prepare("INSERT INTO Planets (Id, Name, Image, Coord, X, Y, SubGridCoord, SubGridX, SubGridY, Region, Sector, Suns, Moons, Position, Distance, LengthDay, LengthYear, Diameter, Gravity) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmtPlanet->execute([
        $planet->Id,
        $planet->Name,
        $planet->Image,
        $planet->Coord,
        $planet->X,
        $planet->Y,
        $planet->SubGridCoord,
        $planet->SubGridX,
        $planet->SubGridY,
        $planet->Region,
        $planet->Sector,
        $planet->Suns,
        $planet->Moons,
        $planet->Position,
        $planet->Distance,
        $planet->LengthDay,
        $planet->LengthYear,
        $planet->Diameter,
        $planet->Gravity
    ]);

    // Insérer les voyages pour cette planète
    foreach ($planetData['trips'] as $dayType => $trips) {
        foreach ($trips as $trip) {
            $tripObj = new Trip($planet->Id, $trip, $dayType);
            $stmtTrip = $pdo->prepare("INSERT INTO trips (planet_id, destination_planet_id, departure_time, ship_id, day_type) VALUES (?, ?, ?, ?, ?)");
            $stmtTrip->execute([
                $tripObj->PlanetId,
                $tripObj->DestinationPlanetId,
                $tripObj->DepartureTime,
                $tripObj->ShipId,
                $tripObj->DayType
            ]);
        }
    }

    // Libérer la mémoire pour la planète actuelle
    unset($planet);
}

// Libérer la mémoire pour le tableau des données
unset($data);

echo "Données importées avec succès!";
?>
