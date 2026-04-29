<?php
/**
 * Personnel Management Module
 * Premium DataTables Implementation with FixedColumns
 */
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($conn)) {
    if (file_exists('../dbconn.php')) {
        include_once "../dbconn.php";
    } else {
        include_once "dbconn.php";
    }
}
?>

<!-- === ASSETS: STYLESHEETS === -->
<link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.bootstrap5.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/5.0.5/css/fixedColumns.bootstrap5.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<div class="content-fluid">
    <!-- === PAGE HEADER === -->
    <div class="content-header py-4">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h2 class="fw-800 text-dark mb-0">Manajemen Kepegawaian</h2>
                    <p class="text-muted mb-0 small mt-1">Kelola database profil, jabatan, dan status kepegawaian secara
                        terpusat</p>
                </div>
                <div class="col-sm-6 text-sm-right">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-right bg-transparent p-0 m-0">
                            <li class="breadcrumb-item"><a href="index.php"
                                    class="text-decoration-none text-muted">Home</a></li>
                            <li class="breadcrumb-item active text-primary fw-bold">Kepegawaian</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <!-- === MAIN CONTENT === -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="modern-card shadow-sm border-0 overflow-hidden">
                        <!-- Table Toolbar -->
                        <div
                            class="modern-card-header d-flex justify-content-between align-items-center py-3 bg-white border-bottom px-4">
                            <div class="d-flex align-items-center gap-3">
                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm"
                                    id="tombolTambahPegawai">
                                    <i class="fas fa-plus me-2"></i> Tambah Pegawai
                                </button>
                                <button type="button"
                                    class="btn btn-soft-secondary btn-sm rounded-circle p-0 d-flex align-items-center justify-content-center"
                                    id="tombolRefresh" style="width: 38px; height: 38px;" title="Refresh Data">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <div class="vr mx-2 text-gray-300 h-20px"></div>
                                <span class="badge badge-soft-primary px-3 py-2"
                                    id="totalPegawaiBadge">Loading...</span>
                            </div>
                            <!-- Custom Search Container -->
                            <div class="d-flex align-items-center position-relative">
                                <i class="fas fa-search position-absolute ms-3 text-muted"
                                    style="font-size: 0.8rem;"></i>
                                <input type="text" id="customSearch"
                                    class="form-control form-control-sm rounded-pill ps-5 bg-light border-0"
                                    placeholder="Cari data pegawai..." style="width: 250px; height: 38px;">
                            </div>
                        </div>

                        <!-- Main Table Implementation -->
                        <div class="card-body p-0">
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
                                    <!-- Dynamic Content Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- === MODAL: FORM PEGAWAI (ADD/EDIT) === -->
    <div class="modal fade" id="modalPegawai" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modern-modal border-0 shadow-lg">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-800 text-dark" id="modalPegawaiLabel">
                        <i class="fas fa-user-plus me-2 text-primary"></i>Form Pegawai
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formPegawai" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="pegawai_id">
                        <input type="hidden" name="foto_lama" id="foto_lama">

                        <div class="row g-4">
                            <!-- Section: Profile Photo -->
                            <div class="col-md-4 text-center">
                                <div class="position-relative d-inline-block group-photo">
                                    <div class="photo-container shadow-sm border">
                                        <img id="preview-foto" src="../images/default.png" class="img-fluid"
                                            style="width: 180px; height: 180px; object-fit: cover;">
                                    </div>
                                    <label for="foto"
                                        class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle shadow-sm p-0 d-flex align-items-center justify-content-center"
                                        style="width: 38px; height: 38px; border: 3px solid #fff; cursor: pointer;">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input type="file" id="foto" name="foto" class="d-none" accept="image/*">
                                </div>
                                <p class="extra-small text-muted mt-3 mb-0">Klik ikon kamera untuk upload<br>(JPG/PNG,
                                    Max 2MB)</p>
                            </div>

                            <!-- Section: Basic Identity -->
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="modern-label">NIP / NIK <span class="text-danger">*</span></label>
                                        <input type="text" name="nip" id="nip" class="form-control modern-input"
                                            required placeholder="Contoh: 19800101...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="modern-label">Nama Lengkap <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="nm_pegawai" id="nm_pegawai"
                                            class="form-control modern-input" required placeholder="Nama Tanpa Gelar">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="modern-label">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" id="tempat_lahir"
                                            class="form-control modern-input" placeholder="Kota/Kabupaten">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="modern-label">Tanggal Lahir</label>
                                        <input type="date" name="tgl_lahir" id="tgl_lahir"
                                            class="form-control modern-input">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="modern-label">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" id="jenis_kelamin"
                                            class="form-select modern-input">
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="modern-label">Pendidikan Terakhir</label>
                                        <input type="text" name="pendidikan" id="pendidikan"
                                            class="form-control modern-input" placeholder="S1 - Hukum">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="modern-label">Tanggal Lulus</label>
                                        <input type="date" name="tgl_lulus" id="tgl_lulus"
                                            class="form-control modern-input">
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Career & Employment -->
                            <div class="col-12">
                                <div class="bg-gray-100 p-4 rounded-4 border">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="modern-label">Jabatan</label>
                                            <input type="text" name="jabatan" id="jabatan"
                                                class="form-control modern-input" placeholder="Staf ...">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="modern-label">Pangkat</label>
                                            <input type="text" name="pangkat" id="pangkat"
                                                class="form-control modern-input" placeholder="Penata Muda">
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
                                                class="form-control modern-input" placeholder="Bidang ...">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="modern-label">Status Kepegawaian</label>
                                            <select name="status_pegawai" id="status_pegawai"
                                                class="form-select modern-input">
                                                <option value="PNS">Pegawai Negeri Sipil (PNS)</option>
                                                <option value="PPPK">PPPK</option>
                                                <option value="Honorer">Tenaga Honorer</option>
                                                <option value="Lainnya">Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Section: Contact Information -->
                            <div class="col-md-6">
                                <label class="modern-label">No. HP / WhatsApp</label>
                                <div class="input-group modern-input-group">
                                    <span class="input-group-text bg-transparent border-0 pe-0"><i
                                            class="fab fa-whatsapp text-success"></i></span>
                                    <input type="text" name="no_hp" id="no_hp" class="form-control border-0"
                                        placeholder="08XXXXXXXXXX">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="modern-label">Email Aktif</label>
                                <div class="input-group modern-input-group">
                                    <span class="input-group-text bg-transparent border-0 pe-0"><i
                                            class="fas fa-envelope text-danger small"></i></span>
                                    <input type="email" name="email" id="email" class="form-control border-0"
                                        placeholder="nama@email.com">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold text-muted"
                        data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formPegawai"
                        class="btn btn-primary rounded-pill px-5 fw-bold shadow">Simpan Data</button>
                </div>
            </div>
        </div>
    </div>

    <!-- === MODAL: DETAIL PEGAWAI (VIEW) === -->
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modern-modal border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 pb-0 position-absolute end-0 top-0" style="z-index: 10;">
                    <button type="button" class="btn-close bg-white rounded-circle p-2 shadow-sm"
                        data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-0">
                    <!-- Detail Header Background -->
                    <div class="bg-primary-gradient py-5 px-4 mb-n5">
                        <div class="photo-container-detail shadow-lg mx-auto mb-3" style="border: 4px solid #fff;">
                            <img id="detail-foto" src="../images/default.png"
                                style="width: 132px; height: 132px; object-fit: cover;">
                        </div>
                        <h4 id="detail-nama" class="fw-800 mb-0 text-white"></h4>
                        <p id="detail-nip" class="text-white-50 small mb-0"></p>
                    </div>

                    <!-- Detail Info Cards -->
                    <div class="bg-white rounded-5 position-relative px-4 pt-5 pb-4 shadow-sm"
                        style="margin-top: -40px;">
                        <div class="badge-soft-primary px-4 py-2 d-inline-block mb-4 mt-2" id="detail-jabatan"></div>

                        <div class="row text-start g-4">
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
                                <label class="detail-label">Status</label>
                                <p class="detail-value" id="detail-status-pegawai"></p>
                            </div>

                            <div class="col-12 border-top pt-3 mt-3">
                                <div class="row">
                                    <div class="col-6 border-end">
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
</div>

