<?php
include 'includes/auth.php';
include '../db.php';
include 'includes/auto_status.php';
// Ambil data reservasi
// ================= FILTER =================
$keyword = $_GET['keyword'] ?? '';
$status  = $_GET['status'] ?? '';

$sql = "
SELECT
    r.*,
    p.name AS nama_tempat
FROM reservations r
LEFT JOIN places p
ON r.place_id = p.id
WHERE 1
";
if (!empty($keyword)) {
    $keyword = mysqli_real_escape_string($conn, $keyword);
    $sql .= " AND r.nama LIKE '%$keyword%' ";
}

if (!empty($status)) {
    $status = mysqli_real_escape_string($conn, $status);
    $sql .= " AND r.status='$status' ";
}
$sql .= " ORDER BY r.id DESC";
$query = mysqli_query($conn, $sql);
$totalDokumen = mysqli_num_rows($query);


$totalDisetujui = 0;
$totalTolak = 0;

$reservations = [];

if ($query->num_rows > 0) {

    while ($row = $query->fetch_assoc()) {

        $reservations[] = $row;

        switch ($row['status']) {

            case 'disetujui':
                $totalDisetujui++;
                break;

            case 'ditolak':
                $totalTolak++;
                break;
        }
    }
}

// Ambil data total tempat aktif
$totalTempatAktif = $conn->query("SELECT COUNT(DISTINCT place_id) AS total FROM reservations WHERE status='disetujui'")->fetch_assoc()['total'];

function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <title>Manajemen Arsip Dokumen Surat | TNG Admin</title>

    <?php include 'includes/header.php'; ?>

    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">

    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/kelola_surat.css">

