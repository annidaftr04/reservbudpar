<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

include '../db.php';

// Inisialisasi variabel pencarian
$kode_booking_search = $_GET['kode_booking'] ?? '';
$nama_search = $_GET['nama'] ?? '';
$hari_search = $_GET['hari'] ?? '';
$place_id_search = $_GET['place_id'] ?? '';
$status_search = $_GET['status'] ?? '';
$sumber_search = $_GET['sumber_reservasi'] ?? '';

// Pagination setup
$items_per_page = 10;
$current_page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($current_page - 1) * $items_per_page;

// Ambil daftar tempat untuk filter dropdown
$places_list = $conn->query("SELECT id, name FROM places ORDER BY name ASC");

// Query utama dengan JOIN ke tabel places dan sub_places
$query = "SELECT r.*, p.name as nama_tempat, sp.nama_subtempat 
            FROM reservations r
            LEFT JOIN places p ON r.place_id = p.id
            LEFT JOIN sub_places sp ON r.sub_place_id = sp.id
            WHERE (r.kode_booking LIKE ? OR ? = '') 
            AND (r.nama LIKE ? OR ? = '') 
            AND (r.hari LIKE ? OR ? = '') 
            AND (r.place_id = ? OR ? = '')
            AND (r.status LIKE ? OR ? = '')
            AND (r.sumber_reservasi LIKE ? OR ? = '')
            ORDER BY r.id DESC
            LIMIT ? OFFSET ?";

$stmt = $conn->prepare($query);
$search_kode = "%$kode_booking_search%";
$search_nama = "%$nama_search%";
$search_hari = "%$hari_search%";
$search_status = "%$status_search%";
$search_sumber = "%$sumber_search%";

$stmt->bind_param(
    "ssssssssssssii",
    $search_kode,
    $kode_booking_search,
    $search_nama,
    $nama_search,
    $search_hari,
    $hari_search,
    $place_id_search,
    $place_id_search,
    $search_status,
    $status_search,
    $search_sumber,
    $sumber_search,
    $items_per_page,
    $offset
);
$stmt->execute();
$result = $stmt->get_result();

// Hitung total data untuk pagination
$count_query = "SELECT COUNT(*) AS total FROM reservations 
                WHERE (kode_booking LIKE ? OR ? = '') 
                AND (nama LIKE ? OR ? = '') 
                AND (hari LIKE ? OR ? = '') 
                AND (place_id = ? OR ? = '')
                AND (status LIKE ? OR ? = '')
                AND (sumber_reservasi LIKE ? OR ? = '')";

