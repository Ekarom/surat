<?php
/**
 * Dashboard Guru - Portal Mandiri
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$id_pegawai = $_SESSION['id'] ?? 0;
$query = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$query->bind_param("i", $id_pegawai);
$query->execute();
$pegawai = $query->get_result()->fetch_assoc();

if (!$pegawai) {
    echo "<div class='content-wrapper bg-light-soft'><section class='content pt-5'><div class='container-fluid'><div class='alert alert-danger shadow-sm rounded-4'>Data profil tidak ditemukan. Silakan hubungi Administrator.</div></div></section></div>";
    return;
}

// Stats for the teacher
$riwayat_count = 0;
$q_riwayat = $conn->query("SELECT COUNT(*) as total FROM riwayat_kepegawaian WHERE pegawai_id = '$id_pegawai'");
if ($q_riwayat) {
    $riwayat_count = $q_riwayat->fetch_assoc()['total'];
}

$poto_db = $pegawai['foto'] ?? '';
$folder_foto = "../file/datakepegawaian/";
$path_file_server = $folder_foto . $poto_db;
$src_foto = (!empty($poto_db) && file_exists($path_file_server)) ? $path_file_server : "../images/default.png";

// Document Monitoring Progress
$requirements = ['Pangkat', 'Jabatan', 'Pendidikan', 'Administrasi', 'KGB'];
$q_check = $conn->prepare("SELECT kategori FROM riwayat_kepegawaian WHERE pegawai_id = ? AND file_lampiran IS NOT NULL AND file_lampiran != ''");
$q_check->bind_param("i", $id_pegawai);
$q_check->execute();
$res_check = $q_check->get_result();
$done_cats = [];
while($r = $res_check->fetch_assoc()) $done_cats[$r['kategori']] = true;
$count_done = count(array_intersect(array_keys($done_cats), $requirements));
$percent = (count($requirements) > 0) ? ($count_done / count($requirements)) * 100 : 0;
?>

<div class="content-wrapper bg-light-soft">
    <section class="content-header pt-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1">Dashboard PTK</h2>
                    <p class="text-muted small mb-0">Portal Mandiri Manajemen Kepegawaian SMP Negeri 171</p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="index_guru.php"
                                class="text-decoration-none text-primary">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </nav>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Welcome Header (Premium Light Gradient) -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="welcome-banner-light p-4 rounded-4 shadow-sm border bg-white">
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center mb-3 mb-md-0">
                                <div class="avatar-wrapper">
                                    <img src="<?php echo $src_foto; ?>"
                                        class="rounded-circle border border-4 border-white shadow-sm"
                                        style="width: 100px; height: 100px; object-fit: cover;">
                                    <span class="status-indicator bg-success"></span>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <h3 class="fw-bold mb-1 text-dark">Selamat Datang,
                                    <?php echo htmlspecialchars($pegawai['nm_pegawai']); ?>!
                                </h3>
                                <p class="mb-0 text-muted">Akses mandiri data kepegawaian Anda secara terpusat dan
                                    transparan.</p>
                                <div class="mt-2">
                                    <span
                                        class="badge bg-primary-soft text-primary rounded-pill px-3"><?php echo htmlspecialchars($pegawai['nrk'] ?: $pegawai['nip']); ?></span>
                                    <span class="badge bg-success-soft text-success rounded-pill px-3 ms-1">Aktif</span>
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end mt-3 mt-md-0 d-flex flex-column gap-2">
                                <a href="?monitoring" class="btn btn-indigo rounded-pill px-4 shadow-sm">
                                    <i class="fas fa-tasks me-2"></i> Monitor Berkas (<?php echo round($percent); ?>%)
                                </a>
                                <div class="d-flex gap-2">
                                    <a href="?profil" class="btn btn-light border rounded-pill px-3 shadow-sm flex-grow-1 small">
                                        <i class="fas fa-user-circle me-1 text-indigo"></i> Profil
                                    </a>
                                    <a href="?riwayat" class="btn btn-light border rounded-pill px-3 shadow-sm flex-grow-1 small">
                                        <i class="fas fa-history me-1 text-indigo"></i> Riwayat
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 stat-card-light">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box bg-blue-soft text-blue me-3"><i class="fas fa-id-badge"></i></div>
                                <span class="text-muted small text-uppercase fw-bold letter-spacing-1">Status</span>
                            </div>
                            <h4 class="fw-bold text-dark">
                                <?php echo htmlspecialchars($pegawai['status_pegawai'] ?: '-'); ?>
                            </h4>
                            <p class="text-muted small mb-0">Status Kepegawaian</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 stat-card-light">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box bg-purple-soft text-purple me-3"><i class="fas fa-briefcase"></i>
                                </div>
                                <span class="text-muted small text-uppercase fw-bold letter-spacing-1">Jabatan</span>
                            </div>
                            <h4 class="fw-bold text-dark text-truncate"
                                title="<?php echo htmlspecialchars($pegawai['jabatan']); ?>">
                                <?php echo htmlspecialchars($pegawai['jabatan'] ?: '-'); ?>
                            </h4>
                            <p class="text-muted small mb-0">Posisi Saat Ini</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 stat-card-light">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon-box bg-cyan-soft text-cyan me-3"><i class="fas fa-layer-group"></i>
                                </div>
                                <span class="text-muted small text-uppercase fw-bold letter-spacing-1">Pangkat</span>
                            </div>
                            <h4 class="fw-bold text-dark"><?php echo htmlspecialchars($pegawai['golongan'] ?: '-'); ?>
                            </h4>
                            <p class="text-muted small mb-0">
                                <?php echo htmlspecialchars($pegawai['pangkat'] ?: 'N/A'); ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <a href="?monitoring" class="text-decoration-none">
                        <div class="card border-0 shadow-sm h-100 stat-card-light">
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="icon-box bg-orange-soft text-orange me-3"><i class="fas fa-tasks"></i>
                                    </div>
                                    <span
                                        class="text-muted small text-uppercase fw-bold letter-spacing-1">Berkas Digital</span>
                                </div>
                                <h4 class="fw-bold text-dark"><?php echo round($percent); ?>% <span
                                        class="fs-6 fw-normal text-muted">Lengkap</span></h4>
                                <p class="text-muted small mb-0"><?php echo $count_done; ?> dari 5 dokumen utama</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Profile Summary & Announcements -->
            <div class="row g-4 mb-5">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-info-circle me-2 text-indigo"></i> Informasi
                                Kepegawaian</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="info-group mb-4">
                                        <label class="text-muted small text-uppercase mb-1 fw-bold">Nomor Induk Pegawai
                                            (NIP)</label>
                                        <div class="text-dark fw-semibold fs-5">
                                            <?php echo htmlspecialchars($pegawai['nip'] ?: '-'); ?>
                                        </div>
                                    </div>
                                    <div class="info-group mb-4">
                                        <label class="text-muted small text-uppercase mb-1 fw-bold">Nomor Registrasi
                                            (NRK)</label>
                                        <div class="text-dark fw-semibold fs-5">
                                            <?php echo htmlspecialchars($pegawai['nrk'] ?: '-'); ?>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <label class="text-muted small text-uppercase mb-1 fw-bold">Unit Kerja</label>
                                        <div class="text-dark fw-semibold">
                                            <?php echo htmlspecialchars($pegawai['unit_kerja'] ?: '-'); ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-group mb-4">
                                        <label class="text-muted small text-uppercase mb-1 fw-bold">Pendidikan</label>
                                        <div class="text-dark fw-semibold">
                                            <?php echo htmlspecialchars($pegawai['pendidikan'] ?: '-'); ?>
                                        </div>
                                    </div>
                                    <div class="info-group mb-4">
                                        <label class="text-muted small text-uppercase mb-1 fw-bold">Tempat, Tgl
                                            Lahir</label>
                                        <div class="text-dark fw-semibold">
                                            <?php
                                            $tgl = (!empty($pegawai['tgl_lahir']) && $pegawai['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($pegawai['tgl_lahir'])) : '-';
                                            echo htmlspecialchars($pegawai['tempat_lahir'] ?: '-') . ", " . $tgl;
                                            ?>
                                        </div>
                                    </div>
                                    <div class="info-group">
                                        <label class="text-muted small text-uppercase mb-1 fw-bold">Kontak</label>
                                        <div class="text-dark fw-semibold"><i
                                                class="fas fa-phone-alt small me-2 text-muted"></i>
                                            <?php echo htmlspecialchars($pegawai['no_hp'] ?: '-'); ?></div>
                                        <div class="text-dark fw-semibold"><i
                                                class="fas fa-envelope small me-2 text-muted"></i>
                                            <?php echo htmlspecialchars($pegawai['email'] ?: '-'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100">
                        <div class="card-header bg-white border-bottom py-3">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-bullhorn me-2 text-warning"></i> Pemberitahuan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-indigo-soft border-0 mb-3">
                                <div class="d-flex">
                                    <div class="me-3"><i class="fas fa-check-circle text-indigo"></i></div>
                                    <div>
                                        <h6 class="fw-bold mb-1 small">Data Terverifikasi</h6>
                                        <p class="small mb-0 text-muted">Profil Anda telah diverifikasi oleh Admin pada
                                            1 Mei 2026.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group list-group-flush small">
                                <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                    <div class="text-muted mb-1">DAPODIK • 2 hari lalu</div>
                                    <div class="fw-bold text-dark">Pembaruan Riwayat Gaji Berkala</div>
                                </div>
                                <div class="list-group-item px-0 py-3 border-0">
                                    <div class="text-muted mb-1">SISTEM • 1 minggu lalu</div>
                                    <div class="fw-bold text-dark">Portal Mandiri Guru Versi 2.0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
    .bg-light-soft {
        background-color: #f8fafc;
        min-height: 100vh;
    }

    .letter-spacing-1 {
        letter-spacing: 1px;
    }

    .welcome-banner-light {
        background: #ffffff;
        background-image: radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.05) 0, transparent 50%), radial-gradient(at 100% 100%, rgba(124, 58, 237, 0.05) 0, transparent 50%);
        border: 1px solid #e2e8f0 !important;
    }

    .avatar-wrapper {
        position: relative;
        display: inline-block;
    }

    .status-indicator {
        position: absolute;
        bottom: 8px;
        right: 8px;
        width: 18px;
        height: 18px;
        border: 3px solid #fff;
        border-radius: 50%;
    }

    .icon-box {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .btn-indigo {
        background: #4f46e5;
        color: white;
        border: none;
        transition: all 0.3s;
    }

    .btn-indigo:hover {
        background: #4338ca;
        color: white;
        transform: translateY(-2px);
    }

    /* Soft Colors */
    .bg-primary-soft {
        background-color: #eef2ff;
    }

    .bg-success-soft {
        background-color: #f0fdf4;
    }

    .bg-blue-soft {
        background-color: #eff6ff;
    }

    .text-blue {
        color: #3b82f6;
    }

    .bg-purple-soft {
        background-color: #f5f3ff;
    }

    .text-purple {
        color: #8b5cf6;
    }

    .bg-cyan-soft {
        background-color: #ecfeff;
    }

    .text-cyan {
        color: #06b6d4;
    }

    .bg-orange-soft {
        background-color: #fff7ed;
    }

    .text-orange {
        color: #f97316;
    }

    .alert-indigo-soft {
        background-color: #f5f3ff;
    }

    .text-indigo {
        color: #6366f1;
    }

    .stat-card-light {
        border: 1px solid #e2e8f0 !important;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card-light:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important;
    }

    .card {
        border-radius: 16px;
        overflow: hidden;
    }

    .breadcrumb-item+.breadcrumb-item::before {
        content: "›";
        color: #94a3b8;
        font-size: 1.2rem;
        line-height: 1;
        vertical-align: middle;
    }
</style>