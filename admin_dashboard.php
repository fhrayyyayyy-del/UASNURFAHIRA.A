<?php
session_start();
$koneksi = mysqli_connect("localhost", "root", "", "fahira_boutique");

if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// 1. PROTEKSI ADMIN (Wajib login sebagai admin)
if (!isset($_SESSION['admin_login'])) {
    echo "<script>
            alert('Silakan login sebagai admin terlebih dahulu!');
            window.location.href = 'login_admin.php';
          </script>";
    exit();
}

$nama_admin = $_SESSION['admin_nama'] ?? 'Admin Boutique';

// 2. PROSES FORM TAMBAH KATEGORI
if (isset($_POST['tambah_kategori'])) {
    $nama_kategori = mysqli_real_escape_string($koneksi, $_POST['nama_kategori']);
    $query = "INSERT INTO tb_kategori (nama_kategori) VALUES ('$nama_kategori')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Kategori berhasil ditambahkan!'); window.location.href='admin_dashboard.php?tab=kategori';</script>";
    }
}

// 3. PROSES FORM TAMBAH BRAND
if (isset($_POST['tambah_brand'])) {
    $nama_brand = mysqli_real_escape_string($koneksi, $_POST['nama_brand']);
    $query = "INSERT INTO tb_brand (nama_brand) VALUES ('$nama_brand')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Brand berhasil ditambahkan!'); window.location.href='admin_dashboard.php?tab=brand';</script>";
    }
}

// 4. PROSES FORM TAMBAH PRODUK
if (isset($_POST['tambah_produk'])) {
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $harga       = mysqli_real_escape_string($koneksi, $_POST['harga']);
    $id_kategori = mysqli_real_escape_string($koneksi, $_POST['id_kategori']);
    $id_brand    = mysqli_real_escape_string($koneksi, $_POST['id_brand']);
    $deskripsi   = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    $query = "INSERT INTO tb_produk (nama_produk, harga, id_kategori, id_brand, deskripsi) 
              VALUES ('$nama_produk', '$harga', '$id_kategori', '$id_brand', '$deskripsi')";
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Produk berhasil ditambahkan!'); window.location.href='admin_dashboard.php?tab=produk';</script>";
    }
}

// 5. PROSES HAPUS (NOTIFIKASI / PESANAN / PRODUK / KERANJANG)
if (isset($_GET['hapus']) && isset($_GET['tabel'])) {
    $id = intval($_GET['hapus']);
    $tabel = $_GET['tabel'];
    
    // Penentuan Primary Key berdasarkan nama tabel
    $pk_map = [
        'tb_notifikasi' => 'id_notifikasi',
        'tb_pesanan'    => 'id_pesanan',
        'tb_produk'     => 'id_produk',
        'tb_keranjang'  => 'id_keranjang'
    ];
    
    if (array_key_exists($tabel, $pk_map)) {
        $pk = $pk_map[$tabel];
        $tab_redirect = str_replace('tb_', '', $tabel);
        mysqli_query($koneksi, "DELETE FROM $tabel WHERE $pk = $id");
        echo "<script>alert('Data berhasil dihapus!'); window.location.href='admin_dashboard.php?tab=$tab_redirect';</script>";
    }
}

// AMBIL DATA DARI DATABASE
$data_notif     = mysqli_query($koneksi, "SELECT * FROM tb_notifikasi ORDER BY id_notifikasi DESC");
$data_pesanan   = mysqli_query($koneksi, "SELECT * FROM tb_pesanan ORDER BY id_pesanan DESC");
$data_produk    = mysqli_query($koneksi, "SELECT * FROM tb_produk ORDER BY id_produk DESC");
$data_kategori  = mysqli_query($koneksi, "SELECT * FROM tb_kategori ORDER BY id_kategori DESC");
$data_brand     = mysqli_query($koneksi, "SELECT * FROM tb_brand ORDER BY id_brand DESC");

