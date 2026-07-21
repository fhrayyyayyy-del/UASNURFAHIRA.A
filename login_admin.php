<?php
session_start();
// Hubungkan ke database
$koneksi = mysqli_connect("localhost", "root", "", "fahira_boutique");

if (isset($_POST['login_admin'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // Cek username di tabel tb_admin
    $query = mysqli_query($koneksi, "SELECT * FROM tb_admin WHERE username = '$username'");
    
    if (mysqli_num_rows($query) === 1) {
        $row = mysqli_fetch_assoc($query);
        
        // Verifikasi password (Gunakan password_verify jika di-hash, atau komparasi langsung jika plain text)
        if ($password === $row['password'] || password_verify($password, $row['password'])) {
            // Set session khusus admin
            $_SESSION['admin_login'] = true;
            $_SESSION['admin_id']    = $row['id_admin'];
            $_SESSION['admin_nama']  = $row['nama_admin'];
            $_SESSION['admin_level'] = $row['level'];

            echo "<script>
                    alert('Login Admin Berhasil!');
                    window.location.href = 'admin_dashboard.php';
                  </script>";
            exit();
        }
    }

    $error = true;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - NFahira Boutique</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: sans-serif; }
        .card-login { max-width: 400px; margin: 80px auto; border: none; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .btn-admin { background-color: #D4AF37; color: white; font-weight: 600; }
        .btn-admin:hover { background-color: #c4a02e; color: white; }
    </style>
</head>
<body>

<div class="container">
    <div class="card card-login p-4">
        <h4 class="text-center fw-bold mb-3">Admin Panel</h4>
        <p class="text-center text-muted small mb-4">NFahira Boutique Control Center</p>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger small py-2">Username atau Password salah!</div>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="mb-3">
                <label class="form-label small fw-semibold">Username Admin</label>
                <input type="text" name="username" class="form-control" placeholder="Masukkan username" required>
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
            </div>
            <button type="submit" name="login_admin" class="btn btn-admin w-100 py-2 mt-2">Login Admin</button>
        </form>
    </div>
</div>

</body>
</html>