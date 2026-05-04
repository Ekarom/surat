<?php
/**
 * Monitoring Berkas PTK - SMP Negeri 171 Jakarta
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$id_pegawai = $_SESSION['id'] ?? 0;

// Essential Categories to Monitor
$requirements = [
    'Pangkat' => 'SK Pangkat / Golongan Terakhir',
    'Jabatan' => 'SK Jabatan Terakhir',
    'Pendidikan' => 'Ijazah Pendidikan Terakhir',
    'Administrasi' => 'SK Kepengurusan Administrasi',
    'KGB' => 'SK Kenaikan Gaji Berkala (KGB)'
];

// Check existing records
$existing = [];
$q = $conn->query("SELECT kategori, file_lampiran FROM riwayat_kepegawaian WHERE pegawai_id = '$id_pegawai'");
while ($row = $q->fetch_assoc()) {
    if (!empty($row['file_lampiran'])) {
        $existing[$row['kategori']] = true;
    }
}

// Calculate Progress
$total_req = count($requirements);
$count_done = 0;
foreach ($requirements as $key => $label) {
    if (isset($existing[$key])) $count_done++;
}
$percent = ($count_done / $total_req) * 100;
?>

<style>
    .progress-modern {
        height: 12px;
        border-radius: 50px;
        background-color: #e2e8f0;
        overflow: hidden;
    }
    .progress-bar-modern {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        transition: width 1s ease-in-out;
    }
    .check-item {
        background: #fff;
        border-radius: 1rem;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
        margin-bottom: 1rem;
        transition: all 0.3s;
    }
    .check-item:hover {
        transform: translateX(5px);
        border-color: #4f46e5;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .check-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .icon-success { background-color: #dcfce7; color: #16a34a; }
    .icon-pending { background-color: #fef9c3; color: #ca8a04; }
</style>

<div class="row">
    <div class="col-12 mb-4">
        <h2 class="fw-800 text-dark mb-1">Sejauh Mana Berkas Anda?</h2>
        <p class="text-muted small">Pantau kelengkapan dokumen digital Anda di sistem.</p>
    </div>

    <!-- Progress Card -->
    <div class="col-lg-12 mb-4">
        <div class="modern-card p-4">
            <div class="d-flex justify-content-between align-items-end mb-3">
                <div>
                    <h5 class="fw-bold mb-1">Persentase Kelengkapan</h5>
                    <p class="text-muted small mb-0">Total <?php echo $count_done; ?> dari <?php echo $total_req; ?> dokumen wajib tersedia.</p>
                </div>
                <div class="text-end">
                    <h3 class="fw-800 text-primary mb-0"><?php echo round($percent); ?>%</h3>
                </div>
            </div>
            <div class="progress progress-modern">
                <div class="progress-bar progress-bar-modern" style="width: <?php echo $percent; ?>%"></div>
            </div>
        </div>
    </div>

    <!-- Checklist -->
    <div class="col-lg-8">
        <div class="mb-3 mt-2">
            <h6 class="fw-bold text-secondary text-uppercase small" style="letter-spacing: 1px;">Checklist Dokumen Wajib</h6>
        </div>
        
        <?php foreach ($requirements as $key => $label): 
            $is_done = isset($existing[$key]);
        ?>
        <div class="check-item d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="check-icon <?php echo $is_done ? 'icon-success' : 'icon-pending'; ?> me-3">
                    <i class="fas <?php echo $is_done ? 'fa-check-circle' : 'fa-clock'; ?>"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark"><?php echo $label; ?></h6>
                    <small class="text-muted"><?php echo $is_done ? 'Dokumen tersedia' : 'Dokumen belum diunggah'; ?></small>
                </div>
            </div>
            <div>
                <?php if ($is_done): ?>
                    <a href="?riwayat" class="btn btn-sm btn-light border px-3 rounded-pill small fw-bold">Lihat</a>
                <?php else: ?>
                    <a href="?riwayat" class="btn btn-sm btn-primary px-3 rounded-pill small fw-bold shadow-sm">Unggah SK</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Side Tips -->
    <div class="col-lg-4">
        <div class="modern-card p-4 bg-light border-0 shadow-none">
            <h6 class="fw-bold mb-3"><i class="fas fa-lightbulb text-warning me-2"></i>Tips Kelengkapan</h6>
            <ul class="small text-muted ps-3 mb-0">
                <li class="mb-2">Pastikan dokumen yang diunggah dalam format PDF atau JPG berkualitas tinggi.</li>
                <li class="mb-2">Gunakan fitur "Unggah SK" untuk menambahkan dokumen yang masih kosong.</li>
                <li class="mb-2">Dokumen yang lengkap akan mempermudah verifikasi kenaikan pangkat dan berkala.</li>
                <li>Hubungi Operator TU jika ada ketidaksesuaian data utama.</li>
            </ul>
        </div>
    </div>
</div>
