-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Oct 13, 2025 at 09:33 AM
-- Server version: 8.0.40
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `MySecretChef`
--

-- --------------------------------------------------------

--
-- Table structure for table `Favorites`
--

CREATE TABLE `Favorites` (
  `user_id` int NOT NULL,
  `recipe_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Ingredient`
--

CREATE TABLE `Ingredient` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Inventory`
--

CREATE TABLE `Inventory` (
  `user_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  `quantity` varchar(16) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `expiration_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Nutrient`
--

CREATE TABLE `Nutrient` (
  `id` int NOT NULL,
  `name` varchar(32) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Recipe`
--

CREATE TABLE `Recipe` (
  `id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `instructions` varchar(500) COLLATE utf8mb4_general_ci NOT NULL,
  `image_url` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `prep_time` smallint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Recipe_Ingredient`
--

CREATE TABLE `Recipe_Ingredient` (
  `recipe_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  `quantity` varchar(16) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `Recipe_Nutrient`
--

CREATE TABLE `Recipe_Nutrient` (
  `recipe_id` int NOT NULL,
  `nutrient_id` int NOT NULL,
  `value` decimal(10,0) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int NOT NULL,
  `username` varchar(30) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(125) COLLATE utf8mb4_general_ci NOT NULL,
  `password` char(60) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Favorites`
--
ALTER TABLE `Favorites`
  ADD PRIMARY KEY (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indexes for table `Ingredient`
--
ALTER TABLE `Ingredient`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `Inventory`
--
ALTER TABLE `Inventory`
  ADD PRIMARY KEY (`user_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `Nutrient`
--
ALTER TABLE `Nutrient`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `Recipe`
--
ALTER TABLE `Recipe`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `Recipe_Ingredient`
--
ALTER TABLE `Recipe_Ingredient`
  ADD PRIMARY KEY (`recipe_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indexes for table `Recipe_Nutrient`
--
ALTER TABLE `Recipe_Nutrient`
  ADD PRIMARY KEY (`recipe_id`,`nutrient_id`),
  ADD KEY `nutrient_id` (`nutrient_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Ingredient`
--
ALTER TABLE `Ingredient`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Nutrient`
--
ALTER TABLE `Nutrient`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `Recipe`
--
ALTER TABLE `Recipe`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Favorites`
--
ALTER TABLE `Favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `Recipe` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Inventory`
--
ALTER TABLE `Inventory`
  ADD CONSTRAINT `inventory_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventory_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `Ingredient` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Recipe_Ingredient`
--
ALTER TABLE `Recipe_Ingredient`
  ADD CONSTRAINT `recipe_ingredient_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `Recipe` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `recipe_ingredient_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `Ingredient` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Recipe_Nutrient`
--
ALTER TABLE `Recipe_Nutrient`
  ADD CONSTRAINT `recipe_nutrient_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `Recipe` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `recipe_nutrient_ibfk_2` FOREIGN KEY (`nutrient_id`) REFERENCES `Nutrient` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
