<?php
// logout.php
// Menghancurkan session dan mengarahkan pengguna kembali ke halaman utama.

session_start();

// Hapus semua variabel session
$_SESSION = array();

// Hancurkan session
session_destroy();

// Arahkan kembali ke halaman index.php
header("location:../index.php");
exit;
?>
