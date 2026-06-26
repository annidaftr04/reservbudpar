<?php
include '../db.php';
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

// Data Admin dari Sesi
$adminName = isset($_SESSION['admin_data']['nama']) ? $_SESSION['admin_data']['nama'] : 'Administrator';

// --- 1. Statistik Dasar ---
$totalReservasi = $conn->query("SELECT COUNT(*) as total FROM reservations")->fetch_assoc()['total'];
$totalPending   = $conn->query("SELECT COUNT(*) as total FROM reservations WHERE status = 'pending'")->fetch_assoc()['total'];
$totalTempat    = $conn->query("SELECT COUNT(*) as total FROM places")->fetch_assoc()['total'];
$totalUser = $conn->query(
    "SELECT COUNT(*) as total FROM users"
)->fetch_assoc()['total'];

// --- 2. Data untuk Doughnut Chart (Status) ---
$statusData = ['pending' => 0, 'disetujui' => 0, 'ditolak' => 0, 'selesai' => 0];
$resStatus = $conn->query("SELECT status, COUNT(*) as count FROM reservations GROUP BY status");
while ($row = $resStatus->fetch_assoc()) {
    $statusData[$row['status']] = (int)$row['count'];
}

// --- 3. Data untuk Bar Chart (Tempat Terpopuler) ---
// Kita joinkan dengan tabel places untuk mendapatkan nama tempatnya
$popularPlaces = [];
$placeLabels = [];
$placeCounts = [];
$resPopular = $conn->query("
    SELECT p.name, COUNT(r.id) as total 
    FROM places p 
    LEFT JOIN reservations r ON p.id = r.place_id 
    GROUP BY p.id 
    ORDER BY total DESC LIMIT 5
");
while ($row = $resPopular->fetch_assoc()) {
    $placeLabels[] = $row['name'];
    $placeCounts[] = (int)$row['total'];
}
// --- 4. USER PALING AKTIF ---
$userLabels = [];
$userCounts = [];
$resUsers = $conn->query("
    SELECT
        user_id,
        nama,
        COUNT(id) as total
    FROM reservations
    GROUP BY user_id
    ORDER BY total DESC
    LIMIT 5
");
while ($row = $resUsers->fetch_assoc()) {
    $userLabels[] =
        $row['nama'];
    $userCounts[] =
        (int)$row['total'];
}
// --- 4. Ambil 5 Reservasi Terbaru ---
$recentReservations = $conn->query("
    SELECT r.*, p.name as tempat_name 
    FROM reservations r 
    LEFT JOIN places p ON r.place_id = p.id 
    ORDER BY r.id DESC LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Modern Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon">

    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #ff9f1c;
            --dark: #212529;
            --light-bg: #f8f9fc;
            --sidebar-color: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: #4a4a4a;
        }

        /* Sidebar Styling */
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

        /* Main Content */
        .main-content {
            margin-left: 260px;
            padding: 2rem;
            transition: all 0.3s;
        }

        /* Stats Cards */
        .stat-card {
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            background: #fff;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
            transition: transform 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }

        .bg-light-primary {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
        }

        .bg-light-success {
            background: rgba(76, 201, 240, 0.1);
            color: #08a1d4;
        }

        .bg-light-warning {
            background: rgba(255, 159, 28, 0.1);
            color: var(--warning);
        }

        .bg-light-danger {
            background: rgba(247, 37, 133, 0.1);
            color: var(--danger);
        }

        .table-responsive {
            background: #fff;
            border-radius: 15px;
            padding: 1rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.05);
        }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        @media (max-width: 992px) {
            .sidebar {
                margin-left: -260px;
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar.active {
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
            <a href="#" class="nav-link active"><i class="fas fa-th-large"></i> Dashboard</a>
            <a href="kelola_reserv.php" class="nav-link"><i class="fas fa-calendar-check"></i> Reservasi</a>
            <a href="kelola_surat.php" class="nav-link"><i class="fas fa-map-marked-alt"></i> Kelola Surat</a>
            <a href="kelola_tempat.php" class="nav-link"><i class="fas fa-map-marked-alt"></i> Kelola Tempat</a>
            <a href="calendar.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Kalender</a>
            <div style="margin: 2rem 0;">
                <hr style="opacity: 0.1;">
            </div>
            <a href="logout_admin.php" class="nav-link text-danger" onclick="confirmAdminLogout(event)"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </div>
    </nav>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0">Overview Dashboard</h4>
                <p class="text-muted">Halo, <?= $adminName; ?>. Berikut adalah ringkasan hari ini.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4" onclick="window.print()">
                <i class="fas fa-file-download me-2"></i> Download Laporan
            </button>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-light-primary"><i class="fas fa-book"></i></div>
                    <div class="text-muted small fw-bold">Total Reservasi</div>
                    <div class="h3 fw-800 mb-0"><?= $totalReservasi; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-light-warning"><i class="fas fa-clock"></i></div>
                    <div class="text-muted small fw-bold">Butuh Persetujuan</div>
                    <div class="h3 fw-800 mb-0"><?= $totalPending; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-light-success"><i class="fas fa-map-pin"></i></div>
                    <div class="text-muted small fw-bold">Total Objek Wisata</div>
                    <div class="h3 fw-800 mb-0"><?= $totalTempat; ?></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="stat-icon bg-light-danger"><i class="fas fa-users"></i></div>
                    <div class="text-muted small fw-bold">User Terdaftar</div>
                    <div class="h3 fw-800 mb-0"><?= $totalUser; ?></div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold">Status Reservasi</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-transparent border-0 pt-4 px-4">
                        <h6 class="fw-bold">5 Tempat Paling Sering Dipesan</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="popularChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4 mb-5">
            <div class="col-12">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="card-header bg-white border-0 px-4 pt-4 pb-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="fw-bold mb-1">
                                    User Paling Aktif
                                </h5>
                                <small class="text-muted">
                                    Berdasarkan jumlah reservasi terbanyak
                                </small>
                            </div>
                            <div class="bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold small">
                                Top User
                            </div>
                        </div>
                    </div>
                    <div class="card-body pt-3 pb-4 px-4">
                        <canvas id="userChart" height="55"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-transparent border-0 pt-4 px-4 d-flex justify-content-between">
                <h6 class="fw-bold">Reservasi Terbaru</h6>
                <a href="kelola_reserv.php" class="btn btn-sm btn-light">Lihat Semua</a>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="table-responsive border-0 p-0">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th>Nama Pemohon</th>
                                <th>Tempat</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $recentReservations->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold text-primary"><?= $row['kode_booking']; ?></td>
                                    <td><?= $row['nama']; ?></td>
                                    <td><?= $row['tempat_name'] ?? 'N/A'; ?></td>
                                    <td><?= date('d M Y', strtotime($row['hari'])); ?></td>
                                    <td>
                                        <?php
                                        $badge = 'bg-secondary';
                                        if ($row['status'] == 'disetujui') $badge = 'bg-success';
                                        if ($row['status'] == 'pending') $badge = 'bg-warning text-dark';
                                        if ($row['status'] == 'ditolak') $badge = 'bg-danger';
                                        ?>
                                        <span class="badge status-badge <?= $badge; ?>"><?= $row['status']; ?></span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Chart Status (Doughnut)
        const ctxStatus = document.getElementById('statusChart').getContext('2d');
        new Chart(ctxStatus, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Disetujui', 'Ditolak', 'Selesai'],
                datasets: [{
                    data: [
                        <?= $statusData['pending']; ?>,
                        <?= $statusData['disetujui']; ?>,
                        <?= $statusData['ditolak']; ?>,
                        <?= $statusData['selesai']; ?>
                    ],
                    backgroundColor: ['#ff9f1c', '#4cc9f0', '#f72585', '#4361ee'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: {
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                cutout: '70%'
            }
        });

        // Chart Terpopuler (Bar)
        const ctxPopular = document.getElementById('popularChart').getContext('2d');
        new Chart(ctxPopular, {
            type: 'bar',
            data: {
                labels: <?= json_encode($placeLabels); ?>,
                datasets: [{
                    label: 'Jumlah Reservasi',
                    data: <?= json_encode($placeCounts); ?>,
                    backgroundColor: '#4361ee',
                    borderRadius: 8,
                    barThickness: 30
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
        // ==========================================
        // USER PALING AKTIF
        // ==========================================

        const ctxUser =
            document
            .getElementById('userChart')
            .getContext('2d');
        new Chart(ctxUser, {
            type: 'bar',
            data: {
                labels: <?= json_encode($userLabels); ?>,
                datasets: [{
                    label: 'Jumlah Reservasi',
                    data: <?= json_encode($userCounts); ?>,
                    backgroundColor: '#10b981',
                    borderRadius: 10,
                    barThickness: 35
                }]
            },
            options: {
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
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