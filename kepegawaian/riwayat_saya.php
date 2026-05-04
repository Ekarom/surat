<?php
/**
 * Riwayat Kepegawaian Saya - Guru Portal
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$id_pegawai = $_SESSION['id'] ?? 0;
$nama_pegawai = $_SESSION['nama'] ?? 'Saya';
?>

<!-- === EXTERNAL ASSETS === -->
<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Toastiin is used globally via index_ptk.php -->

<style>
    /* Reuse Core Styles */
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

    .modern-card-header {
        background-color: #fff;
        border-bottom: 1px solid var(--sap-gray-100);
        padding: 1.25rem 1.5rem;
    }

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

    .modern-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--sap-secondary);
        margin-bottom: 0.4rem;
        display: block;
    }

    .modern-input {
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

    /* Badge Custom Colors */
    .bg-indigo {
        background-color: #6366f1 !important;
        color: #fff;
    }

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

    .bg-administrasi {
        background-color: #8b5cf6 !important;
        color: #fff;
    }

    .bg-organisasi {
        background-color: #64748b !important;
        color: #fff;
    }

    .bg-sertifikasi {
        background-color: #6366f1 !important;
        color: #fff;
    }

    .bg-anak {
        background-color: #ec4899 !important;
        color: #fff;
    }

    .bg-beasiswa {
        background-color: #0d9488 !important;
        color: #fff;
    }

    .bg-kepangkatan {
        background-color: #3b82f6 !important;
        color: #fff;
    }

    /* Select2 Soft Style Override */
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 0.75rem !important;
        border-color: var(--sap-gray-200) !important;
        background-color: var(--sap-gray-50) !important;
        min-height: 42px !important;
        display: flex !important;
        align-items: center !important;
    }

    .select2-container--bootstrap-5 .select2-selection--single .select2-selection__rendered {
        padding-left: 1rem !important;
        color: var(--sap-dark) !important;
        font-size: 0.9rem !important;
    }

    .select2-container--bootstrap-5 .select2-dropdown {
        border-radius: 1rem !important;
        border: none !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
        overflow: hidden;
    }
</style>

