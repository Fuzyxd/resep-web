-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 05, 2026 at 05:24 AM
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
-- Database: `resep_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `favorit`
--

CREATE TABLE `favorit` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resep_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `favorit`
--

INSERT INTO `favorit` (`id`, `user_id`, `resep_id`, `created_at`) VALUES
(18, 1, 2, '2026-02-03 03:28:05');

-- --------------------------------------------------------

--
-- Table structure for table `rating`
--

CREATE TABLE `rating` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resep_id` int(11) NOT NULL,
  `rating` int(11) DEFAULT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `komentar` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resep`
--

CREATE TABLE `resep` (
  `id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `bahan` text NOT NULL,
  `langkah` text NOT NULL,
  `waktu` int(11) DEFAULT NULL COMMENT 'Waktu memasak dalam menit',
  `porsi` int(11) DEFAULT NULL,
  `tingkat_kesulitan` enum('Mudah','Sedang','Sulit') DEFAULT 'Sedang',
  `kategori` varchar(50) DEFAULT NULL,
  `gambar_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resep`
--

INSERT INTO `resep` (`id`, `judul`, `deskripsi`, `bahan`, `langkah`, `waktu`, `porsi`, `tingkat_kesulitan`, `kategori`, `gambar_url`, `created_at`) VALUES
(1, 'Nasi Goreng Spesial', 'Nasi goreng dengan bumbu spesial dan topping lengkap', 'Nasi putih 2 piring, Bawang putih 3 siung, Kecap manis 2 sdm, Telur 2 butir, Ayam suwir 100 gram, Daun bawang 2 batang, Minyak goreng 2 sdm, Garam secukupnya', '1. Tumis bawang putih hingga harum\n2. Masukkan telur, orak-arik\n3. Tambahkan ayam suwir\n4. Masukkan nasi, kecap, dan garam\n5. Aduk rata hingga matang\n6. Taburi daun bawang', 20, 2, 'Mudah', 'Makanan Utama', 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', '2026-02-02 02:16:50'),
(2, 'Soto Ayam Lamongan', 'Soto ayam khas Lamongan dengan kuah bening', 'Ayam 1 ekor, Serai 2 batang, Daun jeruk 5 lembar, Jahe 2 cm, Kunyit 4 cm, Bawang merah 8 butir, Bawang putih 5 siung, Lada 1 sdt, Garam secukupnya, Soun 100 gram, Tauge 100 gram, Kol 100 gram', '1. Rebus ayam hingga matang\n2. Tumis bumbu halus hingga harum\n3. Masukkan bumbu tumis ke dalam rebusan ayam\n4. Tambahkan serai dan daun jeruk\n5. Sajikan dengan pelengkap', 60, 4, 'Sedang', 'Sup', 'https://images.unsplash.com/photo-1563245372-f21724e3856d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', '2026-02-02 02:16:50'),
(3, 'Brownies Coklat Lumer', 'Brownies coklat dengan tekstur lembut dan lumer', 'Coklat batang 200 gram, Mentega 150 gram, Telur 3 butir, Gula pasir 150 gram, Tepung terigu 100 gram, Coklat bubuk 50 gram, Kacang kenari 50 gram', '1. Lelehkan coklat dan mentega\n2. Kocok telur dan gula hingga mengembang\n3. Campurkan lelehan coklat ke dalam adonan telur\n4. Ayak tepung terigu dan coklat bubuk\n5. Panggang dalam oven 180°C selama 30 menit', 45, 8, 'Mudah', 'Dessert', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', '2026-02-02 02:16:50');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firebase_uid` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `display_name` varchar(100) DEFAULT NULL,
  `photo_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firebase_uid`, `email`, `display_name`, `photo_url`, `created_at`) VALUES
(1, '', 'rayhanfuzy@gmail.com', NULL, NULL, '2026-02-03 03:27:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `favorit`
--
ALTER TABLE `favorit`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_favorit` (`user_id`,`resep_id`),
  ADD KEY `resep_id` (`resep_id`);

--
-- Indexes for table `rating`
--
ALTER TABLE `rating`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `resep_id` (`resep_id`);

--
-- Indexes for table `resep`
--
ALTER TABLE `resep`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `firebase_uid` (`firebase_uid`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `favorit`
--
ALTER TABLE `favorit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `rating`
--
ALTER TABLE `rating`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resep`
--
ALTER TABLE `resep`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorit`
--
ALTER TABLE `favorit`
  ADD CONSTRAINT `favorit_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorit_ibfk_2` FOREIGN KEY (`resep_id`) REFERENCES `resep` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `rating`
--
ALTER TABLE `rating`
  ADD CONSTRAINT `rating_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rating_ibfk_2` FOREIGN KEY (`resep_id`) REFERENCES `resep` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
