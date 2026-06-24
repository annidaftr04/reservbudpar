-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 24, 2026 at 09:23 PM
-- Server version: 10.4.21-MariaDB
-- PHP Version: 7.3.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `reservasi_tempat`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `nama`) VALUES
(2, 'admin', '$2y$10$nXF5jNtmt7UpCk1yWyUisuSTryJ0O4qrbViCVWIDeLjg7bFpB1cMu', 'admins'),
(4, 'josÃ©luis_lebrÃ³n@projectmy.ne', '$2y$10$bHJDeHpnRmgRgjX0FMeOeuP5ZOmaTrUWONNXdaIAyg4T0nk.xWGUi', 'josÃ©luis_lebrÃ³n@projectmy.net'),
(5, 'admin123', '$2y$10$rhmN28XOq5nYROb/ZrABpOxegqonE4.qUECeS7NHBP8PAWduqr0Qe', 'admin'),
(7, 'joko', '$2y$10$q9PRjIGEj/9zNropsYoPSeu2hgeDqJvchgYHaEk7K2qdd90umyMcG', 'joko'),
(8, 'admin1', '$2y$10$mHbdbfDyc3hmqPO.s7ZO4u8prRewkZXbWLYw9Q.m2yOQefBr8BU/m', 'admin 3');

-- --------------------------------------------------------

--
-- Table structure for table `email_logs`
--

CREATE TABLE `email_logs` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `jenis_email` enum('approval','kuesioner_h1') NOT NULL,
  `email_tujuan` varchar(100) NOT NULL,
  `status_pengiriman` enum('berhasil','gagal') NOT NULL,
  `waktu_kirim` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `email_logs`
--

INSERT INTO `email_logs` (`id`, `reservation_id`, `jenis_email`, `email_tujuan`, `status_pengiriman`, `waktu_kirim`) VALUES
(1, 51, 'approval', 'annidafitri1711@gmail.com', 'berhasil', '2026-05-29 03:23:37'),
(2, 54, 'approval', 'annidafitri1711@gmail.com', 'berhasil', '2026-05-29 11:02:32'),
(3, 55, 'approval', 'annidafitri1711@gmail.com', 'berhasil', '2026-06-16 02:06:19'),
(4, 55, 'kuesioner_h1', 'annidafitri1711@gmail.com', 'berhasil', '2026-06-21 00:26:47'),
(5, 56, '', 'annidaftr04@gmail.com', 'berhasil', '2026-06-21 01:29:48');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `aktivitas` text NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `notifikasi_log`
--

CREATE TABLE `notifikasi_log` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `jenis_notifikasi` varchar(30) DEFAULT NULL,
  `tanggal_kirim` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `notifikasi_log`
--

INSERT INTO `notifikasi_log` (`id`, `reservation_id`, `email`, `jenis_notifikasi`, `tanggal_kirim`) VALUES
(1, 51, 'annidafitri1711@gmail.com', 'whatsapp_manual', '2026-05-29 03:23:28'),
(2, 54, 'annidafitri1711@gmail.com', 'whatsapp_manual', '2026-05-29 11:02:23');

-- --------------------------------------------------------

--
-- Table structure for table `places`
--

