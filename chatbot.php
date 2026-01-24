<?php
// includes/chatbot_functions.php (Versi Modifikasi: Profesional & Kontekstual)

/**
 * =================================================================================
 * PUSAT KONFIGURASI TOPIC DETECTION
 * =================================================================================
 */
// (Tidak diubah, tetap digunakan untuk logik internal jika diperlukan)
const TECHNICAL_KEYWORDS = [
    'pendaftaran', 'biaya', 'jadwal', 'akademik', 'fakultas', 'ukt', 'spp', 
    'kalender', 'beasiswa', 'nilai', 'test bakat', 'transkrip', 'kampus', 
    'universitas', 'kuliah', 'jurusan', 'dekan', 'dosen', 'mahasiswa'
];

/**
 * =================================================================================
 * FUNGSI UTAMA UNTUK MENANGANI PERTANYAAN
 * =================================================================================
 */

/**
 * Menangani pertanyaan umum dari pengguna.
 */
function handleGeneralQuestion($pdo, $data) {
    $question = $data['question'] ?? '';
    $history = $data['history'] ?? [];
    $userId = $_SESSION['user']['id'];
    $userName = $_SESSION['user']['nama_lengkap'];

    // [BARU] Ambil hasil tes terakhir pengguna
    $latest_test_result = getLatestTestResult($pdo, $userId);
    
    // Dapatkan balasan dari AI
    $gemini_reply = getGeminiResponse($question, $userName, $history, $latest_test_result);

    // Catat interaksi dan kirim balasan
    logChatInteraction($pdo, $userId, $question, $gemini_reply);
    send_json_response(['reply' => $gemini_reply]);
}

/**
 * Menangani pesan pertama untuk menghasilkan judul baru dan mendapatkan balasan.
 */
function handleFirstQuestionAndGenerateTitle($pdo, $data) {
    $question = $data['question'] ?? '';
    $conversationId = $data['conversation_id'] ?? null;
    $history = $data['history'] ?? [];
    $userId = $_SESSION['user']['id'];
    $userName = $_SESSION['user']['nama_lengkap'];

    if (!$conversationId) {
        error_log("Gagal update judul: conversation_id tidak diterima dari frontend.");
        return handleGeneralQuestion($pdo, $data); // Fallback ke fungsi standar
    }

    // [BARU] Ambil hasil tes terakhir pengguna
    $latest_test_result = getLatestTestResult($pdo, $userId);

    // 1. Dapatkan balasan dari AI
    $botReply = getGeminiResponse($question, $userName, $history, $latest_test_result);
    logChatInteraction($pdo, $userId, $question, $botReply);

    // 2. Hasilkan judul baru dari pertanyaan
    // [MODIFIKASI] Memanggil getGeminiResponse untuk judul (tanpa konteks tes)
    $titlePrompt = "Buat judul yang sangat singkat (maksimal 4 kata) dalam Bahasa Indonesia untuk percakapan yang diawali dengan pesan ini: \"{$question}\"";
    $newTitle = getGeminiResponse($titlePrompt, 'System', [], null, true); // Panggil mode 'simple'
    
    // 3. Simpan judul baru ke database
    try {
        $stmt = $pdo->prepare("UPDATE conversations SET title = :title WHERE id = :id AND user_id = :user_id");
        $stmt->execute([
            ':title' => $newTitle,
            ':id' => $conversationId,
            ':user_id' => $userId
        ]);
    } catch (PDOException $e) {
        error_log("Gagal update judul: " . $e->getMessage());
    }
    
    // 4. Kirim balasan dan judul baru ke frontend
    send_json_response(['reply' => $botReply, 'new_title' => $newTitle]);
}

/**
 * =================================================================================
 * FUNGSI INTERAKSI DENGAN GEMINI API
 * =================================================================================
 */

/**
 * [BARU] Mengambil hasil tes terakhir pengguna untuk diberikan sebagai konteks ke AI.
 */
