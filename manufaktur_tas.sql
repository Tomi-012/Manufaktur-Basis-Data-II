-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 10, 2026 at 02:48 PM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `manufaktur_tas`
--

-- --------------------------------------------------------

--
-- Table structure for table `bill_of_materials`
--

CREATE TABLE `bill_of_materials` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `material_id` int NOT NULL,
  `jumlah_dibutuhkan` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bill_of_materials`
--

INSERT INTO `bill_of_materials` (`id`, `product_id`, `material_id`, `jumlah_dibutuhkan`) VALUES
(1, 1, 1, '2.50'),
(2, 1, 3, '4.00'),
(3, 1, 5, '1.00'),
(4, 1, 7, '2.00'),
(5, 1, 9, '3.00'),
(6, 2, 2, '1.00'),
(7, 2, 4, '2.00'),
(8, 2, 6, '1.00'),
(9, 2, 7, '1.00'),
(10, 2, 9, '1.50'),
(11, 3, 1, '1.50'),
(12, 3, 3, '2.00'),
(13, 3, 5, '1.00'),
(14, 3, 8, '1.00'),
(15, 3, 9, '2.00'),
(16, 4, 5, '2.00'),
(18, 4, 2, '2.00'),
(19, 4, 10, '5.00');

-- --------------------------------------------------------

--
-- Table structure for table `kategori_material`
--

CREATE TABLE `kategori_material` (
  `id` int NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori_material`
--

INSERT INTO `kategori_material` (`id`, `nama_kategori`) VALUES
(1, 'Kain'),
(2, 'Aksesoris'),
(3, 'Benang'),
(4, 'Resleting'),
(5, 'Tali');

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--

CREATE TABLE `log_aktivitas` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `aksi` varchar(50) NOT NULL,
  `tabel_referensi` varchar(50) DEFAULT NULL,
  `referensi_id` int DEFAULT NULL,
  `detail` text,
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `log_aktivitas`
--