$count_stmt = $conn->prepare($count_query);
$count_stmt->bind_param(
    "ssssssssssss",
    $search_kode,
    $kode_booking_search,
    $search_nama,
    $nama_search,
    $search_hari,
    $hari_search,
    $place_id_search,
    $place_id_search,
    $search_status,
    $status_search,
    $search_sumber,
    $sumber_search
);
$count_stmt->execute();
$total_data = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_data / $items_per_page);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kelola Reservasi | Admin Panel</title>

    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon">

    <style>
        :root {
            --primary: #4361ee;
            --navy: #1e293b;
            --light-bg: #f8f9fc;
            --sidebar-color: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: #334155;
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            height: 100vh;
            background: var(--sidebar-color);
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
            color: var(--text-muted);
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

        .main-content {
            margin-left: 260px;
            padding: 2rem;
        }

        .filter-card {
            background: #fff;
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            padding: 0.6rem 1rem;
        }

        .table-card {
            background: #fff;
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table thead {
            background: #f1f5f9;
        }

        .table thead th {
            padding: 1.2rem;
            font-weight: 700;
            color: #475569;
            border: none;
        }

        .table tbody td {
            padding: 1.2rem;
            border-bottom: 1px solid #f1f5f9;
        }

        .status-badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
        }

        .status-pending {
            background: #fef3c7;
            color: #d97706;
        }

        .status-disetujui {
            background: #dcfce7;
            color: #16a34a;
        }

        .status-ditolak {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-selesai {
            background: #e0e7ff;
            color: #4338ca;
        }

        .btn-action {
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            transition: 0.2s;
        }

        @media (max-width: 992px) {
            .sidebar {
                margin-left: -260px;
            }

            .main-content {
                margin-left: 0;
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
            <a href="kelola_reserv.php" class="nav-link active"><i class="fas fa-calendar-check"></i> Reservasi</a>
            <a href="kelola_tempat.php" class="nav-link"><i class="fas fa-map-marked-alt"></i> Kelola Tempat</a>
            <a href="calendar.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Kalender</a>
            <hr class="mx-3">
            <a href="logout_admin.php" class="nav-link text-danger" onclick="confirmAdminLogout(event)"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <h4 class="fw-bold mb-4">Kelola Data Reservasi</h4>

        <div class="card filter-card">
            <div class="card-body p-4">
                <div class="d-flex justify-content-end mb-3 gap-2">
                    <a href="tambah_reservasi_admin.php"
                        class="btn btn-primary rounded-3 px-4">
                        <i class="fas fa-plus me-2"></i>
                        Tambah Reservasi
                    </a>
                    <!-- EXCEL -->
                    <a
                        href="export_reservasi.php?kode_booking=<?= urlencode($kode_booking_search) ?>&nama=<?= urlencode($nama_search) ?>&place_id=<?= urlencode($place_id_search) ?>&status=<?= urlencode($status_search) ?>&sumber_reservasi=<?= urlencode($sumber_search) ?>"
                        class="btn btn-success rounded-3 px-4">
                        <i class="fas fa-file-excel me-2"></i>
                        Download Excel
                    </a>
                    <!-- PDF -->
                    <a
                        href="export_reservasi_pdf.php?kode_booking=<?= urlencode($kode_booking_search) ?>&nama=<?= urlencode($nama_search) ?>&place_id=<?= urlencode($place_id_search) ?>&status=<?= urlencode($status_search) ?>&sumber_reservasi=<?= urlencode($sumber_search) ?>"
                        class="btn btn-danger rounded-3 px-4"
                        target="_blank">
                        <i class="fas fa-file-pdf me-2"></i>
                        Download PDF
                    </a>
                </div>
                <form method="GET" class="row g-3">
                    <div class="col-md-2">
                        <label class="small fw-bold text-muted">Kode Booking</label>
                        <input type="text" name="kode_booking" class="form-control" value="<?= htmlspecialchars($kode_booking_search) ?>" placeholder="RES-xxx">
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold text-muted">
                            Sumber
                        </label>

                        <select name="sumber_reservasi"
                            class="form-select">

                            <option value="">Semua</option>

                            <option value="online"
                                <?= $sumber_search == 'online' ? 'selected' : '' ?>>
                                Online
                            </option>

                            <option value="offline"
                                <?= $sumber_search == 'offline' ? 'selected' : '' ?>>
                                Offline
                            </option>

                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">Nama Pemohon</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($nama_search) ?>" placeholder="Cari nama...">
                    </div>
                    <div class="col-md-3">
                        <label class="small fw-bold text-muted">Tempat</label>
                        <select name="place_id" class="form-select">
                            <option value="">Semua Lokasi</option>
                            <?php while ($p = $places_list->fetch_assoc()): ?>
                                <option value="<?= $p['id'] ?>" <?= $place_id_search == $p['id'] ? 'selected' : '' ?>><?= $p['name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small fw-bold text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua</option>
                            <option value="pending" <?= $status_search == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="disetujui" <?= $status_search == 'disetujui' ? 'selected' : '' ?>>Disetujui</option>
                            <option value="ditolak" <?= $status_search == 'ditolak' ? 'selected' : '' ?>>Ditolak</option>
                            <option value="selesai" <?= $status_search == 'selesai' ? 'selected' : '' ?>>Selesai</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100 rounded-3">Cari</button>
                        <a href="kelola_reserv.php" class="btn btn-light rounded-3"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card table-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Pemohon</th>
                            <th>Lokasi / Bagian</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                            <th>Sumber</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr id="row-<?= $row['id'] ?>">
                                    <td class="fw-bold text-primary"><?= $row['kode_booking'] ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($row['email'] ?? 'No Email') ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-navy"><?= htmlspecialchars($row['nama_tempat'] ?? 'N/A') ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($row['nama_subtempat'] ?? '-') ?></div>
                                    </td>
                                    <td>
                                        <div class="small fw-bold"><i class="far fa-calendar-alt me-1 text-primary"></i> <?= date('d M Y', strtotime($row['hari'])) ?></div>
                                        <div class="small text-muted"><i class="far fa-clock me-1"></i> <?= substr($row['jam_mulai'], 0, 5) ?></div>
                                    </td>
                                    <td>
                                        <?php if ($row['tanggal_selesai']): ?>
                                            <div class="small fw-bold text-danger"><i class="far fa-calendar-check me-1"></i> <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></div>
                                        <?php else: ?>
                                            <div class="small fw-bold"><i class="far fa-calendar-check me-1"></i> <?= date('d M Y', strtotime($row['hari'])) ?></div>
                                        <?php endif; ?>
                                        <div class="small text-muted"><i class="far fa-clock me-1"></i> <?= substr($row['jam_selesai'], 0, 5) ?></div>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $row['status'] ?>">
                                            <?= strtoupper($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($row['sumber_reservasi'] == 'online'): ?>
                                            <span class="badge bg-primary">
                                                Online
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                Offline
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="view_reserv.php?id=<?= $row['id'] ?>" class="btn-action bg-info bg-opacity-10 text-info" title="Detail"><i class="fas fa-eye"></i></a>
                                            <a href="edit_reserv.php?id=<?= $row['id'] ?>" class="btn-action bg-warning bg-opacity-10 text-warning" title="Edit/Proses"><i class="fas fa-edit"></i></a>
                                            <button class="btn-action bg-danger bg-opacity-10 text-danger border-0 delete-reservation" data-id="<?= $row['id'] ?>" title="Hapus"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Data tidak ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white border-0 p-4 d-flex justify-content-between align-items-center">
                <div class="small text-muted">Total Data: <b><?= $total_data ?></b></div>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $current_page ? 'active' : '' ?>">
                                <a class="page-link px-3 border-0 rounded-3 mx-1 shadow-sm" href="?page=<?= $i ?>&kode_booking=<?= $kode_booking_search ?>&nama=<?= $nama_search ?>&place_id=<?= $place_id_search ?>&status=<?= $status_search ?>">
                                    <?= $i ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).on('click', '.delete-reservation', function() {
            const id = $(this).data("id");
            Swal.fire({
                title: 'Hapus Reservasi?',
                text: "Tindakan ini tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "delete_reservation.php",
                        method: "POST",
                        data: {
                            id: id
                        },
                        success: function(res) {
                            if (res === 'success') {
                                Swal.fire('Dihapus!', 'Data berhasil dihapus.', 'success');
                                $("#row-" + id).fadeOut();
                            } else {
                                Swal.fire('Gagal!', 'Terjadi kesalahan sistem.', 'error');
                            }
                        }
                    });
                }
            });
        });

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