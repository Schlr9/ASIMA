<?php
session_start();
require_once 'config.php'; // Koneksi ke database ($pdo)

// Atur header untuk merespons sebagai JSON
header('Content-Type: application/json');

// Ambil data JSON yang dikirim dari JavaScript
$data = json_decode(file_get_contents('php://input'), true);

$npm = $data['npm'] ?? '';
$password = $data['password'] ?? '';

// Validasi input dasar
if (empty($npm) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'NPM dan sandi tidak boleh kosong.']);
    exit;
}

try {
    // 1. Cari pengguna berdasarkan NPM
    $stmt = $pdo->prepare("SELECT * FROM users_m WHERE npm = :npm");
    $stmt->execute([':npm' => $npm]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Jika pengguna ditemukan, verifikasi sandinya
    if ($user) {
        // Bandingkan sandi yang diketik ($password) dengan hash di database ($user['password'])
        if (password_verify($password, $user['password'])) {
            // Jika sandi cocok, simpan data pengguna ke session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'npm' => $user['npm'],
                'nama_lengkap' => $user['nama_lengkap'],
                'fakultas' => $user['fakultas'],
                'prodi' => $user['prodi']
                // Jangan simpan sandi di session
            ];
            
            // Kirim respons sukses
            echo json_encode(['success' => true]);
        } else {
            // Jika sandi tidak cocok
            echo json_encode(['success' => false, 'message' => 'NPM atau sandi yang Anda masukkan salah.']);
        }
    } else {
        // Jika NPM tidak ditemukan
        echo json_encode(['success' => false, 'message' => 'NPM atau sandi yang Anda masukkan salah.']);
    }

} catch (PDOException $e) {
    // Tangani error database
    error_log("Login error: " . $e->getMessage()); // Catat error untuk admin
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan pada server.']);
}
?>