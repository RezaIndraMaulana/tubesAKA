<?php
$connect = mysqli_connect('localhost', 'root', '', 'anime_db');

// Cek koneksi
if (!$connect) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>
