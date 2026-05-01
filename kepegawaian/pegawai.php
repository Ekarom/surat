<?php
/**
 * S.A.P KEPEGAWAIAN - Personnel Management Module
 * Premium DataTables Implementation with FixedColumns
 * Managed by Antigravity AI
 */
?>

<!-- === EXTERNAL ASSETS === -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.3/css/responsive.bootstrap5.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<style>
    /* CSS Variables & Core Styles */
    :root {
        --sap-primary: #4f46e5;
        --sap-primary-light: rgba(79, 70, 229, 0.1);
        --sap-primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --sap-secondary: #64748b;
        --sap-success: #10b981;
        --sap-info: #0ea5e9;
        --sap-danger: #ef4444;
        --sap-warning: #f59e0b;
        --sap-dark: #1e293b;
        --sap-gray-50: #f8fafc;
        --sap-gray-100: #f1f5f9;
        --sap-gray-200: #e2e8f0;
        --sap-border-radius: 1rem;
        --sap-shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --sap-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    /* Page Header */
    .page-title {
        font-weight: 800;
        color: var(--sap-dark);
        letter-spacing: -0.025em;
    }

    /* Modern Card */
    .modern-card {
        background: #fff;
        border-radius: var(--sap-border-radius);
        border: none;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .modern-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .modern-card-header {
        background-color: #fff;
        border-bottom: 1px solid var(--sap-gray-100);
        padding: 1.25rem 1.5rem;
    }

    /* Table Styles */
    .table-modern thead th {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--sap-secondary);
        padding: 1.1rem 0.75rem;
        border-bottom: 2px solid var(--sap-gray-100) !important;
        background-color: var(--sap-gray-50);
        border-top: none;
        white-space: nowrap;
    }

    .table-modern tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.85rem;
        color: #334155;
        transition: background-color 0.2s;
    }

    .table-modern tbody tr:hover td {
        background-color: rgba(79, 70, 229, 0.02) !important;
    }

    .table-modern.table-striped tbody tr:nth-of-type(odd) {
        background-color: #fafbfc;
    }

    /* Custom Scrollbar for Table */
    .table-responsive::-webkit-scrollbar {
        height: 8px;
        width: 8px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    /* DataTables Responsive Child Row Style */
    table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before,
    table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before {
        background-color: var(--sap-primary);
        border: 2px solid #fff;
        box-shadow: var(--sap-shadow-sm);
        font-family: "Font Awesome 6 Free";
        content: "\f0fe";
        font-weight: 900;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        font-size: 10px;
    }

    table.dataTable.dtr-inline.collapsed>tbody>tr.parent>td.dtr-control:before,
    table.dataTable.dtr-inline.collapsed>tbody>tr.parent>th.dtr-control:before {
        background-color: var(--sap-danger);
        content: "\f146";
    }

    .dtr-details {
        width: 100%;
        padding: 1rem;
        background: var(--sap-gray-50);
        border-radius: 0.75rem;
    }

    .dtr-title {
        font-weight: 700;
        color: var(--sap-secondary);
        font-size: 0.75rem;
        text-transform: uppercase;
        margin-right: 1rem;
    }

    /* Badges Style */
    .badge-soft {
        font-weight: 600;
        padding: 0.35em 0.8em;
        border-radius: 50px;
        font-size: 0.75rem;
    }

    .badge-soft-pns {
        background-color: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
    }

    .badge-soft-pppk {
        background-color: rgba(14, 165, 233, 0.1);
        color: #0ea5e9;
    }

    .badge-soft-honorer {
        background-color: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .badge-soft-lainnya {
        background-color: rgba(100, 116, 139, 0.1);
        color: #64748b;
    }

    /* Modals */
    .modern-modal {
        border-radius: 1.25rem;
        overflow: hidden;
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
    }

    /* Photo Containers */
    .photo-preview-wrapper {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto;
    }

    .photo-preview {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: var(--sap-shadow-md);
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
        box-shadow: var(--sap-shadow-sm);
        transition: transform 0.2s;
    }

    .photo-upload-btn:hover {
        transform: scale(1.1);
    }

    /* Detail Modal Styles */
    .detail-header {
        background: var(--sap-primary-gradient);
        padding: 3rem 1.5rem 5rem 1.5rem;
        text-align: center;
    }

    .detail-body {
        margin-top: -4rem;
        background: #fff;
        border-radius: 2rem 2rem 0 0;
        padding: 2.5rem 2rem;
        box-shadow: 0 -10px 20px -5px rgba(0, 0, 0, 0.05);
    }

    .detail-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--sap-secondary);
        margin-bottom: 2px;
    }

    .detail-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--sap-dark);
        margin-bottom: 0;
    }

    /* Custom Search Bar */
    #customSearch {
        transition: all 0.3s ease;
        border: 1px solid transparent !important;
    }

    #customSearch:focus {
        width: 300px !important;
        background-color: #fff !important;
        border-color: var(--sap-primary) !important;
        box-shadow: 0 0 0 4px var(--sap-primary-light) !important;
    }

    .bg-soft-primary {
        background-color: var(--sap-primary-light);
        color: var(--sap-primary);
        font-weight: 600;
    }

    .btn-square {
        border-radius: 0.5rem;
    }

    .btn-rounded {
        border-radius: 50px;
    }

    /* Animation */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Pagination Styling */
    .page-link {
        border: none;
        background: transparent;
        color: var(--sap-secondary);
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.6rem 0.9rem;
        border-radius: 0.75rem !important;
        transition: all 0.2s;
        margin: 0 2px;
    }

    .page-link:hover {
        background-color: var(--sap-gray-100);
        color: var(--sap-primary);
    }

    .page-item.active .page-link {
        background: var(--sap-primary-gradient);
        color: #fff;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
    }

    .page-item.disabled .page-link {
        background: transparent;
        opacity: 0.5;
    }

    .dataTables_info {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--sap-secondary);
    }
