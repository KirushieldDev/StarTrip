<?php
/**
    This script imports the ships data from the JSON file and
    inserts them into the database
 */

// Include the Ship class and the database connection one time and must be done
require_once '../class/ship.php';
require_once '../configs/config.php';
global $cnx;

// Read the JSON data from the file and decode it
$jsonData = file_get_contents('../data/ships.json');
$shipsData = json_decode($jsonData, true);

// Check if the decoding was successful
if ($shipsData == null) {
    die("Error during decoding the ships data");
}

try {
    Ship::deleteAllShips($cnx);

    // Loop through each ship in the data
    foreach ($shipsData as $shipData) {
        // Create a new Ship object and insert it into the database
        $ship = new Ship($shipData);
        $ship->insert($cnx);
    }
} catch (Exception $e) {
    echo "Error : " . $e->getMessage();
}