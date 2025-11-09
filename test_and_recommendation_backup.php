<?php

require_once 'config/config.php';
/**
 * file: includes/test_functions.php
 * Versi modifikasi: Mengganti Decision Tree dengan K-Means (K=2)
 * Analisis AI (Gemini) tetap dipertahankan untuk analisis komplementer.
 */

function getTestQuestions($pdo) {
    try {
        $stmt = $pdo->prepare("SELECT id, kategori, pernyataan FROM test_questions ORDER BY kategori, id");
        $stmt->execute();
        $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (count($questions) < 80) {
            send_json_response(['error' => 'Soal tidak lengkap di database. Pastikan ada 80 soal (10 per kategori).'], 500);
            return;
        }

        send_json_response($questions);

    } catch (PDOException $e) {
        error_log("Gagal mengambil soal tes: " . $e->getMessage());
        send_json_response(['error' => 'Terjadi kesalahan saat mengambil soal dari database.'], 500);
    }
}

function submitTest($pdo, $data) {
    try {
        $answers = $data['answers'] ?? [];
        $userId = $_SESSION['user']['id'];
        $userName = $_SESSION['user']['nama_lengkap'];

        // --- 1. Hitung Skor Mentah ---
        $scores = calculateRawScores($pdo, $answers);

        // --- 2. Konversi Skor ke Persentase ---
        $max_score_per_category = 10;
        $percentage_scores = [];
        foreach ($scores as $kat => $score) {
            $percentage_scores[$kat] = round(($score / $max_score_per_category) * 100);
        }

        // --- 3. Rekomendasi Sistem (Menggunakan K-Means K=2) ---
        $result_kmeans = getKMeansRecommendation($percentage_scores);
        $system_recommendation = buildKMeansRecommendationText($userName, $result_kmeans);

        // --- 4. Analisis AI (ringkas, berdasarkan top 2 dominant) ---
        // Ini dipertahankan sebagai analisis komplementer
        $ai_recommendation = generateGeminiCareerAnalysis($percentage_scores);

        // --- 5. Simpan Hasil ke Database ---
        saveTestResult($pdo, $userId, $percentage_scores, $system_recommendation, $ai_recommendation);

        // --- 6. Kirim Respons ke Frontend ---
        send_json_response([
            'recommendation_system' => $system_recommendation,
            'recommendation_ai' => $ai_recommendation,
            'scores' => $percentage_scores
        ]);

    } catch (PDOException $e) {
        error_log("Gagal memproses tes: " . $e->getMessage());
        send_json_response(['error' => 'Terjadi kesalahan saat memproses hasil tes.'], 500);
    }
}

/**
 * ====================== HELPER FUNCTIONS ======================
 */

function calculateRawScores($pdo, $answers) {
    $scores = [
        'linguistik' => 0, 'logis_matematis' => 0, 'spasial' => 0, 
        'kinestetik' => 0, 'musikal' => 0, 'interpersonal' => 0, 
        'intrapersonal' => 0, 'naturalis' => 0
    ];
    
    $question_ids = array_keys($answers);
    if (empty($question_ids)) return $scores;
    
    $placeholders = implode(',', array_fill(0, count($question_ids), '?'));
    $sql = "SELECT id, kategori FROM test_questions WHERE id IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($question_ids);
    $answered_questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($answered_questions as $q) {
        $kat = $q['kategori'];
        if (isset($scores[$kat])) $scores[$kat]++;
    }
    return $scores;
}

/**
 * Menghitung jarak Euclidean antara dua titik (vektor).
 */
function euclideanDistance($point1, $point2) {
    $sum = 0;
    $dimensions = count($point1);
    for ($i = 0; $i < $dimensions; $i++) {
        // Pastikan kedua indeks ada
        if (isset($point1[$i]) && isset($point2[$i])) {
             $sum += pow($point1[$i] - $point2[$i], 2);
        }
    }
    return sqrt($sum);
}


/**
 * ====================== SISTEM REKOMENDASI (K-MEANS) ======================
 */

/**
 * Menggunakan K-Means (K=2) untuk menentukan klaster rekomendasi.
 * Dalam implementasi ini, centroid sudah ditentukan sebelumnya (pre-defined).
 * Kita hanya menghitung jarak ke centroid untuk mengklasifikasikan pengguna baru.
 */
