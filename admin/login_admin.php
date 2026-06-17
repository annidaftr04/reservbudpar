<?php
session_start();
include '../db.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Gunakan prepared statement untuk keamanan
    $query = "SELECT id, username, password FROM admin WHERE username=?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            header('Location: dashboard_admin.php');
            exit;
        } else {
            echo "<script>alert('Password salah!');</script>";
        }
    } else {
        echo "<script>alert('Username tidak ditemukan!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="../assets/img/logotng.png" type="image/x-icon">
    <style>
        :root {
            --bg-color: #e0e5ec;
            --primary-color: #002D62;
            --accent-color: #FF5733;
            --shadow-light: #ffffff;
            --shadow-dark: #a3b1c6;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-color);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: var(--primary-color);
        }

        .login-container {
            background-color: var(--bg-color);
            border-radius: 20px;
            padding: 3rem;
            width: 100%;
            max-width: 450px;
            box-shadow: 10px 10px 20px var(--shadow-dark), -10px -10px 20px var(--shadow-light);
            text-align: center;
            transition: all 0.3s ease;
        }

        .login-container img {
            max-width: 80px;
            margin-bottom: 1rem;
        }

        .login-container h2 {
            font-weight: 600;
            margin-bottom: 2rem;
            font-size: 1.8rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .form-control-neumorphism {
            width: 100%;
            padding: 15px 20px 15px 50px;
            border: none;
            border-radius: 12px;
            background: var(--bg-color);
            box-shadow: inset 5px 5px 10px var(--shadow-dark), inset -5px -5px 10px var(--shadow-light);
            transition: all 0.3s ease;
            color: #333;
        }

        .form-control-neumorphism:focus {
            outline: none;
            box-shadow: inset 2px 2px 5px var(--shadow-dark), inset -2px -2px 5px var(--shadow-light);
        }

        .form-group i {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .btn-login-neumorphism {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 50px;
            font-weight: 600;
            background-color: var(--primary-color);
            color: white;
            box-shadow: 5px 5px 10px var(--shadow-dark), -5px -5px 10px var(--shadow-light);
            transition: all 0.3s ease;
            text-transform: uppercase;
        }

        .btn-login-neumorphism:hover {
            background-color: #1A4D90;
            box-shadow: 2px 2px 5px var(--shadow-dark), -2px -2px 5px var(--shadow-light);
            transform: translateY(-2px);
        }

        .link-register {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .link-register:hover {
            color: var(--accent-color);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <img src="../assets/img/logotng.png" alt="Logo">
        <h2>Login Admin</h2>
        <form method="POST">
            <div class="form-group">
                <i class="fas fa-user"></i>
                <input type="text" class="form-control-neumorphism" id="username" name="username" placeholder="Username" required>
            </div>
            <div class="form-group">
                <i class="fas fa-lock"></i>
                <input type="password" class="form-control-neumorphism" id="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" name="login" class="btn-login-neumorphism">Login</button>
        </form>
        <p class="mt-4">
            Belum punya akun? <a href="register_admin.php" class="link-register">Daftar di sini</a>
        </p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>