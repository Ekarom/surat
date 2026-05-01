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
                        <div class="card card-outline primary sm">
                            <div class="card-header bg-menu-gradient d-flex justify-content-between align-items-center">
                                <button type="button" class="btn btn-primary btn-sm" id="tombolTambahUser">
                                    <i class="fas fa-plus"></i> Tambah Pengguna
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive" id="data-pengguna">
                                    <!-- Tabel data pengguna akan dimuat di sini oleh AJAX -->
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
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                    </div>
                    <div class="modal-body" id="infoModalBody">
                        <!-- Pesan akan ditampilkan di sini -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary custom" data-bs-dismiss="modal">Tutup</button>
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
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                    </div>
                    <div class="modal-body" id="confirmModalBody">
                        <!-- Pesan konfirmasi dinamis -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary custom" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger custom" id="confirmModalButton">Hapus</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- =================================================================== -->
        <!-- |                  MODAL TAMBAH DAN EDIT PENGGUNA                 | -->
        <!-- =================================================================== -->
        <div class="modal fade" id="userModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-menu-gradient">
                        <h6 class="modal-title w-100 text-center" id="userModalLabel">Form User</h6>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                    </div>
                    <div class="modal-body">
                        <!-- Penting: enctype="multipart/form-data" sudah ada untuk handle file -->
                        <form id="formUser" enctype="multipart/form-data">
                            <input type="hidden" name="id" id="id_user">
                            <!-- Input hidden untuk menyimpan nama foto lama saat edit -->
                            <input type="hidden" name="foto_lama" id="foto_lama">

                            <!-- Preview Foto -->
                            <div class="mt-2 text-center">
                                <img id="preview_foto" src="" alt="Preview Foto" class="img-thumbnail rounded-circle" style="width: 140px; height: 140px; object-fit: cover; display: none;">
                                <p id="text-no-foto" class="text-muted small mt-1" style="display: none;">Belum ada foto</p>
                            </div>
        <br>
        <center><span id="badge-level" class="badge bg-danger" style="display: none;">Level: <span id="level-text"></span></span></center>
        <br>
                            <!-- Row 1: Level Akses & Nama Lengkap -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="level" class="form-label"><i class="fas fa-user-shield"></i> Level Akses <span class="text-danger">*</span></label>
                                    <select class="form-select" id="level" name="level" required>
                                        <option value="">-- Pilih Level --</option>
                                        <option value="1">Admin</option>
                                        <option value="2">Staff</option>
                                        <option value="3">User</option>
                                        <option value="4">Guru</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="nama" class="form-label"><i class="fas fa-user"></i> Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nama" name="nama" required placeholder="Masukkan nama lengkap">
                                </div>
                            </div>

                            <!-- Row 2: User ID & NRK/NIKI -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="userid" class="form-label"><i class="fas fa-id-card"></i> User ID <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="userid" name="userid" required placeholder="Masukkan User ID">
                                </div>
                                <div class="col-md-6">
                                    <label for="nik" class="form-label"><i class="fas fa-id-badge"></i> NRK / NIKI <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nik" name="nik" required placeholder="Masukkan NIK/NRK">
                                </div>
                            </div>

                            <!-- Row 3: Password Baru & Status Akun -->
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="status" class="form-label"><i class="fas fa-toggle-on"></i> Status Akun <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <option value="">-- Pilih Status --</option>
                                        <option value="1">Aktif</option>
                                        <option value="0">Nonaktif</option>
                                    </select>
                                </div>
                            </div>
                                <!-- Bagian Upload Foto -->
                            <div class="mb-3">
                                <label for="foto" class="form-label"><i class="fas fa-camera"></i> Foto Profil</label>
                                <div id="drop-zone-foto" class="border rounded p-3" style="background-color: #f8f9fa; transition: all 0.3s ease;">
                                    <input class="d-none" type="file" id="foto" name="poto" accept="image/*">
                                    <div class="d-flex align-items-center mb-2">
                                        <button type="button" class="btn btn-outline-primary btn-sm me-2" id="choose-foto-btn">
                                            <i class="fas fa-folder-open me-1"></i> Pilih Foto...
                                        </button>
                                        <span class="text-muted small">atau drag & drop foto disini.</span>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">
                                            <strong>Total:</strong> <span id="foto-size">0 B</span>
                                        </small>
                                    </div>
                                    <div id="foto-list" class="mb-2"></div>
                                    <small class="text-muted">
                                        Format: <strong>JPG, JPEG, PNG</strong>. Maksimal <strong>1 file</strong> dan total maksimal <strong>2.0 MB</strong>
                                    </small>
                                </div>
                            </div>

                            <hr>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary custom" data-bs-dismiss="modal">Tutup</button>
                        <button type="submit" class="btn btn-primary custom" form="formUser" id="tombolSimpan">Simpan</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- /.content-wrapper -->

    <script>
        $(document).ready(function() {
            // Cek ketersediaan Bootstrap 5
            if (typeof bootstrap === 'undefined') {
                console.error('Bootstrap 5 JS tidak dimuat. Fitur Modal mungkin tidak berfungsi.');
            }

            // Inisialisasi Instance Modal Bootstrap
            const userModal = new bootstrap.Modal(document.getElementById('userModal'));
            const infoModal = new bootstrap.Modal(document.getElementById('infoModal'));
            const confirmModal = new bootstrap.Modal(document.getElementById('confirmModal'));

            // --- FITUR BARU: MODAL DRAGGABLE (Vanilla JS) ---
            function makeModalsDraggable() {
                let activeModal = null;
                let offset = {
                    x: 0,
                    y: 0
                };

                document.addEventListener('mousedown', function(e) {
                    const header = e.target.closest('.modal-header');
                    if (header && header.closest('.modal-dialog')) {
                        activeModal = header.closest('.modal-dialog');
                        const rect = activeModal.getBoundingClientRect();
                        offset.x = e.clientX - rect.left;
                        offset.y = e.clientY - rect.top;
                        document.body.style.userSelect = 'none'; // Mencegah seleksi teks saat drag
                    }
                });

                document.addEventListener('mousemove', function(e) {
                    if (activeModal) {
                        e.preventDefault();
                        activeModal.style.margin = '0';
                        // Pastikan position absolute/fixed agar properti top/left berfungsi
                        // Bootstrap modal-dialog secara default relative, namun script ini memanipulasi top/left
                        // yang akan bekerja jika dikombinasikan dengan margin 0 atau position yang sesuai.
                        activeModal.style.top = `${e.clientY - offset.y}px`;
                        activeModal.style.left = `${e.clientX - offset.x}px`;
                    }
                });

                document.addEventListener('mouseup', function() {
                    activeModal = null;
                    document.body.style.userSelect = '';
                });
            }
            // --- AKHIR FITUR DRAGGABLE ---


            // --- FUNGSI HELPER ---

            function showInfoModal(title, message, type = 'success') {
                $('#infoModalLabel').text(title);
                $('#infoModalBody').html(message);
                const header = $('#infoModalHeader');

                header.removeClass('bg-success bg-danger bg-warning text-white text-dark');

                if (type === 'success') {
                    header.addClass('bg-success text-white');
                } else if (type === 'error') {
                    header.addClass('bg-danger text-white');
                } else if (type === 'warning') {
                    header.addClass('bg-warning text-dark');
                } else {
                    header.addClass('bg-info text-dark');
                }

                infoModal.show();
            }

            function showConfirmModal(message, callback) {
                $('#confirmModalBody').text(message);
                confirmModal.show();

                $('#confirmModalButton').off('click').on('click', function() {
                    confirmModal.hide();
                    callback();
                });
            }

            function muatData() {
                const container = $('#data-pengguna');
                // Optional: Tambahkan spinner kecil atau biarkan transparan jika ingin update halus
                // container.html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Memuat data...</p></div>');

                $.ajax({
                    url: 'proses_user.php',
                    type: 'GET',
                    data: {
                        action: 'muat'
                    },
                    success: function(response) {
                        container.html(response);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error AJAX (muatData):', textStatus, errorThrown);
                        container.html('<div class="text-center p-5 text-danger">Gagal memuat data.</div>');
                        showInfoModal('Error conn', 'Gagal mengambil data dari server.', 'error');
                    }
                });
            }


            // --- PREVIEW GAMBAR LOGIC ---
            $('#foto').change(function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#preview_foto').attr('src', e.target.result).show();
                        $('#text-no-foto').hide();
                    }
                    reader.readAsDataURL(file);
                } else {
                    // Jika user membatalkan pemilihan file, kembalikan ke kondisi sebelumnya
                    // Cek apakah sedang edit (ada foto lama) atau tambah baru
                    const fotoLama = $('#foto_lama').val();
                    if (fotoLama) {
                        $('#preview_foto').attr('src', 'file/profil/' + fotoLama).show();
                        $('#text-no-foto').hide();
                    } else {
                        $('#preview_foto').hide();
                        $('#text-no-foto').show();
                    }
                }
            });

            // --- DRAG AND DROP FOTO UPLOAD ---
            const dropZoneFoto = document.getElementById('drop-zone-foto');
            const fotoInput = document.getElementById('foto');
            const chooseFotoBtn = document.getElementById('choose-foto-btn');
            const fotoList = document.getElementById('foto-list');
            const fotoSizeDisplay = document.getElementById('foto-size');

            // Helper function to clear foto info
            function clearFotoInfo() {
                if (fotoSizeDisplay && fotoList) {
                    fotoSizeDisplay.textContent = '0 B';
                    fotoList.innerHTML = '';
                }
            }

            // Helper function to format file size
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 B';
                const k = 1024;
                const sizes = ['B', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
            }

            // Helper function to display foto info
            function displayFotoInfo(file) {
                if (!fotoSizeDisplay || !fotoList) return;
                
                const fileSize = formatFileSize(file.size);
                fotoSizeDisplay.textContent = fileSize;
                
                // Determine icon based on file type
                let iconClass = 'fa-file-image';
                if (file.type.includes('jpeg') || file.type.includes('jpg')) {
                    iconClass = 'fa-file-image';
                } else if (file.type.includes('png')) {
                    iconClass = 'fa-file-image';
                }
                
                fotoList.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between p-2 bg-white border rounded">
                        <div class="d-flex align-items-center">
                            <i class="fas ${iconClass} text-primary me-2"></i>
                            <span class="small">${file.name}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger p-0" id="remove-foto" title="Hapus foto">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;

                // Add event listener to remove button
                setTimeout(() => {
                    const removeBtn = document.getElementById('remove-foto');
                    if (removeBtn) {
                        removeBtn.addEventListener('click', function(e) {
                            e.stopPropagation();
                            fotoInput.value = '';
                            clearFotoInfo();
                            // Reset preview
                            $('#preview_foto').hide();
                            $('#text-no-foto').show();
                        });
                    }
                }, 0);
            }

            // Initialize drag and drop only if elements exist
            if (dropZoneFoto && fotoInput && chooseFotoBtn && fotoList && fotoSizeDisplay) {
                
                function preventDefaults(e) {
                    e.preventDefault();
                    e.stopPropagation();
                }

                function highlight(e) {
                    dropZoneFoto.style.backgroundColor = '#e7f3ff';
                    dropZoneFoto.style.borderColor = '#0d6efd';
                }

                function unhighlight(e) {
                    dropZoneFoto.style.backgroundColor = '#f8f9fa';
                    dropZoneFoto.style.borderColor = '#dee2e6';
                }

                function handleDrop(e) {
                    const dt = e.dataTransfer;
                    const files = dt.files;

                    if (files.length > 0) {
                        const file = files[0];
                        // Validate image types
                        const validTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                        if (validTypes.includes(file.type)) {
                            if (file.size > 2097152) { // 2 MB in bytes
                                showInfoModal('Peringatan', 'Ukuran foto melebihi batas maksimal 2 MB!', 'warning');
                                return;
                            }
                            fotoInput.files = files;
                            displayFotoInfo(file);
                            // Trigger change event to update preview
                            $(fotoInput).trigger('change');
                        } else {
                            showInfoModal('Peringatan', 'Hanya file JPG, JPEG, dan PNG yang diperbolehkan!', 'warning');
                        }
                    }
                }

                // Prevent default drag behaviors
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    dropZoneFoto.addEventListener(eventName, preventDefaults, false);
                    document.body.addEventListener(eventName, preventDefaults, false);
                });

                // Highlight drop zone when item is dragged over it
                ['dragenter', 'dragover'].forEach(eventName => {
                    dropZoneFoto.addEventListener(eventName, highlight, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dropZoneFoto.addEventListener(eventName, unhighlight, false);
                });

                // Handle dropped files
                dropZoneFoto.addEventListener('drop', handleDrop, false);

                // Click button to browse
                chooseFotoBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    fotoInput.click();
                });

                // Handle file selection via browse (update to show file info)
                fotoInput.addEventListener('change', function(e) {
                    if (this.files.length > 0) {
                        const file = this.files[0];
                        if (file.size > 2097152) {
                            showInfoModal('Peringatan', 'Ukuran foto melebihi batas maksimal 2 MB!', 'warning');
                            this.value = '';
                            clearFotoInfo();
                            return;
                        }
                        displayFotoInfo(file);
                    } else {
                        clearFotoInfo();
                    }
                });

                // Reset drop zone when modal is closed
                $('#userModal').on('hidden.bs.modal', function() {
                    clearFotoInfo();
                });
            }

            // --- INISIALISASI AWAL ---
            muatData();
            makeModalsDraggable();

            // --- EVENT HANDLERS ---

            // 0. LOGIKA SWITCH STATUS (BARU)
            $(document).on('change', '.status-switch', function() {
                var id = $(this).data('id');
                var isChecked = $(this).is(':checked');
                var newStatus = isChecked ? 'Aktif' : 'Nonaktif';
                var label = $(this).siblings('label'); // Ambil label di sebelah switch
                var checkbox = $(this);

                $.ajax({
                    url: 'proses_user.php',
                    type: 'POST',
                    data: {
                        action: 'ubah_status',
                        id: id,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status == 'success') {
                            // 1. Update label teks visual
                            label.text(newStatus);

                            // 2. Tampilkan Notifikasi Sukses (Sesuai Request)
                            showInfoModal('Sukses', response.message, 'success');

                        } else {
                            // Error dari server
                            showInfoModal('Gagal', response.message, 'error');
                            // Kembalikan posisi switch karena gagal
                            checkbox.prop('checked', !isChecked);
                        }
                    },
                    error: function() {
                        showInfoModal('Error', 'Terjadi kesalahan koneksi server.', 'error');
                        // Kembalikan posisi switch karena error
                        checkbox.prop('checked', !isChecked);
                    }
                });
            });

            // 1. Tombol Tambah User
            $('#tombolTambahUser').click(function() {
                $('#formUser')[0].reset();
                $('#id_user').val('');
                $('#foto_lama').val('');
                $('#userModalLabel').html('<i class="fas fa-user-plus"></i> Tambah Pengguna Baru');
                
                // Hide level badge for new user
                $('#badge-level').hide();

                // Set Default Values untuk Level dan Status
                $('#level').val('3'); // Default User
                $('#status').val('1'); // Default Aktif

                // Reset Preview Gambar
                $('#preview_foto').attr('src', '').hide();
                $('#text-no-foto').show().text('Belum ada foto dipilih');

                // Reset upload zone display
                clearFotoInfo();

                // Validasi Password removed since fields are deleted
                $('#passwordHelp').text('Biarkan kosong untuk password default (sama dengan User ID).');

                userModal.show();
            });

            // 2. Submit Form (Simpan/Edit)
            $('#formUser').submit(function(e) {
                e.preventDefault();

                // Password validation removed as fields are deleted from form

                const formData = new FormData(this);
                formData.append('action', 'simpan');

                const btnSimpan = $('#tombolSimpan');
                const btnTextAsli = btnSimpan.html();
                btnSimpan.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');

                userModal.hide();

                $.ajax({
                    url: 'proses_user.php',
                    type: 'POST',
                    data: formData,
                    processData: false, // Penting untuk upload file
                    contentType: false, // Penting untuk upload file
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            muatData();
                            showInfoModal('Notifikasi', response.message, 'success');
                        } else {
                            showInfoModal('Notifikasi', response.message, 'error');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error('Error AJAX (simpan):', textStatus, jqXHR.responseText);
                        showInfoModal('Error System', 'Terjadi kesalahan saat menyimpan data.', 'error');
                    },
                    complete: function() {
                        btnSimpan.prop('disabled', false).html(btnTextAsli);
                    }
                });
            });

            // 3. Tombol Edit
            $('#data-pengguna').on('click', '.tombol-edit', function() {
                const id = $(this).data('id');

                $.ajax({
                    url: 'proses_user.php',
                    type: 'GET',
                    data: {
                        action: 'ambil',
                        id: id
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (!data || data.error) {
                            showInfoModal('Error', data.error || 'Data tidak ditemukan.', 'error');
                            return;
                        }

                        $('#id_user').val(data.id);
                        $('#nama').val(data.nama);
                        $('#userid').val(data.userid); // Pastikan ini userid, bukan username
                        $('#nik').val(data.nik); // Pastikan ini nik bukan null
                        $('#level').val(data.level);

                        // Show and update level badge
                        let levelText = '';
                        if (data.level == '1') {
                            levelText = 'Administrator';
                        } else if (data.level == '2') {
                            levelText = 'Staff';
                        } else if (data.level == '4') {
                            levelText = 'Guru';
                        } else {
                            levelText = 'User';
                        }
                        $('#level-text').text(levelText);
                        $('#badge-level').show();

                        // Normalisasi Status untuk Select Option Modal
                        // Pastikan logic ini sesuai dengan apa yang dikirim server
                        let statusVal = data.status;
                        if (statusVal == 'Aktif') statusVal = '1';
                        if (statusVal == 'Nonaktif') statusVal = '0';
                        $('#status').val(statusVal);

                        $('#foto_lama').val(data.poto); // Simpan nama file lama

                        // Tampilkan foto jika ada
                        if (data.poto) {
                            // Asumsi foto disimpan di folder "file/profil/"
                            $('#preview_foto').attr('src', 'file/profil/' + data.poto).show();
                            $('#text-no-foto').hide();
                        } else {
                            $('#preview_foto').hide();
                            $('#text-no-foto').show().text('User ini belum memiliki foto');
                        }

                        // Reset upload zone display
                        clearFotoInfo();

                        $('#userModalLabel').html('<i class="fas fa-user-edit"></i> Edit Data Pengguna');
                        $('#password').prop('required', false).val('');
                        $('#confirm_password').prop('required', false).val('');
                        $('#passwordHelp').text('Kosongkan jika tidak ingin mengubah password.');

                        userModal.show();
                    },
                    error: function() {
                        showInfoModal('Error', 'Gagal mengambil data user.', 'error');
                    }
                });
            });

            // 4. Tombol Hapus
            $('#data-pengguna').on('click', '.tombol-hapus', function() {
                const id = $(this).data('id');

                showConfirmModal('Yakin ingin menghapus pengguna ini?', function() {
                    $.ajax({
                        url: 'proses_user.php',
                        type: 'POST',
                        data: {
                            action: 'hapus',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                muatData();
                                showInfoModal('Notifikasi', response.message, 'success');
                            } else {
                                showInfoModal('Notifikasi', response.message, 'error');
                            }
                        },
                        error: function() {
                            showInfoModal('Error', 'Gagal menghapus data.', 'error');
                        }
                    });
                });
            });

            // 5. Tombol Reset Password
            $('#data-pengguna').on('click', '.tombol-reset-pass', function() {
                const id = $(this).data('id');
                const nama = $(this).data('nama');

                showConfirmModal('Yakin ingin mereset password untuk <strong>' + nama + '</strong>?<br><small class="text-muted">Password akan direset ke default.</small>', function() {
                    $.ajax({
                        url: 'proses_user.php',
                        type: 'POST',
                        data: {
                            action: 'reset_password',
                            id: id
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                showInfoModal('Notifikasi', response.message, 'success');
                            } else {
                                showInfoModal('Notifikasi', response.message, 'error');
                            }
                        },
                        error: function() {
                            showInfoModal('Error', 'Gagal mereset password.', 'error');
                        }
                    });
                });
            });

        });
    </script>