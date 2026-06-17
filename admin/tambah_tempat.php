<?php
include '../db.php';
session_start();

// Pastikan admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

$message = "";

/* ============ PROSES FORM ============ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name']);
    $description = trim($_POST['description']);
    $lokasi      = trim($_POST['lokasi']);
    $imagePath   = ''; 
    $upload_success = false;

    // Cek apakah ada gambar utama
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedExt  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext         = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $message = "File gambar utama harus berupa format gambar (JPG, PNG, WEBP).";
        } else {
            $uploadDir = __DIR__ . '/../assets/img/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

            $imageName = uniqid('img_', true) . '.' . $ext;
            $target    = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $imagePath = 'assets/img/' . $imageName;
                $upload_success = true;
            } else {
                $message = "Gagal mengunggah gambar utama.";
            }
        }
    } else {
        $message = "Silakan pilih gambar utama untuk sampul.";
    }

    if ($upload_success && $name && $description && $lokasi) {
        $stmt_place = $conn->prepare("INSERT INTO places (name, description, image, lokasi) VALUES (?, ?, ?, ?)");
        $stmt_place->bind_param("ssss", $name, $description, $imagePath, $lokasi);

        if ($stmt_place->execute()) {
            $new_place_id = $conn->insert_id;

            // Proses unggahan galeri foto multi-upload
            if (isset($_FILES['gallery_images'])) {
                $gallery_files = $_FILES['gallery_images'];
                foreach ($gallery_files['tmp_name'] as $key => $tmp_name) {
                    if ($gallery_files['error'][$key] === UPLOAD_ERR_OK) {
                        $gallery_ext = strtolower(pathinfo($gallery_files['name'][$key], PATHINFO_EXTENSION));
                        if (in_array($gallery_ext, ['jpg', 'jpeg', 'png', 'webp'])) {
                            $gallery_image_name = uniqid('gallery_', true) . '.' . $gallery_ext;
                            $gallery_target = $uploadDir . $gallery_image_name;

                            if (move_uploaded_file($tmp_name, $gallery_target)) {
                                $gal_path = 'assets/img/' . $gallery_image_name;
                                $stmt_gallery = $conn->prepare("INSERT INTO place_images (place_id, image_path) VALUES (?, ?)");
                                $stmt_gallery->bind_param("is", $new_place_id, $gal_path);
                                $stmt_gallery->execute();
                            }
                        }
                    }
                }
            }
            header('Location: kelola_tempat.php');
            exit;
        } else {
            $message = "Gagal simpan ke database: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Tambah Tempat Baru | Admin Panel</title>
    
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

        /* Sidebar Styling (Konsisten) */
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

        /* Content Area */
        .main-content { margin-left: 280px; flex: 1; padding: 3rem; min-height: 100vh; }

        .glass-header {
            background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(10px);
            padding: 2rem; border-radius: 24px; border: 1px solid rgba(255,255,255,0.5);
            margin-bottom: 2.5rem; display: flex; justify-content: space-between; align-items: center;
        }

        .content-card {
            background: white; border-radius: 30px; padding: 3rem;
            box-shadow: 0 20px 50px rgba(0,0,0,0.04);
        }

        .form-label { font-weight: 700; font-size: 0.85rem; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; }
        
        .form-control {
            border-radius: 14px; padding: 14px 18px; border: 2.5px solid #f1f5f9;
            font-weight: 500; transition: 0.3s; background: #f8fafc;
        }

        .form-control:focus { border-color: var(--primary); background: white; box-shadow: 0 0 0 5px rgba(67, 97, 238, 0.08); }

        /* Upload Area */
        .upload-area {
            border: 3px dashed #e2e8f0; border-radius: 20px;
            padding: 2.5rem; text-align: center; cursor: pointer;
            transition: 0.3s; background: #ffffff; margin-bottom: 1.5rem;
        }
        .upload-area:hover { border-color: var(--primary); background: rgba(67, 97, 238, 0.02); }

        #mainPreview {
            width: 100%; height: 250px; object-fit: cover;
            border-radius: 18px; display: none; margin-top: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .gallery-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 1rem; margin-top: 1.5rem;
        }

        .gallery-preview-box {
            border-radius: 12px; overflow: hidden; aspect-ratio: 1;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .gallery-preview-box img { width: 100%; height: 100%; object-fit: cover; }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white; border: none; padding: 16px 40px;
            border-radius: 18px; font-weight: 800; transition: 0.3s;
        }
        .btn-submit:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(67, 97, 238, 0.3); color: white; }

        @media (max-width: 992px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; padding: 1.5rem; }
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
            <a href="kelola_tempat.php" class="nav-link active"><i class="fas fa-map-marked-alt"></i> Kelola Tempat</a>
            <a href="calendar.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Kalender</a>
            <hr class="mx-3">
            <a href="logout_admin.php"class="nav-link text-danger"onclick="confirmAdminLogout(event)"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </div>
    </nav>

    <main class="main-content animate__animated animate__fadeIn">
        <header class="glass-header">
            <div>
                <h1 class="h4 fw-800 mb-1">Registrasi Tempat Baru</h1>
                <p class="text-muted small mb-0">Tambahkan objek wisata atau fasilitas umum kota.</p>
            </div>
            <a href="kelola_tempat.php" class="btn btn-light rounded-4 px-4 fw-bold border">
                <i class="fa-solid fa-arrow-left me-2"></i> Kembali
            </a>
        </header>

        <?php if ($message): ?>
            <div class="alert alert-danger rounded-4 shadow-sm animate__animated animate__shakeX">
                <i class="fa-solid fa-circle-exclamation me-2"></i> <?= $message ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data" id="addPlaceForm">
            <div class="content-card">
                <div class="row g-5">
                    <div class="col-xl-7">
                        <div class="mb-4">
                            <label class="form-label">Nama Tempat</label>
                            <input type="text" class="form-control" name="name" placeholder="Masukkan nama tempat..." required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">URL Lokasi (Google Maps)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-2 border-end-0 rounded-start-4"><i class="fa-solid fa-location-dot text-danger"></i></span>
                                <input type="url" class="form-control border-start-0" name="lokasi" placeholder="Tempel link share Google Maps di sini..." required>
                            </div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Deskripsi Lengkap</label>
                            <textarea class="form-control" name="description" rows="10" placeholder="Jelaskan fasilitas dan informasi tempat ini..." required></textarea>
                        </div>
                    </div>

                    <div class="col-xl-5">
                        <div class="mb-5">
                            <label class="form-label">Gambar Utama (Sampul)</label>
                            <div class="upload-area shadow-sm" onclick="document.getElementById('image').click()">
                                <i class="fa-solid fa-image text-muted opacity-50 mb-2"></i>
                                <h6 class="fw-bold mb-1">Pilih Gambar Utama</h6>
                                <p class="text-muted small mb-0">Format: JPG, PNG, WEBP (Maks 2MB)</p>
                                <input type="file" id="image" name="image" class="d-none" accept="image/*" required>
                            </div>
                            <img id="mainPreview" src="#" alt="Preview">
                        </div>

                        <div>
                            <label class="form-label">Galeri Foto Tambahan</label>
                            <div class="upload-area py-4 shadow-sm" onclick="document.getElementById('gallery_images').click()">
                                <i class="fa-solid fa-images text-primary opacity-50 mb-2" style="font-size: 1.5rem;"></i>
                                <p class="small fw-bold mb-0">Klik untuk upload banyak foto</p>
                                <input type="file" id="gallery_images" name="gallery_images[]" class="d-none" multiple accept="image/*">
                            </div>
                            <div id="gallery-preview" class="gallery-grid"></div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-center mt-5 pt-5 border-top">
                    <button type="button" class="btn-submit px-5" onclick="confirmAdd()">
                        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Simpan Data Tempat
                    </button>
                </div>
            </div>
        </form>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Preview Gambar Utama
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('mainPreview');
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    preview.src = event.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(this.files[0]);
            }
        });

        // Preview Multi-upload Galeri
        document.getElementById('gallery_images').addEventListener('change', function(e) {
            const container = document.getElementById('gallery-preview');
            container.innerHTML = ''; // Reset preview
            for (let file of e.target.files) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    const div = document.createElement('div');
                    div.className = 'gallery-preview-box animate__animated animate__zoomIn';
                    div.innerHTML = `<img src="${event.target.result}">`;
                    container.appendChild(div);
                }
                reader.readAsDataURL(file);
            }
        });

        // Konfirmasi SweetAlert
        function confirmAdd() {
            Swal.fire({
                title: 'Simpan Tempat Baru?',
                text: "Pastikan semua data sudah terisi dengan benar.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#4361ee',
                confirmButtonText: 'Ya, Simpan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('addPlaceForm').submit();
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