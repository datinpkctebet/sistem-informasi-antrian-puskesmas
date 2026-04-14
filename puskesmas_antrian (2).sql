-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 10, 2026 at 09:57 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `puskesmas_antrian`
--

-- --------------------------------------------------------

--
-- Table structure for table `queue`
--

CREATE TABLE `queue` (
  `id` int NOT NULL,
  `kode_antrian` varchar(5) NOT NULL,
  `nomor_antrian` int NOT NULL,
  `full_number` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_pasien` varchar(100) DEFAULT NULL,
  `lantai` enum('1','2_kiri','2_kanan','3') NOT NULL,
  `status` enum('waiting','calling','done','called') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT 'waiting',
  `loket` varchar(20) DEFAULT NULL,
  `petugas_id` int DEFAULT NULL,
  `tanggal` date NOT NULL,
  `waktu_masuk` datetime NOT NULL,
  `waktu_panggil` datetime DEFAULT NULL,
  `waktu_selesai` datetime DEFAULT NULL,
  `is_warning` bigint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_called_at` datetime DEFAULT NULL,
  `call_count` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `queue_counter`
--

CREATE TABLE `queue_counter` (
  `id` int NOT NULL,
  `kode_antrian` varchar(5) NOT NULL,
  `tanggal` date NOT NULL,
  `last_number` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int NOT NULL,
  `kode_antrian` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `nama_pelayanan` varchar(100) NOT NULL,
  `lantai` enum('1','2_kiri','2_kanan','3') NOT NULL,
  `is_active` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `kode_antrian`, `nama_pelayanan`, `lantai`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'A', 'KLASTER 3 - USIA DEWASA', '1', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(2, 'B', 'PELAYANAN KESEHATAN GIGI & MULUT', '1', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(3, 'C', 'KLASTER 3 - PTM', '1', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(4, 'D', 'LINTAS KLASTER - TINDAKAN', '1', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(5, 'E', 'LINTAS KLASTER - LAYANAN 24 JAM', '1', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(6, 'F', 'KLASTER 3 - CATIN & RESKPRO', '1', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(7, 'G', 'NURSE STATION LANSIA', '2_kiri', 1, '2026-01-15 08:24:13', '2026-04-09 02:21:00'),
(8, 'H', 'NURSE STATION PTM', '2_kiri', 1, '2026-01-15 08:24:13', '2026-04-09 02:21:12'),
(9, 'I', 'NURSE STATION DEWASA', '2_kiri', 1, '2026-01-15 08:24:13', '2026-04-09 02:21:21'),
(10, 'J', 'LANSIA 1', '2_kiri', 1, '2026-01-15 08:24:13', '2026-04-09 00:39:44'),
(11, 'K', 'LANSIA 2', '2_kiri', 1, '2026-01-15 08:24:13', '2026-04-09 00:40:21'),
(12, 'L', 'PTM 1', '2_kiri', 1, '2026-01-15 08:24:13', '2026-04-09 00:40:31'),
(13, 'M', 'PTM 2', '2_kiri', 1, '2026-01-15 08:24:13', '2026-04-09 00:40:37'),
(14, 'N', 'DEWASA 1', '2_kiri', 1, '2026-01-15 08:24:13', '2026-04-09 00:40:58'),
(15, 'O', 'DEWASA 2', '2_kiri', 1, '2026-01-15 08:24:13', '2026-04-09 00:41:25'),
(16, 'P', 'KLASTER 2 - IBU NIFAS', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(17, 'Q', 'KLASTER 2 - KB & IVA', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(18, 'R', 'KLASTER 2 - MTBS & PRA SEKOLAH', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(19, 'S', 'KLASTER 2 - BAYI BARU LAHIR', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(20, 'T', 'KLASTER 2 - IMUNISASI', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(21, 'U', 'PELAYANAN GIZI', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(22, 'V', 'PELAYANAN DOTS - KUSTA', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(23, 'W', 'PELAYANAN KESEHATAN HAJI', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(24, 'X', 'KLASTER 2 - PERSALINAN', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(25, 'Y', 'KLASTER 2 - TUMBUH KEMBANG ANAK', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(26, 'Z', 'LINTAS KLASTER - LABORATORIUM', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(27, 'AA', 'PELAYANAN PTRM (METADON)', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(28, 'AB', 'PELAYANAN ALAMANDA (KTPA)', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(29, 'AE', 'SURAT KETERANGAN BUTA WARNA', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(30, 'AF', 'PELAYANAN MCU/KETERANGAN SEHAT', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(31, 'AI', 'PELAYANAN SKBN', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(32, 'CG', 'PKG HARI ULANG TAHUN ANAK', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(33, 'CH', 'PKG HARI ULANG THN DEWASA LANSIA', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(34, 'CI', 'TES DNA HPV', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(35, 'CJ', 'PKG HARI ULANG TAHUN USIA REMAJA', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(36, 'CL', 'PEMANTAUAN VAKSIN DENGUE', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(37, 'CM', 'KONSELING IBU HAMIL', '3', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `role` enum('admin','perawat','dokter') NOT NULL,
  `lantai` enum('1','2_kiri','2_kanan','3') DEFAULT NULL,
  `is_active` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `role`, `lantai`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin', NULL, 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(2, 'perawat1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Perawat Lantai 1', 'perawat', '1', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(3, 'dokter1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dr. Ahmad', 'dokter', '1', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(4, 'perawat2_kiri', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Perawat Lantai 2 Kiri', 'perawat', '2_kiri', 1, '2026-01-15 08:24:13', '2026-01-26 08:28:45'),
(5, 'dokter2_kiri', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dr. Siti', 'dokter', '2_kiri', 1, '2026-01-15 08:24:13', '2026-01-26 08:28:45'),
(6, 'perawat2_kanan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Perawat Lantai 2 Kanan', 'perawat', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13'),
(7, 'dokter2_kanan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'dr. Siti', 'dokter', '2_kanan', 1, '2026-01-15 08:24:13', '2026-01-15 08:24:13');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `queue`
--
ALTER TABLE `queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `petugas_id` (`petugas_id`),
  ADD KEY `kode_antrian` (`kode_antrian`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tanggal` (`tanggal`),
  ADD KEY `idx_lantai` (`lantai`);

--
-- Indexes for table `queue_counter`
--
ALTER TABLE `queue_counter`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_counter` (`kode_antrian`,`tanggal`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_antrian` (`kode_antrian`);

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
-- AUTO_INCREMENT for table `queue`
--
ALTER TABLE `queue`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `queue_counter`
--
ALTER TABLE `queue_counter`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `queue`
--
ALTER TABLE `queue`
  ADD CONSTRAINT `queue_ibfk_1` FOREIGN KEY (`petugas_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `queue_ibfk_2` FOREIGN KEY (`kode_antrian`) REFERENCES `services` (`kode_antrian`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
