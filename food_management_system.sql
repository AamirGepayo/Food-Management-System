-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2026 at 03:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `food_management_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `food_management`
--

CREATE TABLE `food_management` (
  `id` int(11) NOT NULL,
  `dishes` varchar(50) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price` varchar(50) DEFAULT NULL,
  `expiration_date` varchar(50) DEFAULT NULL,
  `stock` varchar(50) DEFAULT NULL,
  `image` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_management`
--

INSERT INTO `food_management` (`id`, `dishes`, `category`, `price`, `expiration_date`, `stock`, `image`) VALUES
(1, 'Chocolate Cake', 'Dessert', '185', '2026-05-25', '20', 'assets/img/dish_6a01338305f5b.jpg'),
(2, 'Chicken Adobo', 'Main Course', '220', '2026-05-16', '20', 'assets/img/dish_6a013450ad3bd.webp'),
(3, 'Halo-Halo', 'Dessert', '95', '2026-05-31', '20', 'assets/img/dish_6a0135d7230d9.webp'),
(4, 'Sinigang na Baboy', 'Soup', '210', '2026-05-13', '20', 'assets/img/dish_6a013532ad4ea.webp'),
(5, 'Lumpia Shanghai', 'Appetizer', '65', '2026-05-18', '20', 'assets/img/dish_6a01358d3b293.webp'),
(6, 'Mango Shake', 'Beverage', '65', '2026-05-13', '20', 'assets/img/dish_6a0136530fbfc.webp');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `food_management`
--
ALTER TABLE `food_management`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `food_management`
--
ALTER TABLE `food_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;