function getKMeansRecommendation($scores) {
    // Urutan kategori HARUS konsisten untuk kalkulasi vektor
    $categories_order = [
        'linguistik', 'logis_matematis', 'spasial', 'kinestetik', 
        'musikal', 'interpersonal', 'intrapersonal', 'naturalis'
    ];

    // Konversi skor user (associative array) ke vektor numerik
    $user_vector = [];
    foreach ($categories_order as $cat) {
        $user_vector[] = $scores[$cat] ?? 0;
    }

    // Definisikan 2 Centroid (K=2) yang mewakili profil karir (skor 0-100)
    // MODIFIKASI: Centroid 0: Profil Kompetensi Rendah
    // Skor rendah di semua kategori
    $centroid_0_map = [
        'linguistik' => 25, 'logis_matematis' => 25, 'spasial' => 25, 'kinestetik' => 25,
        'musikal' => 25, 'interpersonal' => 25, 'intrapersonal' => 25, 'naturalis' => 25
    ];
    
    // MODIFIKASI: Centroid 1: Profil Kompetensi Tinggi
    // Skor tinggi di semua kategori
    $centroid_1_map = [
        'linguistik' => 85, 'logis_matematis' => 85, 'spasial' => 85, 'kinestetik' => 85,
        'musikal' => 85, 'interpersonal' => 85, 'intrapersonal' => 85, 'naturalis' => 85
    ];

    // Konversi centroid ke vektor numerik (pastikan urutan sama)
    $centroid_0_vector = [];
    $centroid_1_vector = [];
    foreach ($categories_order as $cat) {
        $centroid_0_vector[] = $centroid_0_map[$cat];
        $centroid_1_vector[] = $centroid_1_map[$cat];
    }

    // Hitung jarak Euclidean
    $dist_0 = euclideanDistance($user_vector, $centroid_0_vector);
    $dist_1 = euclideanDistance($user_vector, $centroid_1_vector);

    // Tentukan klaster terdekat
    // Cluster 0 = Jarak ke centroid 'Rendah' lebih dekat
    // Cluster 1 = Jarak ke centroid 'Tinggi' lebih dekat
    $cluster_id = ($dist_0 < $dist_1) ? 0 : 1;

    // MODIFIKASI: Mapping hasil klaster ke rekomendasi
    $cluster_mapping = [
        0 => [
            'name' => 'Kelompok Kompetensi (Perlu Peningkatan)',
            'description' => 'Skor Anda secara umum menunjukkan perlunya peningkatan di berbagai bidang kecerdasan. Ini adalah kesempatan untuk mengeksplorasi dan menemukan potensi terkuat Anda.'
            // Karir dihapus, sesuai permintaan
        ],
        1 => [
            'name' => 'Kelompok Kompetensi (Tinggi)',
            'description' => 'Skor Anda secara umum sudah baik di berbagai bidang kecerdasan. Ini menunjukkan Anda memiliki landasan yang kuat untuk berbagai karir di bidang TI.'
            // Karir dihapus, sesuai permintaan
        ]
    ];

    return [
        'cluster_id' => $cluster_id,
        'result' => $cluster_mapping[$cluster_id]
    ];
}

/**
 * Membangun teks HTML untuk rekomendasi K-Means.
 */
function buildKMeansRecommendationText($userName, $kmeans_result) {
    $result = $kmeans_result['result'] ?? [];
    $cluster_name = isset($result['name']) ? $result['name'] : 'Kelompok Tidak Diketahui';
    $description = isset($result['description']) ? $result['description'] : '';

    return "<div class='system-recommendation'>
        <h3><strong>Rekomendasi Sistem (K-Means Clustering)</strong></h3>
        <p>Halo " . htmlspecialchars($userName) . ", ini hasil analisis potensimu menggunakan <strong>K-Means Clustering (K=2)</strong>:</p>
        <p><strong>📌 Kelompok Anda:</strong> " . htmlspecialchars($cluster_name) . "</p>
        <p><strong>💡 Penjelasan Kelompok:</strong> " . htmlspecialchars($description) . "</p>
        <!-- <p><strong>🎯 Rekomendasi Karir Utama:</strong> (dihapus)</p> -->
        <p><em>Rekomendasi karir spesifik akan diberikan oleh Analisis AI di bawah, berdasarkan kecerdasan dominan Anda.</em></p>
    </div>";
}


/**
 * ====================== ANALISIS AI (DIPERTAHANKAN) ======================
 */
function getGeminiAnalysis($topTwo, $labels) {
    $dominantLabel = $labels[$topTwo[0]];
    $kombinasiLabel = $labels[$topTwo[1]];

    $prompt = "Buat analisis karir profesional, sangat singkat. 
Dominan: {$dominantLabel}. 
Kombinasi pendukung: {$kombinasiLabel}.
Gunakan maksimal 3 paragraf, tiap paragraf maksimal 2 kalimat, dalam <p> tag.
Tanpa sapaan, tanpa nama mahasiswa.
Fokus pada potensi, kekuatan, dan implikasi karir. 
Sebutkan 2-3 rekomendasi karir spesifik di bidang TI yang cocok dengan kombinasi ini.";

    // Panggil fungsi integrasi AI jika tersedia
    if (function_exists('getGeminiResponseTest')) {
        $raw = getGeminiResponseTest($prompt, '', [], true);
        // Pastikan hasil berupa string
        if (!is_string($raw)) {
            $raw = json_encode($raw);
        }
    } else {
        // Fallback yang jelas agar frontend/administrator tahu konfigurasi belum siap
        return "<p><em>AI service belum terkonfigurasi. Silakan konfigurasi fungsi <strong>getGeminiResponseTest()</strong> untuk memanggil layanan AI agar rekomendasi berdasarkan data mahasiswa dapat dibuat.</em></p>";
    }

    // Bersihkan pembungkus kode jika ada dan batasi ke 3 paragraf
    $raw = str_replace(['```html','```'], '', $raw);
    $parts = array_filter(array_map('trim', explode('</p>', $raw)));
    $limited = array_slice($parts, 0, 3);
    $out = '';
    foreach ($limited as $p) {
        if ($p === '') continue;
        // Tambahkan kembali tag penutup jika perlu
        $p = preg_replace('/^<p>/i', '', $p);
        $out .= '<p>' . trim($p) . '</p>';
    }
    return $out;
}

