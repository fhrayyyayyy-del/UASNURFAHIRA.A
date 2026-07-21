<?php
require 'fungsi.php';

if (isset($_POST['register'])) {
    // Jalankan fungsi registrasi dari fungsi.php
    if (registrasi($_POST) > 0) {
        echo "<script>
                alert('Akun berhasil dibuat! Silakan login.');
                document.location.href = 'login.php';
              </script>";
    } else {
        echo mysqli_error($conn);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - NFahira Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #fcfbf7; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .card-auth { width: 100%; max-width: 450px; border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; background: #ffffff; }
        .header-auth { background-color: #D4AF37; padding: 30px 20px; text-align: center; color: white; }
        .header-auth h3 { font-family: 'Playfair Display', serif; font-weight: 700; letter-spacing: 1px; margin-top: 10px; }
        .btn-dark-custom { background-color: #2c2c2c; color: white; border-radius: 50px; font-weight: 600; letter-spacing: 1px; padding: 12px; transition: all 0.3s; }
        .btn-dark-custom:hover { background-color: #1a1a1a; color: white; }
        .input-group-text { background-color: #f8f9fa; border-right: none; }
        .form-control { border-left: none; }
        .form-control:focus { box-shadow: none; border-color: #dee2e6; }
    </style>
</head>
<body>

<div class="card-auth my-5">
    <div class="header-auth">
        <div class="rounded-circle mx-auto shadow-sm" style="height: 60px; width: 60px; border: 2px solid #ffffff; background-image: url('Logo.jpeg'); background-position: center; background-size: cover;"></div>
        <h3>NFAHIRA BOUTIQUE</h3>
        <p class="mb-0 small opacity-75">Buat akun belanja baru Anda</p>
    </div>
    <div class="card-body p-4">
        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                <div class="input-group">
                    <span class="input-group-text text-muted"><i class="bi bi-card-text"></i></span>
                    <input type="text" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                </div>
            </div>
            
            <!-- INPUT EMAIL DI SINI -->
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Email</label>
                <div class="input-group">
                    <span class="input-group-text text-muted"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="Masukkan alamat email" required>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary">Username</label>
                <div class="input-group">
                    <span class="input-group-text text-muted"><i class="bi bi-person"></i></span>
                    <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold text-secondary">Password</label>
                <div class="input-group">
                    <span class="input-group-text text-muted"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                </div>
            </div>

            <button type="submit" name="register" class="btn btn-dark-custom w-100 text-uppercase mb-3">Daftar Akun <i class="bi bi-arrow-right ms-1"></i></button>
            
            <div class="text-center small mt-4">
                Sudah punya akun? <a href="login.php" style="color: #D4AF37; text-decoration: none;" class="fw-semibold">Masuk disini</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>