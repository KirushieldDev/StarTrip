CREATE TABLE IF NOT EXISTS `cart`
(
    `id`                  int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `user_id`             int(11) UNSIGNED ZEROFILL NOT NULL,
    `departure_planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
    `arrival_planet_id`   int(11) UNSIGNED ZEROFILL NOT NULL,
    `departure_time`      DATETIME                  NOT NULL,
    `arrival_time`        DATETIME                  NOT NULL,
    `price`               DECIMAL(10, 2)            NOT NULL,
    `created_at`          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `passengers`          int(11)                   NOT NULL,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    FOREIGN KEY (`departure_planet_id`) REFERENCES `planet` (`id`),
    FOREIGN KEY (`arrival_planet_id`) REFERENCES `planet` (`id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;