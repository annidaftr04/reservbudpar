<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>

<aside class="sidebar">
    <div class="sidebar-brand">
        <img src="../assets/img/logotng.png" width="35" alt="Logo">
        <span>Admin Reservasi</span>
    </div>

    <nav>

        <a href="dashboard_admin.php"
            class="nav-link <?= $currentPage == 'dashboard_admin.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-house"></i>
            Dashboard
        </a>

        <a href="kelola_reserv.php"
            class="nav-link <?= $currentPage == 'kelola_reserv.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-check"></i>
            Reservasi
        </a>

        <a href="kelola_surat.php"
            class="nav-link <?= $currentPage == 'kelola_surat.php' ? 'active' : '' ?>">
            <i class="fas fa-file-invoice"></i>
            Kelola Surat
        </a>

        <a href="kelola_tempat.php"
            class="nav-link <?= $currentPage == 'kelola_tempat.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-map-location-dot"></i>
            Kelola Tempat
        </a>

        <a href="calendar.php"
            class="nav-link <?= $currentPage == 'calendar.php' ? 'active' : '' ?>">
            <i class="fa-solid fa-calendar-days"></i>
            Kalender
        </a>

        <div style="margin:2rem 0">
            <hr style="opacity:.1;">
        </div>

        <a href="logout_admin.php"
            class="nav-link text-danger"
            onclick="confirmAdminLogout(event)">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>

    </nav>
</aside>