CREATE TABLE IF NOT EXISTS `detailsticket`
(
    `id`                  int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `id_ticket`           int(11) UNSIGNED ZEROFILL NOT NULL,
    `user_id`             int(11) UNSIGNED ZEROFILL NOT NULL,
    `departure_planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
    `arrival_planet_id`   int(11) UNSIGNED ZEROFILL NOT NULL,
    `departure_time`      DATETIME                  NOT NULL,
    `arrival_time`        DATETIME                  NOT NULL,
    `ship_id`             int(11) UNSIGNED ZEROFILL NOT NULL,
    `price`               DECIMAL(10, 2)            NOT NULL,
    `passengers`          int(11)                   NOT NULL,
    PRIMARY KEY (`id`),
    KEY `detailsticket_ticket` (`id_ticket`),
    KEY `detailsticket_user` (`user_id`),
    KEY `detailsticket_departure_planet` (`departure_planet_id`),
    KEY `detailsticket_arrival_planet` (`arrival_planet_id`),
    KEY `detailsticket_ship` (`ship_id`),
    CONSTRAINT `detailsticket_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `ticket` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `detailsticket_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `detailsticket_departure_planet` FOREIGN KEY (`departure_planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `detailsticket_arrival_planet` FOREIGN KEY (`arrival_planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `detailsticket_ship` FOREIGN KEY (`ship_id`) REFERENCES `ship` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;