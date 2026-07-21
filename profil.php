<?php
// 1. Hubungkan ke file fungsi utama toko Anda
require 'fungsi.php';

// Memulai session untuk mengecek status login pembeli
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* 
  2. PROTEKSI HALAMAN (WAJIB LOGIN)
  Jika pembeli belum login, otomatis ditendang ke login.php
*/
$is_login = isset($_SESSION['username']) || isset($_SESSION['user']) || isset($_SESSION['nama']) || isset($_SESSION['id_user']);

if (!$is_login) {
    echo "<script>
            alert('Silakan login terlebih dahulu untuk melihat profil!');
            window.location.href = 'login.php';
          </script>";
    exit();
}

// Menentukan nama tampilan untuk navbar dan profil
$nama_tampilan = $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['user'] ?? 'Pelanggan';
$username_aktif = $_SESSION['username'] ?? $_SESSION['user'] ?? '';

// 3. AMBIL JUMLAH NOTIFIKASI SECARA AMAN
$notif_count = query("SELECT COUNT(*) as total FROM tb_notifikasi");
$total_notif = $notif_count[0]['total'] ?? 0;

// 4. AMBIL DATA DETAIL USER DARI DATABASE tb_user
// Mengasumsikan Anda menyimpan username di session untuk mencarinya di database
$koneksi = mysqli_connect("localhost", "root", "", "fahira_boutique");
$user_data = [];
if ($koneksi && !empty($username_aktif)) {
    $username_clean = mysqli_real_escape_string($koneksi, $username_aktif);
    $result = mysqli_query($koneksi, "SELECT * FROM tb_user WHERE username = '$username_clean' LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        $user_data = mysqli_fetch_assoc($result);
    }
}

// Fallback data jika kolom di database tb_user Anda berbeda
$email_user = $user_data['email'] ?? 'Belum diatur';
$nama_lengkap_user = $user_data['nama'] ?? $nama_tampilan;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - NFahira Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- IMPORT GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
        }
        h1, h2, h3, h4, .navbar-brand {
            font-family: 'Playfair Display', serif !important;
            letter-spacing: 1px;
        }
        .navbar-custom { 
            background-color: #D4AF37; 
        }
        .nav-custom-link {
            font-family: 'Montserrat', sans-serif;
            font-size: 0.9rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85) !important;
            transition: color 0.2s;
        }
        .nav-custom-link:hover, .nav-custom-link.active {
            color: #ffffff !important;
            font-weight: 600;
        }
        .profile-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            background-color: #f3e9c7;
            color: #D4AF37;
            font-size: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-light text-dark">

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-custom border-bottom sticky-top py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <div class="rounded-circle shadow-sm" style="height: 44px; width: 44px; border: 2px solid #ffffff; background-image: url('Logo.jpeg'); background-position: center; background-size: 165%; background-repeat: no-repeat;"></div>
                <span class="fw-bold text-white text-uppercase" style="letter-spacing: 2px; font-size: 1.25rem;">NFahira Boutique</span>
            </a>

            <button class="navbar-toggler border-white text-white" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                <span class="navbar-toggler-icon" style="filter: invert(1);"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarContent">
                <form action="Brand.php" method="GET" class="d-flex mx-auto search-box my-3 my-lg-0" style="max-width: 400px; width: 100%;">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden" style="width: 100%;">
                        <input type="text" name="cari" class="form-control bg-light border-0 ps-3" placeholder="Cari pakaian impianmu..." style="font-size: 0.9rem;">
                        <button class="btn btn-white bg-light border-0 pe-3" type="submit">
                            <i class="bi bi-search text-muted"></i>
                        </button>
                    </div>
                </form>

                <ul class="navbar-nav ms-auto align-items-center gap-3">
                    <li class="nav-item text-center">
                        <a class="nav-link nav-custom-link" href="index.php">
                            <i class="bi bi-house-door-fill d-block fs-5 mb-1"></i>
                            <span>Beranda</span>
                        </a>
                    </li>
                    <li class="nav-item text-center">
                        <a class="nav-link nav-custom-link" href="Brand.php">
                            <i class="bi bi-tags-fill d-block fs-5 mb-1"></i>
                            <span>Brand</span>
                        </a>
                    </li>
                    <li class="nav-item text-center">
                        <a class="nav-link nav-custom-link position-relative" href="Notifikasi.php">
                            <i class="bi bi-bell-fill d-block fs-5 mb-1"></i>
                            <span>Notifikasi</span>
                            <?php if($total_notif > 0): ?>
                                <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; padding: 2px 5px;">
                                    <?= $total_notif; ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li class="nav-item text-center">
                        <a class="nav-link nav-custom-link" href="contac.php">
                            <i class="bi bi-telephone-fill d-block fs-5 mb-1"></i>
                            <span>Contact</span>
                        </a>
                    </li>
                    <li class="nav-item text-center">
                        <!-- Baris 93 yang aman dari syntax error karena if-else terstruktur rapi -->
                        <?php if ($is_login): ?>
                            <a class="nav-link nav-custom-link active" href="profil.php">
                                <i class="bi bi-person-circle d-block fs-5 mb-1"></i>
                                <span><?= htmlspecialchars($nama_tampilan); ?></span>
                            </a>
                        <?php else: ?>
                            <a class="nav-link nav-custom-link" href="login.php">
                                <i class="bi bi-person-fill d-block fs-5 mb-1"></i>
                                <span>Login</span>
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA PROFIL -->
    <div class="container py-5" style="max-width: 600px;">
        <div class="card profile-card p-4 text-center">
            <div class="profile-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            
            <h2 class="fw-bold mb-1"><?= htmlspecialchars($nama_lengkap_user); ?></h2>
            <p class="text-muted small mb-4">@<?= htmlspecialchars($username_aktif); ?></p>
            
            <hr class="opacity-25 mb-4">
            
            <div class="text-start mb-4">
                <div class="mb-3">
                    <label class="text-muted small fw-semibold d-block">Nama Lengkap</label>
                    <span class="fw-medium text-dark"><?= htmlspecialchars($nama_lengkap_user); ?></span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small fw-semibold d-block">Email</label>
                    <span class="fw-medium text-dark"><?= htmlspecialchars($email_user); ?></span>
                </div>
                <div class="mb-3">
                    <label class="text-muted small fw-semibold d-block">Status Akun</label>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 small">Aktif (Pembeli)</span>
                </div>
            </div>
            
            <div class="d-grid gap-2">
                <a href="logout.php" class="btn btn-outline-danger rounded-pill py-2 fw-semibold shadow-sm" onclick="return confirm('Apakah Anda yakin ingin logout?');">
                    <i class="bi bi-box-arrow-right me-1"></i> Keluar Dari Akun
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>