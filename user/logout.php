<?php
session_start();

// Hapus hanya session user
if (isset($_SESSION['user_id'])) {
    unset($_SESSION['user_id']); // Hapus session user_id
    unset($_SESSION['user_username']); // Jika ada session username user
}

// Redirect ke halaman login user
header('Location: index.php');
exit;
?>
