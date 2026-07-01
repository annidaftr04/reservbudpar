<?php
include 'includes/auth.php';
include '../db.php';

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
    <?php include 'includes/header.php'; ?>
    <title>Detail: <?= h($place['name']); ?> | Dashboard Admin</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/detail_tempat.css">

</head>

<body>
    <?php include 'includes/sidebar.php'; ?>

    <!-- MAIN DASHBOARD CONTENT -->
    <mai class="main-content animate__animated animate__fadeIn">
        <!-- Header -->
        <div class="page-header">
            <div>
                <h2 class="page-title mt-3">
                    <i class="fa-solid fa-landmark me-2 text-primary"></i>
                    <?= h($place['name']); ?>
                </h2>
                <p class="page-subtitle">
                    Detail informasi fasilitas dan lokasi wisata.
                </p>
            </div>
        </div>
        <!-- HERO IMAGE -->
        <div class="hero-card">
            <img
                id="displayImg"
                src="../<?= h($place['image']); ?>"
                class="hero-image"
                data-bs-toggle="modal"
                data-bs-target="#imgModal"
                alt="<?= h($place['name']); ?>">
            <div class="hero-overlay">
                <div class="hero-info">
                    <span>
                        <i class="fa-solid fa-images"></i>
                        <?= count($all_images); ?> Foto
                    </span>
                    <span>
                        <i class="fa-solid fa-building"></i>
                        <?= count($sub_places); ?> Sub Tempat
                    </span>
                </div>
            </div>
        </div>
        <!-- INFORMASI -->
        <div class="row mt-4 g-4">
            <div class="col-lg-4">
                <div class="info-card">
                    <h5>
                        <i class="fa-solid fa-location-dot text-primary me-2"></i>
                        Lokasi
                    </h5>
                    <p class="text-muted small">
                        Lokasi tempat wisata dapat dibuka melalui Google Maps.
                    </p>
                    <a href="<?= h($place['lokasi']); ?>"
                        target="_blank"
                        class="btn btn-outline-primary rounded-pill">
                        <i class="fa-solid fa-map-location-dot me-2"></i>
                        Buka Google Maps
                    </a>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="info-card">
                    <h5>
                        <i class="fa-solid fa-align-left text-primary me-2"></i>
                        Deskripsi
                    </h5>
                    <p class="description-text">
                        <?= nl2br(h($place['description'])); ?>
                    </p>
                </div>
            </div>
        </div>
        <!-- ===================== GALERI FOTO ===================== -->
        <div class="gallery-section mt-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-1">
                        <i class="fa-solid fa-images text-primary me-2"></i>
                        Galeri Tempat
                    </h4>
                    <small class="text-muted">
                        Dokumentasi fasilitas dan area tempat wisata.
                    </small>
                </div>
                <span class="badge bg-primary rounded-pill px-3 py-2">
                    <?= count($all_images); ?> Foto
                </span>
            </div>

            <div class="gallery-grid">
                <?php foreach ($all_images as $key => $img): ?>
                    <div class="gallery-item">
                        <img
                            src="../<?= h($img['image_path']); ?>"
                            alt="Gallery"
                            onclick="updateDisplay(this,'../<?= h($img['image_path']); ?>')"
                            data-bs-toggle="modal"
                            data-bs-target="#imgModal">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <!-- ===================== SUB TEMPAT ===================== -->

        <?php if (!empty($sub_places)): ?>
            <div class="sub-section mt-5">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="mb-1">
                            <i class="fa-solid fa-building text-primary me-2"></i>
                            Sub Tempat
                        </h4>
                        <small class="text-muted">
                            Area dan ruangan yang tersedia pada lokasi ini.
                        </small>
                    </div>

                    <span class="badge bg-success rounded-pill px-3 py-2">
                        <?= count($sub_places); ?> Area
                    </span>
                </div>

                <div class="row g-4">
                    <?php foreach ($sub_places as $sub): ?>
                        <div class="col-lg-4 col-md-6">
                            <div class="sub-card">
                                <img
                                    src="../<?= !empty($sub['gambar']) ? h($sub['gambar']) : h($place['image']); ?>"
                                    class="sub-card-image"
                                    alt="<?= h($sub['nama_subtempat']); ?>">
                                <div class="sub-card-body">
                                    <h5>
                                        <?= h($sub['nama_subtempat']); ?>
                                    </h5>
                                    <p>
                                        <?= !empty($sub['deskripsi']) ? h($sub['deskripsi']) : 'Belum ada deskripsi.'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="edit_tempat.php?id=<?= $place['id']; ?>"
                class="btn btn-primary btn-lg rounded-pill px-5 me-3">
                <i class="fa-solid fa-pen-to-square me-2"></i>
                Edit Tempat
            </a>
            <a href="kelola_tempat.php"
                class="btn btn-outline-secondary btn-lg rounded-pill px-5">
                <i class="fa-solid fa-arrow-left me-2"></i>
                Kembali
            </a>
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
        <script src="../assets/js/admin.js"></script>
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