</style>

<div class="container-fluid">
    <!-- === HEADER === -->
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="page-title mb-1">Manajemen Kepegawaian</h2>
            <p class="text-muted small mb-0">Kelola database profil, jabatan, dan status kepegawaian secara terpusat</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-md-end bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a>
                    </li>
                    <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">Kepegawaian</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- === MAIN TABLE CARD === -->
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-primary btn-sm btn-rounded px-4 shadow-sm"
                    id="tombolTambahPegawai">
                    <i class="fas fa-user-plus me-2"></i> Tambah Pegawai
                </button>
                <button type="button"
                    class="btn btn-outline-secondary btn-sm btn-rounded p-0 d-flex align-items-center justify-content-center"
                    id="tombolRefresh" style="width: 36px; height: 36px;" title="Refresh Data">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <div class="vr mx-1 text-gray-300" style="height: 20px;"></div>
                <span class="badge bg-soft-primary rounded-pill px-3 py-2" id="totalPegawaiBadge">Loading...</span>
            </div>

            <div class="position-relative">
                <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="customSearch"
                    class="form-control form-control-sm btn-rounded ps-5 border-0 bg-light"
                    placeholder="Cari data pegawai..." style="width: 250px; height: 36px;">
            </div>
        </div>

        <div class="card-body p-0">
            <div class="p-3">
                <table id="tabelPegawai" class="table table-modern table-striped w-100">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Foto</th>
                            <th>NIP / Nama</th>
                            <th>Tempat</th>
                            <th>Tanggal</th>
                            <th class="text-center">JK</th>
                            <th>Pendidikan</th>
                            <th>Jabatan</th>
                            <th>Pangkat</th>
                            <th>Gol</th>
                            <th>Unit Kerja</th>
                            <th>Status</th>
                            <th class="text-center">Aktif</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Content loaded via AJAX -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- === MODAL: FORM PEGAWAI === -->
