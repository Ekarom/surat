<?php
/**
 * Monitoring Riwayat Kepegawaian (Admin View - Redesigned)
 * Split layout consistent with new profile aesthetic
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$pegawai_id = $_GET['id'] ?? 0;

// Fetch Employee Data
$stmt = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$stmt->bind_param("i", $pegawai_id);
$stmt->execute();
$pegawai = $stmt->get_result()->fetch_assoc();

if (!$pegawai) {
    echo '<div class="alert alert-danger rounded-4 shadow-sm"><i class="fas fa-exclamation-triangle me-2"></i>Data pegawai tidak ditemukan.</div>';
    echo '<a href="?data_pegawai" class="btn btn-primary btn-rounded px-4"><i class="fas fa-arrow-left me-2"></i>Kembali ke Data Pegawai</a>';
    return;
}

$poto_db = $pegawai['foto'] ?? '';
$folder_foto = "../file/datakepegawaian/";
$path_file_server = $folder_foto . $poto_db;
$foto_path = (!empty($poto_db) && file_exists($path_file_server)) ? $path_file_server : "../images/default.png";

// Fetch History Data
$q_riwayat = $conn->query("SELECT * FROM riwayat_kepegawaian WHERE pegawai_id = '$pegawai_id' ORDER BY tmt DESC");
?>

<style>
    .card-modern {
        border: none;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background: #fff;
        margin-bottom: 20px;
    }

    .card-modern .card-header {
        padding: 12px 20px;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
        border: none;
    }

    .header-blue {
        background: #3b82f6;
    }

    .header-red {
        background: #ef4444;
    }

    .header-slate {
        background: #1e293b;
    }

    .info-row {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        align-items: center;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        width: 180px;
        font-weight: 700;
        color: #475569;
        font-size: 0.85rem;
    }

    .info-box {
        flex: 1;
        color: #1e293b;
        background: #f8fafc;
        padding: 6px 12px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        font-size: 0.85rem;
        min-height: 35px;
        display: flex;
        align-items: center;
    }

    .photo-monitor {
        width: 100%;
        max-width: 200px;
        aspect-ratio: 3/4;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .riwayat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
    }

    .card-item-riwayat {
        background: #fff;
        border-radius: 12px;
        padding: 1.25rem;
        border: 1px solid #e2e8f0;
        position: relative;
        transition: transform 0.2s;
    }

    .card-item-riwayat:hover {
        transform: translateY(-3px);
        border-color: #3b82f6;
    }

    .badge-status {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.3rem 0.6rem;
        border-radius: 50px;
    }

    @media (max-width: 768px) {
        .info-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .info-label {
            width: 100%;
            margin-bottom: 5px;
        }
    }
</style>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">Monitoring Kepegawaian</h2>
            <p class="text-muted small mb-0">Pemantauan kelengkapan dokumen dan riwayat pegawai.</p>
        </div>
        <a href="?data_pegawai" class="btn btn-light btn-rounded border px-4 shadow-sm fw-bold">
            <i class="fas fa-arrow-left me-2 text-primary"></i> Kembali
        </a>
    </div>
</div>

<div class="row">
    <!-- Left: Identity Info -->
    <div class="col-lg-8">
        <div class="card-modern">
            <div class="card-header header-blue">
                <i class="fas fa-id-card"></i> Informasi Identitas Pegawai
            </div>
            <div class="card-body p-4">
                <div class="info-row">
                    <div class="info-label">Nama Lengkap</div>
                    <div class="info-box fw-bold"><?php echo htmlspecialchars($pegawai['nm_pegawai']); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">NIP</div>
                    <div class="info-box"><?php echo htmlspecialchars($pegawai['nip'] ?: '-'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">NRK</div>
                    <div class="info-box"><?php echo htmlspecialchars($pegawai['nrk'] ?: '-'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Jabatan</div>
                    <div class="info-box"><?php echo htmlspecialchars($pegawai['jabatan'] ?: '-'); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-label">Unit Kerja</div>
                    <div class="info-box">
                        <?php echo htmlspecialchars($pegawai['unit_kerja'] ?: 'SMP Negeri 171 Jakarta'); ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right: Photo -->
    <div class="col-lg-4">
        <div class="card-modern">
            <div class="card-header header-red">
                <i class="fas fa-camera"></i> Foto Pegawai
            </div>
            <div class="card-body text-center py-4">
                <img src="<?php echo $foto_path; ?>" class="photo-monitor" alt="Foto">
                <div class="mt-3">
                    <span class="badge bg-soft-success text-success border border-success rounded-pill px-3 py-2 small">
                        <i class="fas fa-check-circle me-1"></i> Data Aktif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-2 mb-4">
    <h5 class="fw-bold d-flex align-items-center">
        <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
            style="width: 32px; height: 32px;">
            <i class="fas fa-history small"></i>
        </span>
        Riwayat & Lampiran Dokumen
    </h5>
</div>

<?php if ($q_riwayat && $q_riwayat->num_rows > 0): ?>
    <div class="riwayat-grid">
        <?php
        while ($r = $q_riwayat->fetch_assoc()):
            $has_file = !empty($r['file_lampiran']);
            $icon = 'fa-file-alt';
            if ($r['kategori'] == 'Pangkat')
                $icon = 'fa-award';
            else if ($r['kategori'] == 'Jabatan')
                $icon = 'fa-user-tie';
            else if ($r['kategori'] == 'Pendidikan')
                $icon = 'fa-graduation-cap';
            ?>
            <div class="card-item-riwayat">
                <div class="d-flex justify-content-between mb-3">
                    <div class="bg-light text-primary rounded-3 d-flex align-items-center justify-content-center"
                        style="width: 40px; height: 40px;">
                        <i class="fas <?php echo $icon; ?> fs-5"></i>
                    </div>
                    <div>
                        <span class="badge-status <?php echo $has_file ? 'bg-success text-white' : 'bg-warning text-dark'; ?>">
                            <?php echo $has_file ? 'DOKUMEN ADA' : 'TIDAK ADA FILE'; ?>
                        </span>
                    </div>
                </div>

                <h6 class="fw-bold text-dark mb-1">
                    <?php
                    if ($r['kategori'] == 'Pendidikan' && !empty($r['institusi'])) {
                        echo htmlspecialchars($r['institusi']);
                    } else if ($r['kategori'] == 'KGB' && !empty($r['gaji_pokok'])) {
                        echo 'KGB - Rp ' . number_format($r['gaji_pokok'], 0, ',', '.');
                    } else {
                        echo htmlspecialchars($r['deskripsi']);
                    }
                    ?>
                </h6>
                <p class="text-muted extra-small mb-3">
                    <?php echo htmlspecialchars($r['kategori']); ?>
                    <?php if ($r['kategori'] == 'Pendidikan' && !empty($r['jurusan']))
                        echo '• ' . htmlspecialchars($r['jurusan']); ?>
                    <?php if (($r['kategori'] == 'Diklat' || $r['kategori'] == 'Seminar') && !empty($r['tempat']))
                        echo '• @ ' . htmlspecialchars($r['tempat']); ?>
                    <?php if ($r['kategori'] == 'KGB')
                        echo '• Masa Kerja: ' . ($r['masa_kerja_thn'] ?: 0) . 'th ' . ($r['masa_kerja_bln'] ?: 0) . 'bln'; ?>
                    • TMT: <?php echo (!empty($r['tmt']) && $r['tmt'] != '0000-00-00') ? date('d/m/Y', strtotime($r['tmt'])) : '-'; ?>
                </p>

                <div class="bg-light p-2 rounded mb-3 small">
                    <div class="text-muted extra-small">
                        <?php echo ($r['kategori'] == 'Pendidikan') ? 'No. Ijazah:' : 'No. SK:'; ?>
                    </div>
                    <div class="fw-bold">
                        <?php
                        if ($r['kategori'] == 'Pendidikan' && !empty($r['no_ijazah'])) {
                            echo htmlspecialchars($r['no_ijazah']);
                        } else {
                            echo htmlspecialchars($r['no_sk'] ?: '-');
                        }
                        ?>
                    </div>
                </div>

                <div class="d-grid mt-2">
                    <?php if ($has_file): ?>
                        <a href="../file/datakepegawaian/<?php echo $r['file_lampiran']; ?>" target="_blank"
                            class="btn btn-sm btn-outline-primary fw-bold">
                            <i class="fas fa-external-link-alt me-1"></i> Lihat Dokumen
                        </a>
                    <?php else: ?>
                        <button class="btn btn-sm btn-light border text-muted disabled fw-bold">Tidak Ada File</button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
<?php else: ?>
    <div class="card-modern text-center py-5 bg-white">
        <i class="fas fa-folder-open fa-3x mb-3 text-muted opacity-25"></i>
        <h5 class="text-muted fw-bold">Belum ada data riwayat.</h5>
        <p class="text-muted small">Pegawai belum menginputkan riwayat kepegawaian.</p>
    </div>
<?php endif; ?>

<div class="mt-5 pb-5">
    <div class="alert alert-light border shadow-sm rounded-3">
        <i class="fas fa-info-circle text-primary me-2"></i>
        <strong>Catatan:</strong> Data ini disinkronkan secara real-time dengan portal mandiri guru.
    </div>
</div>