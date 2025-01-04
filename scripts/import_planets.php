<?php
/**
    This script imports the planets data from the JSON file and
    inserts them into the database
 */

// Include the Planet class and the database connection one time and must be done
require_once '../configs/config.php';
require_once '../class/planet.php';
global $cnx;
set_time_limit(30000);
ini_set('memory_limit', '256M');

Planet::$planetsList = [];

// Read the JSON data from the file and decode it
$jsonData = file_get_contents('../data/planets.json');
$planetsData = json_decode($jsonData);

// Check if the decoding was successful
if ($planetsData == null) {
    die("Error during decoding the planets data");
}

try {
    Planet::deleteAllTrips($cnx);
    Planet::deleteAllPlanets($cnx);

    // Loop through each planet in the data
    foreach ($planetsData as $planetData) {
        // Create a new Planet object and insert it into the database
        $planet = new planet($planetData);
        $planet->insert($cnx);

        $planetId = $cnx->lastInsertId();

        $planetIds[] = [
            'planet_id' => $planetId,
            'trips' => $planetData->trips
        ];

        Planet::$planetsList[] = $planet; // Add the planet to the list
    }

    foreach ($planetIds as $planetData) {
        $departurePlanetId = $planetData['planet_id'];
        foreach ($planetData['trips'] as $day => $trips) {
            foreach ($trips as $trip) {
                Planet::insertTrip($cnx, $day, $trip, $departurePlanetId);
            }
        }
    }

} catch (Exception $e) {
    echo "Error : " . $e->getMessage();
}