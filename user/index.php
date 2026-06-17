<?php
session_start();
include '../db.php';

// Ambil data dari tabel places
$sql = "SELECT * FROM places";
$result = $conn->query($sql);
$places = $result->fetch_all(MYSQLI_ASSOC);

// Cek status login
$isLoggedIn = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Reservasi Fasilitas Wisata | Disbudpar Kota Tangerang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon">
    
    <style>
    :root {
        --primary-color: #ff8c42;
        --secondary-color: #0b2a59;
        --bg-light: #f8f9fc;
    }
    body {
        background-color: var(--bg-light);
        color: #333;
        font-family: 'Arial', sans-serif;
        margin: 0;
        padding: 0;
        padding-top: 100px;
    }

    /* ================= NEW MODERN FLOATING NAVBAR ================= */
    .navbar {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(15px) saturate(180%);
        -webkit-backdrop-filter: blur(15px) saturate(180%);
        border: 1px solid rgba(209, 213, 219, 0.3);
        padding: 12px 0;
        margin: 20px auto;
        width: 90%; 
        border-radius: 20px; 
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
    }

    .navbar.scrolled {
        width: 100%;
        margin: 0;
        border-radius: 0 0 20px 20px;
        background: rgba(255, 255, 255, 0.95) !important;
        padding: 8px 0;
    }

    .navbar-brand img {
        transition: transform 0.3s ease;
    }

    .navbar-brand:hover img {
        transform: scale(1.05);
    }

    .navbar-nav .nav-link {
        color: #1a202c !important;
        font-weight: 600;
        font-size: 0.95rem;
        margin: 0 12px;
        padding: 8px 16px !important;
        border-radius: 12px;
        transition: all 0.3s ease;
        position: relative;
    }

    .navbar-nav .nav-link:hover {
        color: var(--primary-color) !important;
        background: rgba(255, 140, 66, 0.08);
    }

    .navbar-nav .nav-link::after {
        content: "";
        position: absolute;
        bottom: 5px;
        left: 50%;
        width: 0;
        height: 2px;
        background: var(--primary-color);
        transition: all 0.3s ease;
        transform: translateX(-50%);
    }

    .navbar-nav .nav-link:hover::after {
        width: 30%;
    }

    .btn-login {
        background: linear-gradient(135deg, #ff8c42, #ffb347);
        color: white !important;
        border: none;
        border-radius: 14px;
        padding: 10px 28px !important;
        font-weight: 700;
        box-shadow: 0 4px 15px rgba(255, 140, 66, 0.3);
        transition: all 0.3s ease;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(255, 140, 66, 0.4);
        filter: brightness(1.1);
    }

    @media (max-width: 991px) {
        .navbar {
            width: 95%;
            padding: 10px 20px;
        }
        .navbar-collapse {
            background: white;
            margin-top: 15px;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .navbar-nav .nav-link {
            margin: 5px 0;
        }
    }

    /* ================= HERO CONTENT LUXURY STYLE ================= */
    .contents {
        position: relative;
        background-image: linear-gradient(rgba(5, 20, 41, 0.6), rgba(5, 20, 41, 0.3)), url('../assets/img/utama4.png');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        width: 100%;
        height: 100vh; 
        display: flex;
        justify-content: center;
        align-items: center; 
        text-align: center;
        color: #fff;
        padding-top: 80px; 
    }

    .hero-text-box {
        max-width: 900px;
        padding: 20px;
        z-index: 5;
        margin: auto; 
    }

    .hero-text-box h1 {
        font-size: 5rem; 
        font-weight: 800;
        line-height: 1.1;
        margin-bottom: 25px;
        text-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    .hero-text-box p {
        font-size: 1.3rem;
        max-width: 700px;
        margin: 0 auto 45px; 
        color: rgba(255,255,255,0.95);
    }

    .scroll-indicator {
        position: absolute;
        bottom: 30px;
        left: 50%;
        transform: translateX(-50%);
        animation: bounce 2s infinite;
        opacity: 0.7;
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% {transform: translateY(0) translateX(-50%);}
        40% {transform: translateY(-10px) translateX(-50%);}
        60% {transform: translateY(-5px) translateX(-50%);}
    }

    @media (max-width: 768px) {
        .hero-text-box h1 { font-size: 2.8rem; }
        .hero-text-box p { font-size: 1rem; }
    }

    /* ================= CARD DESIGN REVISIONS ================= */
    .section-title {
        font-weight: 800;
        color: var(--secondary-color);
        margin-bottom: 50px;
        font-size: 2.5rem;
    }

    .custom-card {
        border: none;
        border-radius: 25px;
        overflow: hidden;
        background: #fff;
        transition: all 0.4s ease;
        box-shadow: 0 15px 35px rgba(0,0,0,0.05);
        height: 100%;
        position: relative;
    }

    .custom-card:hover {
        transform: translateY(-12px);
        box-shadow: 0 25px 45px rgba(11, 42, 89, 0.12);
    }

    .card-img-wrapper {
        position: relative;
        overflow: hidden;
        height: 260px; 
    }

    .card-img-top {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: 0.8s ease;
    }

    .custom-card:hover .card-img-top {
        transform: scale(1.1);
    }

    .card-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(11, 42, 89, 0.9);
        color: white;
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 700;
        backdrop-filter: blur(5px);
        z-index: 2;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .card-body {
        padding: 28px;
        background: #fff;
    }

    .card-title {
        font-weight: 800;
        font-size: 1.35rem;
        color: var(--secondary-color);
        margin-bottom: 12px;
    }

    .location-text {
        color: #64748b;
        font-size: 0.9rem;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
    }

    .status-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        padding-top: 5px;
    }

    .btn-detail {
        background: linear-gradient(135deg, var(--secondary-color), #1a4a8e);
        color: white !important;
        border: none;
        border-radius: 15px;
        font-weight: 700;
        padding: 14px 20px;
        width: 100%;
        transition: 0.3s;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;
        text-decoration: none;
    }

    .btn-detail:hover {
        background: linear-gradient(135deg, var(--primary-color), #ffb347);
        box-shadow: 0 8px 20px rgba(255, 140, 66, 0.25);
    }

    /* ================= PREMIUM FOOTER STYLE ================= */
    .footer-custom {
        background: #051429; 
        color: #ffffff !important;
        padding: 100px 0 40px;
        position: relative;
        overflow: hidden;
    }

    .footer-custom::before {
        content: "";
        position: absolute;
        top: -50px;
        right: -50px;
        width: 250px;
        height: 250px;
        background: var(--primary-color);
        filter: blur(130px);
        opacity: 0.1;
        pointer-events: none;
    }

    .footer-top {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 50px;
        border-radius: 30px;
        margin-bottom: 80px;
        transition: 0.4s;
    }

    .footer-top:hover {
        border-color: var(--primary-color);
        transform: translateY(-5px);
    }

    .footer-title {
        font-size: 36px;
        font-weight: 800;
        color: #ffffff !important;
        margin-bottom: 15px;
    }

    .footer-custom p, 
    .footer-custom .text-muted {
        color: #d1d1d1 !important; 
        line-height: 1.7;
        font-size: 0.95rem;
    }

    .footer-section-title {
        color: #ffffff !important;
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 25px;
        position: relative;
        padding-bottom: 10px;
    }

    .footer-section-title::after {
        content: "";
        position: absolute;
        bottom: 0;
        left: 0;
        width: 35px;
        height: 3px;
        background: var(--primary-color);
        border-radius: 5px;
    }

    .footer-link {
        display: block;
        color: #cccccc !important;
        margin-bottom: 15px;
        text-decoration: none;
        transition: 0.3s ease;
    }

    .footer-link:hover {
        color: var(--primary-color) !important;
        transform: translateX(8px);
    }

    .contact-info p {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .contact-info i {
        color: var(--primary-color) !important;
        margin-top: 5px;
        width: 20px;
        text-align: center;
    }

    .social-group {
        display: flex;
        gap: 12px;
        margin-top: 20px;
    }

    .social-group a {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #ffffff !important;
        transition: 0.3s;
        text-decoration: none;
    }

    .social-group a:hover {
        background: var(--primary-color);
        border-color: var(--primary-color);
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(255, 140, 66, 0.3);
    }

    .copyright-section text-center {
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        padding-top: 30px;
        margin-top: 60px;
        color: #999999 !important;
        font-size: 0.85rem;
    }

    .copyright-section b {
        color: #ffffff;
    }

    .btn-cta-big {
        background: linear-gradient(135deg, #ff8c42, #ffb347);
        border: none;
        color: white !important;
        font-weight: 700;
        border-radius: 50px;
        transition: 0.3s;
        text-decoration: none;
    }

    .btn-cta-big:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 25px rgba(255, 140, 66, 0.4);
    }

    @media (max-width: 768px) {
        .contents { height: 250px; }
        .navbar { border-radius: 20px; padding: 10px; }
        .navbar-nav .nav-link { margin-left: 0; margin-bottom: 10px; }
    }
    @media (max-width: 480px) {
        .contents { height: 200px; }
    }
    </style>
</head>

<body>

<!-- ================= NAVBAR ================= -->
<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <!-- LOGO -->
        <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="../assets/img/logotng.png" style="height:45px; margin-right:10px;">
            <img src="../assets/img/kerenjasa.png" style="height:40px;">
        </a>

        <!-- TOGGLE -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="fas fa-bars text-dark"></span>
        </button>

        <!-- MENU -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link" href="#">
                        <i class="fas fa-th-large me-1 small"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= $isLoggedIn ? 'reservasi.php' : 'login.php' ?>">
                        <i class="fas fa-calendar-check me-1 small text-danger"></i> Reservasi
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-history me-1 small"></i> Riwayat
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#footer">
                        <i class="fas fa-headset me-1 small"></i> Kontak
                    </a>
                </li>
                <!-- BUTTON LOGIN -->
                <li class="nav-item ms-lg-3 mt-3 mt-lg-0">
                    <?php if ($isLoggedIn): ?>
                        <a class="btn btn-login w-100" href="#" onclick="confirmLogout(event)">Keluar</a>
                    <?php else: ?>
                        <a class="btn btn-login w-100" href="login.php">Masuk</a>
                    <?php endif; ?>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- ================= HERO ================= -->
<div class="contents">
    <div class="container">
        <div class="hero-text-box">
            <h1 class="animate__animated animate__fadeInDown">
                Jelajahi Sarana <br> 
                <span style="color: var(--primary-color);">Fasilitas Wisata</span>
            </h1>
            <p class="animate__animated animate__fadeInUp">
                Sistem Informasi resmi pengajuan izin pemanfaatan sarana pariwisata, taman kota, 
                dan museum yang dikelola oleh Dinas Kebudayaan dan Pariwisata Kota Tangerang.
            </p>
            <div class="d-flex justify-content-center gap-3 animate__animated animate__fadeInUp" style="animation-delay: 0.5s;">
                <a href="#dashboard" class="btn btn-login" style="padding: 15px 40px; font-size: 1.1rem;">
                    Mulai Reservasi <i class="fas fa-chevron-right ms-2"></i>
                </a>
                <a href="#footer" class="btn btn-outline-light" style="padding: 15px 40px; border-radius: 30px; font-weight: 600;">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </div>
    <div class="scroll-indicator">
        <a href="#dashboard" class="text-white">
            <p class="small mb-1">Scroll</p>
            <i class="fas fa-chevron-down"></i>
        </a>
    </div>
</div>

<!-- ================= DESTINASI SECTIONS ================= -->
<div class="container py-5" id="dashboard">
    <div class="text-center">
        <h2 class="section-title">Fasilitas Wisata Terdaftar</h2>
    </div>
    
    <div class="row g-4">
        <?php foreach ($places as $place) : ?>
            <div class="col-lg-4 col-md-6 col-sm-12">
                <div class="custom-card">
                    <?php 
                        $category = "Fasilitas Publik";
                        if (stripos($place['name'], 'Taman') !== false) $category = "Taman Kota";
                        if (stripos($place['name'], 'Gedung') !== false) $category = "Gedung Seni";
                        if (stripos($place['name'], 'Museum') !== false) $category = "Museum Sejarah";
                    ?>
                    <div class="card-badge"><?= $category; ?></div>
                    
                    <div class="card-img-wrapper">
                        <img src="../<?= htmlspecialchars($place['image']); ?>" class="card-img-top" alt="<?= htmlspecialchars($place['name']); ?>">
                    </div>
                    
                    <div class="card-body">
                        <h5 class="card-title text-truncate"><?= htmlspecialchars($place['name']); ?></h5>
                        
                        <div class="location-text">
                            <i class="fas fa-map-marker-alt text-danger me-2"></i>
                            <span class="text-truncate">
                                <?php if(!empty($place['lokasi'])): ?>
                                    <a href="<?= htmlspecialchars($place['lokasi']) ?>" target="_blank" class="text-muted" style="text-decoration:none;">Buka Lokasi Maps →</a>
                                <?php else: ?>
                                    Kota Tangerang
                                <?php endif; ?>
                            </span>
                        </div>
                        
                        <div class="status-container">
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill border border-success border-opacity-25">
                                <i class="fas fa-check-circle me-1"></i> Izin Terbuka
                            </span>
                            <span class="small text-muted fw-semibold">
                                <i class="fa-solid fa-users text-primary me-1"></i> Publik
                            </span>
                        </div>

                        <a href="detail.php?id=<?= $place['id']; ?>" class="btn btn-detail">
                            <span>Eksplor & Ajukan Izin</span>
                            <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ================= FOOTER ================= -->
<footer class="footer-custom" id="footer">
    <div class="container">
        <div class="footer-top">
            <div class="row align-items-center">
                <div class="col-lg-8 text-center text-lg-start">
                    <h2 class="footer-title">Pelayanan Publik Disbudpar ✨</h2>
                    <p class="text-muted mb-0">Temukan kenyamanan reservasi tempat wisata dan fasilitas publik Kota Tangerang dalam satu genggaman.</p>
                </div>
                <div class="col-lg-4 text-center text-lg-end mt-4 mt-lg-0">
                    <a href="#dashboard" class="btn btn-cta-big px-5 py-3 shadow-lg">Mulai Ajukan Izin →</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-4 mb-5 mb-lg-0">
                <div class="mb-4">
                    <img src="../assets/img/logotng.png" style="height:50px;" class="me-2">
                    <img src="../assets/img/kerenjasa.png" style="height:45px;">
                </div>
                <p class="text-muted pe-lg-4">
                    Platform resmi reservasi pariwisata Kota Tangerang. Kami hadir untuk memudahkan akses masyarakat terhadap fasilitas publik dan destinasi wisata unggulan.
                </p>
                <div class="social-group">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                </div>
            </div>

            <div class="col-lg-2 col-md-6 mb-5 mb-md-0">
                <h5 class="footer-section-title">Menu Utama</h5>
                <a href="#" class="footer-link">Home</a>
                <a href="#dashboard" class="footer-link">Destinasi</a>
                <a href="#" class="footer-link">Tentang Kami</a>
                <a href="#footer" class="footer-link">Kontak Layanan</a>
            </div>

            <div class="col-lg-2 col-md-6 mb-5 mb-md-0">
                <h5 class="footer-section-title">Layanan</h5>
                <a href="#" class="footer-link">Reservasi Tempat</a>
                <a href="#" class="footer-link">Cek Riwayat</a>
                <a href="#" class="footer-link">Bantuan User</a>
                <a href="#" class="footer-link">Kebijakan Privasi</a>
            </div>

            <div class="col-lg-4 contact-info">
                <h5 class="footer-section-title">Hubungi Kami</h5>
                <p>
                    <i class="fas fa-map-marker-alt"></i>
                    Jl. Mayjen Sutoyo No.11, Sukarasa, Kec. Tangerang, Kota Tangerang, Banten 15111
                </p>
                <p>
                    <i class="fas fa-phone-alt"></i>
                    +62 857-2888-6957 (WhatsApp)
                </p>
                <p>
                    <i class="fas fa-envelope"></i>
                    disbudpar@tangerangkota.go.id
                </p>
            </div>
        </div>

        <div class="copyright-section text-center">
            <div class="row align-items-center">
                <div class="col-md-6 text-md-start mb-3 mb-md-0">
                    &copy; 2026 <b>Disbudpar Kota Tangerang</b> 
                </div>
                <div class="col-md-6 text-md-end">
                    Sistem Informasi Reservasi Fasilitas Wisata
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- ================= SCRIPTS ================= -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
window.addEventListener("scroll", function() {
    let navbar = document.querySelector(".navbar");
    navbar.classList.toggle("scrolled", window.scrollY > 50);
});

function confirmLogout(event) {
    event.preventDefault();
    Swal.fire({
        title: 'Logout?',
        text: 'Anda yakin ingin keluar dari akun?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff8c42',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
        background: '#ffffff',
        borderRadius: '20px'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang logout...',
                text: 'Mohon tunggu sebentar',
                icon: 'success',
                timer: 1200,
                showConfirmButton: false,
                allowOutsideClick: false
            });
            setTimeout(() => {
                window.location.href = 'logout.php';
            }, 1200);
        }
    });
}
</script>

</body>
</html>