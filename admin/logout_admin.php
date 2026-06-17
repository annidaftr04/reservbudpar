<?php
session_start();

// Hanya menghapus session admin
if (isset($_SESSION['admin_id'])) {
    unset($_SESSION['admin_id']); // Hapus session admin_id
    unset($_SESSION['admin_username']); // Jika ada session lain untuk admin
    session_write_close(); // Simpan perubahan sesi
}

// Redirect ke halaman login admin
header('Location: login_admin.php');
exit;
?>