CREATE TABLE `places` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `image` varchar(100) NOT NULL,
  `lokasi` varchar(150) NOT NULL,
  `latitude` decimal(9,6) NOT NULL,
  `longitude` decimal(9,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `places`
--

INSERT INTO `places` (`id`, `name`, `description`, `image`, `lokasi`, `latitude`, `longitude`) VALUES
(20, 'Gedung Seni Budaya', 'Gedung Seni Budaya Kota Tangerang dibuka gratis untuk masyarakat umum.\r\nBerkapasitas 300 orang, bisa digunakan untuk kegiatan kesenian, nonton bareng, seminar, dan lainnya.', 'assets/img/gedung-seni-budaya.jpg', 'https://maps.app.goo.gl/mMsbffgEsEfatCDM6', '0.000000', '0.000000'),
(21, 'Taman Potret Cikokol', 'Taman Potret yang terletak di Kelurahan Babakan, Kecamatan Tangerang tepatnya samping Tangcity Mall. Berdiri sejak 2015 silam dengan daya tarik patung penari merah dan vegetasi tumbuhan, Taman Potret hingga kini masih menjadi pilihan wisata rekreasi gratis yang edukatif.', 'assets/img/taman-potret.jpg', 'https://maps.app.goo.gl/XJpbP6PhqjKSpf3E8', '0.000000', '0.000000'),
(22, 'Taman Gajah Tunggal', 'Taman ini beralamat di Jalan Perintis Kemerdekaan, Babakan, Kecamatan Tangerang, Kota Tangerang, Banten.\r\nJarak taman ini dari Stasiun Tangerang adalah 3-5 kilometer dengan durasi berkendara hampir 20 menit.  Diresmikan pada tahun 2017, taman ini dikenal karena memiliki patung gajah dan fasilitas yang terbuat dari ban. Selain itu, lokasinya yang di tepi Sungai Cisadane juga menjadi daya tarik tersendiri.', 'assets/img/63b4280de86e3.jpg', 'https://maps.app.goo.gl/VLbu3XEy9686sL1n7', '0.000000', '0.000000'),
(23, 'Taman Pramuka', 'Mengusung tema Kepramukaan, taman ini menghadirkan berbagai informasi seputar sejarah pramuka, semaphore, tingkatan, hingga tanda pengenal. Selain menjadi ruang hijau, keberadaannya juga berfungsi sebagai sarana edukatif bagi generasi muda untuk menanamkan nilai kemandirian, kebersamaan, dan cinta lingkungan sebagaimana diajarkan dalam gerakan pramuka.\r\n\r\nFasilitas Taman Pramuka terbilang lengkap, mulai dari area bermain anak, musala, toilet umum, jogging track, refleksi batu koral, hingga camping ground. Seluruhnya dapat dinikmati gratis, sehingga masyarakat punya akses mudah ke ruang hijau yang berfungsi sebagai tempat rekreasi sekaligus sarana interaksi sosial.', 'assets/img/img_674572a28d0838.94641343.jpg', 'https://maps.app.goo.gl/TF4qpC4ng7te731H8', '0.000000', '0.000000'),
(24, 'Taman Hutan Kota', 'Taman ini tampak seperti taman pada umumnya yang banyak tersebar di sudut Kota Tangerang. Rindangnya pepohonan, serta suara aliran Sungai Cisadane menambah syahdu suasana taman ini. Maka tak heran, taman ini juga jadi tempat favorit untuk wisata murah meriah bagi warga Tangerang dan sekitarnya.\r\n\r\nTapi siapa sangka, dibalik rimbunnya pepohonan tersembunyi sebuah kanal tua peninggalan Belanda. Kanal ini dikenal dengan nama kanal Mookervart, dan masyarakat lebih mengenalnya sebagai pintu air kecil untuk membedakannya dengan Pintu Air 10. Tak banyak juga yang tahu bahwa kanal ini sempat menjadi pengatur irigasi untuk aliran air ke Batavia.', 'assets/img/img_6745730d72d142.75727609.jpeg', 'https://maps.app.goo.gl/2u3HXW4kprB8FsTD8', '0.000000', '0.000000'),
(25, 'Taman Ecopark', 'Taman ini dikelilingi pohon rindang yang menghadirkan suasana sejuk dan asri. Menariknya, taman ini dibangun dibantaran Sungai Cisadane dan menjadi salah satu spot yang cukup tepat untuk ngabuburit. Pasalnya, pengunjung akan disuguhkan pemandangan langit senja berpadu dengan cantiknya aliran Sungai Cisadane.', 'assets/img/img_6745785b5cc297.56180676.jpg', 'https://maps.app.goo.gl/M3P1yH1kSLsDyesy6', '0.000000', '0.000000'),
(26, 'Taman Ekspresi', 'Ruang hijau penuh kreativitas dan inspirasi di tengah kota!\r\n\r\nDi sini, setiap sudutnya mengajak kamu untuk berekspresi, berkreasi, dan menikmati keindahan alam sambil bersantai bersama keluarga dan teman.', 'assets/img/img_674578954f7dc6.39723189.jpg', 'https://maps.app.goo.gl/gkXUVtnrr85LPYgj6', '0.000000', '0.000000'),
(33, 'Museum Taman Makam Pahlawan', 'Taman Makam Pahlawan (TMP) Taruna, merupakan tempat dikebumikannya 37 tentara Indonesia yang telah gugur dalam peristiwa Pertempuran Lengkong, yang terjadi pada 25 Januari 1946. Dan tanggal itu pula, pada 2005 telah ditetapkan sebagai Hari Bhakti Taruna Akademi Militer. Berlokasi di Jalan Daan Mogot, Kelurahan Sukaasih, Kecamatan Tangerang, Kota Tangerang, disana terdapat pula Museum Juang Taruna yang mengenalkan tentang sejarah bagaimana perjuangan para pahlawan, salah satunya ialah Mayor Daan Mogot.', 'assets/img/img_68c789d9ccc865.06231488.jpg', 'https://maps.app.goo.gl/UxVBZzjYVK2MhowF7', '0.000000', '0.000000');

-- --------------------------------------------------------

--
-- Table structure for table `place_images`
--

CREATE TABLE `place_images` (
  `id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `kode_booking` varchar(20) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `no_telepon` varchar(15) NOT NULL,
  `hari` date NOT NULL,
  `jam_mulai` time NOT NULL DEFAULT '08:00:00',
  `jam_selesai` time NOT NULL DEFAULT '16:00:00',
  `keterangan` text DEFAULT NULL,
  `status` enum('pending','disetujui','ditolak','selesai') DEFAULT NULL,
  `file_upload` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `catatan` text DEFAULT NULL,
  `ktp_upload` varchar(100) DEFAULT NULL,
  `surat_kelurahan_upload` varchar(100) DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `place_id` int(11) DEFAULT NULL,
  `email_sent` tinyint(1) DEFAULT 0,
  `kuesioner_sent` tinyint(1) DEFAULT 0,
  `sub_place_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `sumber_reservasi` enum('online','offline') DEFAULT 'online'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `kode_booking`, `nama`, `no_telepon`, `hari`, `jam_mulai`, `jam_selesai`, `keterangan`, `status`, `file_upload`, `user_id`, `email`, `catatan`, `ktp_upload`, `surat_kelurahan_upload`, `tanggal_selesai`, `place_id`, `email_sent`, `kuesioner_sent`, `sub_place_id`, `admin_id`, `sumber_reservasi`) VALUES
(43, 'RES-695e01df2b479', 'Jaya Budi Santosa', '085715896276', '2026-01-07', '08:00:00', '16:00:00', 'Tgl.19 Juni 2026 jam 13.30 s/d selesai, utk persiapan acara pelepasan siswa.\nTgl 20 Juni 2026 jam 7.00 s/d 13.30 acara ceremonial pelepasan siswa SMP Islam Al.Ikhlas. Cipondoh.Kota Tangerang.', 'disetujui', 'Surat_Permohonan_RES-695e01df2b479_Jaya_Budi_Santosa.docx', 17, 'artventure1968@gmail.com', 'Harap mengikuti ketentuan yang tercantum dalam surat balasan peminjaman gedung', 'KTP_RES-695e01df2b479_Jaya_Budi_Santosa.jpg', 'Surat_Kelurahan_RES-695e01df2b479_Jaya_Budi_Santosa.pdf', NULL, 20, 0, 0, 1, NULL, 'online'),
(45, 'RES-698198caec536', 'Syabilla Elf Mika', '088973588521', '2026-02-15', '08:00:00', '16:00:00', 'Welcoming and Sashing Ceremony dan Seminar Kebantenan', 'ditolak', 'Surat_Permohonan_RES-698198caec536_Syabilla_Elf_Mika.docx', 19, 'officialdutapariwisatabanten26@gmail.com', 'Gedung Seni Budaya diprioritaskan untuk Warga Kota Tangerang', 'KTP_RES-698198caec536_Syabilla_Elf_Mika.jpeg', 'Surat_Kelurahan_RES-698198caec536_Syabilla_Elf_Mika.pdf', NULL, 20, 0, 0, 1, NULL, 'online'),
(48, 'RES-6a0208ba88e6e', 'shengu', '081288649597', '2026-05-13', '08:00:00', '16:00:00', 'adadeh', 'ditolak', 'Surat_Permohonan_RES-6a0208ba88e6e_shengu.docx', 24, 'bubblegume15@gmail.com', '', 'KTP_RES-6a0208ba88e6e_shengu.jpeg', '6a0208ba8aca5_waterfall.pdf', '2026-05-13', 20, 0, 0, 2, NULL, 'online'),
(49, 'RES-6a120d13c509b', 'shengu', ' 085285936271', '2026-05-24', '08:00:00', '16:00:00', 'kjabhiahdd', 'disetujui', 'Surat_Permohonan_RES-6a120d13c509b_shengu.pdf', 24, 'annidaftr04@gmail.com', 'silahkan', 'KTP_RES-6a120d13c509b_shengu.jpg', NULL, '2026-05-24', 21, 0, 1, NULL, NULL, 'online'),
(50, 'RES-6a147cfa680d6', 'shengu', '081288649597', '2026-05-24', '08:00:00', '16:00:00', 'nikah', 'disetujui', 'Surat_Permohonan_RES-6a147cfa680d6_shengu.pdf', 24, 'nagitaaulia855@gmail.com', 'gasss besti', 'KTP_RES-6a147cfa680d6_shengu.jpg', NULL, '2026-05-24', 22, 0, 1, NULL, NULL, 'online'),
(51, 'RES-6a18a074b3c71', 'shengu', '081288649597', '2026-05-29', '08:00:00', '16:00:00', 'buat acara keluarga besar anjay', 'disetujui', 'Surat_Permohonan_RES-6a18a074b3c71_shengu.pdf', 24, 'annidafitri1711@gmail.com', 'bolehhh tapi tetap jaga kebersihan yaa', 'KTP_RES-6a18a074b3c71_shengu.jpg', NULL, '2026-05-29', 20, 0, 0, 1, NULL, 'online'),
(54, 'RES-6a18ae33701c8', 'shengu', '085285936271', '2026-05-30', '08:00:00', '16:00:00', 'msbds/hidvfsh', 'disetujui', 'Surat_Permohonan_RES-6a18ae33701c8_shengu.pdf', 24, 'annidafitri1711@gmail.com', 'okee', 'KTP_RES-6a18ae33701c8_shengu.jpg', NULL, '2026-05-30', 23, 0, 0, NULL, NULL, 'online'),
(55, 'RES-6a304b8853378', 'Annida', '0895365195846', '2026-06-17', '08:00:00', '16:00:00', 'pameran', 'disetujui', 'Surat_Permohonan_RES-6a304b8853378_Annida.pdf', NULL, 'annidafitri1711@gmail.com', '', 'KTP_RES-6a304b8853378_Annida.jfif', NULL, '2026-06-17', 20, 0, 1, 2, 8, 'offline'),
(56, 'RES-6a36dbe90400e', 'rosiana', '08762839237', '2026-06-23', '08:00:00', '16:00:00', 'jabdajda', 'ditolak', 'Surat_Permohonan_RES-6a36dbe90400e_rosiana.docx', 25, 'annidaftr04@gmail.com', '', 'KTP_RES-6a36dbe90400e_rosiana.pdf', NULL, '2026-06-23', 21, 0, 0, NULL, 8, 'online');

-- --------------------------------------------------------

--
-- Table structure for table `sub_places`
--

CREATE TABLE `sub_places` (
  `id` int(11) NOT NULL,
  `place_id` int(11) NOT NULL,
  `nama_subtempat` varchar(50) NOT NULL,
  `gambar` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sub_places`
--

INSERT INTO `sub_places` (`id`, `place_id`, `nama_subtempat`, `gambar`, `deskripsi`) VALUES
(1, 20, 'Teater', 'assets/img/teater-gedung.jpg', 'Fasilitas teater di Gedung Seni Budaya menawarkan panggung yang luas...'),
(2, 20, 'Lobby', 'assets/img/lobby-gedung.jpg', 'Lobby Gedung Seni Budaya adalah ruang serbaguna yang luas...');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(30) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'anugrah', '$2y$10$ezrXaSsiVhfng6AdbVHbX..qf8SUkw3QPWi8Wc/uEWLGAM.vEI/Ve'),
(3, 'robby', '$2y$10$KbyARx/WTndIQ1M0uIxxue2Amxov65MbiOnxPVcETmyhFbmNZ2RZe'),
(4, 'kakaketjeh', '$2y$10$gbz8ty8EO/PAXCDbdExz5OipYHEla5eLo7gGa/gdDLZ0rcv8eRioC'),
(5, 'zanvils', '$2y$10$IFSryJxq0NUnb2pVd/olyeQOUKW1fFb7ZNUdcCq8TgtwETBlMeCMS'),
(6, 'kenzo', '$2y$10$JUXOM1FGoUpukNDL.Tj/YOZ8B9uElre8vU/c44jBeNi792k598t7O'),
(7, 'kenzo', '$2y$10$vfxO0KZ9KXDfQqnnsS2jde15mBOZ4w4njtJHtq0cEoXSZCN3RsTfG'),
(8, 'annida', '$2y$10$/pKSZ5NDYjbRU.djlDjDpOo5iLumPAYhUoC7Z5PF5ryyd10Taaaae'),
(9, 'El malik', '$2y$10$hWrVYCMltEL.ks58iJgfT.vmJraikIa5qagUCD5/mZHoLWp95c0cy'),
(10, 'admin', '$2y$10$JDztXiZChSVm/Tz439JK2.P.SXMj3ZY5fk.kI8XP5BjdKFDI6aLjS'),
(11, 'kakaketjeh', '$2y$10$U8CpEILSGb4cOS8BREt3YufpOCmQcyfRyaVWn2H2BNxRE/4vQo/US'),
(12, 'ASTUTI', '$2y$10$eFmjCbArO75ZPX9GGfuqsOOYGven6Bbzbg8cPLLorV0F//ca4ZElq'),
(13, 'wiraksana', '$2y$10$Ek8x.9hpotQWesjp7N2NA.9TG42LirsaPaoi0IhqoNrxsqtDI2CCm'),
(14, 'abangadestudio@gmail.com', '$2y$10$RAR8OV3J.xfQdpu7RoQXqOYpw1FkER7Immr.ulmfc1zRVtuBSd4.a'),
(15, 'smpalikhlascipondoh', '$2y$10$/2OA5oizS0liiRfeya2h..BsbCICF.lgWRbPWtCRmAsa.cFIW33Oe'),
(16, 'Shibyan', '$2y$10$X17EdMnTIDRT2wvim42qT.QzP1IYUIWhm23ZvO8onn1qDXofh1Uf6'),
(17, 'artventure1968@gmail.com', '$2y$10$GI3XYb0XmqxypAqhkkN4pe2zUk6zeiB1QdkoYIAkjoy8CvqlSXMXS'),
(18, 'aditiyaibnu', '$2y$10$okeRrzHmF16/M1ERQVklHeOi9/46.inC58rGe9U/5ens.E38QKsdm'),
(19, 'rafisyabanalfaridzi19', '$2y$10$/cR7qYp3ltosxS13dsg6Ye4rDSecj8ZppBm.pn31fj33JwTxzQbMa'),
(20, 'patriciabonauli', '$2y$10$tEv5L2C.bY4DMFzF7ALuU.Zy/lwq4rxZo6jgXaflSqJn1.huOHv5m'),
(21, 'msadanridho', '$2y$10$b5PTxACFzAHga.O415HlCusNy3xVyoCXJz1d2y/5XmjPvTlJYOMGS'),
(22, 'rohman123', '$2y$10$pnPXrCkd30xEcfH.a1126OjiZ1unYhfMUK7RbAX9AjXrW/gu2DGn6'),
(23, 'Forprasbanten', '$2y$10$zWa5rFHpGi061rEQiyQtGOOFRx22QjpUAQvFqKwcFtOKvGf8rjbcW'),
(24, 'shengu', '$2y$10$p.jaiekvj.t8rkdXCxrCru9efiReUWhZJ9ql8N4mc91/f9eY./yrC'),
(25, 'rosiana', '$2y$10$z5/1LV70.Cp7KXe5g5iPs.LKYtfi5R1OUdAzLxQFvC/FNdpO/9L6i');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_email_logs_reservation` (`reservation_id`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifikasi_log`
--
ALTER TABLE `notifikasi_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `places`
--
ALTER TABLE `places`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `place_images`
--
ALTER TABLE `place_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `place_id` (`place_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_booking` (`kode_booking`),
  ADD KEY `fk_sub_place` (`sub_place_id`),
  ADD KEY `fk_admin` (`admin_id`);

--
-- Indexes for table `sub_places`
--
ALTER TABLE `sub_places`
  ADD PRIMARY KEY (`id`),
  ADD KEY `place_id` (`place_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `email_logs`
--
ALTER TABLE `email_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifikasi_log`
--
ALTER TABLE `notifikasi_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `places`
--
ALTER TABLE `places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `place_images`
--
ALTER TABLE `place_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `sub_places`
--
ALTER TABLE `sub_places`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `email_logs`
--
ALTER TABLE `email_logs`
  ADD CONSTRAINT `fk_email_logs_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `place_images`
--
ALTER TABLE `place_images`
  ADD CONSTRAINT `place_images_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`id`),
  ADD CONSTRAINT `fk_sub_place` FOREIGN KEY (`sub_place_id`) REFERENCES `sub_places` (`id`);

--
-- Constraints for table `sub_places`
--
ALTER TABLE `sub_places`
  ADD CONSTRAINT `sub_places_ibfk_1` FOREIGN KEY (`place_id`) REFERENCES `places` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