function generateGeminiCareerAnalysis($scores) {
    $labels = [
        'linguistik' => 'Kecerdasan Linguistik',
        'logis_matematis' => 'Kecerdasan Logis-Matematis',
        'spasial' => 'Kecerdasan Visual-Spasial',
        'kinestetik' => 'Kecerdasan Kinestetik',
        'musikal' => 'Kecerdasan Musikal',
        'interpersonal' => 'Kecerdasan Interpersonal',
        'intrapersonal' => 'Kecerdasan Intrapersonal',
        'naturalis' => 'Kecerdasan Naturalis'
    ];

    arsort($scores);
    $topTwo = array_keys(array_slice($scores, 0, 2, true));

    $aiText = getGeminiAnalysis($topTwo, $labels);

    return "<div class='ai-analysis'>
        <h3 class='text-primary'><strong>Analisis AI & Rekomendasi Karir</strong></h3>
        {$aiText}
    </div>";
}

function saveTestResult($pdo, $userId, $scores, $system_recommendation, $ai_recommendation) {
    $final_recommendation_for_db = 
        $system_recommendation .
        "<hr style='margin:20px 0;'>" .
        $ai_recommendation;

    $sql_save = "INSERT INTO test_results 
        (user_id, skor_linguistik, skor_logis_matematis, skor_spasial, skor_kinestetik, skor_musikal, skor_interpersonal, skor_intrapersonal, skor_naturalis, rekomendasi_teks)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        skor_linguistik=VALUES(skor_linguistik), skor_logis_matematis=VALUES(skor_logis_matematis),
        skor_spasial=VALUES(skor_spasial), skor_kinestetik=VALUES(skor_kinestetik),
        skor_musikal=VALUES(skor_musikal), skor_interpersonal=VALUES(skor_interpersonal),
        skor_intrapersonal=VALUES(skor_intrapersonal), skor_naturalis=VALUES(skor_naturalis),
        rekomendasi_teks=VALUES(rekomendasi_teks), tanggal_tes=NOW()";

    $stmt_save = $pdo->prepare($sql_save);
    $stmt_save->execute([
        $userId,
        $scores['linguistik'], $scores['logis_matematis'], $scores['spasial'],
        $scores['kinestetik'], $scores['musikal'], $scores['interpersonal'],
        $scores['intrapersonal'], $scores['naturalis'],
        $final_recommendation_for_db
    ]);
}

/**
 * Memanggil layanan AI eksternal menggunakan API key dari environment.
 * - Set environment variable GEMINI_API_KEY (dan optional GEMINI_API_ENDPOINT).
 * - Mengembalikan string hasil (default) atau array/raw jika $return_raw=true.
 */
function getGeminiResponseTest(string $prompt, ?string $model = null, array $options = [], bool $return_raw = false) {
    $apiKey = "AIzaSyB8HkMqO2o1j_g5Mq5nK0zHzjyaQXAz5tQ";
    $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

    if (empty($apiKey)) {
        return $return_raw ? ['error' => 'API key not configured (GEMINI_API_KEY)'] : '<p><em>AI API key belum dikonfigurasi. Set GEMINI_API_KEY di environment.</em></p>';
    }

    $payload = $options;
    $payload['prompt'] = $prompt;
    if ($model) $payload['model'] = $model;

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        return $return_raw ? ['error' => $curlErr] : '<p><em>Request error: ' . htmlspecialchars($curlErr) . '</em></p>';
    }

    $data = json_decode($resp, true);
    if ($return_raw) return $data ?? $resp;

    // Ekstrak teks dari beberapa bentuk respons umum
    if (is_array($data)) {
        if (isset($data['choices'][0]['text'])) return $data['choices'][0]['text'];
        if (isset($data['output'])) return is_string($data['output']) ? $data['output'] : json_encode($data['output']);
        if (isset($data['data'][0]['text'])) return $data['data'][0]['text'];
    }

    // Fallback ke string mentah
    return is_string($resp) ? $resp : json_encode($resp);
}
?>

