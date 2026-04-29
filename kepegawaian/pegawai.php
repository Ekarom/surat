<?php
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

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-users-cog text-primary"></i> Manajemen Kepegawaian</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Kepegawaian</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card bg-dark-lighter shadow-lg border-0" style="border-radius: 15px; overflow: hidden;">
                        <div
                            class="card-header border-bottom border-secondary d-flex justify-content-between align-items-center py-3">
                            <div>
                                <button type="button" class="btn btn-light btn-sm shadow-sm fw-bold"
                                    id="tombolTambahPegawai" style="border-radius: 8px;">
                                    <i class="fas fa-plus-circle text-primary"></i> Tambah Pegawai
                                </button>
                            </div>
                            <div class="d-flex align-items-center gap-3">
                                <div class="input-group input-group-sm" style="width: 250px;">
                                    <input type="text" id="search-pegawai" class="form-control border-0 shadow-sm"
                                        placeholder="Cari nama/NIP..." style="border-radius: 8px 0 0 8px;">
                                    <span class="input-group-text bg-white border-0 shadow-sm"
                                        style="border-radius: 0 8px 8px 0;"><i
                                            class="fas fa-search text-muted"></i></span>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" id="tabel-pegawai">
                                <!-- Data will be loaded here via AJAX -->
                                <div class="text-center p-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 text-muted">Memuat data pegawai...</p>
                                </div>
                            </div>
                        </div>
                        <div
                            class="card-footer bg-white border-0 d-flex justify-content-between align-items-center px-4 py-3">
                            <div id="info-entri" class="small text-muted fw-bold"></div>
                            <div id="paginasi-pegawai"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Form Pegawai -->
    <div class="modal fade" id="modalPegawai" data-bs-backdrop="static" tabindex="-1"
        aria-labelledby="modalPegawaiLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg bg-dark-lighter" style="border-radius: 20px;">
                <div class="modal-header border-bottom border-secondary text-white"
                    style="border-radius: 20px 20px 0 0;">
                    <h5 class="modal-title" id="modalPegawaiLabel"><i class="fas fa-user-plus me-2 text-primary"></i>
                        Form Pegawai</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="formPegawai" enctype="multipart/form-data">
                        <input type="hidden" name="id" id="pegawai_id">
                        <input type="hidden" name="foto_lama" id="foto_lama">

                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <div class="position-relative d-inline-block">
                                    <img id="preview-foto" src="images/default.png" class="img-thumbnail shadow-sm"
                                        style="width: 180px; height: 180px; object-fit: cover; border-radius: 50%; border: 4px solid #f8f9fa;">
                                    <label for="foto"
                                        class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle shadow"
                                        style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; border: 2px solid #fff;">
                                        <i class="fas fa-camera"></i>
                                    </label>
                                    <input type="file" id="foto" name="foto" class="d-none" accept="image/*">
                                </div>
                                <p class="small text-muted mt-2">Klik ikon kamera untuk ganti foto<br>(Maks. 2MB,
                                    JPG/PNG)</p>
                            </div>
                            <div class="col-md-8">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">NIP / NIK <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="nip" id="nip" class="form-control rounded-3" required
                                            placeholder="Contoh: 1980...">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Nama Lengkap <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="nm_pegawai" id="nm_pegawai"
                                            class="form-control rounded-3" required placeholder="Nama lengkap & gelar">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Tempat Lahir</label>
                                        <input type="text" name="tempat_lahir" id="tempat_lahir"
                                            class="form-control rounded-3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Tanggal Lahir</label>
                                        <input type="date" name="tgl_lahir" id="tgl_lahir"
                                            class="form-control rounded-3">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Jenis Kelamin</label>
                                        <select name="jenis_kelamin" id="jenis_kelamin" class="form-select rounded-3">
                                            <option value="L">Laki-laki</option>
                                            <option value="P">Perempuan</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold small text-uppercase">Pendidikan
                                            Terakhir</label>
                                        <input type="text" name="pendidikan" id="pendidikan"
                                            class="form-control rounded-3">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4 opacity-50">

                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Jabatan</label>
                                <input type="text" name="jabatan" id="jabatan" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Pangkat</label>
                                <input type="text" name="pangkat" id="pangkat" class="form-control rounded-3">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-uppercase">Golongan</label>
                                <input type="text" name="golongan" id="golongan" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Unit Kerja</label>
                                <input type="text" name="unit_kerja" id="unit_kerja" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Status Kepegawaian</label>
                                <select name="status_pegawai" id="status_pegawai" class="form-select rounded-3">
                                    <option value="PNS">PNS</option>
                                    <option value="PPPK">PPPK</option>
                                    <option value="Honorer">Honorer</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">No. HP / WhatsApp</label>
                                <input type="text" name="no_hp" id="no_hp" class="form-control rounded-3">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-uppercase">Email</label>
                                <input type="email" name="email" id="email" class="form-control rounded-3">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" form="formPegawai" class="btn btn-primary px-4 fw-bold shadow"
                        style="border-radius: 10px;">Simpan Data</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detail Pegawai -->
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg"
                style="border-radius: 20px; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);">
                <div class="modal-header border-0 pb-0">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-4">
                    <div class="mb-3">
                        <img id="detail-foto" src="images/default.png" class="shadow"
                            style="width: 150px; height: 150px; object-fit: cover; border-radius: 50%; border: 5px solid #fff;">
                    </div>
                    <h4 id="detail-nama" class="fw-bold mb-0 text-primary"></h4>
                    <p id="detail-nip" class="text-muted small mb-3"></p>
                    <div class="badge bg-soft-primary px-3 py-2 mb-4" id="detail-jabatan"
                        style="background-color: #e7f1ff; color: #0d6efd; border-radius: 10px;"></div>

                    <div class="row text-start g-3 px-3">
                        <div class="col-6">
                            <label class="small text-muted mb-0">Tempat/Tgl Lahir</label>
                            <p class="small fw-bold mb-0" id="detail-ttl"></p>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted mb-0">Jenis Kelamin</label>
                            <p class="small fw-bold mb-0" id="detail-jk"></p>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted mb-0">Pangkat/Gol</label>
                            <p class="small fw-bold mb-0" id="detail-pangkat"></p>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted mb-0">Pendidikan</label>
                            <p class="small fw-bold mb-0" id="detail-pendidikan"></p>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted mb-0">Unit Kerja</label>
                            <p class="small fw-bold mb-0" id="detail-unit"></p>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted mb-0">Status</label>
                            <p class="small fw-bold mb-0" id="detail-status-pegawai"></p>
                        </div>
                        <div class="col-12 border-top pt-2">
                            <label class="small text-muted mb-0">Kontak</label>
                            <p class="small fw-bold mb-0"><i class="fas fa-phone-alt me-2 text-success"></i><span
                                    id="detail-hp"></span></p>
                            <p class="small fw-bold mb-0"><i class="fas fa-envelope me-2 text-danger"></i><span
                                    id="detail-email"></span></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const modalPegawai = new bootstrap.Modal(document.getElementById('modalPegawai'));
        const modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));
        let currentPage = 1;

        function muatData(page = 1) {
            currentPage = page;
            const search = $('#search-pegawai').val();
            // Path handling: Check if we are in kepegawaian subfolder
            const ajaxUrl = window.location.pathname.includes('/kepegawaian/') ? 'proses_pegawai.php' : 'kepegawaian/proses_pegawai.php';

            $.ajax({
                url: ajaxUrl,
                type: 'GET',
                data: { action: 'muatData', page: page, search: search },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        $('#tabel-pegawai').html(res.data.table);
                        $('#paginasi-pegawai').html(res.data.pagination);
                        $('#info-entri').html(res.data.recordsInfo);
                    }
                }
            });
        }

        muatData();

        // Event Handlers
        $('#search-pegawai').on('keyup', function () {
            muatData(1);
        });

        $(document).on('click', '.page-link', function (e) {
            e.preventDefault();
            muatData($(this).data('page'));
        });

        $('#tombolTambahPegawai').click(function () {
            $('#formPegawai')[0].reset();
            $('#pegawai_id').val('');
            $('#foto_lama').val('');
            $('#preview-foto').attr('src', 'images/default.png');
            $('#modalPegawaiLabel').html('<i class="fas fa-user-plus me-2"></i> Tambah Pegawai Baru');
            modalPegawai.show();
        });

        $('#foto').change(function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#preview-foto').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        $('#formPegawai').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpan');

            $.ajax({
                url: 'proses_pegawai.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        modalPegawai.hide();
                        toastr.success(res.message);
                        muatData(currentPage);
                    } else {
                        toastr.error(res.message);
                    }
                }
            });
        });

        $(document).on('click', '.tombol-edit', function () {
            const id = $(this).data('id');
            $.ajax({
                url: 'proses_pegawai.php',
                type: 'GET',
                data: { action: 'ambil', id: id },
                dataType: 'json',
                success: function (res) {
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
                        $('#no_hp').val(d.no_hp);
                        $('#email').val(d.email);
                        $('#foto_lama').val(d.foto);

                        const foto = d.foto ? '../file/pegawai/' + d.foto : '../images/default.png';
                        $('#preview-foto').attr('src', foto);

                        $('#modalPegawaiLabel').html('<i class="fas fa-user-edit me-2"></i> Edit Data Pegawai');
                        modalPegawai.show();
                    }
                }
            });
        });

        $(document).on('click', '.tombol-view', function () {
            const id = $(this).data('id');
            $.ajax({
                url: 'proses_pegawai.php',
                type: 'GET',
                data: { action: 'ambil', id: id },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        const d = res.data;
                        $('#detail-nama').text(d.nm_pegawai);
                        $('#detail-nip').text('NIP: ' + (d.nip || '-'));
                        $('#detail-jabatan').text(d.jabatan || 'Belum diatur');
                        $('#detail-ttl').text((d.tempat_lahir || '-') + ', ' + (d.tgl_lahir || '-'));
                        $('#detail-jk').text(d.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan');
                        $('#detail-pangkat').text((d.pangkat || '-') + ' / ' + (d.golongan || '-'));
                        $('#detail-pendidikan').text(d.pendidikan || '-');
                        $('#detail-unit').text(d.unit_kerja || '-');
                        $('#detail-status-pegawai').text(d.status_pegawai || '-');
                        $('#detail-hp').text(d.no_hp || '-');
                        $('#detail-email').text(d.email || '-');

                        const foto = d.foto ? '../file/pegawai/' + d.foto : '../images/default.png';
                        $('#detail-foto').attr('src', foto);

                        modalDetail.show();
                    }
                }
            });
        });

        $(document).on('change', '.status-switch', function () {
            const id = $(this).data('id');
            const status = $(this).is(':checked') ? '1' : '0';
            $.ajax({
                url: 'proses_pegawai.php',
                type: 'POST',
                data: { action: 'ubah_status', id: id, status: status },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                    }
                }
            });
        });

        $(document).on('click', '.tombol-hapus', function () {
            const id = $(this).data('id');
            if (confirm('Yakin ingin menghapus data pegawai ini?')) {
                $.ajax({
                    url: 'proses_pegawai.php',
                    type: 'POST',
                    data: { action: 'hapus', id: id },
                    dataType: 'json',
                    success: function (res) {
                        if (res.status === 'success') {
                            toastr.success(res.message);
                            muatData(currentPage);
                        }
                    }
                });
            }
        });
    });
</script>

<style>
    .bg-soft-primary {
        background-color: #e7f1ff;
        color: #0d6efd;
    }

    .card-outline.primary {
        border-top: 3px solid #007bff;
    }

    .bg-menu-gradient {
        background: linear-gradient(135deg, #2c3e50 0%, #00d2ff 100%);
        color: #fff;
    }

    .modal-content {
        border: none;
    }

    .form-control,
    .form-select {
        border: 1px solid #dee2e6;
        padding: 0.6rem 1rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .1);
    }

    .btn-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
    }
</style>