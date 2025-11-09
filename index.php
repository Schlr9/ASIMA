<?php
/**
 * index.php
 * File ini adalah gerbang utama aplikasi.
 * Tugasnya adalah memeriksa status login pengguna dan menampilkan halaman yang sesuai.
 */

// Memulai atau melanjutkan sesi yang sudah ada.
session_start();

// Memuat template header yang berisi semua tag <head>, CSS, dan awal <body>.
// Pastikan path ini benar. Jika index.php ada di folder root, path ini sudah benar.
require_once 'templates/header.php';

// Logika routing berdasarkan session
if (isset($_SESSION['user']) && !empty($_SESSION['user'])) {
    // Jika data 'user' ada di dalam session, berarti pengguna sudah login.
    // Tampilkan halaman utama aplikasi, yaitu tampilan chat.
    require_once 'templates/chat_view.php';
} else {
    // Jika tidak ada data 'user' di session, berarti pengguna belum login.
    // Tampilkan halaman login.
    require_once 'templates/login_view.php';
}

// Memuat template footer yang berisi semua tautan ke file JavaScript.
// Ini memastikan semua fungsionalitas interaktif dimuat di akhir.
require_once 'templates/footer.php';

?>