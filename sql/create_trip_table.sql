CREATE TABLE IF NOT EXISTS `trip` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
