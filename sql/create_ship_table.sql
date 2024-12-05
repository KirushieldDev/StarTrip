CREATE TABLE `ship` (
    `id` int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `camp` varchar(255) NOT NULL,
    `speed_kmh` float NOT NULL,
    `capacity` int(11) NOT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;