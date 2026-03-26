-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 26 mars 2026 à 17:26
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `php_exam_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
  `article_id` int(11) NOT NULL,
  `article_name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` float NOT NULL,
  `publication_date` date NOT NULL,
  `autor_id` int(11) NOT NULL,
  `article_image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`article_id`, `article_name`, `description`, `price`, `publication_date`, `autor_id`, `article_image`) VALUES
(1, 'Ordinateur Portable Pro', 'Un PC ultra puissant pour le développement Laravel.', 1250, '2026-03-18', 1, 'laptop.jpg'),
(2, 'Écran 27 pouces 4K', 'Une résolution incroyable pour ne rater aucun point-virgule.', 349.99, '2026-03-17', 1, 'monitor.jpg'),
(3, 'Clavier Mécanique RGB', 'Parfait pour coder toute la nuit avec style.', 89.5, '2026-03-16', 6, 'keyboard.jpg'),
(4, 'Souris Ergonomique', 'Prenez soin de votre poignet pendant vos sessions de debug.', 55, '2026-03-15', 6, 'mouse.jpg'),
(5, 'Livre : Maîtriser Eloquent', 'Le guide ultime pour comprendre les modèles Laravel.', 29.9, '2026-03-14', 1, 'book.jpg'),
(6, 'Un écran 4k', 'Ecran parfait pour le gaming', 200, '2026-03-21', 6, 'article_69be558d80faf.jpg'),
(7, 'Un beau live', 'parfait pour la lecture', 20, '2026-03-24', 6, 'article_69c25be18f5c1.jpg'),
(8, 'Un beau live', 'parfait pour la lecture', 20, '2026-03-24', 6, 'article_69c25c0c29b0f.jpg'),
(9, 'Un guerrier très fort', 'Il a deux épées', 1, '2026-03-26', 6, 'article_69c544f2caaa8.png');

-- --------------------------------------------------------

--
-- Structure de la table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `article_number` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `invoice`
--

CREATE TABLE `invoice` (
  `invoice_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `amount` float NOT NULL,
  `invoice_address` varchar(255) NOT NULL,
  `invoice_city` varchar(255) NOT NULL,
  `postal_code` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `invoice`
--

INSERT INTO `invoice` (`invoice_id`, `user_id`, `transaction_date`, `amount`, `invoice_address`, `invoice_city`, `postal_code`) VALUES
(1, 6, '2026-03-25', 55, '19 Avenue Philippe Lebas', 'Frévent', 62270),
(2, 6, '2026-03-25', 7500, '19 Avenue Philippe Lebas', 'Frévent', 62270),
(3, 1, '2026-03-26', 180, '19 Avenue Philippe Lebas', 'Frévent', 62270),
(4, 1, '2026-03-26', 1250, '19 Avenue Philippe Lebas', 'Frévent', 62270),
(5, 1, '2026-03-26', 20, '19 Avenue Philippe Lebas', 'Frévent', 62270),
(6, 1, '2026-03-26', 20, '19 Avenue Philippe Lebas', 'Frévent', 62270),
(7, 1, '2026-03-26', 100, '19 Avenue Philippe Lebas', 'Frévent', 62270),
(8, 6, '2026-03-26', 5, '19 Avenue Philippe Lebas', 'Frévent', 62270);

-- --------------------------------------------------------

--
-- Structure de la table `stock`
--

CREATE TABLE `stock` (
  `stock_id` int(11) NOT NULL,
  `article_id` int(11) NOT NULL,
  `actual_stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `stock`
--

INSERT INTO `stock` (`stock_id`, `article_id`, `actual_stock`) VALUES
(1, 7, 0),
(2, 8, 5),
(3, 1, 10),
(4, 2, 10),
(5, 3, 10),
(6, 4, 10),
(7, 5, 10),
(8, 6, 10),
(10, 9, 0);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `solde` float NOT NULL,
  `profile_picture` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`user_id`, `username`, `password`, `email`, `solde`, `profile_picture`, `role`) VALUES
(1, 'admin', '$2y$10$KUISt/NHTo.WY6RceHJ1bOqSKKLec1qSvma7itMHNDJzXlGmeqLZe', 'admin@mail.com', 5e26, 'default.jpg', 'admin'),
(6, 'aZeDenBis', '$2y$10$Dy7K/Isc/4a4P9hvvAMPjuecjnRIDn75MHYcmJiMLc5kiDkKIuFJK', 'lucasgosselin3101@gmail.com', 99995500, 'user_69be551089414.jpg', 'admin');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`article_id`);

--
-- Index pour la table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD KEY `user_id` (`user_id`,`article_id`),
  ADD KEY `article_id` (`article_id`);

--
-- Index pour la table `invoice`
--
ALTER TABLE `invoice`
  ADD PRIMARY KEY (`invoice_id`);

--
-- Index pour la table `stock`
--
ALTER TABLE `stock`
  ADD PRIMARY KEY (`stock_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
  MODIFY `article_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT pour la table `invoice`
--
ALTER TABLE `invoice`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `stock`
--
ALTER TABLE `stock`
  MODIFY `stock_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `article` (`article_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_id_cart` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
