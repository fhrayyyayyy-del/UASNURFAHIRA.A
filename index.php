<?php
// 1. MENGAKTIFKAN SESSION DI BARIS PALING ATAS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. CEK STATUS LOGIN & MENGAMBIL NAMA USER
$is_login = isset($_SESSION['username']) || isset($_SESSION['nama']) || isset($_SESSION['id_user']);
$nama_tampilan = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Profil';
$id_user = $_SESSION['id_user'] ?? null;

// Hubungkan ke file fungsi utama untuk database
require 'fungsi.php';

// Mengambil jumlah notifikasi aktif untuk ikon lonceng di navbar
$notif_count = query("SELECT COUNT(*) as total FROM tb_notifikasi");
$total_notif = $notif_count[0]['total'] ?? 0;

// MENGAMBIL JUMLAH ITEM KERANJANG AKTIF
$total_keranjang = 0;
if ($is_login && $id_user) {
    $keranjang_count = query("SELECT SUM(jumlah) as total FROM tb_keranjang WHERE id_user = '$id_user'");
    $total_keranjang = $keranjang_count[0]['total'] ?? 0;
}

// Mengambil gambar toko dari tb_banner agar dinamis
$banner = query("SELECT * FROM tb_banner LIMIT 1");
$foto_toko = "";

if (!empty($banner)) {
    $foto_toko = $banner[0]['gambar'] ?? $banner[0]['foto'] ?? "";
}

// Cek jalur file fotonya
if (!empty($foto_toko) && file_exists("uploads/" . $foto_toko)) {
    $path_toko = "uploads/" . $foto_toko;
} elseif (!empty($foto_toko) && file_exists($foto_toko)) {
    $path_toko = $foto_toko;
} else {
    if (file_exists("toko.jpeg")) {
        $path_toko = "toko.jpeg";
    } elseif (file_exists("toko.jpg")) {
        $path_toko = "toko.jpg";
    } else {
        $path_toko = "uploads/6a595d86c4fea.jpeg"; 
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kerangka Pemrograman - NFahira Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- IMPORT GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,300;0,400;0,500;0,600;1,300&family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
            background-color: #fcfbf7; 
        }

        .display-heading {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #2c2c2c;
            letter-spacing: 0.5px;
            line-height: 1.3;
        }

        .gold-accent-text {
            color: #C5A028; 
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
            letter-spacing: 2px;
            font-size: 0.85rem;
        }

        .luxury-quote {
            font-family: 'Playfair Display', serif;
            font-style: italic;
            color: #555555;
            border-left: 3px solid #D4AF37;
            padding-left: 20px;
            font-size: 1.15rem;
            margin: 25px 0;
        }

        .desc-text {
            color: #666666;
            font-size: 0.95rem;
            line-height: 1.8;
            font-weight: 300;
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
            text-decoration: none;
            display: block;
        }
        .nav-custom-link:hover, .nav-custom-link.active {
            color: #ffffff !important;
            font-weight: 600;
        }

        /* Styling khusus tombol login lingkaran dengan teks di bawah */
        .login-circle-btn {
            width: 32px;
            height: 32px;
            background-color: rgba(255, 255, 255, 0.25);
            border: 2px solid #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 3px auto; /* Membuat posisi center dan memberi jarak ke teks bawah */
            transition: all 0.3s ease;
        }
        .nav-custom-link:hover .login-circle-btn {
            background-color: #ffffff;
        }
        .nav-custom-link:hover .login-circle-btn i {
            color: #D4AF37 !important;
        }

        .boutique-img-wrapper {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 15px 35px rgba(0,0,0,0.07) !important;
            transition: transform 0.4s ease;
        }
        .boutique-img-wrapper:hover {
            transform: scale(1.02);
        }

        .footer-note {
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            letter-spacing: 3px;
            font-size: 0.75rem;
            color: #999999;
        }
    </style>
</head>
<body>

    <!-- NAVBAR UTAMA -->
    <nav class="navbar navbar-expand-lg navbar-custom border-bottom sticky-top py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <div class="rounded-circle shadow-sm" style="height: 44px; width: 44px; border: 2px solid #ffffff; background-image: url('Logo.jpeg'); background-position: center; background-size: 165%; background-repeat: no-repeat;"></div>
                <span class="fw-bold text-white text-uppercase" style="letter-spacing: 2px; font-size: 1.25rem; font-family: 'Playfair Display', serif !important;">NFahira Boutique</span>
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
                        <a class="nav-link nav-custom-link active" href="index.php">
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

                    <!-- MENU KERANJANG DISISIPKAN DI SINI -->
                    <li class="nav-item text-center">
                        <a class="nav-link nav-custom-link position-relative" href="keranjang.php">
                            <i class="bi bi-cart-fill d-block fs-5 mb-1"></i>
                            <span>Keranjang</span>
                            <?php if($total_keranjang > 0): ?>
                                <span class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; padding: 2px 5px;">
                                    <?= $total_keranjang; ?>
                                </span>
                            <?php endif; ?>
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
                    
                    <!-- TOMBOL LOGIN/PROFIL -->
                    <li class="nav-item text-center" style="min-width: 50px;">
                        <?php if ($is_login): ?>
                            <!-- JIKA USER SUDAH LOGIN: Teks berubah menjadi nama user dan mengarah ke profil.php -->
                            <a class="nav-link nav-custom-link" href="profil.php">
                                <div class="login-circle-btn shadow-sm">
                                    <i class="bi bi-person-fill text-white fs-6" style="transition: color 0.3s ease;"></i>
                                </div>
                                <span><?= htmlspecialchars($nama_tampilan); ?></span>
                            </a>
                        <?php else: ?>
                            <!-- JIKA BELUM LOGIN: Teks default 'Login' dan mengarah ke login.php -->
                            <a class="nav-link nav-custom-link" href="login.php">
                                <div class="login-circle-btn shadow-sm">
                                    <i class="bi bi-person-fill text-white fs-6" style="transition: color 0.3s ease;"></i>
                                </div>
                                <span>Login</span>
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="container py-5 my-3">
        <div class="row align-items-center g-5">
            
            <div class="col-lg-6">
                <span class="gold-accent-text text-uppercase mb-2 d-block">Welcome to Luxury</span>
                <h1 class="display-heading mb-4">Selamat Datang di<br>NFahira Boutique</h1>
                
                <p class="desc-text mb-3">
                    Kami percaya bahwa setiap wanita memiliki kecantikan yang unik. Tampil anggun, percaya diri, dan memukau di setiap kesempatan berharga Anda.
                </p>

                <div class="luxury-quote">
                    "Elegance is not about being noticed, it's about being remembered."
                </div>

                <p class="desc-text mb-4">
                    Temukan koleksi dress premium terbaik yang dikurasi khusus untuk mencerminkan kepribadian, keanggunan, dan pesona terbaik Anda. Nikmati pengalaman berbelanja yang nyaman bersama kami.
                </p>

                <div class="pt-2">
                    <a href="Brand.php" class="btn btn-dark rounded-pill px-4 py-2.5 shadow-sm text-uppercase fw-semibold" style="font-size: 0.8rem; letter-spacing: 1px; background-color: #2c2c2c;">
                        Lihat Koleksi <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="boutique-img-wrapper">
                    <img src="<?= $path_toko; ?>" class="w-100 d-block" alt="NFahira Boutique Store" style="object-fit: cover; height: 420px;">
                </div>
            </div>

        </div>

        <div class="row mt-5 pt-5 border-top border-light">
            <div class="col-12 text-center">
                <p class="footer-note text-uppercase mb-0">Your Style, Your Story.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>