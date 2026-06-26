<?php
include '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

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

function h($string) { return htmlspecialchars($string, ENT_QUOTES, 'UTF-8'); }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Premium Edit: <?= h($place['name']); ?> | TNG Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon">

    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --bg: #f8fafc;
            --card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0;
            display: flex;
        }

        /* Sidebar Styling Sesuai Template */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: white;
            position: fixed;
            border-right: 1px solid rgba(0,0,0,0.05);
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
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-bottom: 5px;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 15px -3px rgba(67, 97, 238, 0.3);
            transform: translateX(5px);
        }

        /* Main Content */
        .main-content { margin-left: 280px; flex: 1; padding: 4rem; min-height: 100vh; }

        .glass-header {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            padding: 2rem; border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            margin-bottom: 3rem;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        }

        .content-card {
            background: white; border-radius: 30px; padding: 3rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0,0,0,0.02);
        }

        .form-label { font-weight: 700; color: var(--text-main); font-size: 0.95rem; margin-bottom: 12px; }
        .form-control {
            border-radius: 16px; padding: 15px 20px; border: 2px solid #f1f5f9;
            font-weight: 500; transition: all 0.3s ease; background: #f8fafc;
        }
        .form-control:focus {
            border-color: var(--primary); background: white;
            box-shadow: 0 0 0 5px rgba(67, 97, 238, 0.1);
        }

        /* Upload Area Mewah */
        .premium-upload {
            border: 3px dashed #e2e8f0; border-radius: 20px;
            padding: 3rem 2rem; text-align: center; cursor: pointer;
            transition: all 0.3s ease; background: #ffffff;
            position: relative; overflow: hidden;
        }
        .premium-upload:hover { border-color: var(--primary); background: #f0f4ff; }
        .premium-upload i { font-size: 2.5rem; color: var(--primary); margin-bottom: 1rem; }

        .main-preview-container {
            position: relative; margin-top: 20px;
            border-radius: 24px; overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            height: 300px;
        }
        .main-preview-container img { width: 100%; height: 100%; object-fit: cover; }

        /* Gallery Grid */
        .gallery-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 1.5rem; margin-top: 2rem;
        }
        .gallery-card {
            position: relative; border-radius: 18px; overflow: hidden;
            aspect-ratio: 1; box-shadow: 0 10px 15px rgba(0,0,0,0.05);
            transition: 0.3s;
        }
        .gallery-card:hover { transform: scale(1.05); }
        .gallery-card img { width: 100%; height: 100%; object-fit: cover; }

        .btn-del-mini {
            position: absolute; top: 10px; right: 10px;
            background: rgba(239, 68, 68, 0.9); color: white;
            border: none; border-radius: 10px; width: 32px; height: 32px;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(5px); transition: 0.3s;
        }
        .btn-del-mini:hover { background: #ef4444; transform: rotate(90deg); }

        .btn-save-lg {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; border: none; padding: 18px 45px;
            border-radius: 20px; font-weight: 800; font-size: 1.1rem;
            box-shadow: 0 15px 30px rgba(67, 97, 238, 0.3);
            transition: all 0.3s ease;
        }
        .btn-save-lg:hover { transform: translateY(-5px); box-shadow: 0 20px 40px rgba(67, 97, 238, 0.4); color: white; }

        @media (max-width: 1024px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; padding: 2rem; }
        }
    </style>
</head>
<body>

    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="../assets/img/logotng.png" width="35" alt="Logo">
            <span>Admin Reservasi</span>
        </div>
        <div class="mt-4">
            <a href="dashboard_admin.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="kelola_reserv.php" class="nav-link "><i class="fas fa-calendar-check"></i> Reservasi</a>
            <a href="kelola_surat.php" class="nav-link"><i class="fas fa-map-location-dot"></i> Kelola Surat</a>
            <a href="kelola_tempat.php" class="nav-link active"><i class="fas fa-map-marked-alt"></i> Kelola Tempat</a>
            <a href="calendar.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Kalender</a>
            <hr class="mx-3">
            <a href="logout_admin.php"class="nav-link text-danger"onclick="confirmAdminLogout(event)"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </div>
    </nav>

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

                <div style="margin: 4rem 0;"><hr style="opacity: 0.05;"></div>

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
                    $.post('delete_image.php', { id: id, place_id: <?= $id; ?> }, function(data) {
                        if(data.trim() === 'success') {
                            $(`#img-box-${id}`).addClass('animate__animated animate__zoomOut').one('animationend', function() {
                                $(this).remove();
                            });
                        }
                    });
                }
            });
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
                window.location.href = 'logout_admin.php';
                    }, 1200);
                }
            });
        }
    </script>
</body>
</html>