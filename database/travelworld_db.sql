-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 27, 2026 at 01:34 AM
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
-- Database: `travelworld_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `airports`
--

CREATE TABLE `airports` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `iata_code` char(3) NOT NULL,
  `city` varchar(100) NOT NULL,
  `country` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `airports`
--

INSERT INTO `airports` (`id`, `name`, `iata_code`, `city`, `country`, `created_at`) VALUES
(1, 'Marseille Provence Airport', 'MRS', 'Marseille', 'France', '2026-07-26 21:48:40'),
(2, 'Charles de Gaulle Airport', 'CDG', 'Paris', 'France', '2026-07-26 21:48:40'),
(3, 'Houari Boumediene Airport', 'ALG', 'Algiers', 'Algeria', '2026-07-26 21:48:40'),
(4, 'Leonardo da Vinci Airport', 'FCO', 'Rome', 'Italy', '2026-07-26 21:48:40'),
(5, 'Barcelona El Prat Airport', 'BCN', 'Barcelona', 'Spain', '2026-07-26 21:48:40'),
(6, 'Marrakesh Menara Airport', 'RAK', 'Marrakesh', 'Morocco', '2026-07-26 21:48:40');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `subject` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

CREATE TABLE `flights` (
  `id` int(10) UNSIGNED NOT NULL,
  `flight_number` varchar(20) NOT NULL,
  `airline_name` varchar(100) NOT NULL,
  `departure_airport_id` int(10) UNSIGNED NOT NULL,
  `arrival_airport_id` int(10) UNSIGNED NOT NULL,
  `departure_datetime` datetime NOT NULL,
  `arrival_datetime` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `available_seats` int(10) UNSIGNED NOT NULL DEFAULT 100,
  `status` enum('scheduled','cancelled','completed') NOT NULL DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `flights`
--

INSERT INTO `flights` (`id`, `flight_number`, `airline_name`, `departure_airport_id`, `arrival_airport_id`, `departure_datetime`, `arrival_datetime`, `price`, `available_seats`, `status`, `created_at`, `updated_at`) VALUES
(1, 'AF101', 'Air France', 1, 2, '2026-08-10 08:30:00', '2026-08-10 10:00:00', 120.00, 100, 'scheduled', '2026-07-26 22:33:47', '2026-07-26 22:33:47'),
(2, 'AF102', 'Air France', 2, 1, '2026-08-10 18:00:00', '2026-08-10 19:30:00', 125.00, 100, 'scheduled', '2026-07-26 22:33:47', '2026-07-26 22:33:47'),
(3, 'AH201', 'Air Algerie', 1, 3, '2026-08-11 09:15:00', '2026-08-11 10:45:00', 180.00, 120, 'scheduled', '2026-07-26 22:33:47', '2026-07-26 22:33:47'),
(4, 'AH202', 'Air Algerie', 3, 1, '2026-08-11 16:30:00', '2026-08-11 18:00:00', 175.00, 120, 'scheduled', '2026-07-26 22:33:47', '2026-07-26 22:33:47'),
(5, 'FR301', 'Ryanair', 1, 4, '2026-08-12 07:00:00', '2026-08-12 08:30:00', 95.50, 90, 'scheduled', '2026-07-26 22:33:47', '2026-07-26 22:33:47'),
(6, 'FR302', 'Ryanair', 4, 1, '2026-08-12 20:00:00', '2026-08-12 21:30:00', 99.50, 90, 'scheduled', '2026-07-26 22:33:47', '2026-07-26 22:33:47'),
(7, 'VY401', 'Vueling', 2, 5, '2026-08-13 14:20:00', '2026-08-13 16:10:00', 110.00, 110, 'scheduled', '2026-07-26 22:33:47', '2026-07-26 22:33:47'),
(8, 'VY402', 'Vueling', 5, 2, '2026-08-14 10:00:00', '2026-08-14 11:50:00', 115.00, 110, 'scheduled', '2026-07-26 22:33:47', '2026-07-26 22:33:47'),
(9, 'AT501', 'Royal Air Maroc', 1, 6, '2026-08-14 11:00:00', '2026-08-14 13:45:00', 210.00, 130, 'scheduled', '2026-07-26 22:33:47', '2026-07-26 22:33:47'),
(10, 'AT502', 'Royal Air Maroc', 6, 1, '2026-08-15 15:00:00', '2026-08-15 17:45:00', 205.00, 130, 'scheduled', '2026-07-26 22:33:47', '2026-07-26 22:33:47');

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

CREATE TABLE `passengers` (
  `id` int(10) UNSIGNED NOT NULL,
  `reservation_id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `date_of_birth` date NOT NULL,
  `nationality` varchar(100) NOT NULL,
  `document_number` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_reference` varchar(20) NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `flight_id` int(10) UNSIGNED NOT NULL,
  `passenger_count` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `airports`
--
ALTER TABLE `airports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `iata_code` (`iata_code`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_flights_arrival_airport` (`arrival_airport_id`),
  ADD KEY `idx_flights_search` (`departure_airport_id`,`arrival_airport_id`,`status`,`departure_datetime`);

--
-- Indexes for table `passengers`
--
ALTER TABLE `passengers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_passengers_reservation` (`reservation_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_reference` (`booking_reference`),
  ADD KEY `fk_reservations_user` (`user_id`),
  ADD KEY `fk_reservations_flight` (`flight_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `airports`
--
ALTER TABLE `airports`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `passengers`
--
ALTER TABLE `passengers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `flights`
--
ALTER TABLE `flights`
  ADD CONSTRAINT `fk_flights_arrival_airport` FOREIGN KEY (`arrival_airport_id`) REFERENCES `airports` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_flights_departure_airport` FOREIGN KEY (`departure_airport_id`) REFERENCES `airports` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `passengers`
--
ALTER TABLE `passengers`
  ADD CONSTRAINT `fk_passengers_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservations_flight` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reservations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
