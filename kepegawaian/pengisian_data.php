<?php
/**
 * Pengisian Data Mandiri - Guru Portal
 * Centralized Form for Profil, Kepegawaian, and Riwayat
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$id_pegawai = $_SESSION['id'] ?? 0;
$nik = $_SESSION['nik'] ?? '';

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$stmt->bind_param("i", $id_pegawai);
$stmt->execute();
$pegawai = $stmt->get_result()->fetch_assoc();

if (!$pegawai) {
    echo "<div class='alert alert-danger'>Data pegawai tidak ditemukan.</div>";
    return;
}

$foto_path = !empty($pegawai['foto']) ? '../file/datakepegawaian/' . $pegawai['foto'] : '../images/default.png';
?>

<!-- === EXTERNAL ASSETS === -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

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

    .page-title {
        font-weight: 800;
        color: var(--sap-dark);
        letter-spacing: -0.025em;
    }

    .modern-card {
        background: #fff;
        border-radius: var(--sap-border-radius);
        border: none;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
        overflow: hidden;
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

    .modern-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--sap-secondary);
        margin-bottom: 0.4rem;
        display: block;
    }

    .modern-input,
    .form-select.modern-input {
        border-radius: 0.75rem;
        border: 1px solid var(--sap-gray-200);
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        background-color: var(--sap-gray-50);
        transition: all 0.2s;
    }

    .modern-input:focus {
        background-color: #fff;
        border-color: var(--sap-primary);
        box-shadow: 0 0 0 4px var(--sap-primary-light);
        outline: none;
    }

    .photo-preview-wrapper {
        position: relative;
        width: 150px;
        height: 150px;
        margin: 0 auto;
    }

    .photo-preview {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .photo-upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--sap-primary-gradient);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .section-title {
        font-size: 0.9rem;
        font-weight: 800;
        color: var(--sap-dark);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid var(--sap-gray-100);
    }

    /* Table Styles for Riwayat */
    .table-modern thead th {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--sap-secondary);
        padding: 1rem;
        background-color: var(--sap-gray-50);
        border-bottom: 2px solid var(--sap-gray-100) !important;
    }

    .table-modern tbody td {
        padding: 1rem;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .btn-rounded {
        border-radius: 50px;
    }

    .btn-indigo {
        background: var(--sap-primary-gradient);
        color: #fff;
        border: none;
    }

    .btn-indigo:hover {
        background: #4338ca;
        color: #fff;
        transform: translateY(-1px);
    }

    /* Category Badges */
    .bg-pangkat {
        background-color: #3b82f6 !important;
        color: #fff;
    }

    .bg-jabatan {
        background-color: #10b981 !important;
        color: #fff;
    }

    .bg-pendidikan {
        background-color: #f59e0b !important;
        color: #fff;
    }

    .bg-kgb {
        background-color: #ef4444 !important;
        color: #fff;
    }

    .bg-indigo {
        background-color: #6366f1 !important;
        color: #fff;
    }

    /* Custom Select2 Modern Style */
    .select2-container--bootstrap-5 {
        margin-bottom: 0;
    }

    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.75rem !important;
        border: 1px solid var(--sap-gray-200) !important;
        min-height: calc(1.5em + 1.2rem + 2px) !important;
        background-color: var(--sap-gray-50) !important;
        transition: all 0.2s !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--bootstrap-5.select2-container--focus .select2-selection {
        background-color: #fff !important;
        border-color: var(--sap-primary) !important;
        box-shadow: 0 0 0 4px var(--sap-primary-light) !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        color: var(--sap-dark) !important;
        padding-left: 1rem !important;
        font-size: 0.9rem !important;
    }

    /* Flatpickr Modern Customization */
    .flatpickr-calendar {
        border-radius: 1rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        border: none !important;
    }

    .flatpickr-day.selected {
        background: var(--sap-primary) !important;
        border-color: var(--sap-primary) !important;
    }

    .datepicker {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' /%3E%3C/svg%3E") !important;
        background-repeat: no-repeat !important;
        background-position: right 1rem center !important;
        background-size: 1.1rem !important;
        padding-right: 2.5rem !important;
    }
</style>

<div class="row">
    <!-- === HEADER === -->
    <div class="col-12 d-md-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title mb-1">Pengisian Data Mandiri</h2>
            <p class="text-muted small mb-0">Lengkapi profil, data kepegawaian, dan riwayat Anda untuk sinkronisasi
                sistem.</p>
        </div>
    </div>

    <!-- === TABS NAVIGATION === -->
    <div class="col-12">
        <ul class="nav nav-tabs nav-tabs-modern" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-profil-tab" data-bs-toggle="tab"
                    data-bs-target="#pills-profil" type="button" role="tab" aria-controls="pills-profil"
                    aria-selected="true">
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
    </div>

    <!-- === TABS CONTENT === -->
    <div class="tab-content" id="pills-tabContent">
        <!-- TAB 1: PROFIL & KEPEGAWAIAN -->
        <div class="tab-pane fade show active" id="pills-profil" role="tabpanel" aria-labelledby="pills-profil-tab">
            <div class="modern-card p-4">
                <form id="formProfil" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="id" value="<?php echo $id_pegawai; ?>">
                    <input type="hidden" name="foto_lama" value="<?php echo $pegawai['foto']; ?>">
                    <input type="hidden" name="nip" value="<?php echo $pegawai['nip']; ?>">
                    <input type="hidden" name="nrk" value="<?php echo $pegawai['nrk']; ?>">

                    <div class="row g-4">
                        <!-- Photo Section -->
                        <div class="col-md-3 text-center border-end">
                            <div class="photo-preview-wrapper mb-3">
                                <img id="preview-foto" src="<?php echo $foto_path; ?>" class="photo-preview">
                                <label for="foto_input" class="photo-upload-btn">
                                    <i class="las la-camera"></i>
                                </label>
                                <input type="file" id="foto_input" name="foto" class="d-none" accept="image/*">
                            </div>
                            <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?></h6>
                            <p class="text-muted small">NIP. <?php echo htmlspecialchars($pegawai['nip'] ?? ''); ?></p>
                        </div>

                        <!-- Form Section -->
                        <div class="col-md-9">
                            <!-- IDENTITAS PRIBADI -->
                            <div class="section-title"><i class="las la-id-card me-2"></i> Identitas Pribadi</div>
                            <div class="row g-3 mb-5">
                                <div class="col-md-6">
                                    <label class="modern-label">Nama Lengkap (*)</label>
                                    <input type="text" name="nm_pegawai" class="form-control modern-input" required
                                        value="<?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?>">
                                    <div class="invalid-feedback">Nama lengkap wajib diisi.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">Jenis Kelamin (*)</label>
                                    <select name="jenis_kelamin" class="form-select modern-input" required>
                                        <option value="L" <?php echo ($pegawai['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                        <option value="P" <?php echo ($pegawai['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                    </select>
                                    <div class="invalid-feedback">Pilih jenis kelamin.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">Agama (*)</label>
                                    <select name="agama" class="form-select modern-input" required>
                                        <option value="">- Pilih -</option>
                                        <?php
                                        $agamas = ['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];
                                        foreach ($agamas as $a):
                                            $sel = ($pegawai['agama'] == $a) ? 'selected' : '';
                                            echo "<option value='$a' $sel>$a</option>";
                                        endforeach;
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">Pilih agama.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Tempat Lahir (*)</label>
                                    <input type="text" name="tempat_lahir" class="form-control modern-input" required
                                        value="<?php echo htmlspecialchars($pegawai['tempat_lahir'] ?? ''); ?>">
                                    <div class="invalid-feedback">Tempat lahir wajib diisi.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Tanggal Lahir (*)</label>
                                    <input type="text" name="tgl_lahir" class="form-control modern-input datepicker"
                                        required value="<?php echo $pegawai['tgl_lahir']; ?>">
                                    <div class="invalid-feedback">Tanggal lahir wajib diisi.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">NUPTK (16 Digit)</label>
                                    <input type="text" name="nuptk" class="form-control modern-input" pattern="\d{16}"
                                        placeholder="Contoh: 1234567890123456"
                                        value="<?php echo htmlspecialchars($pegawai['nuptk'] ?? ''); ?>">
                                    <div class="invalid-feedback">NUPTK harus berupa 16 digit angka.</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">NIK (No. KTP - 16 Digit) (*)</label>
                                    <input type="text" name="nik" class="form-control modern-input" required
                                        pattern="\d{16}" placeholder="Contoh: 3171XXXXXXXXXXXX"
                                        value="<?php echo htmlspecialchars($pegawai['nik'] ?? ''); ?>">
                                    <div class="invalid-feedback">NIK wajib diisi (16 digit angka).</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Nomor Kartu Keluarga (16 Digit) (*)</label>
                                    <input type="text" name="no_kk" class="form-control modern-input" required
                                        pattern="\d{16}" placeholder="Contoh: 3171XXXXXXXXXXXX"
                                        value="<?php echo htmlspecialchars($pegawai['no_kk'] ?? ''); ?>">
                                    <div class="invalid-feedback">No. KK wajib diisi (16 digit angka).</div>
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Nama Ibu Kandung</label>
                                    <input type="text" name="nama_ibu" class="form-control modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['nama_ibu'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Nama Suami/Istri</label>
                                    <input type="text" name="nama_pasangan" class="form-control modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['nama_pasangan'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">NPWP</label>
                                    <input type="text" name="npwp" class="form-control modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['npwp'] ?? ''); ?>">
                                </div>
                                <div class="col-md-12">
                                    <label class="modern-label">Hobby / Kegemaran</label>
                                    <input type="text" name="hobby" class="form-control modern-input"
                                        placeholder="Contoh: Membaca, Olahraga, Musik"
                                        value="<?php echo htmlspecialchars($pegawai['hobby'] ?? ''); ?>">
                                </div>
                            </div>

                            <!-- DATA KEPEGAWAIAN -->
                            <div class="section-title"><i class="las la-briefcase me-2"></i> Data Kepegawaian & Dokumen
                            </div>
                            <div class="row g-3 mb-5">
                                <div class="col-md-4">
                                    <label class="modern-label">Status Pegawai</label>
                                    <select name="status_pegawai" class="form-select modern-input">
                                        <option value="PNS" <?php echo ($pegawai['status_pegawai'] == 'PNS') ? 'selected' : ''; ?>>PNS</option>
                                        <option value="PPPK" <?php echo ($pegawai['status_pegawai'] == 'PPPK') ? 'selected' : ''; ?>>PPPK</option>
                                        <option value="Honorer" <?php echo ($pegawai['status_pegawai'] == 'Honorer') ? 'selected' : ''; ?>>Honorer</option>
                                        <option value="Lainnya" <?php echo ($pegawai['status_pegawai'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Pendidikan Terakhir</label>
                                    <input type="text" name="pendidikan" class="form-control modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['pendidikan'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">Golongan</label>
                                    <select name="golongan" class="form-select modern-input">
                                        <option value="">- Pilih -</option>
                                        <?php
                                        $golongans = ['I/a', 'I/b', 'I/c', 'I/d', 'II/a', 'II/b', 'II/c', 'II/d', 'III/a', 'III/b', 'III/c', 'III/d', 'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e', 'V', 'IX'];
                                        foreach ($golongans as $g):
                                            $sel = ($pegawai['golongan'] == $g) ? 'selected' : '';
                                            echo "<option value='$g' $sel>$g</option>";
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">Tgl. Lulus Pendidikan</label>
                                    <input type="text" name="tgl_lulus" class="form-control modern-input datepicker"
                                        value="<?php echo $pegawai['tgl_lulus']; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">TMT Golongan</label>
                                    <input type="text" name="tmt_golongan" class="form-control modern-input datepicker"
                                        value="<?php echo $pegawai['tmt_golongan']; ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">No. Karpeg</label>
                                    <input type="text" name="no_karpeg" class="form-control modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['no_karpeg'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">No. Taspen</label>
                                    <input type="text" name="no_taspen" class="form-control modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['no_taspen'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">No. BPJS</label>
                                    <input type="text" name="no_bpjs" class="form-control modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['no_bpjs'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">No. Karis/Karsu</label>
                                    <input type="text" name="no_karis_karsu" class="form-control modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['no_karis_karsu'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Masa Kerja (Thn / Bln)</label>
                                    <div class="input-group">
                                        <input type="number" name="masa_kerja_thn" class="form-control modern-input"
                                            value="<?php echo $pegawai['masa_kerja_thn']; ?>" placeholder="Thn">
                                        <input type="number" name="masa_kerja_bln" class="form-control modern-input"
                                            value="<?php echo $pegawai['masa_kerja_bln']; ?>" placeholder="Bln">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Gaji Pokok (Angka Saja)</label>
                                    <input type="text" name="gaji_pokok" class="form-control modern-input"
                                        value="<?php echo number_format($pegawai['gaji_pokok'] ?: 0, 0, ',', '.'); ?>">
                                </div>
                            </div>

                            <!-- KONTAK & ALAMAT -->
                            <div class="section-title"><i class="las la-map-marked-alt me-2"></i> Kontak & Alamat</div>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="modern-label">No. HP / WhatsApp (*)</label>
                                    <input type="text" name="no_hp" class="form-control modern-input" required
                                        pattern="\d{10,15}" value="<?php echo htmlspecialchars($pegawai['no_hp'] ?? ''); ?>">
                                    <div class="invalid-feedback">No. HP wajib diisi (10-15 angka).</div>
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Email (*)</label>
                                    <input type="email" name="email" class="form-control modern-input" required
                                        value="<?php echo htmlspecialchars($pegawai['email'] ?? ''); ?>">
                                    <div class="invalid-feedback">Email tidak valid.</div>
                                </div>
                                <div class="col-12">
                                    <label class="modern-label">Alamat Lengkap Domisili (*)</label>
                                    <textarea name="alamat" class="form-control modern-input" required
                                        rows="3"><?php echo htmlspecialchars($pegawai['alamat'] ?? ''); ?></textarea>
                                    <div class="invalid-feedback">Alamat lengkap wajib diisi.</div>
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">RT</label>
                                    <input type="text" name="rt" class="form-control modern-input" placeholder="000"
                                        value="<?php echo htmlspecialchars($pegawai['rt'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">RW</label>
                                    <input type="text" name="rw" class="form-control modern-input" placeholder="000"
                                        value="<?php echo htmlspecialchars($pegawai['rw'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">Kelurahan</label>
                                    <input type="text" name="kelurahan" class="form-control modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['kelurahan'] ?? ''); ?>">
                                </div>
                                <div class="col-md-3">
                                    <label class="modern-label">Kecamatan</label>
                                    <input type="text" name="kecamatan" class="form-control modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['kecamatan'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="mt-5 text-end">
                                <button type="submit" class="btn btn-indigo btn-rounded px-5 py-2 shadow-sm">
                                    <i class="las la-save me-2"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- TAB 2: RIWAYAT KEPEGAWAIAN -->
        <div class="tab-pane fade" id="pills-riwayat" role="tabpanel" aria-labelledby="pills-riwayat-tab">
            <!-- Horizontal Sub-Tabs for Riwayat -->
            <div class="mb-4">
                <div class="nav nav-tabs nav-tabs-modern riwayat-sub-nav d-flex flex-wrap" id="riwayat-tabs"
                    role="tablist" style="max-width: 100%;">
                    <?php
                    $riwayat_cats = [
                        'Pangkat' => 'las la-layer-group',
                        'Kepangkatan' => 'las la-id-card-clip',
                        'Jabatan' => 'las la-briefcase',
                        'Pendidikan' => 'las la-graduation-cap',
                        'Sertifikasi' => 'las la-certificate',
                        'KGB' => 'las la-money-bill-trend-up',
                        'Diklat' => 'las la-chalkboard-teacher',
                        'Seminar' => 'las la-users-rectangle',
                        'Tugas Tambahan' => 'las la-plus-circle',
                        'Penghargaan' => 'las la-award',
                        'Organisasi' => 'las la-sitemap',
                        'Anak' => 'las la-child',
                        'Beasiswa' => 'las la-user-graduate',
                        'Pengalaman Kerja' => 'las la-briefcase-medical',
                        'Lainnya' => 'las la-ellipsis-h'
                    ];
                    $first = true;
                    foreach ($riwayat_cats as $cat => $icon):
                        $active = $first ? 'active' : '';
                        echo '<button class="nav-link ' . $active . '" data-bs-toggle="tab" data-bs-target="#tab-' . str_replace(' ', '-', $cat) . '" type="button" role="tab" data-category="' . $cat . '">
                                <i class="' . $icon . ' me-2"></i> ' . $cat . '
                              </button>';
                        $first = false;
                    endforeach;
                    ?>
                </div>
            </div>

            <div class="tab-content" id="riwayat-tabs-content">
                <?php
                $first = true;
                foreach ($riwayat_cats as $cat => $icon):
                    $active = $first ? 'show active' : '';
                    ?>
                    <div class="tab-pane fade <?php echo $active; ?>" id="tab-<?php echo str_replace(' ', '-', $cat); ?>"
                        role="tabpanel">
                        <div class="modern-card">
                            <div
                                class="modern-card-header d-flex justify-content-between align-items-center p-4 bg-white border-bottom">
                                <h5 class="mb-0 fw-bold text-indigo"><i class="<?php echo $icon; ?> me-2"></i>Riwayat
                                    <?php echo $cat; ?>
                                </h5>
                                <button type="button"
                                    class="btn btn-indigo btn-sm btn-rounded px-4 shadow-sm btn-tambah-riwayat"
                                    data-category="<?php echo $cat; ?>">
                                    <i class="las la-plus me-2"></i> Tambah Riwayat
                                </button>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-modern table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Deskripsi / Keterangan</th>
                                                <th width="150">TMT / Tanggal</th>
                                                <th width="200">No. SK / Dokumen</th>
                                                <th width="100" class="text-center">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="isiTabelRiwayat" data-category="<?php echo $cat; ?>">
                                            <!-- Loaded via AJAX -->
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

<!-- === MODAL: FORM RIWAYAT === -->
<div class="modal fade" id="modalFormRiwayat" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem;">
            <div class="modal-header border-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="modalFormRiwayatLabel">Form Riwayat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formRiwayat" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <input type="hidden" name="id_riwayat" id="id_riwayat">
                    <input type="hidden" name="pegawai_id_riwayat" value="<?php echo $id_pegawai; ?>">
                    <input type="hidden" name="file_lama_riwayat" id="file_lama_riwayat">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="modern-label">Kategori Riwayat</label>
                            <input type="text" name="kategori" id="riwayat_kategori"
                                class="form-control modern-input bg-light" readonly>
                        </div>

                        <!-- Shared Fields Toggle based on Category -->
                        <div id="section_dynamic" class="col-12">
                            <div class="row g-3" id="extra_fields_container">
                                <!-- Dynamic fields injected by JS -->
                            </div>
                        </div>

                        <!-- Hidden Master Fields to Sync -->
                        <div style="display:none;">
                            <textarea name="deskripsi" id="riwayat_deskripsi"></textarea>
                            <input type="text" name="institusi" id="riwayat_institusi">
                            <input type="text" name="jurusan" id="riwayat_jurusan">
                            <input type="text" name="tempat" id="riwayat_tempat">
                            <input type="text" name="tmt" id="riwayat_tmt" class="datepicker">
                            <input type="text" name="no_sk" id="riwayat_no_sk">
                            <input type="text" name="tgl_sk" id="riwayat_tgl_sk" class="datepicker">
                            <input type="text" name="durasi" id="riwayat_durasi">
                            <input type="number" name="masa_kerja_thn" id="riwayat_masa_kerja_thn">
                            <input type="number" name="masa_kerja_bln" id="riwayat_masa_kerja_bln">
                            <input type="text" name="gaji_pokok" id="riwayat_gaji_pokok">
                        </div>

                        <div class="col-12">
                            <label class="modern-label">Upload Lampiran (PDF/JPG)</label>
                            <input type="file" name="file_lampiran" class="form-control modern-input">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light btn-rounded px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formRiwayat" class="btn btn-indigo btn-rounded px-5">Simpan Riwayat</button>
            </div>
        </div>
    </div>
</div>

<style>
    .nav-pills-custom .nav-link {
        color: var(--sap-secondary);
        font-weight: 500;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        transition: all 0.2s;
        border: 1px solid transparent;
    }

    .nav-pills-custom .nav-link:hover {
        background-color: var(--sap-gray-100);
        color: var(--sap-dark);
    }

    .nav-pills-custom .nav-link.active {
        background-color: var(--sap-primary-light);
        color: var(--sap-primary);
        font-weight: 700;
        border-color: var(--sap-primary);
    }

    .riwayat-sub-nav {
        border-bottom: 1px solid var(--sap-gray-200);
        background: transparent;
        padding: 0;
        border-radius: 0;
        gap: 0.25rem;
    }

    .riwayat-sub-nav .nav-link {
        border: none;
        border-radius: 0.5rem 0.5rem 0 0;
        font-size: 0.85rem;
        padding: 0.75rem 1rem;
        color: var(--sap-secondary);
        font-weight: 600;
        transition: all 0.2s ease;
        position: relative;
        background: transparent;
        margin: 0;
    }

    .riwayat-sub-nav .nav-link:hover {
        color: var(--sap-primary);
        background: var(--sap-gray-50);
    }

    .riwayat-sub-nav .nav-link.active {
        color: var(--sap-primary);
        background: transparent;
        font-weight: 700;
    }

    .riwayat-sub-nav .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--sap-primary);
        border-radius: 3px 3px 0 0;
    }

    .text-indigo {
        color: var(--sap-primary);
    }

    /* Validation Styles */
    .form-control.is-invalid,
    .form-select.is-invalid {
        border-color: #ef4444 !important;
        background-image: none !important;
    }

    .invalid-feedback {
        font-size: 0.7rem;
        font-weight: 600;
        color: #ef4444;
        margin-top: 0.25rem;
        margin-left: 0.5rem;
    }
</style>

<script>
    $(document).ready(function () {
        // Initialize Select2 for main form
        $('.modern-input:not(input):not(textarea)').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: '- Pilih -',
            allowClear: false
        });

        // Initialize Flatpickr for main form
        $(".datepicker").flatpickr({
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d",
            locale: "id",
            allowInput: true,
            disableMobile: true,
            monthSelectorType: "static"
        });

        const ajaxUrl = 'proses_pegawai.php';
        const pegawai_id = <?php echo $id_pegawai; ?>;
        let modalFormRiwayat = new bootstrap.Modal(document.getElementById('modalFormRiwayat'));

        // --- TAB 1: PROFIL LOGIC ---
        $('#foto_input').change(function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) { $('#preview-foto').attr('src', e.target.result); }
                reader.readAsDataURL(file);
            }
        });

        $('#formProfil').on('submit', function (e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                showToast('Mohon lengkapi semua field yang wajib diisi dengan benar.', 'warning');
                return;
            }

            const formData = new FormData(this);
            formData.append('action', 'simpan');

            const btn = $(this).find('button[type="submit"]');
            const oldHtml = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner fa-spin me-2"></i> Menyimpan...');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        showToast(res.message, 'success');
                        setTimeout(() => { location.reload(); }, 1500);
                    } else {
                        showToast(res.message, 'error');
                        btn.prop('disabled', false).html(oldHtml);
                    }
                },
                error: function () {
                    showToast('Gagal menghubungi server.', 'error');
                    btn.prop('disabled', false).html(oldHtml);
                }
            });
        });

        // --- TAB 2: RIWAYAT LOGIC ---
        const loadRiwayat = (category = null) => {
            const target = category ? $(`.isiTabelRiwayat[data-category="${category}"]`) : $('.isiTabelRiwayat');
            target.html('<tr><td colspan="4" class="text-center py-5"><div class="spinner-border text-primary spinner-border-sm me-2"></div><span class="text-muted small">Memuat data...</span></td></tr>');

            const catFilter = category || $('#riwayat-tabs .nav-link.active').data('category');

            $.get(ajaxUrl, { action: 'muatRiwayat', pegawai_id: pegawai_id, kategori: catFilter }, (res) => {
                let html = '';
                if (res.status === 'success' && res.data && res.data.length > 0) {
                    res.data.forEach(item => {
                        const fileBtn = item.file_lampiran ? `<a href="../file/datakepegawaian/${item.file_lampiran}" target="_blank" class="btn btn-xs btn-light border p-1 rounded shadow-sm"><i class="las la-file-pdf text-danger me-1"></i> SK</a>` : '<span class="text-muted small italic">Tidak ada file</span>';

                        let detailDesc = item.deskripsi;
                        if (item.kategori === 'Pendidikan' && item.institusi) {
                            detailDesc = `<strong>${item.institusi}</strong><div class="small text-muted">${item.jurusan || ''}</div>`;
                        }

                        html += `
                        <tr>
                            <td><div>${detailDesc}</div></td>
                            <td class="text-muted small">${item.tmt || '-'}</td>
                            <td><div class="small mb-1">${item.no_sk || '-'}</div>${fileBtn}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light border edit-riwayat" data-id="${item.id}"><i class="las la-edit text-warning"></i></button>
                                    <button class="btn btn-sm btn-light border hapus-riwayat" data-id="${item.id}"><i class="las la-trash text-danger"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="4" class="text-center text-muted py-5 small">Belum ada data riwayat untuk kategori ini.</td></tr>';
                }
                target.html(html);
            }, 'json');
        };

        // Trigger load on tab switch
        $('#pills-riwayat-tab').on('shown.bs.tab', function () {
            loadRiwayat();
        });

        $('#riwayat-tabs button').on('shown.bs.tab', function () {
            loadRiwayat($(this).data('category'));
        });

        // Category Specific Fields Mapping
        const updateRiwayatFields = (kat, data = null) => {
            let html = '';
            if (kat === 'Pendidikan') {
                html = `
                <div class="col-12"><label class="modern-label">Nama Sekolah / Univ (*)</label><input type="text" id="r_institusi" class="form-control modern-input" required value="${data?.institusi || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-md-6"><label class="modern-label">Fakultas / Jurusan</label><input type="text" id="r_jurusan" class="form-control modern-input" value="${data?.jurusan || ''}"></div>
                <div class="col-md-6"><label class="modern-label">Tgl Ijazah (*)</label><input type="text" id="r_tgl_sk" class="form-control modern-input datepicker" required value="${data?.tgl_sk || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-12"><label class="modern-label">No. Ijazah (*)</label><input type="text" id="r_no_sk" class="form-control modern-input" required value="${data?.no_sk || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>`;
            } else if (kat === 'Pangkat' || kat === 'Kepangkatan' || kat === 'Jabatan' || kat === 'Tugas Tambahan') {
                html = `
                <div class="col-12"><label class="modern-label">Nama Pangkat / Jabatan / Tugas (*)</label><input type="text" id="r_deskripsi" class="form-control modern-input" required value="${data?.deskripsi || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-md-6"><label class="modern-label">TMT (Terhitung Mulai Tanggal) (*)</label><input type="text" id="r_tmt" class="form-control modern-input datepicker" required value="${data?.tmt || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-md-6"><label class="modern-label">Tgl SK</label><input type="text" id="r_tgl_sk" class="form-control modern-input datepicker" value="${data?.tgl_sk || ''}"></div>
                <div class="col-12"><label class="modern-label">Nomor SK (*)</label><input type="text" id="r_no_sk" class="form-control modern-input" required value="${data?.no_sk || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>`;
            } else if (kat === 'KGB') {
                html = `
                <div class="col-12"><label class="modern-label">Gaji Pokok Baru (Rp) (*)</label><input type="text" id="r_gaji_pokok" class="form-control modern-input" required value="${data?.gaji_pokok || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-md-6"><label class="modern-label">Masa Kerja (Thn)</label><input type="number" id="r_masa_kerja_thn" class="form-control modern-input" value="${data?.masa_kerja_thn || ''}"></div>
                <div class="col-md-6"><label class="modern-label">Masa Kerja (Bln)</label><input type="number" id="r_masa_kerja_bln" class="form-control modern-input" value="${data?.masa_kerja_bln || ''}"></div>
                <div class="col-md-6"><label class="modern-label">TMT KGB (*)</label><input type="text" id="r_tmt" class="form-control modern-input datepicker" required value="${data?.tmt || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-md-6"><label class="modern-label">No. SK KGB (*)</label><input type="text" id="r_no_sk" class="form-control modern-input" required value="${data?.no_sk || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>`;
            } else if (kat === 'Diklat' || kat === 'Seminar') {
                html = `
                <div class="col-12"><label class="modern-label">Nama Diklat / Seminar (*)</label><input type="text" id="r_deskripsi" class="form-control modern-input" required value="${data?.deskripsi || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-12"><label class="modern-label">Penyelenggara / Tempat</label><input type="text" id="r_tempat" class="form-control modern-input" value="${data?.tempat || ''}"></div>
                <div class="col-md-6"><label class="modern-label">Durasi (Jam/Hari)</label><input type="text" id="r_durasi" class="form-control modern-input" value="${data?.durasi || ''}"></div>
                <div class="col-md-6"><label class="modern-label">Tgl Pelaksanaan (*)</label><input type="text" id="r_tmt" class="form-control modern-input datepicker" required value="${data?.tmt || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>`;
            } else if (kat === 'Anak') {
                html = `
                <div class="col-12"><label class="modern-label">Nama Anak (*)</label><input type="text" id="r_deskripsi" class="form-control modern-input" required value="${data?.deskripsi || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-md-6"><label class="modern-label">Tempat Lahir</label><input type="text" id="r_tempat" class="form-control modern-input" value="${data?.tempat || ''}"></div>
                <div class="col-md-6"><label class="modern-label">Tanggal Lahir (*)</label><input type="text" id="r_tmt" class="form-control modern-input datepicker" required value="${data?.tmt || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-12"><label class="modern-label">Jenis Kelamin / Status</label><select id="r_jurusan" class="form-select modern-input"><option value="L">Laki-laki</option><option value="P">Perempuan</option></select></div>`;
            } else if (kat === 'Pengalaman Kerja') {
                html = `
                <div class="col-12"><label class="modern-label">Nama Perusahaan / Instansi (*)</label><input type="text" id="r_institusi" class="form-control modern-input" required value="${data?.institusi || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-12"><label class="modern-label">Jabatan (*)</label><input type="text" id="r_deskripsi" class="form-control modern-input" required value="${data?.deskripsi || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-md-6"><label class="modern-label">Tgl Mulai</label><input type="text" id="r_tmt" class="form-control modern-input datepicker" value="${data?.tmt || ''}"></div>
                <div class="col-md-6"><label class="modern-label">Durasi (Tahun/Bulan)</label><input type="text" id="r_durasi" class="form-control modern-input" placeholder="Contoh: 2 Tahun" value="${data?.durasi || ''}"></div>`;
            } else {
                html = `
                <div class="col-12"><label class="modern-label">Keterangan / Deskripsi (*)</label><input type="text" id="r_deskripsi" class="form-control modern-input" required value="${data?.deskripsi || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-md-6"><label class="modern-label">Tanggal / TMT (*)</label><input type="text" id="r_tmt" class="form-control modern-input datepicker" required value="${data?.tmt || ''}"><div class="invalid-feedback">Wajib diisi.</div></div>
                <div class="col-md-6"><label class="modern-label">Nomor SK/Dokumen</label><input type="text" id="r_no_sk" class="form-control modern-input" value="${data?.no_sk || ''}"></div>`;
            }
            $('#extra_fields_container').html(html);

            // Re-init Select2 for dynamic fields
            $('#extra_fields_container select.modern-input').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '- Pilih -',
                allowClear: true,
                dropdownParent: $('#modalFormRiwayat')
            });

            // Re-init Flatpickr for dynamic fields
            $('#extra_fields_container .datepicker').flatpickr({
                altInput: true,
                altFormat: "d-m-Y",
                dateFormat: "Y-m-d",
                locale: "id",
                allowInput: true,
                disableMobile: true,
                monthSelectorType: "static",
                dropdownParent: $('#modalFormRiwayat')[0]
            });
        };

        $(document).on('click', '.btn-tambah-riwayat', function () {
            const cat = $(this).data('category');
            $('#formRiwayat')[0].reset();
            $('#id_riwayat').val('');
            $('#riwayat_kategori').val(cat);
            updateRiwayatFields(cat);
            modalFormRiwayat.show();
        });

        $(document).on('click', '.edit-riwayat', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambilRiwayat', id: id }, (res) => {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#id_riwayat').val(d.id);
                    $('#riwayat_kategori').val(d.kategori);
                    updateRiwayatFields(d.kategori, d);
                    $('#file_lama_riwayat').val(d.file_lampiran);
                    modalFormRiwayat.show();
                }
            }, 'json');
        });

        $('#formRiwayat').on('submit', function (e) {
            e.preventDefault();

            if (!this.checkValidity()) {
                e.stopPropagation();
                $(this).addClass('was-validated');
                return;
            }

            // Sync dynamic fields to hidden inputs
            $('#riwayat_deskripsi').val($('#r_deskripsi').val() || $('#r_institusi').val() || '');
            $('#riwayat_institusi').val($('#r_institusi').val() || '');
            $('#riwayat_jurusan').val($('#r_jurusan').val() || '');
            $('#riwayat_tmt').val($('#r_tmt').val() || '');
            $('#riwayat_no_sk').val($('#r_no_sk').val() || '');
            $('#riwayat_tgl_sk').val($('#r_tgl_sk').val() || '');
            $('#riwayat_durasi').val($('#r_durasi').val() || '');
            $('#riwayat_masa_kerja_thn').val($('#r_masa_kerja_thn').val() || '');
            $('#riwayat_masa_kerja_bln').val($('#r_masa_kerja_bln').val() || '');
            $('#riwayat_gaji_pokok').val($('#r_gaji_pokok').val() || '');

            const formData = new FormData(this);
            formData.append('action', 'simpanRiwayat');

            $.ajax({
                url: ajaxUrl, type: 'POST', data: formData, contentType: false, processData: false, dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        showToast(res.message, 'success');
                        modalFormRiwayat.hide();
                        loadRiwayat($('#riwayat_kategori').val());
                    } else showToast(res.message, 'error');
                }
            });
        });

        $(document).on('click', '.hapus-riwayat', function () {
            if (!confirm('Hapus data riwayat ini?')) return;
            const id = $(this).data('id');
            const cat = $(this).closest('.isiTabelRiwayat').data('category');
            $.post(ajaxUrl, { action: 'hapusRiwayat', id: id }, (res) => {
                if (res.status === 'success') { showToast(res.message, 'success'); loadRiwayat(cat); }
                else showToast(res.message, 'error');
            }, 'json');
        });
    });
</script>