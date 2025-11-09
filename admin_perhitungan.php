<?php
// admin_perhitungan.php
// Halaman khusus untuk menampilkan perhitungan K-Means untuk semua user.
require_once __DIR__ . '/config/config.php'; // memuat $pdo

/**
 * ===================================================================
 * FUNGSI PERHITUNGAN K-MEANS (Disalin dari admin_user.php)
 * Ini harus identik untuk memastikan konsistensi data.
 * ===================================================================
 */

/**
 * Menghitung jarak Euclidean.
 */
function euclideanDistance($point1, $point2) {
    $sum = 0;
    $dimensions = count($point1);
    for ($i = 0; $i < $dimensions; $i++) {
        if (isset($point1[$i]) && isset($point2[$i])) {
            $sum += pow($point1[$i] - $point2[$i], 2);
        }
    }
    return sqrt($sum);
}

/**
 * Menghitung ulang K-Means menggunakan Centroid Iterasi 9.
 * Menerima array skor mentah (skor_linguistik, dll)
 */
function recalculateKMeans($scores) {
    // Urutan kategori HARUS konsisten
    $categories_order = [
        'linguistik', 'logis_matematis', 'spasial', 'kinestetik', 
        'musikal', 'interpersonal', 'intrapersonal', 'naturalis'
    ];
    
    $user_vector = [];
    foreach ($categories_order as $cat) {
        $user_vector[] = $scores['skor_' . $cat] ?? 0;
    }

    // Centroid C1 (Klaster Rendah - Iterasi 9)
    $centroid_0_vector = [1.78788, 1.90909, 1.69697, 2.09091, 1.78788, 1.66667, 1.9697, 1.72727]; 
    
    // Centroid C2 (Klaster Tinggi - Iterasi 9)
    $centroid_1_vector = [5.35294, 7.41176, 6.0, 6.52941, 6.35294, 5.29412, 6.88235, 6.05882];

    // Hitung jarak Euclidean
    $dist_0 = euclideanDistance($user_vector, $centroid_0_vector);
    $dist_1 = euclideanDistance($user_vector, $centroid_1_vector);

    // Tentukan klaster terdekat
    $cluster_id = ($dist_0 < $dist_1) ? 0 : 1;

    // Mapping nama klaster
    $cluster_mapping = [
        0 => 'Kelompok Kompetensi (Perlu Peningkatan)', // C1
        1 => 'Kelompok Kompetensi (Tinggi)'  // C2
    ];

    return [
        'dist_0'       => round($dist_0, 5), // Jarak ke C1
        'dist_1'       => round($dist_1, 5), // Jarak ke C2
        'cluster_name' => $cluster_mapping[$cluster_id]
    ];
}

/**
 * ===================================================================
 * LOGIKA PENGAMBILAN DATA UTAMA
 * ===================================================================
 */

