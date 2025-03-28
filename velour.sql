-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2025 at 03:39 PM
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
-- Database: `velour`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `orderID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `orderList` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `totalPrice` int(10) NOT NULL,
  `orderDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','completed','cancelled') NOT NULL,
  `deliveryAddress` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `productID` int(11) NOT NULL,
  `productName` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL,
  `productImage` varchar(255) NOT NULL,
  `price` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`productID`, `productName`, `category`, `quantity`, `productImage`, `price`) VALUES
(1, 'Stronger With You Intensely', 'Armani Line-up', 20, 'stronger_with_you_intensely.png', 7000),
(2, 'Stronger With You Absolutely', 'Armani Line-up', 20, 'stronger_with_you_absolutely.png', 7000),
(3, 'Stronger With You', 'Armani Line-up', 20, 'stronger_with_you.png', 7000),
(4, 'Aqua de Gio Parfum', 'Armani Line-up', 20, 'acqua_di_gio.png', 10000),
(5, 'Aqua de Gio Parfum', 'Armani Line-up', 20, 'acqua_di_gio_profondo.png', 10000),
(6, 'Ultra Male', 'Jean Paul Gaultier Line-up', 20, 'ultra_male.png', 6500),
(8, 'Le Male Elixir', 'Jean Paul Gaultier Line-up', 20, 'le_male_elixir.png', 6500),
(9, 'Le Beau', 'Jean Paul Gaultier Line-up', 20, 'le_beau.png', 6500),
(10, 'Le Beau Le Parfum', 'Jean Paul Gaultier Line-up', 20, 'le_beau_le_parfum.png', 6500),
(11, 'Aventus', 'Creed Line-up', 20, 'aventus.png', 20000),
(12, 'Silver Mountain Water', 'Creed Line-up', 20, 'silver_mountain_water.png', 20000),
(13, 'Virgin Island Water', 'Creed Line-up', 20, 'virgin_island_water.png', 20000),
(14, 'Green Irish Tweed', 'Creed Line-up', 20, 'green_irish_tweed.png', 20000),
(15, 'Millesime Imperial', 'Creed Line-up', 20, 'millesime_imperial.png', 20000),
(16, 'Le Male Parfum', 'Jean Paul Gaultier Line-up', 20, 'le_male_parfum.png', 6500),
(17, 'Stronger With You Parfum', 'Armani Line-up', 20, 'stronger_with_you_parfum.jpg', 7000);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('client','admin') NOT NULL,
  `reset_token` varchar(100) NOT NULL,
  `reset_expires` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `reset_token`, `reset_expires`) VALUES
(17, 'admin01', 'johnzelle0923@gmail.com', '$2y$10$340sXi5R3xfsFfLGr6Vj5.NiF//NrL7MurZ.sGinrXNNf0kHzmEce', 'admin', '', '0000-00-00 00:00:00'),
(25, 'Johnzelle', 'johnzelle@gmail.com', '$2y$10$a9yHTMHCakJeZjmSi/rSFOHQy3SEsech.WqLpQ1DoQEE.f9/D/9b2', 'client', '', '0000-00-00 00:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`orderID`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`productID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `orderID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `productID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
