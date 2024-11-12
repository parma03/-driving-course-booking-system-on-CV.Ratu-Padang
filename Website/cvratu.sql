-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 19, 2024 at 01:28 PM
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
-- Database: `cvratu`
--

-- --------------------------------------------------------

--
-- Table structure for table `tb_absensi`
--

CREATE TABLE `tb_absensi` (
  `id_absensi` int(11) NOT NULL,
  `id_pelatihan` int(11) DEFAULT NULL,
  `keterangan_absensi` varchar(255) DEFAULT NULL,
  `waktu_absensi` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_bensin`
--

CREATE TABLE `tb_bensin` (
  `id_bensin` int(11) NOT NULL,
  `id_pelatihan` int(11) NOT NULL,
  `gambarbukti` text NOT NULL,
  `nominal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_buku`
--

CREATE TABLE `tb_buku` (
  `id_buku` int(11) NOT NULL,
  `id_mobil` int(11) DEFAULT NULL,
  `nama_buku` varchar(255) NOT NULL,
  `file` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_buku`
--

INSERT INTO `tb_buku` (`id_buku`, `id_mobil`, `nama_buku`, `file`) VALUES
(1, 2, 'Panduan Agya', 'agya.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `tb_esim`
--

CREATE TABLE `tb_esim` (
  `id_esim` int(11) NOT NULL,
  `id_pelatihan` int(11) NOT NULL,
  `no_sim` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_instruktur`
--

CREATE TABLE `tb_instruktur` (
  `id_instruktur` int(11) NOT NULL,
  `name_instruktur` varchar(255) NOT NULL,
  `nohp` varchar(255) NOT NULL,
  `tipe_instruktur` varchar(255) NOT NULL,
  `img` text DEFAULT NULL,
  `img_ttd` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_instruktur`
--

INSERT INTO `tb_instruktur` (`id_instruktur`, `name_instruktur`, `nohp`, `tipe_instruktur`, `img`, `img_ttd`) VALUES
(65, 'Adamas', '628544541154444', 'mobil manual', 'A3E4D05C-93B5-4B9A-888A-6968321757C8.jpeg', 'userimg.jpg'),
(66, 'Adames', '628544541154444', 'mobil matic', 'Screenshot 2024-06-10 233635.png', NULL),
(77, 'Instruktur', '62846497967439', 'mobil matic', NULL, NULL),
(99, 'Riski', '62896217159545', 'mobil manual', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_jadwal`
--

CREATE TABLE `tb_jadwal` (
  `id_jadwal` int(11) NOT NULL,
  `hari` varchar(255) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_jadwal`
--

INSERT INTO `tb_jadwal` (`id_jadwal`, `hari`, `jam_mulai`, `jam_selesai`) VALUES
(1, 'Senin', '09:00:00', '10:00:00'),
(4, 'Senin', '10:00:00', '12:00:00'),
(6, 'Senin', '14:00:00', '15:00:00'),
(7, 'Senin', '15:00:00', '16:00:00'),
(8, 'Senin', '17:00:00', '18:00:00'),
(9, 'Selasa', '09:00:00', '10:00:00'),
(10, 'Selasa', '10:00:00', '12:00:00'),
(11, 'Selasa', '14:00:00', '15:00:00'),
(12, 'Selasa', '15:00:00', '16:00:00'),
(13, 'Selasa', '17:00:00', '18:00:00'),
(14, 'Rabu', '09:00:00', '10:00:00'),
(15, 'Rabu', '10:00:00', '12:00:00'),
(16, 'Rabu', '14:00:00', '15:00:00'),
(17, 'Rabu', '15:00:00', '16:00:00'),
(18, 'Rabu', '17:00:00', '18:00:00'),
(19, 'Kamis', '09:00:00', '10:00:00'),
(20, 'Kamis', '10:00:00', '12:00:00'),
(21, 'Kamis', '14:00:00', '15:00:00'),
(22, 'Kamis', '15:00:00', '16:00:00'),
(23, 'Kamis', '17:00:00', '18:00:00'),
(24, 'Jumat', '09:00:00', '10:00:00'),
(25, 'Jumat', '10:00:00', '12:00:00'),
(26, 'Jumat', '14:00:00', '15:00:00'),
(27, 'Jumat', '15:00:00', '16:00:00'),
(28, 'Jumat', '17:00:00', '18:00:00'),
(29, 'Sabtu', '09:00:00', '10:00:00'),
(30, 'Sabtu', '10:00:00', '12:00:00'),
(31, 'Sabtu', '14:00:00', '15:00:00'),
(32, 'Sabtu', '15:00:00', '16:00:00'),
(33, 'Sabtu', '17:00:00', '18:00:00'),
(35, 'Selasa', '09:00:00', '10:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tb_jawaban`
--

CREATE TABLE `tb_jawaban` (
  `id_jawaban` int(11) NOT NULL,
  `id_pelatihan` int(11) NOT NULL,
  `id_soal` int(11) NOT NULL,
  `jawaban` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_jenis_pelatihan`
--

CREATE TABLE `tb_jenis_pelatihan` (
  `id_jenis_pelatihan` int(11) NOT NULL,
  `nama_jenis` varchar(255) NOT NULL,
  `kategori` varchar(255) NOT NULL,
  `keterangan` varchar(255) NOT NULL,
  `harga` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_jenis_pelatihan`
--

INSERT INTO `tb_jenis_pelatihan` (`id_jenis_pelatihan`, `nama_jenis`, `kategori`, `keterangan`, `harga`) VALUES
(15, 'Intensif + SIM', 'Mobil Matic', ' Pelatihan 60 Menit', 2150000),
(16, 'Intensif + SIM', 'Mobil Matic', ' Pelatihan 60 Menit', 2350000),
(17, 'Intensif + SIM', 'Mobil Manual', ' Pelatihan 60 Menit', 2000000),
(18, 'Intensif + SIM', 'Mobil Manual', ' Pelatihan 60 Menit', 1750000);

-- --------------------------------------------------------

--
-- Table structure for table `tb_konsumen`
--

CREATE TABLE `tb_konsumen` (
  `id_konsumen` int(12) NOT NULL,
  `name_konsumen` varchar(255) NOT NULL,
  `nohp` varchar(255) NOT NULL,
  `alamat` varchar(255) DEFAULT NULL,
  `jenis_kelamin` varchar(255) DEFAULT NULL,
  `tempat_lahir` varchar(255) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `img` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_konsumen`
--

INSERT INTO `tb_konsumen` (`id_konsumen`, `name_konsumen`, `nohp`, `alamat`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `email`, `img`) VALUES
(64, 'Bobo', '628544541154444', NULL, 'Pria', 'Padang', '2024-07-18', 'asxdklask@gmail.com', 'Screenshot 2024-06-10 233635.png'),
(67, 'Sasa', '6289624199643', NULL, 'Wanita', 'Padang', '2024-07-10', 'melisamaulidia727@gmail.com', ''),
(69, 'Ratu', '6289621187094', NULL, NULL, NULL, NULL, NULL, ''),
(71, 'ratu', '6289621197094', NULL, NULL, NULL, NULL, NULL, ''),
(72, 'ratu', '6289621197094', NULL, NULL, NULL, NULL, NULL, ''),
(73, 'melisa', '6289621197094', NULL, 'Wanita', 'padang', '2024-07-16', 'melisamaulidia727@gmail.com', ''),
(74, 'kaila', '6289621197094', NULL, NULL, NULL, NULL, NULL, ''),
(75, 'Peserta', '6283896343244', NULL, NULL, NULL, NULL, NULL, ''),
(78, 'Peserta1', '628464649491649', NULL, NULL, NULL, NULL, NULL, ''),
(79, 'Dhea', '6289621187094', NULL, 'Wanita', 'Padang', '2024-07-17', 'melisamaulidia727@gmail.com', ''),
(81, 'Kaila', '6289621188094', NULL, NULL, NULL, NULL, NULL, NULL),
(82, 'Raisa', '62896218754643', NULL, NULL, NULL, NULL, NULL, NULL),
(83, 'Rara', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(84, 'caca', '6289621187694', NULL, 'Wanita', 'padang', '2024-08-04', 'caca@gmai.com', NULL),
(90, 'Andre', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(91, 'Renita Astri', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(92, 'Putri', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(93, 'Putri1', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(94, 'trainer', '628544541154444', NULL, NULL, NULL, NULL, NULL, NULL),
(95, 'Safutri', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(96, 'Hasbi', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(97, 'Arif', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(100, 'Lulu1', '6289621159546', NULL, NULL, NULL, NULL, NULL, NULL),
(101, 'Arif', '6289621187095', NULL, NULL, NULL, NULL, NULL, NULL),
(102, 'Sasa', '6289621187096', NULL, NULL, NULL, NULL, NULL, NULL),
(104, 'Deri', '6289621157096', NULL, NULL, NULL, NULL, NULL, NULL),
(105, 'fahry', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(106, 'fahry', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(107, 'fahry', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(108, 'Novi', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL),
(109, 'Imam', '6289621187094', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_manajer`
--

CREATE TABLE `tb_manajer` (
  `id_manajer` int(11) NOT NULL,
  `name_manajer` varchar(255) NOT NULL,
  `nohp` varchar(255) NOT NULL,
  `img` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_manajer`
--

INSERT INTO `tb_manajer` (`id_manajer`, `name_manajer`, `nohp`, `img`) VALUES
(89, 'manajer1', '628544541154444', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tb_mobil`
--

CREATE TABLE `tb_mobil` (
  `id_mobil` int(11) NOT NULL,
  `nama_mobil` varchar(255) NOT NULL,
  `tipe_mobil` varchar(255) NOT NULL,
  `no_mobil` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_mobil`
--

INSERT INTO `tb_mobil` (`id_mobil`, `nama_mobil`, `tipe_mobil`, `no_mobil`) VALUES
(1, 'Avanza', 'Mobil Manual', 'BA 456 ZC'),
(4, 'Brio', 'Mobil Matic', 'BA 123 ZX'),
(5, 'Sienta', 'Mobil Manual', 'BA 123 ZX'),
(6, 'Crv', 'Mobil Matic', 'BA 123 ZX'),
(7, 'Rush', 'Mobil Manual', 'BA 123 ZX');

-- --------------------------------------------------------

--
-- Table structure for table `tb_nilai`
--

CREATE TABLE `tb_nilai` (
  `id_nilai` int(11) NOT NULL,
  `id_pelatihan` int(11) NOT NULL,
  `nilai_teori` int(11) NOT NULL,
  `nilai_percaya_diri` int(11) DEFAULT NULL,
  `nilai_kesopanan_mengemudi` int(11) DEFAULT NULL,
  `nilai_kepatuhan_lalin` int(11) DEFAULT NULL,
  `nilai_sikap` int(11) DEFAULT NULL,
  `nilai_pengetahuan_kendaraan` int(11) DEFAULT NULL,
  `nilai_keamanan` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_pelatihan`
--

CREATE TABLE `tb_pelatihan` (
  `id_pelatihan` int(11) NOT NULL,
  `id_konsumen` int(11) NOT NULL,
  `id_instruktur` int(11) DEFAULT NULL,
  `id_jadwal` int(11) NOT NULL,
  `id_jenis_pelatihan` int(11) NOT NULL,
  `id_mobil` int(11) NOT NULL,
  `tanggal_bo` date NOT NULL,
  `status` varchar(255) NOT NULL,
  `id_buku` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_perbaikan_mobil`
--

CREATE TABLE `tb_perbaikan_mobil` (
  `id_perbaikan_mobil` int(11) NOT NULL,
  `id_mobil` int(11) NOT NULL,
  `gambarbuktimobil` text NOT NULL,
  `nominal` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_soal`
--

CREATE TABLE `tb_soal` (
  `id_soal` int(11) NOT NULL,
  `tipe_soal` varchar(255) DEFAULT NULL,
  `nama_soal` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tb_user`
--

CREATE TABLE `tb_user` (
  `id_user` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tb_user`
--

INSERT INTO `tb_user` (`id_user`, `username`, `password`, `role`) VALUES
(1, 'admin', '202cb962ac59075b964b07152d234b70', 'admin'),
(64, 'bobo', 'c277b3e70605ffa7b9f57b856798f482', 'peserta'),
(65, 'adamas', 'a32e47b6768b0b5c30e59df1e8193651', 'instruktur'),
(66, 'adames', '77a8a01d6d532938ba61a1a924ed91a2', 'instruktur'),
(67, 'Sasa', 'c277b3e70605ffa7b9f57b856798f482', 'peserta'),
(69, 'Ratu', 'a0d4b56384a257fe8d4f54137ed785ee', 'peserta'),
(71, 'ratu', '0c2a8d71355133b582cf278dfa8b489b', 'peserta'),
(72, 'ratu', '0c2a8d71355133b582cf278dfa8b489b', 'peserta'),
(73, 'melisa', 'b7e301ef9c360516bdf920a8fac0f647', 'peserta'),
(74, 'kaila', '49574b29d23ab681e472251f03c4d08e', 'peserta'),
(75, 'Peserta', 'fa9bc487c5287cef627b40e966c9fa5b', 'peserta'),
(77, 'Instruktur', '108373615893bf5223ed539a784ed8fa', 'instruktur'),
(78, 'Peserta1', 'fa9bc487c5287cef627b40e966c9fa5b', 'peserta'),
(79, 'Dhea', 'fb3a24feabccfde227fb101185717ae5', 'peserta'),
(81, 'Kaila', '49574b29d23ab681e472251f03c4d08e', 'peserta'),
(82, 'Raisa', 'c80984ec12536675ff9855987c47fe2f', 'peserta'),
(83, 'Rara', '850a1bc6584256b6687e8c544e158852', 'peserta'),
(84, 'Caca', '3c25fbadc0d1b31c3587c8dcd844d914', 'peserta'),
(89, 'manajer1', 'e82ae00d5714a8de86cc3a9d631a2859', 'manajer'),
(90, 'Andre', '7747feb91123162dc2f2d426f6b7487d', 'peserta'),
(91, 'Renita', '51d29c5f2cb8c273977f6248e8295ce3', 'peserta'),
(92, 'Putri', '8881dd9584d399ab46e04f9115a299d2', 'peserta'),
(93, 'Putri1', '8881dd9584d399ab46e04f9115a299d2', 'peserta'),
(94, 'trainer', '4b064b51defaa4545cabf2141aaffcf7', 'peserta'),
(95, 'Safutri', '08b4d3e6697b033d59d81838db94779f', 'peserta'),
(96, 'Hasbi', 'dc216e452b2f1cee535fb82210dde888', 'peserta'),
(97, 'Arif', 'd3d6919b71913c1782daf5186ff9f9d3', 'peserta'),
(99, 'Riski', 'cca279ee2312b009276f334a8cd6b9b1', 'instruktur'),
(100, 'Lulu', 'c277b3e70605ffa7b9f57b856798f482', 'peserta'),
(101, 'Arif', 'd3d6919b71913c1782daf5186ff9f9d3', 'peserta'),
(102, 'Sasa', 'aa88dfe8f5d7a7733ef20693affb2968', 'peserta'),
(104, 'Deri', '6f1d183cbc5218bb062f4ff27eb6550d', 'peserta'),
(105, 'Fahry', '0b95ab0fb0806b3102a97e9484aa6e7e', 'peserta'),
(106, 'Fahry', '0b95ab0fb0806b3102a97e9484aa6e7e', 'peserta'),
(107, 'Fahry', '0b95ab0fb0806b3102a97e9484aa6e7e', 'peserta'),
(108, 'Novi', '2f2508983161bedb9e752e3e90e55720', 'peserta'),
(109, 'Imam', '94435c3afbd3e11c5be5fb55332ed9e7', 'peserta');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tb_absensi`
--
ALTER TABLE `tb_absensi`
  ADD PRIMARY KEY (`id_absensi`);

--
-- Indexes for table `tb_bensin`
--
ALTER TABLE `tb_bensin`
  ADD PRIMARY KEY (`id_bensin`);

--
-- Indexes for table `tb_buku`
--
ALTER TABLE `tb_buku`
  ADD PRIMARY KEY (`id_buku`);

--
-- Indexes for table `tb_esim`
--
ALTER TABLE `tb_esim`
  ADD PRIMARY KEY (`id_esim`);

--
-- Indexes for table `tb_instruktur`
--
ALTER TABLE `tb_instruktur`
  ADD PRIMARY KEY (`id_instruktur`);

--
-- Indexes for table `tb_jadwal`
--
ALTER TABLE `tb_jadwal`
  ADD PRIMARY KEY (`id_jadwal`);

--
-- Indexes for table `tb_jawaban`
--
ALTER TABLE `tb_jawaban`
  ADD PRIMARY KEY (`id_jawaban`);

--
-- Indexes for table `tb_jenis_pelatihan`
--
ALTER TABLE `tb_jenis_pelatihan`
  ADD PRIMARY KEY (`id_jenis_pelatihan`);

--
-- Indexes for table `tb_konsumen`
--
ALTER TABLE `tb_konsumen`
  ADD PRIMARY KEY (`id_konsumen`);

--
-- Indexes for table `tb_manajer`
--
ALTER TABLE `tb_manajer`
  ADD PRIMARY KEY (`id_manajer`);

--
-- Indexes for table `tb_mobil`
--
ALTER TABLE `tb_mobil`
  ADD PRIMARY KEY (`id_mobil`);

--
-- Indexes for table `tb_nilai`
--
ALTER TABLE `tb_nilai`
  ADD PRIMARY KEY (`id_nilai`);

--
-- Indexes for table `tb_pelatihan`
--
ALTER TABLE `tb_pelatihan`
  ADD PRIMARY KEY (`id_pelatihan`);

--
-- Indexes for table `tb_perbaikan_mobil`
--
ALTER TABLE `tb_perbaikan_mobil`
  ADD PRIMARY KEY (`id_perbaikan_mobil`);

--
-- Indexes for table `tb_soal`
--
ALTER TABLE `tb_soal`
  ADD PRIMARY KEY (`id_soal`);

--
-- Indexes for table `tb_user`
--
ALTER TABLE `tb_user`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tb_absensi`
--
ALTER TABLE `tb_absensi`
  MODIFY `id_absensi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `tb_bensin`
--
ALTER TABLE `tb_bensin`
  MODIFY `id_bensin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_buku`
--
ALTER TABLE `tb_buku`
  MODIFY `id_buku` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tb_esim`
--
ALTER TABLE `tb_esim`
  MODIFY `id_esim` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tb_jadwal`
--
ALTER TABLE `tb_jadwal`
  MODIFY `id_jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `tb_jawaban`
--
ALTER TABLE `tb_jawaban`
  MODIFY `id_jawaban` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tb_jenis_pelatihan`
--
ALTER TABLE `tb_jenis_pelatihan`
  MODIFY `id_jenis_pelatihan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `tb_manajer`
--
ALTER TABLE `tb_manajer`
  MODIFY `id_manajer` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `tb_mobil`
--
ALTER TABLE `tb_mobil`
  MODIFY `id_mobil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tb_nilai`
--
ALTER TABLE `tb_nilai`
  MODIFY `id_nilai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `tb_pelatihan`
--
ALTER TABLE `tb_pelatihan`
  MODIFY `id_pelatihan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT for table `tb_perbaikan_mobil`
--
ALTER TABLE `tb_perbaikan_mobil`
  MODIFY `id_perbaikan_mobil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tb_soal`
--
ALTER TABLE `tb_soal`
  MODIFY `id_soal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tb_user`
--
ALTER TABLE `tb_user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=110;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
