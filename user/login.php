<?php
session_start();
include '../db.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Menggunakan Prepared Statement agar lebih aman
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];

            header('Location: index.php');
            exit;
        } else {
            $error = "Password yang Anda masukkan salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Otentikasi Layanan | Disbudpar Kota Tangerang</title>

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
            position: relative;
            overflow: hidden;
        }

        /* RACIKAN UTAMA: Neo-Glassmorphism Container Card Melayang */
        .glass-login-card {
            background: rgba(255, 255, 255, 0.12);
            /* Transparansi Kaca Akrilik */
            backdrop-filter: blur(35px) saturate(200%);
            /* Efek Frosted Berembun Tebal */
            -webkit-backdrop-filter: blur(35px) saturate(200%);
            border: 2px solid rgba(255, 255, 255, 0.25);
            /* Garis Tepi Kaca */
            border-radius: 36px;
            padding: 50px 45px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 40px 100px rgba(0, 0, 0, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
            position: relative;
            transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .glass-login-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 140, 66, 0.4);
        }

        .brand-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            background: rgba(255, 255, 255, 0.8);
            /* Background putih kecil di logo agar logo tetap tajam */
            padding: 10px 20px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.05);
        }

        .brand-logos img {
            height: 42px;
        }

        .header-text h2 {
            font-weight: 800;
            color: #ffffff;
            /* Ubah teks header jadi putih karena background kaca */
            font-size: 1.9rem;
            letter-spacing: -1.2px;
            margin-bottom: 6px;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .header-text p {
            color: rgba(255, 255, 255, 0.7);
            font-size: 0.9rem;
            margin-bottom: 2.5rem;
            font-weight: 500;
        }

        .form-label {
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            display: block;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.1);
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
            font-size: 1.05rem;
            z-index: 5;
            transition: all 0.3s ease;
        }

        /* Input Glassmorphic Terang */
        .form-control {
            border-radius: 18px;
            padding: 15px 18px 14px 52px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            background: rgba(255, 255, 255, 0.1);
            font-weight: 600;
            color: #ffffff;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
            width: 100%;
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }

        /* Focus Glow Input */
        .form-control:focus {
            box-shadow: 0 0 0 5px rgba(255, 140, 66, 0.25);
            border-color: var(--primary-glow);
            background: rgba(255, 255, 255, 0.18);
            outline: none;
            color: #ffffff;
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

        /* Tombol Otentikasi Dengan Efek Cahaya Menyala */
        .btn-login {
            background: linear-gradient(135deg, #ff8c42 0%, #ff6b00 100%);
            border: none;
            border-radius: 18px;
            padding: 15px;
            font-weight: 800;
            color: white;
            font-size: 1rem;
            box-shadow: 0 12px 30px rgba(255, 140, 66, 0.35);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            width: 100%;
            margin-top: 1rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(255, 140, 66, 0.5);
            filter: brightness(1.1);
        }

        .footer-links {
            margin-top: 2.2rem;
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

    <div class="glass-login-card animate__animated animate__fadeInUp">

        <div class="brand-logos animate__animated animate__zoomIn" style="animation-delay: 0.3s;">
            <img src="../assets/img/logotng.png" alt="Logo Tangerang">
            <img src="../assets/img/kerenjasa.png" alt="Logo Keren Jasa">
        </div>

        <div class="header-text text-center">
            <h2>Selamat Datang</h2>
            <p>Silakan masuk untuk melanjutkan reservasi</p>
        </div>

        <?php if (isset($error)): ?>
            <div class="error-msg animate__animated animate__shakeX">
                <i class="fas fa-circle-exclamation"></i>
                <span><?= $error; ?></span>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <div class="input-group-custom">
                    <input type="text" class="form-control" name="username" placeholder="Masukkan username" required autocomplete="off">
                    <i class="fas fa-user input-icon"></i>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label">Password</label>
                <div class="input-group-custom">
                    <input type="password" class="form-control password-field" id="password" name="password" placeholder="Masukkan password" required>
                    <i class="fas fa-lock input-icon"></i>
                    <span class="password-toggle" id="togglePassword">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit" name="login" class="btn btn-login">Masuk</button>
        </form>

        <div class="footer-links text-center">
            <p class="mb-2">Belum punya akun? <a href="register.php">Daftar Akun</a></p>
            <a href="index.php" style="border:none;"><i class="fas fa-arrow-left me-2"></i>Kembali ke Beranda</a>
        </div>

    </div>

    <script>
        const togglePassword = document.querySelector("#togglePassword");
        const password = document.querySelector("#password");

        togglePassword.addEventListener("click", function() {
            const type = password.getAttribute("type") === "password" ? "text" : "password";
            password.setAttribute("type", type);

            const icon = this.querySelector("i");
            icon.classList.toggle("fa-eye");
            icon.classList.toggle("fa-eye-slash");
        });
    </script>
</body>

</html>