<div class="modal fade" id="modalPegawai" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modern-modal border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="modalPegawaiLabel">
                    <i class="fas fa-user-circle me-2 text-primary"></i>Form Pegawai
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formPegawai" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="pegawai_id">
                    <input type="hidden" name="foto_lama" id="foto_lama">

                    <div class="row g-4">
                        <!-- Photo Upload Section -->
                        <div class="col-md-4 text-center">
                            <div class="photo-preview-wrapper">
                                <img id="preview-foto" src="../images/default.png" class="photo-preview">
                                <label for="foto" class="photo-upload-btn">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="foto" name="foto" class="d-none" accept="image/*">
                            </div>
                            <p class="extra-small text-muted mt-3">Format: JPG/PNG, Max 2MB</p>
                        </div>

                        <!-- Basic Info Section -->
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="modern-label">NIP / NIK <span class="text-danger">*</span></label>
                                    <input type="text" name="nip" id="nip" class="form-control modern-input" required
                                        placeholder="NIP Pegawai">
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="nm_pegawai" id="nm_pegawai"
                                        class="form-control modern-input" required placeholder="Nama Lengkap">
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" id="tempat_lahir"
                                        class="form-control modern-input" placeholder="Kota Lahir">
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Tanggal Lahir</label>
                                    <input type="date" name="tgl_lahir" id="tgl_lahir"
                                        class="form-control modern-input">
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-select modern-input">
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Pendidikan</label>
                                    <input type="text" name="pendidikan" id="pendidikan"
                                        class="form-control modern-input" placeholder="S1 - ...">
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Tgl Lulus</label>
                                    <input type="date" name="tgl_lulus" id="tgl_lulus"
                                        class="form-control modern-input">
                                </div>
                            </div>
                        </div>

                        <!-- Employment Info Section -->
                        <div class="col-12">
                            <div class="bg-light p-4 rounded-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="modern-label">Jabatan</label>
                                        <input type="text" name="jabatan" id="jabatan" class="form-control modern-input"
                                            placeholder="Staf">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="modern-label">Pangkat</label>
                                        <input type="text" name="pangkat" id="pangkat" class="form-control modern-input"
                                            placeholder="Pangkat">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="modern-label">Golongan</label>
                                        <input type="text" name="golongan" id="golongan"
                                            class="form-control modern-input" placeholder="III/a">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="modern-label">TMT Golongan</label>
                                        <input type="date" name="tmt_golongan" id="tmt_golongan"
                                            class="form-control modern-input">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="modern-label">Unit Kerja</label>
                                        <input type="text" name="unit_kerja" id="unit_kerja"
                                            class="form-control modern-input" placeholder="Bidang/Unit">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="modern-label">Status Kepegawaian</label>
                                        <select name="status_pegawai" id="status_pegawai"
                                            class="form-select modern-input">
                                            <option value="PNS">PNS</option>
                                            <option value="PPPK">PPPK</option>
                                            <option value="Honorer">Honorer</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Info Section -->
                        <div class="col-md-6">
                            <label class="modern-label">No. HP / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="fab fa-whatsapp text-success"></i></span>
                                <input type="text" name="no_hp" id="no_hp" class="form-control modern-input border-0"
                                    placeholder="08...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="fas fa-envelope text-primary"></i></span>
                                <input type="email" name="email" id="email" class="form-control modern-input border-0"
                                    placeholder="email@domain.com">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light btn-rounded px-4 fw-bold"
                    data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formPegawai" class="btn btn-primary btn-rounded px-5 fw-bold">Simpan
                    Data</button>
            </div>
        </div>
    </div>
</div>

