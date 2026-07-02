<?php
include 'includes/auth.php';
include '../db.php';
include 'includes/auto_status.php';

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

$stmt = $conn->prepare("
    SELECT
        reservations.*,
        places.name AS nama_tempat
    FROM reservations
    LEFT JOIN places
        ON reservations.place_id = places.id
    WHERE reservations.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Reservasi tidak ditemukan.";
    exit;
}

$reservation = $result->fetch_assoc();

$stmt->close();
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

    <title>Edit Reservasi | TNG Admin</title>

    <?php include 'includes/header.php'; ?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/edit_reservasi.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
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
                            <select id="status" class="form-select fw-bold">
                                <option value="pending"
                                    <?= $reservation['status'] == 'pending' ? 'selected' : ''; ?>>
                                    Pending
                                </option>

                                <option value="disetujui"
                                    <?= $reservation['status'] == 'disetujui' ? 'selected' : ''; ?>>
                                    Disetujui
                                </option>

                                <option value="ditolak"
                                    <?= $reservation['status'] == 'ditolak' ? 'selected' : ''; ?>>
                                    Ditolak
                                </option>

                                <option value="selesai"
                                    <?= $reservation['status'] == 'selesai' ? 'selected' : ''; ?>
                                    disabled>
                                    Selesai (Otomatis)
                                </option>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function confirmSave() {

            const newStatus = document.getElementById('status').value;
            const oldStatus = '<?= $reservation['status']; ?>';

            let title = 'Simpan perubahan?';
            let text = '';
            let icon = 'question';
            let confirmText = 'Ya';

            if (newStatus === 'disetujui' && oldStatus !== 'disetujui') {

                title = 'Setujui Reservasi?';
                text = 'Status reservasi akan diubah menjadi disetujui.';
                confirmText = 'Ya, Setujui';

            } else if (newStatus === 'ditolak' && oldStatus !== 'ditolak') {

                title = 'Tolak Reservasi?';
                text = 'Status reservasi akan diubah menjadi ditolak.';
                icon = 'warning';
                confirmText = 'Ya, Tolak';

            }

            Swal.fire({
                title: title,
                text: text,
                icon: icon,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Batal'
            }).then((result) => {

                if (result.isConfirmed) {
                    submitStatusUpdate();
                }

            });

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
                message = `Halo ${nama},

                Kami dengan senang hati menginformasikan bahwa reservasi Anda telah DISETUJUI. 🎉

                Detail reservasi:

                📍 Tempat : ${tempatText}
                📅 Tanggal : ${tanggal}
                🆔 Kode Booking : ${kodeBooking}

                ${catatan ? `📝 Catatan Admin :
                ${catatan}

                ` : ''}

                Silakan datang sesuai jadwal yang telah ditentukan.

                Terima kasih telah menggunakan layanan reservasi Disbudpar Kota Tangerang 😊`;
            } else {
                message = `Halo ${nama},

                Mohon maaf, reservasi Anda belum dapat kami setujui.
                
                Detail reservasi:

                📍 Tempat : ${tempatText}
                📅 Tanggal : ${tanggal}
                🆔 Kode Booking : ${kodeBooking}

                ${catatan ? `📝 Alasan / Catatan Admin :
                ${catatan}
                ` : ''}
                
                Untuk informasi lebih lanjut, silakan hubungi admin Disbudpar Kota Tangerang.

                Terima kasih atas perhatian dan pengertiannya 🙏`;
            }
            const waLink = `https://web.whatsapp.com/send?phone=${formattedPhone}&text=${encodeURIComponent(message)}`;

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
    </script>

</body>

</html>