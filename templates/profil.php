<?php
session_start();
require_once '../config/config.php'; // Menyediakan koneksi database $pdo

// Jika pengguna tidak login, alihkan ke halaman utama/login
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}
$user = $_SESSION['user'];
$userId = $user['id'];

// Mengambil hasil tes bakat terakhir milik pengguna
$test_result = null;
try {
    $stmt = $pdo->prepare("SELECT * FROM test_results WHERE user_id = ? ORDER BY tanggal_tes DESC LIMIT 1");
    $stmt->execute([$userId]);
    $test_result = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Menangani error database dengan baik tanpa menghentikan seluruh halaman
    error_log("Database Error: " . $e->getMessage());
    $db_error = "Gagal memuat data hasil tes.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - <?php echo htmlspecialchars($user['nama_lengkap']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-gray-800 dark:text-gray-200 font-sans transition-colors duration-300">

<div class="w-full max-w-4xl mx-auto my-8 sm:my-12 md:my-16 px-4">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden">
        
        <div class="relative h-32 md:h-40 bg-blue-600">
            <div class="absolute -bottom-12 left-1/2 -translate-x-1/2 w-24 h-24 rounded-full border-4 border-white dark:border-gray-800 shadow-lg bg-blue-600 flex items-center justify-center text-4xl font-bold text-white select-none">
                <?php echo strtoupper(substr($user['nama_lengkap'], 0, 1)); ?>
            </div>
        </div>
        
        <div class="pt-16 pb-4 text-center border-b border-gray-200 dark:border-gray-700">
            <h1 class="text-2xl md:text-3xl font-bold"><?php echo htmlspecialchars($user['nama_lengkap']); ?></h1>
            <p class="text-gray-500 dark:text-gray-400"><?php echo htmlspecialchars($user['npm']); ?></p>
        </div>

        <div class="flex justify-center border-b border-gray-200 dark:border-gray-700">
            <a href="../index.php" class="flex-1 p-4 text-center font-semibold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"></path><rect x="4" y="12" width="16" height="8" rx="1"></rect><path d="M2 12h2"></path><path d="M20 12h2"></path><path d="M12 12v4"></path></svg>
                Asisten Chat
            </a>
            <a href="#" class="flex-1 p-4 text-center font-bold text-blue-600 border-b-2 border-blue-600 flex items-center justify-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                Profil Saya
            </a>
            <a href="../config/logout.php" onclick="return confirm('Apakah Anda yakin ingin logout?');" title="Logout" class="flex-1 p-4 text-center font-semibold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex items-center justify-center gap-2">
                 <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                Logout
            </a>
        </div>

        <div class="p-4 md:p-8 grid grid-cols-1 lg:grid-cols-2 gap-6 md:gap-8">
            
            <div class="space-y-6 md:space-y-8">
                <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold mb-4">📝 Data Diri</h2>
                    <div class="space-y-2">
                        <p><strong>Fakultas:</strong> <?php echo htmlspecialchars($user['fakultas']); ?></p>
                        <p><strong>Program Studi:</strong> <?php echo htmlspecialchars($user['prodi']); ?></p>
                    </div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold mb-4">🔒 Ganti Sandi</h2>
                    <form id="changePasswordForm" class="space-y-4">
                        <div>
                            <label for="old_password" class="block text-sm font-medium mb-1">Sandi Lama</label>
                            <input type="password" id="old_password" name="old_password" autocomplete="current-password" class="block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label for="new_password" class="block text-sm font-medium mb-1">Sandi Baru</label>
                            <input type="password" id="new_password" name="new_password" autocomplete="new-password" class="block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <div>
                            <label for="confirm_password" class="block text-sm font-medium mb-1">Konfirmasi Sandi Baru</label>
                            <input type="password" id="confirm_password" autocomplete="new-password" class="block w-full bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                        </div>
                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition-colors">Perbarui Sandi</button>
                        <p id="password-feedback" class="text-sm text-center h-4 mt-2"></p> 
                    </form>
                </div>
            </div>
            
            <div class="space-y-6 md:space-y-8">
                <div class="bg-gray-50 dark:bg-gray-900/50 p-6 rounded-lg border border-gray-200 dark:border-gray-700">
                    <h2 class="text-xl font-bold mb-4">🧭 Hasil Tes Bakat Terakhir</h2>
                    <div id="test-result-container">
                        <?php if ($test_result): ?>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mb-4 text-center">Diambil pada: <?php echo date("d F Y, H:i", strtotime($test_result['tanggal_tes'])); ?></p>
                            <div class="relative mb-4 h-64 md:h-80">
                                <canvas id="careerChart"></canvas>
                            </div>
                            <div class="text-left text-sm prose prose-sm dark:prose-invert max-w-none"><?php echo $test_result['rekomendasi_teks']; ?></div>
                        <?php elseif (isset($db_error)): ?>
                             <p class="text-red-500 text-center my-8"><?php echo $db_error; ?></p>
                        <?php else: ?>
                            <div class="text-center py-8">
                                <p class="text-gray-500 dark:text-gray-400 mb-4">Anda belum pernah mengambil tes bakat.</p>
                                <a href="../index.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg inline-block">Mulai Tes Sekarang</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- Logika untuk merender Radar Chart ---
    <?php if ($test_result): ?>
    const scores = <?php echo json_encode([
        'linguistik' => $test_result['skor_linguistik'] ?? 0,
        'logis_matematis' => $test_result['skor_logis_matematis'] ?? 0,
        'spasial' => $test_result['skor_spasial'] ?? 0,
        'kinestetik' => $test_result['skor_kinestetik'] ?? 0,
        'musikal' => $test_result['skor_musikal'] ?? 0,
        'interpersonal' => $test_result['skor_interpersonal'] ?? 0,
        'intrapersonal' => $test_result['skor_intrapersonal'] ?? 0,
        'naturalis' => $test_result['skor_naturalis'] ?? 0,
    ]); ?>;
    
    const chartCanvas = document.getElementById('careerChart');
    if (chartCanvas) {
        renderRadarChart(chartCanvas, scores);
    }
    <?php endif; ?>

    // --- Logika untuk formulir ganti sandi ---
    const passwordForm = document.getElementById('changePasswordForm'); // ✅ ID form diperbaiki
    if (passwordForm) {
        passwordForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const feedbackEl = document.getElementById('password-feedback');
            const oldPassword = document.getElementById('old_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;

            // Validasi di sisi klien terlebih dahulu
            if (newPassword !== confirmPassword) {
                feedbackEl.textContent = 'Sandi baru dan konfirmasi tidak cocok!';
                feedbackEl.className = 'text-sm text-center text-red-500';
                return;
            }

            feedbackEl.textContent = 'Memproses...';
            feedbackEl.className = 'text-sm text-center text-yellow-500';

            try {
                const response = await fetch('../config/api.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'update_password',
                        old_password: oldPassword,
                        new_password: newPassword
                    })
                });
                const data = await response.json();
                
                if (data.success) {
                    feedbackEl.textContent = data.message;
                    feedbackEl.className = 'text-sm text-center text-green-500';
                    passwordForm.reset();
                } else {
                    feedbackEl.textContent = data.message;
                    feedbackEl.className = 'text-sm text-center text-red-500';
                }
            } catch(error) {
                feedbackEl.textContent = 'Terjadi kesalahan saat menghubungi server.';
                feedbackEl.className = 'text-sm text-center text-red-500';
            }
        });
    }
});

