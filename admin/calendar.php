<?php
include '../db.php';
session_start();

// Pastikan admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header('Location: login_admin.php');
    exit;
}

// =====================================================
// AMBIL DATA RESERVASI
// =====================================================
$sql = "
SELECT
    reservations.*,
    places.name AS nama_tempat
FROM reservations
LEFT JOIN places
ON reservations.place_id = places.id
WHERE reservations.status IN ('disetujui', 'selesai')
ORDER BY reservations.hari ASC
";

$result = $conn->query($sql);
$events = [];
$upcoming = [];
$totalReservasi = 0;
$totalHariIni = 0;
$totalDisetujui = 0;
$tempatAktif = [];
$today = date('Y-m-d');

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $totalReservasi++;
        if ($row['status'] == 'disetujui') {
            $totalDisetujui++;
        }
        if ($row['hari'] == $today) {
            $totalHariIni++;
        }
        $tempatAktif[] = $row['nama_tempat'];
        
        // --- LOGIKA OTOMATISASI WARNA (AUTO-TRIGGER) SIKLUS ACARA ---
        // Tentukan batas akhir acara untuk pengecekan hari ini
        $batasAkhirAcara = !empty($row['tanggal_selesai']) ? $row['tanggal_selesai'] : $row['hari'];
        
        // Jika status asli 'selesai' ATAU tanggal hari ini sudah melewati batas akhir acara, maka otomatis ubah status visualnya menjadi selesai
        $statusVisual = $row['status'];
        if ($row['status'] === 'disetujui' && $today > $batasAkhirAcara) {
            $statusVisual = 'selesai';
        }

        // Warna disesuaikan dengan status visual (Biru Tua untuk akan datang, Biru Muda untuk selesai)
        $eventColor = ($statusVisual === 'selesai') ? '#06b6d4' : '#4361ee'; 

        $events[] = [
            'title' => $row['nama_tempat'],
            'start' => $row['hari'],
            'end' => $row['tanggal_selesai'] ? date('Y-m-d', strtotime($row['tanggal_selesai'] . ' +1 day')) : date('Y-m-d', strtotime($row['hari'] . ' +1 day')),
            'backgroundColor' => $eventColor, 
            'borderColor' => $eventColor,
            'extendedProps' => [
                'nama' => $row['nama'],
                'tempat' => $row['nama_tempat'],
                'jam_mulai' => $row['jam_mulai'],
                'jam_selesai' => $row['jam_selesai'],
                'tanggal_mulai_raw' => $row['hari'],
                'tanggal_selesai_raw' => $row['tanggal_selesai'], 
                'keterangan' => $row['keterangan'],
                'status' => $statusVisual, // Menggunakan status visual yang sudah di-update
                'kuesioner_sent' => (int)$row['kuesioner_sent']
            ]
        ];

        // Daftar kegiatan mendatang (hanya yang benar-benar belum terlaksana)
        if ($row['hari'] >= $today && $statusVisual === 'disetujui') {
            $upcoming[] = $row;
        }
    }
}

