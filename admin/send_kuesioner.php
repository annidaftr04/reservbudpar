<?php

require '../db.php';
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


// =====================================================
// H+1 DARI TANGGAL SELESAI
// =====================================================

$kemarin = date('Y-m-d', strtotime('-1 day'));


// =====================================================
// AMBIL DATA RESERVASI
// =====================================================

$sql = "

SELECT 
    reservations.*,
    places.name AS nama_tempat

FROM reservations

LEFT JOIN places
ON reservations.place_id = places.id

WHERE reservations.tanggal_selesai = ?
AND reservations.status = 'disetujui'
AND reservations.kuesioner_sent = 0

";

$stmt = $conn->prepare($sql);

$stmt->bind_param("s", $kemarin);

$stmt->execute();

$result = $stmt->get_result();


// DEBUG
echo 'Tanggal dicari: ' . $kemarin;
echo '<br>';

echo 'Jumlah data ditemukan: ' . $result->num_rows;
echo '<hr>';


// =====================================================
// LINK KUESIONER
// =====================================================

$link_kuesioner =
    "https://forms.gle/ISI_LINK_KUESIONER";


// =====================================================
// LOOP EMAIL
// =====================================================

while ($row = $result->fetch_assoc()) {

    $mail = new PHPMailer(true);

    try {

        // =============================================
        // SMTP
        // =============================================

        $mail->isSMTP();

        $mail->Host = 'smtp.gmail.com';

        $mail->SMTPAuth = true;

        $mail->Username =
            'disbudparreservasi@gmail.com';

        // APP PASSWORD
        $mail->Password =
            'hisy vkai avfe lrml';

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port = 587;


        // =============================================
        // PENGIRIM
        // =============================================

        $mail->setFrom(
            'disbudparreservasi@gmail.com',
            'Disbudpar Reservasi'
        );


        // =============================================
        // PENERIMA
        // =============================================

        $mail->addAddress(
            $row['email'],
            $row['nama']
        );

        $mail->isHTML(true);


        // =============================================
        // SUBJECT
        // =============================================

        $mail->Subject =
            'Terima Kasih Telah Menggunakan Fasilitas Disbudpar';


        // =============================================
        // BODY EMAIL
        // =============================================

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
            background:linear-gradient(135deg,#0d6efd,#002D62);
            padding:40px;
            text-align:center;
            color:white;
        ">

            <h1 style="
                margin:0;
                font-size:30px;
            ">
                Terima Kasih 🙏
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
                Halo <b>{$row['nama']}</b>,
            </p>

            <p style="
                font-size:15px;
                line-height:1.9;
                color:#555;
            ">

                Terima kasih telah menggunakan fasilitas

                <b style='color:#0d6efd'>
                    {$row['nama_tempat']}
                </b>

                dari Dinas Kebudayaan dan Pariwisata
                Kota Tangerang.

            </p>

            <div style="
                background:#f8f9fa;
                border-left:5px solid #0d6efd;
                padding:20px;
                border-radius:14px;
                margin:25px 0;
                color:#555;
                line-height:1.8;
            ">

                Kami berharap kegiatan Anda berjalan
                dengan lancar dan memberikan pengalaman
                yang menyenangkan.

            </div>

            <p style="
                font-size:15px;
                line-height:1.8;
                color:#555;
            ">

                Untuk membantu kami meningkatkan kualitas pelayanan,
                kami sangat menghargai kesediaan Anda untuk
                mengisi kuesioner evaluasi melalui tombol berikut:

            </p>

            <!-- BUTTON -->

            <div style="
                text-align:center;
                margin:40px 0;
            ">

                <a href="https://ekinerjabudpar.org/kuesionertangerang/"

                    style="
                        background:linear-gradient(135deg,#0d6efd,#0047b3);
                        color:white;
                        padding:16px 35px;
                        border-radius:14px;
                        text-decoration:none;
                        font-weight:bold;
                        display:inline-block;
                        font-size:15px;
                        box-shadow:0 8px 20px rgba(13,110,253,0.3);
                    ">

                    Isi Kuesioner

                </a>

            </div>

            <p style="
                font-size:14px;
                color:#777;
                line-height:1.8;
            ">

                Masukan dan saran dari Anda sangat berarti
                bagi kami dalam meningkatkan pelayanan
                fasilitas publik di Kota Tangerang.

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


        // =============================================
        // KIRIM EMAIL
        // =============================================

        // =============================================
        // KIRIM EMAIL
        // =============================================

        $mail->send();


        // =============================================
        // EMAIL LOG
        // =============================================

        $jenis_email = 'kuesioner_h1';

        $status_pengiriman = 'berhasil';

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

            $row['id'],
            $jenis_email,
            $row['email'],
            $status_pengiriman

        );

        $log->execute();


        echo 'Email berhasil dikirim ke: ' .
            $row['email'];

        echo '<hr>';


        // =============================================
        // UPDATE SUDAH TERKIRIM
        // =============================================

        $update = $conn->prepare("

            UPDATE reservations

            SET kuesioner_sent = 1

            WHERE id = ?

        ");

        $update->bind_param(
            "i",
            $row['id']
        );

        $update->execute();
    } catch (Exception $e) {

        // =========================================
        // EMAIL LOG GAGAL
        // =========================================

        $jenis_email = 'kuesioner_h1';

        $status_pengiriman = 'gagal';

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

            $row['id'],
            $jenis_email,
            $row['email'],
            $status_pengiriman

        );

        $log->execute();


        echo 'Email gagal dikirim ke: ' .
            $row['email'];

        echo '<br>';

        echo $mail->ErrorInfo;

        echo '<hr>';
    }
}

echo 'Selesai kirim email kuesioner';