<!-- === MODAL: DETAIL PEGAWAI === -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 position-absolute end-0 top-0" style="z-index: 10;">
                <button type="button" class="btn-close bg-white rounded-circle p-2 shadow-sm" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="detail-header text-white">
                    <img id="detail-foto" src="../images/default.png" class="photo-preview mb-3"
                        style="width: 120px; height: 120px; border-width: 5px;">
                    <h4 id="detail-nama" class="fw-bold mb-1"></h4>
                    <p id="detail-nip" class="opacity-75 small mb-0"></p>
                </div>
                <div class="detail-body">
                    <div class="text-center mb-4">
                        <span class="badge bg-soft-primary px-4 py-2 rounded-pill fw-bold" id="detail-jabatan"></span>
                    </div>

                    <div class="row g-4">
                        <div class="col-6">
                            <label class="detail-label">Tempat/Tgl Lahir</label>
                            <p class="detail-value" id="detail-ttl"></p>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Jenis Kelamin</label>
                            <p class="detail-value" id="detail-jk"></p>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Pangkat/Gol & TMT</label>
                            <p class="detail-value" id="detail-pangkat-tmt"></p>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Pendidikan & Lulus</label>
                            <p class="detail-value" id="detail-pendidikan-tgl"></p>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Unit Kerja</label>
                            <p class="detail-value" id="detail-unit"></p>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Status Pegawai</label>
                            <p class="detail-value" id="detail-status-pegawai"></p>
                        </div>

                        <div class="col-12 pt-3 mt-3 border-top">
                            <div class="row">
                                <div class="col-6">
                                    <label class="detail-label">WhatsApp</label>
                                    <p class="detail-value text-success" id="detail-hp"></p>
                                </div>
                                <div class="col-6">
                                    <label class="detail-label">Email</label>
                                    <p class="detail-value text-primary" id="detail-email"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- === MODAL: RIWAYAT KEPEGAWAIAN === -->
<div class="modal fade" id="modalRiwayat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modern-modal border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <div>
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-history me-2 text-info"></i>Riwayat Kepegawaian
                    </h5>
                    <p class="text-muted small mb-0" id="riwayat-nama-pegawai"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0">Daftar Riwayat</h6>
                    <button type="button" class="btn btn-primary btn-sm btn-rounded px-3" id="tombolTambahRiwayat">
                        <i class="fas fa-plus me-1"></i> Tambah
                    </button>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="tabelRiwayat">
                        <thead class="bg-light">
                            <tr>
                                <th class="small fw-bold">Kategori</th>
                                <th class="small fw-bold">Deskripsi</th>
                                <th class="small fw-bold">TMT</th>
                                <th class="small fw-bold">No. SK</th>
                                <th class="small fw-bold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="isiTabelRiwayat">
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- === MODAL: FORM RIWAYAT === -->
<div class="modal fade" id="modalFormRiwayat" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="modalFormRiwayatLabel">Form Riwayat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formRiwayat" enctype="multipart/form-data">
                    <input type="hidden" name="id_riwayat" id="id_riwayat">
                    <input type="hidden" name="pegawai_id_riwayat" id="pegawai_id_riwayat">
                    <input type="hidden" name="file_lama_riwayat" id="file_lama_riwayat">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="modern-label">Kategori</label>
                            <select name="kategori" id="riwayat_kategori" class="form-select modern-input" required>
                                <option value="Pangkat">Riwayat Pangkat/Golongan</option>
                                <option value="Jabatan">Riwayat Jabatan</option>
                                <option value="Pendidikan">Riwayat Pendidikan</option>
                                <option value="Diklat">Riwayat Diklat/Pelatihan</option>
                                <option value="Penghargaan">Penghargaan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="modern-label">Deskripsi / Keterangan</label>
                            <textarea name="deskripsi" id="riwayat_deskripsi" class="form-control modern-input" rows="2" required placeholder="Contoh: Penata Muda / IIIa atau Kepala Seksi..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">TMT (Terhitung Mulai Tanggal)</label>
                            <input type="date" name="tmt" id="riwayat_tmt" class="form-control modern-input">
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">No. SK</label>
                            <input type="text" name="no_sk" id="riwayat_no_sk" class="form-control modern-input" placeholder="Nomor Surat Keputusan">
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">Tanggal SK</label>
                            <input type="date" name="tgl_sk" id="riwayat_tgl_sk" class="form-control modern-input">
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">Lampiran SK (PDF/JPG)</label>
                            <input type="file" name="file_lampiran" id="riwayat_file" class="form-control modern-input">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light btn-rounded px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formRiwayat" class="btn btn-primary btn-rounded px-5 fw-bold">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- === SCRIPTS === -->
