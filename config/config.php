<?php
// config.php
// File untuk menyimpan semua konfigurasi dengan koneksi PDO.

// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ganti dengan username database Anda
define('DB_PASS', '');     // Ganti dengan password database Anda
define('DB_NAME', 'db_asima'); // Ganti dengan nama database Anda
define('DB_CHARSET', 'utf8mb4');

// Konfigurasi Gemini API
define('GEMINI_API_KEY', 'AIzaSyBApbt_QEIxZWRXY5V5ebw1wRfiKyYDWuI');

// Membuat DSN (Data Source Name) untuk PDO
$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Melempar exception jika ada error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Mengembalikan hasil sebagai array asosiatif
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Menggunakan prepared statements asli
];

try {
    // Membuat instance PDO
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // Gunakan fungsi send_json_response jika sudah didefinisikan, jika tidak, die()
    if (function_exists('send_json_response')) {
        send_json_response(['error' => 'Database connection failed: ' . $e->getMessage()], 503);
    } else {
        header('Content-Type: application/json');
        http_response_code(503);
        die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
    }
}
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=db_asima;charset=utf8mb4",
        "root", 
        "",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}
?>
