<?php
// 1. Hubungkan ke file fungsi utama toko Anda
require 'fungsi.php';

// Memulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika user sudah login, langsung alihkan ke halaman profil agar tidak login dua kali
if (isset($_SESSION['username']) || isset($_SESSION['id_user'])) {
    header("Location: profil.php");
    exit();
}

$error = false;

// 2. LOGIKA PROSES LOGIN
if (isset($_POST['login'])) {
    $koneksi = mysqli_connect("localhost", "root", "", "fahira_boutique");
    
    if (!$koneksi) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }

    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // Mencari user berdasarkan username di tb_user
    $result = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username = '$username'");

    // Cek apakah username ditemukan
    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        
        // Cek password (mendukung password_verify jika di-hash, atau string biasa untuk development awal)
        if (password_verify($password, $row['password']) || $password === $row['password']) {
            
            // Set Session untuk otentikasi halaman lain
            $_SESSION['id_user']  = $row['id_user'] ?? $row['id'] ?? 1;
            $_SESSION['username'] = $row['username'];
            $_SESSION['nama']     = $row['nama'] ?? $row['username'];
            
            echo "<script>
                    alert('Login berhasil! Selamat datang di NFahira Boutique.');
                    window.location.href = 'profil.php';
                  </script>";
            exit();
        }
    }
    
    // Jika gagal, set flag error menjadi true
    $error = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NFahira Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- IMPORT GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        h2, .brand-title {
            font-family: 'Playfair Display', serif !important;
            letter-spacing: 1px;
        }
        .login-card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            background-color: #ffffff;
            max-width: 420px;
            width: 100%;
            padding: 2.5rem;
        }
        .brand-logo {
            height: 60px;
            width: 60px;
            border: 2px solid #D4AF37;
            background-image: url('Logo.jpeg'); 
            background-position: center; 
            background-size: 165%; 
            background-repeat: no-repeat;
            margin: 0 auto 15px;
        }
        .form-control:focus {
            border-color: #D4AF37;
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
        }
        .btn-login {
            background-color: #D4AF37;
            color: white;
            font-weight: 600;
            border: none;
            transition: all 0.2s;
        }
        .btn-login:hover {
            background-color: #b8952e;
            color: white;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <div class="login-card text-center">
        <!-- Logo Brand -->
        <div class="brand-logo rounded-circle shadow-sm"></div>
        <h2 class="fw-bold text-dark mb-1 brand-title">NFahira Boutique</h2>
        <p class="text-muted small mb-4">Masuk untuk mulai memesan pakaian impianmu</p>

        <!-- Alert Jika Login Gagal -->
        <?php if ($error): ?>
            <div class="alert alert-danger border-0 small py-2 rounded-3 text-start d-flex align-items-center gap-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <span>Username atau password salah!</span>
            </div>
        <?php endif; ?>

        <!-- Form Login -->
        <form action="" method="POST" class="text-start">
            <div class="mb-3">
                <label class="form-label fw-semibold small text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control bg-light border-start-0 ps-0" placeholder="Masukkan username Anda" required autocomplete="off">
                </div>
            </div>
            
            <div class="mb-4">
                <label class="form-label fw-semibold small text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control bg-light border-start-0 ps-0" placeholder="Masukkan password Anda" required>
                </div>
            </div>

            <div class="d-grid mb-3">
                <button type="submit" name="login" class="btn btn-login py-2.5 rounded-pill shadow-sm">
                    Masuk Sekarang <i class="bi bi-arrow-right ms-1"></i>
                </button>
            </div>
        </form>

        <div class="mt-4 pt-2 border-top border-light">
            <p class="text-muted small mb-0">Belum punya akun? <a href="registrasi.php" style="color: #D4AF37;" class="fw-semibold text-decoration-none">Daftar di sini</a></p>
            <a href="index.php" class="d-inline-block mt-3 text-secondary small text-decoration-none"><i class="bi bi-house-door me-1"></i> Kembali ke Beranda</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>