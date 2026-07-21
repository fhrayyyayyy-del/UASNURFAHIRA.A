<?php
// Koneksi ke Database MySQL Laragon
$host = "localhost";
$user = "root";
$pass = "";
$db   = "fahira_boutique"; // Nama database Anda

$koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek Koneksi
if (mysqli_connect_errno()) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Fungsi untuk mempermudah pengambilan data (Read/Select)
function query($query) {
    global $koneksi;
    $result = mysqli_query($koneksi, $query);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    return $rows;
}
?>