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

        // --- 1. Hitung Skor Mentah (Skala 0-10) ---
        $scores = calculateRawScores($pdo, $answers);

        // --- 2. Konversi Skor ke Persentase (HANYA UNTUK AI & FRONTEND CHART) ---
        $max_score_per_category = 10;
        $percentage_scores = [];
        foreach ($scores as $kat => $score) {
            $percentage_scores[$kat] = round(($score / $max_score_per_category) * 100);
        }

        // --- 3. Rekomendasi Sistem (Menggunakan K-Means K=2) ---
        $result_kmeans = getKMeansRecommendation($scores); 
        // [MODIFIKASI] Dapatkan teks bersih TANPA disclaimer
        $system_recommendation_clean = buildKMeansRecommendationText($userName, $result_kmeans);

        // --- 4. Analisis AI (ringkas, berdasarkan top 2 dominant) ---
        $ai_recommendation_clean = generateGeminiCareerAnalysis($percentage_scores);

        // --- 5. Simpan Hasil (BERSIH) ke Database ---
        // [MODIFIKASI] Kirim versi bersih ke database
        saveTestResult($pdo, $userId, $scores, $system_recommendation_clean, $ai_recommendation_clean);

        // --- [MODIFIKASI] 6. Buat Teks Disclaimer HANYA untuk Chat ---
        $disclaimer_html = "
        <div class='disclaimer-box' style='background-color: #fefce8; border: 1px solid #eab308; color: #a16207; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 0.9em; line-height: 1.5;'>
            <strong>Perhatian:</strong> Tes ini dilakukan hanya untuk inventaris Anda, bukan tes psikologi ataupun tes lainnya. Tes ini dilakukan untuk memberi gambaran pada Anda yang melibatkan 8 kecerdasan.
        </div>";
        
        // Gabungkan disclaimer HANYA untuk respons JSON
        $system_recommendation_for_chat = $disclaimer_html . $system_recommendation_clean;

        // --- 7. Kirim Respons (dengan Disclaimer) ke Frontend ---
        send_json_response([
            'recommendation_system' => $system_recommendation_for_chat, // Kirim versi dengan disclaimer
            'recommendation_ai' => $ai_recommendation_clean, // Kirim AI bersih
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
 * [PERUBAHAN] Fungsi ini sekarang menerima $scores (0-10) dan menggunakan Centroid Iterasi 9.
 */
function getKMeansRecommendation($scores) {
    // Urutan kategori HARUS konsisten untuk kalkulasi vektor
    $categories_order = [
        'linguistik', 'logis_matematis', 'spasial', 'kinestetik', 
        'musikal', 'interpersonal', 'intrapersonal', 'naturalis'
    ];

    // Konversi skor user (associative array) ke vektor numerik (Skala 0-10)
    $user_vector = [];
    foreach ($categories_order as $cat) {
        $user_vector[] = $scores[$cat] ?? 0;
    }

    // --- [PERUBAHAN] ---
    // Definisikan Centroid (Berdasarkan data C1 dan C2 dari Iterasi 9)
    // Pastikan menggunakan titik (.) sebagai pemisah desimal
    
    // C1 (Klaster Rendah - Iterasi 9)
    // Urutan: linguistik, logis_matematis, spasial, kinestetik, musikal, interpersonal, intrapersonal, naturalis
    $centroid_0_vector = [1.78788, 1.90909, 1.69697, 2.09091, 1.78788, 1.66667, 1.9697, 1.72727]; 
    
    // C2 (Klaster Tinggi - Iterasi 9)
    $centroid_1_vector = [5.35294, 7.41176, 6.0, 6.52941, 6.35294, 5.29412, 6.88235, 6.05882];
    // --- [AKHIR PERUBAHAN] ---
    
    // Hitung jarak Euclidean
    $dist_0 = euclideanDistance($user_vector, $centroid_0_vector);
    $dist_1 = euclideanDistance($user_vector, $centroid_1_vector);

    // Tentukan klaster terdekat
    // Cluster 0 = Jarak ke C1 'Rendah' lebih dekat
    // Cluster 1 = Jarak ke C2 'Tinggi' lebih dekat
    $cluster_id = ($dist_0 < $dist_1) ? 0 : 1;

    // MODIFIKASI: Mapping hasil klaster ke rekomendasi
    $cluster_mapping = [
        0 => [
            'name' => 'Kelompok Kompetensi (Perlu Peningkatan)',
            'description' => 'Skor Anda secara umum menunjukkan perlunya peningkatan di berbagai bidang kecerdasan. Ini adalah kesempatan untuk mengeksplorasi dan menemukan potensi terkuat Anda.'
        ],
        1 => [
            'name' => 'Kelompok Kompetensi (Tinggi)',
            'description' => 'Skor Anda secara umum sudah baik di berbagai bidang kecerdasan. Ini menunjukkan Anda memiliki landasan yang kuat untuk berbagai karir di bidang TI.'
        ]
    ];

    return [
        'cluster_id' => $cluster_id,
        'result' => $cluster_mapping[$cluster_id]
    ];
}

/**
 * [MODIFIKASI] Membangun teks HTML untuk rekomendasi K-Means (Versi Bersih).
 */
function buildKMeansRecommendationText($userName, $kmeans_result) {
    $result = $kmeans_result['result'] ?? [];
    $cluster_name = isset($result['name']) ? $result['name'] : 'Kelompok Tidak Diketahui';
    $description = isset($result['description']) ? $result['description'] : '';

    // --- [MODIFIKASI] Disclaimer DIHAPUS dari sini ---

    return "<div class='system-recommendation style='margin-bottom: 20px; padding: 2em;'>
        <h3><strong>Rekomendasi Sistem (K-Means Clustering)</strong></h3>
        <p>Halo " . htmlspecialchars($userName) . ", ini hasil analisis potensimu menggunakan <strong>K-Means Clustering (K=2)</strong>:</p>
        <p><strong>📌 Kelompok Anda:</strong> " . htmlspecialchars($cluster_name) . "</p>
        <p><strong>💡 Penjelasan Kelompok:</strong> " . htmlspecialchars($description) . "</p>
        <p><em>Rekomendasi karir spesifik akan diberikan oleh Analisis AI di bawah, berdasarkan kecerdasan dominan Anda.</em></p>
    </div>";
}


/**
 * ====================== ANALISIS AI (DIPERTAHANKAN) ======================
 */
 
/**
 * FUNGSI INI DISESUAIKAN (KODE PERBAIKAN):
 * 1. Untuk memeriksa 'error' dalam $rawResponse.
 * 2. Untuk mengekstrak teks dari $rawResponse['candidates'][...].
 * 3. [UPDATE] Memperbaiki prompt agar meminta HTML <strong>, bukan asterisks.
 */
function getGeminiAnalysis($topTwo, $labels) {
    $dominantLabel = $labels[$topTwo[0]];
    $kombinasiLabel = $labels[$topTwo[1]];

    // --- PERBAIKAN PROMPT (Meminta <strong>) ---
    $prompt = "Analisis karir profesional yang sangat singkat.
Kecerdasan dominan: {$dominantLabel}.
Kecerdasan pendukung: {$kombinasiLabel}.

Instruksi:
1. Tulis dalam 3 paragraf, masing-masing dalam tag <p>.
2. Tiap paragraf maksimal 2 kalimat.
3. JANGAN gunakan sapaan (seperti Halo) atau kalimat pembuka (seperti 'Berikut analisis...').
4. Langsung mulai dengan paragraf analisis potensi dan kekuatan.
5. Fokus pada potensi, kekuatan, dan implikasi karir.
6. Sebutkan 2-3 rekomendasi karir spesifik di bidang TI pada paragraf terakhir.
7. PENTING: Saat menyebutkan nama karir (misal: Technical Writer), gunakan tag HTML <strong> untuk membuatnya tebal (contoh: <strong>Technical Writer</strong>). JANGAN gunakan asterisks (*).";
    // --- AKHIR PERBAIKAN PROMPT ---

    $raw = ''; // Inisialisasi $raw sebagai string

    // Panggil fungsi integrasi AI jika tersedia
    if (function_exists('getGeminiResponseTest')) {
        // $return_raw=true akan mengembalikan ARRAY dari API
        $rawResponse = getGeminiResponseTest($prompt, '', [], true);

        // --- PERBAIKAN: Cek error di sini ---
        if (isset($rawResponse['error'])) {
             // Jika ada error, langsung kembalikan pesan error
             $errorMsg = is_array($rawResponse['error']) ? ($rawResponse['error']['message'] ?? json_encode($rawResponse['error'])) : $rawResponse['error'];
             return "<p><em>Gagal mendapatkan analisis AI: " . htmlspecialchars($errorMsg) . "</em></p>";
        }
        
        // Ekstrak teks dari respons sukses (format generateContent)
        $aiText = $rawResponse['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($aiText === null) {
            // Jika sukses tapi format tidak dikenali
            return "<p><em>Gagal mem-parsing respons AI. Format tidak dikenali.</em></p>";
        }
        
        // $raw sekarang adalah teks bersih dari AI
        $raw = $aiText;

    } else {
        // Fallback jika fungsi tidak ada
        return "<p><em>AI service belum terkonfigurasi. Silakan konfigurasi fungsi <strong>getGeminiResponseTest()</strong> untuk memanggil layanan AI agar rekomendasi berdasarkan data mahasiswa dapat dibuat.</em></p>";
    }

    // Bersihkan pembungkus kode jika ada dan batasi ke 3 paragraf
    // Kode ini sekarang membersihkan TEKS dari AI, bukan JSON ERROR
    $raw = str_replace(['```html','```'], '', $raw);
    
    // Perbaikan: Hapus tag <p> yang mungkin ganda dari AI sebelum memecah
    $raw = preg_replace('/^<p>/i', '', $raw);
    $raw = preg_replace('/<\/p>$/i', '', $raw);

    $parts = array_filter(array_map('trim', explode('</p><p>', $raw)));
    
    // Jika explode di atas gagal (karena AI tidak menggunakan </p><p> sebagai pemisah), 
    // coba explode standar
    if (count($parts) < 2) {
         $parts = array_filter(array_map('trim', explode('</p>', $raw)));
    }
    
    $limited = array_slice($parts, 0, 3);
    $out = '';
    foreach ($limited as $p) {
        if ($p === '') continue;
        // Tambahkan kembali tag pembuka/penutup
        $p = preg_replace('/^<p>/i', '', $p); // Hapus jika masih ada
        $p = preg_replace('/<\/p>$/i', '', $p); // Hapus jika masih ada
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

    arsort($scores); // $scores di sini adalah persentase (0-100)
    $topTwo = array_keys(array_slice($scores, 0, 2, true));

    $aiText = getGeminiAnalysis($topTwo, $labels);

    return "<div class='ai-analysis'>
        <h3 class='text-primary'><strong>Analisis AI & Rekomendasi Karir</strong></h3>
        {$aiText}
    </div>";
}

/**
 * [MODIFIKASI] Fungsi ini sekarang menerima $system_recommendation (BERSIH)
 */
function saveTestResult($pdo, $userId, $scores, $system_recommendation, $ai_recommendation) {
    
    // [MODIFIKASI] $system_recommendation di sini adalah versi BERSIH (TANPA disclaimer)
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
        $scores['linguistik'], $scores['logis_matematis'], $scores['spasial'], // Ini sekarang skor 0-10
        $scores['kinestetik'], $scores['musikal'], $scores['interpersonal'],
        $scores['intrapersonal'], $scores['naturalis'],
        $final_recommendation_for_db
    ]);
}

/**
 * Memanggil layanan AI eksternal (Google Gemini) menggunakan API key.
 * * FUNGSI INI SUDAH DIPERBAIKI (KODE PERBAIKAN):
 * 1. Menghapus header 'Authorization: Bearer' yang salah (penyebab 401).
 * 2. Mengubah format payload agar sesuai dengan endpoint 'generateContent' (Gemini).
 * 3. Menambahkan penanganan error HTTP status dari cURL.
 */
function getGeminiResponseTest(string $prompt, ?string $model = null, array $options = [], bool $return_raw = false) {
    // API Key Anda
    $apiKey = "API-Key"; 
    
    // Endpoint sudah benar (menggunakan ?key=...)
    $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $apiKey;

    if (empty($apiKey)) {
        $errorMsg = 'API key not configured (GEMINI_API_KEY)';
        return $return_raw ? ['error' => $errorMsg] : '<p><em>' . $errorMsg . '</em></p>';
    }

    // --- PERBAIKAN 2: Format Payload (Body) ---
    // Model Gemini mengharapkan format 'contents', bukan 'prompt'
    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];
    
    // Opsi tambahan (jika ada) seperti 'generationConfig' atau 'safetySettings'
    if (isset($options['generationConfig'])) {
         $payload['generationConfig'] = $options['generationConfig'];
    }
    if (isset($options['safetySettings'])) {
         $payload['safetySettings'] = $options['safetySettings'];
    }

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // --- PERBAIKAN 1: Hapus Header 'Authorization: Bearer' ---
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        // 'Authorization: Bearer ' . $apiKey, // <-- INI PENYEBAB 401 - HAPUS
        'Content-Type: application/json'
    ]);
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 20); // Tambahkan waktu timeout sedikit

    $resp = curl_exec($ch);
    $curlErr = curl_error($ch);
    $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($curlErr) {
        return $return_raw ? ['error' => $curlErr] : '<p><em>Request error: ' . htmlspecialchars($curlErr) . '</em></p>';
    }

    $data = json_decode($resp, true);

    // --- PERBAIKAN 3: Penanganan Error HTTP Status ---
    // Jika status bukan 200 OK, kembalikan format error
    if ($httpStatus != 200) {
        $errorMsg = $data['error']['message'] ?? $resp;
        if ($return_raw) {
            return ['error' => $errorMsg, 'status' => $httpStatus];
        }
        return '<p><em>AI Error (HTTP ' . $httpStatus . '): ' . htmlspecialchars($errorMsg) . '</em></p>';
    }

    // Jika $return_raw=true, kembalikan data (sudah sukses 200 OK)
    if ($return_raw) return $data ?? $resp;

    // Ekstrak teks dari respons Gemini (format generateContent)
    if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
        return $data['candidates'][0]['content']['parts'][0]['text'];
    }
    
    // Fallback jika format respons tidak terduga
    $fallbackMsg = 'Gagal mem-parsing respons AI. Respons: ' . htmlspecialchars($resp);
    return $return_raw ? ['error' => $fallbackMsg] : '<p><em>' . $fallbackMsg . '</em></p>';
}
?>