INSERT INTO `log_aktivitas` (`id`, `user_id`, `aksi`, `tabel_referensi`, `referensi_id`, `detail`, `tanggal`) VALUES
(1, 1, 'reset_password', NULL, NULL, 'Password direset melalui halaman lupa password', '2026-05-10 13:40:59'),
(2, 1, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 13:41:07'),
(3, 1, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 13:43:26'),
(4, 2, 'reset_password', NULL, NULL, 'Password direset melalui halaman lupa password', '2026-05-10 13:43:45'),
(5, 2, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 13:43:52'),
(6, 2, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 13:45:19'),
(7, 3, 'reset_password', NULL, NULL, 'Password direset melalui halaman lupa password', '2026-05-10 13:45:33'),
(8, 3, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 13:45:40'),
(9, 3, 'produksi', 'produksi', 1, 'Memproduksi 23 unit Tas Ransel Outdoor', '2026-05-10 13:45:59'),
(10, 3, 'produksi', 'produksi', 2, 'Memproduksi 12 unit Tas Laptop 15 inch', '2026-05-10 13:46:37'),
(11, 3, 'produksi', 'produksi', 3, 'Memproduksi 21 unit Tas Selempang Casual', '2026-05-10 13:47:51'),
(12, 3, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 13:49:55'),
(13, 3, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 13:53:12'),
(14, 3, 'produksi', 'produksi', 4, 'Mengajukan produksi 4 unit Tas Ransel Outdoor (menunggu validasi)', '2026-05-10 13:53:32'),
(15, 3, 'produksi', 'produksi', 5, 'Mengajukan produksi 11 unit Tas Selempang Casual (menunggu validasi)', '2026-05-10 13:53:49'),
(16, 3, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 13:54:44'),
(17, 1, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 13:54:50'),
(18, 1, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 14:01:38'),
(19, 3, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 14:02:06'),
(20, 3, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 14:05:09'),
(21, 2, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 14:05:15'),
(22, 2, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 14:06:10'),
(23, 1, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 14:06:14'),
(24, 1, 'tambah_material', 'materials', 10, 'Menambahkan material baru: Kulit Jerapah', '2026-05-10 14:09:52'),
(25, 1, 'edit_material', 'materials', 10, 'Mengupdate material: Kulit Jerapah', '2026-05-10 14:10:06'),
(26, 1, 'tolak_produksi', 'produksi', 5, 'Menolak produksi 11 unit Tas Selempang Casual - stok material dikembalikan', '2026-05-10 14:10:39'),
(27, 1, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 14:10:45'),
(28, 3, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 14:10:51'),
(29, 3, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 14:19:04'),
(30, 1, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 14:19:09'),
(31, 1, 'edit_produk', 'products', 3, 'Mengupdate produk: Tas Laptop 15 inch', '2026-05-10 14:21:09'),
(32, 1, 'tambah_produk', 'products', 4, 'Menambahkan produk baru: Tas Selempang', '2026-05-10 14:21:44'),
(33, 1, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 14:22:02'),
(34, 2, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 14:22:09'),
(35, 2, 'material_masuk', 'material_masuk', 0, 'Menambah stok Kain Cordura 1000D sebanyak 100', '2026-05-10 14:22:18'),
(36, 2, 'material_masuk', 'material_masuk', 0, 'Menambah stok Kulit Jerapah sebanyak 102', '2026-05-10 14:22:23'),
(37, 2, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 14:22:40'),
(38, 3, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 14:22:44'),
(39, 3, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 14:23:20'),
(40, 1, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 14:23:27'),
(41, 1, 'logout', NULL, NULL, 'User logout dari sistem', '2026-05-10 14:25:15'),
(42, 3, 'login', NULL, NULL, 'User login ke sistem', '2026-05-10 14:25:21'),
(43, 3, 'produksi', 'produksi', 6, 'Mengajukan produksi 1 unit Tas Selempang (menunggu validasi)', '2026-05-10 14:25:31');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int NOT NULL,
  `kategori_id` int NOT NULL,
  `nama_material` varchar(150) NOT NULL,
  `satuan` varchar(30) NOT NULL,
  `stok` decimal(10,2) DEFAULT '0.00',
  `stok_minimum` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `kategori_id`, `nama_material`, `satuan`, `stok`, `stok_minimum`, `created_at`) VALUES
(1, 1, 'Kain Cordura 1000D', 'meter', '114.50', '20.00', '2026-05-10 13:30:55'),
(2, 1, 'Kain Canvas', 'meter', '57.00', '15.00', '2026-05-10 13:30:55'),
(3, 2, 'Gesper Plastik', 'pcs', '368.00', '100.00', '2026-05-10 13:30:55'),
(4, 2, 'Gesper Metal', 'pcs', '258.00', '50.00', '2026-05-10 13:30:55'),
(5, 3, 'Benang Nilon Hitam', 'roll', '9.00', '10.00', '2026-05-10 13:30:55'),
(6, 3, 'Benang Nilon Putih', 'roll', '19.00', '10.00', '2026-05-10 13:30:55'),
(7, 4, 'Resleting 30cm', 'pcs', '125.00', '50.00', '2026-05-10 13:30:55'),
(8, 4, 'Resleting 50cm', 'pcs', '138.00', '30.00', '2026-05-10 13:30:55'),
(9, 5, 'Tali Webbing 2.5cm', 'meter', '63.50', '40.00', '2026-05-10 13:30:55'),
(10, 1, 'Kulit Jerapah', 'meter', '97.00', '10.00', '2026-05-10 14:09:52');

-- --------------------------------------------------------

--
-- Table structure for table `material_masuk`
--

CREATE TABLE `material_masuk` (
  `id` int NOT NULL,
  `material_id` int NOT NULL,
  `user_id` int NOT NULL,
  `jumlah` decimal(10,2) NOT NULL,
  `keterangan` text,
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `material_masuk`
--

INSERT INTO `material_masuk` (`id`, `material_id`, `user_id`, `jumlah`, `keterangan`, `tanggal`) VALUES
(1, 1, 2, '100.00', '', '2026-05-10 14:22:18'),
(2, 10, 2, '102.00', '', '2026-05-10 14:22:23');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `deskripsi` text,
  `stok` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `nama_produk`, `deskripsi`, `stok`, `created_at`) VALUES
(1, 'Tas Ransel Outdoor', 'Tas ransel untuk kegiatan outdoor dengan kapasitas 40L', 23, '2026-05-10 13:30:55'),
(2, 'Tas Selempang Casual', 'Tas selempang untuk kegiatan sehari-hari', 21, '2026-05-10 13:30:55'),
(3, 'Tas Laptop 15 inch', 'Tas laptop dengan padding khusus', 12, '2026-05-10 13:30:55'),
(4, 'Tas Selempang', 'kualitas nomor 1', 0, '2026-05-10 14:21:44');

-- --------------------------------------------------------

--
-- Table structure for table `produksi`
--

CREATE TABLE `produksi` (
  `id` int NOT NULL,
  `product_id` int NOT NULL,
  `user_id` int NOT NULL,
  `jumlah_produksi` int NOT NULL,
  `status` enum('proses','selesai','gagal') DEFAULT 'proses',
  `tanggal` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produksi`
--

INSERT INTO `produksi` (`id`, `product_id`, `user_id`, `jumlah_produksi`, `status`, `tanggal`) VALUES
(1, 1, 3, 23, 'selesai', '2026-05-10 13:45:59'),
(2, 3, 3, 12, 'selesai', '2026-05-10 13:46:37'),
(3, 2, 3, 21, 'selesai', '2026-05-10 13:47:51'),
(4, 1, 3, 4, 'proses', '2026-05-10 13:53:32'),
(5, 2, 3, 11, 'gagal', '2026-05-10 13:53:49'),
(6, 4, 3, 1, 'proses', '2026-05-10 14:25:31');

-- --------------------------------------------------------

--
-- Table structure for table `produksi_detail`
--

CREATE TABLE `produksi_detail` (
  `id` int NOT NULL,
  `produksi_id` int NOT NULL,
  `material_id` int NOT NULL,
  `jumlah_terpakai` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `produksi_detail`
--

INSERT INTO `produksi_detail` (`id`, `produksi_id`, `material_id`, `jumlah_terpakai`) VALUES
(1, 1, 1, '57.50'),
(2, 1, 3, '92.00'),
(3, 1, 5, '23.00'),
(4, 1, 7, '46.00'),
(5, 1, 9, '69.00'),
(6, 2, 1, '18.00'),
(7, 2, 3, '24.00'),
(8, 2, 5, '12.00'),
(9, 2, 8, '12.00'),
(10, 2, 9, '24.00'),
(11, 3, 2, '21.00'),
(12, 3, 4, '42.00'),
(13, 3, 6, '21.00'),
(14, 3, 7, '21.00'),
(15, 3, 9, '31.50'),
(16, 4, 1, '10.00'),
(17, 4, 3, '16.00'),
(18, 4, 5, '4.00'),
(19, 4, 7, '8.00'),
(20, 4, 9, '12.00'),
(21, 5, 2, '11.00'),
(22, 5, 4, '22.00'),
(23, 5, 6, '11.00'),
(24, 5, 7, '11.00'),
(25, 5, 9, '16.50'),
(26, 6, 5, '2.00'),
(27, 6, 2, '2.00'),
(28, 6, 10, '5.00');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('administrator','procurement','gudang') NOT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expire` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `nama`, `username`, `password`, `role`, `reset_token`, `reset_token_expire`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$1NMNgTK3QakFDhvvPqTnCekzqbCOrEdmgmlVOMqz0d0Nov8aXTuUa', 'administrator', NULL, NULL, '2026-05-10 13:30:55'),
(2, 'Staff Procurement', 'procurement', '$2y$10$rR3RPz3R8Yt8iVVZquBnMOq86hcPWQtgGYzYyQg7GC1DXRXSCc9PO', 'procurement', NULL, NULL, '2026-05-10 13:30:55'),
(3, 'Staff Gudang', 'gudang', '$2y$10$KtmiHVcEq/D90tyNu8Q.ZO5O9yxaoAjViH6DrAUIBjcWCxdgRKQJG', 'gudang', NULL, NULL, '2026-05-10 13:30:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bill_of_materials`
--
ALTER TABLE `bill_of_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `kategori_material`
--
ALTER TABLE `kategori_material`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kategori_id` (`kategori_id`);

--
-- Indexes for table `material_masuk`
--
ALTER TABLE `material_masuk`
  ADD PRIMARY KEY (`id`),
  ADD KEY `material_id` (`material_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `produksi`
--
ALTER TABLE `produksi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `produksi_detail`
--
ALTER TABLE `produksi_detail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `produksi_id` (`produksi_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bill_of_materials`
--
ALTER TABLE `bill_of_materials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `kategori_material`
--
ALTER TABLE `kategori_material`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `material_masuk`
--
ALTER TABLE `material_masuk`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `produksi`
--
ALTER TABLE `produksi`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `produksi_detail`
--
ALTER TABLE `produksi_detail`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bill_of_materials`
--
ALTER TABLE `bill_of_materials`
  ADD CONSTRAINT `bill_of_materials_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bill_of_materials_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `log_aktivitas`
--
ALTER TABLE `log_aktivitas`
  ADD CONSTRAINT `log_aktivitas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `materials`
--
ALTER TABLE `materials`
  ADD CONSTRAINT `materials_ibfk_1` FOREIGN KEY (`kategori_id`) REFERENCES `kategori_material` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `material_masuk`
--
ALTER TABLE `material_masuk`
  ADD CONSTRAINT `material_masuk_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `material_masuk_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `produksi`
--
ALTER TABLE `produksi`
  ADD CONSTRAINT `produksi_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `produksi_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Constraints for table `produksi_detail`
--
ALTER TABLE `produksi_detail`
  ADD CONSTRAINT `produksi_detail_ibfk_1` FOREIGN KEY (`produksi_id`) REFERENCES `produksi` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `produksi_detail_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`) ON DELETE RESTRICT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
