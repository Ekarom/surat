<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// PERBAIKAN: Gunakan dbconn.php sesuai file yang Anda miliki
if (file_exists('dbconn.php')) {
    include "dbconn.php";
} else {
    include "koneksi.php"; // Fallback jika dbconn tidak ada
}

// Cek conn untuk tampilan awal
if (isset($conn) && $conn->connect_error) {
    die("Koneksi database gagal saat memuat view: " . $conn->connect_error);
}

// --- Ambil Data Pengguna secara Statis ---
$query = "SELECT * FROM tb_user ORDER BY id ASC";
$result = $conn->query($query);
$dataRows = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $dataRows[] = $row;
    }
}
?>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manajemen User</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Manajemen User</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card ">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center flex-wrap">
                                <div>
                                    <button type="button" class="btn btn-primary btn-sm" id="tombolTambahUser">
                                        <i class="fas fa-plus"></i> Tambah Pengguna
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped" style="width:100%">
                                <thead class="box-shadow-0 bg-gradient-x-secondary">
                                    <tr class="text-white">
                                        <th width="50">No</th>
                                        <th>Username</th>
                                        <th>Nama Lengkap</th>
                                        <th>Level</th>
                                        <th>Last Login</th>
                                        <th>IP Address</th>
                                        <th>Status</th>
                                        <th>Edit</th>
                                        <th>Hapus</th>
                                        <th>Reset Pass</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    foreach ($dataRows as $row):
                                        $levelBadge = '';
                                        $lvl = (string) $row['level'];
                                        if ($lvl === '1') $levelBadge = '<span class="badge bg-danger text-white">Admin</span>';
                                        elseif ($lvl === '2') $levelBadge = '<span class="badge bg-warning text-dark">Staff</span>';
                                        elseif ($lvl === '3') $levelBadge = '<span class="badge bg-info text-white">User</span>';
                                        elseif ($lvl === '4') $levelBadge = '<span class="badge bg-success text-white">Guru</span>';

                                        $is_active = ($row['status'] == '1' || $row['status'] == 'Aktif');
                                        $isChecked = $is_active ? 'checked' : '';
                                        $switchButton = '
                                                    <div class="custom-control custom-switch d-flex justify-content-center">
                                                        <input class="custom-control-input status-switch" type="checkbox" 
                                                            data-id="' . $row['id'] . '" 
                                                            id="switch' . $row['id'] . '" 
                                                            ' . $isChecked . '>
                                                        <label class="custom-control-label" for="switch' . $row['id'] . '"></label>
                                                    </div>';
                                        
                                        $userData = [
                                            'id' => $row['id'],
                                            'nama' => htmlspecialchars($row['nama'] ?? '', ENT_QUOTES, 'UTF-8'),
                                            'userid' => htmlspecialchars($row['userid'] ?? '', ENT_QUOTES, 'UTF-8'),
                                            'nik' => htmlspecialchars($row['nik'] ?? '', ENT_QUOTES, 'UTF-8'),
                                            'email' => htmlspecialchars($row['email'] ?? '', ENT_QUOTES, 'UTF-8'),
                                            'level' => $row['level'],
                                            'status' => $is_active ? '1' : '0',
                                            'poto' => $row['poto'] ?? ''
                                        ];
                                        ?>
                                            <tr>
                                                <td class="text-center"><?php echo $no++; ?></td>
                                                <td class="fw-bold"><?php echo $userData['userid']; ?></td>
                                                <td><?php echo $userData['nama']; ?></td>
                                                <td class="text-center"><?php echo $levelBadge; ?></td>
                                                <td class="text-center small text-muted">
                                                    <?php echo ($row['last_login'] ?: '-'); ?>
                                                </td>
                                                <td class="text-center small"><?php echo ($row['ip'] ?: '-'); ?></td>
                                                <td class="text-center"><?php echo $switchButton; ?></td>
                                                <td class="text-center">
                                                    <span class="badge badge-warning badge-square tombol-edit"
                                                        data-id="<?php echo $userData['id']; ?>" 
                                                        title="Edit Data">
                                                        <i class="la la-edit"></i>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-danger badge-square tombol-hapus"
                                                        data-id="<?php echo $row['id']; ?>" title="Hapus User">
                                                        <i class="la la-trash"></i>
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge badge-info text-white badge-square tombol-reset-pass"
                                                        data-id="<?php echo $row['id']; ?>"
                                                        data-nama="<?php echo $userData['nama']; ?>"
                                                        title="Reset Password">
                                                        <i class="la la-key"></i>
                                                    </span>
                                                </td>
                                            </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.col-12 -->
        </div>
        <!-- /.row -->