<!-- === ASSETS: SCRIPTS === -->
<script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/5.0.5/js/dataTables.fixedColumns.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function () {
        /**
         * INITIALIZATION
         */
        const ajaxUrl = 'proses_pegawai.php';
        let modalPegawai, modalDetail;

        // Bootstrap Modals
        const initModals = () => {
            if (typeof bootstrap !== 'undefined') {
                modalPegawai = new bootstrap.Modal(document.getElementById('modalPegawai'));
                modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));
            } else {
                setTimeout(initModals, 100);
            }
        };
        initModals();

        // Date Formatter Helper
        const formatDate = (dateStr) => {
            if (!dateStr || dateStr === '0000-00-00') return '-';
            const parts = dateStr.split('-');
            return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateStr;
        };

        /**
         * DATATABLES CONFIGURATION
         */
        const table = new DataTable('#tabelPegawai', {
            layout: {
                topStart: null,
                topEnd: null,
                bottomStart: 'info',
                bottomEnd: null
            },
            fixedColumns: { left: 3, right: 2 },
            paging: false,
            scrollCollapse: true,
            scrollX: true,
            scrollY: 450,
            autoWidth: false,
            order: [[2, 'asc']],
            ajax: {
                url: ajaxUrl,
                type: "GET",
                data: { action: "muatDataJSON" }
            },
            columns: [
                {
                    "data": null,
                    "className": "text-center align-middle",
                    "render": (data, type, row, meta) => meta.row + 1
                },
                {
                    "data": "foto",
                    "className": "text-center align-middle",
                    "render": (data) => {
                        const path = data ? '../file/pegawai/' + data : '../images/default.png';
                        return `<img src="${path}" class="img-thumbnail rounded-circle shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">`;
                    }
                },
                {
                    "data": null,
                    "className": "align-middle",
                    "render": (data, type, row) => `
                        <div>
                            <div class="fw-bold text-dark small">${row.nm_pegawai}</div>
                            <div class="text-muted extra-small">${row.nip || '-'}</div>
                        </div>`
                },
                { "data": "tempat_lahir", "className": "align-middle small" },
                {
                    "data": "tgl_lahir",
                    "className": "align-middle small",
                    "render": (data) => formatDate(data)
                },
                {
                    "data": "jenis_kelamin",
                    "className": "text-center align-middle small",
                    "render": (data) => data === 'L' ? 'L' : 'P'
                },
                { "data": "pendidikan", "className": "align-middle small" },
                { "data": "jabatan", "className": "align-middle small" },
                { "data": "pangkat", "className": "align-middle small" },
                { "data": "golongan", "className": "align-middle small" },
                { "data": "unit_kerja", "className": "align-middle small" },
                { "data": "status_pegawai", "className": "align-middle small" },
                {
                    "data": "status",
                    "className": "text-center align-middle",
                    "render": (data, type, row) => {
                        const isChecked = (data == '1' || data == 'Aktif') ? 'checked' : '';
                        return `
                            <div class="form-check form-switch d-flex justify-content-center">
                                <input class="form-check-input status-switch" type="checkbox" role="switch" data-id="${row.id}" ${isChecked}>
                            </div>`;
                    }
                },
                {
                    "data": null,
                    "className": "text-center align-middle",
                    "render": (data, type, row) => `
                        <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-soft-primary btn-xs tombol-view" data-id="${row.id}" title="Detail"><i class="fas fa-eye"></i></button>
                            <button class="btn btn-soft-info btn-xs tombol-edit" data-id="${row.id}" title="Edit"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-soft-danger btn-xs tombol-hapus" data-id="${row.id}" title="Hapus"><i class="fas fa-trash"></i></button>
                        </div>`
                }
            ],
            "language": {
                "search": "Cari Pegawai:",
                "info": "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                "zeroRecords": "Tidak ada data pegawai ditemukan"
            },
            drawCallback: function (settings) {
                $('#totalPegawaiBadge').text(settings._iRecordsTotal + ' Total Pegawai');
            }
        });

        // Refresh Table
        $('#tombolRefresh').click(function () {
            const btn = $(this);
            btn.find('i').addClass('fa-spin');
            table.ajax.reload(() => {
                setTimeout(() => btn.find('i').removeClass('fa-spin'), 500);
                toastr.info('Data berhasil diperbarui');
            }, false);
        });

        // Custom Search Implementation
        $('#customSearch').on('keyup', function () {
            table.search(this.value).draw();
        });

        /**
         * EVENT HANDLERS: CRUD OPERATIONS
         */

        // Create
        $('#tombolTambahPegawai').click(function () {
            $('#formPegawai')[0].reset();
            $('#pegawai_id').val('');
            $('#foto_lama').val('');
            $('#preview-foto').attr('src', '../images/default.png');
            $('#modalPegawaiLabel').html('<i class="fas fa-user-plus me-2 text-primary"></i> Tambah Pegawai Baru');
            modalPegawai.show();
        });

        // Photo Preview
        $('#foto').change(function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => $('#preview-foto').attr('src', e.target.result);
                reader.readAsDataURL(file);
            }
        });

        // Save (Insert/Update)
        $('#formPegawai').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpan');

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: (res) => {
                    if (res.status === 'success') {
                        modalPegawai.hide();
                        toastr.success(res.message);
                        table.ajax.reload(null, false);
                    } else {
                        toastr.error(res.message);
                    }
                }
            });
        });

        // Edit (Populate Data)
        $(document).on('click', '.tombol-edit', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambil', id: id }, (res) => {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#pegawai_id').val(d.id);
                    $('#nip').val(d.nip);
                    $('#nm_pegawai').val(d.nm_pegawai);
                    $('#tempat_lahir').val(d.tempat_lahir);
                    $('#tgl_lahir').val(d.tgl_lahir);
                    $('#jenis_kelamin').val(d.jenis_kelamin);
                    $('#jabatan').val(d.jabatan);
                    $('#pangkat').val(d.pangkat);
                    $('#golongan').val(d.golongan);
                    $('#unit_kerja').val(d.unit_kerja);
                    $('#status_pegawai').val(d.status_pegawai);
                    $('#pendidikan').val(d.pendidikan);
                    $('#tgl_lulus').val(d.tgl_lulus);
                    $('#tmt_golongan').val(d.tmt_golongan);
                    $('#no_hp').val(d.no_hp);
                    $('#email').val(d.email);
                    $('#foto_lama').val(d.foto);

                    const foto = d.foto ? '../file/pegawai/' + d.foto : '../images/default.png';
                    $('#preview-foto').attr('src', foto);

                    $('#modalPegawaiLabel').html('<i class="fas fa-user-edit me-2 text-primary"></i> Edit Data Pegawai');
                    modalPegawai.show();
                }
            }, 'json');
        });

        // Detail (View Mode)
        $(document).on('click', '.tombol-view', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambil', id: id }, (res) => {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#detail-nama').text(d.nm_pegawai || '-');
                    $('#detail-nip').text('NIP: ' + (d.nip || '-'));
                    $('#detail-jabatan').text(d.jabatan || 'Belum diatur');

                    // TTL Formatting
                    let ttl = '-';
                    const tgl = formatDate(d.tgl_lahir);
                    if (d.tempat_lahir && tgl !== '-') ttl = d.tempat_lahir + ', ' + tgl;
                    else ttl = d.tempat_lahir || tgl;
                    $('#detail-ttl').text(ttl);

                    $('#detail-jk').text(d.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan');

                    // Career Info
                    const tmt = formatDate(d.tmt_golongan);
                    let pGol = (d.pangkat || '') + (d.pangkat && d.golongan ? ' / ' : '') + (d.golongan || '');
                    if (pGol && tmt !== '-') pGol += ` (TMT: ${tmt})`;
                    else pGol = pGol || (tmt !== '-' ? `TMT: ${tmt}` : '-');
                    $('#detail-pangkat-tmt').text(pGol);

                    // Education Info
                    const lulus = formatDate(d.tgl_lulus);
                    let pend = d.pendidikan || '';
                    if (pend && lulus !== '-') pend += ` (Lulus: ${lulus})`;
                    else pend = pend || (lulus !== '-' ? `Lulus: ${lulus}` : '-');
                    $('#detail-pendidikan-tgl').text(pend);

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

        // Status Toggle
        $(document).on('change', '.status-switch', function () {
            const el = $(this), id = el.data('id'), status = el.is(':checked') ? '1' : '0';
            $.post(ajaxUrl, { action: 'ubah_status', id: id, status: status }, (res) => {
                if (res.status === 'success') toastr.success(res.message);
                else { el.prop('checked', !el.is(':checked')); toastr.error(res.message); }
            }, 'json').fail(() => { el.prop('checked', !el.is(':checked')); toastr.error('Sistem Error'); });
        });

        // Delete
        $(document).on('click', '.tombol-hapus', function () {
            const id = $(this).data('id');
            if (confirm('Hapus data pegawai ini secara permanen?')) {
                $.post(ajaxUrl, { action: 'hapus', id: id }, (res) => {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        table.ajax.reload(null, false);
                    }
                }, 'json');
            }
        });
    });
