<?php

session_start();

if (!isset($_SESSION['admin_id'])) {

    exit("Akses ditolak");
}

require '../db.php';


// ==============================================
// FILTER
// ==============================================

$kode_booking =
    $_GET['kode_booking'] ?? '';

$nama =
    $_GET['nama'] ?? '';

$place_id =
    $_GET['place_id'] ?? '';

$status =
    $_GET['status'] ?? '';
    
$sumber_search =
    $_GET['sumber_reservasi'] ?? '';


// ==============================================
// QUERY
// ==============================================

$query = "

    SELECT

        r.*,
        p.name AS nama_tempat

    FROM reservations r

    LEFT JOIN places p
    ON r.place_id = p.id

    WHERE

        (r.kode_booking LIKE ? OR ? = '')

    AND

        (r.nama LIKE ? OR ? = '')

    AND

        (r.place_id = ? OR ? = '')

    AND

    (r.status LIKE ? OR ? = '')

    AND

        (r.sumber_reservasi LIKE ? OR ? = '')

    ORDER BY r.id DESC

";

$stmt = $conn->prepare($query);

$search_kode =
    "%$kode_booking%";

$search_nama =
    "%$nama%";

$search_status =
    "%$status%";

$search_sumber =
    "%$sumber_search%";

$stmt->bind_param(

    "ssssssssss",

    $search_kode,
    $kode_booking,

    $search_nama,
    $nama,

    $place_id,
    $place_id,

    $search_status,
    $status,

    $search_sumber,
    $sumber_search

);
$stmt->execute();

$result =
    $stmt->get_result();


// ==============================================
// HEADER EXCEL
// ==============================================

header("Content-Type: application/vnd.ms-excel");

header(

    "Content-Disposition: attachment; filename=laporan_reservasi.xls"

);


// ==============================================
// TABLE
// ==============================================

echo "

<table border='1'>

<tr>

    <th>Kode Booking</th>
    <th>Nama</th>
    <th>Email</th>
    <th>No Telepon</th>
    <th>Tempat</th>
    <th>Tanggal</th>
    <th>Jam Mulai</th>
    <th>Jam Selesai</th>
    <th>Status</th>
    <th>Sumber Reservasi</th>

</tr>

";

while ($row = $result->fetch_assoc()) {

    echo "

    <tr>

        <td>{$row['kode_booking']}</td>

        <td>{$row['nama']}</td>

        <td>{$row['email']}</td>

        <td>{$row['no_telepon']}</td>

        <td>{$row['nama_tempat']}</td>

        <td>{$row['hari']}</td>

        <td>{$row['jam_mulai']}</td>

        <td>{$row['jam_selesai']}</td>

        <td>{$row['status']}</td>

        <td>{$row['sumber_reservasi']}</td>

    </tr>

    ";
}

echo "</table>";
