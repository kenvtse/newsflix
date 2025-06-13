<?php
// includes/koneksi.php

$host = "localhost"; // Biasanya localhost untuk XAMPP
$user = "root";      // Username MySQL Anda (default XAMPP adalah 'root')
$pass = "";          // Password MySQL Anda (default XAMPP adalah kosong)
$db_name = "db_berita"; // Nama database yang sudah kita buat

// Buat koneksi
$koneksi = mysqli_connect($host, $user, $pass, $db_name);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
// echo "Koneksi database berhasil!"; // Anda bisa mengaktifkan ini untuk menguji koneksi
?>