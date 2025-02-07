CREATE TABLE IF NOT EXISTS `planet` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;