</script>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    :root {
        --primary: #4f46e5;
        --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --gray-100: #f8fafc;
        --gray-200: #e2e8f0;
        --dark: #1e293b;
        --text-muted: #64748b;
    }

    .content-wrapper {
        background-color: #f8fafc !important;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .fw-800 {
        font-weight: 800;
    }

    .bg-primary-gradient {
        background: var(--primary-gradient);
    }

    /* Modern Card & Table UI */
    .modern-card {
        background: #fff;
        border-radius: 1.5rem;
    }

    .h-20px {
        height: 20px;
    }

    .table-modern thead th {
        font-size: 0.72rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        padding: 1rem !important;
        letter-spacing: 0.08em !important;
        vertical-align: middle !important;
        border: none !important;
    }

    /* Grouped Header Styles */
    .header-group th {
        padding: 0.6rem !important;
        font-size: 0.75rem !important;
        color: #fff !important;
    }

    .group-primary {
        background: #4f46e5 !important;
    }

    .group-info {
        background: #0ea5e9 !important;
    }

    .table-modern thead tr:last-child th {
        background: #fff !important;
        color: var(--primary) !important;
        border-bottom: 2px solid rgba(79, 70, 229, 0.1) !important;
        box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.05);
    }

    .table-modern tbody td {
        vertical-align: middle !important;
        padding: 1rem !important;
        border-color: #f1f5f9 !important;
        font-size: 0.85rem !important;
    }

    .table-modern.table-striped tbody tr:nth-of-type(odd) {
        background-color: #fafbfc !important;
    }

    .table-modern.table-striped tbody tr:hover {
        background-color: #f1f5f9 !important;
        transition: 0.2s;
    }

    /* FixedColumns Customization */
    .dtfc-fixed-left,
    .dtfc-fixed-right {
        background-color: #fff !important;
        z-index: 5 !important;
    }

    /* Elegant Shadow for Fixed Columns */
    .table-modern.dataTable tr>.dtfc-fixed-left:last-child {
        border-right: 1px solid rgba(0, 0, 0, 0.05) !important;
        box-shadow: 10px 0 15px -10px rgba(0, 0, 0, 0.08) !important;
    }

    .table-modern.dataTable tr>.dtfc-fixed-right:first-child {
        border-left: 1px solid rgba(0, 0, 0, 0.05) !important;
        box-shadow: -10px 0 15px -10px rgba(0, 0, 0, 0.08) !important;
    }

    .table-modern.dataTable tbody tr:hover>.dtfc-fixed-left,
    .table-modern.dataTable tbody tr:hover>.dtfc-fixed-right {
        background-color: #f8fafc !important;
    }

    /* Modal & Inputs */
    .modern-modal {
        border-radius: 1.5rem;
    }

    .modern-label {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-muted);
        letter-spacing: 0.05em;
        margin-bottom: 0.4rem;
        display: block;
    }

    .modern-input {
        border-radius: 0.75rem;
        border: 1px solid var(--gray-200);
        padding: 0.6rem 1rem;
        font-size: 0.875rem;
        transition: 0.3s;
    }

    .modern-input:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .modern-input-group {
        background: var(--gray-100);
        border: 1px solid var(--gray-200);
        border-radius: 0.75rem;
        overflow: hidden;
    }

    .photo-container {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        overflow: hidden;
        background-color: var(--gray-100);
    }

    .photo-container-detail {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        overflow: hidden;
        background-color: #fff;
    }

    /* Detail Modal Elements */
    .detail-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 2px;
        display: block;
    }

    .detail-value {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 0;
    }

    .badge-soft-primary {
        background: rgba(79, 70, 229, 0.1);
        color: var(--primary);
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 0.75rem;
    }

    /* Buttons */
    .btn-xs {
        padding: 0.4rem 0.7rem;
        font-size: 0.75rem;
        border-radius: 0.75rem;
        border: none;
    }

    .btn-soft-primary {
        background: rgba(79, 70, 229, 0.1);
        color: var(--primary);
    }

    .btn-soft-primary:hover {
        background: var(--primary);
        color: #fff;
    }

    .btn-soft-info {
        background: rgba(14, 165, 233, 0.1);
        color: #0ea5e9;
    }

    .btn-soft-info:hover {
        background: #0ea5e9;
        color: #fff;
    }

    .btn-soft-danger {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .btn-soft-danger:hover {
        background: #ef4444;
        color: #fff;
    }

    .btn-soft-secondary {
        background: var(--gray-100);
        color: var(--text-muted);
        border: 1px solid var(--gray-200);
    }

    .btn-soft-secondary:hover {
        background: var(--gray-200);
        color: var(--dark);
    }

    .btn-primary {
        background: var(--primary-gradient);
        border: none;
    }

    .btn-primary:hover {
        opacity: 0.9;
        transform: translateY(-1px);
    }

    /* DataTables Pagination & Search */
    .dt-container .dt-search input {
        border-radius: 2rem !important;
        padding: 0.5rem 1.25rem !important;
        border: 1px solid var(--gray-200) !important;
        border-color: var(--gray-200) !important;
    }
</style>