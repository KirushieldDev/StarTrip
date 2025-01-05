<?php
global $cnx;
require_once '../configs/config.php';

try {
    // Read and execute SQL files
    $sqlFiles = [
        '../sql/create_user_table.sql',
        '../sql/create_ship_table.sql',
        '../sql/create_planet_table.sql',
        '../sql/create_logs.sql',
        '../sql/create_trip_table.sql',
        '../sql/create_cart.sql',
        '../sql/create_detailscart.sql',
        '../sql/create_ticket.sql'
    ];

    foreach ($sqlFiles as $file) {
        if (file_exists($file)) {
            $sql = file_get_contents($file);
            if ($sql !== false) {
                $cnx->exec($sql);
                echo "Table created from " . basename($file) . " successfully!<br>";
            } else {
                echo "Error reading file: " . basename($file) . "<br>";
            }
        } else {
            echo "File not found: " . basename($file) . "<br>";
        }
    }

    echo "\nAll tables have been created successfully!\n";

} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
} 