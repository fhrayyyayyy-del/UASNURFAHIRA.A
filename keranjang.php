<?php
session_start();
require 'fungsi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['id_user'])) {
    echo "<script>alert('Silakan login terlebih dahulu untuk melihat keranjang!'); window.location.href='login.php';</script>";
    exit;
}

$id_user = $_SESSION['id_user'];

// Ambil data produk di keranjang milik user yang sedang login
$query_keranjang = "SELECT k.id_keranjang, k.jumlah, p.id_produk, p.nama_produk, p.harga, p.foto 
                   FROM tb_keranjang k 
                   JOIN tb_produk p ON k.id_produk = p.id_produk 
                   WHERE k.id_user = '$id_user'";
$data_keranjang = query($query_keranjang);

// Logika hapus item dari keranjang
if (isset($_GET['hapus'])) {
    $id_keranjang = (int) $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM tb_keranjang WHERE id_keranjang = '$id_keranjang' AND id_user = '$id_user'");
    echo "<script>alert('Item berhasil dihapus dari keranjang'); window.location.href='keranjang.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - NFahira Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #fcfbf7; font-family: sans-serif; }
        .navbar-custom { background-color: #D4AF37; }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom py-3 shadow-sm">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="index.php">NFahira Boutique</a>
            <a href="Brand.php" class="btn btn-outline-light btn-sm"><i class="bi bi-arrow-left me-1"></i> Kembali ke Brand</a>
        </div>
    </nav>

    <div class="container py-5">
        <h3 class="fw-bold mb-4"><i class="bi bi-cart3 me-2"></i>Keranjang Belanja Anda</h3>

        <?php if (empty($data_keranjang)): ?>
            <div class="alert alert-warning text-center py-4">
                <h5>Keranjang Belanja Masih Kosong</h5>
                <p class="mb-3">Yuk, pilih pakaian impianmu dulu!</p>
                <a href="Brand.php" class="btn btn-primary bg-dark border-0">Belanja Sekarang</a>
            </div>
        <?php else: ?>
            <div class="table-responsive bg-white rounded shadow-sm p-3">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $grand_total = 0;
                        foreach ($data_keranjang as $item): 
                            $subtotal = $item['harga'] * $item['jumlah'];
                            $grand_total += $subtotal;

                            // LOGIKA PENENTUAN GAMBAR SAMA SEPERTI BRAND.PHP
                            $nama_produk = $item['nama_produk'] ?? 'Produk';
                            $file_db = trim($item['foto'] ?? '');
                            $nama_file_biasa = strtolower(trim($nama_produk));

                            if (!empty($file_db) && file_exists($file_db)) {
                                $path_gambar = $file_db;
                            } elseif (file_exists($nama_file_biasa . ".jpeg")) {
                                $path_gambar = $nama_file_biasa . ".jpeg";
                            } elseif (file_exists($nama_file_biasa . ".jpg")) {
                                $path_gambar = $nama_file_biasa . ".jpg";
                            } elseif (file_exists($nama_file_biasa . ".png")) {
                                $path_gambar = $nama_file_biasa . ".png";
                            } else {
                                $path_gambar = $file_db; // Fallback default
                            }
                        ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= htmlspecialchars($path_gambar); ?>" style="width: 60px; height: 60px; object-fit: cover;" class="rounded border" alt="<?= htmlspecialchars($nama_produk); ?>">
                                        <span class="fw-semibold"><?= htmlspecialchars($nama_produk); ?></span>
                                    </div>
                                </td>
                                <td>Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                <td><?= $item['jumlah']; ?></td>
                                <td class="fw-bold">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                <td>
                                    <a href="keranjang.php?hapus=<?= $item['id_keranjang']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin ingin menghapus item ini?')">
                                        <i class="bi bi-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end fw-bold fs-5">Total Pembayaran:</td>
                            <td colspan="2" class="fw-bold fs-5 text-success">Rp <?= number_format($grand_total, 0, ',', '.'); ?></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="d-flex justify-content-between mt-4">
                    <a href="Brand.php" class="btn btn-outline-secondary">Lanjut Belanja</a>
                    <a href="contac.php" class="btn navbar-custom text-white fw-bold px-4">Checkout / Pesan Sekarang</a>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>