</div>
<!-- /.container-fluid -->
</section>
<!-- /.content -->

<!-- =================================================================== -->
<!-- |                      MODAL NOTIFIKASI INFO                      | -->
<!-- =================================================================== -->
<div class="modal fade" id="infoModal" tabindex="-1" aria-labelledby="infoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="infoModalHeader">
                <h5 class="modal-title text-white" id="infoModalLabel">Notifikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="infoModalBody">
                <!-- Pesan akan ditampilkan di sini -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- |                      MODAL KONFIRMASI AKSI                      | -->
<!-- =================================================================== -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="confirmModalLabel">Notifikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="confirmModalBody">
                <!-- Pesan konfirmasi dinamis -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger custom" id="confirmModalButton">Hapus</button>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- |                      MODAL TAMBAH PENGGUNA                      | -->
<!-- =================================================================== -->
<div class="modal fade" id="modalTambah" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header box-shadow-0 bg-gradient-x-primary text-white">
                <h6 class="modal-title w-100 text-center text-white" id="modalTambahLabel"><i
                        class="la la-user-plus"></i> Tambah Pengguna Baru</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formTambahUser" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="simpan">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tambah_level"><i class="fas fa-user-shield"></i> Level Akses
                                <span class="text-danger">*</span></label>
                            <select class="custom-select" id="tambah_level" name="level" required>
                                <option value="">-- Pilih Level --</option>
                                <option value="1">Admin</option>
                                <option value="2">Staff</option>
                                <option value="3">User</option>
                                <option value="4">Guru</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tambah_nama"><i class="fas fa-user"></i> Nama Lengkap <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tambah_nama" name="nama" required
                                placeholder="Masukkan nama lengkap">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tambah_userid"><i class="fas fa-id-card"></i> User ID <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tambah_userid" name="userid" required
                                placeholder="Masukkan User ID">
                        </div>
                        <div class="col-md-6">
                            <label for="tambah_nik"><i class="fas fa-id-badge"></i> NRK / NIKI <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="tambah_nik" name="nik" required
                                placeholder="Masukkan NIK/NRK">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tambah_email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" class="form-control" id="tambah_email" name="email"
                                placeholder="Masukkan Email">
                        </div>
                        <div class="col-md-6">
                            <label for="tambah_password"><i class="fas fa-key"></i> Password</label>
                            <input type="password" class="form-control" id="tambah_password" name="password"
                                placeholder="Biarkan kosong untuk default">
                            <small class="text-muted" id="passwordHelpTambah">Default: sama dengan User ID</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="tambah_status"><i class="fas fa-toggle-on"></i> Status Akun
                                <span class="text-danger">*</span></label>
                            <select class="custom-select" id="tambah_status" name="status" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-camera"></i> Foto Profil</label>
                        <div id="drop-zone-tambah" class="border rounded p-3 text-center"
                            style="background-color: #f8f9fa; border-style: dashed !important; border-width: 2px !important;">
                            <input class="d-none" type="file" id="tambah_poto" name="poto" accept="image/*">
                            <div class="mb-2">
                                <img id="preview_tambah" src="" alt="Preview" class="img-thumbnail rounded-circle"
                                    style="width: 100px; height: 100px; object-fit: cover; display: none;">
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mb-2" id="btn_pilih_tambah">
                                <i class="fas fa-folder-open mr-1"></i> Pilih Foto...
                            </button>
                            <p class="text-muted small mb-0">atau drag & drop foto disini (Max 2MB)</p>
                            <div id="info_foto_tambah" class="mt-2 small text-primary"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary custom" form="formTambahUser"
                    id="btnSimpanTambah">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- =================================================================== -->
