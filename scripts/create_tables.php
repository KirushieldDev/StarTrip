<?php
require_once '../configs/config.php';

try {
    // Create planet table if not exists
    $cnx->exec("CREATE TABLE IF NOT EXISTS `planet` (
        `id` int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
        `name` varchar(190) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL,
        `image` varchar(255) DEFAULT NULL,
        `coord` varchar(255) DEFAULT NULL,
        `x` float NOT NULL,
        `y` float NOT NULL,
        `sub_grid_coord` varchar(255) DEFAULT NULL,
        `sub_grid_x` float NOT NULL,
        `sub_grid_y` float NOT NULL,
        `sun_name` varchar(255) DEFAULT NULL,
        `region` varchar(255) NOT NULL,
        `sector` varchar(255) NOT NULL,
        `suns` int(11) NOT NULL,
        `moons` int(11) NOT NULL,
        `position` int(11) NOT NULL,
        `distance` float NOT NULL,
        `length_day` float NOT NULL,
        `length_year` float NOT NULL,
        `diameter` float NOT NULL,
        `gravity` float NOT NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `name` (`name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // Create ship table if not exists
    $cnx->exec("CREATE TABLE IF NOT EXISTS `ship` (
        `id` int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `camp` varchar(255) NOT NULL,
        `speed_kmh` float NOT NULL,
        `capacity` int(11) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // Create trip table if not exists
    $cnx->exec("CREATE TABLE IF NOT EXISTS `trip` (
        `id` int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
        `planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
        `day_of_week` enum('Primeday','Centaxday','Taungsday','Zhellday','Benduday') NOT NULL,
        `destination_planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
        `departure_time` time NOT NULL,
        `ship_id` int(11) UNSIGNED ZEROFILL NOT NULL,
        PRIMARY KEY (`id`),
        KEY `trip_planet` (`planet_id`),
        KEY `trip_ship` (`ship_id`),
        CONSTRAINT `trip_planet` FOREIGN KEY (`planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `trip_ship` FOREIGN KEY (`ship_id`) REFERENCES `ship` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // Create ticket table if not exists
    $cnx->exec("CREATE TABLE IF NOT EXISTS `ticket` (
        `id` int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
        `id_user` int(11) UNSIGNED ZEROFILL NOT NULL,
        `departure_planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
        `arrival_planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
        `ship_id` int(11) UNSIGNED ZEROFILL NOT NULL,
        PRIMARY KEY (`id`),
        KEY `ticket_departure_planet` (`departure_planet_id`),
        KEY `ticket_arrival_planet` (`arrival_planet_id`),
        KEY `ticket_ship` (`ship_id`),
        CONSTRAINT `ticket_departure_planet` FOREIGN KEY (`departure_planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `ticket_arrival_planet` FOREIGN KEY (`arrival_planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `ticket_ship` FOREIGN KEY (`ship_id`) REFERENCES `ship` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    // Create cart table if not exists
    $cnx->exec("CREATE TABLE IF NOT EXISTS `cart` (
        `id` int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
        `id_user` int(11) UNSIGNED ZEROFILL NOT NULL,
        `departure_planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
        `arrival_planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
        `ship_id` int(11) UNSIGNED ZEROFILL NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `cart_departure_planet` (`departure_planet_id`),
        KEY `cart_arrival_planet` (`arrival_planet_id`),
        KEY `cart_ship` (`ship_id`),
        CONSTRAINT `cart_departure_planet` FOREIGN KEY (`departure_planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `cart_arrival_planet` FOREIGN KEY (`arrival_planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT `cart_ship` FOREIGN KEY (`ship_id`) REFERENCES `ship` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    echo "All tables have been created successfully!\n";

} catch(PDOException $e) {
    echo "Error creating tables: " . $e->getMessage() . "\n";
} 