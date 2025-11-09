<?php
// Single-file Master Admin CRUD untuk tabel `users_m`
require_once __DIR__ . '/config/config.php'; // memuat $pdo

function json_response($data = [], $status = 200) {
    header('Content-Type: application/json');
    http_response_code($status);
    echo json_encode($data);
    exit;
}

// --- [PERUBAHAN] --- Fungsi helper K-Means disalin ke sini ---
/**
 * Menghitung jarak Euclidean (disalin dari test_and_recommendation.php)
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
 * Versi modifikasi dari K-Means, hanya untuk menghitung ulang jarak
 * [PERUBAHAN] Centroid diganti sesuai data C1 dan C2 dari "Iterasi 9".
 */
function recalculateKMeans($scores) {
    // Urutan kategori HARUS konsisten
    $categories_order = [
        'linguistik', 'logis_matematis', 'spasial', 'kinestetik', 
        'musikal', 'interpersonal', 'intrapersonal', 'naturalis'
    ];
    
    $user_vector = [];
    foreach ($categories_order as $cat) {
        // Ambil skor dari $scores (yang sudah format 'skor_nama')
        // Ini diasumsikan skor mentah (0-10)
        $user_vector[] = $scores['skor_' . $cat] ?? 0;
    }

    // --- [PERUBAHAN] ---
    // Definisikan Centroid (Berdasarkan data C1 dan C2 dari Iterasi 9)
    // Pastikan menggunakan titik (.) sebagai pemisah desimal
    
    // C1 (Klaster Rendah - Iterasi 9)
    $centroid_0_vector = [1.78788, 1.90909, 1.69697, 2.09091, 1.78788, 1.66667, 1.9697, 1.72727]; 
    
    // C2 (Klaster Tinggi - Iterasi 9)
    $centroid_1_vector = [5.35294, 7.41176, 6.0, 6.52941, 6.35294, 5.29412, 6.88235, 6.05882];
    // --- [AKHIR PERUBAHAN] ---

    // Hitung jarak Euclidean
    $dist_0 = euclideanDistance($user_vector, $centroid_0_vector);
    $dist_1 = euclideanDistance($user_vector, $centroid_1_vector);

    // Tentukan klaster terdekat
    $cluster_id = ($dist_0 < $dist_1) ? 0 : 1;

    // Mapping nama klaster (HARUS SAMA DENGAN FILE TES)
    $cluster_mapping = [
        0 => 'Kelompok Kompetensi (Perlu Peningkatan)',
        1 => 'Kelompok Kompetensi (Tinggi)'
    ];

    return [
        'dist_0'       => round($dist_0, 5), // Jarak ke C1 (Iterasi 9)
        'dist_1'       => round($dist_1, 5), // Jarak ke C2 (Iterasi 9)
        'cluster_name' => $cluster_mapping[$cluster_id]
    ];
}
// --- [AKHIR PERUBAHAN] ---


