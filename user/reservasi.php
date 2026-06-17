<?php
session_start();
include '../db.php';

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

date_default_timezone_set('Asia/Jakarta');

// Tangkap data dari navigasi sebelumnya
// ==============================
// AMBIL DATA DARI URL
// ==============================

$place_id = $_GET['place_id'] ?? '';
$sub_place_id = $_GET['sub_place_id'] ?? '';
$selected_date = $_GET['date'] ?? date('Y-m-d');

$selected_place = '';
$selected_sub_place = '';
$displayed_place_name = '';

// ==============================
// AMBIL DATA TEMPAT
// ==============================

if ($place_id != '') {

    $stmtPlace = $conn->prepare("
        SELECT *
        FROM places
        WHERE id = ?
    ");

    $stmtPlace->bind_param("i", $place_id);
    $stmtPlace->execute();

    $placeData = $stmtPlace->get_result()->fetch_assoc();

    if ($placeData) {

        $selected_place = $placeData['name'];
        $displayed_place_name = $placeData['name'];
    }
}

// ==============================
// AMBIL DATA SUB TEMPAT
// ==============================

if ($sub_place_id != '') {

    $stmtSub = $conn->prepare("
        SELECT *
        FROM sub_places
        WHERE id = ?
    ");

    $stmtSub->bind_param("i", $sub_place_id);
    $stmtSub->execute();

    $subData = $stmtSub->get_result()->fetch_assoc();

    if ($subData) {

        $selected_sub_place = $subData['nama_subtempat'];

        $displayed_place_name .= ' (' . $selected_sub_place . ')';
    }
}

// Ambil informasi user untuk form
$user_id = $_SESSION['user_id'];
$user_query = "SELECT username FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("i", $user_id);
$user_stmt->execute();
$user_data = $user_stmt->get_result()->fetch_assoc();
$user_stmt->close();

// AMBIL DAFTAR TEMPAT UNTUK DROPLIST
$all_places = $conn->query("
    SELECT id, name
    FROM places
    ORDER BY name ASC
");
// ================= PROSES SIMPAN =================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['hari'])) {

    $kode_booking = uniqid('RES-');

    $nama              = $_POST['nama'];
    $no_telepon        = $_POST['no_telepon'];
    $email             = $_POST['email'];

    $hari              = $_POST['hari'];
    $tanggal_selesai   = $_POST['tanggal_selesai'];

    $jam_mulai         = $_POST['jam_mulai'];
    $jam_selesai       = $_POST['jam_selesai'];

    $keterangan        = $_POST['keterangan'];

    $status            = 'pending';

    // ambil dari URL/database baru
    $place_id_fix      = $place_id ?: null;
    $sub_place_id_fix  = $sub_place_id ?: null;

    // upload file
    function uploadFile($inputName, $dir)
    {

        if (isset($_FILES[$inputName]) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {

            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            $name = uniqid() . '_' . basename($_FILES[$inputName]['name']);

            move_uploaded_file(
                $_FILES[$inputName]['tmp_name'],
                $dir . $name
            );

            return $name;
        }

        return null;
    }

    $file_name = uploadFile('file_upload', '../uploads/');
    $ktp_name  = uploadFile('ktp_upload', '../uploads/ktp/');

    $surat_kelurahan = null;

    if ($place_id == 20) {

        // upload surat
        if (
            isset($_FILES['surat_kelurahan_upload']) &&
            $_FILES['surat_kelurahan_upload']['error'] == 0
        ) {

            $namaFile =
                time() . '_' .
                $_FILES['surat_kelurahan_upload']['name'];

            move_uploaded_file(
                $_FILES['surat_kelurahan_upload']['tmp_name'],
                '../assets/doc/' . $namaFile
            );

            $surat_kelurahan = $namaFile;
        } else {

            die("Surat kelurahan wajib diupload untuk Gedung Seni Budaya.");
        }
    }

    // QUERY BARU SESUAI DATABASE
    $sql_insert = "
        INSERT INTO reservations (
            kode_booking,
            user_id,
            place_id,
            sub_place_id,
            nama,
            no_telepon,
            email,
            hari,
            tanggal_selesai,
            jam_mulai,
            jam_selesai,
            keterangan,
            file_upload,
            ktp_upload,
            surat_kelurahan_upload,
            status
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
        )
    ";

    $stmt = $conn->prepare($sql_insert);

    // DEBUG kalau query gagal
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param(
        "siiissssssssssss",

        $kode_booking,
        $user_id,
        $place_id_fix,
        $sub_place_id_fix,
        $nama,
        $no_telepon,
        $email,
        $hari,
        $tanggal_selesai,
        $jam_mulai,
        $jam_selesai,
        $keterangan,
        $file_name,
        $ktp_name,
        $surat_kelurahan,
        $status
    );


    if (!$stmt->execute()) {
        die("Execute gagal: " . $stmt->error);
    } else {

        require '../vendor/autoload.php';

        $mail = new PHPMailer(true);

        try {

            // =========================
            // CONFIG SMTP
            // =========================

            $mail->isSMTP();

            $mail->Host = 'smtp.gmail.com';

            $mail->SMTPAuth = true;

            $mail->Username = 'disbudparreservasi@gmail.com';

            $mail->Password = 'mvphwlcvcwiebjcf';

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

            $mail->Port = 465;

            // DEBUG
            $mail->SMTPDebug = 0;

            // =========================
            // PENGIRIM
            // =========================

            $mail->setFrom(
                'disbudparreservasi@gmail.com',
                'Sistem Reservasi Disbudpar'
            );

            // =========================
            // TUJUAN EMAIL ADMIN
            // =========================

            $mail->addAddress(
                'skpbudpar@gmail.com',
                'Admin Disbudpar'
            );

            // =========================
            // FORMAT EMAIL
            // =========================

            $mail->isHTML(true);

            $mail->Subject =
                'Reservasi Baru Masuk - ' . $kode_booking;

            // =========================
            // FORMAT TEMPAT
            // =========================

            $nama_tempat_email = $selected_place;

            if (!empty($selected_sub_place)) {

                $nama_tempat_email .=
                    ' (' . $selected_sub_place . ')';
            }

            // =========================
            // ISI EMAIL
            // =========================

            $mail->Body = "

        <div style='font-family: Arial, sans-serif;'>

            <h2 style='color:#002D62;'>
                Reservasi Baru Masuk
            </h2>

            <p>
                Terdapat pengajuan reservasi baru
                yang masuk ke sistem.
            </p>

            <table
                cellpadding='8'
                style='border-collapse: collapse; width:100%;'
            >

                <tr>
                    <td><b>Kode Booking</b></td>
                    <td>: {$kode_booking}</td>
                </tr>

                <tr>
                    <td><b>Nama Pemohon</b></td>
                    <td>: {$nama}</td>
                </tr>

                <tr>
                    <td><b>Tempat</b></td>
                    <td>: {$nama_tempat_email}</td>
                </tr>

                <tr>
                    <td><b>Tanggal</b></td>
                    <td>: {$hari} s/d {$tanggal_selesai}</td>
                </tr>

                <tr>
                    <td><b>Jam</b></td>
                    <td>: {$jam_mulai} - {$jam_selesai}</td>
                </tr>

            </table>

            <br>

            <p>
                Silakan login ke dashboard admin
                untuk melakukan verifikasi reservasi.
            </p>

        </div>

        ";

            // =========================
            // KIRIM EMAIL
            // =========================

            $mail->send();
        } catch (Exception $e) {

            file_put_contents(

                'email_error.txt',

                $mail->ErrorInfo

            );
        }

        // =========================
        // ALERT SUKSES
        // =========================

        echo "

    <script>

        window.onload = function() {

            Swal.fire(

                'Berhasil!',

                'Reservasi Anda telah diajukan.',

                'success'

            ).then(() => {

                window.location.href='dashboard.php';

            });

        };

    </script>

    ";
    }
    $stmt->close();
}

// Ambil Tanggal Terisi (Booked)
$booked_dates = [];

if ($place_id != '') {

    $sqlBooked = "
        SELECT hari
        FROM reservations
        WHERE place_id = ?
        AND status = 'disetujui'
    ";

    if ($sub_place_id != '') {

        $sqlBooked .= " AND sub_place_id = ?";
    }

    $stmtB = $conn->prepare($sqlBooked);

    if ($sub_place_id != '') {

        $stmtB->bind_param("ii", $place_id, $sub_place_id);
    } else {

        $stmtB->bind_param("i", $place_id);
    }

    $stmtB->execute();

    $resB = $stmtB->get_result();

    while ($r = $resB->fetch_assoc()) {

        $booked_dates[] = $r['hari'];
    }
}

function build_calendar_custom($month, $year, $booked_dates, $selected_date)
{
    $daysOfWeek = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    $firstDay = mktime(0, 0, 0, $month, 1, $year);
    $numberDays = date('t', $firstDay);
    $startDay = date('w', $firstDay);

    $html = "<div class='modern-calendar'>";
    $html .= "<div class='calendar-header'>" . date('F Y', $firstDay) . "</div>";
    $html .= "<div class='calendar-grid'>";
    foreach ($daysOfWeek as $d) $html .= "<div class='day-name'>$d</div>";
    for ($i = 0; $i < $startDay; $i++) $html .= "<div></div>";

    for ($d = 1; $d <= $numberDays; $d++) {
        $fullDate = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-" . str_pad($d, 2, "0", STR_PAD_LEFT);
        $class = in_array($fullDate, $booked_dates) ? 'booked' : (($fullDate < date('Y-m-d')) ? 'past' : 'available');
        $activeClass = ($fullDate === $selected_date) ? 'selected' : '';
        $html .= "<div class='day-num $class $activeClass' data-date='$fullDate'>$d</div>";
    }
    $html .= "</div></div>";
    return $html;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Reservasi | Kota Tangerang</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon" />
    <style>
        :root {
            --primary: #002D62;
            --accent: #FF5733;
            --glass: rgba(255, 255, 255, 0.95);
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            padding: 40px 0;
        }

        .main-container {
            max-width: 1100px;
            margin: auto;
        }

        .glass-card {
            background: var(--glass);
            backdrop-filter: blur(15px);
            border-radius: 40px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
        }

        .sidebar-info {
            background: var(--primary);
            color: white;
            padding: 50px;
            flex: 1 1 400px;
        }

        .form-section {
            padding: 50px;
            flex: 1 1 600px;
            background: white;
        }

        .modern-calendar {
            background: rgba(255, 255, 255, 0.1);
            padding: 25px;
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .calendar-header {
            text-align: center;
            font-weight: 800;
            margin-bottom: 20px;
            color: white;
            font-size: 1.2rem;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            text-align: center;
        }

        .day-name {
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
        }

        .day-num {
            padding: 12px 0;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
        }

        .day-num.available {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .day-num.available:hover {
            background: var(--accent);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .day-num.selected {
            background: white !important;
            color: var(--primary) !important;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .day-num.booked {
            background: #fee2e2;
            color: #ef4444;
            cursor: not-allowed;
            text-decoration: line-through;
            opacity: 0.6;
        }

        .day-num.past {
            opacity: 0.3;
            cursor: not-allowed;
        }

        .form-label {
            font-weight: 700;
            color: var(--primary);
            font-size: 0.9rem;
            margin-bottom: 8px;
        }

        .form-control {
            border-radius: 15px;
            border: 2px solid #edf2f7;
            padding: 12px 18px;
            transition: 0.3s;
            font-weight: 500;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(0, 45, 98, 0.1);
        }

        .upload-box {
            border: 2px dashed #cbd5e0;
            border-radius: 20px;
            padding: 20px;
            background: #f8fafc;
            transition: 0.3s;
        }

        .btn-submit {
            background: linear-gradient(135deg, var(--primary) 0%, #004d99 100%);
            color: white;
            border-radius: 20px;
            padding: 18px;
            font-weight: 800;
            border: none;
            width: 100%;
            transition: 0.3s;
            box-shadow: 0 15px 30px rgba(0, 45, 98, 0.2);
            letter-spacing: 1px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 40px rgba(0, 45, 98, 0.3);
        }

        .badge-step {
            background: rgba(0, 45, 98, 0.1);
            color: var(--primary);
            padding: 8px 15px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.75rem;
        }

        /* Style Droplist */
        .dropdown-luxury {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 15px;
            padding: 15px;
            width: 100%;
            font-weight: 600;
            cursor: pointer;
        }

        .dropdown-luxury option {
            color: black;
        }
    </style>
</head>

<body>

    <div class="container main-container">
        <div class="glass-card">
            <div class="sidebar-info">
                <a href="index.php" class="text-decoration-none text-white opacity-75 mb-4 d-inline-block">
                    <i class="fas fa-chevron-left me-2"></i> Kembali ke Galeri
                </a>

                <h2 class="fw-extrabold mb-4" style="font-weight: 800;">Pilih Jadwal</h2>
                <p class="opacity-75 small mb-5">Silakan tentukan tanggal mulai acara Anda pada kalender di bawah ini.</p>

                <?= build_calendar_custom(5, 2026, $booked_dates, $selected_date); ?>

                <div class="mt-5 pt-4 border-top border-white border-opacity-10">
                    <h6 class="fw-bold mb-3"><i class="fas fa-location-dot me-2 text-accent"></i>Lokasi Terpilih</h6>

                    <?php if ($selected_place): ?>
                        <div class="p-4 rounded-4"
                            style="
                            background: rgba(255,255,255,0.1);
                            border: 1px solid rgba(255,255,255,0.1);
                        ">
                            <span class="fs-5 fw-bold d-block">
                                <?= $displayed_place_name ?>
                            </span>
                        </div>
                    <?php else: ?>
                        <select class="dropdown-luxury" name="tempat" id="tempat_select" form="formRes" required onchange="handleLocationChange(this.value)">
                            <option value="" disabled selected>-- Pilih Lokasi --</option>
                            <?php while ($row = $all_places->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>">
                                    <?= $row['name'] ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-section">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <h3 class="fw-bold text-dark mb-0">Konfirmasi Reservasi</h3>
                    <span class="badge-step text-uppercase">Langkah Akhir</span>
                </div>

                <form method="POST" enctype="multipart/form-data" id="formRes">
                    <input type="hidden" id="hari" name="hari" value="<?= $selected_date ?>" required>
                    <input type="hidden" name="sub_tempat" value="<?= htmlspecialchars($selected_sub_place) ?>">
                    <input type="hidden" name="jam_mulai" value="08:00">
                    <input type="hidden" name="jam_selesai" value="16:00">

                    <div class="row g-4">
                        <div class="col-md-12">
                            <label class="form-label">
                                Nama Lengkap Pemohon
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($user_data['username']) ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Nomor WhatsApp
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="no_telepon" placeholder="0812..." required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Alamat Email
                                <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" name="email" placeholder="email@anda.com" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Tanggal Mulai Acara
                                <span class="text-danger">*</span>
                            </label>
                            <div class="small text-muted mb-2">
                                Pilih tanggal mulai pelaksanaan acara Anda.
                            </div>
                            <input type="date" class="form-control bg-light" id="display_hari" value="<?= $selected_date ?>" readonly>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                Tanggal Selesai Acara
                                <span class="text-danger">*</span>
                            </label>
                            <div class="small text-muted mb-2">
                                Pilih tanggal selesai kegiatan atau acara.
                            </div>
                            <input type="date" class="form-control" name="tanggal_selesai" id="tanggal_selesai" required min="<?= $selected_date ?>" value="<?= $selected_date ?>">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">
                                Tujuan / Keterangan Acara
                                <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" name="keterangan" rows="3" placeholder="Sebutkan jenis kegiatan Anda..." required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-primary">
                                Surat Permohonan Izin
                                <span class="text-danger">*</span>
                            </label>
                            <div class="small text-muted mb-2">
                                Upload surat permohonan kegiatan resmi.
                            </div>
                            <input type="file"
                                class="form-control"
                                name="file_upload"
                                required>
                            <!-- TEMPLATE -->
                            <div class="mt-2">
                                <a href="../assets/doc/Surat_Permohonan.docx"
                                    download
                                    class="text-decoration-none small fw-semibold text-primary">
                                    <i class="fas fa-download me-1"></i>
                                    Download Contoh Surat Permohonan
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6"
                            id="wrapper_kelurahan"
                            style="display: none;">
                            <label class="form-label text-primary">
                                Surat Keterangan Kelurahan
                                <span class="text-danger">*</span>
                            </label>
                            <div class="small text-muted mb-2">
                                Khusus reservasi Gedung Seni Budaya.
                            </div>
                            <input type="file"
                                class="form-control"
                                name="surat_kelurahan_upload"
                                id="input_kelurahan">
                            <!-- TEMPLATE -->
                            <div class="mt-2">
                                <a href="../assets/doc/Surat_keterangan_kelurahan.docx"
                                    download
                                    class="text-decoration-none small fw-semibold text-primary">
                                    <i class="fas fa-download me-1"></i>
                                    Download Contoh Surat Kelurahan
                                </a>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label text-primary">
                                Identitas Diri (KTP/SIM)
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control" name="ktp_upload" required>
                        </div>

                        <div class="col-md-12 mt-5">
                            <button type="submit" class="btn-submit text-uppercase mb-3">
                                Kirim Pengajuan <i class="fas fa-paper-plane ms-2"></i>
                            </button>
                            <div class="text-center">
                                <a href="dashboard.php" class="text-muted fw-bold small text-decoration-none">
                                    <i class="fas fa-times me-1"></i> Batal dan Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Logic Surat Kelurahan
        function checkKelurahanRequirement(placeName) {
            const wrapper = document.getElementById('wrapper_kelurahan');
            const input = document.getElementById('input_kelurahan');
            if (placeName == '20') {
                wrapper.style.display = 'block';
                input.setAttribute('required', 'required');
            } else {
                wrapper.style.display = 'none';
                input.removeAttribute('required');
            }
        }

        function handleLocationChange(val) {
            checkKelurahanRequirement(val);
            window.location.href = `reservasi.php?place_id=${val}`;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const initialPlace = "<?= $selected_place ?>";
            if (initialPlace) checkKelurahanRequirement(initialPlace);
        });

        document.querySelectorAll(".day-num.available").forEach(td => {
            td.addEventListener("click", function() {
                let date = this.getAttribute("data-date");
                document.getElementById("hari").value = date;
                document.getElementById("display_hari").value = date;
                let tglSelesaiInput = document.getElementById("tanggal_selesai");
                tglSelesaiInput.min = date;
                tglSelesaiInput.value = date;
                document.querySelectorAll(".day-num").forEach(el => el.classList.remove("selected"));
                this.classList.add("selected");
            });
        });

        document.getElementById('formRes').onsubmit = function() {
            Swal.fire({
                title: 'Memproses Pengajuan',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                }
            });
        };
    </script>
</body>

</html>