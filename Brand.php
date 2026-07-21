<?php
// 1. MENGAKTIFKAN SESSION DI BARIS PALING ATAS
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hubungkan ke file fungsi utama untuk database
require 'fungsi.php';

// 2. CEK STATUS LOGIN & MENGAMBIL NAMA USER
$is_login = isset($_SESSION['username']) || isset($_SESSION['nama']) || isset($_SESSION['id_user']);
$nama_tampilan = $_SESSION['nama'] ?? $_SESSION['username'] ?? 'Profil';
$id_user = $_SESSION['id_user'] ?? null;

// LOGIKA PROSES TAMBAH KE KERANJANG
if (isset($_POST['tambah_keranjang'])) {
    if (!$is_login) {
        echo "<script>alert('Silakan login terlebih dahulu untuk menambah barang ke keranjang!'); window.location.href='login.php';</script>";
        exit;
    }

    $id_produk = (int) $_POST['id_produk'];
    $jumlah = (int) ($_POST['jumlah'] ?? 1);

    // Cek apakah produk sudah ada di keranjang user tersebut
    $cek_keranjang = query("SELECT * FROM tb_keranjang WHERE id_user = '$id_user' AND id_produk = '$id_produk'");

    if (!empty($cek_keranjang)) {
        // Jika sudah ada, update jumlahnya
        $query_input = "UPDATE tb_keranjang SET jumlah = jumlah + $jumlah WHERE id_user = '$id_user' AND id_produk = '$id_produk'";
    } else {
        // Jika belum ada, masukkan data baru
        $query_input = "INSERT INTO tb_keranjang (id_user, id_produk, jumlah) VALUES ('$id_user', '$id_produk', '$jumlah')";
    }

    // Eksekusi query (Menggunakan koneksi dari fungsi.php)
    if (mysqli_query($koneksi, $query_input)) {
        echo "<script>alert('Produk berhasil ditambahkan ke keranjang!'); window.location.href='Brand.php';</script>";
        exit;
    } else {
        echo "<script>alert('Gagal menambahkan ke keranjang.');</script>";
    }
}

// Mengambil jumlah notifikasi aktif
$notif_count = query("SELECT COUNT(*) as total FROM tb_notifikasi");
$total_notif = $notif_count[0]['total'] ?? 0;

// Mengambil total item di keranjang user
$total_keranjang = 0;
if ($is_login && $id_user) {
    $keranjang_count = query("SELECT SUM(jumlah) as total FROM tb_keranjang WHERE id_user = '$id_user'");
    $total_keranjang = $keranjang_count[0]['total'] ?? 0;
}

// Logika Pencarian & Filter Produk
if (isset($_GET['cari']) && !empty(trim($_GET['cari']))) {
    $keyword = $_GET['cari'];
    $produk = query("SELECT * FROM tb_produk WHERE (nama_produk LIKE '%$keyword%' OR deskripsi LIKE '%$keyword%') ORDER BY stok DESC, id_produk DESC");
} else {
    $produk = query("SELECT * FROM tb_produk ORDER BY stok DESC, id_produk DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Brand - NFahira Boutique</title>
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
            background-color: #fcfbf7;
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
            margin: 0 auto 3px auto;
            transition: all 0.3s ease;
        }
        .nav-custom-link:hover .login-circle-btn {
            background-color: #ffffff;
        }
        .nav-custom-link:hover .login-circle-btn i {
            color: #D4AF37 !important;
        }

        .product-card {
            border: none;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.08) !important;
        }
        .product-img {
            height: 280px;
            object-fit: cover;
            width: 100%;
        }

        /* Tampilan khusus untuk produk yang SOLD OUT */
        .sold-out-img {
            filter: grayscale(80%) opacity(0.65);
        }
        .badge-sold {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #dc3545;
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 6px 12px;
            border-radius: 50rem;
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
            z-index: 2;
        }
    </style>