$perhitungan_data = [];
try {
    // 1. Ambil semua hasil tes beserta data user
    $sql = "SELECT 
                u.id,u.npm, 
                u.nama_lengkap, 
                tr.skor_linguistik, 
                tr.skor_logis_matematis, 
                tr.skor_spasial,
                tr.skor_kinestetik, 
                tr.skor_musikal, 
                tr.skor_interpersonal,
                tr.skor_intrapersonal, 
                tr.skor_naturalis,
                c.nama_cluster AS klaster_tersimpan
            FROM test_results tr
            JOIN users_m u ON tr.user_id = u.id
            LEFT JOIN clusters c ON tr.cluster_id = c.id
            ORDER BY u.id ASC";
            
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $all_results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Hitung ulang K-Means untuk setiap user
    foreach ($all_results as $result) {
        // Fungsi recalculateKMeans mengharapkan array skor
        $calculation = recalculateKMeans($result); 
        
        // Gabungkan data user, skor, dan hasil kalkulasi
        $perhitungan_data[] = array_merge($result, $calculation);
    }

} catch (PDOException $e) {
    $error = "Gagal memuat data: " . $e->getMessage();
}
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Admin - Perhitungan K-Means</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body{font-family:Inter,system-ui,Segoe UI,Roboto,Helvetica,Arial;background:#f3f4f6;padding:28px}
        .card{background:white;border-radius:10px;box-shadow:0 6px 18px rgba(16,24,40,.3);max-width:none;overflow:hidden}
        .header{background:#1e40af;color:white;padding:16px 20px;display:flex;align-items:center;justify-content:space-between}
        .p{padding:18px}
        table{width:100%;border-collapse:collapse;}
        th,td{padding:10px 12px;border:1px solid #e6e7ea;text-align:left;font-size: 0.875rem;}
        th{background:#f9fafb;}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .75rem;border-radius:8px;background:#1e40af;color:white;border:0;cursor:pointer; text-decoration: none;}
        .highlight-c1{background-color: #fefce8; color: #a16207;}
        .highlight-c2{background-color: #f0f9ff; color: #0284c7;}
    </style>
</head>
<body>
    <div class="card" style="max-width:1400px;margin:0 auto">
        <div class="header">
            <h2 style="margin:0">Laporan Perhitungan K-Means (Iterasi 9)</h2>
            <div>
                <a href="admin_user.php" class="btn">Kembali ke Master User</a>
            </div>
        </div>

        <div class="p">
            <div class="small mb-4">Menampilkan hasil perhitungan ulang K-Means untuk semua data tes yang tersimpan.</div>
            
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <div style="overflow:auto">
                <table>
                    <thead>
                        <tr>
                            <th class="w-10">#</th>
                            <th>NPM</th>
                            <th>Nama Lengkap</th>
                            <th>X1 (Ling)</th>
                            <th>X2 (Logis)</th>
                            <th>X3 (Spasial)</th>
                            <th>X4 (Kines)</th>
                            <th>X5 (Musik)</th>
                            <th>X6 (Inter)</th>
                            <th>X7 (Intra)</th>
                            <th>X8 (Nat)</th>
                            <th>Jarak ke C1</th>
                            <th>Jarak ke C2</th>
                            <th>Hasil Klaster (Hitung Ulang)</th>
                            <th>Klaster (Tersimpan)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($perhitungan_data as $index => $data): ?>
                            <?php
                                // Tentukan highlight berdasarkan hasil hitung ulang
                                $highlight_class = $data['cluster_name'] === 'Kelompok Kompetensi (Tinggi)' ? 'highlight-c2' : 'highlight-c1';
                            ?>
                            <tr class="<?php echo $highlight_class; ?>">
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($data['npm']); ?></td>
                                <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($data['skor_linguistik']); ?></td>
                                <td><?php echo htmlspecialchars($data['skor_logis_matematis']); ?></td>
                                <td><?php echo htmlspecialchars($data['skor_spasial']); ?></td>
                                <td><?php echo htmlspecialchars($data['skor_kinestetik']); ?></td>
                                <td><?php echo htmlspecialchars($data['skor_musikal']); ?></td>
                                <td><?php echo htmlspecialchars($data['skor_interpersonal']); ?></td>
                                <td><?php echo htmlspecialchars($data['skor_intrapersonal']); ?></td>
                                <td><?php echo htmlspecialchars($data['skor_naturalis']); ?></td>
                                <td><?php echo htmlspecialchars($data['dist_0']); ?></td>
                                <td><?php echo htmlspecialchars($data['dist_1']); ?></td>
                                <td><?php echo htmlspecialchars(str_replace('Kelompok Kompetensi ', '', $data['cluster_name'])); ?></td>
                                <td><?php echo htmlspecialchars(str_replace('Kelompok Kompetensi ', '', $data['klaster_tersimpan'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <?php if (empty($perhitungan_data)): ?>
                            <tr>
                                <td colspan="15" class="text-center p-4 text-gray-500">
                                    Tidak ada data tes yang ditemukan di database.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>