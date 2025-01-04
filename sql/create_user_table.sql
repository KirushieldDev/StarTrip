CREATE TABLE IF NOT EXISTS `user`
(
    `id`         int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT,
    `username`   varchar(50)  NOT NULL UNIQUE,
    `password`   varchar(255) NOT NULL,
    `email`      varchar(100) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `role`       ENUM('user', 'admin') DEFAULT 'user',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;