// Query JOIN untuk mengambil data KeranjangKonsumen beserta info Produk
$data_keranjang = mysqli_query($koneksi, "SELECT k.*, p.nama_produk, p.harga 
                                          FROM tb_keranjang k 
                                          LEFT JOIN tb_produk p ON k.id_produk = p.id_produk 
                                          ORDER BY k.id_keranjang DESC");

// Tab Aktif
$tab_aktif = $_GET['tab'] ?? 'notifikasi';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - NFahira Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #f4f6f9; }
        .sidebar { min-height: 100vh; background-color: #212529; color: white; }
        .sidebar .nav-link { color: #adb5bd; font-weight: 500; border-radius: 8px; margin-bottom: 5px; }
        .sidebar .nav-link.active, .sidebar .nav-link:hover { background-color: #D4AF37; color: white; }
        .card-custom { border: none; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .btn-gold { background-color: #D4AF37; color: white; font-weight: 600; }
        .btn-gold:hover { background-color: #c4a02e; color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- SIDEBAR -->
        <div class="col-md-3 col-lg-2 sidebar p-3 d-flex flex-column justify-content-between">
            <div>
                <h4 class="fw-bold text-warning text-center py-3 border-bottom border-secondary mb-4" style="font-family: 'Playfair Display', serif;">
                    NFahira Admin
                </h4>
                <div class="nav flex-column nav-pills" role="tablist">
                    <button class="nav-link text-start <?= $tab_aktif=='notifikasi'?'active':''; ?>" data-bs-toggle="pill" data-bs-target="#tab-notif">
                        <i class="bi bi-bell-fill me-2"></i> Kelola Notifikasi
                    </button>
                    <button class="nav-link text-start <?= $tab_aktif=='pesanan'?'active':''; ?>" data-bs-toggle="pill" data-bs-target="#tab-pesanan">
                        <i class="bi bi-bag-check-fill me-2"></i> Kelola Pesanan
                    </button>
                    <button class="nav-link text-start <?= $tab_aktif=='keranjang'?'active':''; ?>" data-bs-toggle="pill" data-bs-target="#tab-keranjang">
                        <i class="bi bi-cart-fill me-2"></i> Keranjang Konsumen
                    </button>
                    <button class="nav-link text-start <?= $tab_aktif=='produk'?'active':''; ?>" data-bs-toggle="pill" data-bs-target="#tab-produk">
                        <i class="bi bi-journal-album me-2"></i> Tambah / Kelola Produk
                    </button>
                    <button class="nav-link text-start <?= $tab_aktif=='kategori'?'active':''; ?>" data-bs-toggle="pill" data-bs-target="#tab-kategori">
                        <i class="bi bi-grid-fill me-2"></i> Tambah Kategori
                    </button>
                    <button class="nav-link text-start <?= $tab_aktif=='brand'?'active':''; ?>" data-bs-toggle="pill" data-bs-target="#tab-brand">
                        <i class="bi bi-tags-fill me-2"></i> Tambah Brand
                    </button>
                </div>
            </div>
            <div>
                <hr class="border-secondary">
                <div class="d-flex align-items-center justify-content-between px-2">
                    <span class="small fw-semibold"><i class="bi bi-person-circle me-1"></i> <?= htmlspecialchars($nama_admin); ?></span>
                    <a href="logout_admin.php" class="btn btn-sm btn-outline-danger" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
                </div>
            </div>
        </div>

        <!-- KONTEN UTAMA -->
        <div class="col-md-9 col-lg-10 p-4">
            <div class="tab-content">

                <!-- TAB 1: KELOLA NOTIFIKASI -->
                <div class="tab-pane fade <?= $tab_aktif=='notifikasi'?'show active':''; ?>" id="tab-notif">
                    <h3 class="fw-bold mb-4">Kelola Notifikasi & Pembatalan</h3>
                    <div class="card card-custom p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Judul</th>
                                        <th>Pesan / Informasi</th>
                                        <th>Tanggal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($data_notif && mysqli_num_rows($data_notif) > 0): ?>
                                        <?php while($row = mysqli_fetch_assoc($data_notif)): ?>
                                        <tr>
                                            <td>#<?= $row['id_notifikasi'] ?? '-'; ?></td>
                                            <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($row['judul'] ?? 'Notifikasi'); ?></span></td>
                                            <td><?= htmlspecialchars($row['pesan'] ?? '-'); ?></td>
                                            <td class="small text-muted"><?= $row['tanggal'] ?? '-'; ?></td>
                                            <td>
                                                <a href="admin_dashboard.php?hapus=<?= $row['id_notifikasi']; ?>&tabel=tb_notifikasi" class="btn btn-sm btn-danger" onclick="return confirm('Hapus notifikasi ini?')"><i class="bi bi-trash"></i> Hapus</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="5" class="text-center text-muted">Belum ada notifikasi masuk.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 2: KELOLA PESANAN -->
                <div class="tab-pane fade <?= $tab_aktif=='pesanan'?'show active':''; ?>" id="tab-pesanan">
                    <h3 class="fw-bold mb-4">Kelola Pesanan Masuk</h3>
                    <div class="card card-custom p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Nama Pemesan</th>
                                        <th>Produk</th>
                                        <th>Total / Detail</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($data_pesanan && mysqli_num_rows($data_pesanan) > 0): ?>
                                        <?php while($p = mysqli_fetch_assoc($data_pesanan)): ?>
                                        <tr>
                                            <td>#<?= $p['id_pesanan'] ?? '-'; ?></td>
                                            <td><?= htmlspecialchars($p['nama'] ?? $p['id_user'] ?? 'Pelanggan'); ?></td>
                                            <td><?= htmlspecialchars($p['nama_produk'] ?? 'Detail Produk'); ?></td>
                                            <td>Rp <?= number_format($p['total_harga'] ?? 0, 0, ',', '.'); ?></td>
                                            <td><span class="badge bg-info"><?= $p['status'] ?? 'Baru'; ?></span></td>
                                            <td>
                                                <a href="admin_dashboard.php?hapus=<?= $p['id_pesanan']; ?>&tabel=tb_pesanan" class="btn btn-sm btn-danger" onclick="return confirm('Hapus pesanan ini?')"><i class="bi bi-trash"></i> Hapus</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted">Belum ada data pesanan di tabel `tb_pesanan`.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB BARU: KERANJANG KONSUMEN -->
                <div class="tab-pane fade <?= $tab_aktif=='keranjang'?'show active':''; ?>" id="tab-keranjang">
                    <h3 class="fw-bold mb-4">Keranjang Konsumen</h3>
                    <div class="card card-custom p-4">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Keranjang</th>
                                        <th>ID User / Konsumen</th>
                                        <th>Produk</th>
                                        <th>Jumlah (Qty)</th>
                                        <th>Subtotal</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($data_keranjang && mysqli_num_rows($data_keranjang) > 0): ?>
                                        <?php while($k = mysqli_fetch_assoc($data_keranjang)): 
                                            $jumlah = $k['jumlah'] ?? $k['qty'] ?? 1;
                                            $harga  = $k['harga'] ?? 0;
                                            $subtotal = $harga * $jumlah;
                                        ?>
                                        <tr>
                                            <td>#<?= $k['id_keranjang']; ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($k['id_user'] ?? 'User #'.$k['id_user']); ?></span></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($k['nama_produk'] ?? 'Produk ID: '.$k['id_produk']); ?></td>
                                            <td><?= $jumlah; ?></td>
                                            <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                            <td>
                                                <a href="admin_dashboard.php?hapus=<?= $k['id_keranjang']; ?>&tabel=tb_keranjang" class="btn btn-sm btn-danger" onclick="return confirm('Hapus item dari keranjang konsumen ini?')"><i class="bi bi-trash"></i> Hapus</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="6" class="text-center text-muted">Belum ada item di keranjang konsumen saat ini.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 3: TAMBAH & KELOLA PRODUK -->
                <div class="tab-pane fade <?= $tab_aktif=='produk'?'show active':''; ?>" id="tab-produk">
                    <h3 class="fw-bold mb-4">Kelola Data Produk</h3>
                    
                    <!-- FORM TAMBAH PRODUK -->
                    <div class="card card-custom p-4 mb-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle me-1"></i> Tambah Produk Baru</h5>
                        <form action="" method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Nama Produk</label>
                                    <input type="text" name="nama_produk" class="form-control" placeholder="Contoh: Maroon Indian Married Dress" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Harga (Rp)</label>
                                    <input type="number" name="harga" class="form-control" placeholder="Contoh: 500000000" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Kategori</label>
                                    <select name="id_kategori" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Kategori --</option>
                                        <?php 
                                        if($data_kategori) {
                                            mysqli_data_seek($data_kategori, 0);
                                            while($kat = mysqli_fetch_assoc($data_kategori)): 
                                        ?>
                                            <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                                        <?php 
                                            endwhile; 
                                        } 
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Brand</label>
                                    <select name="id_brand" class="form-select" required>
                                        <option value="" disabled selected>-- Pilih Brand --</option>
                                        <?php 
                                        if($data_brand) {
                                            mysqli_data_seek($data_brand, 0);
                                            while($br = mysqli_fetch_assoc($data_brand)): 
                                        ?>
                                            <option value="<?= $br['id_brand']; ?>"><?= htmlspecialchars($br['nama_brand']); ?></option>
                                        <?php 
                                            endwhile; 
                                        } 
                                        ?>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-semibold">Deskripsi Produk</label>
                                    <textarea name="deskripsi" class="form-control" rows="2" placeholder="Tuliskan rincian bahan dan keunggulan gaun..."></textarea>
                                </div>
                                <div class="col-12 text-end">
                                    <button type="submit" name="tambah_produk" class="btn btn-gold px-4"><i class="bi bi-save me-1"></i> Simpan Produk</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- TABEL PRODUK -->
                    <div class="card card-custom p-4">
                        <h5 class="fw-bold mb-3">Daftar Produk</h5>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nama Produk</th>
                                        <th>Harga</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if($data_produk && mysqli_num_rows($data_produk) > 0): ?>
                                        <?php while($prod = mysqli_fetch_assoc($data_produk)): ?>
                                        <tr>
                                            <td>#<?= $prod['id_produk']; ?></td>
                                            <td class="fw-semibold"><?= htmlspecialchars($prod['nama_produk']); ?></td>
                                            <td>Rp <?= number_format($prod['harga'] ?? 0, 0, ',', '.'); ?></td>
                                            <td>
                                                <a href="admin_dashboard.php?hapus=<?= $prod['id_produk']; ?>&tabel=tb_produk" class="btn btn-sm btn-danger" onclick="return confirm('Hapus produk ini?')"><i class="bi bi-trash"></i> Hapus</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr><td colspan="4" class="text-center text-muted">Belum ada produk.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 4: TAMBAH KATEGORI -->
                <div class="tab-pane fade <?= $tab_aktif=='kategori'?'show active':''; ?>" id="tab-kategori">
                    <h3 class="fw-bold mb-4">Kelola Kategori Produk</h3>
                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="card card-custom p-4">
                                <h5 class="fw-bold mb-3">Tambah Kategori Baru</h5>
                                <form action="" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Nama Kategori</label>
                                        <input type="text" name="nama_kategori" class="form-control" placeholder="Contoh: Gaun Pengantin" required>
                                    </div>
                                    <button type="submit" name="tambah_kategori" class="btn btn-gold w-100">Tambah Kategori</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card card-custom p-4">
                                <h5 class="fw-bold mb-3">Daftar Kategori</h5>
                                <ul class="list-group list-group-flush">
                                    <?php 
                                    if($data_kategori && mysqli_num_rows($data_kategori) > 0):
                                        mysqli_data_seek($data_kategori, 0);
                                        while($k = mysqli_fetch_assoc($data_kategori)): 
                                    ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>#<?= $k['id_kategori']; ?> - <strong><?= htmlspecialchars($k['nama_kategori']); ?></strong></span>
                                        </li>
                                    <?php 
                                        endwhile; 
                                    else:
                                    ?>
                                        <li class="list-group-item text-muted text-center">Belum ada data kategori.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB 5: TAMBAH BRAND -->
                <div class="tab-pane fade <?= $tab_aktif=='brand'?'show active':''; ?>" id="tab-brand">
                    <h3 class="fw-bold mb-4">Kelola Brand Boutique</h3>
                    <div class="row g-4">
                        <div class="col-md-5">
                            <div class="card card-custom p-4">
                                <h5 class="fw-bold mb-3">Tambah Brand Baru</h5>
                                <form action="" method="POST">
                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold">Nama Brand / Koleksi</label>
                                        <input type="text" name="nama_brand" class="form-control" placeholder="Contoh: Royal Luxury" required>
                                    </div>
                                    <button type="submit" name="tambah_brand" class="btn btn-gold w-100">Tambah Brand</button>
                                </form>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="card card-custom p-4">
                                <h5 class="fw-bold mb-3">Daftar Brand</h5>
                                <ul class="list-group list-group-flush">
                                    <?php 
                                    if($data_brand && mysqli_num_rows($data_brand) > 0):
                                        mysqli_data_seek($data_brand, 0);
                                        while($b = mysqli_fetch_assoc($data_brand)): 
                                    ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <span>#<?= $b['id_brand']; ?> - <strong><?= htmlspecialchars($b['nama_brand']); ?></strong></span>
                                        </li>
                                    <?php 
                                        endwhile; 
                                    else:
                                    ?>
                                        <li class="list-group-item text-muted text-center">Belum ada data brand.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>