function getLatestTestResult($pdo, $userId) {
    try {
        $sql = "SELECT 
                    c.nama_cluster,
                    tr.skor_linguistik, tr.skor_logis_matematis, tr.skor_spasial,
                    tr.skor_kinestetik, tr.skor_musikal, tr.skor_interpersonal,
                    tr.skor_intrapersonal, tr.skor_naturalis
                FROM test_results tr
                LEFT JOIN clusters c ON tr.cluster_id = c.id
                WHERE tr.user_id = :user_id
                ORDER BY tr.tanggal_tes DESC
                LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return null; // Tidak ditemukan tes
        }

        // Format hasil tes sebagai string konteks untuk AI
        $context = "KONTEKS PENGGUNA (JANGAN DISEBUTKAN KECUALI DITANYA):\n";
        $context .= "Data tes bakat terakhir pengguna:\n";
        $context .= "- Kelompok: " . ($result['nama_cluster'] ?? 'Belum ada') . "\n";
        $context .= "- Skor (Skala 0-10): \n";
        $context .= "  - Linguistik: " . $result['skor_linguistik'] . "\n";
        $context .= "  - Logis: " . $result['skor_logis_matematis'] . "\n";
        $context .= "  - Spasial: " . $result['skor_spasial'] . "\n";
        $context .= "  - Kinestetik: " . $result['skor_kinestetik'] . "\n";
        $context .= "  - Musikal: " . $result['skor_musikal'] . "\n";
        $context .= "  - Interpersonal: " . $result['skor_interpersonal'] . "\n";
        $context .= "  - Intrapersonal: " . $result['skor_intrapersonal'] . "\n";
        $context .= "  - Naturalis: " . $result['skor_naturalis'] . "\n";
        
        return $context;

    } catch (PDOException $e) {
        error_log("Gagal mengambil hasil tes terakhir: " . $e->getMessage());
        return null;
    }
}


/**
 * [MODIFIKASI] Mengirim prompt ke Gemini API dengan instruksi non-empatik dan konteks tes.
 */
function getGeminiResponse($prompt, $userName, $history = [], $latest_test_result = null, $simple_mode = false) {
    $api_key = 'API-KEY';
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $api_key;

    // [MODIFIKASI] Instruksi sistem diubah menjadi profesional dan non-empatik.
    if ($simple_mode) {
        // Mode simpel untuk tugas internal seperti membuat judul
        $system_instruction_text = "Anda adalah AI yang efisien. Berikan jawaban yang sangat singkat sesuai prompt.";
    } else {
        $system_instruction_text = "Peran Anda: Anda adalah asisten virtual 'Asima' dari Universitas Mandiri.
        Anda sedang berbicara dengan mahasiswa '{$userName}'.
        ATURAN:
        1. Jawab pertanyaan secara langsung, informatif, dan profesional.
        2. Gunakan format Markdown (bullet points, bold) jika jawabannya kompleks agar terstruktur dan mudah dibaca.
        3. JANGAN gunakan sapaan ramah (seperti 'Halo', 'Tentu', dll.) di awal jawaban. Langsung ke intinya.";

        // [BARU] Tambahkan konteks hasil tes jika ada
        if ($latest_test_result) {
            $system_instruction_text .= "\n\n" . $latest_test_result;
        }
    }
    // --- [AKHIR MODIFIKASI] ---
    
    $clean_history = sanitize_history_for_gemini($history);
    
    $contents = $clean_history; 
    $contents[] = ['role' => 'user', 'parts' => [['text' => $prompt]]];

    $payload = json_encode([
        'contents' => $contents,
        'systemInstruction' => ['parts' => [['text' => $system_instruction_text]]]
    ]);

    // Proses cURL...
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ['Content-Type:application/json'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 45
    ]);
    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($http_code == 200 && $result) {
        $response_data = json_decode($result, true);
        $reply = $response_data['candidates'][0]['content']['parts'][0]['text'] ?? "Maaf, saya tidak dapat memproses jawaban saat ini.";
        // Bersihkan balasan jika mode simpel (untuk judul)
        if ($simple_mode) {
            $reply = trim(str_replace(['"', "'", "*"], '', $reply));
        }
        return $reply;
    } else {
        error_log("Gemini API Error: (Status: {$http_code}) " . ($error ?: $result));
        return "Maaf, terjadi sedikit gangguan saat menghubungi asisten AI.";
    }
}

/**
 * Menghasilkan judul singkat untuk percakapan berdasarkan pesan pertama.
 * [MODIFIKASI] Fungsi ini sekarang hanya wrapper untuk getGeminiResponse mode simpel.
 */
function generateTitleForConversation($firstMessage) {
    $prompt = "Buat judul yang sangat singkat (maksimal 4 kata) dalam Bahasa Indonesia untuk percakapan yang diawali dengan pesan ini: \"{$firstMessage}\"";
    // Memanggil getGeminiResponse dengan mode simpel
    return getGeminiResponse($prompt, 'System', [], null, true);
}

