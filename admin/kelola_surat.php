<?php
include '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

// Ambil data reservasi
$query = mysqli_query($conn, "
SELECT
    r.*,
    p.name AS nama_tempat
FROM reservations r
LEFT JOIN places p
ON r.place_id = p.id
ORDER BY r.id DESC
");
$totalDokumen = mysqli_num_rows($query);

// Hitung Statistik
$totalHariIni = 0;
$totalDisetujui = 0;
$today = date('Y-m-d');

// Tampung data untuk dilooping di table biar query tidak jebol pas di-loop ganda
$reservations = [];
if ($query->num_rows > 0) {
    while ($row = $query->fetch_assoc()) {
        $reservations[] = $row;
        if ($row['status'] == 'disetujui') {
            $totalDisetujui++;
        }
        if ($row['hari'] == $today) {
            $totalHariIni++;
        }
    }
}

// Ambil data total tempat aktif
$totalTempatAktif = $conn->query("SELECT COUNT(DISTINCT place_id) AS total FROM reservations WHERE status='disetujui'")->fetch_assoc()['total'];

function h($string)
{
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}
$totalTolak = mysqli_num_rows(mysqli_query(
    $conn,
    "SELECT id FROM reservations WHERE status='ditolak'"
));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manajemen Arsip Dokumen Surat | TNG Admin</title>
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

            --gradient-1: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            --gradient-2: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --gradient-3: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            --gradient-4: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: var(--dark);
            margin: 0;
            display: flex;
        }
        /* Sidebar Base Frame */
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
        /* Main Workspace Wrapper */
        .main-content {
            margin-left: 280px;
            flex: 1;
            padding: 3rem;
            min-height: 100vh;
        }
        .glass-header {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(15px);
            padding: 2rem;
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            margin-bottom: 2.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.02);
        }
        /* Statistik Berwarna */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            border-radius: 22px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            border: none;
            color: white;
            transition: 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.08);
        }
        .stat-card h3 {
            font-size: 36px;
            font-weight: 800;
            margin: 0;
            color: white;
            letter-spacing: -1px;
        }
        .stat-card p {
            margin: 5px 0 0;
            color: rgba(255, 255, 255, 0.85);
            font-size: 14px;
            font-weight: 600;
        }
        .stat-card i {
            position: absolute;
            right: 20px;
            bottom: 20px;
            font-size: 32px;
            color: rgba(255, 255, 255, 0.22);
        }
        /* Filter Controls */
        .filter-card {
            background: white;
            border-radius: 24px;
            padding: 20px 24px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
            margin-bottom: 2rem;
        }
        .form-control,
        .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            font-weight: 500;
            transition: 0.3s;
        }
        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.08);
            outline: none;
        }
        .btn-search {
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 12px;
            font-weight: 700;
            transition: 0.2s;
        }
        .btn-search:hover {
            background: var(--secondary);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 97, 238, 0.2);
        }
        /* Data Workspace Table */
        .table-wrapper-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.02);
        }
        .table {
            margin-bottom: 0;
            vertical-align: middle;
        }
        .table th {
            background: #f8fafc !important;
            color: var(--muted-gray);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.78rem;
            letter-spacing: 0.5px;
            padding: 16px;
            border-bottom: 2px solid #edf2f7;
        }
        .table td {
            padding: 16px;
            border-bottom: 1px solid #edf2f7;
            font-weight: 500;
            font-size: 0.92rem;
        }
        /* Badges Style */
        .badge-status {
            padding: 6px 14px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.78rem;
            display: inline-block;
            text-transform: uppercase;
        }
        .bg-disetujui {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.15);
        }
        .bg-ditolak {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border: 1px solid rgba(239, 68, 68, 0.15);
        }
        .bg-pending {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
            border: 1px solid rgba(245, 158, 11, 0.15);
        }
        .bg-selesai {
            background: rgba(6, 182, 212, 0.1);
            color: #06b6d4;
            border: 1px solid rgba(6, 182, 212, 0.15);
        }
        /* Indicator Check/Cross Icons */
        .doc-indicator {
            font-size: 1.15rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .doc-check {
            color: #10b981;
        }
        .doc-cross {
            color: #cbd5e1;
        }
        .btn-detail-premium {
            background: rgba(67, 97, 238, 0.08);
            color: var(--primary);
            font-weight: 700;
            font-size: 0.82rem;
            border-radius: 10px;
            padding: 8px 16px;
            border: none;
            transition: 0.2s;
        }
        .btn-detail-premium:hover {
            background: var(--primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.15);
        }
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }
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
            <a href="kelola_reserv.php" class="nav-link"><i class="fas fa-calendar-check"></i> Reservasi</a>
            <a href="kelola_surat.php" class="nav-link active"><i class="fas fa-file-invoice"></i> Kelola Surat</a>
            <a href="kelola_tempat.php" class="nav-link"><i class="fas fa-map-marked-alt"></i> Kelola Tempat</a>
            <a href="calendar.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Kalender</a>
            <hr class="mx-3" style="opacity:0.1;">
            <a href="logout_admin.php" class="nav-link text-danger" onclick="confirmAdminLogout(event)"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </nav>

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
            <div class="row g-3">
                <div class="col-md-5">
                    <input type="text" class="form-control" placeholder="Cari Berdasarkan Nama Pemohon...">
                </div>
                <div class="col-md-4">
                    <select class="form-select">
                        <option value="">Semua Klasifikasi Status</option>
                        <option value="pending">Pending (Menunggu)</option>
                        <option value="disetujui">Disetujui</option>
                        <option value="ditolak">Ditolak</option>
                        <option value="selesai">Selesai Acara</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button class="btn-search w-100"><i class="fas fa-magnifying-glass me-2"></i> Jalankan Cari</button>
                </div>
            </div>
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
                                $statusClass = 'bg-pending';
                                $statusLabel = 'Pending';
                                if ($row['status'] == 'disetujui') {
                                    $statusClass = 'bg-disetujui';
                                    $statusLabel = 'Disetujui';
                                } elseif ($row['status'] == 'ditolak') {
                                    $statusClass = 'bg-ditolak';
                                    $statusLabel = 'Ditolak';
                                } elseif ($row['status'] == 'selesai') {
                                    $statusClass = 'bg-selesai';
                                    $statusLabel = 'Selesai';
                                }
                        ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?= $no++; ?></td>
                                    <td class="fw-bold text-primary"><?= $row['kode_booking']; ?></td>
                                    <td><?= htmlspecialchars($row['nama']); ?></td>
                                    <td>
                                        <span class="badge-status <?= $statusClass; ?>">
                                            <?= $statusLabel; ?>
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
    <script>
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