<div class="row">
    <!-- === HEADER === -->
    <div class="col-12 d-md-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title mb-1">Riwayat Kepegawaian</h2>
            <p class="text-muted small mb-0">Kelola riwayat pangkat, jabatan, pendidikan, dan dokumen pendukung Anda.
            </p>
        </div>
        <nav aria-label="breadcrumb" class="mt-2 mt-md-0">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item active text-primary fw-bold">Riwayat</li>
            </ol>
        </nav>
    </div>

    <!-- === MAIN CONTENT === -->
    <div class="modern-card">
        <div class="modern-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="las la-list-ul me-2 text-indigo"></i>Daftar Riwayat Saya</h5>
            <button type="button" class="btn btn-indigo btn-sm btn-rounded px-4 shadow-sm" id="tombolTambahRiwayat">
                <i class="las la-plus me-2"></i> Tambah Riwayat
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-modern table-hover mb-0">
                    <thead>
                        <tr>
                            <th width="150">Kategori</th>
                            <th>Deskripsi / Keterangan</th>
                            <th width="120">TMT</th>
                            <th width="200">No. SK / Dokumen</th>
                            <th width="100" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="isiTabelRiwayat">
                        <!-- Loaded via AJAX -->
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                                <span class="text-muted small">Memuat data...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- === MODAL: FORM RIWAYAT === -->
<div class="modal fade" id="modalFormRiwayat" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalFormRiwayatLabel">Form Riwayat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formRiwayat" enctype="multipart/form-data">
                    <input type="hidden" name="id_riwayat" id="id_riwayat">
                    <input type="hidden" name="pegawai_id_riwayat" id="pegawai_id_riwayat"
                        value="<?php echo $id_pegawai; ?>">
                    <input type="hidden" name="file_lama_riwayat" id="file_lama_riwayat">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="modern-label">Kategori</label>
                            <select name="kategori" id="riwayat_kategori" class="form-select modern-input" required>
                                <option value="Pangkat">Riwayat Pangkat/Golongan</option>
                                <option value="Kepangkatan">Riwayat Kepangkatan</option>
                                <option value="Jabatan">Riwayat Jabatan</option>
                                <option value="Pendidikan">Riwayat Pendidikan</option>
                                <option value="Sertifikasi">Riwayat Sertifikasi</option>
                                <option value="Anak">Riwayat Anak</option>
                                <option value="Beasiswa">Riwayat Beasiswa</option>
                                <option value="Diklat">Riwayat Diklat/Pelatihan</option>
                                <option value="Seminar">Seminar / Workshop / Webinar</option>
                                <option value="KGB">Kenaikan Gaji Berkala (KGB)</option>
                                <option value="Tugas Tambahan">Tugas Tambahan (Wali Kelas/Ka. Lab/dll)</option>
                                <option value="Penghargaan">Penghargaan / Tanda Jasa</option>
                                <option value="Organisasi">Pengalaman Organisasi</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <!-- === SECTION: PENDIDIKAN === -->
                        <div id="section_pendidikan" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Nama Institusi / Sekolah</label>
                                <input type="text" name="institusi" id="riwayat_institusi"
                                    class="form-control modern-input" placeholder="Contoh: Universitas Indonesia">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Fakultas / Jurusan</label>
                                <input type="text" name="jurusan" id="riwayat_jurusan" class="form-control modern-input"
                                    placeholder="Contoh: Teknik Informatika">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Gelar Belakang</label>
                                <input type="text" name="gelar_belakang" id="riwayat_gelar_belakang"
                                    class="form-control modern-input" placeholder="Contoh: S.Kom">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">No. Ijazah</label>
                                <input type="text" name="no_ijazah" id="riwayat_no_ijazah"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Tanggal Ijazah</label>
                                <input type="date" name="tgl_ijazah" id="riwayat_tgl_ijazah"
                                    class="form-control modern-input">
                            </div>
                        </div>

                        <!-- === SECTION: DIKLAT / SEMINAR === -->
                        <div id="section_diklat" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Penyelenggara / Tempat</label>
                                <input type="text" name="tempat" id="riwayat_tempat" class="form-control modern-input">
                            </div>
                            <div class="col-md-12">
                                <label class="modern-label">Durasi (Jam/Hari)</label>
                                <input type="text" name="durasi" id="riwayat_durasi" class="form-control modern-input"
                                    placeholder="Contoh: 32 Jam">
                            </div>
                        </div>

                        <!-- === SECTION: KGB === -->
                        <div id="section_kgb" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-md-6">
                                <label class="modern-label">Masa Kerja (Tahun)</label>
                                <input type="number" name="masa_kerja_thn" id="riwayat_masa_kerja_thn"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Masa Kerja (Bulan)</label>
                                <input type="number" name="masa_kerja_bln" id="riwayat_masa_kerja_bln"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-12">
                                <label class="modern-label">Gaji Pokok Baru (Rp)</label>
                                <input type="text" name="gaji_pokok" id="riwayat_gaji_pokok"
                                    class="form-control modern-input" placeholder="0">
                            </div>
                        </div>

                        <!-- === SECTION: SERTIFIKASI === -->
                        <div id="section_sertifikasi" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Nama Sertifikasi</label>
                                <input type="text" name="deskripsi_sert" id="riwayat_deskripsi_sert"
                                    class="form-control modern-input"
                                    placeholder="Contoh: Sertifikasi Guru Profesional">
                            </div>
                            <div class="col-12">
                                <label class="modern-label">Lembaga Penyelenggara</label>
                                <input type="text" name="institusi_sert" id="riwayat_institusi_sert"
                                    class="form-control modern-input" placeholder="Contoh: Kemdikbud">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">No. Sertifikat</label>
                                <input type="text" name="no_sk_sert" id="riwayat_no_sk_sert" class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Tanggal Sertifikat</label>
                                <input type="date" name="tgl_sk_sert" id="riwayat_tgl_sk_sert" class="form-control modern-input">
                            </div>
                        </div>

                        <!-- === SECTION: ANAK === -->
                        <div id="section_anak" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Nama Anak</label>
                                <input type="text" name="deskripsi_anak" id="riwayat_deskripsi_anak"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Tempat Lahir</label>
                                <input type="text" name="tempat_anak" id="riwayat_tempat_anak"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Tanggal Lahir</label>
                                <input type="date" name="tmt_anak" id="riwayat_tmt_anak"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-12">
                                <label class="modern-label">Status / Jenis Kelamin</label>
                                <select name="jurusan_anak" id="riwayat_jurusan_anak" class="form-select modern-input">
                                    <option value="Anak Kandung (L)">Anak Kandung (Laki-laki)</option>
                                    <option value="Anak Kandung (P)">Anak Kandung (Perempuan)</option>
                                    <option value="Anak Tiri (L)">Anak Tiri (Laki-laki)</option>
                                    <option value="Anak Tiri (P)">Anak Tiri (Perempuan)</option>
                                    <option value="Anak Angkat (L)">Anak Angkat (Laki-laki)</option>
                                    <option value="Anak Angkat (P)">Anak Angkat (Perempuan)</option>
                                </select>
                            </div>
                        </div>

                        <!-- === SECTION: BEASISWA === -->
                        <div id="section_beasiswa" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Nama Beasiswa</label>
                                <input type="text" name="deskripsi_beasiswa" id="riwayat_deskripsi_beasiswa"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-12">
                                <label class="modern-label">Penyelenggara</label>
                                <input type="text" name="institusi_beasiswa" id="riwayat_institusi_beasiswa"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Tgl. Mulai Beasiswa</label>
                                <input type="date" name="tmt_beasiswa" id="riwayat_tmt_beasiswa" class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">No. SK Beasiswa (Jika ada)</label>
                                <input type="text" name="no_sk_beasiswa" id="riwayat_no_sk_beasiswa" class="form-control modern-input">
                            </div>
                        </div>

                        <!-- === SECTION: PANGKAT / KEPANGKATAN === -->
                        <div id="section_pangkat" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Golongan / Ruang</label>
                                <input type="text" name="deskripsi_pangkat" id="riwayat_deskripsi_pangkat"
                                    class="form-control modern-input" placeholder="Contoh: Penata Muda / IIIa">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">TMT (Terhitung Mulai Tanggal)</label>
                                <input type="date" name="tmt_pangkat" id="riwayat_tmt_pangkat" class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">No. SK</label>
                                <input type="text" name="no_sk_pangkat" id="riwayat_no_sk_pangkat" class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Tanggal SK</label>
                                <input type="date" name="tgl_sk_pangkat" id="riwayat_tgl_sk_pangkat" class="form-control modern-input">
                            </div>
                        </div>

                        <!-- === SECTION: JABATAN === -->
                        <div id="section_jabatan" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Nama Jabatan</label>
                                <input type="text" name="deskripsi_jabatan" id="riwayat_deskripsi_jabatan"
                                    class="form-control modern-input" placeholder="Contoh: Guru Muda">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">TMT Jabatan</label>
                                <input type="date" name="tmt_jabatan" id="riwayat_tmt_jabatan" class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">No. SK Jabatan</label>
                                <input type="text" name="no_sk_jabatan" id="riwayat_no_sk_jabatan" class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Tanggal SK</label>
                                <input type="date" name="tgl_sk_jabatan" id="riwayat_tgl_sk_jabatan" class="form-control modern-input">
                            </div>
                        </div>

                        <!-- === SECTION: TUGAS TAMBAHAN === -->
                        <div id="section_tugas" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Nama Tugas Tambahan</label>
                                <input type="text" name="deskripsi_tugas" id="riwayat_deskripsi_tugas"
                                    class="form-control modern-input" placeholder="Contoh: Wali Kelas / Kepala Lab">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">TMT Tugas Tambahan</label>
                                <input type="date" name="tmt_tugas" id="riwayat_tmt_tugas" class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">No. SK Penugasan</label>
                                <input type="text" name="no_sk_tugas" id="riwayat_no_sk_tugas" class="form-control modern-input">
                            </div>
                        </div>

                        <!-- === SECTION: PENGHARGAAN === -->
                        <div id="section_penghargaan" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Nama Penghargaan / Tanda Jasa</label>
                                <input type="text" name="deskripsi_penghargaan" id="riwayat_deskripsi_penghargaan"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Tanggal Penghargaan</label>
                                <input type="date" name="tgl_sk_penghargaan" id="riwayat_tgl_sk_penghargaan" class="form-control modern-input">
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">No. Piagam / SK</label>
                                <input type="text" name="no_sk_penghargaan" id="riwayat_no_sk_penghargaan" class="form-control modern-input">
                            </div>
                        </div>

                        <!-- === SECTION: ORGANISASI === -->
                        <div id="section_organisasi" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Nama Organisasi</label>
                                <input type="text" name="deskripsi_organisasi" id="riwayat_deskripsi_organisasi"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-12">
                                <label class="modern-label">Jabatan di Organisasi</label>
                                <input type="text" name="jurusan_organisasi" id="riwayat_jurusan_organisasi"
                                    class="form-control modern-input">
                            </div>
                            <div class="col-12">
                                <label class="modern-label">Tahun / Periode</label>
                                <input type="text" name="tmt_organisasi" id="riwayat_tmt_organisasi" class="form-control modern-input" placeholder="Contoh: 2020-2022">
                            </div>
                        </div>

                        <!-- === SECTION: LAINNYA === -->
                        <div id="section_lainnya" class="row g-3 mx-0 px-0 mt-0" style="display:none;">
                            <div class="col-12">
                                <label class="modern-label">Deskripsi Keterangan</label>
                                <textarea name="deskripsi_lainnya" id="riwayat_deskripsi_lainnya"
                                    class="form-control modern-input" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Tanggal Kejadian / Berlaku</label>
                                <input type="date" name="tmt_lainnya" id="riwayat_tmt_lainnya" class="form-control modern-input">
                            </div>
                        </div>

                        <!-- === HIDDEN MASTER FIELDS (Synced via JS) === -->
                        <div style="display:none;">
                            <textarea name="deskripsi" id="riwayat_deskripsi"></textarea>
                            <input type="text" name="institusi" id="riwayat_institusi">
                            <input type="text" name="jurusan" id="riwayat_jurusan">
                            <input type="text" name="tempat" id="riwayat_tempat">
                            <input type="date" name="tmt" id="riwayat_tmt">
                            <input type="text" name="no_sk" id="riwayat_no_sk">
                            <input type="date" name="tgl_sk" id="riwayat_tgl_sk">
                        </div>

                        <div class="col-md-6">
                            <label class="modern-label">Lampiran Dokumen (PDF/JPG)</label>
                            <input type="file" name="file_lampiran" id="riwayat_file" class="form-control modern-input">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formRiwayat" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- === SCRIPTS === -->
