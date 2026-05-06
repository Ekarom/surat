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

$base_dir = file_exists('dbconn.php') ? '' : '../';

// Calculate absolute web root URL more robustly
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$domain = $_SERVER['HTTP_HOST'];
$script_path = str_replace('\\', '/', $_SERVER['SCRIPT_NAME']);
$current_dir = dirname($script_path);
$web_root_path = ($base_dir == '../') ? dirname($current_dir) : $current_dir;
$web_root = $protocol . $domain . rtrim($web_root_path, '/') . '/';

// [CHECKPOINT] Jika data kosong, tampilkan Halaman Instruksi Sinkronisasi
if (!$school || empty($school['nsekolah'])) {
    ?>
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="premium-card p-5">
                    <div class="mb-4">
                        <div class="p-4 bg-light d-inline-block rounded-circle mb-3">
                            <i class="las la-school fs-1 text-muted" style="font-size: 4rem !important;"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark">Data Profil Belum Tersedia</h3>
                    <p class="text-muted mb-4">Profil sekolah di modul Kepegawaian belum diatur atau masih
                        kosong.<br>Silakan lakukan sinkronisasi data dari Profil Utama untuk melanjutkan.</p>
                    <a href="?sinkron_sekolah" class="btn btn-primary btn-lg px-5 rounded-pill shadow">
                        <i class="las la-sync-alt me-2"></i> Sinkronkan Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php
    return;
}

