-- ========================================================
-- Database: db_parkir_nusantara
-- Studi Kasus: Smart Parking System - Nusantara Mall Parking
-- Kelas: XI PPLG / RPL - SMK Letris Indonesia 2
-- ========================================================

CREATE DATABASE IF NOT EXISTS `db_parkir_nusantara`;
USE `db_parkir_nusantara`;

-- --------------------------------------------------------
-- Struktur dari tabel `tbl_petugas`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_petugas` (
  `id_petugas` INT(11) NOT NULL AUTO_INCREMENT,
  `nama_petugas` VARCHAR(100) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_petugas`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Dumping data untuk tabel `tbl_petugas`
-- Akun:
-- 1. username: petugas, password default: 123456
-- 2. username: petugas2, password default: 123456
INSERT INTO `tbl_petugas` (`id_petugas`, `nama_petugas`, `username`, `password`) VALUES
(1, 'Petugas Parkir', 'petugas', '$2y$10$3Q5AJHhT9pU/3jK9A7D51.FEY7N4Fp/LIQFbio49OVXBXWKo9P2kC'),
(2, 'Siti Rahma', 'petugas2', '$2y$10$3Q5AJHhT9pU/3jK9A7D51.FEY7N4Fp/LIQFbio49OVXBXWKo9P2kC');

-- --------------------------------------------------------
-- Struktur dari tabel `tbl_transaksi_parkir`
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `tbl_transaksi_parkir` (
  `id_parkir` INT(11) NOT NULL AUTO_INCREMENT,
  `plat_nomor` VARCHAR(20) NOT NULL,
  `jenis_kendaraan` VARCHAR(30) NOT NULL,
  `waktu_masuk` DATETIME NOT NULL,
  `waktu_keluar` DATETIME DEFAULT NULL,
  `durasi` INT(11) DEFAULT NULL,
  `total_biaya` INT(11) DEFAULT NULL,
  `status` VARCHAR(15) NOT NULL DEFAULT 'Masuk',
  `id_petugas` INT(11) NOT NULL,
  PRIMARY KEY (`id_parkir`),
  KEY `id_petugas` (`id_petugas`),
  CONSTRAINT `fk_petugas_parkir` FOREIGN KEY (`id_petugas`) REFERENCES `tbl_petugas` (`id_petugas`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Data Uji Pengujian (Tugas 5):
-- 1. Kendaraan baru masuk (untuk demonstrasi toleransi <= 10 menit, gratis Rp0)
-- 2. Motor durasi 90 menit (Tarif 2 jam: 3.000 + 2.000 = Rp5.000)
-- 3. Mobil durasi 90 menit (Tarif 2 jam: 5.000 + 4.000 = Rp9.000)
-- 4. Box/Truk Ringan durasi 90 menit (Tarif 2 jam: 10.000 + 7.000 = Rp17.000)
-- --------------------------------------------------------

INSERT INTO `tbl_transaksi_parkir` (`id_parkir`, `plat_nomor`, `jenis_kendaraan`, `waktu_masuk`, `waktu_keluar`, `durasi`, `total_biaya`, `status`, `id_petugas`) VALUES
(1, 'B 1111 MTR', 'Motor', DATE_SUB(NOW(), INTERVAL 90 MINUTE), NULL, NULL, NULL, 'Masuk', 1),
(2, 'B 2222 MBL', 'Mobil', DATE_SUB(NOW(), INTERVAL 90 MINUTE), NULL, NULL, NULL, 'Masuk', 1),
(3, 'B 3333 BOX', 'Box/Truk Ringan', DATE_SUB(NOW(), INTERVAL 90 MINUTE), NULL, NULL, NULL, 'Masuk', 1),
(4, 'B 4444 BRU', 'Motor', DATE_SUB(NOW(), INTERVAL 3 MINUTE), NULL, NULL, NULL, 'Masuk', 1);
