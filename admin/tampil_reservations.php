<?php
include '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

try {
    // Query untuk mengambil data dari tabel reservations
    $query = "SELECT tempat, nama, hari, keterangan FROM reservations ORDER BY id";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();

    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'id' => $row['tempat'],
            'title' => $row['nama'] . ' - ' . $row['tempat'],
            'start' => $row['hari'] // Format tanggal harus ISO 8601 (YYYY-MM-DD)
        ];
    }

    // Mengembalikan data dalam format JSON
    header('Content-Type: application/json');
    echo json_encode($events);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
?>
