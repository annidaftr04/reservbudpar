<?php
session_start();
include '../db.php';

// Cek login admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

// Ambil ID dari URL
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("ID reservasi tidak valid.");
}

// Query detail dengan JOIN ke tabel places dan sub_places agar data akurat
$query = "SELECT r.*, p.name as nama_tempat, sp.nama_subtempat 
          FROM reservations r
          LEFT JOIN places p ON r.place_id = p.id
          LEFT JOIN sub_places sp ON r.sub_place_id = sp.id
          WHERE r.id = $id";

$result = $conn->query($query);

if ($result->num_rows == 0) {
    die("Reservasi tidak ditemukan.");
}

$reservation = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Reservasi | Admin Panel</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon">

    <style>
        :root {
            --primary: #4361ee;
            --navy: #1e293b;
            --light-bg: #f8f9fc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--light-bg);
            color: #334155;
            padding: 40px 0;
        }

        .container { max-width: 1000px; }

        .detail-card {
            background: #fff;
            border: none;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .card-header-gradient {
            background: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            padding: 40px;
            color: white;
            text-align: center;
        }

        .booking-code {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 20px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.9rem;
            display: inline-block;
            margin-top: 10px;
        }

        .section-title {
            font-weight: 800;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title::after {
            content: ""; flex: 1; height: 1px; background: #e2e8f0;
        }

        .info-group { margin-bottom: 25px; }

        .info-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .info-value { font-weight: 600; color: #1e293b; font-size: 1rem; }

        .status-pill {
            padding: 8px 20px; border-radius: 50px;
            font-weight: 700; font-size: 0.8rem; text-transform: uppercase;
        }

        .status-pending { background: #fef3c7; color: #d97706; }
        .status-disetujui { background: #dcfce7; color: #16a34a; }
        .status-ditolak { background: #fee2e2; color: #dc2626; }
        .status-selesai { background: #e0e7ff; color: #4338ca; }

        .file-link {
            display: flex; align-items: center; gap: 10px;
            background: #f1f5f9; padding: 12px 15px; border-radius: 12px;
            color: var(--primary); text-decoration: none; font-weight: 600;
            font-size: 0.9rem; transition: 0.2s;
        }

        .file-link:hover { background: #e2e8f0; color: #3f37c9; }

        .admin-note {
            background: #f8fafc; border-left: 4px solid #cbd5e1;
            padding: 20px; border-radius: 0 12px 12px 0; font-style: italic;
        }

        .btn-back {
            background: #fff; color: #64748b; border: 1px solid #e2e8f0;
            padding: 12px 30px; border-radius: 12px; font-weight: 700; transition: 0.3s;
        }

        .btn-back:hover { background: #f1f5f9; color: #1e293b; }
    </style>
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
                                    class="file-link"
                                >
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
                        <div class="info-value"><i class="far fa-clock me-1"></i> <?= substr($reservation['jam_mulai'],0,5); ?> - <?= substr($reservation['jam_selesai'],0,5); ?></div>
                    </div>

                    <div class="info-group">
                        <div class="info-label">Keterangan Kegiatan</div>
                        <div class="info-value small fw-normal"><?= nl2br(htmlspecialchars($reservation['keterangan'])); ?></div>
                    </div>

                    <div class="mt-5">
                        <h6 class="section-title"><i class="fas fa-info-circle"></i> Status & Catatan</h6>
                        <div class="mb-3">
                            <span class="status-pill status-<?= $reservation['status']; ?>">
                                <?= strtoupper($reservation['status']); ?>
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