<!-- |                        MODAL EDIT PENGGUNA                      | -->
<!-- =================================================================== -->
<div class="modal fade" id="modalEdit" data-backdrop="static" data-keyboard="false" tabindex="-1"
    aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header box-shadow-0 bg-gradient-x-primary text-white">
                <h6 class="modal-title w-100 text-center text-white" id="modalEditLabel"><i class="la la-user-edit"></i>
                    Edit Data Pengguna</h6>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditUser" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="simpan">
                    <input type="hidden" name="id" id="edit_id">
                    <input type="hidden" name="poto_lama" id="edit_poto_lama">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_level"><i class="fas fa-user-shield"></i> Level Akses
                                <span class="text-danger">*</span></label>
                            <select class="custom-select" id="edit_level" name="level" required>
                                <option value="">-- Pilih Level --</option>
                                <option value="1">Admin</option>
                                <option value="2">Staff</option>
                                <option value="3">User</option>
                                <option value="4">Guru</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_nama"><i class="fas fa-user"></i> Nama Lengkap <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nama" name="nama" required
                                placeholder="Masukkan nama lengkap">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_userid"><i class="fas fa-id-card"></i> User ID <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_userid" name="userid" required
                                placeholder="Masukkan User ID">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_nik"><i class="fas fa-id-badge"></i> NRK / NIKI <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="edit_nik" name="nik" required
                                placeholder="Masukkan NIK/NRK">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_email"><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email"
                                placeholder="Masukkan Email">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_password"><i class="fas fa-key"></i> Ganti
                                Password</label>
                            <input type="password" class="form-control" id="edit_password" name="password"
                                placeholder="Kosongkan jika tidak diganti">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_status"><i class="fas fa-toggle-on"></i> Status Akun
                                <span class="text-danger">*</span></label>
                            <select class="custom-select" id="edit_status" name="status" required>
                                <option value="1">Aktif</option>
                                <option value="0">Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold"><i class="fas fa-camera"></i> Foto Profil</label>
                        <div id="drop-zone-edit" class="border rounded p-3 text-center"
                            style="background-color: #f8f9fa; border-style: dashed !important; border-width: 2px !important;">
                            <input class="d-none" type="file" id="edit_poto" name="poto" accept="image/*">
                            <div class="mb-2">
                                <img id="preview_edit" src="" alt="Preview" class="img-thumbnail rounded-circle"
                                    style="width: 100px; height: 100px; object-fit: cover; display: none;">
                                <div id="no_foto_edit" class="text-muted small">Belum ada foto</div>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm mb-2" id="btn_pilih_edit">
                                <i class="fas fa-folder-open mr-1"></i> Ganti Foto...
                            </button>
                            <p class="text-muted small mb-0">atau drag & drop foto disini (Max 2MB)</p>
                            <div id="info_foto_edit" class="mt-2 small text-primary"></div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Tutup</button>
                <button type="submit" class="btn btn-primary custom" form="formEditUser" id="btnSimpanEdit">Simpan
                    Perubahan</button>
            </div>
        </div>
    </div>
</div>

</div>
<!-- /.content-wrapper -->

