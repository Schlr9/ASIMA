<?php
// user_functions.php
// Fungsi-fungsi yang berkaitan dengan manajemen akun pengguna, menggunakan PDO.

/**
 * Mengambil transkrip akademik pengguna dari database.
 * VERSI INI SUDAH DIPERBAIKI MENGGUNAKAN PDO.
 */
function getAcademicTranscript($pdo) {
    try {
        $userId = $_SESSION['user']['id'];
        
        $sql = "SELECT mk.kode_matkul, mk.nama_matkul, mk.sks, kn.nilai_huruf 
                FROM khs_nilai kn
                JOIN mata_kuliah mk ON kn.matkul_id = mk.id
                WHERE kn.user_id = :userId
                ORDER BY mk.kode_matkul ASC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':userId' => $userId]);
        
        // fetchAll() akan mengambil semua baris hasil ke dalam array
        $transcript = $stmt->fetchAll();
        
        // Kirim data yang berhasil diambil
        send_json_response($transcript);

    } catch (PDOException $e) {
        // Jika terjadi error database, kirim respons error yang jelas
        send_json_response(['error' => 'Gagal mengambil data transkrip dari database.', 'detail' => $e->getMessage()], 500);
    }
}

/**
 * Mengganti sandi pengguna.
 */
function updatePassword($pdo, $data) {
    try {
        // Pastikan sesi pengguna ada
        if (!isset($_SESSION['user']['id'])) {
            send_json_response(['success' => false, 'message' => 'Sesi tidak valid, silakan login kembali.'], 401);
            return;
        }
        $userId = $_SESSION['user']['id'];

        $oldPassword = $data['old_password'] ?? '';
        $newPassword = $data['new_password'] ?? '';
        
        // 1. Validasi Input Dasar
        if (empty($oldPassword) || empty($newPassword)) {
            send_json_response(['success' => false, 'message' => 'Sandi lama dan baru tidak boleh kosong.'], 400);
            return;
        }

        if (strlen($newPassword) < 6) {
            send_json_response(['success' => false, 'message' => 'Sandi baru minimal harus 6 karakter.'], 400);
            return;
        }

        // 2. Ambil Hash Sandi Saat Ini dari Database
        $stmt = $pdo->prepare("SELECT password FROM users_m WHERE id = :userId");
        $stmt->execute([':userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            send_json_response(['success' => false, 'message' => 'Pengguna tidak ditemukan.'], 404);
            return;
        }
        
        $currentHashedPassword = $user['password'];

        // 3. Verifikasi Sandi Lama
        // Memastikan sandi lama yang dimasukkan pengguna cocok dengan yang ada di database.
        if (password_verify($oldPassword, $currentHashedPassword)) {
            
            // 4. INJEKSI KODE: Cek Apakah Sandi Baru Sama dengan Sandi Lama
            // Ini adalah praktik keamanan yang baik untuk memastikan pengguna benar-benar mengubah sandi mereka.
            if (password_verify($newPassword, $currentHashedPassword)) {
                send_json_response(['success' => false, 'message' => 'Sandi baru tidak boleh sama dengan sandi lama.'], 400);
                return;
            }

            // 5. Buat Hash untuk Sandi Baru
            // Jika semua pemeriksaan lolos, hash sandi baru menggunakan algoritma BCRYPT.
            $newHashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

            // 6. Update Database dengan Hash Sandi yang Baru
            $stmt_update = $pdo->prepare("UPDATE users_m SET password = :newPassword WHERE id = :userId");
            $stmt_update->execute([':newPassword' => $newHashedPassword, ':userId' => $userId]);
            
            send_json_response(['success' => true, 'message' => 'Sandi berhasil diperbarui.']);

        } else {
            // Jika sandi lama tidak cocok
            send_json_response(['success' => false, 'message' => 'Sandi lama yang Anda masukkan salah.'], 400);
        }
    } catch (PDOException $e) {
        // Tangani error database yang tidak terduga
        error_log("Update password error: " . $e->getMessage()); // Catat error untuk developer
        send_json_response(['success' => false, 'message' => 'Terjadi kesalahan pada server database.'], 500);
    }
}




?>