</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
    <main class="main-content animate__animated animate__fadeIn">
        <header class="glass-header">
            <div class="header-title">
                <h1 class="m-0 fw-800 h3 text-dark" style="letter-spacing:-1px;">Kelola Berkas Surat</h1>
                <p class="text-muted small mb-0 mt-1">Audit berkas persyaratan izin pemanfaatan tempat pemohon secara berkala</p>
            </div>
        </header>

        <div class="stats-grid">
            <div class="stat-card" style="background: var(--gradient-1);">
                <h3><?= $totalDokumen ?></h3>
                <p>Total Dokumen Masuk</p>
                <i class="fa-solid fa-folder-tree"></i>
            </div>
            <div class="stat-card" style="background: var(--gradient-2);">
                <h3><?= $totalDisetujui ?></h3>
                <p>Berkas Disetujui</p>
                <i class="fa-solid fa-file-circle-check"></i>
            </div>
            <div class="stat-card" style="background: var(--gradient-3);">
                <h3><?= $totalTolak ?></h3>
                <p>Berkas Ditolak</p>
                <i class="fa-solid fa-file-circle-xmark"></i>
            </div>
            <div class="stat-card" style="background: var(--gradient-4);">
                <h3><?= $totalTempatAktif ?></h3>
                <p>Tempat Aktif Terpakai</p>
                <i class="fa-solid fa-building-circle-check"></i>
            </div>
        </div>

        <div class="filter-card">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-5">
                        <input
                            type="text"
                            name="keyword"
                            class="form-control"
                            placeholder="Cari Berdasarkan Nama Pemohon..."
                            value="<?= htmlspecialchars($keyword); ?>">
                    </div>
                    <div class="col-md-4">
                        <select
                            name="status"
                            class="form-select">
                            <option value=""
                                <?= $status == '' ? 'selected' : ''; ?>>
                                Semua Klasifikasi Status
                            </option>

                            <option value="disetujui"
                                <?= $status == 'disetujui' ? 'selected' : ''; ?>>
                                Disetujui
                            </option>

                            <option value="ditolak"
                                <?= $status == 'ditolak' ? 'selected' : ''; ?>>
                                Ditolak
                            </option>

                            <option value="pending"
                                <?= $status == 'pending' ? 'selected' : ''; ?>>
                                Pending
                            </option>

                            <option value="selesai"
                                <?= $status == 'selesai' ? 'selected' : ''; ?>>
                                Selesai
                            </option>

                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button
                                type="submit"
                                class="btn-search flex-fill">
                                <i class="fas fa-search me-2"></i>
                                Cari
                            </button>

                            <a
                                href="kelola_surat.php"
                                class="btn btn-secondary">
                                <i class="fas fa-rotate-left"></i>
                            </a>

                        </div>

                    </div>
                </div>
            </form>
        </div>

        <div class="table-wrapper-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold m-0 text-secondary"><i class="fa-solid fa-table-list me-1"></i> Data Arsip Dokumen Masuk</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th width="60">No</th>
                            <th>Kode Booking</th>
                            <th>Nama Pemohon</th>
                            <th>Status Izin</th>
                            <th class="text-center">S. Permohonan</th>
                            <th class="text-center">S. Kelurahan</th>
                            <th class="text-center">KTP</th>
                            <th width="100" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($reservations)):
                            $no = 1;
                            foreach ($reservations as $row) :
                                // Binding status class badge secara dinamis
                                $status = strtolower($row['status']);

                                $statusClass = [
                                    'pending'    => 'bg-pending',
                                    'disetujui'  => 'bg-disetujui',
                                    'ditolak'    => 'bg-ditolak',
                                    'selesai'    => 'bg-selesai'
                                ];

                                $statusLabel = [
                                    'pending'    => 'Pending',
                                    'disetujui'  => 'Disetujui',
                                    'ditolak'    => 'Ditolak',
                                    'selesai'    => 'Selesai'
                                ];
                        ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?= $no++; ?></td>
                                    <td class="fw-bold text-primary"><?= $row['kode_booking']; ?></td>
                                    <td><?= htmlspecialchars($row['nama']); ?></td>
                                    <td>
                                        <span class="badge-status <?= $statusClass[$status] ?? 'bg-secondary' ?>">
                                            <?= $statusLabel[$status] ?? ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($row['file_upload'])) : ?>
                                            <div class="mb-2">
                                                <a href="../uploads/<?= $row['file_upload']; ?>"
                                                    target="_blank"
                                                    class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="../uploads/<?= $row['file_upload']; ?>"
                                                    download
                                                    class="btn btn-success btn-sm">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                            <?php if (!empty($row['nomor_surat_permohonan'])) : ?>
                                                <small class="fw-bold text-primary d-block">
                                                    <?= htmlspecialchars($row['nomor_surat_permohonan']); ?>
                                                </small>
                                            <?php else : ?>
                                                <small class="text-danger d-block">
                                                    Belum Diisi
                                                </small>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($row['surat_kelurahan_upload'])) : ?>
                                            <div class="mb-2">
                                                <a href="../uploads/kelurahan/<?= $row['surat_kelurahan_upload']; ?>"
                                                    target="_blank"
                                                    class="btn btn-info btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="../uploads/kelurahan/<?= $row['surat_kelurahan_upload']; ?>"
                                                    download
                                                    class="btn btn-success btn-sm">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                            </div>
                                            <?php if (!empty($row['nomor_surat_kelurahan'])) : ?>
                                                <small class="fw-bold text-primary d-block">
                                                    <?= htmlspecialchars($row['nomor_surat_kelurahan']); ?>
                                                </small>
                                            <?php else : ?>
                                                <small class="text-danger d-block">
                                                    Belum Diisi
                                                </small>
                                            <?php endif; ?>
                                        <?php else : ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($row['ktp_upload'])) : ?>
                                            <a href="../uploads/ktp/<?= $row['ktp_upload']; ?>"
                                                target="_blank"
                                                class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="../uploads/ktp/<?= $row['ktp_upload']; ?>"
                                                download
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php else : ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="edit_surat.php?id=<?= $row['id']; ?>"
                                            class="btn btn-warning btn-sm rounded-3">
                                            <i class="fas fa-pen"></i>
                                            Edit
                                        </a>
                                    </td>
                                </tr>
                            <?php
                            endforeach;
                        else:
                            ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-5">
                                    <i class="fa-regular fa-folder-open fa-3x mb-3 text-light-gray"></i>
                                    <p class="fw-bold small mb-0">Belum ada dokumen surat yang masuk ke sistem database.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../assets/js/admin.js"></script>
</body>

</html>