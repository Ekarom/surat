<?php
/**
 * Data Profil Sekolah
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

// Data already fetched in dbconn.php as global variables or $g array
// But let's make sure we have the latest data if this is a standalone include
$query = "SELECT * FROM profils WHERE id = 1";
$res = $conn->query($query);
$school = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : [];

if (!$school) {
    echo "<div class='container-fluid py-4'><div class='alert alert-warning'>Data profil sekolah belum diatur.</div></div>";
    return;
}

$base_dir = file_exists('dbconn.php') ? '' : '../';
$logo_path = $base_dir . "images/" . ($school['logo_sekolah'] ?: 'logo_default.png');
if (!file_exists($logo_path))
    $logo_path = $base_dir . "images/logo_default.png";
?>

<style>
    :root {
        --school-primary: #4f46e5;
        --school-primary-light: #e0e7ff;
        --school-secondary: #3b82f6;
        --school-accent: #f59e0b;
        --school-text-main: #1e293b;
        --school-text-muted: #64748b;
        --school-bg: #f8fafc;
        --school-card-bg: #ffffff;
        --school-border: #e2e8f0;
        --school-radius: 24px;
        --school-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
    }

    .school-profile-container {
        padding: 1.5rem;
        background: var(--school-bg);
    }

    .premium-card {
        background: var(--school-card-bg);
        border-radius: var(--school-radius);
        border: 1px solid var(--school-border);
        box-shadow: var(--school-shadow);
        overflow: hidden;
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .premium-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .sidebar-profile {
        text-align: center;
        padding: 2.5rem 1.5rem;
    }

    .school-logo-wrapper {
        position: relative;
        display: inline-block;
        margin-bottom: 1.5rem;
    }

    .school-logo-large {
        width: 140px;
        height: 140px;
        object-fit: contain;
        background: #fff;
        padding: 12px;
        border-radius: 40px;
        box-shadow: 0 15px 30px -10px rgba(79, 70, 229, 0.2);
        border: 4px solid var(--school-primary-light);
    }

    .npsn-badge {
        display: inline-block;
        background: var(--school-primary-light);
        color: var(--school-primary);
        font-weight: 700;
        font-size: 0.85rem;
        padding: 0.4rem 1rem;
        border-radius: 12px;
        margin-top: 0.5rem;
    }

    .contact-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: #f1f5f9;
        border-radius: 16px;
        margin-bottom: 10px;
        color: var(--school-text-main);
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .contact-item:hover {
        background: var(--school-primary-light);
        color: var(--school-primary);
        transform: translateX(5px);
    }

    .contact-icon {
        width: 36px;
        height: 36px;
        background: #fff;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }

    .section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: var(--school-text-main);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: var(--school-primary);
        font-size: 1.4rem;
    }

    .asset-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .asset-item {
        background: #f8fafc;
        border: 1px solid var(--school-border);
        border-radius: 16px;
        padding: 12px;
        text-align: center;
        transition: all 0.2s ease;
    }

    .asset-item:hover {
        border-color: var(--school-primary);
        background: #fff;
    }

    .asset-thumb {
        height: 50px;
        object-fit: contain;
        margin-bottom: 8px;
    }

    .asset-label {
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--school-text-muted);
        text-transform: uppercase;
    }

    .info-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 16px;
    }

    .info-box {
        padding: 1.25rem;
        background: #f8fafc;
        border-radius: 20px;
        border: 1px solid var(--school-border);
    }

    .info-box-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--school-text-muted);
        text-transform: uppercase;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .info-box-value {
        font-weight: 600;
        color: var(--school-text-main);
        font-size: 1rem;
        line-height: 1.5;
    }

    .pimpinan-item {
        background: #fff;
        border: 1px solid var(--school-border);
        border-radius: 20px;
        padding: 1.25rem;
        display: flex;
        align-items: center;
        gap: 16px;
        height: 100%;
        transition: all 0.3s ease;
    }

    .pimpinan-item:hover {
        border-color: var(--school-primary);
        transform: translateY(-5px);
    }

    .pimpinan-avatar {
        width: 56px;
        height: 56px;
        background: var(--school-primary-light);
        color: var(--school-primary);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.75rem;
    }

    .social-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 16px;
        background: #f1f5f9;
        color: var(--school-text-main);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s ease;
    }

    .social-link.youtube:hover {
        background: #fee2e2;
        color: #dc2626;
    }

    .social-link.facebook:hover {
        background: #dbeafe;
        color: #2563eb;
    }

    .social-link.twitter:hover {
        background: #e0f2fe;
        color: #0ea5e9;
    }

    .social-link.instagram:hover {
        background: #fdf2f8;
        color: #db2777;
    }

    .kop-preview {
        background: #fff;
        border: 2px dashed var(--school-border);
        border-radius: 20px;
        padding: 20px;
        min-height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 768px) {
        .school-profile-container {
            padding: 1rem;
        }

        .sidebar-profile {
            padding: 1.5rem 1rem;
        }
    }
</style>

<div class="school-profile-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark">Data Profil Sekolah</h3>
            <p class="text-muted small mb-0">Informasi identitas dan pimpinan satuan pendidikan.</p>
        </div>
        <?php if (isset($lv) && $lv == '1'): ?>
            <button class="btn btn-primary px-4 shadow-sm rounded-pill" data-bs-toggle="modal"
                data-bs-target="#editProfilModal">
                <i class="las la-edit me-1"></i> Edit Profil
            </button>
        <?php endif; ?>
    </div>

    <div class="row g-4">
        <!-- Left Sidebar: Brand Identity -->
        <div class="col-lg-4 col-md-5">
            <div class="premium-card sidebar-profile">
                <div class="school-logo-wrapper">
                    <img src="<?php echo $logo_path; ?>" alt="Logo Sekolah" class="img-fluid mb-3"
                        style="max-height: 150px;">
                </div>
                <h4 class="fw-bold mb-1 px-3"><?php echo htmlspecialchars($school['nsekolah']); ?></h4>
                <div class="npsn-badge">NPSN: <?php echo htmlspecialchars($school['npsn']); ?></div>

                <div class="mt-4 px-2 text-start">
                    <a href="mailto:<?php echo $school['email']; ?>" class="contact-item">
                        <div class="contact-icon text-danger"><i class="las la-envelope"></i></div>
                        <div class="text-truncate">
                            <div class="extra-small text-muted fw-bold">Email</div>
                            <div class="small fw-bold"><?php echo htmlspecialchars($school['email']); ?></div>
                        </div>
                    </a>
                    <a href="tel:<?php echo $school['no_telp']; ?>" class="contact-item">
                        <div class="contact-icon text-primary"><i class="las la-phone"></i></div>
                        <div>
                            <div class="extra-small text-muted fw-bold">Telepon</div>
                            <div class="small fw-bold"><?php echo htmlspecialchars($school['no_telp']); ?></div>
                        </div>
                    </a>
                    <a href="<?php echo (strpos($school['website'], 'http') === 0 ? '' : 'https://') . $school['website']; ?>"
                        target="_blank" class="contact-item">
                        <div class="contact-icon text-info"><i class="las la-globe"></i></div>
                        <div class="text-truncate">
                            <div class="extra-small text-muted fw-bold">Website</div>
                            <div class="small fw-bold"><?php echo htmlspecialchars($school['website']); ?></div>
                        </div>
                    </a>
                </div>
            </div>

            <div class="premium-card p-4">
                <div class="section-title"><i class="las la-images"></i> Aset Visual</div>
                <div class="asset-grid">
                    <div class="asset-item align-center">
                        <?php $logo_pemda_path = $base_dir . "images/" . ($school['logo_pemda'] ?: 'logo_default.png'); ?>
                        <img src="<?php echo $logo_pemda_path; ?>" class="asset-thumb">
                        <div class="asset-label">Logo Pemda</div>
                    </div>
                    <div class="asset-item" style="grid-column: span 2;">
                        <?php $bg_login_path = $base_dir . "images/" . ($school['background_login'] ?: 'bg_default.jpg'); ?>
                        <img src="<?php echo $bg_login_path; ?>" class="asset-thumb w-100"
                            style="object-fit: cover; border-radius: 8px;">
                        <div class="asset-label mt-1">Background Login</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content: Detailed Info -->
        <div class="col-lg-8 col-md-7">
            <div class="premium-card p-4">
                <div class="section-title"><i class="las la-info-circle"></i> Identitas Sekolah</div>
                <div class="info-card-grid">
                    <div class="info-box" style="grid-column: span 2;">
                        <div class="info-box-label"><i class="las la-map-marker-alt"></i> Alamat Jalan</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($school['alamat']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Kelurahan</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($school['kelurahan']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Kecamatan</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($school['kecamatan']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Kota / Kabupaten</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($school['kabupaten']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Provinsi</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($school['provinsi']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Kode Pos</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($school['kodepos']); ?></div>
                    </div>
                    <div class="info-box">
                        <div class="info-box-label">Kode Sekolah</div>
                        <div class="info-box-value"><?php echo htmlspecialchars($school['kode'] ?: '-'); ?></div>
                    </div>
                </div>
            </div>

            <div class="premium-card p-4">
                <div class="section-title"><i class="las la-users"></i> Pimpinan & Pejabat</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="pimpinan-item">
                            <div class="pimpinan-avatar"><i class="las la-user-tie"></i></div>
                            <div>
                                <div class="extra-small text-muted fw-bold text-uppercase">Kepala Sekolah</div>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($school['kepsek']); ?></div>
                                <div class="extra-small text-muted">NIP.
                                    <?php echo htmlspecialchars($school['nipkepsek']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="pimpinan-item">
                            <div class="pimpinan-avatar"><i class="las la-user-shield"></i></div>
                            <div>
                                <div class="extra-small text-muted fw-bold text-uppercase">Kepala TU</div>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($school['ktu'] ?: '-'); ?>
                                </div>
                                <div class="extra-small text-muted">NIP.
                                    <?php echo htmlspecialchars($school['nipktu'] ?: '-'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="pimpinan-item">
                            <div class="pimpinan-avatar"><i class="las la-user-check"></i></div>
                            <div>
                                <div class="extra-small text-muted fw-bold text-uppercase">Pengawas Sekolah</div>
                                <div class="fw-bold text-dark">
                                    <?php echo htmlspecialchars($school['pengawas'] ?: '-'); ?>
                                </div>
                                <div class="extra-small text-muted">NIP.
                                    <?php echo htmlspecialchars($school['nippengawas'] ?: '-'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-6">
                    <div class="premium-card p-4 h-100">
                        <div class="section-title"><i class="las la-hashtag"></i> Media Sosial</div>
                        <div class="row g-2">
                            <div class="col-6">
                                <a href="<?php echo $school['youtube']; ?>" target="_blank" class="social-link youtube">
                                    <i class="lab la-youtube fs-4"></i> YouTube
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo $school['facebook']; ?>" target="_blank"
                                    class="social-link facebook">
                                    <i class="lab la-facebook fs-4"></i> Facebook
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo $school['twitter']; ?>" target="_blank" class="social-link twitter">
                                    <i class="lab la-twitter fs-4"></i> Twitter
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo $school['instagram']; ?>" target="_blank"
                                    class="social-link instagram">
                                    <i class="lab la-instagram fs-4"></i> Instagram
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="premium-card p-4 h-100">
                        <div class="section-title"><i class="las la-file-alt"></i> Kop Dinas (Preview)</div>
                        <div class="kop-preview overflow-hidden">
                            <div style="zoom: 0.5; width: 200%;">
                                <?php echo $school['kop_dinas'] ?: '<span class="text-muted italic">Belum diatur</span>'; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (isset($lv) && $lv == '1'): ?>
    <!-- Edit Profil Modal -->
    <div class="modal fade" id="editProfilModal" tabindex="-1" aria-labelledby="editProfilModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; overflow: hidden;">
                <div class="modal-header bg-primary text-white p-4 border-0">
                    <div>
                        <h5 class="modal-title fw-bold" id="editProfilModalLabel text-white">Pengaturan Profil Sekolah</h5>
                        <p class="mb-0 small opacity-75">Sesuaikan identitas dan informasi pimpinan sekolah.</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form id="formEditProfil" enctype="multipart/form-data">
                    <div class="modal-body p-0">
                        <ul class="nav nav-pills nav-justified bg-light p-2" id="editTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active rounded-pill py-3 fw-bold" id="identity-tab"
                                    data-bs-toggle="tab" data-bs-target="#identity" type="button" role="tab">
                                    <i class="las la-id-card me-1"></i> Identitas
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link rounded-pill py-3 fw-bold" id="officials-tab" data-bs-toggle="tab"
                                    data-bs-target="#officials" type="button" role="tab">
                                    <i class="las la-user-tie me-1"></i> Pimpinan
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link rounded-pill py-3 fw-bold" id="social-tab" data-bs-toggle="tab"
                                    data-bs-target="#social" type="button" role="tab">
                                    <i class="las la-share-alt me-1"></i> Media Sosial
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link rounded-pill py-3 fw-bold" id="assets-tab" data-bs-toggle="tab"
                                    data-bs-target="#assets" type="button" role="tab">
                                    <i class="las la-photo-video me-1"></i> Aset Visual
                                </button>
                            </li>
                        </ul>
                        <div class="tab-content p-4" id="editTabsContent">
                            <!-- Tab Identitas -->
                            <div class="tab-pane fade show active" id="identity" role="tabpanel">
                                <div class="mb-4">
                                    <label class="form-label fw-bold"><i class="las la-edit me-1 text-primary"></i>Kop Surat
                                        / Header</label>
                                    <textarea name="kop_dinas"
                                        id="kop_dinas_editor"><?php echo $school['kop_dinas'] ?? ''; ?></textarea>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Nama Sekolah</label>
                                        <input type="text" name="nsekolah" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['nsekolah']); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">NPSN</label>
                                        <input type="text" name="npsn" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['npsn']); ?>" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Kode Sekolah</label>
                                        <input type="text" name="kode" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['kode'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">Alamat Lengkap</label>
                                        <textarea name="alamat" class="form-control rounded-3"
                                            rows="2"><?php echo htmlspecialchars($school['alamat']); ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Kelurahan</label>
                                        <input type="text" name="kelurahan" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['kelurahan']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Kecamatan</label>
                                        <input type="text" name="kecamatan" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['kecamatan']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Kabupaten/Kota</label>
                                        <input type="text" name="kabupaten" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['kabupaten']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Provinsi</label>
                                        <input type="text" name="provinsi" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['provinsi']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Kode Pos</label>
                                        <input type="text" name="kodepos" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['kodepos']); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Telepon</label>
                                        <input type="text" name="no_telp" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['no_telp']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Email Sekolah</label>
                                        <input type="email" name="email" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['email']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Website</label>
                                        <input type="text" name="website" class="form-control rounded-3"
                                            value="<?php echo htmlspecialchars($school['website']); ?>">
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Pimpinan -->
                            <div class="tab-pane fade" id="officials" role="tabpanel">
                                <div class="bg-light p-3 rounded-4 mb-4">
                                    <h6 class="fw-bold text-primary mb-3 d-flex align-items-center"><i
                                            class="las la-user-tie me-2 fs-4"></i> Kepala Sekolah</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Nama Lengkap & Gelar</label>
                                            <input type="text" name="kepsek" class="form-control rounded-3"
                                                value="<?php echo htmlspecialchars($school['kepsek']); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">NIP</label>
                                            <input type="text" name="nipkepsek" class="form-control rounded-3"
                                                value="<?php echo htmlspecialchars($school['nipkepsek']); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">NRK</label>
                                            <input type="text" name="nrkkepsek" class="form-control rounded-3"
                                                value="<?php echo htmlspecialchars($school['nrkkepsek'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-light p-3 rounded-4 mb-4">
                                    <h6 class="fw-bold text-primary mb-3 d-flex align-items-center"><i
                                            class="las la-user-shield me-2 fs-4"></i> Kepala Tata Usaha</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Nama Lengkap</label>
                                            <input type="text" name="ktu" class="form-control rounded-3"
                                                value="<?php echo htmlspecialchars($school['ktu'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">NIP</label>
                                            <input type="text" name="nipktu" class="form-control rounded-3"
                                                value="<?php echo htmlspecialchars($school['nipktu'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">NRK</label>
                                            <input type="text" name="nrkktu" class="form-control rounded-3"
                                                value="<?php echo htmlspecialchars($school['nrkktu'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-light p-3 rounded-4">
                                    <h6 class="fw-bold text-primary mb-3 d-flex align-items-center"><i
                                            class="las la-user-check me-2 fs-4"></i> Pengawas Sekolah</h6>
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold">Nama Lengkap</label>
                                            <input type="text" name="pengawas" class="form-control rounded-3"
                                                value="<?php echo htmlspecialchars($school['pengawas'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">NIP</label>
                                            <input type="text" name="nippengawas" class="form-control rounded-3"
                                                value="<?php echo htmlspecialchars($school['nippengawas'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small fw-bold">NRK</label>
                                            <input type="text" name="nrkpengawas" class="form-control rounded-3"
                                                value="<?php echo htmlspecialchars($school['nrkpengawas'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Media Sosial -->
                            <div class="tab-pane fade" id="social" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-danger text-white border-0"><i
                                                    class="lab la-youtube fs-4"></i></span>
                                            <input type="text" name="youtube" class="form-control"
                                                placeholder="Link YouTube"
                                                value="<?php echo htmlspecialchars($school['youtube'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-primary text-white border-0"><i
                                                    class="lab la-facebook fs-4"></i></span>
                                            <input type="text" name="facebook" class="form-control"
                                                placeholder="Link Facebook"
                                                value="<?php echo htmlspecialchars($school['facebook'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-info text-white border-0"><i
                                                    class="lab la-twitter fs-4"></i></span>
                                            <input type="text" name="twitter" class="form-control"
                                                placeholder="Link Twitter"
                                                value="<?php echo htmlspecialchars($school['twitter'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="input-group">
                                            <span class="input-group-text bg-danger text-white border-0"
                                                style="background: linear-gradient(45deg, #f09433 0%,#e6683c 25%,#dc2743 50%,#cc2366 75%,#bc1888 100%);"><i
                                                    class="lab la-instagram fs-4"></i></span>
                                            <input type="text" name="instagram" class="form-control"
                                                placeholder="Link Instagram"
                                                value="<?php echo htmlspecialchars($school['instagram'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Tab Logo & Assets -->
                            <div class="tab-pane fade" id="assets" role="tabpanel">
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded-4 text-center h-100">
                                            <label class="form-label small fw-bold d-block mb-3">Logo Sekolah</label>
                                            <div class="mb-3 bg-light rounded-3 p-3 d-inline-block">
                                                <img src="<?php echo $logo_path; ?>"
                                                    style="height: 100px; width: 100px; object-fit: contain;">
                                            </div>
                                            <input type="file" name="logo_sekolah"
                                                class="form-control form-control-sm rounded-pill" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded-4 text-center h-100">
                                            <label class="form-label small fw-bold d-block mb-3">Logo Pemda</label>
                                            <div class="mb-3 bg-light rounded-3 p-3 d-inline-block">
                                                <img src="<?php echo $logo_pemda_path; ?>"
                                                    style="height: 100px; width: 100px; object-fit: contain;">
                                            </div>
                                            <input type="file" name="logo_pemda"
                                                class="form-control form-control-sm rounded-pill" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="p-3 border rounded-4 text-center h-100">
                                            <label class="form-label small fw-bold d-block mb-3">Stempel Sekolah</label>
                                            <div class="mb-3 bg-light rounded-3 p-3 d-inline-block">
                                                <img src="<?php echo $stempel_path; ?>"
                                                    style="height: 100px; width: 100px; object-fit: contain;">
                                            </div>
                                            <input type="file" name="stempel_sekolah"
                                                class="form-control form-control-sm rounded-pill" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-4 text-center">
                                            <label class="form-label small fw-bold d-block mb-3">Background Login</label>
                                            <div class="mb-3 bg-light rounded-3 p-2 w-100 overflow-hidden"
                                                style="height: 120px;">
                                                <img src="<?php echo $bg_login_path; ?>" class="w-100 h-100"
                                                    style="object-fit: cover; border-radius: 8px;">
                                            </div>
                                            <input type="file" name="background_login"
                                                class="form-control form-control-sm rounded-pill" accept="image/*">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="p-3 border rounded-4 text-center">
                                            <label class="form-label small fw-bold d-block mb-3">TTD Kepala Sekolah</label>
                                            <div class="mb-3 bg-light rounded-3 p-2 w-100 overflow-hidden"
                                                style="height: 120px; display: flex; align-items: center; justify-content: center;">
                                                <?php $ttd_path = $base_dir . "file/logo/" . ($school['ttd_kepsek'] ?: 'ttd_default.png'); ?>
                                                <img src="<?php echo $ttd_path; ?>"
                                                    style="max-height: 100%; max-width: 100%; object-fit: contain;">
                                            </div>
                                            <input type="file" name="ttd_kepsek"
                                                class="form-control form-control-sm rounded-pill" accept="image/*">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3 border-0">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none px-4"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-5 fw-bold shadow rounded-pill" id="btnSimpanProfil">
                            <i class="las la-save me-1"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Load Summernote & SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {
            // Initialize Summernote
            $('#kop_dinas_editor').summernote({
                placeholder: 'Masukkan Kop Surat...',
                tabsize: 2,
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });

            // Handle Form Submit
            $('#formEditProfil').on('submit', function (e) {
                e.preventDefault();

                const btn = $('#btnSimpanProfil');
                const originalText = btn.html();
                btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Menyimpan...');

                const formData = new FormData(this);

                $.ajax({
                    url: 'kepegawaian/proses_profil_sekolah.php',
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (res) {
                        if (res.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                            btn.prop('disabled', false).html(originalText);
                        }
                    },
                    error: function () {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem.' });
                        btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
<?php endif; ?>