function renderRadarChart(canvasElement, scores) {
    const ctx = canvasElement.getContext('2d');
    
    // [PERBAIKAN] Kalikan skor 0-10 dengan 10 agar menjadi 0-100 untuk chart
    const chartData = [
        (scores.linguistik || 0) * 10, 
        (scores.logis_matematis || 0) * 10, 
        (scores.spasial || 0) * 10,
        (scores.kinestetik || 0) * 10, 
        (scores.musikal || 0) * 10, 
        (scores.interpersonal || 0) * 10,
        (scores.intrapersonal || 0) * 10, 
        (scores.naturalis || 0) * 10
    ];

    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: ['Linguistik', 'Logis Matematis', 'Spasial', 'Kinestetik', 'Musikal', 'Interpersonal', 'Intrapersonal', 'Naturalis'],
            datasets: [{
                label: 'Skor Bakat (%)',
                data: chartData, // Menggunakan data yang sudah dikali 10
                fill: true,
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                borderColor: 'rgb(59, 130, 246)',
                pointBackgroundColor: 'rgb(59, 130, 246)',
                pointBorderColor: '#fff',
                pointHoverBackgroundColor: '#fff',
                pointHoverBorderColor: 'rgb(59, 130, 246)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                r: {
                    suggestedMin: 0,
                    suggestedMax: 100, // Skala chart tetap 0-100
                    pointLabels: { 
                        font: { size: 10 },
                        color: document.documentElement.classList.contains('dark') ? '#fff' : '#fff'
                    },
                    grid: { color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)' },
                    angleLines: { color: document.documentElement.classList.contains('dark') ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)' },
                    ticks: {
                        backdropColor: 'rgba(0,0,0,0)',
                        color: document.documentElement.classList.contains('dark') ? '#fff' : '#fff'
                    }
                }
            }
        }
    });
}
</script>
</body>
</html>