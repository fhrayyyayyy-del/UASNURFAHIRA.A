<?php
// 1. Hubungkan ke file fungsi utama toko Anda
require 'fungsi.php';

// Memulai session untuk mengecek status login pembeli
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* 
  2. PROTEKSI HALAMAN (WAJIB LOGIN SEBELUM MEMESAN)
  Jika pembeli belum login, otomatis ditendang ke login.php
*/
$is_login = isset($_SESSION['username']) || isset($_SESSION['user']) || isset($_SESSION['nama']) || isset($_SESSION['id_user']);

if (!$is_login) {
    echo "<script>
            alert('Silakan login terlebih dahulu untuk melakukan pemesanan!');
            window.location.href = 'login.php';
          </script>";
    exit();
}

// Menentukan nama tampilan untuk navbar dan pencatatan database
$nama_tampilan = $_SESSION['nama'] ?? $_SESSION['username'] ?? $_SESSION['user'] ?? 'Profil';
$id_user       = $_SESSION['id_user'] ?? $_SESSION['id'] ?? 0;

// 3. AMBIL JUMLAH KERANJANG & NOTIFIKASI SECARA AMAN
$keranjang_count = query("SELECT SUM(jumlah) as total FROM tb_keranjang WHERE id_user = '$id_user'");
$total_keranjang = $keranjang_count[0]['total'] ?? 0;

$notif_count = query("SELECT COUNT(*) as total FROM tb_notifikasi");
$total_notif = $notif_count[0]['total'] ?? 0;

// 4. MENGAMBIL DATA PRODUK DARI DATABASE
$produk_toko = query("SELECT * FROM tb_produk ORDER BY nama_produk ASC");

// Nomor Admin WhatsApp Resmi
$nomorAdmin = "6283846715845";

// Gunakan koneksi database global/pusat dari fungsi.php jika ada, atau buat fallback koneksi
if (!isset($koneksi) || !$koneksi) {
    $koneksi = mysqli_connect("localhost", "root", "", "fahira_boutique");
    if (!$koneksi) {
        die("Koneksi database gagal: " . mysqli_connect_error());
    }
}