$logo_path = $base_dir . "images/" . ($school['logo_sekolah'] ?: 'logo_default.png');
// Server-side check for existence
$logo_server_path = $base_dir . "images/" . ($school['logo_sekolah'] ?: 'logo_default.png');
if (!file_exists($logo_server_path))
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
        border: 1px solid var(--school-border);
        border-radius: 12px;
        padding: 30px 20px;
        min-height: 120px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.02);
    }

    @media (max-width: 768px) {
        .school-profile-container {
            padding: 0.75rem;
        }

        .sidebar-profile {
            padding: 2rem 1rem;
        }

        .info-card-grid {
            grid-template-columns: 1fr;
        }

        .info-box[style*="grid-column: span 2"] {
            grid-column: span 1 !important;
        }

        .pimpinan-item {
            padding: 1rem;
            gap: 12px;
        }

        .pimpinan-avatar {
            width: 48px;
            height: 48px;
            font-size: 1.5rem;
        }

        .social-link {
            font-size: 0.85rem;
            padding: 10px;
        }

        .section-title {
            font-size: 1rem;
        }

        .kop-btn-container {
            flex-direction: column !important;
            gap: 12px;
        }

        .kop-btn-container button {
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .asset-grid {
            grid-template-columns: 1fr;
        }

        .asset-item[style*="grid-column: span 2"] {
            grid-column: span 1 !important;
        }

        .school-logo-large {
            width: 120px;
            height: 120px;
        }
    }
</style>

<div class="school-profile-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-dark">Data Profil Sekolah</h3>
            <p class="text-muted small mb-0">Identitas sekolah yang dikelola melalui <a href="?sinkron_sekolah"
                    class="text-primary fw-bold">Halaman Sinkronisasi</a>.</p>
        </div>
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
                    <div class="asset-item" style="grid-column: span 2;">
                        <?php
                        $lp = $school['logo_pemda'];
                        $lp_file = ($lp && file_exists($base_dir . "images/" . $lp)) ? $lp : 'logo_jayaraya.png';
                        ?>
                        <img src="<?php echo $base_dir . "images/" . $lp_file; ?>" class="asset-thumb">
                        <div class="asset-label">Logo Pemda</div>
                    </div>
                    <div class="asset-item" style="grid-column: span 2;">
                        <?php
                        $bg = $school['background_login'];
                        $bg_file = ($bg && file_exists($base_dir . "images/" . $bg)) ? $bg : 'bg_default.jpg';
                        ?>
                        <img src="<?php echo $base_dir . "images/" . $bg_file; ?>" class="asset-thumb w-100"
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
                </div>
            </div>

            <div class="premium-card p-4">
                <div class="section-title"><i class="las la-file-alt"></i> Kop Dinas (Header)</div>
                <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 15px;">
                    <div class="d-flex align-items-center gap-3">
                        <i class="las la-info-circle fs-2"></i>
                        <div class="small">Gunakan template untuk hasil yang presisi dan sesuai standar kedinasan.</div>
                    </div>
                </div>

                <div class="editor-wrapper mb-3">
                    <textarea id="summernote_kop"><?php echo htmlspecialchars($school['kop_dinas']); ?></textarea>
                </div>

                <div class="d-flex justify-content-between align-items-center kop-btn-container">
                    <button type="button" class="btn btn-outline-success rounded-pill px-4" id="btnGunakanTemplate"
                        disabled>
                        Gunakan Template Kop
                    </button>
                    <button type="button" class="btn btn-primary rounded-pill px-5 shadow-sm" id="btnSimpanKop"
                        disabled>
                        <i class="las la-save me-1"></i> Simpan Kop Dinas
                    </button>
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
                <div class="col-md-12">
                    <div class="premium-card p-4 h-100">
                        <div class="section-title"><i class="las la-hashtag"></i> Media Sosial</div>
                        <div class="row g-2">
                            <div class="col-md-3 col-6">
                                <a href="<?php echo $school['youtube']; ?>" target="_blank" class="social-link youtube">
                                    <i class="lab la-youtube fs-4"></i> YouTube
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="<?php echo $school['facebook']; ?>" target="_blank"
                                    class="social-link facebook">
                                    <i class="lab la-facebook fs-4"></i> Facebook
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="<?php echo $school['twitter']; ?>" target="_blank" class="social-link twitter">
                                    <i class="lab la-twitter fs-4"></i> Twitter
                                </a>
                            </div>
                            <div class="col-md-3 col-6">
                                <a href="<?php echo $school['instagram']; ?>" target="_blank"
                                    class="social-link instagram">
                                    <i class="lab la-instagram fs-4"></i> Instagram
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load Summernote & Logic -->
<script>
    $(document).ready(function () {
        let summernoteLoaded = false;

        // Dynamic Loader for Summernote
        function loadSummernote(callback) {
            if (summernoteLoaded) {
                if (callback) callback();
                return;
            }

            const cssUrl = '../plugins/summernote/summernote-bs4.min.css';
            const jsUrl = '../plugins/summernote/summernote-bs4.min.js';

            // Load CSS
            if (!$('link[href="' + cssUrl + '"]').length) {
                $('head').append('<link rel="stylesheet" href="' + cssUrl + '">');
            }

            // Load JS
            $.getScript(jsUrl, function () {
                summernoteLoaded = true;
                if (callback) callback();
            }).fail(function () {
                console.warn("Local Summernote failed, falling back to CDN");
                $.getScript('https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js', function () {
                    summernoteLoaded = true;
                    if (callback) callback();
                });
            });
        }

        // Initialize immediately
        loadSummernote(function () {
            $('#summernote_kop').summernote({
                height: 350,
                placeholder: 'Tulis kop dinas di sini...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview']]
                ]
            });
            // Enable buttons after initialization
            $('#btnGunakanTemplate, #btnSimpanKop').prop('disabled', false);
        });

        // Template Logic
        $('#btnGunakanTemplate').click(function () {
            const nsekolah = <?php echo json_encode($school['nsekolah']); ?>;
            const alamat = <?php echo json_encode($school['alamat']); ?>;
            const kelurahan = <?php echo json_encode($school['kelurahan']); ?>;
            const kecamatan = <?php echo json_encode($school['kecamatan']); ?>;
            const kabupaten = <?php echo json_encode($school['kabupaten']); ?>;
            const web = <?php echo json_encode($school['website']); ?>;
            const email = <?php echo json_encode($school['email']); ?>;
            const pos = <?php echo json_encode($school['kodepos']); ?>;
            const logoPemda = <?php
            $lp = $school['logo_pemda'];
            $possible_pemda = [$lp, 'logo_jayaraya.png', 'logo_default.png'];
            $final_pemda_file = 'logo_jayaraya.png';
            foreach ($possible_pemda as $l) {
                if ($l && file_exists($base_dir . 'images/' . $l)) {
                    $final_pemda_file = $l;
                    break;
                }
            }
            echo json_encode($web_root . 'images/' . $final_pemda_file);
            ?>;

            const logoSekolah = <?php
            $ls = $school['logo_sekolah'];
            $possible_sekolah = [$ls, 'logo_sekolah.png', 'logo_default.png'];
            $final_sekolah_file = 'logo_default.png';
            foreach ($possible_sekolah as $l) {
                if ($l && file_exists($base_dir . 'images/' . $l)) {
                    $final_sekolah_file = $l;
                    break;
                }
            }
            echo json_encode($web_root . 'images/' . $final_sekolah_file);
            ?>;

            const template = `
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 2px; font-family: 'Arial', sans-serif; color: #000; table-layout: fixed;">
                    <tbody>
                        <tr>
                            <td style="width: 15%; text-align: center; vertical-align: middle; padding-bottom: 5px;">
                                <img src="${logoPemda}" style="width: 90px; height: auto; max-width: 100%;" alt="Logo DKI">
                            </td>
                            <td style="width: 70%; text-align: center; vertical-align: middle;">
                                <div style="margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase; line-height: 1.2;">PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA</div>
                                <div style="margin: 0; font-size: 16px; font-weight: bold; text-transform: uppercase; line-height: 1.2;">DINAS PENDIDIKAN</div>
                                <div style="margin: 2px 0; font-size: 24px; font-weight: 800; text-transform: uppercase; line-height: 1.1;">${nsekolah}</div>
                                <div style="margin: 0; font-size: 12px; line-height: 1.4;">${alamat} Kel. ${kelurahan} Kec. ${kecamatan} ${kabupaten}</div>
                                <div style="margin: 0; font-size: 12px; line-height: 1.4;">Website: ${web} | Email: ${email}</div>
                                <div style="margin: 8px 0 0 0; font-size: 20px; font-weight: bold; letter-spacing: 12px; text-transform: uppercase; line-height: 1; padding-left: 12px;">J A K A R T A</div>
                            </td>
                            <td style="width: 15%; text-align: center; vertical-align: middle; padding-bottom: 5px;">
                                <img src="${logoSekolah}" style="width: 90px; height: auto; max-width: 100%;" alt="Logo Sekolah">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div style="text-align: right; font-style: italic; font-size: 12px; margin: 0 0 2px 0; font-family: 'Arial', sans-serif;">Kode Pos ${pos}</div>
                <hr style="border: 0; border-top: 5px solid black; opacity: 1; margin: 0;">
            `;

            $('#summernote_kop').summernote('code', template);
        });

        // Save Logic
        $('#btnSimpanKop').click(function () {
            const content = $('#summernote_kop').summernote('code');
            const btn = $(this);
            const originalHtml = btn.html();

            btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-1"></i> Menyimpan...');

            $.ajax({
                url: 'proses_profil_sekolah.php',
                type: 'POST',
                data: {
                    kop_dinas: content
                },
                success: function (res) {
                    if (res.status === 'success') {
                        if (typeof showToast === 'function') {
                            showToast(res.message, 'success');
                        } else {
                            alert(res.message);
                        }
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        if (typeof showToast === 'function') {
                            showToast(res.message, 'error');
                        } else {
                            alert(res.message);
                        }
                        btn.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function () {
                    alert('Gagal menghubungi server.');
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>