<?php
include 'includes/auth.php';
include '../db.php';
include 'includes/auto_status.php';

// =====================================================
// AMBIL DATA RESERVASI
// =====================================================

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
        
        // Jika status asli 'selesai' ATAU tanggal hari ini sudah melewati batas akhir acara, maka otomatis ubah status visualnya menjadi selesai
        $statusVisual = $row['status'];

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
    <?php include 'includes/header.php'; ?>
    <title>Kalender Reservasi | TNG Admin</title>

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />

    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.5/locales/id.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="../assets/css/calendar.css">
</head>

<body>
    <?php include 'includes/sidebar.php'; ?>
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
                    <?php if (count($upcoming) > 0): ?>
                        <?php foreach (array_slice($upcoming, 0, 5) as $item): ?>
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
    <script src="../assets/js/admin.js"></script>
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
                    const formatOpsi = {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    };
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
                        customClass: {
                            popup: 'rounded-4'
                        }
                    });
                }
            });
            calendar.render();
        });
    </script>
</body>

</html>