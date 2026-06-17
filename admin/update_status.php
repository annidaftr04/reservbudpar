<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

require '../db.php';
require 'reservation_pdf.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ======================================================
// INPUT
// ======================================================
$id           = intval($_POST['id']);
$nama         = $_POST['nama'];
$no_telepon   = $_POST['no_telepon'];
$email        = $_POST['email'];
$place_id     = intval($_POST['place_id']);
$keterangan   = $_POST['keterangan'];
$status       = $_POST['status'];
$catatan      = $_POST['note'];
$admin_id     = $_SESSION['admin_id'];

// ======================================================
// UPDATE DATABASE
// ======================================================
$stmt = $conn->prepare("
    UPDATE reservations
SET
    nama = ?,
    no_telepon = ?,
    email = ?,
    place_id = ?,
    keterangan = ?,
    catatan = ?,
    status = ?,
    admin_id = ?
WHERE id = ?
");

$stmt->bind_param(
    "sssisssii",
    $nama,
    $no_telepon,
    $email,
    $place_id,
    $keterangan,
    $catatan,
    $status,
    $admin_id,
    $id
);

if (!$stmt->execute()) {
    echo "Gagal update database";
    exit;
}

// ======================================================
// AMBIL DATA RESERVASI
// ======================================================
$res = $conn->prepare("
    SELECT
        reservations.*,
        places.name AS nama_tempat
    FROM reservations
    LEFT JOIN places
    ON reservations.place_id = places.id
    WHERE reservations.id = ?
");
$res->bind_param("i", $id);
$res->execute();
$reservation =
    $res
    ->get_result()
    ->fetch_assoc();
// ======================================================
// JIKA STATUS MASIH PENDING
// ======================================================
if ($status === 'pending') {
    echo "OK";
    exit;
}

// ======================================================
// PHPMailer
// ======================================================
$mail = new PHPMailer(true);
try {
    // ==================================================
    // SMTP
    // ==================================================
    $mail->isSMTP();
    $mail->Host =
        'smtp.gmail.com';
    $mail->SMTPAuth =
        true;
    $mail->Username =
        'disbudparreservasi@gmail.com';
    $mail->Password =
        'hisy vkai avfe lrml';
    $mail->SMTPSecure =
        PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // ==================================================
    // PENGIRIM
    // ==================================================
    $mail->setFrom(
        'disbudparreservasi@gmail.com',
        'Disbudpar Reservasi'
    );

    // ==================================================
    // PENERIMA
    // ==================================================
    $mail->addAddress(
        $email,
        $nama
    );
    $mail->isHTML(true);
    // ==================================================
    // EMAIL DISETUJUI
    // ==================================================
    if ($status === 'disetujui') {
        $mail->Subject =
            'Reservasi Disetujui - #' .
            $reservation['kode_booking'];
        $mail->Body = <<<HTML
<div style="
    margin:0;
    padding:40px 20px;
    background:#f4f7fb;
    font-family:Arial,sans-serif;
">
    <div style="
        max-width:650px;
        margin:auto;
        background:white;
        border-radius:24px;
        overflow:hidden;
        box-shadow:0 10px 40px rgba(0,0,0,0.08);
    ">
        <!-- HEADER -->
        <div style="
            background:linear-gradient(135deg,#198754,#0d6efd);
            padding:40px;
            text-align:center;
            color:white;
        ">
            <div style="
                font-size:55px;
                margin-bottom:10px;
            ">
                ✅
            </div>

            <h1 style="
                margin:0;
                font-size:30px;
            ">
                Reservasi Disetujui
            </h1>

            <p style="
                margin-top:12px;
                opacity:0.9;
                font-size:15px;
            ">
                Dinas Kebudayaan dan Pariwisata Kota Tangerang
            </p>
        </div>
        <!-- CONTENT -->
        <div style="padding:40px;">
            <p style="
                font-size:16px;
                color:#333;
            ">
                Halo <b>{$nama}</b>,
            </p>

            <p style="
                font-size:15px;
                line-height:1.9;
                color:#555;
            ">
                Reservasi Anda telah berhasil
                <b style="color:#198754">
                    DISETUJUI
                </b>.
            </p>
            <!-- DETAIL -->
            <div style="
                background:#f8f9fa;
                border-radius:18px;
                padding:25px;
                margin:30px 0;
            ">
                <table width="100%" cellpadding="10">
                    <tr>
                        <td width="35%">
                            <b>Kode Booking</b>
                        </td>
                        <td>
                            {$reservation['kode_booking']}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <b>Tempat</b>
                        </td>
                        <td>
                            {$reservation['nama_tempat']}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <b>Tanggal</b>
                        </td>
                        <td>
                            {$reservation['hari']}
                        </td>
                    </tr>
                </table>
            </div>
            <!-- INFO PDF -->
            <div style="
                background:#e8f7ee;
                border-left:5px solid #198754;
                padding:18px;
                border-radius:12px;
                color:#333;
                line-height:1.8;
            ">
                Surat reservasi resmi telah
                dilampirkan dalam bentuk PDF
                pada email ini.
            </div>
            <br>
            <p style="
                font-size:15px;
                line-height:1.8;
                color:#666;
            ">
                Mohon untuk menggunakan fasilitas
                sesuai jadwal yang telah disetujui.
            </p>
            <br>
            <p style="
                font-size:15px;
                color:#333;
            ">
                Hormat kami,<br>
                <b>
                    Disbudpar Kota Tangerang
                </b>
            </p>
        </div>
        <!-- FOOTER -->
        <div style="
            background:#f8f9fa;
            padding:20px;
            text-align:center;
            font-size:12px;
            color:#888;
        ">
            © 2026 Dinas Kebudayaan dan Pariwisata Kota Tangerang
        </div>
    </div>
</div>
HTML;
        // ==============================================
        // ATTACH PDF
        // ==============================================
        $pdfString = buatPDFReservasi(
            $reservation,
            $status
        );

        $mail->addStringAttachment(
            $pdfString,
            'Surat_Reservasi_' .
                $reservation['kode_booking'] .
                '.pdf',
            'base64',
            'application/pdf'
        );
    }

    // ==================================================
    // EMAIL DITOLAK
    // ==================================================
    elseif ($status === 'ditolak') {
        $mail->Subject =
            'Reservasi Ditolak - #' .
            $reservation['kode_booking'];
        $mail->Body = <<<HTML
<div style="
    padding:40px;
    background:#f4f7fb;
    font-family:Arial,sans-serif;
">
    <div style="
        max-width:650px;
        margin:auto;
        background:white;
        border-radius:24px;
        overflow:hidden;
    ">
        <div style="
            background:linear-gradient(135deg,#dc3545,#8b0000);
            padding:40px;
            text-align:center;
            color:white;
        ">
            <div style="
                font-size:55px;
                margin-bottom:10px;
            ">
                ❌
            </div>
            <h1 style="
                margin:0;
                font-size:30px;
            ">
                Reservasi Ditolak
            </h1>
        </div>
        <div style="padding:40px;">
            <p>
                Halo <b>{$nama}</b>,
            </p>

            <p style="
                line-height:1.8;
                color:#555;
            ">
                Mohon maaf,
                reservasi Anda dinyatakan
                <b style="color:#dc3545">
                    DITOLAK
                </b>.
            </p>
            <div style="
                background:#fff5f5;
                border-left:5px solid #dc3545;
                padding:22px;
                border-radius:14px;
                margin:30px 0;
            ">
                <div style="
                    font-weight:bold;
                    margin-bottom:10px;
                    color:#dc3545;
                ">
                    Catatan Admin
                </div>
                <div style="
                    color:#555;
                    line-height:1.8;
                ">
                    {$catatan}
                </div>
            </div>
        </div>
    </div>
</div>
HTML;
    }
    // ==================================================
    // KIRIM EMAIL
    // ==================================================
    $mail->send();
    // ==================================================
    // EMAIL LOG BERHASIL
    // ==================================================
    $jenis_email =
        ($status == 'disetujui')
        ? 'approval'
        : 'penolakan';
    $status_pengiriman =
        'berhasil';
    $log = $conn->prepare("
        INSERT INTO email_logs (
            reservation_id,
            jenis_email,
            email_tujuan,
            status_pengiriman,
            waktu_kirim
        )
        VALUES (?, ?, ?, ?, NOW())
    ");
    $log->bind_param(
        "isss",

        $id,
        $jenis_email,
        $email,
        $status_pengiriman
    );
    $log->execute();
    echo "OK";
}
// ======================================================
// ERROR EMAIL
// ======================================================
catch (Exception $e) {
    $jenis_email =
        ($status == 'disetujui')
        ? 'approval'
        : 'penolakan';
    $status_pengiriman =
        'gagal';
    $log = $conn->prepare("
        INSERT INTO email_logs (
            reservation_id,
            jenis_email,
            email_tujuan,
            status_pengiriman,
            waktu_kirim
        )
        VALUES (?, ?, ?, ?, NOW())
    ");
    $log->bind_param(
        "isss",
        $id,
        $jenis_email,
        $email,
        $status_pengiriman
    );
    $log->execute();
    echo $mail->ErrorInfo;
}
