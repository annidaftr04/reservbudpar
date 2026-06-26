<?php
session_start();
include '../db.php';

// Pastikan admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

// Ambil ID tempat dari URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: kelola_tempat.php');
    exit;
}

$place_id = intval($_GET['id']);

// Ambil data tempat utama
$sql_place = "SELECT * FROM places WHERE id = ?";
$stmt_place = $conn->prepare($sql_place);
$stmt_place->bind_param("i", $place_id);
$stmt_place->execute();
$result_place = $stmt_place->get_result();
$place = $result_place->fetch_assoc();
$stmt_place->close();

// Ambil data galeri foto tambahan
$sql_images = "SELECT * FROM place_images WHERE place_id = ?";
$stmt_images = $conn->prepare($sql_images);
$stmt_images->bind_param("i", $place_id);
$stmt_images->execute();
$result_images = $stmt_images->get_result();
$images = $result_images->fetch_all(MYSQLI_ASSOC);
$stmt_images->close();

// Ambil data sub tempat terkait
$sql_sub = "SELECT * FROM sub_places WHERE place_id = ?";
$stmt_sub = $conn->prepare($sql_sub);
$stmt_sub->bind_param("i", $place_id);
$stmt_sub->execute();
$result_sub = $stmt_sub->get_result();
$sub_places = $result_sub->fetch_all(MYSQLI_ASSOC);
$stmt_sub->close();

if (!$place) {
    header('Location: kelola_tempat.php');
    exit;
}

// Gabungkan gambar utama ke dalam array galeri
$all_images = array_merge([['image_path' => $place['image']]], $images);

