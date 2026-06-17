<?php

require '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $reservation_id =
        intval($_POST['reservation_id']);

    $email =
        $_POST['email'];

    $jenis_notifikasi =
        'whatsapp_manual';

    $stmt = $conn->prepare("

        INSERT INTO notifikasi_log (

            reservation_id,
            email,
            jenis_notifikasi,
            tanggal_kirim

        )

        VALUES (?, ?, ?, NOW())

    ");

    $stmt->bind_param(

        "iss",

        $reservation_id,
        $email,
        $jenis_notifikasi

    );

    if ($stmt->execute()) {

        echo 'OK';

    } else {

        echo 'Gagal';

    }

}
?>