$totalTempatAktif = count(array_unique($tempatAktif));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Reservasi | TNG Admin</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="../assets/img/logotng.png">
    
    <style>
        :root {
            --primary: #4361ee;
            --secondary: #3f37c9;
            --bg: #f8fafc;
            --sidebar-color: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            
            --gradient-1: linear-gradient(135deg, #4361ee 0%, #3f37c9 100%);
            --gradient-2: linear-gradient(135deg, #ff9f1c 0%, #ffb703 100%);
            --gradient-3: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --gradient-4: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg);
            color: var(--text-main);
            margin: 0;
            display: flex;
            overflow-x: hidden;
        }

        .sidebar {
            width: 280px; height: 100vh; background: var(--sidebar-color);
            position: fixed; border-right: 1px solid rgba(0,0,0,0.05);
            z-index: 1100; padding: 2.5rem 1.5rem;
        }

        .sidebar-brand {
            display: flex; align-items: center; gap: 12px;
            padding-bottom: 2.5rem; font-weight: 800; font-size: 1.5rem;
            color: var(--primary); letter-spacing: -1px;
        }

        .nav-link {
            display: flex; align-items: center; gap: 15px;
            padding: 14px 1rem; color: var(--text-muted); text-decoration: none;
            font-weight: 600; border-radius: 14px; transition: 0.3s;
            margin-bottom: 8px;
        }

        .nav-link:hover, .nav-link.active {
            background: var(--primary); color: white;
            box-shadow: 0 10px 20px -5px rgba(67, 97, 238, 0.4);
        }

        .main-content { 
            margin-left: 280px; 
            flex: 1; 
            padding: 3rem; 
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .glass-header {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(15px);
            padding: 2rem; border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            margin-bottom: 2.5rem;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.03);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            border-radius: 22px;
            padding: 25px;
            position: relative;
            overflow: hidden;
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            color: white;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.1);
        }
        .stat-card h3 {
            font-size: 36px;
            font-weight: 800;
            margin: 0;
            letter-spacing: -1px;
            color: white;
        }
        .stat-card p {
            margin: 5px 0 0;
            color: rgba(255,255,255,0.85);
            font-size: 14px;
            font-weight: 600;
        }
        .stat-card i {
            position: absolute;
            right: 20px;
            bottom: 20px;
            font-size: 32px;
            color: rgba(255, 255, 255, 0.25);
        }

        .main-grid {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 25px;
        }

        .calendar-card {
            background: white;
            border-radius: 24px;
            padding: 30px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.04);
            border: 1px solid rgba(0, 0, 0, 0.01);
        }

        .fc-toolbar-title {
            font-size: 20px !important;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.5px;
        }
        .fc-button-primary {
            background: #f1f5f9 !important;
            border: 1px solid #e2e8f0 !important;
            color: var(--text-main) !important;
            border-radius: 12px !important;
            padding: 8px 16px !important;
            font-weight: 700 !important;
            font-size: 14px !important;
            box-shadow: none !important;
            transition: all 0.2s ease;
        }
        .fc-button-primary:hover {
            background: var(--primary) !important;
            color: white !important;
            border-color: var(--primary) !important;
        }
        .fc-button-active {
            background: var(--primary) !important;
            color: white !important;
            border-color: var(--primary) !important;
        }
        .fc-daygrid-event {
            border: none !important;
            border-radius: 10px !important;
            padding: 6px 10px !important;
            font-weight: 600;
            font-size: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .fc-day-today {
            background: rgba(67, 97, 238, 0.05) !important;
        }
        .fc-col-header-cell-cushion {
            font-weight: 700;
            color: var(--text-muted);
            font-size: 14px;
            text-decoration: none !important;
        }
        .fc-daygrid-day-number {
            font-weight: 600;
            color: var(--text-main);
            font-size: 13px;
            text-decoration: none !important;
            padding: 8px !important;
        }

        .side-card {
            background: white;
            border-radius: 24px;
            padding: 25px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.04);
            margin-bottom: 20px;
            border: 1px solid rgba(0, 0, 0, 0.01);
        }
        .side-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--text-main);
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .upcoming-item {
            padding: 16px;
            border-radius: 16px;
            background: #f8fafc;
            margin-bottom: 15px;
            border-left: 4px solid var(--primary);
            transition: transform 0.2s ease;
        }
        .upcoming-item:hover {
            transform: translateX(4px);
        }
        .upcoming-item h6 {
            margin: 0 0 4px 0;
            font-weight: 700;
            color: var(--text-main);
            font-size: 14px;
        }
        .upcoming-item small {
            color: var(--text-muted);
            font-weight: 500;
            font-size: 12px;
        }
        .legend-item {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
        }
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 6px;
        }

        @media(max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; padding: 1.5rem; }
            .main-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="../assets/img/logotng.png" width="35" alt="Logo">
        <span>Admin Reservasi</span>
    </div>
    <nav>
        <a href="dashboard_admin.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="kelola_reserv.php" class="nav-link"><i class="fa-solid fa-calendar-check"></i> Reservasi</a>
        <a href="kelola_surat.php" class="nav-link"><i class="fa-solid fa-map-location-dot"></i> Kelola Surat</a>
        <a href="kelola_tempat.php" class="nav-link"><i class="fa-solid fa-map-location-dot"></i> Kelola Tempat</a>
        <a href="calendar.php" class="nav-link active"><i class="fa-solid fa-calendar-days"></i> Kalender</a>
        <div style="margin: 2rem 0;"><hr style="opacity: 0.1;"></div>
        <a href="logout_admin.php"class="nav-link text-danger"onclick="confirmAdminLogout(event)"><i class="fas fa-sign-out-alt"></i>Logout</a>    </nav>
