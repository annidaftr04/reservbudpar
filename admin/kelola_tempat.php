<?php
include '../db.php';
session_start();

// Pastikan admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

// Proses Hapus Data Tempat
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $id = intval($_POST['id']);
    $sql = "SELECT image FROM places WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $place = $result->fetch_assoc();

    if ($place && $place['image']) {
        $imagePath = "../" . $place['image'];
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $sql = "DELETE FROM places WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header('Location: kelola_tempat.php');
        exit;
    }
}

// Pagination
$items_per_page = 9; // Grid 3x3 sempurna
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

$sql = "SELECT * FROM places ORDER BY id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$places = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total_data = $conn->query("SELECT COUNT(*) AS total FROM places")->fetch_assoc()['total'];
$total_pages = ceil($total_data / $items_per_page);

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
    <title>Manajemen Konten Obyek Wisata | TNG Admin</title>

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
            --muted-gray: #64748b;
            --light-bg: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f1f5f9;
            color: var(--dark);
            margin: 0;
            display: flex;
        }

        /* Sidebar Glassmorphism Container */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: white;
            position: fixed;
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            z-index: 1100;
            padding: 2.5rem 1.5rem;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 2.5rem;
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary);
            letter-spacing: -1px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 14px 1rem;
            color: var(--muted-gray);
            text-decoration: none;
            font-weight: 600;
            border-radius: 14px;
            transition: 0.3s;
            margin-bottom: 8px;
        }

        .nav-link:hover,
        .nav-link.active {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(67, 97, 238, 0.4);
        }

        /* Layout Area Base */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 3rem;
            min-height: 100vh;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            padding: 2rem;
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            margin-bottom: 3rem;
        }

        .header-title h1 {
            font-weight: 800;
            font-size: 2.1rem;
            letter-spacing: -1.2px;
            margin-bottom: 4px;
            color: var(--dark);
        }

        /* Responsive Matrix Grid */
        .place-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 22px;
        }

        .place-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.01);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            height: 100%;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.03);
        }

        .place-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
        }

        .card-img-wrapper {
            position: relative;
            height: 210px;
            overflow: hidden;
            background: #e2e8f0;
        }

        .card-img-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.6s ease;
        }

        .place-card:hover .card-img-wrapper img {
            transform: scale(1.08);
        }

        /* Kategori Penanda Khusus Otomatis Sesuai Naskah Penelitian */
        .card-top-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 5px 14px;
            border-radius: 50px;
            text-transform: uppercase;
        }

        .card-body-content {
            padding: 1.5rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .place-name {
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--dark);
            margin-bottom: 6px;
            letter-spacing: -0.5px;
        }

        .place-loc {
            font-size: 0.85rem;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-bottom: 14px;
            font-weight: 600;
        }

        .place-desc {
            font-size: 0.88rem;
            color: #94a3b8;
            line-height: 1.6;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        /* Bar Aksi Dashboard Bagian Bawah */
        .card-action-bar {
            display: flex;
            border-top: 1px solid #f1f5f9;
            background: #fafafa;
            padding: 10px 1.5rem;
            gap: 10px;
        }

        .control-btn {
            flex: 1;
            padding: 9px;
            border-radius: 12px;
            font-size: 0.82rem;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            transition: 0.2s;
            border: none;
        }

        .btn-view-md {
            background: rgba(67, 97, 238, 0.08);
            color: var(--primary);
        }

        .btn-view-md:hover {
            background: var(--primary);
            color: white;
        }

        .btn-edit-md {
            background: rgba(16, 185, 129, 0.08);
            color: #10b981;
        }

        .btn-edit-md:hover {
            background: #10b981;
            color: white;
        }

        .btn-delete-md {
            background: rgba(239, 68, 68, 0.08);
            color: #ef4444;
        }

        .btn-delete-md:hover {
            background: #ef4444;
            color: white;
        }

        /* Modernized Add Button */
        .btn-add-modern {
            background: var(--primary);
            color: white;
            padding: 14px 24px;
            border-radius: 16px;
            font-weight: 700;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
            transition: 0.3s;
        }

        .btn-add-modern:hover {
            transform: translateY(-2px);
            background: var(--secondary);
            color: white;
        }

        /* Minimalist Rounded Pagination */
        .pagination-container {
            margin-top: 4rem;
            display: flex;
            justify-content: center;
        }

        .page-link {
            border: none;
            background: white;
            margin: 0 4px;
            border-radius: 12px;
            color: var(--dark);
            font-weight: 700;
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.02);
            transition: 0.2s;
        }

        .page-link:hover {
            background: #e2e8f0;
        }

        .active .page-link {
            background: var(--primary) !important;
            color: white !important;
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1.5rem;
            }

            .btn-add-modern {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="../assets/img/logotng.png" width="35" alt="Logo">
            <span>Admin Reservasi</span>
        </div>
        <div class="mt-4">
            <a href="dashboard_admin.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="kelola_reserv.php" class="nav-link"><i class="fas fa-calendar-check"></i> Reservasi</a>
            <a href="kelola_surat.php" class="nav-link"><i class="fas fa-file-invoice"></i> Kelola Surat</a>
            <a href="kelola_tempat.php" class="nav-link active"><i class="fas fa-map-marked-alt"></i> Kelola Tempat</a>
            <a href="calendar.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Kalender</a>
            <hr class="mx-3" style="opacity:0.1;">
            <a href="logout_admin.php" class="nav-link text-danger" onclick="confirmAdminLogout(event)"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

    <!-- MAIN INTERFACE CONTENT -->
    <main class="main-content animate__animated animate__fadeIn">
        <header class="page-header">
            <div class="header-title">
                <h1>Daftar Tempat Wisata</h1>
                <p class="text-muted small mb-0">Manajemen konten klasterisasi objek sarana pariwisata daerah Kota Tangerang</p>
            </div>
            <a href="tambah_tempat.php" class="btn-add-modern">
                <i class="fa-solid fa-plus"></i> Registrasi Objek Baru
            </a>
        </header>

        <div class="place-grid">
            <?php if (count($places)): foreach ($places as $p):
                    // Deteksi Otomatis Kategori Objek sesuai klaster Bab 1 Penelitian
                    $kategoriBadge = "Destinasi Wisata";
                    if (stripos($p['name'], 'Taman') !== false) $kategoriBadge = "Taman Kota";
                    if (stripos($p['name'], 'Gedung') !== false) $kategoriBadge = "Gedung Seni";
                    if (stripos($p['name'], 'Museum') !== false) $kategoriBadge = "Museum Cagar";
            ?>
                    <div class="place-card">
                        <div class="card-img-wrapper">
                            <div class="card-top-badge"><?= $kategoriBadge; ?></div>
                            <img src="../<?= h($p['image']) ?>" alt="<?= h($p['name']) ?>">
                        </div>

                        <div class="card-body-content">
                            <div class="place-name text-truncate"><?= h($p['name']) ?></div>
                            <div class="place-loc">
                                <i class="fa-solid fa-location-dot text-danger"></i>
                                <span class="text-truncate">Objek Kota Tangerang</span>
                            </div>
                            <div class="place-desc"><?= h($p['description']) ?></div>
                        </div>

                        <!-- BAR AKSI DI-REPOSISI DI BAWAH KARTU SECARA PROPORSIONAL -->
                        <div class="card-action-bar">
                            <a href="detail_tempat.php?id=<?= $p['id'] ?>" class="control-btn btn-view-md" title="View"><i class="fa-solid fa-expand"></i> Detail</a>
                            <a href="edit_tempat.php?id=<?= $p['id'] ?>" class="control-btn btn-edit-md" title="Edit"><i class="fa-solid fa-pen-to-square"></i> Edit</a>
                            <form method="POST" id="del_<?= $p['id'] ?>" class="d-inline flex-grow-1 m-0 p-0">
                                <input type="hidden" name="id" value="<?= $p['id'] ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="button" class="control-btn btn-delete-md w-100" onclick="confirmDelete(<?= $p['id'] ?>)"><i class="fa-solid fa-trash-can"></i> Hapus</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach;
            else: ?>
                <div class="text-center py-5 w-100">
                    <img src="../assets/img/empty.svg" width="180" alt="Empty">
                    <p class="mt-3 text-muted fw-bold">Belum ada data tempat terdaftar.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- PAGINATION SECTION -->
        <nav class="pagination-container">
            <ul class="pagination mb-0">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i == $current_page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Tempat?',
                text: "Data beserta berkas gambar terlampir akan dihapus permanen dari sistem database!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus Sekarang!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('del_' + id).submit();
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