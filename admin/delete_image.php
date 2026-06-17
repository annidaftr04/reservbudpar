<?php
session_start();
header('Content-Type: text/plain');
include '../db.php';

// Pastikan admin sudah login
if (!isset($_SESSION['admin_id'])) {
    echo "Unauthorized";
    exit;
}

// Pastikan request method adalah POST dan id gambar tersedia
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id']) || !isset($_POST['place_id'])) {
    echo "Invalid request";
    exit;
}

$image_id = intval($_POST['id']);
$place_id = intval($_POST['place_id']);

// Ambil path gambar dari database
$stmt = $conn->prepare("SELECT image_path FROM place_images WHERE id = ? AND place_id = ?");
$stmt->bind_param("ii", $image_id, $place_id);
$stmt->execute();
$result = $stmt->get_result();
$image = $result->fetch_assoc();
$stmt->close();

if ($image) {
    // Hapus file fisik dari server
    $file_path = '../' . $image['image_path'];
    if (file_exists($file_path)) {
        if (unlink($file_path)) {
            // Hapus entri dari database
            $stmt_delete = $conn->prepare("DELETE FROM place_images WHERE id = ?");
            $stmt_delete->bind_param("i", $image_id);
            if ($stmt_delete->execute()) {
                echo "success";
            } else {
                echo "Gagal menghapus entri dari database.";
            }
            $stmt_delete->close();
        } else {
            echo "Gagal menghapus file fisik. Cek izin folder.";
        }
    } else {
        // Jika file tidak ditemukan, hapus saja dari DB
        $stmt_delete = $conn->prepare("DELETE FROM place_images WHERE id = ?");
        $stmt_delete->bind_param("i", $image_id);
        if ($stmt_delete->execute()) {
             echo "success";
        } else {
            echo "Gagal menghapus entri database karena file tidak ditemukan.";
        }
        $stmt_delete->close();
    }
} else {
    echo "Gambar tidak ditemukan di database.";
}

$conn->close();
?>