</head>
<body>

    <!-- NAVBAR UTAMA -->
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
                        <input type="text" name="cari" class="form-control bg-light border-0 ps-3" placeholder="Cari pakaian impianmu..." style="font-size: 0.9rem;" value="<?= isset($_GET['cari']) ? htmlspecialchars($_GET['cari']) : ''; ?>">
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
                        <a class="nav-link nav-custom-link active" href="Brand.php">
                            <i class="bi bi-tags-fill d-block fs-5 mb-1"></i>
                            <span>Brand</span>
                        </a>
                    </li>
                    <!-- MENU KERANJANG -->
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

    <!-- KONTEN PRODUK -->
    <div class="container py-5">
        <div class="mb-4">
            <?php if (isset($_GET['cari']) && !empty(trim($_GET['cari']))): ?>
                <h2 class="fw-bold text-dark">Hasil Pencarian: "<span class="text-secondary"><?= htmlspecialchars($_GET['cari']); ?></span>"</h2>
                <a href="Brand.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 mt-1"><i class="bi bi-x-circle me-1"></i> Bersihkan Pencarian</a>
            <?php else: ?>
                <h2 class="fw-bold text-dark">Semua Koleksi Pakaian</h2>
                <p class="text-muted small">Jelajahi berbagai model dress premium terbaik di NFahira Boutique.</p>
            <?php endif; ?>
        </div>

        <div class="row g-4 mt-2">
            <?php if (empty($produk)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-bag-x text-muted" style="font-size: 4rem;"></i>
                    <h4 class="fw-bold mt-3 text-secondary">Pakaian Tidak Ditemukan</h4>
                </div>
            <?php else: ?>
                <?php foreach ($produk as $prod): 
                    $nama = $prod['nama_produk'] ?? 'Koleksi Dress';
                    $harga = isset($prod['harga']) ? "Rp " . number_format($prod['harga'], 0, ',', '.') : 'Hubungi Admin';
                    $deskripsi = $prod['deskripsi'] ?? '';
                    $stok = (int) ($prod['stok'] ?? 0);
                    $file_db = trim($prod['foto'] ?? ''); 

                    $nama_file_biasa = strtolower(trim($nama)); 

                    if (!empty($file_db) && file_exists($file_db)) {
                        $path_gambar = $file_db;
                    } elseif (file_exists($nama_file_biasa . ".jpeg")) {
                        $path_gambar = $nama_file_biasa . ".jpeg";
                    } elseif (file_exists($nama_file_biasa . ".jpg")) {
                        $path_gambar = $nama_file_biasa . ".jpg";
                    } elseif (file_exists($nama_file_biasa . ".png")) {
                        $path_gambar = $nama_file_biasa . ".png";
                    } else {
                        $path_gambar = $file_db; 
                    }
                ?>
                    <div class="col-6 col-md-4 col-lg-3">
                        <div class="card product-card h-100 shadow-sm bg-white">
                            
                            <!-- BADGE SOLD OUT JIKA STOK HABIS -->
                            <?php if ($stok <= 0): ?>
                                <span class="badge-sold"><i class="bi bi-x-circle-fill me-1"></i> SOLD OUT</span>
                            <?php endif; ?>

                            <img src="<?= htmlspecialchars($path_gambar); ?>" class="product-img <?= ($stok <= 0) ? 'sold-out-img' : ''; ?>" alt="<?= htmlspecialchars($nama); ?>">
                            
                            <div class="card-body d-flex flex-column p-3">
                                <h5 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 1rem;"><?= htmlspecialchars($nama); ?></h5>
                                <p class="text-muted small text-truncate mb-2" style="font-size: 0.85rem;"><?= htmlspecialchars($deskripsi); ?></p>
                                
                                <p class="fw-semibold mt-auto mb-3 text-end" style="color: <?= ($stok <= 0) ? '#6c757d' : '#D4AF37'; ?>; font-size: 1.05rem;">
                                    <?= $harga; ?>
                                </p>

                                <!-- FORM TAMBAH KERANJANG -->
                                <?php if ($stok > 0): ?>
                                    <form action="Brand.php" method="POST">
                                        <input type="hidden" name="id_produk" value="<?= $prod['id_produk']; ?>">
                                        <input type="hidden" name="jumlah" value="1">
                                        <button type="submit" name="tambah_keranjang" class="btn btn-sm navbar-custom text-white w-100 rounded-pill py-2 shadow-sm fw-semibold" style="font-size: 0.85rem;">
                                            <i class="bi bi-cart-plus me-1"></i> Tambah Keranjang
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary w-100 rounded-pill py-2 shadow-sm fw-semibold opacity-75" disabled style="font-size: 0.85rem;">
                                        <i class="bi bi-slash-circle me-1"></i> Stok Habis
                                    </button>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>