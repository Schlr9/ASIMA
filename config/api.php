<?php
// api.php
// File endpoint tunggal untuk semua permintaan aplikasi.

// Debug: Aktifkan error reporting agar error PHP terlihat di browser
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (ob_get_level() == 0) ob_start();

// --- FIX: baca php://input hanya sekali dan tulis log ke path yang benar ---
$raw_input = file_get_contents('php://input');
$logMsg = date('Y-m-d H:i:s') . " | REQUEST ke api.php | URI: " . ($_SERVER['REQUEST_URI'] ?? '') . " | POST: " . $raw_input . "\n";
file_put_contents(__DIR__ . '/debug_log.txt', $logMsg, FILE_APPEND);


require_once '../utils.php'; // Naik satu level ke root, lalu cari utils.php
require_once '../user_functions.php'; // Naik satu level
require_once '../test_and_recommendation.php'; // Naik satu level
require_once '../chatbot.php'; // Naik satu level
require_once 'config.php'; // Tetap, karena config.php ada di folder yang sama




try {
    session_start();

    // Cek otorisasi
    if (!isset($_SESSION['user'])) {
        send_json_response(['error' => 'Unauthorized session. Please login again.'], 401);
        // Jangan exit manual, biarkan send_json_response yang exit
    }

    // Tentukan action dari GET atau POST
    $post_data = json_decode($raw_input, true) ?? [];
    $action = $_GET['action'] ?? $_POST['action'] ?? $post_data['action'] ?? '';
    $action = trim($action);


    // Jalankan switch berdasarkan action
    switch ($action) {
        // --- Test Functions ---
        case 'get_test_questions':
            getTestQuestions($pdo);
            break;
        case 'submit_test':
            submitTest($pdo, $post_data);
            break;
        // --- Chatbot Functions ---
        case 'ask_general':
            handleGeneralQuestion($pdo, $post_data);
            break;
        // --- User Functions ---
        case 'update_password':
            updatePassword($pdo, $post_data);
            break;
        case 'load_conversations':
            loadConversationsFromDB($pdo, $_SESSION['user']['id']);
            break;
        case 'save_conversation':
            saveConversationToDB($pdo, $_SESSION['user']['id'], $post_data['conversation']);
            break;
        case 'delete_conversation':
            deleteConversationFromDB($pdo, $_SESSION['user']['id'], $post_data['conversationId']);
            break;
        case 'ask_and_generate_title':
            handleFirstQuestionAndGenerateTitle($pdo, $post_data);
            break;
        default:
            send_json_response(['error' => "Invalid action provided: '" . $action . "'"], 400);
            break;
    }

    // Menutup koneksi PDO
    $pdo = null;


} catch (Throwable $e) {
    // Log error ke debug_log.txt
    $logMsg = date('Y-m-d H:i:s') . " | " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
    file_put_contents(__DIR__ . '/debug_log.txt', $logMsg, FILE_APPEND);
    if (ob_get_length()) ob_end_clean();
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    http_response_code(500);
    echo json_encode([
        'error' => 'An unexpected server error occurred.',
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
    exit;
}

if (ob_get_length()) ob_end_clean();
?>