<?php
session_start();
include '../db.php';

// Proteksi halaman login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = intval($_SESSION['user_id']);

/* === Pagination Setup === */
$items_per_page = 5;
$current_page   = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$offset         = ($current_page - 1) * $items_per_page;

/* === Hitung total data === */
$stmtTotal = $conn->prepare("SELECT COUNT(*) AS total FROM reservations WHERE user_id = ?");
$stmtTotal->bind_param('i', $user_id);
$stmtTotal->execute();
$total_data  = $stmtTotal->get_result()->fetch_assoc()['total'] ?? 0;
$total_pages = (int) ceil($total_data / $items_per_page);

/* === Ambil data reservasi === */
$dataQuery = "
    SELECT 
        reservations.*,
        places.name AS place_name,
        sub_places.nama_subtempat AS sub_place_name

    FROM reservations

    LEFT JOIN places 
        ON reservations.place_id = places.id

    LEFT JOIN sub_places 
        ON reservations.sub_place_id = sub_places.id

    WHERE reservations.user_id = ?

    ORDER BY reservations.id DESC

    LIMIT ? OFFSET ?
";
$stmtData  = $conn->prepare($dataQuery);
$stmtData->bind_param('iii', $user_id, $items_per_page, $offset);
$stmtData->execute();
$result = $stmtData->get_result();

