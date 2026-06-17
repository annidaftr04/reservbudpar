<?php
include '../db.php';
session_start();

// --- LOGIKA DATA ---
if (isset($_GET['id'])) {
    $place_id = intval($_GET['id']);

    // Ambil data tempat berdasarkan ID
    $sql = "SELECT * FROM places WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $place_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $place = $result->fetch_assoc();

    if (!$place) {
        echo "Tempat tidak ditemukan.";
        exit;
    }

    // Ambil galeri foto dari tabel place_images
    $sql_gallery = "SELECT image_path FROM place_images WHERE place_id = ?";
    $stmt_gallery = $conn->prepare($sql_gallery);
    $stmt_gallery->bind_param("i", $place_id);
    $stmt_gallery->execute();
    $result_gallery = $stmt_gallery->get_result();

    $gallery_images = [];
    while ($row_gallery = $result_gallery->fetch_assoc()) {
        $gallery_images[] = '../' . htmlspecialchars($row_gallery['image_path']);
    }

    // Fallback jika galeri kosong
    if (empty($gallery_images)) {
        $gallery_images[] = '../' . htmlspecialchars($place['image']);
    }

    // ==============================
    // AMBIL SUB TEMPAT
    // ==============================
    $sqlSub = "SELECT * FROM sub_places WHERE place_id = ?";
    $stmtSub = $conn->prepare($sqlSub);
    $stmtSub->bind_param("i", $place_id);
    $stmtSub->execute();
    $resultSub = $stmtSub->get_result();

    $sub_places = [];
    while ($sub = $resultSub->fetch_assoc()) {
        $sub_places[] = $sub;
    }

    // ==============================
    // AMBIL TANGGAL BOOKING
    // ==============================
    $booked_dates = [];
    if (!empty($sub_places)) {
        foreach ($sub_places as $sub) {
            $sub_id = $sub['id'];
            $stmtBook = $conn->prepare("
                SELECT hari
                FROM reservations
                WHERE place_id = ?
                AND sub_place_id = ?
                AND status = 'disetujui'
            ");
            $stmtBook->bind_param("ii", $place_id, $sub_id);
            $stmtBook->execute();
            $resBook = $stmtBook->get_result();

            while ($r = $resBook->fetch_assoc()) {
                $booked_dates[$sub_id][] = $r['hari'];
            }
        }
    } else {
        $stmtBook = $conn->prepare("
            SELECT hari
            FROM reservations
            WHERE place_id = ?
            AND status = 'disetujui'
        ");
        $stmtBook->bind_param("i", $place_id);
        $stmtBook->execute();
        $resBook = $stmtBook->get_result();

        while ($r = $resBook->fetch_assoc()) {
            $booked_dates['main'][] = $r['hari'];
        }
    }
} else {
    echo "ID tidak ditemukan.";
    exit;
}

// --- FUNGSI KALENDER MODEREN ---
function build_calendar($month, $year, $booked_dates, $place_id, $sub_place_id = null)
{
    $daysOfWeek = ['S', 'S', 'R', 'K', 'J', 'S', 'M'];
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    $numberDays = date('t', $firstDayOfMonth);
    $dateComponents = getdate($firstDayOfMonth);
    $dayOfWeek = $dateComponents['wday'] == 0 ? 7 : $dateComponents['wday'];

    $calendar = "<div class='calendar-container-modern animate__animated animate__fadeIn'>";
    $calendar .= "<table class='table table-borderless text-center mb-0'>";
    $calendar .= "<thead><tr>";
    foreach ($daysOfWeek as $day) {
        $calendar .= "<th class='text-muted small fw-bold'>$day</th>";
    }
    $calendar .= "</tr></thead><tbody><tr>";

    if ($dayOfWeek > 1) {
        for ($k = 1; $k < $dayOfWeek; $k++) {
            $calendar .= "<td></td>";
        }
    }

    $currentDay = 1;
    while ($currentDay <= $numberDays) {
        if ($dayOfWeek > 7) {
            $dayOfWeek = 1;
            $calendar .= "</tr><tr>";
        }
        $date = "$year-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-" . str_pad($currentDay, 2, "0", STR_PAD_LEFT);
        $isBooked = is_array($booked_dates) && in_array($date, $booked_dates);
        $today = date('Y-m-d');
        $isPast = $date < $today;

        if ($isBooked) {
            $class = 'booked';
        } elseif ($isPast) {
            $class = 'past';
        } else {
            $class = 'available';
        }

        if (!$isBooked && !$isPast) {
            $url = "reservasi.php?place_id=$place_id&date=$date";
            if ($sub_place_id != null) {
                $url .= "&sub_place_id=$sub_place_id";
            }
            $click = "onclick=\"window.location.href='$url'\"";
        } else {
            $click = "";
        }

        $calendar .= "<td><div class='day-cell $class' $click>$currentDay</div></td>";
        $currentDay++;
        $dayOfWeek++;
    }
    $calendar .= "</tr></tbody></table></div>";
    return $calendar;
}

// Ambil bulan & tahun dari URL
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

$bulanIndonesia = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eksplorasi - <?= htmlspecialchars($place['name']); ?></title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon" />

    <style>
        :root {
            --primary: #0b2a59;
            --accent: #ff8c42;
            --bg-light: #f8f9fc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #cbd5e1 100%);
            min-height: 100vh;
            padding: 60px 0;
            color: #1e293b;
        }

        .luxury-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 40px;
            box-shadow: 0 40px 80px rgba(11, 42, 89, 0.08);
            padding: 35px;
        }

        .hero-image-container {
            position: relative;
            border-radius: 28px;
            overflow: hidden;
            height: 460px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.12);
        }

        .hero-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: opacity 0.3s ease-in-out;
        }

        .floating-badge {
            position: absolute;
            top: 25px;
            right: 25px;
            background: rgba(11, 42, 89, 0.85);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            padding: 12px 22px;
            border-radius: 50px;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            z-index: 5;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .gallery-nav {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 10px;
            background: rgba(255, 255, 255, 0.2);
            padding: 8px;
            border-radius: 20px;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            z-index: 5;
        }

        .gallery-nav img {
            width: 60px;
            height: 42px;
            border-radius: 10px;
            cursor: pointer;
            object-fit: cover;
            transition: 0.2s;
            border: 2px solid transparent;
        }

        .gallery-nav img:hover,
        .gallery-nav img.active {
            border-color: white;
            transform: translateY(-2px);
        }

        .desc-box {
            background: rgba(11, 42, 89, 0.03);
            border-left: 5px solid var(--primary);
            padding: 24px;
            border-radius: 18px;
            margin-top: 25px;
        }

        #roomDesc {
            transition: opacity 0.3s ease-in-out;
            line-height: 1.7;
        }

        .day-cell {
            height: 40px;
            width: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .day-cell.available {
            background: #f1f5f9;
            color: var(--primary);
        }

        .day-cell.available:hover {
            background: var(--primary);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 8px 15px rgba(11, 42, 89, 0.2);
        }

        .day-cell.booked {
            background: #fee2e2;
            color: #ef4444;
            cursor: not-allowed;
            text-decoration: line-through;
        }

        .day-cell.past {
            background: #f1f5f9;
            color: #cbd5e1;
            cursor: not-allowed;
            opacity: 0.5;
        }

        .nav-pills-luxury {
            background: #f1f5f9;
            padding: 6px;
            border-radius: 16px;
        }

        .nav-pills-luxury .nav-link {
            border-radius: 12px;
            font-weight: 700;
            color: #64748b;
            font-size: 0.9rem;
            padding: 10px;
        }

        .nav-pills-luxury .nav-link.active {
            background: white;
            color: var(--primary);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .btn-reserve {
            background: linear-gradient(135deg, var(--primary), #1e40af);
            color: white;
            border: none;
            padding: 16px;
            border-radius: 16px;
            width: 100%;
            font-weight: 800;
            letter-spacing: 0.5px;
            box-shadow: 0 12px 25px rgba(11, 42, 89, 0.2);
            transition: 0.3s;
        }

        .btn-reserve:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(11, 42, 89, 0.3);
        }

        .instruction-box {
            background: #fff8f5;
            border: 2px dashed var(--accent);
            border-radius: 20px;
            padding: 35px 20px;
            color: var(--primary);
        }
    </style>
</head>

<body>

    <div class="container animate__animated animate__fadeIn">
        <div class="luxury-card">
            <div class="row g-5">
                <div class="col-lg-7">
                    <div class="hero-image-container">
                        <img src="../<?= htmlspecialchars($place['image']) ?>" id="mainVisual" alt="Visual">
                        <div class="floating-badge" id="quotaBadge">
                            <i class="fas fa-users me-2"></i> Area Publik
                        </div>
                        <div class="gallery-nav" id="mainGalleryNav">
                            <?php foreach ($gallery_images as $img): ?>
                                <img src="<?= $img ?>" onclick="document.getElementById('mainVisual').src=this.src">
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="mt-4 px-2">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div style="width: 25px; height: 3px; background: var(--accent); border-radius: 5px;"></div>
                            <span class="fw-bold small text-uppercase" style="color: var(--accent); letter-spacing: 1px;">Detail Objek</span>
                        </div>
                        <h1 class="display-6" style="font-weight: 800; color: var(--primary); letter-spacing: -1px;"><?= htmlspecialchars($place['name']); ?></h1>

                        <div class="desc-box">
                            <h6 class="fw-bold text-primary mb-2"><i class="fas fa-info-circle me-2"></i>Informasi Deskripsi</h6>
                            <p id="roomDesc" class="text-muted mb-0">
                                <?= nl2br(htmlspecialchars($place['description'])); ?>
                            </p>
                        </div>

                        <div class="mt-4 d-flex align-items-center gap-3">
                            <div class="p-3 bg-light rounded-4 border">
                                <i class="fas fa-map-marked-alt text-danger fa-lg"></i>
                            </div>
                            <div>
                                <p class="mb-0 text-muted small fw-semibold">Lokasi Pemetaan</p>
                                <a href="<?= htmlspecialchars($place['lokasi']) ?>" target="_blank" class="fw-bold text-primary text-decoration-none small">
                                    Navigasi Google Maps <i class="fas fa-external-link-alt ms-1" style="font-size: 0.75rem;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="bg-white p-4 rounded-4 border shadow-sm d-flex flex-column">
                        <div class="text-center mb-4">
                            <h4 class="fw-bold mb-3">
                                Cek Ketersediaan
                            </h4>
                            <?php
                            $prevMonth = $month - 1;
                            $prevYear  = $year;
                            if ($prevMonth < 1) {
                                $prevMonth = 12;
                                $prevYear--;
                            }
                            $nextMonth = $month + 1;
                            $nextYear  = $year;
                            if ($nextMonth > 12) {
                                $nextMonth = 1;
                                $nextYear++;
                            }
                            ?>
                            <div class="d-flex justify-content-center align-items-center gap-2">
                                <a href="?id=<?= $place_id ?>&month=<?= $prevMonth ?>&year=<?= $prevYear ?>"
                                    class="btn btn-sm btn-light rounded-circle shadow-sm">
                                    <i class="fas fa-chevron-left"></i>
                                </a>

                                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-2 fw-bold">
                                    <?= $bulanIndonesia[$month] . ' ' . $year ?>
                                </span>

                                <a href="?id=<?= $place_id ?>&month=<?= $nextMonth ?>&year=<?= $nextYear ?>"
                                    class="btn btn-sm btn-light rounded-circle shadow-sm">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </div>
                        </div>
                        <?php if (!empty($sub_places)): ?>
                            <ul class="nav nav-pills nav-pills-luxury mb-4">
                                <?php foreach ($sub_places as $sub):
                                    $sub_desc = !empty($sub['deskripsi']) ? $sub['deskripsi'] : $place['description'];
                                    $sub_img = !empty($sub['gambar']) ? '../' . $sub['gambar'] : '../' . $place['image'];
                                ?>
                                    <li class="nav-item flex-fill">
                                        <button
                                            class="nav-link w-100 sub-place-btn"
                                            data-bs-toggle="pill"
                                            data-bs-target="#sub<?= $sub['id'] ?>"
                                            onclick="updateUI(this, '<?= htmlspecialchars($sub_img) ?>')"
                                            data-info="<?= htmlspecialchars($sub_desc) ?>"
                                            data-badge="Bagian: <?= htmlspecialchars($sub['nama_subtempat']) ?>">
                                            <?= htmlspecialchars($sub['nama_subtempat']) ?>
                                        </button>
                                    </li>
                                <?php endforeach; ?>
                            </ul>

                            <div class="tab-content d-none" id="calendarWrapper">
                                <?php
                                $first = true;
                                foreach ($sub_places as $sub):
                                ?>
                                    <div class="tab-pane fade <?= $first ? 'show active' : '' ?>" id="sub<?= $sub['id'] ?>">
                                        <?= build_calendar($month, $year, $booked_dates[$sub['id']] ?? [], $place_id, $sub['id']); ?>
                                    </div>
                                <?php
                                    $first = false;
                                endforeach;
                                ?>
                            </div>

                            <div id="selectPrompt" class="instruction-box text-center my-2 animate__animated animate__fadeIn">
                                <i class="fa-solid fa-circle-info fa-2x text-warning mb-3"></i>
                                <h6 class="fw-bold mb-1">Pilih Area Terlebih Dahulu</h6>
                                <p class="small text-muted mb-0">Silakan tentukan area pilihan Anda (Teater / Lobby) di atas untuk memunculkan kalender.</p>
                            </div>
                        <?php else: ?>
                            <div>
                                <?= build_calendar($month, $year, $booked_dates['main'] ?? [], $place_id); ?>
                            </div>
                        <?php endif; ?>

                        <div class="mt-4 p-3 bg-light rounded-4 d-flex justify-content-between align-items-center border">
                            <div class="small fw-bold text-muted"><i class="fas fa-circle text-primary me-2"></i>Tersedia (Klik Tanggal)</div>
                            <div class="small fw-bold text-muted"><i class="fas fa-circle text-danger opacity-50 me-2"></i>Penuh</div>
                        </div>

                        <button class="btn-reserve mt-4" onclick="Swal.fire('Pilih Tanggal','Silakan klik langsung pada kotak angka tanggal di kalender untuk mulai mengajukan reservasi!','info')">
                            AJUKAN RESERVASI SEKARANG
                        </button>

                        <div class="text-center mt-3">
                            <a href="index.php" class="text-muted small fw-bold text-decoration-none">
                                <i class="fas fa-chevron-left me-1"></i> Kembali ke Beranda
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function updateUI(btn, imgSrc) {
            const mainImg = document.getElementById('mainVisual');
            const descText = document.getElementById('roomDesc');
            const quotaBadge = document.getElementById('quotaBadge');
            const galleryNav = document.getElementById('mainGalleryNav');

            const selectPrompt = document.getElementById('selectPrompt');
            const calendarWrapper = document.getElementById('calendarWrapper');

            // Pindahkan fungsi remove d-none langsung ke elemen tab-content utama
            if (selectPrompt) selectPrompt.style.setProperty('display', 'none', 'important');
            if (calendarWrapper) calendarWrapper.classList.remove('d-none');

            const newInfo = btn.getAttribute('data-info');
            const newBadge = btn.getAttribute('data-badge');

            mainImg.style.opacity = '0';
            descText.style.opacity = '0';
            quotaBadge.style.opacity = '0';
            if (galleryNav) galleryNav.style.opacity = '0';

            setTimeout(() => {
                mainImg.src = imgSrc;
                descText.innerHTML = newInfo.replace(/(?:\r\n|\r|\n)/g, '<br>');
                quotaBadge.innerHTML = `<i class="fas fa-door-open me-2"></i> ${newBadge}`;

                if (galleryNav) galleryNav.style.display = 'none';

                mainImg.style.opacity = '1';
                descText.style.opacity = '1';
                quotaBadge.style.opacity = '1';
            }, 250);
        }
    </script>
</body>

</html>