<?php
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];


    // Validasi apakah password cocok
    if ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Gunakan Prepared Statement agar aman dari SQL Injection
        $stmt = $conn->prepare(
            "INSERT INTO users (username, password) VALUES (?, ?)"
        );
        $stmt->bind_param("ss", $username, $hashed_password);

        if ($stmt->execute()) {

            $redirect = isset($_GET['redirect'])
                ? $_GET['redirect']
                : '';

            echo "<script>
            window.location.href =
            'login.php?status=registered&redirect=' +
            encodeURIComponent('$redirect');
          </script>";

            exit;
        } else {
            $error = "Registrasi gagal! Username mungkin sudah digunakan.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Portal Layanan | Disbudpar Kota Tangerang</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon">

    <style>
        :root {
            --primary-glow: #ff8c42;
            --secondary-glow: #4361ee;
            --dark-overlay: #030712;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            /* Wallpaper sinematik disamakan persis dengan halaman login */
            background: linear-gradient(135deg, rgba(3, 7, 18, 0.45) 0%, rgba(11, 42, 89, 0.35) 100%),
                url("../assets/img/newbg.jpg") no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Racikan Tabung Kaca Akrilik Melayang (Neo-Glassmorphism) */
        .glass-register-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(35px) saturate(200%);
            -webkit-backdrop-filter: blur(35px) saturate(200%);
            border: 2px solid rgba(255, 255, 255, 0.25);
            border-radius: 36px;
            padding: 45px 40px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .glass-register-card:hover {
            transform: translateY(-6px);
            border-color: rgba(255, 140, 66, 0.4);
        }

        .brand-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.8);
            padding: 10px 20px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .brand-logos img {
            height: 40px;
        }

        .header-text h2 {
            font-weight: 800;
            color: #ffffff;
            font-size: 1.85rem;
            letter-spacing: -1px;
            margin-bottom: 6px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .header-text p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 2rem;
            font-weight: 500;
        }

        .form-label {
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
            display: block;
        }

        .input-group-custom {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .input-icon {
            position: absolute;
            left: 20px;
            color: rgba(255, 255, 255, 0.5);
            font-size: 1rem;
            z-index: 5;
            transition: all 0.3s ease;
        }

        .form-control {
            border-radius: 18px;
            padding: 14px 18px 14px 52px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            font-weight: 600;
            color: #ffffff;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            width: 100%;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        .form-control:focus {
            box-shadow: 0 0 0 5px rgba(255, 140, 66, 0.25);
            border-color: var(--primary-glow);
            background: rgba(255, 255, 255, 0.18);
            outline: none;
        }

        .form-control:focus+.input-icon,
        .input-group-custom:focus-within .input-icon {
            color: var(--primary-glow);
        }

        .password-toggle {
            position: absolute;
            right: 20px;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.5);
            z-index: 5;
            transition: color 0.2s ease;
        }

        .password-toggle:hover {
            color: #ffffff;
        }

        .btn-register {
            background: linear-gradient(135deg, #ff8c42 0%, #ff6b00 100%);
            border: none;
            border-radius: 18px;
            padding: 15px;
            font-weight: 800;
            color: white;
            font-size: 1rem;
            box-shadow: 0 12px 30px rgba(255, 140, 66, 0.35);
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 1rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(255, 140, 66, 0.5);
            filter: brightness(1.1);
        }

        .footer-links {
            margin-top: 2rem;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .footer-links p,
        .footer-links {
            color: rgba(255, 255, 255, 0.7);
        }

        .footer-links a {
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            border-bottom: 1.5px solid rgba(255, 255, 255, 0.3);
            padding-bottom: 2px;
            transition: all 0.2s ease;
        }

        .footer-links a:hover {
            color: var(--primary-glow);
            border-color: var(--primary-glow);
        }

        .error-msg {
            background: rgba(220, 38, 38, 0.2);
            color: #fecaca;
            padding: 14px;
            border-radius: 16px;
            font-size: 0.85rem;
            margin-bottom: 1.5rem;
            text-align: center;
            border: 1px solid rgba(220, 38, 38, 0.3);
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            backdrop-filter: blur(10px);
        }
    </style>
</head>

<body>

    <!-- CARD UTAMA: GLASSMORPHISM REGISTER CARD -->
    <div class="glass-register-card animate__animated animate__fadeInUp">

        <!-- Kop Keren Jasa & Kota Tangerang -->
        <div class="brand-logos animate__animated animate__zoomIn" style="animation-delay: 0.2s;">
            <img src="../assets/img/logotng.png" alt="Logo Tangerang">
            <img src="../assets/img/kerenjasa.png" alt="Logo Keren Jasa">
        </div>

        <div class="header-text text-center">
            <h2>Daftar Akun</h2>
            <p>Bergabunglah untuk mulai menggunakan layanan portal</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-msg animate__animated animate__shakeX">
                <i class="fas fa-circle-exclamation"></i>
                <span><?= $error; ?></span>
            </div>
        <?php endif; ?>

        <!-- Form Pembuatan Akun -->
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group-custom">
                    <i class="fas fa-user input-icon"></i>
                    <input type="text" class="form-control" name="username" placeholder="Pilih nama pengguna" required autocomplete="off">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <div class="input-group-custom">
                    <i class="fas fa-lock input-icon"></i>
                    <input type="password" class="form-control" id="password" name="password" placeholder="Buat kata sandi rahasia" required>
                    <span class="password-toggle" onclick="toggleField('password', this)">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Konfirmasi Password</label>
                <div class="input-group-custom">
                    <i class="fas fa-shield-halved input-icon"></i>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi kata sandi di atas" required>
                    <span class="password-toggle" onclick="toggleField('confirm_password', this)">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit" class="btn btn-register">Buat Akun Sekarang</button>
        </form>

        <!-- Navigasi Footer -->
        <div class="footer-links text-center">
            <p class="mb-2">Sudah memiliki akun? <a href="login.php?redirect=<?= urlencode($_GET['redirect'] ?? '') ?>">
                    Login di sini
                </a></p>
            <a href="index.php" style="border:none;"><i class="fas fa-house me-2"></i>Kembali ke Beranda</a>
        </div>

    </div>

    <!-- Script Toggler Mata Intip Sandi -->
    <script>
        function toggleField(inputId, toggleEl) {
            const inputField = document.getElementById(inputId);
            const type = inputField.getAttribute("type") === "password" ? "text" : "password";
            inputField.setAttribute("type", type);

            const icon = toggleEl.querySelector("i");
            icon.classList.toggle("fa-eye");
            icon.classList.toggle("fa-eye-slash");
        }
    </script>
</body>

</html>