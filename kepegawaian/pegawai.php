<?php
/**
 * S.A.P KEPEGAWAIAN - Personnel Management Module
 * Premium DataTables Implementation with FixedColumns
 * Managed by Antigravity AI
 */
?>

<!-- === EXTERNAL ASSETS === -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/5.0.5/css/fixedColumns.bootstrap5.css">
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
        box-shadow: var(--sap-shadow-sm);
        transition: box-shadow 0.3s ease;
    }

    .modern-card:hover {
        box-shadow: var(--sap-shadow-md);
    }

    .modern-card-header {
        background-color: #fff;
        border-bottom: 1px solid var(--sap-gray-50);
        padding: 1rem 1.5rem;
    }

    /* Table Styles */
    .table-modern thead th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: var(--sap-secondary);
        padding: 1rem 0.75rem;
        border-bottom: 1px solid var(--sap-gray-100) !important;
        background-color: var(--sap-gray-50);
        border-top: none;
    }

    .table-modern tbody td {
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid #f8fafc;
        font-size: 0.875rem;
    }

    .table-modern.table-striped tbody tr:nth-of-type(odd) {
        background-color: #fafbfc;
    }

    /* DataTables FixedColumns Shadow */
    .dtfc-fixed-left,
    .dtfc-fixed-right {
        background-color: #fff !important;
        z-index: 5;
    }

    .table-modern.dataTable tr>.dtfc-fixed-left:last-child {
        box-shadow: 5px 0 10px -5px rgba(0, 0, 0, 0.1);
    }

    .table-modern.dataTable tr>.dtfc-fixed-right:first-child {
        box-shadow: -5px 0 10px -5px rgba(0, 0, 0, 0.1);
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

    /* Utility Classes */
    .btn-rounded {
        border-radius: 50px;
    }

    .bg-soft-primary {
        background-color: var(--sap-primary-light);
        color: var(--sap-primary);
    }

    .bg-soft-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: var(--sap-success);
    }

    .extra-small {
        font-size: 0.75rem;
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
    <div class="modern-card">
        <div class="modern-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
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
            <div class="table-responsive">
                <table id="tabelPegawai" class="table table-modern table-striped text-nowrap w-100">
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

<!-- === SCRIPTS === -->
<script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/5.0.5/js/dataTables.fixedColumns.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function () {
        const ajaxUrl = 'proses_pegawai.php';
        let modalPegawai, modalDetail;

        // Initialize Modals
        const initModals = () => {
            if (typeof bootstrap !== 'undefined') {
                modalPegawai = new bootstrap.Modal(document.getElementById('modalPegawai'));
                modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));
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
            layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: null },
            fixedColumns: { left: 3, right: 2 },
            paging: false,
            scrollCollapse: true,
            scrollX: true,
            scrollY: '50vh',
            order: [[2, 'asc']],
            ajax: { url: ajaxUrl, type: "GET", data: { action: "muatDataJSON" } },
            columns: [
                { data: null, className: "text-center align-middle", render: (data, type, row, meta) => meta.row + 1 },
                {
                    data: "foto",
                    className: "text-center align-middle",
                    render: (data) => {
                        const path = data ? '../file/pegawai/' + data : '../images/default.png';
                        return `<img src="${path}" class="rounded-circle shadow-sm" style="width: 38px; height: 38px; object-fit: cover; border: 2px solid #fff;">`;
                    }
                },
                {
                    data: null,
                    className: "align-middle",
                    render: (data, type, row) => `
                    <div style="line-height: 1.4;">
                        <div class="fw-bold text-dark small">${row.nm_pegawai}</div>
                        <div class="text-muted extra-small mt-1">${row.nip || '-'}</div>
                    </div>`
                },
                { data: "tempat_lahir", className: "align-middle small" },
                { data: "tgl_lahir", className: "align-middle small", render: (data) => formatDate(data) },
                { data: "jenis_kelamin", className: "text-center align-middle small" },
                { data: "pendidikan", className: "align-middle small" },
                { data: "jabatan", className: "align-middle small" },
                { data: "pangkat", className: "align-middle small" },
                { data: "golongan", className: "align-middle small" },
                { data: "unit_kerja", className: "align-middle small" },
                { data: "status_pegawai", className: "align-middle small" },
                {
                    data: "status",
                    className: "text-center align-middle",
                    render: (data, type, row) => {
                        const isChecked = (data == '1' || data == 'Aktif') ? 'checked' : '';
                        return `<div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input status-switch" type="checkbox" role="switch" data-id="${row.id}" ${isChecked}>
                            </div>`;
                    }
                },
                {
                    data: null,
                    className: "text-center align-middle",
                    render: (data, type, row) => `
                    <div class="d-flex justify-content-center gap-1">
                        <button class="btn btn-primary btn-square p-0 d-flex align-items-center justify-content-center tombol-view" data-id="${row.id}" style="width:28px; height:28px;" title="Detail"><i class="la la-eye"></i></button>
                        <button class="btn btn-warning btn-square p-0 d-flex align-items-center justify-content-center tombol-edit" data-id="${row.id}" style="width:28px; height:28px;" title="Edit"><i class="la la-edit"></i></button>
                        <button class="btn btn-danger btn-square p-0 d-flex align-items-center justify-content-center tombol-hapus" data-id="${row.id}" style="width:28px; height:28px;" title="Hapus"><i class="la la-trash"></i></button>
                    </div>`
                }
            ],
            language: { search: "Cari:", info: "Menampilkan _START_ - _END_ dari _TOTAL_ data", zeroRecords: "Tidak ada data ditemukan" },
            drawCallback: function (settings) {
                const api = this.api();
                const total = api.page.info().recordsTotal;
                $('#totalPegawaiBadge').text(total + ' Pegawai');
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
                    $('#detail-status_pegawai').text(d.status_pegawai || '-');
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
    });
</script>