<?php
// Memulai session
session_start();

// 1. Hapus semua data session yang tersimpan
$_SESSION = [];
session_unset();

// 2. Hancurkan/patahkan session aktif
session_destroy();

// 3. Alihkan halaman langsung ke login.php setelah sukses logout
echo "<script>
        alert('Anda telah berhasil keluar dari akun.');
        window.location.href = 'login.php';
      </script>";
exit();
?>