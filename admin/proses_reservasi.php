<?php
session_start();
include '../db.php';

// Cek apakah form telah disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data yang dikirimkan dari form
    $nama = $_POST['nama'];
    $no_telepon = $_POST['no_telepon'];
    $tempat = $_POST['tempat'];
    $hari = $_POST['hari'];
    $jam_mulai = $_POST['jam_mulai'];
    $jam_selesai = $_POST['jam_selesai'];
    $keterangan = $_POST['keterangan'];
    $status = 'pending'; // Status default saat pertama kali dibuat

    // Ambil kode booking terakhir dari database
    $query = "SELECT MAX(CAST(SUBSTRING(kode_booking, 5) AS UNSIGNED)) AS last_code FROM reservations";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $last_code = $row['last_code'] ? $row['last_code'] : 0;

    // Generate kode booking berikutnya
    $new_code = 'RES-' . str_pad($last_code + 1, 4, '0', STR_PAD_LEFT);

    // Simpan data reservasi ke database
    $query = "INSERT INTO reservations (kode_booking, nama, no_telepon, tempat, hari, jam_mulai, jam_selesai, keterangan, status) 
              VALUES ('$new_code', '$nama', '$no_telepon', '$tempat', '$hari', '$jam_mulai', '$jam_selesai', '$keterangan', '$status')";

    if ($conn->query($query)) {
        // Jika berhasil, alihkan ke dashboard
        echo "<script>
                alert('Reservasi Anda telah berhasil diajukan!');
                window.location.href = 'dashboard.php';
              </script>";
    } else {
        echo "<script>
                alert('Terjadi kesalahan. Mohon coba lagi!');
              </script>";
    }
}
?>
