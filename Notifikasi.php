<?php
// 1. Hubungkan ke file fungsi utama toko Anda
require 'fungsi.php';

// Memulai session untuk mengecek status login pembeli
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$is_login = isset($_SESSION['username']) || isset($_SESSION['user']) || isset($_SESSION['nama']) || isset($_SESSION['id_user']);
$nama_tampilan = $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['user'] ?? 'Profil';
$id_user = $_SESSION['id_user'] ?? null;

// 2. KONEKSI KE DATABASE
$koneksi = mysqli_connect("localhost", "root", "", "fahira_boutique");

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// 3. AMBIL DATA NOTIFIKASI DARI DATABASE
$query_notif = mysqli_query($koneksi, "SELECT * FROM tb_notifikasi ORDER BY tanggal DESC");
$total_notif = mysqli_num_rows($query_notif);

// 4. MENGAMBIL JUMLAH ITEM KERANJANG AKTIF (DISISIPKAN)
$total_keranjang = 0;
if ($is_login && $id_user) {
    $query_keranjang = mysqli_query($koneksi, "SELECT SUM(jumlah) as total FROM tb_keranjang WHERE id_user = '$id_user'");
    if ($query_keranjang) {
        $row_keranjang = mysqli_fetch_assoc($query_keranjang);
        $total_keranjang = $row_keranjang['total'] ?? 0;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - NFahira Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- IMPORT GOOGLE FONTS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            font-weight: 400;
            background-color: #f8f9fa;
        }
        h1, h2, h3, h4, .navbar-brand, .modal-title {
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
            text-decoration: none;
            display: block;
        }
        .nav-custom-link:hover, .nav-custom-link.active {
            color: #ffffff !important;
            font-weight: 600;
        }

        /* Styling khusus tombol login/profil lingkaran (DISISIPKAN UNTUK KONSISTENSI UI) */
        .login-circle-btn {
            width: 32px;
            height: 32px;
            background-color: rgba(255, 255, 255, 0.25);
            border: 2px solid #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 3px auto;
            transition: all 0.3s ease;
        }
        .nav-custom-link:hover .login-circle-btn {
            background-color: #ffffff;
        }
        .nav-custom-link:hover .login-circle-btn i {
            color: #D4AF37 !important;
        }

        .notif-card {
            border: none;
            border-left: 5px solid #D4AF37;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
            background-color: #ffffff;
            transition: all 0.2s;
            cursor: pointer;
        }
        .notif-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
            background-color: #fffdf6;
        }
        .notif-icon {
            color: #D4AF37;
            font-size: 1.5rem;
        }
        
        /* STYLING MODAL CUSTOM */
        .modal-custom-header {
            background-color: #D4AF37 !important;
            color: white !important;
            border-bottom: none;
        }
        .modal-custom-header .btn-close {
            filter: invert(1) grayscale(100%) brightness(200%);
        }
        .btn-mengerti {
            background-color: #D4AF37;
            color: white;
            font-weight: 500;
            border-radius: 50px;
            padding: 8px 30px;
            border: none;
            transition: background-color 0.2s;
        }
        .btn-mengerti:hover {
            background-color: #b8952e;
            color: white;
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

                    <!-- MENU KERANJANG BELANJA (DISISIPKAN) -->
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
                        <a class="nav-link nav-custom-link active position-relative" href="Notifikasi.php">
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
                    <li class="nav-item text-center" style="min-width: 50px;">
                        <?php if ($is_login): ?>
                            <a class="nav-link nav-custom-link" href="profil.php">
                                <div class="login-circle-btn shadow-sm">
                                    <i class="bi bi-person-fill text-white fs-6" style="transition: color 0.3s ease;"></i>
                                </div>
                                <span><?= htmlspecialchars($nama_tampilan); ?></span>
                            </a>
                        <?php else: ?>
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

    <!-- AREA NOTIFIKASI PEMBERITAHUAN -->
    <div class="container py-5" style="max-width: 900px;">
        
        <div class="d-flex align-items-center justify-content-between mb-5">
            <h1 class="fw-bold text-dark m-0" style="font-size: 2.2rem;">Pemberitahuan</h1>
            <a href="index.php" class="btn btn-sm btn-outline-dark rounded-pill px-4 py-2 small fw-medium">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
            </a>
        </div>

        <div class="d-flex flex-column gap-3">
            <?php if($total_notif > 0): ?>
                <?php while($row = mysqli_fetch_assoc($query_notif)): ?>
                    <!-- KARTU NOTIFIKASI BISA DIKLIK KARENA data-bs-toggle & data-bs-target -->
                    <div class="card notif-card p-4" data-bs-toggle="modal" data-bs-target="#modalNotif<?= $row['id_notifikasi'] ?? $row['id']; ?>">
                        <div class="row align-items-center g-3">
                            <div class="col-auto">
                                <i class="bi bi-bell-fill notif-icon"></i>
                            </div>
                            <div class="col">
                                <h5 class="fw-bold text-dark mb-1" style="font-size: 1.15rem;">
                                    <?= htmlspecialchars($row['judul']); ?>
                                </h5>
                                <p class="text-secondary small m-0 text-truncate" style="max-width: 500px;">
                                    <?= htmlspecialchars($row['pesan']); ?>
                                </p>
                            </div>
                            <div class="col-md-auto text-md-end text-start text-muted small">
                                <span><i class="bi bi-calendar3 me-1"></i> <?= date('d M Y', strtotime($row['tanggal'])); ?></span>
                            </div>
                        </div>
                    </div>

                    <!-- MODAL POP UP DETAIL UNTUK TIAP NOTIFIKASI -->
                    <div class="modal fade" id="modalNotif<?= $row['id_notifikasi'] ?? $row['id']; ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
                            <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
                                <div class="modal-header modal-custom-header py-3 px-4">
                                    <h5 class="modal-title fw-bold fs-5 d-flex align-items-center gap-2">
                                        <i class="bi bi-info-circle-fill"></i> Detail Informasi
                                    </h5>
                                    <!-- PERBAIKAN: data-bs-dismiss="modal" untuk tombol close modal -->
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body text-center p-5">
                                    <div class="mb-4" style="color: #D4AF37; font-size: 4rem; line-height: 1;">
                                        <i class="bi bi-envelope-heart"></i>
                                    </div>
                                    <h3 class="fw-bold text-dark mb-3" style="font-size: 1.6rem;"><?= htmlspecialchars($row['judul']); ?></h3>
                                    <p class="text-muted mb-4 px-2" style="font-size: 0.95rem; line-height: 1.6;">
                                        <?= htmlspecialchars($row['pesan']); ?>
                                    </p>
                                    <button type="button" class="btn btn-mengerti shadow-sm" data-bs-dismiss="modal">Mengerti</button>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 bg-white rounded-3 shadow-sm border border-light">
                    <i class="bi bi-bell-slash text-muted fs-1 mb-2 d-block"></i>
                    <h5 class="fw-semibold text-secondary">Belum ada pemberitahuan baru</h5>
                    <p class="text-muted small">Semua riwayat pesanan baju akan tampil di halaman ini.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>