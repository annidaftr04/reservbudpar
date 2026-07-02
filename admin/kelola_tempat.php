<?php
include 'includes/auth.php';
include '../db.php';
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

    <title>Manajemen Konten Obyek Wisata | TNG Admin</title>

    <?php include 'includes/header.php'; ?>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/kelola_tempat.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
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
    <script src="../assets/js/admin.js"></script>
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
    </script>
</body>

</html>