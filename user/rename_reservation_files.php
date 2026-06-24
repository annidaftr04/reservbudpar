<?php
include '../db.php';

$result = $conn->query("
    SELECT
        id,
        kode_booking,
        nama,
        file_upload,
        ktp_upload,
        surat_kelurahan_upload
    FROM reservations
");

while ($row = $result->fetch_assoc()) {

    $kode = $row['kode_booking'];

    $nama = preg_replace(
        '/[^A-Za-z0-9]/',
        '_',
        trim($row['nama'])
    );

    /* ======================
       SURAT PERMOHONAN
    ====================== */

    if (!empty($row['file_upload'])) {

        $oldFile = '../uploads/' . $row['file_upload'];

        if (file_exists($oldFile)) {

            $ext = strtolower(
                pathinfo($oldFile, PATHINFO_EXTENSION)
            );

            $newName =
                'Surat_Permohonan_' .
                $kode . '_' .
                $nama . '.' . $ext;

            $newPath = '../uploads/' . $newName;

            rename($oldFile, $newPath);

            $stmt = $conn->prepare("
                UPDATE reservations
                SET file_upload = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "si",
                $newName,
                $row['id']
            );

            $stmt->execute();
        }
    }

    /* ======================
       KTP
    ====================== */

    if (!empty($row['ktp_upload'])) {

        $oldFile = '../uploads/ktp/' . $row['ktp_upload'];

        if (file_exists($oldFile)) {

            $ext = strtolower(
                pathinfo($oldFile, PATHINFO_EXTENSION)
            );

            $newName =
                'KTP_' .
                $kode . '_' .
                $nama . '.' . $ext;

            $newPath = '../uploads/ktp/' . $newName;

            rename($oldFile, $newPath);

            $stmt = $conn->prepare("
                UPDATE reservations
                SET ktp_upload = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "si",
                $newName,
                $row['id']
            );

            $stmt->execute();
        }
    }

    /* ======================
       SURAT KELURAHAN
    ====================== */

    if (!empty($row['surat_kelurahan_upload'])) {

        $oldFile =
            '../assets/doc/' .
            $row['surat_kelurahan_upload'];

        if (file_exists($oldFile)) {

            $ext = strtolower(
                pathinfo($oldFile, PATHINFO_EXTENSION)
            );

            $newName =
                'Surat_Kelurahan_' .
                $kode . '_' .
                $nama . '.' . $ext;

            $newPath =
                '../assets/doc/' .
                $newName;

            rename($oldFile, $newPath);

            $stmt = $conn->prepare("
                UPDATE reservations
                SET surat_kelurahan_upload = ?
                WHERE id = ?
            ");

            $stmt->bind_param(
                "si",
                $newName,
                $row['id']
            );

            $stmt->execute();
        }
    }
}

echo "Selesai!";
