<?php
/**
 * Profil Saya - Teacher Portal (Visual Profile)
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
    echo "<div class='container-fluid py-4'><div class='alert alert-danger shadow-sm rounded-4'>Profil tidak ditemukan.</div></div>";
    return;
}

$poto_db = $pegawai['foto'] ?? '';
$src_foto = (!empty($poto_db) && file_exists("../file/datakepegawaian/" . $poto_db)) ? "../file/datakepegawaian/" . $poto_db : "../images/default.png";

// Get latest history for highlights
$stmt_last = $conn->prepare("SELECT kategori, deskripsi FROM riwayat_kepegawaian WHERE pegawai_id = ? ORDER BY tmt DESC LIMIT 3");
$stmt_last->bind_param("i", $id_pegawai);
$stmt_last->execute();
$highlights = $stmt_last->get_result();
?>

<style>
    :root {
        --profile-primary: #4f46e5;
        --profile-secondary: #818cf8;
        --profile-dark: #1e1b4b;
        --profile-light: #f5f3ff;
    }

    .profile-hero {
        background: linear-gradient(135deg, var(--profile-primary) 0%, var(--profile-secondary) 100%);
        height: 200px;
        border-radius: 20px 20px 0 0;
        position: relative;
        margin-bottom: 80px;
        box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.2);
    }

    .profile-photo-wrapper {
        position: absolute;
        bottom: -60px;
        left: 40px;
        padding: 6px;
        background: #fff;
        border-radius: 24px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
    }

    .profile-photo-large {
        width: 150px;
        height: 150px;
        border-radius: 18px;
        object-fit: cover;
    }

    .profile-meta {
        position: absolute;
        bottom: -50px;
        left: 210px;
        color: #fff;
    }

    .profile-name {
        font-size: 1.8rem;
        font-weight: 800;
        margin-bottom: 4px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        color: #1e293b;
    }

    .profile-role {
        font-size: 1rem;
        font-weight: 500;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .badge-verified {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .stats-card {
        background: #fff;
        border-radius: 16px;
        padding: 20px;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .stats-card:hover {
        border-color: var(--profile-primary);
        transform: translateY(-4px);
    }

    .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        font-size: 1.25rem;
    }

    .highlight-item {
        padding: 16px;
        border-left: 3px solid var(--profile-secondary);
        background: #fff;
        margin-bottom: 12px;
        border-radius: 0 12px 12px 0;
        transition: all 0.2s;
    }

    .highlight-item:hover {
        background: var(--profile-light);
    }

    .contact-pill {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        background: #fff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        color: #475569;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.2s;
    }

    .contact-pill:hover {
        background: var(--profile-primary);
        color: #fff;
        border-color: var(--profile-primary);
    }

    .section-title {
        font-weight: 800;
        color: var(--profile-dark);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @media (max-width: 768px) {
        .profile-hero {
            height: 120px;
            margin-bottom: 120px;
        }

        .profile-photo-wrapper {
            left: 50%;
            transform: translateX(-50%);
            bottom: -60px;
        }

        .profile-meta {
            left: 0;
            right: 0;
            bottom: -110px;
            text-align: center;
        }

        .profile-name {
            font-size: 1.4rem;
            color: #1e293b;
        }
    }
</style>

<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="profile-hero">
            <div class="profile-photo-wrapper">
                <img src="<?php echo $src_foto; ?>" class="profile-photo-large" alt="Foto">
            </div>
            <div class="profile-meta">
                <h1 class="profile-name"><?php echo htmlspecialchars($pegawai['nm_pegawai']); ?></h1>
                <div class="profile-role">
                    <i class="las la-chalkboard-teacher text-primary"></i>
                    <?php echo htmlspecialchars($pegawai['jabatan'] ?: 'Tenaga Pendidik'); ?>
                    <span class="badge-verified">
                        <i class="las la-check-circle"></i> Verified
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mt-2">
        <!-- Quick Stats -->
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stat-icon bg-primary text-white">
                    <i class="las la-user-tie"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase">Status</div>
                <div class="h5 fw-bold mb-0"><?php echo htmlspecialchars($pegawai['status_pegawai'] ?: '-'); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stat-icon bg-success text-white">
                    <i class="las la-layer-group"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase">Golongan</div>
                <div class="h5 fw-bold mb-0"><?php echo htmlspecialchars($pegawai['golongan'] ?: '-'); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stat-icon bg-warning text-white">
                    <i class="las la-calendar-alt"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase">Masa Kerja</div>
                <div class="h5 fw-bold mb-0">
                    <?php echo ($pegawai['masa_kerja_thn'] ?: '0') . ' Thn ' . ($pegawai['masa_kerja_bln'] ?: '0') . ' Bln'; ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card">
                <div class="stat-icon bg-info text-white">
                    <i class="las la-university"></i>
                </div>
                <div class="text-muted small fw-bold text-uppercase">Pendidikan</div>
                <div class="h5 fw-bold mb-0"><?php echo htmlspecialchars($pegawai['pendidikan'] ?: '-'); ?></div>
            </div>
        </div>

        <!-- Career Highlights -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="section-title">
                    <i class="las la-award text-primary"></i> Highlight Karir & Riwayat
                </h5>
                <?php if ($highlights->num_rows > 0): ?>
                    <?php while ($h = $highlights->fetch_assoc()): ?>
                        <div class="highlight-item">
                            <div class="small fw-bold text-primary text-uppercase">
                                <?php echo htmlspecialchars($h['kategori']); ?></div>
                            <div class="fw-bold mt-1"><?php echo htmlspecialchars($h['deskripsi']); ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-4 text-muted">
                        <i class="las la-info-circle fa-2x mb-2 opacity-50"></i>
                        <p>Belum ada highlight riwayat untuk ditampilkan.</p>
                    </div>
                <?php endif; ?>

                <div class="mt-3 text-end">
                    <a href="?riwayat" class="btn btn-link text-primary fw-bold text-decoration-none">
                        Lihat Seluruh Riwayat <i class="las la-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Professional Summary Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mt-4 bg-primary text-white">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h4 class="fw-bold">Bio-Data Lengkap</h4>
                        <p class="opacity-75 mb-0">Lihat dan perbarui seluruh informasi kepegawaian Anda pada halaman
                            Data Saya.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="?isi_data" class="btn btn-light fw-bold px-4 py-2 rounded-pill">
                            Lengkapi Data Saya <i class="las la-edit ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- School Info Card [NEW] -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mt-4">
                <h5 class="section-title">
                    <i class="las la-school text-primary"></i> Unit Kerja / Sekolah
                </h5>
                <div class="row align-items-center">
                    <div class="col-auto">
                        <div class="bg-light p-3 rounded-4">
                            <i class="las la-building fa-3x text-primary opacity-50"></i>
                        </div>
                    </div>
                    <div class="col">
                        <div class="h5 fw-bold mb-1"><?php echo htmlspecialchars($GLOBALS['namasek'] ?? 'Sekolah Belum Diatur'); ?></div>
                        <div class="text-muted small">NPSN: <?php echo htmlspecialchars($GLOBALS['npsn'] ?? '-'); ?></div>
                        <div class="mt-2 small text-dark">
                            <i class="las la-map-marker me-1"></i> <?php echo htmlspecialchars($GLOBALS['alamatsek'] ?? '-'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact & Actions -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <h5 class="section-title">
                    <i class="las la-id-card text-primary"></i> Informasi Kontak
                </h5>
                <div class="d-grid gap-3">
                    <a href="tel:<?php echo $pegawai['no_hp']; ?>" class="contact-pill">
                        <i class="las la-phone-alt"></i>
                        <?php echo htmlspecialchars($pegawai['no_hp'] ?: 'Tidak ada nomor'); ?>
                    </a>
                    <a href="mailto:<?php echo $pegawai['email']; ?>" class="contact-pill">
                        <i class="las la-envelope"></i>
                        <?php echo htmlspecialchars($pegawai['email'] ?: 'Tidak ada email'); ?>
                    </a>
                    <div class="contact-pill">
                        <i class="las la-map-marker-alt"></i>
                        <?php echo htmlspecialchars($pegawai['alamat'] ?: 'Alamat belum diisi'); ?>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="section-title small text-uppercase text-muted">Quick Actions</h5>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="?isi_data" class="btn btn-outline-primary w-100 fw-bold py-3">
                            <i class="las la-edit mb-2 d-block"></i> Edit Data
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="?administrasi" class="btn btn-outline-dark w-100 fw-bold py-3">
                            <i class="las la-file-pdf mb-2 d-block"></i> Berkas
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