</aside>

<main class="main-content">
    <header class="glass-header">
        <div>
            <h1 class="h3 fw-800 mb-1">Kalender Reservasi</h1>
            <p class="text-muted small mb-0">Pantau jadwal peminjaman tempat dan objek fasilitas yang disetujui.</p>
        </div>
    </header>

    <div class="stats-grid">
        <div class="stat-card" style="background: var(--gradient-1);">
            <h3><?= $totalReservasi ?></h3>
            <p>Total Kegiatan</p>
            <i class="fa-solid fa-calendar-days"></i>
        </div>
        <div class="stat-card" style="background: var(--gradient-2);">
            <h3><?= $totalHariIni ?></h3>
            <p>Reservasi Hari Ini</p>
            <i class="fa-solid fa-clock animate__animated animate__pulse animate__infinite"></i>
        </div>
        <div class="stat-card" style="background: var(--gradient-3);">
            <h3><?= $totalDisetujui ?></h3>
            <p>Aktif (Disetujui)</p>
            <i class="fa-solid fa-circle-check"></i>
        </div>
        <div class="stat-card" style="background: var(--gradient-4);">
            <h3><?= $totalTempatAktif ?></h3>
            <p>Tempat Aktif</p>
            <i class="fa-solid fa-building-flag"></i>
        </div>
    </div>

    <div class="main-grid">
        <div class="calendar-card">
            <div id="calendar"></div>
        </div>

        <div>
            <div class="side-card" style="background: #e0f2fe; border: 1px solid #bae6fd;">
                <div class="d-flex align-items-center gap-2 text-primary mb-2">
                    <i class="fa-solid fa-robot animate__animated animate__bounce animate__infinite" style="font-size: 1.2rem;"></i>
                    <h6 class="fw-bold mb-0" style="color: #0369a1;">Sistem Otomatisasi</h6>
                </div>
                <p class="small text-muted mb-0" style="font-size: 12px; line-height: 1.5;">
                    Fungsi pemicu otomatis akan mendeteksi agenda <b>"Selesai"</b> untuk mengirimkan berkas kuesioner tepat pada <b>H+1</b> pasca-acara.
                </p>
            </div>

            <div class="side-card">
                <div class="side-title">Reservasi Mendatang</div>
                <?php if(count($upcoming) > 0): ?>
                    <?php foreach(array_slice($upcoming, 0, 5) as $item): ?>
                        <div class="upcoming-item">
                            <h6><?= htmlspecialchars($item['nama_tempat']) ?></h6>
                            <small class="d-block mb-1"><i class="fa-regular fa-user me-1"></i> <?= htmlspecialchars($item['nama']) ?></small>
                            <small><i class="fa-regular fa-calendar me-1"></i> <?= date('d M Y', strtotime($item['hari'])) ?> • <?= substr($item['jam_mulai'], 0, 5) ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted small mb-0">Belum ada reservasi mendatang.</p>
                <?php endif; ?>
            </div>

            <div class="side-card">
                <div class="side-title">Keterangan / Legend</div>
                <div class="legend-item">
                    <div class="legend-color" style="background:#4361ee"></div>
                    Akan Datang (Disetujui)
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background:#06b6d4"></div>
                    Selesai Pelaksanaan
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background: rgba(67, 97, 238, 0.04); border: 1px dashed var(--primary);"></div>
                    Hari Ini (Hari Aktif)
                </div>
            </div>
        </div>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');
        const calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            height: 'auto',
            events: <?= json_encode($events); ?>,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth'
            },

            eventClick(info) {
                const data = info.event.extendedProps;
                
                let infoTanggalHtml = '';
                const formatOpsi = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                const tglMulaiObj = new Date(data.tanggal_mulai_raw);
                
                if (data.tanggal_selesai_raw && data.tanggal_selesai_raw !== data.tanggal_mulai_raw) {
                    const tglSelesaiObj = new Date(data.tanggal_selesai_raw);
                    infoTanggalHtml = `
                        <p class="mb-1"><b>Tanggal Mulai:</b> ${tglMulaiObj.toLocaleDateString('id-ID', formatOpsi)}</p>
                        <p class="mb-2"><b>Tanggal Selesai:</b> <span class="text-danger fw-bold">${tglSelesaiObj.toLocaleDateString('id-ID', formatOpsi)}</span></p>
                    `;
                } else {
                    infoTanggalHtml = `<p class="mb-2"><b>Tanggal Pelaksanaan:</b> ${tglMulaiObj.toLocaleDateString('id-ID', formatOpsi)}</p>`;
                }
                
                // LOGIKA SINKRONISASI COCOK STATUS DARI DATABASE + HASIL AUTO-TRIGGER
                let kuesionerStatusHtml = '';
                if (data.status === 'selesai' || parseInt(data.kuesioner_sent) === 1) {
                    if (parseInt(data.kuesioner_sent) === 1) {
                        kuesionerStatusHtml = `
                            <div class="mt-3 p-3 bg-success bg-opacity-10 text-success rounded-3 small fw-bold border border-success border-opacity-25">
                                <i class="fa-solid fa-envelope-circle-check me-2"></i> Kuesioner Evaluasi: SUDAH TERKIRIM
                                <span class="d-block text-muted fw-normal mt-1" style="font-size:11px;">Sistem otomatis telah mengirimkan tautan kuesioner ke email pemohon pada H+1 setelah acara.</span>
                            </div>
                        `;
                    } else {
                        kuesionerStatusHtml = `
                            <div class="mt-3 p-3 bg-warning bg-opacity-10 text-warning rounded-3 small fw-bold border border-warning border-opacity-25">
                                <i class="fa-solid fa-clock me-2"></i> Kuesioner Evaluasi: ANTRIAN KIRIM (H+1)
                                <span class="d-block text-muted fw-normal mt-1" style="font-size:11px;">Acara telah selesai. Script otomatisasi dijadwalkan mengirim email besok pagi.</span>
                            </div>
                        `;
                    }
                } else {
                    kuesionerStatusHtml = `
                        <div class="mt-3 p-3 bg-secondary bg-opacity-10 text-muted rounded-3 small fw-bold border border-secondary border-opacity-10">
                            <i class="fa-solid fa-hourglass-start me-2"></i> Kuesioner Evaluasi: Menunggu Acara Selesai
                        </div>
                    `;
                }

                Swal.fire({
                    title: 'Detail Jadwal Reservasi',
                    html: `
                        <div style="text-align:left; font-family:'Plus Jakarta Sans', sans-serif; font-size:14px; line-height: 1.6;">
                            <p class="mb-2"><b>Nama Pemohon:</b><br> ${data.nama}</p>
                            <p class="mb-2"><b>Lokasi Tempat:</b><br> ${data.tempat}</p>
                            ${infoTanggalHtml}
                            <p class="mb-2"><b>Waktu Durasi:</b><br> ${data.jam_mulai.substring(0,5)} - ${data.jam_selesai.substring(0,5)} WIB</p>
                            <p class="mb-2"><b>Keterangan Kegiatan:</b><br>${data.keterangan}</p>
                            ${kuesionerStatusHtml}
                        </div>
                    `,
                    icon: 'info',
                    confirmButtonColor: '#4361ee',
                    customClass: { popup: 'rounded-4' }
                });
            }
        });
        calendar.render();
    });
    function confirmAdminLogout(event) {
        event.preventDefault();
        Swal.fire({
        title: 'Logout Admin?',
        text: 'Anda yakin ingin keluar dari dashboard admin?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Logout',
        cancelButtonText: 'Batal',
        borderRadius: '20px'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Sedang logout...',
                text: 'Mohon tunggu sebentar',
                icon: 'success',
                timer: 1200,
                showConfirmButton: false,
                allowOutsideClick: false
            });
        setTimeout(() => {
            window.location.href = 'logout_admin.php';
                }, 1200);
            }
        });
    }
</script>
</body>
</html>