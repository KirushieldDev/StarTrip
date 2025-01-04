CREATE TABLE IF NOT EXISTS `ticket`
(
    `id`                  int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `user_id`             int(11) UNSIGNED ZEROFILL NOT NULL,
    `departure_planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
    `arrival_planet_id`   int(11) UNSIGNED ZEROFILL NOT NULL,
    `departure_time`      DATETIME                  NOT NULL,
    `arrival_time`        DATETIME                  NOT NULL,
    `ship_id`             int(11) UNSIGNED ZEROFILL NOT NULL,
    `price`               DECIMAL(10, 2)            NOT NULL,
    `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ticket_user` (`user_id`),
    KEY `ticket_departure_planet` (`departure_planet_id`),
    KEY `ticket_arrival_planet` (`arrival_planet_id`),
    KEY `ticket_ship` (`ship_id`),
    CONSTRAINT `ticket_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `ticket_departure_planet` FOREIGN KEY (`departure_planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `ticket_arrival_planet` FOREIGN KEY (`arrival_planet_id`) REFERENCES `planet` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `ticket_ship` FOREIGN KEY (`ship_id`) REFERENCES `ship` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;