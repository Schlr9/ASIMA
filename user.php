<?php
// user.php
// VERSI DEBUGGING: Menulis semua input ke file log.

// --- BLOK DEBUGGING ---
ini_set('display_errors', 1);
error_reporting(E_ALL);

$log_file = __DIR__ . '/debug_log.txt';
$raw_input = file_get_contents('php://input');
$log_data = [
    'timestamp' => date('Y-m-d H:i:s'),
    'file_called' => 'user.php',
    'request_method' => $_SERVER['REQUEST_METHOD'],
    'get_params' => $_GET,
    'post_params' => $_POST,
    'raw_input_body' => $raw_input
];
file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
// --- AKHIR BLOK DEBUGGING ---

ob_start();

// DIPERBAIKI: Memuat utils.php terlebih dahulu agar fungsi error tersedia.
require_once 'utils.php';
require_once 'config.php';
require_once 'user_functions.php';
require_once 'test_and_recommendation.php';
require_once 'chatbot.php'; 

try {
    session_start();
    
    if (!isset($_SESSION['user'])) {
        send_json_response(['error' => 'Unauthorized session.'], 401);
    }

    $post_data = json_decode($raw_input, true) ?? [];
    $action = $_GET['action'] ?? $_POST['action'] ?? $post_data['action'] ?? '';
    $action = trim($action);

    switch ($action) {
        case 'get_academic_transcript':
            getAcademicTranscript($pdo);
            break;
        case 'get_test_questions':
            getTestQuestions($pdo);
            break;
        case 'submit_test':
            submitTest($pdo, $post_data);
            break;
        case 'ask_general':
            handleGeneralQuestion($pdo, $post_data);
            break;
        case 'update_password':
            updatePassword($pdo, $post_data);
            break;
        default:
            send_json_response(['error' => "Invalid action provided for user.php: '" . htmlspecialchars($action) . "'"], 400);
            break;
    }

    $pdo = null;

} catch (Throwable $e) {
    file_put_contents($log_file, "EXCEPTION in user.php: " . $e->getMessage() . "\n\n", FILE_APPEND);
    send_json_response(['error' => 'An unexpected server error occurred.', 'message' => $e->getMessage()], 500);
}

ob_end_flush();
?>