function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail: <?= h($place['name']); ?> | Dashboard Admin</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon">

    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --dark: #0f172a;
            --muted: #64748b;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: var(--dark);
            margin: 0;
            display: flex;
        }

        /* Sidebar Base */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: white;
            position: fixed;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            z-index: 1000;
            padding: 2rem 1rem;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 1rem 2rem;
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--primary);
            letter-spacing: -1px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 1rem;
            color: var(--muted);
            text-decoration: none;
            font-weight: 600;
            border-radius: 12px;
            transition: 0.3s;
            margin-bottom: 5px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 15px -3px rgba(67, 97, 238, 0.3);
        }

        /* Main Content Grid */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 3rem;
            min-height: 100vh;
        }

        .luxury-dashboard-card {
            background: #ffffff;
            border-radius: 36px;
            padding: 2.5rem;
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.02);
        }

        /* LEFT SIDE: Showcase & Gallery */
        .showcase-wrapper {
            position: relative;
        }

        .main-display-box {
            width: 100%;
            height: 400px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.08);
            position: relative;
        }

        .main-display-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: 0.3s;
        }

        .floating-badge {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(10px);
            color: white;
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .thumb-scroll-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 12px;
            margin-top: 1.25rem;
        }

        .thumb-grid {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding-bottom: 4px;
        }

        .thumb-item {
            width: 90px;
            height: 65px;
            object-fit: cover;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.2s;
            border: 3px solid transparent;
            flex-shrink: 0;
        }

        .thumb-item.active {
            border-color: var(--primary);
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.15);
        }

        /* RIGHT SIDE: Clean Info Box */
        .info-header-badge {
            background: rgba(67, 97, 238, 0.08);
            color: var(--primary);
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 1rem;
        }

        .info-title {
            font-weight: 800;
            font-size: 2.2rem;
            color: var(--dark);
            letter-spacing: -1px;
            margin-bottom: 1.5rem;
        }

        .section-label {
            font-size: 0.8rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.75px;
            color: var(--primary);
            margin-bottom: 0.5rem;
        }

        .desc-premium-box {
            background: rgba(15, 23, 42, 0.02);
            border-left: 4px solid var(--dark);
            padding: 1.5rem;
            border-radius: 0 16px 16px 0;
            color: #475569;
            line-height: 1.7;
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
        }

        /* Sub Area Cards List */
        .sub-list-container {
            max-height: 250px;
            overflow-y: auto;
            padding-right: 5px;
        }

        .sub-inline-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            margin-bottom: 10px;
        }

        .sub-inline-img {
            width: 65px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        /* Buttons Grid */
        .actions-group {
            display: flex;
            gap: 12px;
            margin-top: auto;
            padding-top: 1rem;
        }

        .btn-action {
            padding: 12px 24px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            transition: 0.2s;
        }

        .btn-act-primary {
            background: var(--primary);
            color: white;
            border: none;
        }

        .btn-act-primary:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(67, 97, 238, 0.2);
            color: white;
        }

        .btn-act-light {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #e2e8f0;
        }

        .btn-act-light:hover {
            background: #e2e8f0;
            color: var(--dark);
            transform: translateY(-2px);
        }

        @media (max-width: 1199px) {
            .main-content {
                padding: 1.5rem;
            }

            .main-display-box {
                height: 320px;
            }
        }

        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <nav class="sidebar">
        <div class="sidebar-brand">
            <img src="../assets/img/logotng.png" width="35" alt="Logo">
            <span>Admin Reservasi</span>
        </div>
        <div class="mt-4">
            <a href="dashboard_admin.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="kelola_reserv.php" class="nav-link"><i class="fas fa-calendar-check"></i> Reservasi</a>
            <a href="kelola_surat.php" class="nav-link"><i class="fa-solid fa-map-location-dot"></i> Kelola Surat</a>
            <a href="kelola_tempat.php" class="nav-link active"><i class="fas fa-map-marked-alt"></i> Kelola Tempat</a>
            <a href="calendar.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Kalender</a>
            <hr class="mx-3" style="opacity:0.1;">
            <a href="logout_admin.php" class="nav-link text-danger" onclick="confirmAdminLogout(event)"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <!-- MAIN DASHBOARD CONTENT -->
    <main class="main-content animate__animated animate__fadeIn">
        <div class="luxury-dashboard-card">
            <div class="row g-5">

                <!-- KOLOM KIRI: SHOWCASE VISUAL & GALERI FOTO -->
                <div class="col-xl-6 col-lg-12">
                    <div class="showcase-wrapper">
                        <div class="main-display-box">
                            <img id="displayImg" src="../<?= h($place['image']); ?>" alt="Main Display" data-bs-toggle="modal" data-bs-target="#imgModal" style="cursor: zoom-in;">
                            <div class="floating-badge"><i class="fa-solid fa-images me-1"></i> Mode Pratinjau</div>
                        </div>

                        <div class="thumb-scroll-box">
                            <div class="section-label mb-2" style="font-size:11px;"><i class="fa-solid fa-camera me-1"></i> Pilih Alur Dokumentasi Gambar</div>
                            <div class="thumb-grid">
                                <?php foreach ($all_images as $key => $img): ?>
                                    <img src="../<?= h($img['image_path']); ?>"
                                        class="thumb-item <?= $key === 0 ? 'active' : '' ?>"
                                        onclick="updateDisplay(this, '../<?= h($img['image_path']); ?>')"
                                        alt="Thumb">
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: SPESIFIKASI DATA METADATA & KONTROL -->
                <div class="col-xl-6 col-lg-12 d-flex flex-column">
                    <div>
                        <div class="info-header-badge">
                            <i class="fa-solid fa-building-shield"></i> Validasi Fasilitas Dinas
                        </div>

                        <h1 class="info-title"><?= h($place['name']); ?></h1>

                        <div class="mb-4">
                            <div class="section-label">Pemetaan Geografis</div>
                            <a href="<?= h($place['lokasi']); ?>" target="_blank" class="text-primary fw-bold text-decoration-none small d-inline-flex align-items-center gap-1">
                                <i class="fa-solid fa-location-arrow"></i> Integrasikan Ke Google Maps Aplikasi <i class="fa-solid fa-up-right-from-square" style="font-size:10px;"></i>
                            </a>
                        </div>

                        <div class="mb-4">
                            <div class="section-label">Uraian / Deskripsi Pokok</div>
                            <div class="desc-premium-box">
                                <?= nl2br(h($place['description'])); ?>
                            </div>
                        </div>

                        <!-- SUB AREA AKAN MERENDER SECARA OTOMATIS HANYA JIKA ADA DATA (MISAL DI GEDUNG SENI BUDAYA) -->
                        <?php if (!empty($sub_places)): ?>
                            <div class="mb-4">
                                <div class="section-label">Daftar Sub-Area Ruangan Terdaftar</div>
                                <div class="sub-list-container">
                                    <?php foreach ($sub_places as $sub): ?>
                                        <div class="sub-inline-item">
                                            <img src="../<?= !empty($sub['gambar']) ? h($sub['gambar']) : h($place['image']); ?>" class="sub-inline-img" alt="Sub-Area">
                                            <div>
                                                <h6 class="fw-bold text-primary mb-1" style="font-size:14px;"><?= h($sub['nama_subtempat']); ?></h6>
                                                <p class="small text-muted mb-0 text-truncate" style="max-width: 250px; font-size:12px;"><?= h($sub['deskripsi']); ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- TOMBOL AKSI DI PANEL BAWAH -->
                    <div class="actions-group">
                        <a href="edit_tempat.php?id=<?= $place['id'] ?>" class="btn-action btn-act-primary">
                            <i class="fa-solid fa-sliders"></i> Ubah Struktur Data
                        </a>
                        <a href="kelola_tempat.php" class="btn-action btn-act-light">
                            <i class="fa-solid fa-arrow-left-long"></i> Kembali
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- LIGHTBOX MODAL FULL DISPLAY -->
    <div class="modal fade" id="imgModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content bg-transparent border-0">
                <div class="modal-body p-0 text-center">
                    <img id="modalImg" src="../<?= h($place['image']); ?>" class="img-fluid rounded-4 shadow-lg">
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS CORE DEPENDENCIES -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function updateDisplay(el, path) {
            const display = document.getElementById('displayImg');
            const modalImg = document.getElementById('modalImg');
            display.src = path;
            modalImg.src = path;

            document.querySelectorAll('.thumb-item').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }

        function confirmAdminLogout(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Logout Admin?',
                text: 'Anda yakin ingin keluar dari dashboard admin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'logout_admin.php';
                }
            });
        }
    </script>
</body>

</html>