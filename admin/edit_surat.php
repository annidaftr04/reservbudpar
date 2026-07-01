<?php
include '../db.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: login_admin.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: kelola_surat.php");
    exit;
}

$id = (int) $_GET['id'];

// Simpan Data
if (isset($_POST['simpan'])) {

    $nomor_permohonan = trim($_POST['nomor_surat_permohonan']);
    $nomor_kelurahan  = trim($_POST['nomor_surat_kelurahan']);

    $stmt = $conn->prepare("
        UPDATE reservations
        SET
            nomor_surat_permohonan = ?,
            nomor_surat_kelurahan = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "ssi",
        $nomor_permohonan,
        $nomor_kelurahan,
        $id
    );

    if ($stmt->execute()) {

        echo "<script>
        alert('Nomor surat berhasil disimpan');
        window.location='kelola_surat.php';
        </script>";

        exit;
    }
}

// Ambil Data
$query = $conn->query("
SELECT
r.*,
p.name AS nama_tempat
FROM reservations r
LEFT JOIN places p
ON r.place_id=p.id
WHERE r.id='$id'
");

if ($query->num_rows == 0) {
    die("Data tidak ditemukan");
}

$data = $query->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Surat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="icon" href="../assets/img/logotng.png">
    <style>
        body {
            background: #f4f6fb;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .card {
            border: none;
            border-radius: 18px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .05);
        }

        .info-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 3px;
        }

        .info-value {
            font-weight: 700;
            font-size: 16px;
            margin-bottom: 18px;
        }

        .form-control {
            border-radius: 10px;
        }

        .btn {
            border-radius: 10px;
        }

        .doc-box {
            border: 1px solid #e5e7eb;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-file-signature"></i>
                            Edit Administrasi Surat
                        </h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">
                                    Kode Booking
                                </div>
                                <div class="info-value">
                                    <?= $data['kode_booking']; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">
                                    Nama Pemohon
                                </div>
                                <div class="info-value">
                                    <?= htmlspecialchars($data['nama']); ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">
                                    Tempat
                                </div>
                                <div class="info-value">
                                    <?= $data['nama_tempat']; ?>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="info-label">
                                    Status
                                </div>
                                <div class="info-value">
                                    <?= ucfirst($data['status']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <form method="POST">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                Administrasi Surat
                            </h5>
                        </div>

                        <div class="card-body">
                            <div class="row">
                                <!-- Nomor Surat Permohonan -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        Nomor Surat Permohonan
                                    </label>
                                    <input
                                        type="text"
                                        name="nomor_surat_permohonan"
                                        class="form-control"
                                        placeholder="Masukkan nomor surat permohonan..."
                                        value="<?= htmlspecialchars($data['nomor_surat_permohonan'] ?? ''); ?>">
                                </div>

                                <!-- Nomor Surat Kelurahan -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-semibold">
                                        Nomor Surat Kelurahan
                                    </label>
                                    <input
                                        type="text"
                                        name="nomor_surat_kelurahan"
                                        class="form-control"
                                        placeholder="Masukkan nomor surat kelurahan..."
                                        value="<?= htmlspecialchars($data['nomor_surat_kelurahan'] ?? ''); ?>">
                                </div>
                            </div>
                            <hr class="my-4">
                            <h5 class="mb-4">
                                <i class="fas fa-folder-open text-primary"></i>
                                Dokumen Pemohon
                            </h5>
                            <div class="doc-box">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <strong>
                                            <i class="fas fa-file-pdf text-danger"></i>
                                            Surat Permohonan
                                        </strong>
                                    </div>

                                    <div class="col-md-8 text-end">
                                        <?php if (!empty($data['file_upload'])) : ?>
                                            <a
                                                href="../uploads/<?= $data['file_upload']; ?>"
                                                target="_blank"
                                                class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                                Preview
                                            </a>

                                            <a
                                                href="../uploads/<?= $data['file_upload']; ?>"
                                                download
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-download"></i>
                                                Download
                                            </a>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">
                                                Tidak Ada File
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-box">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <strong>
                                            <i class="fas fa-file-pdf text-danger"></i>
                                            Surat Kelurahan
                                        </strong>
                                    </div>

                                    <div class="col-md-8 text-end">
                                        <?php if (!empty($data['surat_kelurahan_upload'])) : ?>
                                            <a
                                                href="../uploads/kelurahan/<?= $data['surat_kelurahan_upload']; ?>"
                                                target="_blank"
                                                class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                                Preview
                                            </a>

                                            <a
                                                href="../uploads/kelurahan/<?= $data['surat_kelurahan_upload']; ?>"
                                                download
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-download"></i>
                                                Download
                                            </a>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">
                                                Tidak Ada File
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-box">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <strong>
                                            <i class="fas fa-id-card text-primary"></i>
                                            KTP
                                        </strong>
                                    </div>

                                    <div class="col-md-8 text-end">
                                        <?php if (!empty($data['ktp_upload'])) : ?>
                                            <a
                                                href="../uploads/ktp/<?= $data['ktp_upload']; ?>"
                                                target="_blank"
                                                class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i>
                                                Preview
                                            </a>

                                            <a
                                                href="../uploads/ktp/<?= $data['ktp_upload']; ?>"
                                                download
                                                class="btn btn-success btn-sm">
                                                <i class="fas fa-download"></i>
                                                Download
                                            </a>
                                        <?php else : ?>
                                            <span class="badge bg-secondary">
                                                Tidak Ada File
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end mt-4">
                                <a
                                    href="kelola_surat.php"
                                    class="btn btn-secondary me-2">
                                    <i class="fas fa-arrow-left"></i>
                                    Kembali
                                </a>

                                <button
                                    type="submit"
                                    name="simpan"
                                    class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>