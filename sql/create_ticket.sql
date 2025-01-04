CREATE TABLE `ticket`
(
    `id`                  int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `id_user`             int(11) UNSIGNED ZEROFILL NOT NULL,
    `departure_planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
    `arrival_planet_id`   int(11) UNSIGNED ZEROFILL NOT NULL,
    `ship_id`             int(11) UNSIGNED ZEROFILL NOT NULL,
    PRIMARY KEY (`id`),
    KEY `ticket_departure_planet` (`departure_planet_id`),
    KEY `ticket_arrival_planet` (`arrival_planet_id`),
    KEY `ticket_ship` (`ship_id`),
    CONSTRAINT `ticket_departure_planet` FOREIGN KEY (`departure_planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `ticket_arrival_planet` FOREIGN KEY (`arrival_planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `ticket_ship` FOREIGN KEY (`ship_id`) REFERENCES `ship` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;