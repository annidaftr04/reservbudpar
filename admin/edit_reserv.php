<?php
session_start();
include '../db.php';

// ======================================================
// CEK LOGIN ADMIN
// ======================================================

if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

// ======================================================
// CEK ID RESERVASI
// ======================================================

if (!isset($_GET['id'])) {
    echo "ID reservasi tidak ditemukan.";
    exit;
}

$id = intval($_GET['id']);

// ======================================================
// AMBIL DATA RESERVASI + NAMA TEMPAT
// ======================================================

$query = "
    SELECT reservations.*, places.name AS nama_tempat
    FROM reservations
    LEFT JOIN places ON reservations.place_id = places.id
    WHERE reservations.id = $id
";

$result = $conn->query($query);

if (!$result || $result->num_rows === 0) {
    echo "Reservasi tidak ditemukan.";
    exit;
}

$reservation = $result->fetch_assoc();
// ======================================================
// RIWAYAT RESERVASI USER
// ======================================================

$user_id = $reservation['user_id'];

$queryHistory = $conn->prepare("

    SELECT

        COUNT(*) as total_reservasi,

        SUM(
            CASE
            WHEN status = 'disetujui'
            THEN 1
            ELSE 0
            END
        ) as total_disetujui,
        SUM(
            CASE
            WHEN status = 'ditolak'
            THEN 1
            ELSE 0
            END
        ) as total_ditolak,
        MAX(hari) as terakhir_reservasi
    FROM reservations
    WHERE user_id = ?
");

$queryHistory->bind_param(
    "i",
    $user_id
);

$queryHistory->execute();
$history =
    $queryHistory
    ->get_result()
    ->fetch_assoc();

// ======================================================
// AMBIL DATA TEMPAT
// ======================================================

$queryPlaces = "SELECT id, name FROM places";
$resultPlaces = $conn->query($queryPlaces);
if (!$resultPlaces) {
    echo "Error: " . $conn->error;
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Reservasi | TNG Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --bg: #f8fafc;
            --sidebar-color: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0;
            display: flex;
            overflow-x: hidden;
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
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        }

        .content-card {
            background: white;
            border-radius: 30px;
            padding: 3rem;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(0, 0, 0, 0.01);
        }

        .form-label {
            font-weight: 700;
            color: var(--text-main);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .form-control,
        .form-select {
            border-radius: 14px;
            padding: 14px 18px;
            border: 2.5px solid #f1f5f9;
            font-weight: 500;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 5px rgba(67, 97, 238, 0.08);
            outline: none;
        }

        .booking-tag {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            padding: 5px 15px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.8rem;
        }

        .btn-modern {
            padding: 14px 35px;
            border-radius: 16px;
            font-weight: 700;
            transition: 0.3s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-save-md {
            background: var(--primary);
            color: white;
            border: none;
        }

        .btn-save-md:hover {
            background: #3651d4;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.2);
        }

        .btn-cancel-md {
            background: #e2e8f0;
            color: #64748b;
            text-decoration: none;
        }

        .btn-cancel-md:hover {
            background: #cbd5e1;
            transform: translateY(-3px);
        }

        @media (max-width: 992px) {
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
    <aside class="sidebar">
        <div class="sidebar-brand">
            <img src="../assets/img/logotng.png" width="35" alt="Logo">
            <span>Admin Reservasi</span>
        </div>
        <nav>
            <a href="dashboard_admin.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="kelola_reserv.php" class="nav-link active"><i class="fa-solid fa-calendar-check"></i> Reservasi</a>
            <a href="kelola_surat.php" class="nav-link"><i class="fa-solid fa-map-location-dot"></i> Kelola Surat</a>
            <a href="kelola_tempat.php" class="nav-link"><i class="fa-solid fa-map-location-dot"></i> Kelola Tempat</a>
            <a href="calendar.php" class="nav-link"><i class="fa-solid fa-calendar-days"></i> Kalender</a>
            <div style="margin: 2rem 0;">
                <hr style="opacity: 0.1;">
            </div>
            <a href="logout_admin.php" class="nav-link text-danger" onclick="confirmAdminLogout(event)"><i class="fas fa-sign-out-alt"></i>Logout</a>
        </nav>
    </aside>

    <main class="main-content animate__animated animate__fadeIn">
        <header class="glass-header">
            <div>
                <h1 class="h4 fw-800 mb-1">Edit Rincian Reservasi</h1>
                <span class="booking-tag">ID Booking: <?= htmlspecialchars($reservation['kode_booking']); ?></span>
            </div>
            <a href="kelola_reserv.php" class="btn btn-light rounded-4 px-4 fw-bold border shadow-sm">
                <i class="fa-solid fa-chevron-left me-2"></i> Kembali
            </a>
        </header>
        <div class="content-card">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4">
                        <i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i>
                        Riwayat Reservasi User
                    </h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-primary bg-opacity-10 h-100">
                                <small class="text-muted">
                                    Total Reservasi
                                </small>
                                <h2 class="fw-bold text-primary mt-2">
                                    <?= $history['total_reservasi']; ?>x
                                </h2>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-success bg-opacity-10 h-100">
                                <small class="text-muted">
                                    Disetujui
                                </small>
                                <h2 class="fw-bold text-success mt-2">
                                    <?= $history['total_disetujui']; ?>x
                                </h2>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-danger bg-opacity-10 h-100">
                                <small class="text-muted">
                                    Ditolak
                                </small>
                                <h2 class="fw-bold text-danger mt-2">
                                    <?= $history['total_ditolak']; ?>x
                                </h2>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-4 bg-dark bg-opacity-10 h-100">
                                <small class="text-muted">
                                    Reservasi Terakhir
                                </small>
                                <h6 class="fw-bold mt-3">

                                    <?= $history['terakhir_reservasi']
                                        ? date(
                                            'd M Y',
                                            strtotime(
                                                $history['terakhir_reservasi']
                                            )
                                        )
                                        : '-'; ?>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <form id="editForm">
                <div class="row g-4">
                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Nama Pemohon</label>
                            <input type="text" id="nama" class="form-control" value="<?= htmlspecialchars($reservation['nama']); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">No Telepon / WhatsApp</label>
                            <input type="text" id="no_telepon" class="form-control" value="<?= htmlspecialchars($reservation['no_telepon']); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($reservation['email']); ?>">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Tempat Destinasi</label>
                            <select id="tempat" class="form-select">
                                <?php while ($places = $resultPlaces->fetch_assoc()): ?>
                                    <option value="<?= $places['id']; ?>" <?= $reservation['place_id'] == $places['id'] ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($places['name']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-4">
                            <label class="form-label">Keterangan Acara</label>
                            <textarea id="keterangan" class="form-control" rows="5"><?= htmlspecialchars($reservation['keterangan']); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Catatan Internal Admin</label>
                            <textarea id="catatan" class="form-control" rows="5"><?= htmlspecialchars($reservation['catatan']); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-primary">Status Persetujuan (Approval)</label>
                            <select id="status" class="form-select fw-bold text-uppercase">
                                <option value="pending" <?= $reservation['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="disetujui" <?= $reservation['status'] == 'disetujui' ? 'selected' : ''; ?>>Disetujui</option>
                                <option value="ditolak" <?= $reservation['status'] == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                    <button type="button" class="btn-modern btn-save-md shadow-sm" onclick="confirmSave()">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <a href="kelola_reserv.php" class="btn-modern btn-cancel-md border text-center">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmSave() {
            const newStatus = document.getElementById('status').value;
            const oldStatus = '<?= $reservation['status']; ?>';

            // ==================================================
            // STATUS DISETUJUI
            // ==================================================
            if (newStatus === 'disetujui' && oldStatus !== 'disetujui') {

                Swal.fire({
                    title: 'Setujui Reservasi?',
                    text: 'Status reservasi akan diubah menjadi disetujui.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Setujui',
                    cancelButtonText: 'Batal'
                }).then((result) => {

                    if (result.isConfirmed) {
                        submitStatusUpdate();
                    }

                });

            }
            // ==================================================
            // STATUS DITOLAK
            // ==================================================
            else if (newStatus === 'ditolak' && oldStatus !== 'ditolak') {

                Swal.fire({
                    title: 'Tolak Reservasi?',
                    text: 'Status reservasi akan diubah menjadi ditolak.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Tolak',
                    cancelButtonText: 'Batal'
                }).then((result) => {

                    if (result.isConfirmed) {
                        submitStatusUpdate();
                    }

                });

            }
            // ==================================================
            // SAVE BIASA
            // ==================================================
            else {
                Swal.fire({
                    title: 'Simpan perubahan?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitStatusUpdate();
                    }
                });
            }
        }

        // ======================================================
        // WHATSAPP
        // ======================================================
        function sendWhatsApp(status) {
            const nama = document.getElementById('nama').value;
            const noTelepon = document.getElementById('no_telepon').value.replace(/[^0-9]/g, '');
            const tempat = document.getElementById('tempat');
            const tempatText = tempat.options[tempat.selectedIndex].text;
            const catatan = document.getElementById('catatan').value;
            const kodeBooking = '<?= $reservation['kode_booking']; ?>';
            const tanggal = '<?= $reservation['hari']; ?>';

            let formattedPhone = noTelepon;
            if (formattedPhone.startsWith('0')) {
                formattedPhone = '62' + formattedPhone.substring(1);
            }

            let message = '';

            if (status === 'disetujui') {
                message =
                    `Halo ${nama},
        Kami dengan senang hati menginformasikan bahwa reservasi Anda telah DISETUJUI. 🎉
        Detail reservasi:
        📍 Tempat : ${tempatText}
        📅 Tanggal : ${tanggal}
        🆔 Kode Booking : ${kodeBooking}
        ${catatan ? `📝 Catatan Admin :
        ${catatan}
        ` : ''}Silakan datang sesuai jadwal yang telah ditentukan.
        Terima kasih telah menggunakan layanan reservasi Disbudpar Kota Tangerang 😊`;
            } else {
                message =
                    `Halo ${nama},
        Mohon maaf, reservasi Anda belum dapat kami setujui.
        Detail reservasi:
        📍 Tempat : ${tempatText}
        📅 Tanggal : ${tanggal}
        🆔 Kode Booking : ${kodeBooking}
        ${catatan ? `📝 Alasan / Catatan Admin :
        ${catatan}
        ` : ''}Untuk informasi lebih lanjut, silakan hubungi admin Disbudpar Kota Tangerang.
        Terima kasih atas perhatian dan pengertiannya 🙏`;
            }
            const waLink = `https://wa.me/${formattedPhone}?text=${encodeURIComponent(message)}`;

            // ==========================================
            // SIMPAN LOG WHATSAPP
            // ==========================================
            fetch('save_notif_log.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'reservation_id=<?= $reservation['id']; ?>' +
                    '&email=' +
                    encodeURIComponent(
                        document.getElementById('email').value
                    )
            });


            // ==========================================
            // BUKA WHATSAPP
            // ==========================================

            window.open(waLink, '_blank');
        }

        // ======================================================
        // SUBMIT AJAX
        // ======================================================
        function submitStatusUpdate() {
            const formData = new FormData();
            formData.append('id', '<?= $reservation['id']; ?>');
            formData.append('nama', document.getElementById('nama').value);
            formData.append('no_telepon', document.getElementById('no_telepon').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('place_id', document.getElementById('tempat').value);
            formData.append('keterangan', document.getElementById('keterangan').value);
            formData.append('status', document.getElementById('status').value);
            formData.append('note', document.getElementById('catatan').value);

            fetch('update_status.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    if (data.trim() === 'EMAIL_SUCCESS') {

                        Swal.fire({
                            icon: 'success',
                            title: 'Email Berhasil Dikirim',
                            text: 'Reservasi berhasil diperbarui dan email notifikasi telah berhasil dikirim.'
                        }).then(() => {

                            Swal.fire({
                                title: 'Kirim WhatsApp juga?',
                                text: 'Notifikasi email sudah berhasil dikirim.',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Kirim WA',
                                cancelButtonText: 'Tidak'
                            }).then((result) => {

                                if (result.isConfirmed) {
                                    sendWhatsApp(
                                        document.getElementById('status').value
                                    );
                                }

                                window.location.href =
                                    'kelola_reserv.php';

                            });

                        });

                    } else if (data.trim() === 'EMAIL_FAILED') {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Reservasi Berhasil Disimpan',
                            text: 'Namun email notifikasi gagal dikirim.'
                        });

                    } else {

                        Swal.fire({
                            icon: 'warning',
                            title: 'Email Gagal Dikirim',
                            text: 'Status reservasi berhasil diperbarui, namun email gagal dikirim.'
                        }).then(() => {

                            Swal.fire({
                                title: 'Kirim WhatsApp sebagai alternatif?',
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, Kirim WA',
                                cancelButtonText: 'Tidak'
                            }).then((result) => {

                                if (result.isConfirmed) {
                                    sendWhatsApp(
                                        document.getElementById('status').value
                                    );
                                }

                                window.location.href = 'kelola_reserv.php';

                            });

                        });

                        console.log(data);
                    }
                })
                .catch(error => {
                    console.log(error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Server error'
                    });
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