<script>
    $(document).ready(function () {

        // Inisialisasi Instance Modal Bootstrap (Downgraded to jQuery for BS4)
        // const modalTambah = new bootstrap.Modal(document.getElementById('modalTambah'));
        // const modalEdit = new bootstrap.Modal(document.getElementById('modalEdit'));
        // const infoModal = new bootstrap.Modal(document.getElementById('infoModal'));
        // const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

        // --- FITUR: MODAL DRAGGABLE ---
        function makeModalsDraggable() {
            let activeModal = null;
            let offset = { x: 0, y: 0 };

            document.addEventListener('mousedown', function (e) {
                const header = e.target.closest('.modal-header');
                if (header && header.closest('.modal-dialog')) {
                    activeModal = header.closest('.modal-dialog');
                    const rect = activeModal.getBoundingClientRect();
                    offset.x = e.clientX - rect.left;
                    offset.y = e.clientY - rect.top;
                    document.body.style.userSelect = 'none';
                }
            });

            document.addEventListener('mousemove', function (e) {
                if (activeModal) {
                    e.preventDefault();
                    activeModal.style.margin = '0';
                    activeModal.style.top = `${e.clientY - offset.y}px`;
                    activeModal.style.left = `${e.clientX - offset.x}px`;
                }
            });

            document.addEventListener('mouseup', function () {
                activeModal = null;
                document.body.style.userSelect = '';
            });
        }

        // --- FUNGSI HELPER UI ---

        function showInfoModal(title, message, type = 'success') {
            $('#infoModalLabel').text(title);
            $('#infoModalBody').html(message);
            const header = $('#infoModalHeader');
            header.removeClass('bg-success bg-danger bg-warning text-white text-dark');
            if (type === 'success') header.addClass('bg-success text-white');
            else if (type === 'error') header.addClass('bg-danger text-white');
            else if (type === 'warning') header.addClass('bg-warning text-dark');
            else header.addClass('bg-info text-dark');
            $('#infoModal').modal('show');
        }

        function showConfirmModal(message, callback) {
            $('#confirmModalBody').html(message);
            $('#confirmModal').modal('show');
            $('#confirmModalButton').off('click').on('click', function () {
                $('#confirmModal').modal('hide');
                callback();
            });
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        // --- LOGIK PREVIEW & UPLOAD (Reusable) ---

        function setupPhotoUpload(type) {
            const dropZone = document.getElementById(`drop-zone-${type}`);
            const input = document.getElementById(`${type}_poto`);
            const btnPilih = document.getElementById(`btn_pilih_${type}`);
            const preview = document.getElementById(`preview_${type}`);
            const info = document.getElementById(`info_foto_${type}`);
            const noFoto = document.getElementById(`no_foto_${type}`);

            if (!dropZone || !input) return;

            $(input).change(function () {
                const file = this.files[0];
                if (file) {
                    if (file.size > 2097152) {
                        showInfoModal('Peringatan', 'Ukuran foto melebihi 2 MB!', 'warning');
                        this.value = '';
                        $(info).text('');
                        $(preview).hide();
                        if (noFoto) $(noFoto).show();
                        return;
                    }
                    const reader = new FileReader();
                    reader.onload = e => {
                        $(preview).attr('src', e.target.result).show();
                        if (noFoto) $(noFoto).hide();
                        $(info).text(`${file.name} (${formatFileSize(file.size)})`);
                    };
                    reader.readAsDataURL(file);
                }
            });

            $(btnPilih).click(() => input.click());

            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(e => {
                dropZone.addEventListener(e, ev => { ev.preventDefault(); ev.stopPropagation(); });
            });

            $(dropZone).on('dragenter dragover', function () {
                $(this).css({ 'background-color': '#e7f3ff', 'border-color': '#0d6efd' });
            }).on('dragleave drop', function () {
                $(this).css({ 'background-color': '#f8f9fa', 'border-color': '#dee2e6' });
            });

            dropZone.addEventListener('drop', e => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    $(input).trigger('change');
                }
            });
        }

        // --- INISIALISASI ---
        $('.content table.table').DataTable({
            scrollY: 450,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
            // fixedColumns: {
            // leftColumns: 3
            // }
        });
        makeModalsDraggable();
        setupPhotoUpload('tambah');
        setupPhotoUpload('edit');

        // --- EVENT HANDLERS ---

        // 1. Switch Status
        $(document).on('change', '.status-switch', function () {
            const id = $(this).data('id');
            const isChecked = $(this).is(':checked');
            const newStatus = isChecked ? '1' : '0';
            const checkbox = $(this);

            $.ajax({
                url: 'proses_user.php',
                type: 'POST',
                data: { action: 'ubah_status', id: id, status: newStatus },
                dataType: 'json',
                success: response => {
                    if (response.status === 'success') showInfoModal('Sukses', response.message, 'success');
                    else { showInfoModal('Gagal', response.message, 'error'); checkbox.prop('checked', !isChecked); }
                },
                error: () => { showInfoModal('Error', 'Kesalahan koneksi server.', 'error'); checkbox.prop('checked', !isChecked); }
            });
        });

        // 2. Tombol Tambah
        $('#tombolTambahUser').click(function () {
            $('#formTambahUser')[0].reset();
            $('#preview_tambah').hide().attr('src', '');
            $('#info_foto_tambah').text('');
            $('#modalTambah').modal('show');
        });

        // 3. Tombol Edit
        $('.content table.table tbody').on('click', '.tombol-edit', function () {
            const id = $(this).data('id');
            $.ajax({
                url: 'proses_user.php',
                type: 'GET',
                data: { action: 'ambil', id: id },
                dataType: 'json',
                success: data => {
                    if (!data || data.error) return showInfoModal('Error', 'Data tidak ditemukan', 'error');

            $('#edit_id').val(data.id);
            $('#edit_nama').val(data.nama);
            $('#edit_userid').val(data.userid);
            $('#edit_nik').val(data.nik);
            $('#edit_email').val(data.email);
            $('#edit_level').val(data.level);
                    $('#edit_status').val((data.status == '1' || data.status == 'Aktif') ? '1' : '0');
            $('#edit_poto_lama').val(data.poto);
            $('#edit_password').val('');

            if (data.poto) {
                $('#preview_edit').attr('src', 'file/profil/' + data.poto).show();
                $('#no_foto_edit').hide();
            } else {
                $('#preview_edit').hide();
                $('#no_foto_edit').show();
            }
            $('#info_foto_edit').text('');
            $('#modalEdit').modal('show');
                },
                error: () => showInfoModal('Error', 'Gagal mengambil data user.', 'error')
            });
        });

        // 4. Submit Forms
        $('#formTambahUser, #formEditUser').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const isEdit = this.id === 'formEditUser';
            const btn = isEdit ? $('#btnSimpanEdit') : $('#btnSimpanTambah');
            const btnText = btn.html();

            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

            $.ajax({
                url: 'proses_user.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: response => {
                    if (response.status === 'success') {
                        isEdit ? $('#modalEdit').modal('hide') : $('#modalTambah').modal('hide');
                        showInfoModal('Berhasil', response.message, 'success');
                        setTimeout(() => window.location.reload(), 1000);
                    } else {
                        showInfoModal('Gagal', response.message, 'error');
                        btn.prop('disabled', false).html(btnText);
                    }
                },
                error: () => {
                    showInfoModal('Error', 'Terjadi kesalahan sistem.', 'error');
                    btn.prop('disabled', false).html(btnText);
                }
            });
        });

        // 5. Hapus & Reset Pass (Tetap)
        $('.content table.table tbody').on('click', '.tombol-hapus', function () {
            const id = $(this).data('id');
            showConfirmModal('Yakin ingin menghapus pengguna ini?', () => {
                $.ajax({
                    url: 'proses_user.php',
                    type: 'POST',
                    data: { action: 'hapus', id: id },
                    dataType: 'json',
                    success: response => {
                        if (response.status === 'success') {
                            showInfoModal('Berhasil', response.message, 'success');
                            setTimeout(() => window.location.reload(), 1000);
                        } else showInfoModal('Gagal', response.message, 'error');
                    }
                });
            });
        });

        $('.content table.table tbody').on('click', '.tombol-reset-pass', function () {
            const id = $(this).data('id');
            const nama = $(this).data('nama');
            showConfirmModal('Reset password untuk <strong>' + nama + '</strong>?', () => {
                $.ajax({
                    url: 'proses_user.php',
                    type: 'POST',
                    data: { action: 'reset_password', id: id },
                    dataType: 'json',
                    success: response => showInfoModal('Notifikasi', response.message, response.status)
                });
            });
        });
    });
</script>