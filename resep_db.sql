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
  `kalori` int(11) DEFAULT NULL COMMENT 'Perkiraan kalori per porsi',
  `protein` decimal(5,1) DEFAULT NULL COMMENT 'Perkiraan protein (g) per porsi',
  `karbohidrat` decimal(5,1) DEFAULT NULL COMMENT 'Perkiraan karbohidrat (g) per porsi',
  `lemak` decimal(5,1) DEFAULT NULL COMMENT 'Perkiraan lemak (g) per porsi',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resep`
--

INSERT INTO `resep` (`id`, `judul`, `deskripsi`, `bahan`, `langkah`, `waktu`, `porsi`, `tingkat_kesulitan`, `kategori`, `gambar_url`, `kalori`, `protein`, `karbohidrat`, `lemak`, `created_at`) VALUES
(1, 'Nasi Goreng Spesial', 'Nasi goreng dengan bumbu spesial dan topping lengkap', 'Nasi putih 2 piring, Bawang putih 3 siung, Kecap manis 2 sdm, Telur 2 butir, Ayam suwir 100 gram, Daun bawang 2 batang, Minyak goreng 2 sdm, Garam secukupnya', '1. Tumis bawang putih hingga harum\n2. Masukkan telur, orak-arik\n3. Tambahkan ayam suwir\n4. Masukkan nasi, kecap, dan garam\n5. Aduk rata hingga matang\n6. Taburi daun bawang', 20, 2, 'Mudah', 'Makanan Utama', 'https://images.unsplash.com/photo-1565958011703-44f9829ba187?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 550, 22.0, 65.0, 22.0, '2026-02-02 02:16:50'),
(2, 'Soto Ayam Lamongan', 'Soto ayam khas Lamongan dengan kuah bening', 'Ayam 1 ekor, Serai 2 batang, Daun jeruk 5 lembar, Jahe 2 cm, Kunyit 4 cm, Bawang merah 8 butir, Bawang putih 5 siung, Lada 1 sdt, Garam secukupnya, Soun 100 gram, Tauge 100 gram, Kol 100 gram', '1. Rebus ayam hingga matang\n2. Tumis bumbu halus hingga harum\n3. Masukkan bumbu tumis ke dalam rebusan ayam\n4. Tambahkan serai dan daun jeruk\n5. Sajikan dengan pelengkap', 60, 4, 'Sedang', 'Sup', 'https://images.unsplash.com/photo-1563245372-f21724e3856d?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 320, 24.0, 28.0, 12.0, '2026-02-02 02:16:50'),
(3, 'Brownies Coklat Lumer', 'Brownies coklat dengan tekstur lembut dan lumer', 'Coklat batang 200 gram, Mentega 150 gram, Telur 3 butir, Gula pasir 150 gram, Tepung terigu 100 gram, Coklat bubuk 50 gram, Kacang kenari 50 gram', '1. Lelehkan coklat dan mentega\n2. Kocok telur dan gula hingga mengembang\n3. Campurkan lelehan coklat ke dalam adonan telur\n4. Ayak tepung terigu dan coklat bubuk\n5. Panggang dalam oven 180°C selama 30 menit', 45, 8, 'Mudah', 'Dessert', 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?ixlib=rb-4.0.3&auto=format&fit=crop&w=500&q=80', 420, 6.0, 52.0, 22.0, '2026-02-02 02:16:50'),
(4, 'Ayam Rica-Rica Manado', 'Ayam pedas rempah khas Manado dengan aroma daun jeruk','Ayam 500 gram, cabai merah 10 buah, cabai rawit 5 buah, bawang merah 6 siung, bawang putih 4 siung, jahe 2 cm, serai 1 batang, daun jeruk 4 lembar, garam, gula, minyak','1. Haluskan cabai, bawang, jahe\n2. Tumis bumbu hingga harum\n3. Masukkan ayam, aduk rata\n4. Tambahkan serai dan daun jeruk\n5. Masak hingga ayam empuk dan bumbu meresap',40,3,'Sedang','Makanan Utama','https://source.unsplash.com/500x400/?ayam-pedas', 380, 32.0, 8.0, 22.0, '2026-02-02 02:16:50'),
(5, 'Ikan Goreng Sambal Dabu-Dabu', 'Ikan goreng renyah dengan sambal segar khas Sulawesi','Ikan kembung 2 ekor, jeruk nipis, garam, minyak, tomat 2 buah, bawang merah 5 siung, cabai rawit 6 buah, daun bawang, minyak panas','1. Lumuri ikan dengan garam dan jeruk nipis\n2. Goreng hingga kering\n3. Iris semua bahan sambal\n4. Siram minyak panas\n5. Sajikan bersama ikan',30,2,'Mudah','Makanan Utama','https://source.unsplash.com/500x400/?ikan-goreng', 450, 35.0, 6.0, 30.0, '2026-02-02 02:16:50'),
(6, 'Tumis Kangkung Terasi', 'Sayur tumis sederhana dengan aroma terasi','Kangkung 1 ikat, bawang putih 3 siung, bawang merah 4 siung, cabai 4 buah, terasi 1 sdt, saus tiram 1 sdm, garam, minyak','1. Tumis bawang dan cabai\n2. Masukkan terasi hingga harum\n3. Tambahkan kangkung\n4. Bumbui garam dan saus tiram\n5. Masak cepat hingga layu',10,2,'Mudah','Sayuran','https://source.unsplash.com/500x400/?kangkung', 160, 5.0, 10.0, 11.0, '2026-02-02 02:16:50'),
(7, 'Bakwan Jagung', 'Gorengan jagung manis renyah','Jagung manis 2 tongkol, tepung terigu 150 gram, telur 1 butir, daun bawang, bawang putih 2 siung, garam, merica, air secukupnya, minyak','1. Campur semua bahan menjadi adonan\n2. Panaskan minyak\n3. Ambil satu sendok adonan\n4. Goreng hingga kuning keemasan\n5. Tiriskan dan sajikan',20,4,'Mudah','Snack','https://source.unsplash.com/500x400/?bakwan', 280, 6.0, 32.0, 14.0, '2026-02-02 02:16:50'),
(8, 'Pisang Goreng Madu', 'Pisang goreng manis legit dengan madu','Pisang kepok 5 buah, tepung terigu 120 gram, tepung beras 2 sdm, gula 2 sdm, madu 3 sdm, air, minyak','1. Campur tepung, gula, dan air jadi adonan\n2. Celupkan pisang\n3. Goreng hingga renyah\n4. Angkat dan tiriskan\n5. Siram madu sebelum disajikan',15,3,'Mudah','Dessert','https://source.unsplash.com/500x400/?pisang-goreng', 330, 4.0, 50.0, 13.0, '2026-02-02 02:16:50'),
(9, 'Sate Ayam Madura', 'Sate ayam bakar dengan bumbu kacang manis gurih khas Madura', 'Dada ayam 500 gram, tusuk sate, kacang tanah 150 gram, bawang putih 3 siung, kemiri 2 butir, kecap manis 4 sdm, gula merah, garam, minyak', '1. Potong ayam kecil-kecil lalu tusuk sate\n2. Haluskan kacang goreng dan bumbu\n3. Tambahkan kecap dan air secukupnya\n4. Bakar sate sambil dioles bumbu\n5. Sajikan dengan saus kacang',35,4,'Sedang','Makanan Utama','https://source.unsplash.com/500x400/?sate-ayam', 520, 35.0, 28.0, 28.0, '2026-02-02 02:16:50'),
(10,'Gado-Gado Jakarta', 'Salad sayuran rebus dengan saus kacang kental dan gurih', 'Tauge 100 gram, kacang panjang, kol, kentang rebus, tahu goreng, tempe goreng, telur rebus, kacang tanah 150 gram, bawang putih 2 siung, gula merah, garam, air asam jawa', '1. Rebus semua sayuran hingga matang\n2. Goreng tahu dan tempe\n3. Haluskan kacang tanah dan bumbu\n4. Tambahkan air hingga jadi saus\n5. Susun sayur dan siram saus kacang',25,3,'Mudah','Salad','https://source.unsplash.com/500x400/?gado-gado', 420, 16.0, 38.0, 22.0, '2026-02-02 02:16:50'),
(11,'Rawon Daging Jawa Timur','Sup daging berkuah hitam khas kluwek yang gurih','Daging sapi 500 gram, kluwek 5 buah, bawang merah 6 siung, bawang putih 4 siung, serai 1 batang, daun jeruk 3 lembar, garam, gula, minyak','1. Rebus daging hingga empuk\n2. Haluskan bumbu dan tumis hingga harum\n3. Masukkan kluwek dan bumbu ke kuah\n4. Tambahkan daging\n5. Masak hingga meresap lalu sajikan',90,4,'Sedang','Sup','https://source.unsplash.com/500x400/?rawon', 360, 26.0, 10.0, 24.0, '2026-02-02 02:16:50'),
(12,'Nasi Uduk Betawi','Nasi gurih santan dengan aroma rempah','Beras 500 gram, santan 600 ml, daun salam, serai, garam','1. Campur santan dan rempah\n2. Masukkan beras\n3. Masak di rice cooker hingga matang\n4. Aduk dan kukus sebentar\n5. Sajikan dengan lauk',35,4,'Mudah','Makanan Utama','https://source.unsplash.com/500x400/?nasi-uduk', 340, 6.0, 52.0, 12.0, '2026-02-02 02:16:50'),
(13,'Pempek Palembang','Olahan ikan dan sagu dengan kuah cuko asam manis pedas','Ikan tenggiri giling 300 gram, tepung sagu 200 gram, bawang putih 3 siung, garam, gula merah, asam jawa','1. Campur ikan dan bumbu\n2. Tambahkan sagu dan uleni\n3. Bentuk lalu rebus\n4. Goreng sebentar\n5. Sajikan dengan kuah cuko',60,4,'Sedang','Snack','https://source.unsplash.com/500x400/?pempek', 360, 14.0, 52.0, 10.0, '2026-02-02 02:16:50'),
(14,'Soto Betawi Santan','Soto daging kuah santan kental gurih','Daging sapi 400 gram, santan 500 ml, susu cair 200 ml, bawang merah, bawang putih, pala, serai','1. Rebus daging\n2. Tumis bumbu halus\n3. Campurkan santan dan susu\n4. Masukkan daging\n5. Sajikan hangat',60,3,'Sedang','Sup','https://source.unsplash.com/500x400/?soto-betawi', 480, 24.0, 12.0, 36.0, '2026-02-02 02:16:50'),
(15,'Ayam Bakar Taliwang','Ayam bakar pedas manis khas Lombok','Ayam 1 ekor, cabai merah 8 buah, bawang putih 4 siung, terasi, gula merah, kecap manis','1. Haluskan bumbu\n2. Lumuri ayam\n3. Bakar sambil dioles bumbu\n4. Balik hingga matang\n5. Sajikan panas',40,3,'Sedang','Makanan Utama','https://source.unsplash.com/500x400/?ayam-bakar', 420, 34.0, 6.0, 28.0, '2026-02-02 02:16:50'),
(16,'Perkedel Kentang','Perkedel lembut dari kentang goreng','Kentang 500 gram, telur 1 butir, bawang putih 2 siung, merica, garam, minyak','1. Goreng kentang lalu haluskan\n2. Campur bumbu dan telur\n3. Bentuk bulat pipih\n4. Goreng hingga kecoklatan\n5. Sajikan hangat',25,4,'Mudah','Snack','https://source.unsplash.com/500x400/?perkedel', 260, 5.0, 28.0, 14.0, '2026-02-02 02:16:50'),
(17,'Sayur Asem Sunda','Sayur kuah asam segar dengan aneka sayuran','Labu siam, kacang panjang, jagung, melinjo, asam jawa, bawang merah, bawang putih','1. Rebus air dan bumbu\n2. Masukkan sayuran keras dulu\n3. Tambahkan sayur lain\n4. Masak hingga matang\n5. Koreksi rasa',30,4,'Mudah','Sayuran','https://source.unsplash.com/500x400/?sayur-asem', 120, 4.0, 18.0, 4.0, '2026-02-02 02:16:50'),
(18,'Martabak Telur Mini','Martabak gurih isi telur dan daging cincang','Kulit lumpia, telur 3 butir, daging cincang 150 gram, daun bawang, bawang putih, garam','1. Tumis daging dan bumbu\n2. Campur dengan telur\n3. Isi kulit lumpia\n4. Lipat rapi\n5. Goreng hingga renyah',20,3,'Sedang','Snack','https://source.unsplash.com/500x400/?martabak', 430, 18.0, 28.0, 28.0, '2026-02-02 02:16:50'),
(19,'Bubur Ayam Kampung','Bubur lembut dengan topping ayam gurih','Beras 200 gram, ayam suwir, kaldu ayam, daun bawang, bawang goreng, kecap','1. Masak beras dengan banyak air hingga lembut\n2. Tambahkan kaldu\n3. Sajikan dengan topping ayam dan pelengkap\n4. Aduk sebelum dimakan',40,3,'Mudah','Makanan Utama','https://source.unsplash.com/500x400/?bubur-ayam', 310, 16.0, 42.0, 9.0, '2026-02-02 02:16:50');

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