/* === Ambil nama pengguna === */
$user_stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$user_stmt->bind_param('i', $user_id);
$user_stmt->execute();
$users = $user_stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard Layanan | Kota Tangerang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon">
    <style>
        :root {
            --primary: #002D62;
            --accent: #FF5733;
            --glass: rgba(255, 255, 255, 0.85);
            --bg-grad: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-grad);
            min-height: 100vh;
            color: #1a202c;
        }

        /* --- Ambient background element --- */
        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, rgba(0, 45, 98, 0.05), transparent),
                radial-gradient(circle at bottom left, rgba(255, 87, 51, 0.05), transparent);
            z-index: -1;
        }

        /* --- Luxury Wrapper --- */
        .dashboard-wrapper {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            border-radius: 50px;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.1);
            padding: 50px;
            margin-top: 50px;
            margin-bottom: 50px;
        }

        /* --- Header Section --- */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .user-greeting h1 {
            font-weight: 800;
            color: var(--primary);
            letter-spacing: -1px;
            margin-bottom: 5px;
        }

        .btn-new-reserv {
            background: linear-gradient(135deg, var(--primary) 0%, #004d99 100%);
            color: white;
            padding: 14px 28px;
            border-radius: 20px;
            font-weight: 700;
            border: none;
            box-shadow: 0 15px 30px rgba(0, 45, 98, 0.25);
            transition: 0.3s ease;
        }

        .btn-new-reserv:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(0, 45, 98, 0.35);
            color: white;
        }

        /* --- Table Styling (Modern Card) --- */
        .modern-table-container {
            background: white;
            border-radius: 35px;
            padding: 20px;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.03);
            border: 1px solid #f0f0f0;
        }

        .table thead th {
            background: none;
            color: #a0aec0;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 20px;
            border-bottom: 2px solid #f8fafc;
        }

        .table tbody td {
            padding: 20px;
            vertical-align: middle;
            border: none;
        }

        .table tbody tr {
            border-bottom: 1px solid #f8fafc;
            transition: 0.2s;
        }

        .table tbody tr:hover {
            background-color: #f8fbff;
        }

        /* --- Status Badges --- */
        .status-pill {
            padding: 8px 16px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
        }

        .status-disetujui {
            background: #e6fffa;
            color: #319795;
        }

        .status-pending {
            background: #fffaf0;
            color: #dd6b20;
        }

        .status-ditolak {
            background: #fff5f5;
            color: #e53e3e;
        }

        .btn-action {
            background: #f1f5f9;
            color: var(--primary);
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 10px 20px;
            border: none;
            transition: 0.3s;
        }

        .btn-action:hover {
            background: var(--primary);
            color: white;
        }

        /* --- Pagination --- */
        .pagination .page-link {
            border: none;
            background: none;
            color: #a0aec0;
            font-weight: 700;
            padding: 12px 18px;
            margin: 0 5px;
            border-radius: 15px;
        }

        .pagination .page-item.active .page-link {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 20px rgba(0, 45, 98, 0.2);
        }

        /* --- Navbar (Same as Detail Page) --- */
        .nav-luxury {
            background: var(--glass);
            backdrop-filter: blur(10px);
            border-radius: 25px;
            padding: 10px 25px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>

    <div class="container">
        <nav class="navbar navbar-expand-lg nav-luxury">
            <div class="container-fluid">
                <a class="navbar-brand d-flex align-items-center" href="#">
                    <img src="../assets/img/logotng.png" height="40" alt="Logo">
                    <span class="ms-2 fw-bold text-primary">Disbudpar Tangerang</span>
                </a>
                <div class="ms-auto d-flex align-items-center">
                    <span class="me-3 fw-bold small text-muted"><i class="far fa-user-circle me-1"></i> <?= htmlspecialchars($users['username']); ?></span>
                    <a href="logout.php" class="text-danger fw-bold small text-decoration-none"><i class="fas fa-sign-out-alt"></i> Keluar</a>
                </div>
            </div>
        </nav>

        <div class="dashboard-wrapper">
            <div class="page-header">
                <div class="user-greeting">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <div style="width: 30px; height: 3px; background: var(--accent); border-radius: 5px;"></div>
                        <span class="fw-bold small text-uppercase text-muted" style="letter-spacing: 1px;">Manajemen Layanan</span>
                    </div>
                    <h1>Halo, <?= explode(' ', htmlspecialchars($users['username']))[0]; ?>!</h1>
                    <p class="text-muted">Pantau status reservasi tempat Anda secara real-time.</p>
                </div>

                <div class="d-flex gap-3">
                    <a href="index.php" class="btn btn-outline-primary shadow-sm" style="border-radius: 20px; font-weight: 700; padding: 14px 28px;">
                        <i class="fas fa-arrow-left me-2"></i> Ke Beranda
                    </a>
                    <a href="index.php#booking-form" class="btn-new-reserv">
                        <i class="fas fa-plus-circle me-2"></i> Reservasi Baru
                    </a>
                </div>
            </div>

            <div class="modern-table-container">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>ID Booking</th>
                                <th>Informasi Acara</th>
                                <th>Jadwal Pelaksanaan</th>
                                <th>Status</th>
                                <th class="text-center">Opsi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): ?>
                                <?php while ($row = $result->fetch_assoc()):
                                    $statusClass = 'status-' . strtolower($row['status']);
                                ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary"><?= htmlspecialchars($row['kode_booking']); ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold">

                                                <?= htmlspecialchars($row['place_name']); ?>

                                                <?php if (!empty($row['sub_place_name'])): ?>

                                                    (<?= htmlspecialchars($row['sub_place_name']); ?>)

                                                <?php endif; ?>

                                            </div>
                                            <div class="small text-muted text-truncate" style="max-width: 250px;">
                                                <?= htmlspecialchars($row['keterangan']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-bold"><i class="far fa-calendar-check me-1 text-accent"></i> <?= date('d M Y', strtotime($row['hari'])); ?></div>
                                            <div class="small text-muted"><?= $row['jam_mulai']; ?> - <?= $row['jam_selesai']; ?> WIB</div>
                                        </td>
                                        <td>
                                            <span class="status-pill <?= $statusClass ?>"><?= ucfirst(htmlspecialchars($row['status'])); ?></span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-2 align-items-center">
                                                <!-- DETAIL -->
                                                <a
                                                    href="v_reserv.php?id=<?= urlencode($row['id']); ?>"
                                                    class="btn-action shadow-sm">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Detail
                                                </a>
                                                <?php
                                                // ==========================================
                                                // CEK H+1 KUESIONER
                                                // ==========================================
                                                $tanggalSelesai =
                                                    !empty($row['tanggal_selesai'])
                                                    ? $row['tanggal_selesai']
                                                    : $row['hari'];
                                                $tanggalKuesioner =
                                                    date(
                                                        'Y-m-d',
                                                        strtotime($tanggalSelesai . ' +1 day')
                                                    );
                                                $hariIni =
                                                    date('Y-m-d');

                                                // ==========================================
                                                // LINK KUESIONER
                                                // ==========================================
                                                $linkKuesioner =
                                                    "https://ekinerjabudpar.org/kuesionertangerang/";
                                                ?>
                                                <?php if (
                                                    $row['status'] === 'disetujui'
                                                    &&
                                                    $hariIni >= $tanggalKuesioner
                                                ): ?>
                                                    <a
                                                        href="<?= $linkKuesioner ?>"
                                                        target="_blank"
                                                        class="btn btn-sm btn-success rounded-pill px-3 fw-bold"
                                                        style="
                                                            font-size:12px;
                                                    ">
                                                        <i class="fas fa-clipboard-list me-1"></i>
                                                        Isi Kuesioner
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <img src="https://illustrations.popsy.co/blue/waiting.svg" style="height: 180px;" class="mb-3 opacity-75">
                                        <h5 class="fw-bold text-muted">Belum ada riwayat reservasi</h5>
                                        <p class="text-muted small">Mulai ajukan reservasi pertama Anda hari ini.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php if ($total_pages > 1): ?>
                <nav class="mt-5">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?= $current_page <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $current_page - 1; ?>"><i class="fas fa-chevron-left"></i></a>
                        </li>
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?= $i; ?>"><?= $i; ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $current_page >= $total_pages ? 'disabled' : ''; ?>">
                            <a class="page-link" href="?page=<?= $current_page + 1; ?>"><i class="fas fa-chevron-right"></i></a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>