<script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.3/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.3/js/responsive.bootstrap5.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function () {
        const ajaxUrl = 'proses_pegawai.php';
        let modalPegawai, modalDetail, modalRiwayat, modalFormRiwayat;

        // Initialize Modals
        const initModals = () => {
            if (typeof bootstrap !== 'undefined') {
                modalPegawai = new bootstrap.Modal(document.getElementById('modalPegawai'));
                modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));
                modalRiwayat = new bootstrap.Modal(document.getElementById('modalRiwayat'));
                modalFormRiwayat = new bootstrap.Modal(document.getElementById('modalFormRiwayat'));
            } else {
                setTimeout(initModals, 100);
            }
        };
        initModals();

        // Helpers
        const formatDate = (dateStr) => {
            if (!dateStr || dateStr === '0000-00-00') return '-';
            const parts = dateStr.split('-');
            return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateStr;
        };

        // Toastr Config
        toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right" };

        // DataTable Initialization
        const table = new DataTable('#tabelPegawai', {
            layout: { 
                topStart: null, 
                topEnd: null, 
                bottomStart: 'info', 
                bottomEnd: 'paging' 
            },
            responsive: {
                details: {
                    type: 'column',
                    target: 'tr'
                }
            },
            paging: true,
            pageLength: 10,
            order: [[2, 'asc']],
            ajax: { url: ajaxUrl, type: "GET", data: { action: "muatDataJSON" } },
            columns: [
                { 
                    data: null, 
                    className: "text-center align-middle dtr-control", 
                    orderable: false,
                    render: (data, type, row, meta) => `<span class="fw-bold text-secondary opacity-75 small">${meta.row + 1}</span>` 
                },
                {
                    data: "foto",
                    className: "text-center align-middle",
                    responsivePriority: 3,
                    render: (data) => {
                        const path = data ? '../file/pegawai/' + data : '../images/default.png';
                        return `<img src="${path}" class="rounded-circle shadow-sm border border-2 border-white" style="width: 38px; height: 38px; object-fit: cover;">`;
                    }
                },
                {
                    data: null,
                    className: "align-middle",
                    responsivePriority: 1,
                    render: (data, type, row) => `
                    <div class="d-flex align-items-center gap-2">
                        <div style="line-height: 1.3;">
                            <div class="fw-bold text-dark" style="font-size: 0.9rem;">${row.nm_pegawai}</div>
                            <div class="text-muted extra-small d-flex align-items-center gap-1">
                                <i class="fas fa-id-badge opacity-50"></i> ${row.nip || '-'}
                            </div>
                        </div>
                    </div>`
                },
                { data: "tempat_lahir", className: "align-middle small text-muted", responsivePriority: 10 },
                { data: "tgl_lahir", className: "align-middle small text-muted", responsivePriority: 10, render: (data) => formatDate(data) },
                {
                    data: "jenis_kelamin",
                    className: "text-center align-middle small",
                    responsivePriority: 9,
                    render: (data) => data === 'L' ? '<span class="text-primary fw-bold">L</span>' : '<span class="text-danger fw-bold">P</span>'
                },
                { data: "pendidikan", className: "align-middle small", responsivePriority: 8 },
                { data: "jabatan", className: "align-middle small fw-medium", responsivePriority: 5 },
                { data: "pangkat", className: "align-middle small text-muted", responsivePriority: 7 },
                { data: "golongan", className: "align-middle small text-muted", responsivePriority: 7 },
                { data: "unit_kerja", className: "align-middle small fw-medium", responsivePriority: 6 },
                {
                    data: "status_pegawai",
                    className: "align-middle text-center",
                    responsivePriority: 4,
                    render: (data) => {
                        const s = (data || '').toLowerCase();
                        let cls = 'badge-soft-lainnya';
                        if (s.includes('pns')) cls = 'badge-soft-pns';
                        else if (s.includes('pppk')) cls = 'badge-soft-pppk';
                        else if (s.includes('honorer')) cls = 'badge-soft-honorer';
                        return `<span class="badge-soft ${cls}">${data || '-'}</span>`;
                    }
                },
                {
                    data: "status",
                    className: "text-center align-middle",
                    responsivePriority: 11,
                    render: (data, type, row) => {
                        const isChecked = (data == '1' || data == 'Aktif') ? 'checked' : '';
                        return `<div class="form-check form-switch d-flex justify-content-center m-0">
                                <input class="form-check-input status-switch" type="checkbox" role="switch" data-id="${row.id}" ${isChecked} style="cursor: pointer; width: 2.2em; height: 1.1em;">
                            </div>`;
                    }
                },
                {
                    data: null,
                    className: "text-center align-middle",
                    responsivePriority: 2,
                    render: (data, type, row) => `
                    <div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-sm btn-light border shadow-sm btn-rounded d-flex align-items-center justify-content-center tombol-riwayat" 
                            data-id="${row.id}" data-nama="${row.nm_pegawai}" style="width:30px; height:30px;" title="Riwayat Kepegawaian" data-bs-toggle="tooltip">
                            <i class="fas fa-history text-info" style="font-size: 0.8rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-light border shadow-sm btn-rounded d-flex align-items-center justify-content-center tombol-view" 
                            data-id="${row.id}" style="width:30px; height:30px;" title="Detail" data-bs-toggle="tooltip">
                            <i class="fas fa-eye text-primary" style="font-size: 0.8rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-light border shadow-sm btn-rounded d-flex align-items-center justify-content-center tombol-edit" 
                            data-id="${row.id}" style="width:30px; height:30px;" title="Edit" data-bs-toggle="tooltip">
                            <i class="fas fa-edit text-warning" style="font-size: 0.8rem;"></i>
                        </button>
                        <button class="btn btn-sm btn-light border shadow-sm btn-rounded d-flex align-items-center justify-content-center tombol-hapus" 
                            data-id="${row.id}" style="width:30px; height:30px;" title="Hapus" data-bs-toggle="tooltip">
                            <i class="fas fa-trash text-danger" style="font-size: 0.8rem;"></i>
                        </button>
                    </div>`
                }
            ],
            language: { search: "Cari:", info: "Menampilkan _START_ - _END_ dari _TOTAL_ data", zeroRecords: "Tidak ada data ditemukan" },
            drawCallback: function (settings) {
                const api = this.api();
                const total = api.page.info().recordsTotal;
                $('#totalPegawaiBadge').text(total + ' Pegawai');

                // Initialize Tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function (tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }
        });

        // Custom Search
        $('#customSearch').on('keyup', function () { table.search(this.value).draw(); });

        // Refresh
        $('#tombolRefresh').click(function () {
            const btn = $(this);
            btn.find('i').addClass('fa-spin');
            table.ajax.reload(() => {
                setTimeout(() => btn.find('i').removeClass('fa-spin'), 500);
                toastr.success('Data diperbarui');
            }, false);
        });

        // Form: Create
        $('#tombolTambahPegawai').click(function () {
            $('#formPegawai')[0].reset();
            $('#pegawai_id, #foto_lama').val('');
            $('#preview-foto').attr('src', '../images/default.png');
            $('#modalPegawaiLabel').html('<i class="fas fa-user-plus me-2 text-primary"></i>Tambah Pegawai Baru');
            modalPegawai.show();
        });

        // Form: Photo Preview
        $('#foto').change(function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => $('#preview-foto').attr('src', e.target.result);
                reader.readAsDataURL(file);
            }
        });

        // Form: Submit
        $('#formPegawai').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpan');
            $.ajax({
                url: ajaxUrl, type: 'POST', data: formData, processData: false, contentType: false, dataType: 'json',
                success: (res) => {
                    if (res.status === 'success') {
                        modalPegawai.hide();
                        toastr.success(res.message);
                        table.ajax.reload(null, false);
                    } else toastr.error(res.message);
                }
            });
        });

        // Action: Edit
        $(document).on('click', '.tombol-edit', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambil', id: id }, (res) => {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#pegawai_id').val(d.id); $('#nip').val(d.nip); $('#nm_pegawai').val(d.nm_pegawai);
                    $('#tempat_lahir').val(d.tempat_lahir); $('#tgl_lahir').val(d.tgl_lahir); $('#jenis_kelamin').val(d.jenis_kelamin);
                    $('#jabatan').val(d.jabatan); $('#pangkat').val(d.pangkat); $('#golongan').val(d.golongan);
                    $('#unit_kerja').val(d.unit_kerja); $('#status_pegawai').val(d.status_pegawai); $('#pendidikan').val(d.pendidikan);
                    $('#tgl_lulus').val(d.tgl_lulus); $('#tmt_golongan').val(d.tmt_golongan); $('#no_hp').val(d.no_hp);
                    $('#email').val(d.email); $('#foto_lama').val(d.foto);
                    const foto = d.foto ? '../file/pegawai/' + d.foto : '../images/default.png';
                    $('#preview-foto').attr('src', foto);
                    $('#modalPegawaiLabel').html('<i class="fas fa-user-edit me-2 text-primary"></i>Edit Data Pegawai');
                    modalPegawai.show();
                }
            }, 'json');
        });

        // Action: Detail
        $(document).on('click', '.tombol-view', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambil', id: id }, (res) => {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#detail-nama').text(d.nm_pegawai || '-');
                    $('#detail-nip').text('NIP: ' + (d.nip || '-'));
                    $('#detail-jabatan').text(d.jabatan || 'Staf');

                    let ttl = '-';
                    const tgl = formatDate(d.tgl_lahir);
                    if (d.tempat_lahir && tgl !== '-') ttl = d.tempat_lahir + ', ' + tgl;
                    else ttl = d.tempat_lahir || tgl;
                    $('#detail-ttl').text(ttl);
                    $('#detail-jk').text(d.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan');

                    const tmt = formatDate(d.tmt_golongan);
                    let pGol = (d.pangkat || '') + (d.pangkat && d.golongan ? ' / ' : '') + (d.golongan || '');
                    if (pGol && tmt !== '-') pGol += ` (TMT: ${tmt})`;
                    $('#detail-pangkat-tmt').text(pGol || '-');

                    const lulus = formatDate(d.tgl_lulus);
                    let pend = d.pendidikan || '';
                    if (pend && lulus !== '-') pend += ` (Lulus: ${lulus})`;
                    $('#detail-pendidikan-tgl').text(pend || '-');

                    $('#detail-unit').text(d.unit_kerja || '-');
                    $('#detail-status-pegawai').text(d.status_pegawai || '-');
                    $('#detail-hp').text(d.no_hp || '-');
                    $('#detail-email').text(d.email || '-');
                    const foto = d.foto ? '../file/pegawai/' + d.foto : '../images/default.png';
                    $('#detail-foto').attr('src', foto);
                    modalDetail.show();
                }
            }, 'json');
        });

        // Action: Status
        $(document).on('change', '.status-switch', function () {
            const el = $(this), id = el.data('id'), status = el.is(':checked') ? '1' : '0';
            $.post(ajaxUrl, { action: 'ubah_status', id: id, status: status }, (res) => {
                if (res.status === 'success') toastr.success(res.message);
                else { el.prop('checked', !el.is(':checked')); toastr.error(res.message); }
            }, 'json');
        });

        // Action: Delete
        $(document).on('click', '.tombol-hapus', function () {
            const id = $(this).data('id');
            if (confirm('Hapus data pegawai ini?')) {
                $.post(ajaxUrl, { action: 'hapus', id: id }, (res) => {
                    if (res.status === 'success') { toastr.success(res.message); table.ajax.reload(null, false); }
                }, 'json');
            }
        });

        // === RIWAYAT LOGIC ===

        const loadRiwayat = (pegawai_id) => {
            $.get(ajaxUrl, { action: 'muatRiwayat', pegawai_id: pegawai_id }, (res) => {
                let html = '';
                if (res.data && res.data.length > 0) {
                    res.data.forEach(item => {
                        const fileBtn = item.file_lampiran ? `<a href="../file/riwayat/${item.file_lampiran}" target="_blank" class="btn btn-xs btn-light border p-1 rounded" title="Lihat SK"><i class="fas fa-file-pdf text-danger"></i></a>` : '';
                        html += `
                        <tr>
                            <td><span class="badge bg-light text-dark small">${item.kategori}</span></td>
                            <td class="small fw-medium">${item.deskripsi}</td>
                            <td class="small">${formatDate(item.tmt)}</td>
                            <td class="small text-muted">${item.no_sk || '-'} ${fileBtn}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-link text-warning p-0 edit-riwayat" data-id="${item.id}" title="Edit"><i class="fas fa-edit"></i></button>
                                    <button class="btn btn-link text-danger p-0 hapus-riwayat" data-id="${item.id}" title="Hapus"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center text-muted py-3 small italic">Belum ada data riwayat</td></tr>';
                }
                $('#isiTabelRiwayat').html(html);
            }, 'json');
        };

        $(document).on('click', '.tombol-riwayat', function () {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            $('#pegawai_id_riwayat').val(id);
            $('#riwayat-nama-pegawai').text(nama);
            loadRiwayat(id);
            modalRiwayat.show();
        });

        $('#tombolTambahRiwayat').click(function () {
            $('#formRiwayat')[0].reset();
            $('#id_riwayat, #file_lama_riwayat').val('');
            $('#modalFormRiwayatLabel').text('Tambah Riwayat');
            modalFormRiwayat.show();
        });

        $(document).on('click', '.edit-riwayat', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambilRiwayat', id: id }, (res) => {
                const d = res.data;
                $('#id_riwayat').val(d.id);
                $('#riwayat_kategori').val(d.kategori);
                $('#riwayat_deskripsi').val(d.deskripsi);
                $('#riwayat_tmt').val(d.tmt);
                $('#riwayat_no_sk').val(d.no_sk);
                $('#riwayat_tgl_sk').val(d.tgl_sk);
                $('#file_lama_riwayat').val(d.file_lampiran);
                $('#modalFormRiwayatLabel').text('Edit Riwayat');
                modalFormRiwayat.show();
            }, 'json');
        });

        $('#formRiwayat').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpanRiwayat');
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
                        loadRiwayat($('#pegawai_id_riwayat').val());
                    } else {
                        toastr.error(res.message);
                    }
                }
            });
        });

        $(document).on('click', '.hapus-riwayat', function () {
            const id = $(this).data('id');
            if (confirm('Hapus riwayat ini?')) {
                $.post(ajaxUrl, { action: 'hapusRiwayat', id: id }, (res) => {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        loadRiwayat($('#pegawai_id_riwayat').val());
                    }
                }, 'json');
            }
        });
    });
</script>