// 5. LOGIKA KIRIM FORM PEMESANAN
if (isset($_POST['submit_pesanan'])) {
    
    $nama        = trim($_POST['nama_lengkap'] ?? '');
    $nohp        = trim($_POST['nomor_hp'] ?? '');
    $produkRaw   = trim($_POST['kategori_produk'] ?? '');
    $alamat      = trim($_POST['alamat'] ?? '');

    $produkData  = explode('|', $produkRaw);
    $namaProduk  = $produkData[0] ?? 'Produk Boutique';
    $hargaText   = $produkData[1] ?? '0';

    $totalHarga  = (int) preg_replace('/[^0-9]/', '', $hargaText);
    $kodePesanan = "NFA-" . rand(1000, 9999);

    // A. SIMPAN KE TABEL tb_pesanan
    $stmt1 = mysqli_prepare($koneksi, "INSERT INTO tb_pesanan (id_user, kode_pesanan, tanggal_pesan, total_harga, status) VALUES (?, ?, NOW(), ?, 'Diproses')");
    mysqli_stmt_bind_param($stmt1, "isi", $id_user, $kodePesanan, $totalHarga);
    mysqli_stmt_execute($stmt1);
    mysqli_stmt_close($stmt1);

    // B. SIMPAN KE TABEL tb_notifikasi
    $isi_pesan = "Pemesan: $nama ($nohp) - Kode: $kodePesanan - Produk: $namaProduk - Harga: $hargaText - Alamat: $alamat";
    $judul_notif = "Pesanan Baru ($kodePesanan)";
    
    $stmt2 = mysqli_prepare($koneksi, "INSERT INTO tb_notifikasi (judul, pesan, tanggal) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($stmt2, "ss", $judul_notif, $isi_pesan);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    // C. FORMAT TEKS WHATSAPP
    $teks = "*HALO NFAHIRA BOUTIQUE, SAYA INGIN MEMESAN BAJU*\n\n" .
            "📦 _Kode Pesanan:_ *" . $kodePesanan . "*\n\n" .
            "✍️ _Data Lengkap Pemesan:_\n" .
            "• Nama : " . $nama . "\n" .
            "• No. HP : " . $nohp . "\n\n" .
            "👗 _Detail Produk:_\n" .
            "• Produk : " . $namaProduk . "\n" .
            "• Harga : *" . $hargaText . "*\n\n" .
            "📍 _Alamat Pengiriman:_\n" .
            "• " . $alamat . "\n\n" .
            "Mohon segera dikonfirmasi ketersediaan barangnya, Terima kasih! ✨";

    $urlTujuan = "https://api.whatsapp.com/send?phone=" . $nomorAdmin . "&text=" . urlencode($teks);

    echo "<script>
            alert('Pesanan Kode $kodePesanan berhasil dicatat ke sistem! Mengarahkan ke WhatsApp...');
            window.open('$urlTujuan', '_blank');
            window.location.href = 'Notifikasi.php';
          </script>";
    exit();
}

// 6. LOGIKA KIRIM FORM PEMBATALAN
if (isset($_POST['submit_pembatalan'])) {
    
    $nama_batal  = trim($_POST['nama_batal'] ?? '');
    $nohp_batal  = trim($_POST['nohp_batal'] ?? '');
    $kode_pesan  = trim($_POST['kode_pesanan'] ?? '');
    $alasan      = trim($_POST['alasan_pembatalan'] ?? '');

    // A. UPDATE STATUS DI TABEL tb_pesanan
    $stmt_update = mysqli_prepare($koneksi, "UPDATE tb_pesanan SET status = 'Dibatalkan' WHERE kode_pesanan = ?");
    mysqli_stmt_bind_param($stmt_update, "s", $kode_pesan);
    mysqli_stmt_execute($stmt_update);
    mysqli_stmt_close($stmt_update);

    // B. SIMPAN KE TABEL tb_notifikasi
    $isi_notif_batal = "PEMBATALAN - Nama: $nama_batal ($nohp_batal) - Kode Pesanan: $kode_pesan - Alasan: $alasan";
    $judul_batal = "Pembatalan Pesanan ($kode_pesan)";
    
    $stmt_batal = mysqli_prepare($koneksi, "INSERT INTO tb_notifikasi (judul, pesan, tanggal) VALUES (?, ?, NOW())");
    mysqli_stmt_bind_param($stmt_batal, "ss", $judul_batal, $isi_notif_batal);
    mysqli_stmt_execute($stmt_batal);
    mysqli_stmt_close($stmt_batal);

    // C. FORMAT TEKS WHATSAPP
    $teks_batal = "*HALO ADMIN NFAHIRA BOUTIQUE, SAYA INGIN MEMBATALKAN PESANAN*\n\n" .
                  "❌ _Data Pembatalan Pesanan:_\n" .
                  "• Nama Pemesan : " . $nama_batal . "\n" .
                  "• No. HP / WA : " . $nohp_batal . "\n" .
                  "• Kode / ID Pesanan : *" . $kode_pesan . "*\n\n" .
                  "💬 _Alasan Pembatalan:_\n" .
                  "\"" . $alasan . "\"\n\n" .
                  "Mohon bantuan Admin untuk memproses pembatalan ini. Terima kasih! 🙏";

    $urlBatal = "https://api.whatsapp.com/send?phone=" . $nomorAdmin . "&text=" . urlencode($teks_batal);

    echo "<script>
            alert('Pengajuan pembatalan dikirim ke sistem! Mengarahkan ke WhatsApp Admin...');
            window.open('$urlBatal', '_blank');
            window.location.href = 'Notifikasi.php';
          </script>";
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order & Contact - NFahira Boutique</title>
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
        .contact-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            background-color: #ffffff;
        }
        .contact-icon-box {
            width: 50px;
            height: 50px;
            background-color: #fffdf5;
            border: 1px solid #f3e9c7;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #D4AF37;
            font-size: 1.3rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #D4AF37;
            box-shadow: 0 0 0 0.25rem rgba(212, 175, 55, 0.25);
        }
        .btn-order {
            background-color: #25D366;
            color: white;
            font-weight: 600;
            border: none;
            transition: background-color 0.2s;
        }
        .btn-order:hover {
            background-color: #20ba5a;
            color: white;
        }
        .btn-batal {
            background-color: #dc3545;
            color: white;
            font-weight: 600;
            border: none;
            transition: background-color 0.2s;
        }
        .btn-batal:hover {
            background-color: #bb2d3b;
            color: white;
        }
    </style>
</head>
<body class="bg-light text-dark">

    <!-- NAVBAR DENGAN FITUR LOGIN/PROFIL DINAMIS & KERANJANG -->
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
                        <a class="nav-link nav-custom-link active" href="contac.php">
                            <i class="bi bi-telephone-fill d-block fs-5 mb-1"></i>
                            <span>Contact</span>
                        </a>
                    </li>
                    
                    <li class="nav-item text-center">
                        <?php if ($is_login): ?>
                            <a class="nav-link nav-custom-link" href="dashboard_konsumen.php">
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

    <!-- KONTEN FORM PEMESANAN & PEMBATALAN -->
    <div class="container py-5" style="max-width: 900px;">
        
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h1 class="fw-bold text-dark m-0">Pemesanan & Kontak</h1>
            <a href="index.php" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
            </a>
        </div>

        <!-- NAV TAB PILIHAN FORM -->
        <ul class="nav nav-pills nav-fill mb-4 bg-white p-2 rounded-4 shadow-sm" id="formTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold rounded-pill py-2" id="order-tab" data-bs-toggle="tab" data-bs-target="#order-form" type="button" role="tab">
                    <i class="bi bi-bag-heart me-1"></i> Form Pemesanan Cepat
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-danger fw-semibold rounded-pill py-2" id="cancel-tab" data-bs-toggle="tab" data-bs-target="#cancel-form" type="button" role="tab">
                    <i class="bi bi-x-circle me-1"></i> Form Pembatalan Pesanan
                </button>
            </li>
        </ul>

        <div class="tab-content" id="formTabContent">
            
            <!-- 1. FORM PEMESANAN CEPAT -->
            <div class="tab-pane fade show active" id="order-form" role="tabpanel">
                <div class="card contact-card p-4 p-md-5 mb-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-bag-heart text-warning fs-1"></i>
                        <h2 class="fw-bold mt-2" style="font-size: 1.75rem;">Form Pemesanan Cepat</h2>
                        <p class="text-muted small">Silakan isi data di bawah ini untuk memesan langsung melalui WhatsApp admin kami.</p>
                    </div>

                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nama Lengkap</label>
                                <input type="text" name="nama_lengkap" class="form-control bg-light py-2 rounded-3" placeholder="Masukkan nama Anda" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nomor HP / WhatsApp</label>
                                <input type="tel" name="nomor_hp" class="form-control bg-light py-2 rounded-3" placeholder="Contoh: 0812345xxxx" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Pilih Kategori Produk</label>
                                <select name="kategori_produk" class="form-select bg-light py-2 rounded-3" required>
                                    <option value="" disabled selected>-- Pilih Koleksi Pakaian --</option>
                                    <?php if(empty($produk_toko)): ?>
                                        <option value="MAROON INDIAN MARRIED DRESS|Rp 500.000.000">MAROON INDIAN MARRIED DRESS (Rp 500.000.000)</option>
                                        <option value="BLUE AIR MARRIED DRES|Rp 350.000.000">BLUE AIR MARRIED DRES (Rp 350.000.000)</option>
                                        <option value="PINKY DRESS|Rp 320.000.000">PINKY DRESS (Rp 320.000.000)</option>
                                        <option value="NAVY DRESS|Rp 200.000.000">NAVY DRESS (Rp 200.000.000)</option>
                                    <?php else: ?>
                                        <?php foreach($produk_toko as $prod): 
                                            $nama_p = $prod['nama_produk'] ?? 'Pakaian Boutique';
                                            $harga_p = isset($prod['harga']) ? "Rp " . number_format($prod['harga'], 0, ',', '.') : 'Hubungi Admin';
                                        ?>
                                            <option value="<?= htmlspecialchars($nama_p) . '|' . $harga_p; ?>">
                                                <?= htmlspecialchars($nama_p); ?> (<?= $harga_p; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Alamat Pengiriman Lengkap</label>
                                <textarea name="alamat" class="form-control bg-light py-2 rounded-3" rows="3" placeholder="Tuliskan alamat lengkap pengiriman rumah Anda" required></textarea>
                            </div>
                            <div class="col-12 text-center mt-4">
                                <button type="submit" name="submit_pesanan" class="btn btn-order w-100 py-2.5 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-whatsapp fs-5"></i> Kirim Pesanan via WhatsApp
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 2. FORM PEMBATALAN PESANAN -->
            <div class="tab-pane fade" id="cancel-form" role="tabpanel">
                <div class="card contact-card p-4 p-md-5 mb-5 border-start border-danger border-4">
                    <div class="text-center mb-4">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-1"></i>
                        <h2 class="fw-bold mt-2" style="font-size: 1.75rem;">Form Pembatalan Pemesanan</h2>
                        <p class="text-muted small">Ingin membatalkan pesanan? Isi formulir ini untuk pemberitahuan resmi ke WhatsApp Admin.</p>
                    </div>

                    <form action="" method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nama Lengkap Pemesan</label>
                                <input type="text" name="nama_batal" class="form-control bg-light py-2 rounded-3" placeholder="Masukkan nama sesuai pesanan" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold small">Nomor HP / WhatsApp</label>
                                <input type="tel" name="nohp_batal" class="form-control bg-light py-2 rounded-3" placeholder="Contoh: 0812345xxxx" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Kode / ID Pesanan</label>
                                <input type="text" name="kode_pesanan" class="form-control bg-light py-2 rounded-3" placeholder="Contoh: NFA-8392" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold small">Alasan Pembatalan</label>
                                <textarea name="alasan_pembatalan" class="form-control bg-light py-2 rounded-3" rows="3" placeholder="Tuliskan alasan singkat pembatalan pesanan Anda..." required></textarea>
                            </div>
                            <div class="col-12 text-center mt-4">
                                <button type="submit" name="submit_pembatalan" class="btn btn-batal w-100 py-2.5 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-x-circle-fill fs-5"></i> Konfirmasi & Batalkan Pesanan via WA
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>

        <!-- KARTU INFORMASI KONTAK BUTIK -->
        <div class="card contact-card p-4 p-md-5">
            <div class="text-center mb-4">
                <h2 class="fw-bold" style="font-size: 1.75rem;">Hubungi Kami</h2>
                <p class="text-muted small">Informasi lengkap mengenai kontak resmi dan lokasi butik fisik kami.</p>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 d-flex flex-column gap-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="contact-icon-box"><i class="bi bi-telephone-fill"></i></div>
                        <div>
                            <h4 class="fw-bold mb-1" style="font-size: 1.15rem;">Nomor Telepon</h4>
                            <p class="text-secondary m-0" style="font-size: 0.95rem;">0838-4671-5845</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="contact-icon-box"><i class="bi bi-envelope-fill"></i></div>
                        <div>
                            <h4 class="fw-bold mb-1" style="font-size: 1.15rem;">Email Resmi</h4>
                            <p class="text-secondary m-0" style="font-size: 0.95rem;">fhrayyyayyy@gmail.com</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 d-flex flex-column gap-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="contact-icon-box"><i class="bi bi-whatsapp"></i></div>
                        <div>
                            <h4 class="fw-bold mb-1" style="font-size: 1.15rem;">WhatsApp Chat</h4>
                            <p class="text-secondary m-0" style="font-size: 0.95rem;">0838-4671-5845</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-start gap-3">
                        <div class="contact-icon-box"><i class="bi bi-geo-alt-fill"></i></div>
                        <div>
                            <h4 class="fw-bold mb-1" style="font-size: 1.15rem;">Alamat Butik</h4>
                            <p class="text-secondary m-0" style="font-size: 0.95rem;">Selong, Nusa Tenggara Barat</p>
                        </div>
                    </div>
                </div>

                <hr class="mt-5 opacity-25">

                <div class="col-12 text-center mt-2">
                    <div class="d-inline-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-clock-fill text-muted fs-5"></i>
                        <h3 class="fw-bold m-0" style="font-size: 1.35rem; color: #212529;">Jam Operasional</h3>
                    </div>
                    <p class="text-secondary m-0" style="font-size: 0.95rem; font-weight: 500;">
                        Senin - Sabtu : <span style="color: #D4AF37;" class="fw-semibold">08.00 - 20.00 WITA</span>
                    </p>
                </div>
            </div>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>