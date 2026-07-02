<?php
date_default_timezone_set('Asia/Jakarta');

$today = date('Y-m-d');
$now = date('H:i:s');

$sql = "
UPDATE reservations
SET status = 'selesai'
WHERE status = 'disetujui'
AND (
    (
        tanggal_selesai IS NOT NULL
        AND (
            tanggal_selesai < '$today'
            OR (
                tanggal_selesai = '$today'
                AND jam_selesai <= '$now'
            )
        )
    )
    OR
    (
        tanggal_selesai IS NULL
        AND (
            hari < '$today'
            OR (
                hari = '$today'
                AND jam_selesai <= '$now'
            )
        )
    )
)
";

$conn->query($sql);
