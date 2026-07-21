<?php
// Koneksi ke database (menggunakan static/global)
if (!isset($koneksi)) {
    $koneksi = mysqli_connect("localhost", "root", "", "fahira_boutique");
}

// Cek koneksi
if (mysqli_connect_errno()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Fungsi query global
if (!function_exists('query')) {
    function query($query) {
        global $koneksi;
        $result = mysqli_query($koneksi, $query);
        if (!$result) {
            die("Query Error: " . mysqli_error($koneksi));
        }
        $rows = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $rows[] = $row;
        }
        return $rows;
    }
}

// Fungsi registrasi yang disesuaikan dengan struktur tabel tb_user
if (!function_exists('registrasi')) {
    function registrasi($data) {
        global $koneksi;

        $nama     = mysqli_real_escape_string($koneksi, $data['nama']);
        $email    = mysqli_real_escape_string($koneksi, strtolower(trim($data['email'])));
        $username = mysqli_real_escape_string($koneksi, strtolower(trim($data['username'])));
        $password = mysqli_real_escape_string($koneksi, $data['password']);

        // Cek apakah email sudah terdaftar
        $cek_email = mysqli_query($koneksi, "SELECT email FROM tb_user WHERE email = '$email'");
        if (mysqli_fetch_assoc($cek_email)) {
            echo "<script>alert('Email sudah terdaftar!');</script>";
            return false;
        }

        // Enkripsi password
        $password_aman = password_hash($password, PASSWORD_DEFAULT);

        // Menggunakan nama_lengkap sesuai kolom di database
        $query = "INSERT INTO tb_user (username, password, nama_lengkap, email) VALUES ('$username', '$password_aman', '$nama', '$email')";
        
        mysqli_query($koneksi, $query);

        return mysqli_affected_rows($koneksi);
    }
}
?>