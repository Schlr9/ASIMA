<?php
// utils.php
// Berisi fungsi-fungsi bantuan umum.

/**
 * Mengirim respons JSON dan menghentikan skrip dengan bersih.
 * @param mixed $data Data yang akan di-encode ke JSON.
 * @param int $statusCode Kode status HTTP.
 */
function send_json_response($data, $statusCode = 200) {
    // Hapus semua output yang mungkin sudah ada di buffer (seperti warnings).
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}

/**
 * Helper function untuk mengkategorikan skor menjadi 'Tinggi', 'Sedang', atau 'Rendah'.
 * @param int $score Skor yang akan dikategorikan.
 * @return string Kategori skor.
 */
function getScoreCategory($score) {
    if ($score > 70) return 'Tinggi';
    if ($score >= 40) return 'Sedang';
    return 'Rendah';
}
?>
