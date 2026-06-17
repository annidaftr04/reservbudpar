<?php
include '../db.php';
session_start();

// Pastikan admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = trim($_POST['id']);
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $lokasi = trim($_POST['lokasi']);
    $image_path = null;

    // Ambil path gambar utama yang ada sekarang
    $stmt_fetch = $conn->prepare("SELECT image FROM places WHERE id = ?");
    $stmt_fetch->bind_param("i", $id);
    $stmt_fetch->execute();
    $result_fetch = $stmt_fetch->get_result();
    $current_place = $result_fetch->fetch_assoc();
    $stmt_fetch->close();
    $image_path = $current_place['image'];

    // Proses Unggah Gambar Utama Baru
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($fileExtension, $allowedExtensions)) {
            $uploadDir = __DIR__ . '/../assets/img/';
            $imageName = uniqid('img_', true) . '.' . $fileExtension;
            $uploadedImagePath = $uploadDir . $imageName;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadedImagePath)) {
                // Hapus gambar utama lama jika ada
                if ($image_path && file_exists(__DIR__ . '/../' . $image_path)) {
                    unlink(__DIR__ . '/../' . $image_path);
                }
                $image_path = 'assets/img/' . $imageName;
            }
        }
    }

    // Perbarui data utama di tabel places
    $stmt_update_place = $conn->prepare("UPDATE places SET name = ?, description = ?, image = ?, lokasi = ? WHERE id = ?");
    $stmt_update_place->bind_param("ssssi", $name, $description, $image_path, $lokasi, $id);
    $stmt_update_place->execute();
    $stmt_update_place->close();

    // Proses Unggah Galeri Foto Baru
    if (isset($_FILES['new_images']) && count($_FILES['new_images']['name']) > 0) {
        $uploadDir = __DIR__ . '/../assets/img/';
        
        foreach ($_FILES['new_images']['tmp_name'] as $key => $tmp_name) {
            $file_name = $_FILES['new_images']['name'][$key];
            $file_tmp = $_FILES['new_images']['tmp_name'][$key];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($file_ext, $allowed_extensions)) {
                $new_file_name = uniqid('gallery_', true) . '.' . $file_ext;
                $new_file_path = $uploadDir . $new_file_name;

                if (move_uploaded_file($file_tmp, $new_file_path)) {
                    // Simpan jalur file ke tabel place_images
                    $stmt_insert = $conn->prepare("INSERT INTO place_images (place_id, image_path) VALUES (?, ?)");
                    $image_db_path = 'assets/img/' . $new_file_name;
                    $stmt_insert->bind_param("is", $id, $image_db_path);
                    $stmt_insert->execute();
                    $stmt_insert->close();
                }
            }
        }
    }

    header('Location: kelola_tempat.php');
    exit;
}
?>