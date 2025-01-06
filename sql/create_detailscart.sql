-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 05 jan. 2025 à 15:49
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT = @@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS = @@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION = @@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `startrip`
--

-- --------------------------------------------------------

--
-- Structure de la table `detailscart`
--

CREATE TABLE IF NOT EXISTS `detailscart`
(
    `id`                  int(11) UNSIGNED ZEROFILL NOT NULL,
    `id_cart`             int(11) UNSIGNED ZEROFILL NOT NULL,
    `user_id`             int(11) UNSIGNED ZEROFILL NOT NULL,
    `departure_planet_id` int(11) UNSIGNED ZEROFILL NOT NULL,
    `arrival_planet_id`   int(11) UNSIGNED ZEROFILL NOT NULL,
    `departure_time`      datetime                  NOT NULL,
    `arrival_time`        datetime                  NOT NULL,
    `ship_id`             int(11) UNSIGNED ZEROFILL NOT NULL,
    `price`               decimal(10, 2)            NOT NULL,
    `created_at`          timestamp                 NOT NULL DEFAULT current_timestamp(),
    `passengers`          int(11)                   NOT NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `detailscart`
--
ALTER TABLE `detailscart`
    ADD PRIMARY KEY (`id`),
    ADD KEY `id_cart` (`id_cart`),
    ADD KEY `user_id` (`user_id`),
    ADD KEY `departure_planet_id` (`departure_planet_id`),
    ADD KEY `arrival_planet_id` (`arrival_planet_id`),
    ADD KEY `ship_id` (`ship_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `detailscart`
--
ALTER TABLE `detailscart`
    MODIFY `id` int(11) UNSIGNED ZEROFILL NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `detailscart`
--
ALTER TABLE `detailscart`
    ADD CONSTRAINT `detailscart_ibfk_1` FOREIGN KEY (`id_cart`) REFERENCES `cart` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `detailscart_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
    ADD CONSTRAINT `detailscart_ibfk_3` FOREIGN KEY (`departure_planet_id`) REFERENCES `planet` (`id`),
    ADD CONSTRAINT `detailscart_ibfk_4` FOREIGN KEY (`arrival_planet_id`) REFERENCES `planet` (`id`),
    ADD CONSTRAINT `detailscart_ibfk_5` FOREIGN KEY (`ship_id`) REFERENCES `ship` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT = @OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS = @OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION = @OLD_COLLATION_CONNECTION */;
