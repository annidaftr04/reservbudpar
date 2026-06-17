<?php
session_start();
include '../db.php';

// Proteksi akses user
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "ID reservasi tidak ditemukan.";
    exit;
}

$id = intval($_GET['id']);

$query = "
    SELECT 
        reservations.*,

        places.name AS nama_tempat,

        sub_places.nama_subtempat

    FROM reservations

    LEFT JOIN places 
        ON reservations.place_id = places.id

    LEFT JOIN sub_places 
        ON reservations.sub_place_id = sub_places.id

    WHERE reservations.id = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Reservasi tidak ditemukan.";
    exit;
}

$reservation = $result->fetch_assoc();

// Pemetaan status
$statusLower = strtolower($reservation['status'] ?? '');
$statusMap = [
    'disetujui' => ['bg' => '#d1fae5', 'text' => '#065f46', 'label' => 'DISETUJUI'],
    'ditolak'   => ['bg' => '#fee2e2', 'text' => '#991b1b', 'label' => 'DITOLAK'],
    'pending'   => ['bg' => '#fef3c7', 'text' => '#92400e', 'label' => 'PENDING'],
    'selesai'   => ['bg' => '#e0f2fe', 'text' => '#075985', 'label' => 'SELESAI']
];
$statusStyle = $statusMap[$statusLower] ?? ['bg' => '#f1f5f9', 'text' => '#475569', 'label' => strtoupper($statusLower)];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Detail Reservasi | <?= htmlspecialchars($reservation['kode_booking']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #002D62;
            --accent: #FF5733;
            --text-main: #1e293b;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 50px 0;
        }

        .luxury-card {
            background: #ffffff;
            border-radius: 35px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            max-width: 950px;
            margin: auto;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        .header-section {
            background: var(--primary);
            padding: 35px 50px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .booking-id {
            font-size: 1.5rem;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .status-pill {
            background: <?= $statusStyle['bg'] ?>;
            color: <?= $statusStyle['text'] ?>;
            padding: 8px 20px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 0.7rem;
            letter-spacing: 1.5px;
        }

        .content-padding {
            padding: 50px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--primary);
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .info-group {
            margin-bottom: 25px;
        }

        .label-tiny {
            font-size: 0.65rem;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 1px;
            margin-bottom: 6px;
            display: block;
        }

        .value-bold {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1.5;
        }

        .doc-item {
            background: #f8fafc;
            border: 1.5px solid #f1f5f9;
            border-radius: 20px;
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none !important;
            transition: 0.3s;
            margin-bottom: 15px;
        }

        .doc-item:hover {
            background: #ffffff;
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05);
        }

        .icon-box {
            width: 45px;
            height: 45px;
            background: #ffffff;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .desc-area {
            background: #f8fafc;
            border-radius: 20px;
            padding: 20px;
            font-size: 0.9rem;
            color: var(--text-main);
            border: 1px dashed #e2e8f0;
            line-height: 1.7;
        }

        .footer-action {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-back-home {
            background: #f1f5f9;
            color: var(--primary);
            padding: 12px 25px;
            border-radius: 15px;
            font-weight: 700;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-back-home:hover {
            background: var(--primary);
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="luxury-card">
            <div class="header-section">
                <div>
                    <span class="label-tiny" style="color: rgba(255,255,255,0.7)">KODE BOOKING</span>
                    <div class="booking-id"><?= htmlspecialchars($reservation['kode_booking']); ?></div>
                </div>
                <div class="status-pill"><?= $statusStyle['label'] ?></div>
            </div>

            <div class="content-padding">
                <div class="row g-5">
                    <div class="col-lg-7">
                        <div class="section-title"><i class="fa-solid fa-circle-info"></i> Detail Pelaksanaan</div>

                        <div class="row">
                            <div class="col-12 info-group">
                                <span class="label-tiny">Nama Lengkap Pemohon</span>
                                <div class="value-bold" style="font-size: 1.2rem; color: var(--primary);"><?= htmlspecialchars($reservation['nama']); ?></div>
                            </div>
                            <div class="col-md-6 info-group">
                                <span class="label-tiny">WhatsApp / Telp</span>
                                <div class="value-bold"><i class="fa-brands fa-whatsapp me-1 text-success"></i> <?= htmlspecialchars($reservation['no_telepon']); ?></div>
                            </div>
                            <div class="col-md-6 info-group">
                                <span class="label-tiny">Alamat Email</span>
                                <div class="value-bold"><?= htmlspecialchars($reservation['email']); ?></div>
                            </div>
                            <div class="col-12 info-group">
                                <span class="label-tiny">Lokasi & Sub-Tempat</span>
                                <div class="value-bold">
                                    <i class="fa-solid fa-location-dot me-1 text-danger"></i>
                                    <?= htmlspecialchars($reservation['nama_tempat']); ?>
                                    <?= !empty($reservation['nama_subtempat'])
                                        ? '<span class="text-muted mx-1">/</span> ' . htmlspecialchars($reservation['nama_subtempat'])
                                        : '';
                                    ?>
                                </div>
                            </div>

                            <div class="col-md-6 info-group">
                                <span class="label-tiny">Tanggal Mulai</span>
                                <div class="value-bold"><i class="fa-regular fa-calendar me-1 text-primary"></i> <?= date('d M Y', strtotime($reservation['hari'])); ?></div>
                            </div>
                            <div class="col-md-6 info-group">
                                <span class="label-tiny">Tanggal Selesai</span>
                                <div class="value-bold">
                                    <i class="fa-regular fa-calendar-check me-1 text-accent"></i>
                                    <?= $reservation['tanggal_selesai'] ? date('d M Y', strtotime($reservation['tanggal_selesai'])) : date('d M Y', strtotime($reservation['hari'])); ?>
                                </div>
                            </div>

                            <div class="col-12 info-group">
                                <span class="label-tiny">Waktu Pelaksanaan</span>
                                <div class="value-bold"><i class="fa-regular fa-clock me-1 text-muted"></i> <?= substr($reservation['jam_mulai'], 0, 5); ?> - <?= substr($reservation['jam_selesai'], 0, 5); ?> WIB</div>
                            </div>
                            <div class="col-12">
                                <span class="label-tiny">Keterangan Acara</span>
                                <div class="desc-area"><?= nl2br(htmlspecialchars($reservation['keterangan'])); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="section-title"><i class="fa-solid fa-file-shield"></i> Dokumen Pendukung</div>

                        <span class="label-tiny">Surat Permohonan Izin</span>
                        <?php if ($reservation['file_upload']): ?>
                            <a href="../uploads/<?= htmlspecialchars($reservation['file_upload']); ?>" target="_blank" class="doc-item">
                                <div class="icon-box"><i class="fa-solid fa-file-pdf"></i></div>
                                <div>
                                    <div class="value-bold small">Surat_Permohonan.pdf</div><small class="text-primary fw-bold" style="font-size: 0.7rem;">LIHAT DOKUMEN</small>
                                </div>
                            </a>
                        <?php else: ?>
                            <p class="text-muted small italic mb-3">Dokumen permohonan tidak tersedia.</p>
                        <?php endif; ?>

                        <span class="label-tiny mt-4 d-block">Identitas Diri (KTP/SIM)</span>
                        <?php if ($reservation['ktp_upload']): ?>
                            <a href="../uploads/ktp/<?= htmlspecialchars($reservation['ktp_upload']); ?>" target="_blank" class="doc-item">
                                <div class="icon-box"><i class="fa-solid fa-id-card"></i></div>
                                <div>
                                    <div class="value-bold small">KTP_Pemohon.jpg</div><small class="text-primary fw-bold" style="font-size: 0.7rem;">LIHAT IDENTITAS</small>
                                </div>
                            </a>
                        <?php else: ?>
                            <p class="text-muted small italic mb-3">Dokumen KTP tidak tersedia.</p>
                        <?php endif; ?>

                        <?php if ($reservation['nama_tempat'] === 'Gedung Seni Budaya'): ?>
                            <span class="label-tiny mt-4 d-block">Surat Keterangan Kelurahan</span>
                            <?php if ($reservation['surat_kelurahan_upload']): ?>
                                <a href="../uploads/kelurahan/<?= htmlspecialchars($reservation['surat_kelurahan_upload']); ?>" target="_blank" class="doc-item" style="border-left: 4px solid var(--accent);">
                                    <div class="icon-box text-warning"><i class="fa-solid fa-building-circle-check"></i></div>
                                    <div>
                                        <div class="value-bold small">Surat_Kelurahan.pdf</div><small class="text-warning fw-bold" style="font-size: 0.7rem;">DOKUMEN WAJIB GSB</small>
                                    </div>
                                </a>
                            <?php else: ?>
                                <div class="alert alert-warning py-2 small fw-bold mt-2"><i class="fas fa-exclamation-triangle me-1"></i> Belum Mengunggah Surat Kelurahan</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="footer-action">
                    <a href="dashboard.php" class="btn-back-home"><i class="fa-solid fa-arrow-left-long me-2"></i> Kembali ke Dashboard</a>
                    <div class="text-end">
                        <div class="label-tiny">Dicetak Pada</div>
                        <div class="value-bold small text-muted"><?= date('d/m/Y H:i'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>