// AJAX endpoints
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    
    if ($_GET['action'] === 'list') { 
        // ... (kode 'list' tidak berubah) ...
        $npm = trim($_GET['npm'] ?? '');
        $nama = trim($_GET['nama'] ?? '');
        $fakultas = trim($_GET['fakultas'] ?? '');
        $prodi = trim($_GET['prodi'] ?? '');
        $cluster = trim($_GET['cluster'] ?? '');

        $sql = "SELECT u.id, u.npm, u.nama_lengkap, u.fakultas, u.prodi,
                        COALESCE(c.nama_cluster, '') AS cluster
                FROM users_m u
                LEFT JOIN test_results tr ON tr.user_id = u.id
                LEFT JOIN clusters c ON tr.cluster_id = c.id
                WHERE 1=1";
        $params = [];
        if ($npm !== '') { $sql .= " AND u.npm LIKE :npm"; $params[':npm'] = "%{$npm}%"; }
        if ($nama !== '') { $sql .= " AND u.nama_lengkap LIKE :nama"; $params[':nama'] = "%{$nama}%"; }
        if ($fakultas !== '') { $sql .= " AND u.fakultas = :fakultas"; $params[':fakultas'] = $fakultas; }
        if ($prodi !== '') { $sql .= " AND u.prodi = :prodi"; $params[':prodi'] = $prodi; }
        $sql .= " ORDER BY u.id";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            json_response(['ok' => true, 'users' => $users]);
        } catch (PDOException $e) {
            json_response(['ok' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    if ($_GET['action'] === 'get_scores') {
        // ... (kode 'get_scores' tidak berubah) ...
        $userId = intval($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            json_response(['ok' => false, 'error' => 'User ID tidak valid'], 400);
        }

        try {
            $stmt = $pdo->prepare("SELECT 
                                    skor_linguistik, skor_logis_matematis, skor_spasial, 
                                    skor_kinestetik, skor_musikal, skor_interpersonal, 
                                    skor_intrapersonal, skor_naturalis 
                                  FROM test_results 
                                  WHERE user_id = :user_id 
                                  ORDER BY tanggal_tes DESC 
                                  LIMIT 1");
            $stmt->execute([':user_id' => $userId]);
            $scores = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$scores) {
                json_response(['ok' => false, 'error' => 'User ini belum mengerjakan tes.'], 404);
            }
            
            json_response(['ok' => true, 'scores' => $scores]);

        } catch (PDOException $e) {
            json_response(['ok' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

    if ($_GET['action'] === 'get_calculation') {
        // ... (kode 'get_calculation' tidak berubah) ...
        $userId = intval($_GET['user_id'] ?? 0);
        if ($userId <= 0) {
            json_response(['ok' => false, 'error' => 'User ID tidak valid'], 400);
        }

        try {
            // 1. Ambil skor (mentah 0-10) dari database
            $stmt = $pdo->prepare("SELECT 
                                    skor_linguistik, skor_logis_matematis, skor_spasial, 
                                    skor_kinestetik, skor_musikal, skor_interpersonal, 
                                    skor_intrapersonal, skor_naturalis 
                                  FROM test_results 
                                  WHERE user_id = :user_id 
                                  ORDER BY tanggal_tes DESC 
                                  LIMIT 1");
            $stmt->execute([':user_id' => $userId]);
            $scores = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$scores) {
                json_response(['ok' => false, 'error' => 'User ini belum mengerjakan tes.'], 404);
            }
            
            // 2. Hitung ulang jarak K-Means menggunakan fungsi yang disalin
            $calculation = recalculateKMeans($scores);
            
            json_response(['ok' => true, 'calculation' => $calculation]);

        } catch (PDOException $e) {
            json_response(['ok' => false, 'error' => 'Database error: ' . $e->getMessage()], 500);
        }
    }

} 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // ... (Semua kode POST: create, update, delete tidak berubah) ...
    $action = $_POST['action'];

    if ($action === 'create') {
        $npm = trim($_POST['npm'] ?? '');
        $nama = trim($_POST['nama_lengkap'] ?? '');
        $fakultas = trim($_POST['fakultas'] ?? '');
        $prodi = trim($_POST['prodi'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($npm === '' || $nama === '') {
            json_response(['ok' => false, 'error' => 'Lengkapi field npm dan nama lengkap'], 400);
        }

        // cek npm unik
        $s = $pdo->prepare("SELECT COUNT(*) FROM users_m WHERE npm = :npm");
        $s->execute([':npm' => $npm]);
        if ($s->fetchColumn() > 0) {
            json_response(['ok' => false, 'error' => 'NPM sudah digunakan'], 409);
        }

        $hash = $password ? password_hash($password, PASSWORD_DEFAULT) : password_hash(bin2hex(random_bytes(5)), PASSWORD_DEFAULT);

        $ins = $pdo->prepare("INSERT INTO users_m (npm, nama_lengkap, fakultas, prodi, password) VALUES (:npm, :nama, :fak, :prodi, :pwd)");
        $ins->execute([
            ':npm' => $npm,
            ':nama' => $nama,
            ':fak' => $fakultas,
            ':prodi' => $prodi,
            ':pwd' => $hash
        ]);

        json_response(['ok' => true, 'message' => 'User berhasil dibuat']);
    }

    if ($action === 'update') {
        $id = intval($_POST['id'] ?? 0);
        $npm = trim($_POST['npm'] ?? '');
        $nama = trim($_POST['nama_lengkap'] ?? '');
        $fakultas = trim($_POST['fakultas'] ?? '');
        $prodi = trim($_POST['prodi'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($id <= 0 || $npm === '' || $nama === '') {
            json_response(['ok' => false, 'error' => 'Data tidak lengkap'], 400);
        }

        // cek npm conflict (kecuali sendiri)
        $s = $pdo->prepare("SELECT id FROM users_m WHERE npm = :npm AND id <> :id");
        $s->execute([':npm' => $npm, ':id' => $id]);
        if ($s->fetch()) {
            json_response(['ok' => false, 'error' => 'NPM sudah dipakai oleh user lain'], 409);
        }

        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $upd = $pdo->prepare("UPDATE users_m SET npm = :npm, nama_lengkap = :nama, fakultas = :fak, prodi = :prodi, password = :pwd WHERE id = :id");
            $upd->execute([
                ':npm' => $npm, ':nama' => $nama, ':fak' => $fakultas, ':prodi' => $prodi, ':pwd' => $hash, ':id' => $id
            ]);
        } else {
            $upd = $pdo->prepare("UPDATE users_m SET npm = :npm, nama_lengkap = :nama, fakultas = :fak, prodi = :prodi WHERE id = :id");
            $upd->execute([
                ':npm' => $npm, ':nama' => $nama, ':fak' => $fakultas, ':prodi' => $prodi, ':id' => $id
            ]);
        }

        json_response(['ok' => true, 'message' => 'User berhasil diperbarui']);
    }

    if ($action === 'delete') {
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) json_response(['ok' => false, 'error' => 'ID tidak valid'], 400);
        $del = $pdo->prepare("DELETE FROM users_m WHERE id = :id");
        $del->execute([':id' => $id]);
        json_response(['ok' => true, 'message' => 'User dihapus']);
    }
}

// Render halaman admin
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Master Admin - Users</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <style>
        body{font-family:Inter,system-ui,Segoe UI,Roboto,Helvetica,Arial;background:#f3f4f6;padding:28px}
        .card{background:white;border-radius:10px;box-shadow:0 6px 18px rgba(16,24,40,.3);max-width:;overflow:hidden}
        .header{background:#1e40af;color:white;padding:16px 20px;display:flex;align-items:center;justify-content:space-between}
        .p{padding:18px}
        table{width:100%;border-collapse:collapse;background:blue-9--;}
        th,td{padding:10px;border-bottom:1px solid #e6e7ea;text-align:left}
        .btn{display:inline-flex;align-items:center;gap:.5rem;padding:.5rem .75rem;border-radius:8px;background:#1e40af;color:white;border:0;cursor:pointer}
        .btn.ghost{background:#efefef;color:#111}
        .form-row{display:flex;gap:.5rem;flex-wrap:wrap}
        .form-row input, .form-row select {padding:.5rem .6rem;border:1px solid #d1d5db;border-radius:8px;min-width:160px}
        .small{font-size:.85rem;color:#6b7280}
        .center{display:flex;align-items:center;gap:.5rem}
        .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.4);z-index:50}
        .modal .box{background:white;padding:18px;border-radius:10px;min-width:360px;max-width:90%}
    </style>
</head>
<body>
    <div class="card" style="max-width:980px;margin:0 auto">
        <div class="header">
            <h2 style="margin:0">Master Admin — Users</h2>
            <div>
                <button id="btn-new" class="btn">+ Tambah</button>
            </div>
        </div>

        <div class="p">
            <div class="small">Daftar users (tabel <code>users_m</code>)</div>

            <div style="margin-top:12px;display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
                <input id="filter-npm" placeholder="Cari NPM" style="padding:.45rem .6rem;border:1px solid #d1d5db;border-radius:8px">
                <input id="filter-nama" placeholder="Cari Nama" style="padding:.45rem .6rem;border:1px solid #d1d5db;border-radius:8px">
                <select id="filter-fakultas" style="padding:.45rem .6rem;border:1px solid #d1d5db;border-radius:8px">
                    <option value="">-- Semua Fakultas --</option>
                </select>
                <select id="filter-prodi" style="padding:.45rem .6rem;border:1px solid #d1d5db;border-radius:8px">
                    <option value="">-- Semua Prodi --</option>
                </select>
                <button id="btn-filter" class="btn ghost" type="button">Filter</button>
                <button id="btn-reset-filter" class="btn" type="button" style="background:#10b981">Reset</button>
            </div>

            <div style="margin-top:12px;overflow:auto">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>NPM</th>
                            <th>Nama Lengkap</th>
                            <th>Fakultas</th>
                            <th>Prodi</th>
                            <th>Kelompok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="modal" class="modal" aria-hidden="true">
        <div class="box">
            <h3 id="modal-title">Tambah User</h3>
            <form id="user-form" class="p" onsubmit="return false;">
                <input type="hidden" name="id" id="user-id" value="">
                <div class="form-row">
                    <input type="text" name="npm" id="f-npm" placeholder="NPM" required>
                    <input type="text" name="nama_lengkap" id="f-nama" placeholder="Nama lengkap" required>
                </div>
                <div class="form-row" style="margin-top:8px">
                    <select name="fakultas" id="f-fakultas" required>
                        </select>
                    <select name="prodi" id="f-prodi" required>
                        </select>
                </div>
                <div style="margin-top:8px" class="small">Kosongkan password jika tidak ingin mengubahnya saat edit.</div>
                <div class="form-row" style="margin-top:8px">
                    <input type="password" name="password" id="f-password" placeholder="Password (opsional)">
                </div>

                <div style="margin-top:12px;display:flex;gap:.5rem;justify-content:flex-end">
                    <button id="btn-cancel" class="btn ghost" type="button">Batal</button>
                    <button id="btn-save" class="btn" type="button">Simpan</button>
                </div>
            </form>
        </div>
    </div>

<script>
async function fetchList() {
    try {
        const q = new URLSearchParams();
        const npm = document.getElementById('filter-npm').value.trim();
        const nama = document.getElementById('filter-nama').value.trim();
        const fakultas = document.getElementById('filter-fakultas').value;
        const prodi = document.getElementById('filter-prodi').value;
        if (npm) q.set('npm', npm);
        if (nama) q.set('nama', nama);
        if (fakultas) q.set('fakultas', fakultas);
        if (prodi) q.set('prodi', prodi);
        q.set('action', 'list');

        const res = await fetch('?' + q.toString());
        const j = await res.json();
        if (!j.ok) throw new Error(j.error || 'Gagal memuat data');
        const tbody = document.getElementById('users-tbody');
        tbody.innerHTML = '';
        j.users.forEach((u, idx) => {
            const tr = document.createElement('tr');
            
            // Tombol 'Lihat Calc' (oranye) dan 'Lihat Skor' (hijau)
            tr.innerHTML = `
                <td>${idx+1}</td>
                <td>${escapeHtml(u.npm)}</td>
                <td>${escapeHtml(u.nama_lengkap)}</td>
                <td>${escapeHtml(u.fakultas || '')}</td>
                <td>${escapeHtml(u.prodi || '')}</td>
                <td>${escapeHtml(u.cluster || 'Belum Melakukan Tes')}</td>
                <td class="center">
                    ${u.cluster ? `<button class="btn" style="background:#0d9488" onclick="showScores(${u.id})">Lihat Skor</button>` : ''}
                    ${u.cluster ? `<button class="btn" style="background:#f97316" onclick="showCalculation(${u.id})">Lihat Calc</button>` : ''}
                    <button class="btn ghost" onclick="openEdit(${u.id})">Edit</button>
                    <button class="btn" style="background:#ef4444" onclick="doDelete(${u.id})">Hapus</button>
                </td>
            `;
            
            tbody.appendChild(tr);
        });
    } catch (err) {
        alert('Gagal memuat daftar user: ' + err.message);
    }
}

function escapeHtml(s){ return (s+'').replace(/[&<>"']/g, c=>({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' })[c]); }

const modal = document.getElementById('modal');
// ... (kode JS modal, form, event listener tidak berubah) ...
const form = document.getElementById('user-form');
const btnNew = document.getElementById('btn-new');
const btnCancel = document.getElementById('btn-cancel');
const btnSave = document.getElementById('btn-save');

btnNew.addEventListener('click', ()=> openNew());
btnCancel.addEventListener('click', ()=> closeModal());
btnSave.addEventListener('click', ()=> submitForm());

// Tambahkan event listener untuk tombol filter & reset
document.getElementById('btn-filter').addEventListener('click', ()=> fetchList());
document.getElementById('btn-reset-filter').addEventListener('click', ()=> {
    document.getElementById('filter-npm').value = '';
    document.getElementById('filter-nama').value = '';
    document.getElementById('filter-fakultas').value = '';
    // kosongkan pilihan prodi juga
    const fp = document.getElementById('filter-prodi');
    fp.innerHTML = '<option value="">-- Semua Prodi --</option>';
    fetchList();
});

// support tekan Enter di input filter untuk submit
['filter-npm','filter-nama'].forEach(id => {
    const el = document.getElementById(id);
    if (el) {
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                fetchList();
            }
        });
    }
});

function openNew(){
    document.getElementById('modal-title').textContent = 'Tambah User';
    form.reset();
    document.getElementById('user-id').value = '';
    populateFakultas();
    populateProdi(''); // kosongkan prodi
    openModal();
}

async function openEdit(id){
    try {
        const res = await fetch('?action=list');
        const j = await res.json();
        const u = (j.users || []).find(x => Number(x.id) === Number(id));
        if (!u) throw new Error('User tidak ditemukan');
        document.getElementById('modal-title').textContent = 'Edit User';
        document.getElementById('user-id').value = u.id;
        document.getElementById('f-npm').value = u.npm;
        document.getElementById('f-nama').value = u.nama_lengkap;
        populateFakultas();
        if (u.fakultas) {
            document.getElementById('f-fakultas').value = u.fakultas;
            populateProdi(u.fakultas, u.prodi || '');
        } else {
            populateProdi('');
        }
        document.getElementById('f-password').value = '';
        openModal();
    } catch (err) {
        alert('Gagal membuka user: ' + err.message);
    }
}

function openModal(){ modal.style.display = 'flex'; modal.setAttribute('aria-hidden','false'); }
function closeModal(){ modal.style.display = 'none'; modal.setAttribute('aria-hidden','true'); }

async function submitForm(){
    const id = document.getElementById('user-id').value;
    const fd = new FormData(form);
    fd.append('action', id ? 'update' : 'create');
    if (id) fd.append('id', id);

    try {
        const res = await fetch('', { method:'POST', body:fd });
        const j = await res.json();
        if (!j.ok) throw new Error(j.error || 'Gagal');
        alert(j.message || 'Sukses');
        closeModal();
        fetchList();
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

async function doDelete(id){
    if (!confirm('Hapus user ini?')) return;
    const fd = new FormData();
    fd.append('action','delete');
    fd.append('id', id);
    try {
        const res = await fetch('', { method:'POST', body:fd });
        const j = await res.json();
        if (!j.ok) throw new Error(j.error || 'Gagal menghapus');
        alert(j.message || 'Terhapus');
        fetchList();
    } catch (err) {
        alert('Error: ' + err.message);
    }
}

async function showScores(userId) {
    // ... (fungsi 'showScores' tidak berubah) ...
    try {
        const res = await fetch(`?action=get_scores&user_id=${userId}`);
        const j = await res.json();
        
        if (!j.ok) {
            throw new Error(j.error || 'Gagal memuat skor');
        }
        
        const scores = j.scores;
        
        // Format skor untuk ditampilkan di alert
        let scoreText = "Hasil Skor Mentah (Skala 0-10):\n\n"; 
        scoreText += `Linguistik: ${scores.skor_linguistik}\n`;
        scoreText += `Logis-Matematis: ${scores.skor_logis_matematis}\n`;
        scoreText += `Spasial: ${scores.skor_spasial}\n`;
        scoreText += `Kinestetik: ${scores.skor_kinestetik}\n`;
        scoreText += `Musikal: ${scores.skor_musikal}\n`;
        scoreText += `Interpersonal: ${scores.skor_interpersonal}\n`;
        scoreText += `Intrapersonal: ${scores.skor_intrapersonal}\n`;
        scoreText += `Naturalis: ${scores.skor_naturalis}\n`;
        
        alert(scoreText);
        
    } catch (err) {
        alert('Error: ' + err.message);
    }
}



// --- [PERUBAHAN] --- Memperbarui teks 'Lihat Calc'
async function showCalculation(userId) {
    try {
        const res = await fetch(`?action=get_calculation&user_id=${userId}`);
        const j = await res.json();
        
        if (!j.ok) {
            throw new Error(j.error || 'Gagal memuat kalkulasi');
        }
        
        const calc = j.calculation;
        
        // Format teks untuk ditampilkan di alert
        let calcText = "Perhitungan Manual (Iterasi 9)\n\n";
        calcText += `Jarak ke Centroid C1: ${calc.dist_0}\n`; // Diubah
        calcText += `Jarak ke Centroid C2: ${calc.dist_1}\n\n`; // Diubah
        
        if (calc.dist_0 < calc.dist_1) {
            calcText += `Hasil: Jarak ke C1 lebih dekat.\n`;
        } else {
            calcText += `Hasil: Jarak ke C2 lebih dekat.\n`;
        }
        calcText += `Klaster: ${calc.cluster_name}\n`;

        alert(calcText);
        
    } catch (err) {
        alert('Error: ' + err.message);
    }
}
// --- [AKHIR PERUBAHAN] ---


// ... (Sisa kode JS: fakultasList, prodiMap, populateFakultas, dll tidak berubah) ...
const fakultasList = [
    "Fakultas Teknik",
    "Fakultas Ekonomi",
    "Fakultas Keguruan dan Ilmu Pendidikan",
    "Fakultas Sains"
];

const prodiMap = {
    "Fakultas Teknik": ["Teknik Informatika", "Sistem Informasi", "Teknik Komputer dan Jaringan"],
    "Fakultas Ekonomi": ["Manajemen", "Akuntansi"],
    "Fakultas Keguruan dan Ilmu Pendidikan": ["Pendidikan Sastra Bahasa Indoneisa", "Pendidikan Matematika",
                                            "Pendidikan Guru Sekolah Dasar", "Pendidikan Bahasa Inggris", "Pendidikan Ppkn", "Pendidikan jasmani dan Kesehatan Rekreasi",
                                            "Pendidikan Bahasa Inggris"],
    "Fakultas Sains": ["Fisika"]
};

function populateFakultas() {
    const sel = document.getElementById('f-fakultas');
    sel.innerHTML = '<option value="">-- Pilih Fakultas --</option>';
    fakultasList.forEach(f => {
        const opt = document.createElement('option');
        opt.value = f;
        opt.textContent = f;
        sel.appendChild(opt);
    });

    // juga isi filter dropdown fakultas
    const ff = document.getElementById('filter-fakultas');
    ff.innerHTML = '<option value="">-- Semua Fakultas --</option>';
    fakultasList.forEach(f => {
        const opt = document.createElement('option');
        opt.value = f;
        opt.textContent = f;
        ff.appendChild(opt);
    });
}

function populateProdi(fakultas, selectValue = '') {
    const sel = document.getElementById('f-prodi');
    sel.innerHTML = '<option value="">-- Pilih Prodi --</option>';
    if (!fakultas || !prodiMap[fakultas]) return;
    prodiMap[fakultas].forEach(p => {
        const opt = document.createElement('option');
        opt.value = p;
        opt.textContent = p;
        if (p === selectValue) opt.selected = true;
        sel.appendChild(opt);
    });

    // update filter prodi
    const fp = document.getElementById('filter-prodi');
    fp.innerHTML = '<option value="">-- Semua Prodi --</option>';
    if (prodiMap[fakultas]) {
        prodiMap[fakultas].forEach(p => {
            const o = document.createElement('option');
            o.value = p;
            o.textContent = p;
            fp.appendChild(o);
        });
    }
}

// tambahkan ini supaya prodi di modal terisi ketika pilih fakultas
document.getElementById('f-fakultas').addEventListener('change', (e) => {
    populateProdi(e.target.value);
});

// update prodi ketika filter fakultas berubah
document.getElementById('filter-fakultas').addEventListener('change', (e)=>{
    const val = e.target.value;
    const fp = document.getElementById('filter-prodi');
    fp.innerHTML = '<option value="">-- Semua Prodi --</option>';
    if (val && prodiMap[val]) prodiMap[val].forEach(p=>{ const o=document.createElement('option'); o.value=p; o.textContent=p; fp.appendChild(o); });
});

// panggil saat buka modal New/Edit
function openNew(){
    document.getElementById('modal-title').textContent = 'Tambah User';
    form.reset();
    document.getElementById('user-id').value = '';
    populateFakultas();
    populateProdi(); // kosongkan prodi
    openModal();
}

async function openEdit(id){
    try {
        const res = await fetch('?action=list');
        const j = await res.json();
        const u = (j.users || []).find(x => Number(x.id) === Number(id));
        if (!u) throw new Error('User tidak ditemukan');
        document.getElementById('modal-title').textContent = 'Edit User';
        document.getElementById('user-id').value = u.id;
        document.getElementById('f-npm').value = u.npm;
        document.getElementById('f-nama').value = u.nama_lengkap;
        populateFakultas();
        if (u.fakultas) {
            document.getElementById('f-fakultas').value = u.fakultas;
            populateProdi(u.fakultas, u.prodi || '');
        } else {
            populateProdi('');
        }
        document.getElementById('f-password').value = '';
        openModal();
    } catch (err) {
        alert('Gagal membuka user: ' + err.message);
    }
}

// inisialisasi halaman: isi dropdown fakultas saat load
document.addEventListener('DOMContentLoaded', () => {
    populateFakultas();
    populateProdi('');
    fetchList();
});
</script>
</body>
</html>