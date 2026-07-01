<?php
include 'includes/auth.php';
include '../db.php';

$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    header('Location: kelola_tempat.php');
    exit;
}

$stmt_place = $conn->prepare("SELECT * FROM places WHERE id = ?");
$stmt_place->bind_param("i", $id);
$stmt_place->execute();
$place = $stmt_place->get_result()->fetch_assoc();
$stmt_place->close();

$stmt_images = $conn->prepare("SELECT * FROM place_images WHERE place_id = ?");
$stmt_images->bind_param("i", $id);
$stmt_images->execute();
$images = $stmt_images->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_images->close();

if (!$place) {
    header('Location: kelola_tempat.php');
    exit;
}

function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <title>Edit Tempat | Dashboard Admin</title>

    <?php include 'includes/header.php'; ?>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/edit_tempat.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content animate__animated animate__fadeIn">
        <header class="glass-header">
            <div>
                <h1 class="h3 fw-800 mb-1">Update Data Wisata</h1>
                <p class="text-muted small mb-0">Sesuaikan informasi detail untuk <strong><?= h($place['name']); ?></strong></p>
            </div>
            <a href="kelola_tempat.php" class="btn btn-light rounded-4 px-4 fw-bold border">
                <i class="fa-solid fa-chevron-left me-2"></i> Kembali
            </a>
        </header>

        <form action="update_tempat.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?= h($place['id']); ?>">

            <div class="content-card">
                <div class="row g-5">
                    <div class="col-xl-7">
                        <div class="mb-4">
                            <label class="form-label">Nama Destinasi</label>
                            <input type="text" class="form-control" name="name" value="<?= h($place['name']); ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Link Lokasi (Google Maps URL)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-2 border-end-0 rounded-start-4"><i class="fa-solid fa-location-dot text-danger"></i></span>
                                <input type="url" class="form-control border-start-0" name="lokasi" value="<?= h($place['lokasi']); ?>" required>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <textarea class="form-control" name="description" rows="10" required><?= h($place['description']); ?></textarea>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <label class="form-label">Gambar Utama Display</label>
                        <div class="premium-upload shadow-sm" onclick="document.getElementById('placeImage').click()">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <h6 class="fw-bold mb-1">Ganti Foto Sampul</h6>
                            <p class="text-muted small">JPG, PNG, atau WEBP (Max 2MB)</p>
                            <input type="file" id="placeImage" name="image" class="d-none" accept="image/*">
                        </div>

                        <div class="main-preview-container shadow">
                            <img src="../<?= h($place['image']); ?>" id="mainPreview" alt="Preview">
                        </div>
                    </div>
                </div>

                <div style="margin: 4rem 0;">
                    <hr style="opacity: 0.05;">
                </div>

                <div class="gallery-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <h4 class="fw-800 mb-1">Galeri Foto Tambahan</h4>
                            <p class="text-muted small">Foto-foto ini akan muncul di slider detail pengunjung</p>
                        </div>
                        <button type="button" class="btn btn-primary rounded-4 fw-bold px-4 shadow-sm" onclick="document.getElementById('new_images').click()">
                            <i class="fa-solid fa-plus me-2"></i> Tambah Foto
                        </button>
                        <input type="file" id="new_images" name="new_images[]" class="d-none" multiple accept="image/*">
                    </div>

                    <div id="gallery-container" class="gallery-grid">
                        <?php foreach ($images as $img): ?>
                            <div class="gallery-card animate__animated animate__zoomIn" id="img-box-<?= $img['id']; ?>">
                                <img src="../<?= h($img['image_path']); ?>">
                                <button type="button" class="btn-del-mini" onclick="deleteImg(<?= $img['id']; ?>)">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-5 pt-5 border-top">
                    <button type="submit" class="btn-save-lg">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Semua Perubahan
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        // Preview Realtime Gambar Utama
        document.getElementById('placeImage').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (event) => document.getElementById('mainPreview').src = event.target.result;
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Preview Galeri Baru
        document.getElementById('new_images').addEventListener('change', function(e) {
            const container = document.getElementById('gallery-container');
            for (let file of e.target.files) {
                const reader = new FileReader();
                reader.onload = (ev) => {
                    const html = `
                        <div class="gallery-card animate__animated animate__bounceIn" style="border: 2px solid var(--primary)">
                            <img src="${ev.target.result}">
                            <div class="badge bg-primary position-absolute bottom-0 start-0 m-2">Baru</div>
                        </div>`;
                    container.insertAdjacentHTML('beforeend', html);
                }
                reader.readAsDataURL(file);
            }
        });

        // AJAX Hapus Gambar
        function deleteImg(id) {
            Swal.fire({
                title: 'Hapus foto?',
                text: "Foto akan langsung terhapus dari server!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Hapus!'
            }).then((res) => {
                if (res.isConfirmed) {
                    $.post('delete_image.php', {
                        id: id,
                        place_id: <?= $id; ?>
                    }, function(data) {
                        if (data.trim() === 'success') {
                            $(`#img-box-${id}`).addClass('animate__animated animate__zoomOut').one('animationend', function() {
                                $(this).remove();
                            });
                        }
                    });
                }
            });
        }
    </script>
</body>

</html>