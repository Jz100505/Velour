-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 31, 2025 at 08:13 AM
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
  `orderID` int(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `orderList` varchar(255) NOT NULL,
  `quantity` int(255) NOT NULL,
  `totalPrice` int(10) NOT NULL,
  `orderDate` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('pending','completed','cancelled') NOT NULL,
  `deliveryAddress` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`orderID`, `username`, `orderList`, `quantity`, `totalPrice`, `orderDate`, `status`, `deliveryAddress`) VALUES
(1, 'user01', '[{\"id\":1,\"name\":\"Stronger With You Intensely\",\"price\":7000,\"quantity\":1},{\"id\":2,\"name\":\"Stronger With You Absolutely\",\"price\":7000,\"quantity\":1},{\"id\":17,\"name\":\"Stronger With You Parfums\",\"price\":7000,\"quantity\":1}]', 3, 21000, '2025-03-30 14:44:15', 'cancelled', 'Sample Address&#13;&#10;'),
(2, 'user01', '[{\"id\":6,\"name\":\"Ultra Male\",\"price\":6500,\"quantity\":1}]', 1, 6500, '2025-03-30 15:23:37', 'pending', 'sample address'),
(3, 'user01', '[{\"id\":9,\"name\":\"Le Beau\",\"price\":6500,\"quantity\":1}]', 1, 6500, '2025-03-30 15:24:19', 'cancelled', 'sample address&#13;&#10;'),
(4, 'user01', '[{\"id\":12,\"name\":\"Silver Mountain Water\",\"price\":20000,\"quantity\":1}]', 1, 20000, '2025-03-30 15:49:45', 'completed', 'sample address');

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `productID` int(255) NOT NULL,
  `productName` varchar(255) NOT NULL,
  `category` varchar(255) NOT NULL,
  `quantity` int(255) NOT NULL,
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
(4, 'Acqua di Gio', 'Armani Line-up', 20, 'acqua_di_gio.png', 10000),
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
(17, 'Stronger With You Parfums', 'Armani Line-up', 20, 'stronger_with_you_parfum.jpg', 7000);

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
(1, 'admin01', 'johnzelle0923@gmail.com', '$2y$10$4oPbX4ARJOTco6ad7x8mQuVw.eFmeuqpk4KG3/kA4CCfmZqZiE0dK', 'admin', '', '0000-00-00 00:00:00'),
(2, 'user01', 'johnzelle1005@gmail.com', '$2y$10$ZOgElLHAkNthfdvC1w8Rp.XJpt58Ptyl9ilEKKKqxAy0IlkRF1V6.', 'client', '', '0000-00-00 00:00:00');

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
  MODIFY `orderID` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `productID` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(255) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
