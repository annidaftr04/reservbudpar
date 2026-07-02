<?php
include 'includes/auth.php';
include '../db.php';
include 'includes/auto_status.php';

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ID reservasi tidak valid.");
}

// Query detail dengan JOIN ke tabel places dan sub_places agar data akurat
$stmt = $conn->prepare("
    SELECT
        r.*,
        p.name AS nama_tempat,
        sp.nama_subtempat
    FROM reservations r
    LEFT JOIN places p
        ON r.place_id = p.id
    LEFT JOIN sub_places sp
        ON r.sub_place_id = sp.id
    WHERE r.id = ?
");

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Reservasi tidak ditemukan.");
}

$reservation = $result->fetch_assoc();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <title>Detail Reservasi | Admin Panel</title>

    <?php include 'includes/header.php'; ?>

    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/view_reserv.css">
</head>

<body>

    <div class="container">
        <div class="detail-card">
            <div class="card-header-gradient">
                <h2 class="fw-800 mb-0">Detail Reservasi</h2>
                <div class="booking-code">
                    <i class="fas fa-ticket-alt me-2"></i> <?= htmlspecialchars($reservation['kode_booking']); ?>
                </div>
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="row g-5">
                    <div class="col-md-6">
                        <h6 class="section-title"><i class="fas fa-user"></i> Informasi Pemohon</h6>

                        <div class="info-group">
                            <div class="info-label">Nama Lengkap</div>
                            <div class="info-value"><?= htmlspecialchars($reservation['nama']); ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Nomor Telepon / WA</div>
                            <div class="info-value"><?= htmlspecialchars($reservation['no_telepon']); ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Alamat Email</div>
                            <div class="info-value"><?= htmlspecialchars($reservation['email'] ?? '-'); ?></div>
                        </div>

                        <div class="mt-5">
                            <h6 class="section-title"><i class="fas fa-file-alt"></i> Dokumen Lampiran</h6>
                            <div class="d-grid gap-2">
                                <?php if ($reservation['file_upload']): ?>
                                    <a href="../uploads/<?= $reservation['file_upload']; ?>" target="_blank" class="file-link">
                                        <i class="fas fa-file-pdf text-danger"></i> Proposal Kegiatan
                                    </a>
                                <?php endif; ?>
                                <?php if ($reservation['ktp_upload']): ?>
                                    <a href="../uploads/ktp/<?= $reservation['ktp_upload']; ?>" target="_blank" class="file-link">
                                        <i class="fas fa-id-card text-primary"></i> Identitas (KTP)
                                    </a>
                                <?php endif; ?>
                                <?php if (
                                    !empty($reservation['surat_kelurahan_upload']) &&
                                    stripos($reservation['nama_tempat'], 'Gedung Seni Budaya') !== false
                                ): ?>
                                    <a
                                        href="../uploads/kelurahan/<?= $reservation['surat_kelurahan_upload']; ?>"
                                        target="_blank"
                                        class="file-link">
                                        <i class="fas fa-envelope-open-text text-success"></i>
                                        Surat Keterangan Kelurahan
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h6 class="section-title"><i class="fas fa-map-marker-alt"></i> Detail Lokasi & Waktu</h6>

                        <div class="info-group">
                            <div class="info-label">Lokasi Utama</div>
                            <div class="info-value"><?= htmlspecialchars($reservation['nama_tempat'] ?? 'Tempat tidak ditemukan'); ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Area / Bagian</div>
                            <div class="info-value text-primary"><?= htmlspecialchars($reservation['nama_subtempat'] ?? 'Seluruh Area'); ?></div>
                        </div>

                        <div class="row info-group">
                            <div class="col-6 border-end">
                                <div class="info-label text-success">Tanggal Mulai</div>
                                <div class="info-value"><?= date('d F Y', strtotime($reservation['hari'])); ?></div>
                            </div>
                            <div class="col-6">
                                <div class="info-label text-danger">Tanggal Selesai</div>
                                <div class="info-value">
                                    <?= $reservation['tanggal_selesai'] ? date('d F Y', strtotime($reservation['tanggal_selesai'])) : '<span class="text-muted small">Sama dengan tgl mulai</span>'; ?>
                                </div>
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Rentang Waktu (Jam)</div>
                            <div class="info-value"><i class="far fa-clock me-1"></i> <?= substr($reservation['jam_mulai'], 0, 5); ?> - <?= substr($reservation['jam_selesai'], 0, 5); ?></div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Keterangan Kegiatan</div>
                            <div class="info-value small fw-normal"><?= nl2br(htmlspecialchars($reservation['keterangan'])); ?></div>
                        </div>

                        <div class="mt-5">
                            <h6 class="section-title"><i class="fas fa-info-circle"></i> Status & Catatan</h6>
                            <div class="mb-3">
                                <?php

                                $status = strtolower($reservation['status']);

                                $statusLabel = [
                                    'pending'    => 'Pending',
                                    'disetujui'  => 'Disetujui',
                                    'ditolak'    => 'Ditolak',
                                    'selesai'    => 'Selesai'
                                ];

                                ?>

                                <span class="status-pill status-<?= $status; ?>">
                                    <?= $statusLabel[$status] ?? ucfirst($status); ?>
                                </span>
                            </div>
                            <div class="admin-note">
                                <div class="small fw-bold text-muted mb-1 text-uppercase">Catatan Admin:</div>
                                <?= htmlspecialchars($reservation['catatan'] ?: 'Belum ada catatan dari administrator.'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer bg-white border-0 p-4 p-md-5 text-center">
                <a href="kelola_reserv.php" class="btn btn-back">
                    <i class="fas fa-arrow-left me-2"></i> Kembali ke Daftar
                </a>
                <a href="edit_reserv.php?id=<?= $id; ?>" class="btn btn-primary px-4 py-2 rounded-3 ms-2" style="font-weight: 700;">
                    <i class="fas fa-edit me-2"></i> Edit / Proses
                </a>
            </div>
        </div>
    </div>

</body>

</html>