<!-- Toastr library removed, using Toastiin shim -->

<script>
    $(document).ready(function () {
        const ajaxUrl = 'proses_pegawai.php';
        const pegawai_id = <?php echo $id_pegawai; ?>;
        let modalFormRiwayat = new bootstrap.Modal(document.getElementById('modalFormRiwayat'));

        const formatDate = (dateStr) => {
            if (!dateStr || dateStr === '0000-00-00') return '-';
            const parts = dateStr.split('-');
            return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateStr;
        };

        const loadRiwayat = () => {
            $.get(ajaxUrl, { action: 'muatRiwayat', pegawai_id: pegawai_id }, (res) => {
                let html = '';
                if (res.status === 'success' && res.data && res.data.length > 0) {
                    res.data.forEach(item => {
                        const fileBtn = item.file_lampiran ? `<a href="../file/datakepegawaian/${item.file_lampiran}" target="_blank" class="btn btn-xs btn-light border p-1 rounded shadow-sm" title="Lihat Dokumen"><i class="las la-file-pdf text-danger me-1"></i> Dokumen</a>` : '<span class="text-muted small italic">Tidak ada file</span>';

                        let badgeClass = 'bg-info';
                        switch (item.kategori) {
                            case 'Pangkat': badgeClass = 'bg-pangkat'; break;
                            case 'Kepangkatan': badgeClass = 'bg-kepangkatan'; break;
                            case 'Jabatan': badgeClass = 'bg-jabatan'; break;
                            case 'Pendidikan': badgeClass = 'bg-pendidikan'; break;
                            case 'Sertifikasi': badgeClass = 'bg-sertifikasi'; break;
                            case 'Anak': badgeClass = 'bg-anak'; break;
                            case 'Beasiswa': badgeClass = 'bg-beasiswa'; break;
                            case 'Diklat': case 'Seminar': badgeClass = 'bg-indigo'; break;
                            case 'KGB': badgeClass = 'bg-kgb'; break;
                            case 'Administrasi': badgeClass = 'bg-administrasi'; break;
                            case 'Organisasi': badgeClass = 'bg-organisasi'; break;
                            case 'Tugas Tambahan': badgeClass = 'bg-dark'; break;
                        }

                        let detailHtml = `<div class="fw-medium text-dark">${item.deskripsi}</div>`;
                        if (item.kategori === 'Pendidikan' && item.institusi) {
                            detailHtml = `<div class="fw-bold text-dark">${item.institusi}</div><div class="small text-muted">${item.jurusan || ''} ${item.gelar_belakang ? '(' + item.gelar_belakang + ')' : ''}</div>`;
                        } else if (item.kategori === 'Anak') {
                            detailHtml = `<div class="fw-bold text-dark">${item.deskripsi}</div><div class="small text-muted">${item.jurusan || ''} • Lahir: ${item.tempat || ''}, ${formatDate(item.tmt)}</div>`;
                        } else if (item.kategori === 'Sertifikasi') {
                            detailHtml = `<div class="fw-bold text-dark">${item.deskripsi}</div><div class="small text-muted">Penyelenggara: ${item.institusi || '-'}</div>`;
                        } else if (item.kategori === 'Beasiswa') {
                            detailHtml = `<div class="fw-bold text-dark">${item.deskripsi}</div><div class="small text-muted">Pemberi: ${item.institusi || '-'}</div>`;
                        } else if ((item.kategori === 'Diklat' || item.kategori === 'Seminar') && item.tempat) {
                            detailHtml = `<div class="fw-bold text-dark">${item.deskripsi}</div><div class="small text-muted">@ ${item.tempat} ${item.durasi ? '• ' + item.durasi : ''}</div>`;
                        } else if (item.kategori === 'KGB') {
                            const gaji = item.gaji_pokok ? new Intl.NumberFormat('id-ID').format(item.gaji_pokok) : '0';
                            detailHtml = `<div class="fw-bold text-dark">KGB - Rp ${gaji}</div><div class="small text-muted">Masa Kerja: ${item.masa_kerja_thn || 0}th ${item.masa_kerja_bln || 0}bln</div>`;
                        }

                        let docInfo = item.no_sk || '-';
                        if (item.kategori === 'Pendidikan' && item.no_ijazah) {
                            docInfo = `Ijazah: ${item.no_ijazah}`;
                        }

                        html += `
                    <tr>
                        <td><span class="badge ${badgeClass} border-0 small px-2 py-1">${item.kategori}</span></td>
                        <td>${detailHtml}</td>
                        <td class="text-muted small">${formatDate(item.tmt)}</td>
                        <td>
                            <div class="small text-muted mb-1">${docInfo}</div>
                            ${fileBtn}
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button class="btn btn-sm btn-light border shadow-sm edit-riwayat" data-id="${item.id}" title="Edit"><i class="las la-edit text-warning"></i></button>
                                <button class="btn btn-sm btn-light border shadow-sm hapus-riwayat" data-id="${item.id}" title="Hapus"><i class="las la-trash text-danger"></i></button>
                            </div>
                        </td>
                    </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center text-muted py-5 small">' + (res.message || 'Belum ada data riwayat kepegawaian.') + '</td></tr>';
                }
                $('#isiTabelRiwayat').html(html);
            }, 'json').fail(() => {
                $('#isiTabelRiwayat').html('<tr><td colspan="5" class="text-center text-danger py-5 small">Gagal memuat data dari server.</td></tr>');
            });
        };

        // Initial Load
        loadRiwayat();

        // Initialize Select2
        $('#riwayat_kategori').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalFormRiwayat'),
            width: '100%'
        });

        // Handle Category Toggling
        $('#riwayat_kategori').change(function () {
            const kat = $(this).val();
            // Hide all specific sections
            $('#section_pendidikan, #section_diklat, #section_kgb, #section_anak, #section_sertifikasi, #section_beasiswa, #section_pangkat, #section_jabatan, #section_tugas, #section_penghargaan, #section_organisasi, #section_lainnya').hide();
            
            if (kat === 'Pangkat' || kat === 'Kepangkatan') {
                $('#section_pangkat').show();
            } else if (kat === 'Jabatan') {
                $('#section_jabatan').show();
            } else if (kat === 'Pendidikan') {
                $('#section_pendidikan').show();
            } else if (kat === 'Diklat' || kat === 'Seminar') {
                $('#section_diklat').show();
            } else if (kat === 'KGB') {
                $('#section_kgb').show();
            } else if (kat === 'Anak') {
                $('#section_anak').show();
            } else if (kat === 'Sertifikasi') {
                $('#section_sertifikasi').show();
            } else if (kat === 'Beasiswa') {
                $('#section_beasiswa').show();
            } else if (kat === 'Tugas Tambahan') {
                $('#section_tugas').show();
            } else if (kat === 'Penghargaan') {
                $('#section_penghargaan').show();
            } else if (kat === 'Organisasi') {
                $('#section_organisasi').show();
            } else {
                $('#section_lainnya').show();
            }
        });

        $('#tombolTambahRiwayat').click(function () {
            $('#formRiwayat')[0].reset();
            $('#id_riwayat, #file_lama_riwayat').val('');
            $('#pegawai_id_riwayat').val(pegawai_id);
            $('#modalFormRiwayatLabel').text('Tambah Riwayat Baru');
            $('#riwayat_kategori').trigger('change');
            modalFormRiwayat.show();
        });

        $(document).on('click', '.edit-riwayat', function () {
            const id = $(this).data('id');
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="las la-spinner fa-spin"></i>');

            $.get(ajaxUrl, { action: 'ambilRiwayat', id: id }, (res) => {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#id_riwayat').val(d.id);
                    $('#pegawai_id_riwayat').val(d.pegawai_id);
                    $('#riwayat_kategori').val(d.kategori).trigger('change');
                    $('#riwayat_deskripsi').val(d.deskripsi);
                    $('#riwayat_tmt').val(d.tmt);
                    $('#riwayat_no_sk').val(d.no_sk);
                    $('#riwayat_tgl_sk').val(d.tgl_sk);

                    // Populate extra fields
                    $('#riwayat_institusi').val(d.institusi);
                    $('#riwayat_jurusan').val(d.jurusan);
                    $('#riwayat_no_ijazah').val(d.no_ijazah);
                    $('#riwayat_tgl_ijazah').val(d.tgl_ijazah);
                    $('#riwayat_gelar_belakang').val(d.gelar_belakang);
                    $('#riwayat_tempat').val(d.tempat);
                    $('#riwayat_durasi').val(d.durasi);
                    $('#riwayat_masa_kerja_thn').val(d.masa_kerja_thn);
                    $('#riwayat_masa_kerja_bln').val(d.masa_kerja_bln);
                    $('#riwayat_gaji_pokok').val(d.gaji_pokok);

                    // Sync specific fields for new categories
                    if (d.kategori === 'Anak') {
                        $('#riwayat_deskripsi_anak').val(d.deskripsi);
                        $('#riwayat_tempat_anak').val(d.tempat);
                        $('#riwayat_tmt_anak').val(d.tmt);
                        $('#riwayat_jurusan_anak').val(d.jurusan);
                    } else if (d.kategori === 'Sertifikasi') {
                        $('#riwayat_deskripsi_sert').val(d.deskripsi);
                        $('#riwayat_institusi_sert').val(d.institusi);
                        $('#riwayat_no_sk_sert').val(d.no_sk);
                        $('#riwayat_tgl_sk_sert').val(d.tgl_sk);
                    } else if (d.kategori === 'Beasiswa') {
                        $('#riwayat_deskripsi_beasiswa').val(d.deskripsi);
                        $('#riwayat_institusi_beasiswa').val(d.institusi);
                        $('#riwayat_tmt_beasiswa').val(d.tmt);
                        $('#riwayat_no_sk_beasiswa').val(d.no_sk);
                    } else if (d.kategori === 'Pangkat' || d.kategori === 'Kepangkatan') {
                        $('#riwayat_deskripsi_pangkat').val(d.deskripsi);
                        $('#riwayat_tmt_pangkat').val(d.tmt);
                        $('#riwayat_no_sk_pangkat').val(d.no_sk);
                        $('#riwayat_tgl_sk_pangkat').val(d.tgl_sk);
                    } else if (d.kategori === 'Jabatan') {
                        $('#riwayat_deskripsi_jabatan').val(d.deskripsi);
                        $('#riwayat_tmt_jabatan').val(d.tmt);
                        $('#riwayat_no_sk_jabatan').val(d.no_sk);
                        $('#riwayat_tgl_sk_jabatan').val(d.tgl_sk);
                    } else if (d.kategori === 'Tugas Tambahan') {
                        $('#riwayat_deskripsi_tugas').val(d.deskripsi);
                        $('#riwayat_tmt_tugas').val(d.tmt);
                        $('#riwayat_no_sk_tugas').val(d.no_sk);
                    } else if (d.kategori === 'Penghargaan') {
                        $('#riwayat_deskripsi_penghargaan').val(d.deskripsi);
                        $('#riwayat_tgl_sk_penghargaan').val(d.tgl_sk);
                        $('#riwayat_no_sk_penghargaan').val(d.no_sk);
                    } else if (d.kategori === 'Organisasi') {
                        $('#riwayat_deskripsi_organisasi').val(d.deskripsi);
                        $('#riwayat_jurusan_organisasi').val(d.jurusan);
                        $('#riwayat_tmt_organisasi').val(d.tmt);
                    } else {
                        $('#riwayat_deskripsi_lainnya').val(d.deskripsi);
                        $('#riwayat_tmt_lainnya').val(d.tmt);
                    }

                    $('#file_lama_riwayat').val(d.file_lampiran);
                    $('#modalFormRiwayatLabel').text('Edit Data Riwayat');
                    modalFormRiwayat.show();
                } else {
                    toastr.error(res.message);
                }
            }, 'json').fail(() => {
                toastr.error('Gagal mengambil data dari server.');
            }).always(() => {
                btn.prop('disabled', false).html('<i class="las la-edit text-warning"></i>');
            });
        });

        $('#formRiwayat').on('submit', function (e) {
            // Pre-submit sync for special categories
            const kat = $('#riwayat_kategori').val();
            if (kat === 'Anak') {
                $('#riwayat_deskripsi').val($('#riwayat_deskripsi_anak').val());
                $('#riwayat_tempat').val($('#riwayat_tempat_anak').val());
                $('#riwayat_tmt').val($('#riwayat_tmt_anak').val());
                // No change to tmt because it's shared
                $('#riwayat_jurusan').val($('#riwayat_jurusan_anak').val());
            } else if (kat === 'Sertifikasi') {
                $('#riwayat_deskripsi').val($('#riwayat_deskripsi_sert').val());
                $('#riwayat_institusi').val($('#riwayat_institusi_sert').val());
                $('#riwayat_no_sk').val($('#riwayat_no_sk_sert').val());
                $('#riwayat_tgl_sk').val($('#riwayat_tgl_sk_sert').val());
            } else if (kat === 'Beasiswa') {
                $('#riwayat_deskripsi').val($('#riwayat_deskripsi_beasiswa').val());
                $('#riwayat_institusi').val($('#riwayat_institusi_beasiswa').val());
                $('#riwayat_tmt').val($('#riwayat_tmt_beasiswa').val());
                $('#riwayat_no_sk').val($('#riwayat_no_sk_beasiswa').val());
            } else if (kat === 'Pangkat' || kat === 'Kepangkatan') {
                $('#riwayat_deskripsi').val($('#riwayat_deskripsi_pangkat').val());
                $('#riwayat_tmt').val($('#riwayat_tmt_pangkat').val());
                $('#riwayat_no_sk').val($('#riwayat_no_sk_pangkat').val());
                $('#riwayat_tgl_sk').val($('#riwayat_tgl_sk_pangkat').val());
            } else if (kat === 'Jabatan') {
                $('#riwayat_deskripsi').val($('#riwayat_deskripsi_jabatan').val());
                $('#riwayat_tmt').val($('#riwayat_tmt_jabatan').val());
                $('#riwayat_no_sk').val($('#riwayat_no_sk_jabatan').val());
                $('#riwayat_tgl_sk').val($('#riwayat_tgl_sk_jabatan').val());
            } else if (kat === 'Tugas Tambahan') {
                $('#riwayat_deskripsi').val($('#riwayat_deskripsi_tugas').val());
                $('#riwayat_tmt').val($('#riwayat_tmt_tugas').val());
                $('#riwayat_no_sk').val($('#riwayat_no_sk_tugas').val());
            } else if (kat === 'Penghargaan') {
                $('#riwayat_deskripsi').val($('#riwayat_deskripsi_penghargaan').val());
                $('#riwayat_tgl_sk').val($('#riwayat_tgl_sk_penghargaan').val());
                $('#riwayat_no_sk').val($('#riwayat_no_sk_penghargaan').val());
            } else if (kat === 'Organisasi') {
                $('#riwayat_deskripsi').val($('#riwayat_deskripsi_organisasi').val());
                $('#riwayat_jurusan').val($('#riwayat_jurusan_organisasi').val());
                $('#riwayat_tmt').val($('#riwayat_tmt_organisasi').val());
            } else if (kat === 'Lainnya') {
                $('#riwayat_deskripsi').val($('#riwayat_deskripsi_lainnya').val());
                $('#riwayat_tmt').val($('#riwayat_tmt_lainnya').val());
            }

            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpanRiwayat');

            const btn = $('button[form="formRiwayat"]');
            const oldHtml = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner fa-spin me-2"></i> Memproses...');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        modalFormRiwayat.hide();
                        loadRiwayat();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function (xhr) {
                    toastr.error('Gagal menyimpan data. Terjadi kesalahan pada server.');
                    console.error(xhr.responseText);
                },
                complete: function () {
                    btn.prop('disabled', false).html(oldHtml);
                }
            });
        });

        $(document).on('click', '.hapus-riwayat', function () {
            const id = $(this).data('id');
            if (confirm('Apakah Anda yakin ingin menghapus data riwayat ini?')) {
                $.post(ajaxUrl, { action: 'hapusRiwayat', id: id }, (res) => {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        loadRiwayat();
                    }
                }, 'json');
            }
        });
    });
</script>
