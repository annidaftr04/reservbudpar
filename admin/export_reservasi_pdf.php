<?php

session_start();

if (!isset($_SESSION['admin_id'])) {

    exit("Akses ditolak");
}

require '../db.php';

require '../fpdf/fpdf.php';


// ==========================================
// FILTER
// ==========================================

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


// ==========================================
// QUERY
// ==========================================

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


// ==========================================
// PDF
// ==========================================

$pdf = new FPDF('L', 'mm', 'A4');

$pdf->AddPage();


// ==========================================
// HEADER
// ==========================================

$pdf->Image('../assets/img/logotng.png', 10, 10, 20);

$pdf->SetFont('Arial', 'B', 16);

$pdf->Cell(0, 10, 'LAPORAN DATA RESERVASI', 0, 1, 'C');

$pdf->SetFont('Arial', '', 11);

$pdf->Cell(0, 8, 'Dinas Kebudayaan dan Pariwisata Kota Tangerang', 0, 1, 'C');

$pdf->Ln(8);


// ==========================================
// INFO FILTER
// ==========================================

$pdf->SetFont('Arial', '', 10);

$pdf->Cell(40, 7, 'Filter Status');
$pdf->Cell(60, 7, ': ' . ($status ?: 'Semua'));
$pdf->Ln();

$pdf->Cell(40, 7, 'Filter Tempat');
$pdf->Cell(60, 7, ': ' . ($place_id ?: 'Semua'));
$pdf->Ln();

$pdf->Cell(40, 7, 'Filter Sumber Reservasi');
$pdf->Cell(60, 7, ': ' . ($sumber_search ?: 'Semua'));
$pdf->Ln(12);


// ==========================================
// TABLE HEADER
// ==========================================

$pdf->SetFillColor(67, 97, 238);

$pdf->SetTextColor(255);

$pdf->SetFont('Arial', 'B', 10);

$pdf->Cell(38, 10, 'Kode Booking', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Nama', 1, 0, 'C', true);
$pdf->Cell(55, 10, 'Tempat', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Tanggal', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Mulai', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Selesai', 1, 0, 'C', true);
$pdf->Cell(28, 10, 'Status', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'Sumber', 1, 1, 'C', true);


// ==========================================
// TABLE BODY
// ==========================================

$pdf->SetTextColor(0);

$pdf->SetFont('Arial', '', 9);

while ($row = $result->fetch_assoc()) {

    $tanggal_selesai =
        $row['tanggal_selesai']
        ? $row['tanggal_selesai']
        : $row['hari'];

    $pdf->Cell(38, 10, $row['kode_booking'], 1);

    $pdf->Cell(40, 10, substr($row['nama'], 0, 20), 1);

    $pdf->Cell(55, 10, substr($row['nama_tempat'], 0, 30), 1);

    $pdf->Cell(
        25,
        10,
        date('d-m-Y', strtotime($row['hari'])),
        1
    );

    $pdf->Cell(
        20,
        10,
        substr($row['jam_mulai'], 0, 5),
        1
    );

    $pdf->Cell(
        20,
        10,
        substr($row['jam_selesai'], 0, 5),
        1
    );

    $pdf->Cell(
        28,
        10,
        strtoupper($row['status']),
        1
    );

    $pdf->Cell(
        25,
        10,
        ucfirst($row['sumber_reservasi']),
        1
    );

    $pdf->Ln();
}


// ==========================================
// FOOTER
// ==========================================

$pdf->Ln(10);

$pdf->SetFont('Arial', 'I', 9);

$pdf->Cell(

    0,
    8,

    'Dicetak pada: ' . date('d-m-Y H:i'),

    0,
    1,
    'R'

);


// ==========================================
// OUTPUT
// ==========================================

$pdf->Output('I', 'laporan_reservasi.pdf');