/**
 * =================================================================================
 * FUNGSI-FUNGSI PEMBANTU
 * =================================================================================
 */

/**
 * [DIHAPUS] Fungsi getEmpatheticInstruction() telah dihapus.
 */
// function getEmpatheticInstruction($userName) { ... }

/**
 * Memeriksa apakah sebuah teks mengandung kata kunci dari daftar yang diberikan.
 */
function isTopicMatch($text, $keywords) {
    foreach ($keywords as $keyword) {
        if (stripos($text, $keyword) !== false) { // Gunakan stripos untuk case-insensitive
            return true;
        }
    }
    return false;
}

/**
 * Mencatat interaksi antara pengguna dan chatbot ke database.
 */
function logChatInteraction($pdo, $userId, $question, $reply) {
    if (empty(trim($question)) || empty(trim($reply))) return;
    try {
        $sql = "INSERT INTO user_questions_log (user_id, question_text, response_text) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $question, $reply]);
    } catch (PDOException $e) {
        error_log("Gagal mencatat interaksi: " . $e->getMessage());
    }
}

/**
 * =================================================================================
 * FUNGSI MANAJEMEN PERCAKAPAN (DATABASE)
 * =================================================================================
 */

function loadConversationsFromDB($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("SELECT id, title, messages FROM conversations WHERE user_id = ? ORDER BY updated_at DESC");
        $stmt->execute([$userId]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($conversations as &$conv) {
            $decoded_messages = json_decode($conv['messages'], true);
            $conv['messages'] = is_array($decoded_messages) ? $decoded_messages : [];
        }

        send_json_response($conversations);
    } catch (PDOException $e) {
        send_json_response(['error' => 'Gagal memuat percakapan: ' . $e->getMessage()], 500);
    }
}

function saveConversationToDB($pdo, $userId, $conversationData) {
    if (empty($conversationData['id'])) {
        send_json_response(['error' => 'Data percakapan tidak lengkap.'], 400); return;
    }

    $id = $conversationData['id'];
    $title = $conversationData['title'] ?? 'Percakapan Baru';
    $messages = $conversationData['messages'] ?? [];
    $testResultJson = null;

    if (is_array($messages)) {
        foreach ($messages as $message) {
            if (isset($message['type']) && $message['type'] === 'test_result' && isset($message['content'])) {
                $testResultJson = json_encode($message['content']);
                break; 
            }
        }
    }
    
    $messagesJson = json_encode($messages);

    try {
        $sql = "INSERT INTO conversations (id, user_id, title, messages, test_result_json, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())
                ON DUPLICATE KEY UPDATE 
                title = VALUES(title), messages = VALUES(messages),
                test_result_json = VALUES(test_result_json), updated_at = NOW()";
                
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id, $userId, $title, $messagesJson, $testResultJson]);
        send_json_response(['success' => true]);
    } catch (PDOException $e) {
        send_json_response(['error' => 'Gagal menyimpan percakapan: ' . $e->getMessage()], 500);
    }
}

function deleteConversationFromDB($pdo, $userId, $conversationId) {
    if (empty($conversationId)) {
        send_json_response(['error' => 'ID percakapan kosong.'], 400); return;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM conversations WHERE id = ? AND user_id = ?");
        $stmt->execute([$conversationId, $userId]);
        send_json_response(['success' => true]);
    } catch (PDOException $e) {
        send_json_response(['error' => 'Gagal menghapus percakapan: ' . $e->getMessage()], 500);
    }
}

/**
 * Membersihkan array history untuk memastikan formatnya valid untuk Gemini API.
 */
function sanitize_history_for_gemini($history) {
    if (!is_array($history)) {
        return []; // Jika history bukan array, kembalikan array kosong
    }

    $clean_history = [];
    foreach ($history as $message) {
        // Pastikan setiap pesan valid: role ada, parts ada, dan text tidak kosong
        if (
            is_array($message) &&
            isset($message['role']) && in_array($message['role'], ['user', 'model']) &&
            isset($message['parts']) && is_array($message['parts']) && !empty($message['parts']) &&
            isset($message['parts'][0]['text']) &&
            trim($message['parts'][0]['text']) !== ''
        ) {
            $clean_history[] = $message;
        }
    }
    return $clean_history;
}
?>
