<?php
/**
 * Data Saya - Teacher Portal (Full Administrative Data)
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$base_dir = file_exists('dbconn.php') ? '' : '../';

$id_pegawai = $_SESSION['id'] ?? 0;
$level_user = $_SESSION['level'] ?? '';

// Check if an ID is passed in the URL (e.g. for Admins viewing a profile)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $requested_id = $_GET['id'];

    // SECURITY: Guru (Level 4) can only view their own ID
    if ($level_user == '4' && $requested_id != $id_pegawai) {
        echo "
        <div class='container-fluid py-4'>
            <div class='alert alert-danger shadow-sm rounded-4 p-4'>
                <div class='d-flex align-items-center mb-2'>
                    <i class='las la-exclamation-triangle fa-2x me-3'></i>
                    <h4 class='mb-0 fw-bold'>Akses Ditolak</h4>
                </div>
                <p class='mb-0'>Anda hanya dapat melihat data Anda sendiri!</p>
                <hr>
                <a href='?data_saya' class='btn btn-outline-danger btn-sm'>Kembali ke Data Saya</a>
            </div>
        </div>";
        return;
    }

    // Admins can override the session ID with the GET parameter
    $id_pegawai = $requested_id;
}

$query = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$query->bind_param("i", $id_pegawai);
$query->execute();
$pegawai = $query->get_result()->fetch_assoc();

if (!$pegawai) {
    echo "<div class='container-fluid py-4'><div class='alert alert-danger shadow-sm rounded-4'>Data tidak ditemukan.</div></div>";
    return;
}

$poto_db = $pegawai['foto'] ?? '';
$src_foto = (!empty($poto_db) && file_exists("../file/datakepegawaian/" . $poto_db)) ? "../file/datakepegawaian/" . $poto_db : "../images/default.png";

// Fetch All History Data
$stmt_hist = $conn->prepare("SELECT * FROM riwayat_kepegawaian WHERE pegawai_id = ? ORDER BY tmt ASC");
$stmt_hist->bind_param("i", $id_pegawai);
$stmt_hist->execute();
$history_all = $stmt_hist->get_result();
$hist_rows = [];
while ($row = $history_all->fetch_assoc()) {
    $hist_rows[] = $row;
}

// Calculate completion percentage (Synced with pengisian_data standards)
$completion_fields = [
    'nm_pegawai' => 'Nama Lengkap',
    'jenis_kelamin' => 'Jenis Kelamin',
    'agama' => 'Agama',
    'tempat_lahir' => 'Tempat Lahir',
    'tgl_lahir' => 'Tanggal Lahir',
    'nik' => 'NIK (KTP)',
    'no_kk' => 'No. KK',
    'no_hp' => 'No. HP',
    'email' => 'Email',
    'alamat' => 'Alamat',
    'status_pegawai' => 'Status Pegawai',
    'golongan' => 'Pangkat/Golongan',
    'pendidikan' => 'Pendidikan',
    'unit_kerja' => 'Unit Kerja'
];

$missing_fields = [];
$filled = 0;
foreach ($completion_fields as $f => $label) {
    $val = trim($pegawai[$f] ?? '');
    if ($val !== '' && $val !== '-' && $val !== '0000-00-00' && $val !== '0') {
        $filled++;
    } else {
        $missing_fields[] = $label;
    }
}

// Required history check
$required_hist = ['Pangkat' => 'Riwayat Pangkat', 'Jabatan' => 'Riwayat Jabatan', 'Pendidikan' => 'Riwayat Pendidikan'];
$user_hist_cats = array_column($hist_rows, 'kategori');
foreach ($required_hist as $rh_cat => $rh_label) {
    if (in_array($rh_cat, $user_hist_cats)) {
        $filled++;
    } else {
        $missing_fields[] = $rh_label;
    }
}
$completion = round(($filled / (count($completion_fields) + count($required_hist))) * 100);
$tooltip_text = !empty($missing_fields) ? "Belum diisi: " . implode(', ', $missing_fields) : "Data sudah lengkap!";

?>

<!-- External Assets for Premium UI -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

<style>
    :root {
        --sap-primary: #4f46e5;
        --sap-primary-light: rgba(79, 70, 229, 0.1);
        --sap-primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --sap-secondary: #64748b;
        --sap-dark: #1e293b;
        --sap-gray-50: #f8fafc;
        --sap-gray-100: #f1f5f9;
        --sap-gray-200: #e2e8f0;
        --sap-border-radius: 1rem;
    }

    .card-modern {
        border: none;
        border-radius: var(--sap-border-radius);
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        background: #fff;
        margin-bottom: 20px;
    }

    .nav-tabs-modern {
        border-bottom: 2px solid var(--sap-gray-100);
        margin-bottom: 2.5rem;
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        border-top: none;
        border-left: none;
        border-right: none;
    }

    .nav-tabs-modern .nav-link {
        border: none;
        color: var(--sap-secondary);
        font-weight: 700;
        padding: 1rem 2rem;
        position: relative;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.95rem;
        background: transparent;
        border-radius: 0.75rem 0.75rem 0 0;
    }

    .nav-tabs-modern .nav-link:hover {
        color: var(--sap-primary);
        background-color: var(--sap-gray-50);
    }

    .nav-tabs-modern .nav-link.active {
        color: var(--sap-primary);
        background: transparent;
    }

    .nav-tabs-modern .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 4px;
        background: var(--sap-primary-gradient);
        border-radius: 4px;
    }

    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid var(--sap-gray-100);
        align-items: center;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        width: 160px;
        font-weight: 700;
        color: var(--sap-secondary);
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .photo-monitor {
        width: 140px;
        height: 140px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        cursor: pointer;
        transition: all 0.3s;
    }

    .photo-monitor:hover {
        transform: scale(1.05);
        border-color: var(--sap-primary);
        box-shadow: 0 15px 30px rgba(79, 70, 229, 0.2);
    }

    .completion-bar-wrapper {
        height: 8px;
        background: var(--sap-gray-200);
        border-radius: 10px;
        overflow: hidden;
        margin-top: 5px;
    }

    .completion-bar {
        height: 100%;
        background: var(--sap-primary-gradient);
        border-radius: 10px;
        transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 0 10px var(--sap-primary-light);
    }

    .section-title {
        font-size: 0.85rem;
        font-weight: 800;
        color: var(--sap-dark);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--sap-gray-100);
    }

    /* Modern Input Styles */
    .modern-input {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 8px 15px;
        font-size: 0.9rem;
        background-color: #f8fafc;
        width: 100%;
        transition: all 0.2s;
        color: #1e293b;
    }

    .modern-input:focus {
        background-color: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .input-group > .modern-input {
        width: auto;
        flex: 1 1 auto;
    }

    .datepicker {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' /%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 1rem center !important;
        background-size: 1.1rem !important;
        padding-right: 2.5rem !important;
    }

    .flatpickr-calendar {
        border-radius: 1rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        border: none !important;
    }

    .flatpickr-day.selected {
        background: var(--sap-primary) !important;
        border-color: var(--sap-primary) !important;
    }

    .info-box-edit {
        flex: 1;
        display: flex;
        align-items: center;
    }

    /* Select2 Bootstrap 5 Theme Override */
    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1) !important;
        background-color: #fff !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: #1e293b !important;
        padding-left: 15px !important;
    }

    .required-dot {
        color: #ef4444;
        margin-left: 3px;
        font-weight: bold;
    }

    .table-premium thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        border-top: none;
        padding: 15px 12px;
    }

    .table-premium tbody td {
        padding: 15px 12px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
    }

    .badge-status {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
    }

    .bg-soft-success {
        background-color: #dcfce7;
        color: #15803d;
    }

    .bg-soft-warning {
        background-color: #fef9c3;
        color: #854d0e;
    }

    .bg-soft-danger {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .bg-soft-primary {
        background-color: #e0e7ff;
        color: #4338ca;
    }

    .btn-action-view {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
        text-decoration: none !important;
    }

    .btn-action-view:hover {
        transform: scale(1.1);
        background: #3b82f6;
        color: #fff !important;
    }

    .btn-indigo {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: #fff;
        border: none;
    }

    .btn-indigo:hover {
        background: #4338ca;
        color: #fff;
        transform: translateY(-1px);
    }

    .sticky-save-bar {
        position: sticky;
        bottom: 1.5rem;
        z-index: 100;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        border-radius: 50px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        padding: 12px 24px;
        margin-top: 2.5rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .riwayat-sub-nav .nav-link {
        padding: 0.6rem 1.2rem;
        font-size: 0.8rem;
        border-radius: 50px;
        margin-bottom: 0.5rem;
    }

    .riwayat-sub-nav .nav-link.active {
        background: var(--sap-primary-gradient) !important;
        color: #fff !important;
        box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        transform: translateY(-2px);
    }

    .extra-small {
        font-size: 0.75rem;
    }

    .italic {
        font-style: italic;
    }
</style>

<div class="container-fluid py-4">
    <!-- === HEADER === -->
    <div class="row mb-4">
        <div class="col-12 d-md-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">Data Saya</h2>
                <p class="text-muted small mb-0">Kelola profil, kepegawaian, dan riwayat profesional Anda secara
                    mandiri.</p>
            </div>
            <div class="text-end mt-3 mt-md-0" style="min-width: 220px;" data-bs-toggle="tooltip"
                data-bs-placement="bottom" title="<?php echo htmlspecialchars($tooltip_text); ?>">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small fw-bold text-muted">Profil Selesai</span>
                    <span class="small fw-bold text-primary"><?php echo $completion; ?>%</span>
                </div>
                <div class="completion-bar-wrapper">
                    <div class="completion-bar" style="width: <?php echo $completion; ?>%;"></div>
                </div>
                <p class="text-muted mb-0" style="font-size: 0.65rem; margin-top: 4px;">* Progres mencakup Profil,
                    Kepegawaian, & Riwayat Utama.</p>
            </div>
        </div>
    </div>

    <!-- === TABS NAVIGATION === -->
    <ul class="nav nav-tabs nav-tabs-modern" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-profil-tab" data-bs-toggle="tab" data-bs-target="#pills-profil"
                type="button" role="tab" aria-controls="pills-profil" aria-selected="true">
                <i class="las la-user-edit me-2"></i> Profil & Kepegawaian
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-riwayat-tab" data-bs-toggle="tab" data-bs-target="#pills-riwayat"
                type="button" role="tab" aria-controls="pills-riwayat" aria-selected="false">
                <i class="las la-history me-2"></i> Riwayat Kepegawaian
            </button>
        </li>
    </ul>

    <div class="tab-content" id="pills-tabContent">
        <!-- TAB 1: PROFIL & KEPEGAWAIAN -->
        <div class="tab-pane fade show active" id="pills-profil" role="tabpanel" aria-labelledby="pills-profil-tab">
            <form id="formDataSaya" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?php echo $id_pegawai; ?>">
                <input type="hidden" name="foto_lama" value="<?php echo $pegawai['foto']; ?>">

                <div class="card-modern shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-3 text-center border-end">
                                <label for="foto_input" style="cursor: pointer;">
                                    <img src="<?php echo $src_foto; ?>" class="photo-monitor mb-3" id="preview-foto"
                                        alt="Foto" title="Klik untuk ubah foto">
                                </label>
                                <input type="file" name="foto" id="foto_input" class="d-none" accept="image/*">
                                <h6 class="fw-bold mb-1 text-dark">
                                    <?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?></h6>
                                <p class="text-muted extra-small mb-2">NIP.
                                    <?php echo htmlspecialchars($pegawai['nip'] ?? ''); ?></p>
                                <span class="badge bg-soft-success rounded-pill px-3 py-1 extra-small">
                                    <i class="las la-check-circle me-1"></i> Terverifikasi
                                </span>
                            </div>
                            <div class="col-md-9">
                                <div class="section-title ps-3"><i class="las la-id-card me-2"></i> Identitas Utama
                                </div>
                                <div class="row g-3 ps-3">
                                    <div class="col-md-12">
                                        <div class="info-row">
                                            <div class="info-label">Nama Lengkap <span class="required-dot">*</span>
                                            </div>
                                            <div class="info-box-edit">
                                                <div class="input-group">
                                                    <input type="text" name="gelar_depan"
                                                        class="form-control modern-input" style="max-width: 85px;"
                                                        placeholder="Dr."
                                                        value="<?php echo htmlspecialchars($pegawai['gelar_depan'] ?? ''); ?>">
                                                    <input type="text" name="nm_pegawai"
                                                        class="form-control modern-input fw-bold text-primary"
                                                        style="flex: 2;"
                                                        placeholder="Nama Lengkap"
                                                        value="<?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?>"
                                                        required>
                                                    <input type="text" name="gelar_belakang"
                                                        class="form-control modern-input" style="max-width: 110px;"
                                                        placeholder="M.Pd"
                                                        value="<?php echo htmlspecialchars($pegawai['gelar_belakang'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <div class="info-label">Jabatan</div>
                                            <div class="info-box-edit">
                                                <input type="text" name="jabatan" class="modern-input"
                                                    value="<?php echo htmlspecialchars($pegawai['jabatan'] ?? ''); ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <div class="info-label">Unit Kerja</div>
                                            <div class="info-box-edit">
                                                <input type="text" name="unit_kerja" class="modern-input"
                                                    value="<?php echo htmlspecialchars(($pegawai['unit_kerja'] ?? '') ?: 'SMP Negeri 171 Jakarta'); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    <!-- Biodata Lengkap -->
                    <div class="col-lg-12">
                        <div class="card-modern shadow-sm border-0">
                            <div class="card-body p-4">
                                <div class="section-title"><i class="las la-user me-2"></i> Biodata Lengkap</div>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="info-label mb-2">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" class="form-select modern-input">
                                            <option value="L" <?php echo ($pegawai['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?php echo ($pegawai['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="info-label mb-2">Agama</label>
                                        <select name="agama" class="form-select modern-input select2-edit">
                                            <option value="">- Pilih -</option>
                                            <?php foreach (['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'] as $a): ?>
                                                <option value="<?php echo $a; ?>" <?php echo ($pegawai['agama'] == $a) ? 'selected' : ''; ?>><?php echo $a; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="info-label mb-2">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['tempat_lahir'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="info-label mb-2">Tanggal Lahir</label>
                                        <input type="text" name="tgl_lahir" class="form-control modern-input datepicker"
                                            value="<?php echo ($pegawai['tgl_lahir'] != '0000-00-00') ? $pegawai['tgl_lahir'] : ''; ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="info-label mb-2">NIK (KTP)</label>
                                        <input type="text" name="nik" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['nik'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="info-label mb-2">No. KK</label>
                                        <input type="text" name="no_kk" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['no_kk'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="info-label mb-2">NUPTK</label>
                                        <input type="text" name="nuptk" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['nuptk'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="info-label mb-2">Nama Ibu Kandung</label>
                                        <input type="text" name="nama_ibu" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['nama_ibu'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="info-label mb-2">Nama Pasangan</label>
                                        <input type="text" name="nama_pasangan" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['nama_pasangan'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="info-label mb-2">Hobby</label>
                                        <input type="text" name="hobby" class="modern-input"
                                            placeholder="Membaca, Olahraga..."
                                            value="<?php echo htmlspecialchars($pegawai['hobby'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="section-title mt-5"><i class="las la-briefcase me-2"></i> Kepegawaian &
                                    Dokumen</div>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="info-label mb-2">Status</label>
                                        <select name="status_pegawai" class="form-select modern-input select2-edit">
                                            <option value="PNS" <?php echo ($pegawai['status_pegawai'] == 'PNS') ? 'selected' : ''; ?>>PNS</option>
                                            <option value="PPPK" <?php echo ($pegawai['status_pegawai'] == 'PPPK') ? 'selected' : ''; ?>>PPPK</option>
                                            <option value="Honorer" <?php echo ($pegawai['status_pegawai'] == 'Honorer') ? 'selected' : ''; ?>>Honorer</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="info-label mb-2">Golongan</label>
                                        <select name="golongan" class="form-select modern-input select2-edit">
                                            <?php foreach (['I/a', 'I/b', 'I/c', 'I/d', 'II/a', 'II/b', 'II/c', 'II/d', 'III/a', 'III/b', 'III/c', 'III/d', 'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e', 'V', 'IX', 'Tidak Ada'] as $g): ?>
                                                <option value="<?php echo $g; ?>" <?php echo ($pegawai['golongan'] == $g) ? 'selected' : ''; ?>><?php echo $g; ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="info-label mb-2">Pendidikan Terakhir</label>
                                        <div class="input-group">
                                            <input type="text" name="pendidikan" class="form-control modern-input"
                                                placeholder="Jenjang"
                                                value="<?php echo htmlspecialchars($pegawai['pendidikan'] ?? ''); ?>">
                                            <input type="text" name="tgl_lulus"
                                                class="form-control modern-input datepicker" placeholder="Tgl Lulus"
                                                value="<?php echo ($pegawai['tgl_lulus'] != '0000-00-00') ? $pegawai['tgl_lulus'] : ''; ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <label class="info-label mb-2">TMT Pangkat</label>
                                        <input type="text" name="tmt_pangkat"
                                            class="form-control modern-input datepicker"
                                            value="<?php echo ($pegawai['tmt_pangkat'] != '0000-00-00') ? $pegawai['tmt_pangkat'] : ''; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="info-label mb-2">TMT Golongan</label>
                                        <input type="text" name="tmt_golongan"
                                            class="form-control modern-input datepicker"
                                            value="<?php echo ($pegawai['tmt_golongan'] != '0000-00-00') ? $pegawai['tmt_golongan'] : ''; ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="info-label mb-2">TMT Jabatan</label>
                                        <input type="text" name="tmt_jabatan"
                                            class="form-control modern-input datepicker"
                                            value="<?php echo ($pegawai['tmt_jabatan'] != '0000-00-00') ? $pegawai['tmt_jabatan'] : ''; ?>">
                                    </div>

                                    <div class="col-md-6">
                                        <label class="info-label mb-2">TMT CPNS</label>
                                        <input type="text" name="tmt_cpns" class="form-control modern-input datepicker"
                                            value="<?php echo ($pegawai['tmt_cpns'] != '0000-00-00') ? $pegawai['tmt_cpns'] : ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="info-label mb-2">TMT PNS</label>
                                        <input type="text" name="tmt_pns" class="form-control modern-input datepicker"
                                            value="<?php echo ($pegawai['tmt_pns'] != '0000-00-00') ? $pegawai['tmt_pns'] : ''; ?>">
                                    </div>

                                    <div class="col-md-4">
                                        <label class="info-label mb-2">Gaji Pokok</label>
                                        <div class="input-group input-group-modern">
                                            <span class="input-group-text-modern">Rp</span>
                                            <input type="text" name="gaji_pokok" id="gaji_pokok_mask"
                                                class="modern-input fw-bold text-success text-end"
                                                value="<?php echo number_format($pegawai['gaji_pokok'] ?: 0, 0, ',', '.'); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="info-label mb-2">Masa Kerja (Thn/Bln)</label>
                                        <div class="input-group input-group-modern">
                                            <input type="number" name="masa_kerja_thn"
                                                class="form-control modern-input text-center"
                                                value="<?php echo $pegawai['masa_kerja_thn']; ?>">
                                            <span class="input-group-text-modern" style="border-radius:0;">Thn</span>
                                            <input type="number" name="masa_kerja_bln"
                                                class="form-control modern-input text-center"
                                                value="<?php echo $pegawai['masa_kerja_bln']; ?>">
                                            <span class="input-group-text-modern"
                                                style="border-radius: 0 0.75rem 0.75rem 0;">Bln</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="info-label mb-2">NPWP</label>
                                        <input type="text" name="npwp" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['npwp'] ?? ''); ?>">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="info-label mb-2">No. Karpeg</label>
                                        <input type="text" name="no_karpeg" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['no_karpeg'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="info-label mb-2">No. Taspen</label>
                                        <input type="text" name="no_taspen" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['no_taspen'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="info-label mb-2">No. BPJS</label>
                                        <input type="text" name="no_bpjs" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['no_bpjs'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="info-label mb-2">No. Karis/Karsu</label>
                                        <input type="text" name="no_karis_karsu" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['no_karis_karsu'] ?? ''); ?>">
                                    </div>
                                </div>

                                <div class="section-title mt-5"><i class="las la-map-marker me-2"></i> Kontak & Alamat
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="info-label mb-2">No. HP / WhatsApp</label>
                                        <input type="text" name="no_hp" class="modern-input fw-bold text-primary"
                                            value="<?php echo htmlspecialchars($pegawai['no_hp'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="info-label mb-2">Email</label>
                                        <input type="email" name="email" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['email'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="info-label mb-2">Alamat Lengkap</label>
                                        <textarea name="alamat" class="modern-input"
                                            rows="3"><?php echo htmlspecialchars($pegawai['alamat'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="info-label mb-2">RT / RW</label>
                                        <div class="input-group">
                                            <input type="text" name="rt" class="form-control modern-input text-center"
                                                placeholder="RT"
                                                value="<?php echo htmlspecialchars($pegawai['rt'] ?? ''); ?>">
                                            <input type="text" name="rw" class="form-control modern-input text-center"
                                                placeholder="RW"
                                                value="<?php echo htmlspecialchars($pegawai['rw'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="info-label mb-2">Kelurahan</label>
                                        <input type="text" name="kelurahan" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['kelurahan'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-5">
                                        <label class="info-label mb-2">Kecamatan</label>
                                        <input type="text" name="kecamatan" class="modern-input"
                                            value="<?php echo htmlspecialchars($pegawai['kecamatan'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sticky-save-bar d-flex justify-content-between align-items-center">
                    <div class="small text-muted d-none d-md-block">
                        <i class="las la-info-circle me-1"></i> Data Anda akan diverifikasi oleh Admin kepegawaian.
                    </div>
                    <button type="submit" class="btn btn-indigo btn-rounded px-5 py-2 shadow-sm">
                        <i class="las la-save me-2"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>

        <!-- TAB 2: RIWAYAT KEPEGAWAIAN -->
        <div class="tab-pane fade" id="pills-riwayat" role="tabpanel" aria-labelledby="pills-riwayat-tab">
            <!-- Horizontal Sub-Tabs for Categories -->
            <div class="nav nav-tabs nav-tabs-modern riwayat-sub-nav d-flex flex-wrap mb-4" id="riwayat-tabs"
                role="tablist">
                <?php
                $riwayat_cats = [
                    'Pangkat' => 'las la-layer-group',
                    'Jabatan' => 'las la-briefcase',
                    'Pendidikan' => 'las la-graduation-cap',
                    'Sertifikasi' => 'las la-certificate',
                    'KGB' => 'las la-money-bill-trend-up',
                    'Diklat' => 'las la-chalkboard-teacher',
                    'Seminar' => 'las la-users-rectangle',
                    'Organisasi' => 'las la-sitemap',
                    'Penghargaan' => 'las la-award'
                ];
                $first = true;
                foreach ($riwayat_cats as $cat => $icon):
                    $active = $first ? 'active' : '';
                    echo '<button class="nav-link ' . $active . ' me-2 mb-2" data-bs-toggle="tab" data-bs-target="#tab-' . str_replace(' ', '-', $cat) . '" type="button" role="tab">
                            <i class="' . $icon . ' me-2"></i> ' . $cat . '
                          </button>';
                    $first = false;
                endforeach;
                ?>
            </div>

            <div class="tab-content" id="riwayat-tabs-content">
                <?php
                $first = true;
                foreach ($riwayat_cats as $cat => $icon):
                    $active = $first ? 'show active' : '';
                    ?>
                    <div class="tab-pane fade <?php echo $active; ?>" id="tab-<?php echo str_replace(' ', '-', $cat); ?>"
                        role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="<?php echo $icon; ?> me-2"></i> Daftar Riwayat
                                <?php echo $cat; ?></h6>
                            <button type="button" class="btn btn-indigo btn-rounded btn-sm px-3 btn-tambah-riwayat"
                                data-category="<?php echo $cat; ?>">
                                <i class="las la-plus me-1"></i> Tambah <?php echo $cat; ?>
                            </button>
                        </div>
                        <div class="card-modern shadow-sm border-0">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-premium align-middle nowrap mb-0" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="50">No</th>
                                                <th>Keterangan / Institusi</th>
                                                <th>No. SK / Ijazah</th>
                                                <th class="text-center">TMT</th>
                                                <th class="text-end">Gaji Pokok</th>
                                                <th class="text-center">Masa Kerja</th>
                                                <th class="text-center" width="80">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $has_cat_data = false;
                                            $no = 1;
                                            foreach ($hist_rows as $r):
                                                if ($r['kategori'] == $cat):
                                                    $has_cat_data = true;
                                                    $has_file = !empty($r['file_lampiran']);
                                                    ?>
                                                    <tr>
                                                        <td class="text-center text-muted fw-bold"><?php echo $no++; ?></td>
                                                        <td><?php echo htmlspecialchars(($r['institusi'] ?: ($r['deskripsi'] ?: '-'))); ?>
                                                        </td>
                                                        <td><code><?php echo htmlspecialchars(($r['no_sk'] ?: ($r['no_ijazah'] ?: '-'))); ?></code>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo (!empty($r['tmt']) && $r['tmt'] != '0000-00-00') ? date('d/m/Y', strtotime($r['tmt'])) : '-'; ?>
                                                        </td>
                                                        <td class="text-end fw-bold text-success">
                                                            <?php echo ($r['gaji_pokok'] > 0) ? 'Rp ' . number_format($r['gaji_pokok'], 0, ',', '.') : '-'; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php echo ($r['masa_kerja_thn'] ?: 0) . 'th ' . ($r['masa_kerja_bln'] ?: 0) . 'bln'; ?>
                                                        </td>
                                                        <td class="text-center">
                                                            <?php if ($has_file): ?>
                                                                <a href="../file/datakepegawaian/<?php echo $r['file_lampiran']; ?>"
                                                                    target="_blank" class="btn-action-view text-primary border"><i
                                                                        class="las la-external-link-alt fs-5"></i></a>
                                                            <?php else: ?>
                                                                <button class="btn-action-view text-muted border opacity-50" disabled><i
                                                                        class="las la-file-excel fs-5"></i></button>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php
                                                endif;
                                            endforeach;
                                            if (!$has_cat_data):
                                                ?>
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="las la-folder-open fa-3x mb-3 opacity-25"></i>
                                                            <p class="mb-0 fw-bold">Belum ada riwayat <?php echo $cat; ?>.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                    $first = false;
                endforeach;
                ?>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        // Initialize Bootstrap Tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        // Initialize Select2
        $('.select2-edit').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        // Robust Flatpickr Initialization
        const initDatepicker = (selector = ".datepicker") => {
            if (typeof flatpickr !== 'undefined') {
                $(selector).flatpickr({
                    altInput: true,
                    altFormat: "d-m-Y",
                    dateFormat: "Y-m-d",
                    locale: "id",
                    allowInput: true,
                    disableMobile: "auto",
                    clickOpens: true,
                    monthSelectorType: "static",
                    animate: true
                });
            }
        };
        initDatepicker();

        // Currency Masking
        $('#gaji_pokok_mask, #riwayat_gaji_pokok_mask').on('input', function () {
            let val = $(this).val().replace(/[^0-9]/g, '');
            if (val === '') val = '0';
            $(this).val(new Intl.NumberFormat('id-ID').format(val));
            // Sync to hidden input if exists
            const hiddenId = $(this).attr('id').replace('_mask', '');
            $('#' + hiddenId).val(val);
        });

        // Photo Preview
        $('#foto_input').change(function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#preview-foto').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        // Main Profile Submit
        $('#formDataSaya').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpan');

            const btn = $(this).find('button[type="submit"]');
            const oldHtml = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner fa-spin me-2"></i> Menyimpan...');

            $.ajax({
                url: 'proses_pegawai.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        if (typeof showToast === 'function') showToast(res.message, 'success');
                        else alert(res.message);
                        setTimeout(() => { location.reload(); }, 1500);
                    } else {
                        if (typeof showToast === 'function') showToast(res.message, 'error');
                        else alert(res.message);
                        btn.prop('disabled', false).html(oldHtml);
                    }
                },
                error: function () {
                    alert('Gagal menghubungi server.');
                    btn.prop('disabled', false).html(oldHtml);
                }
            });
        });

        // Riwayat Management Logic
        const loadRiwayat = (category = null) => {
            const targetCat = category || $('#riwayat-tabs button.active').data('category');
            const tabId = '#tab-' + targetCat.replace(/ /g, '-');
            const tbody = $(tabId + ' tbody');

            tbody.html('<tr><td colspan="7" class="text-center py-4"><div class="spinner-border text-primary spinner-border-sm me-2"></div> Memuat...</td></tr>');

            $.get('proses_pegawai.php', { action: 'muatRiwayat', pegawai_id: '<?php echo $id_pegawai; ?>', kategori: targetCat }, function (res) {
                if (res.status === 'success') {
                    let html = '';
                    if (res.data && res.data.length > 0) {
                        res.data.forEach((r, index) => {
                            const fileBtn = r.file_lampiran ? `<a href="../file/datakepegawaian/${r.file_lampiran}" target="_blank" class="btn btn-sm btn-light border p-1 rounded shadow-sm"><i class="las la-file-pdf text-danger me-1"></i> SK</a>` : '<span class="text-muted extra-small">No File</span>';

                            html += `
                                <tr>
                                    <td class="text-center text-muted fw-bold">${index + 1}</td>
                                    <td>${r.institusi || r.deskripsi || '-'}</td>
                                    <td><code>${r.no_sk || r.no_ijazah || '-'}</code></td>
                                    <td class="text-center">${r.tmt || '-'}</td>
                                    <td class="text-end fw-bold text-success">${r.gaji_pokok > 0 ? 'Rp ' + new Intl.NumberFormat('id-ID').format(r.gaji_pokok) : '-'}</td>
                                    <td class="text-center">${(r.masa_kerja_thn || 0) + 'th ' + (r.masa_kerja_bln || 0) + 'bln'}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">
                                            ${fileBtn}
                                            <button class="btn btn-sm btn-light border edit-riwayat" data-id="${r.id}"><i class="las la-edit text-warning"></i></button>
                                            <button class="btn btn-sm btn-light border btn-hapus-riwayat" data-id="${r.id}"><i class="las la-trash text-danger"></i></button>
                                        </div>
                                    </td>
                                </tr>`;
                        });
                    } else {
                        html = `<tr><td colspan="7" class="text-center py-5"><div class="text-muted"><i class="las la-folder-open fa-3x mb-3 opacity-25"></i><p class="mb-0 fw-bold">Belum ada riwayat ${targetCat}.</p></div></td></tr>`;
                    }
                    tbody.html(html);
                }
            }, 'json');
        };

        // Trigger load on tab switch
        $('#pills-riwayat-tab').on('shown.bs.tab', function () {
            loadRiwayat();
        });

        $('#riwayat-tabs button').on('shown.bs.tab', function () {
            loadRiwayat($(this).data('category'));
        });

        $('.btn-tambah-riwayat').click(function () {
            const cat = $(this).data('category');
            $('#modalFormRiwayatLabel').text('Tambah Riwayat ' + cat);
            $('#formRiwayat')[0].reset();
            $('#id_riwayat').val('');
            $('#riwayat_kategori').val(cat);
            generateDynamicFields(cat);
            $('#modalFormRiwayat').modal('show');
        });

        $(document).on('click', '.edit-riwayat', function () {
            const id = $(this).data('id');
            $.get('proses_pegawai.php', { action: 'ambilRiwayat', id: id }, function (res) {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#modalFormRiwayatLabel').text('Edit Riwayat ' + d.kategori);
                    $('#id_riwayat').val(d.id);
                    $('#riwayat_kategori').val(d.kategori);
                    generateDynamicFields(d.kategori, d);
                    $('#modalFormRiwayat').modal('show');
                }
            }, 'json');
        });

        function generateDynamicFields(cat, data = null) {
            // Helper to sanitize dates
            const sDate = (d) => (d && d !== '0000-00-00') ? d : '';
            
            let fields = '';
            const common = `
                <div class="col-12">
                    <label class="modern-label">Keterangan / Deskripsi</label>
                    <input type="text" name="deskripsi" class="form-control modern-input" required value="${data?.deskripsi || ''}">
                </div>
                <div class="col-md-6">
                    <label class="modern-label">No. SK / Ijazah</label>
                    <input type="text" name="no_sk" class="form-control modern-input" value="${data?.no_sk || ''}">
                </div>
                <div class="col-md-6">
                    <label class="modern-label">Tgl. SK / Ijazah</label>
                    <input type="text" name="tgl_sk" class="form-control modern-input datepicker-modal" value="${sDate(data?.tgl_sk)}">
                </div>
                <div class="col-md-6">
                    <label class="modern-label">TMT</label>
                    <input type="text" name="tmt" class="form-control modern-input datepicker-modal" value="${sDate(data?.tmt)}">
                </div>
            `;

            if (cat === 'Pendidikan') {
                fields = `
                    <div class="col-12">
                        <label class="modern-label">Nama Institusi / Sekolah</label>
                        <input type="text" name="institusi" class="form-control modern-input" required value="${data?.institusi || ''}">
                    </div>
                    <div class="col-md-8">
                        <label class="modern-label">Jurusan / Prodi</label>
                        <input type="text" name="jurusan" class="form-control modern-input" value="${data?.jurusan || ''}">
                    </div>
                    <div class="col-md-4">
                        <label class="modern-label">Tgl. Lulus</label>
                        <input type="text" name="tmt" class="form-control modern-input datepicker-modal" value="${sDate(data?.tmt)}">
                    </div>
                    <div class="col-12">
                        <label class="modern-label">No. Ijazah</label>
                        <input type="text" name="no_ijazah" class="form-control modern-input" value="${data?.no_ijazah || ''}">
                    </div>
                `;
            } else if (cat === 'Pangkat' || cat === 'KGB') {
                fields = common + `
                    <div class="col-md-6">
                        <label class="modern-label">Gaji Pokok (Angka)</label>
                        <input type="number" name="gaji_pokok" class="form-control modern-input" value="${data?.gaji_pokok || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="modern-label">Masa Kerja Thn</label>
                        <input type="number" name="masa_kerja_thn" class="form-control modern-input" value="${data?.masa_kerja_thn || ''}">
                    </div>
                    <div class="col-md-3">
                        <label class="modern-label">Masa Kerja Bln</label>
                        <input type="number" name="masa_kerja_bln" class="form-control modern-input" value="${data?.masa_kerja_bln || ''}">
                    </div>
                `;
            } else {
                fields = common + `
                    <div class="col-12">
                        <label class="modern-label">Institusi / Tempat</label>
                        <input type="text" name="institusi" class="form-control modern-input" value="${data?.institusi || ''}">
                    </div>
                `;
            }

            $('#extra_fields_container').html(fields);
            initDatepicker('.datepicker-modal');
        }

        $('#formRiwayat').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpanRiwayat');
            const btn = $(this).find('button[type="submit"]');
            const oldHtml = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner fa-spin me-2"></i> Menyimpan...');

            $.ajax({
                url: 'proses_pegawai.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        if (typeof showToast === 'function') showToast(res.message, 'success');
                        else alert(res.message);
                        $('#modalFormRiwayat').modal('hide');
                        loadRiwayat();
                    } else {
                        alert(res.message);
                    }
                    btn.prop('disabled', false).html(oldHtml);
                },
                error: function () {
                    alert('Gagal menghubungi server.');
                    btn.prop('disabled', false).html(oldHtml);
                }
            });
        });

        $(document).on('click', '.btn-hapus-riwayat', function () {
            if (confirm('Yakin ingin menghapus riwayat ini?')) {
                const id = $(this).data('id');
                $.post('proses_pegawai.php', { action: 'hapusRiwayat', id: id }, function (res) {
                    if (res.status === 'success') {
                        loadRiwayat();
                    } else {
                        alert(res.message);
                    }
                }, 'json');
            }
        });

        // Load initial data
        loadRiwayat('Pangkat');
    });
</script>

<!-- === MODAL: FORM RIWAYAT === -->
<div class="modal fade" id="modalFormRiwayat" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem;">
            <div class="modal-header border-0 pt-4 px-4 pb-0">
                <h5 class="modal-title fw-bold" id="modalFormRiwayatLabel">Form Riwayat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formRiwayat" enctype="multipart/form-data">
                    <input type="hidden" name="id_riwayat" id="id_riwayat">
                    <input type="hidden" name="pegawai_id_riwayat" value="<?php echo $id_pegawai; ?>">
                    <input type="hidden" name="kategori" id="riwayat_kategori">

                    <div class="row g-3" id="extra_fields_container">
                        <!-- Dynamic fields injected here -->
                    </div>

                    <div class="col-12 mt-3">
                        <label class="modern-label">Upload Lampiran (PDF/JPG)</label>
                        <input type="file" name="file_lampiran" class="form-control modern-input">
                        <div class="extra-small text-muted mt-1">* Max 2MB.</div>
                    </div>

                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-light btn-rounded px-4 me-2"
                            data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-indigo btn-rounded px-4 shadow-sm">Simpan Riwayat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>