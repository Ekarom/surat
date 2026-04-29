    <?php

// --- 1. KONEKSI DATABASE ---
// Sesuaikan path ini
if (file_exists("dbconn.php")) {
    include "dbconn.php";
} elseif (file_exists("dbconn.php")) {
    include "dbconn.php";
} else {
    // Fallback koneksi manual (sesuaikan dengan upload.php)
    $conn = mysqli_connect("localhost", "root", "", "surat");
}

// --- 2. AMBIL ID USER ---
// Prioritas: Session -> Default ID (untuk testing/debug)
$id_user = 0;

// Cek session key yang mungkin dipakai
$possible_keys = ['id', 'user_id', 'id_user', 'admin_id'];
foreach ($possible_keys as $key) {
    if (isset($_SESSION[$key])) {
        $id_user = $_SESSION[$key];
        break;
    }
}

// JIKA MASIH KOSONG, PAKAI ID 4 (User Admin di database Anda) UNTUK TESTING
if ($id_user == 0) {
    $id_user = 4; 
}

// --- 3. QUERY DATA USER ---
$query = mysqli_query($conn, "SELECT * FROM tb_user WHERE id = '$id_user'");
$data  = mysqli_fetch_array($query);

// Data untuk ditampilkan
$nama_user  = $data['nama'] ?? "User Tidak Ditemukan";
$level_user = ($data['level'] ?? 0) == 1 ? "Administrator" : "Staff";
$status_user= ($data['status'] ?? 0) == 1 ? "Aktif" : "Non-Aktif";
$foto_db    = $data['poto'] ?? ""; // Nama file dari database (misal: user_123.jpg)
$email    = $data['email'] ?? ""; // Nama file dari database (misal: user_123.jpg)
$ip    = $data['ip'] ?? ""; // Nama file dari database (misal: user_123.jpg)
$log    = $data['last_login'] ?? ""; // Nama file dari database (misal: user_123.jpg)


// --- 4. LOGIKA URL FOTO ---
// Cek apakah ada foto di DB dan filenya benar-benar ada di folder
$path_folder = "file/profil/";
$path_file   = $path_folder . $foto_db;
$foto_url    = "";

if (!empty($foto_db) && file_exists($path_file)) {
    // Jika foto ada di folder file/profil/
    $foto_url = $path_file; 
} else {
    // Jika tidak ada, pakai avatar default online
    $foto_url = "images/male.png";
}
?>

    <style>
.profile-card {
            background-color: #FFFFFF;
            border: 1px solid #495057;
            border-radius: 0.75rem;
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
        }

        /* Gambar Profil */
        .profile-img-container {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto 1rem;
        }

        .profile-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 4px solid #495057;
        }

        .upload-btn-wrapper {
            margin-top: 10px;
        }

        /* Garis pemisah horizontal */
        .hr-custom {
            border-top: 1px solid #6c757d;
            opacity: 0.5;
            margin: 1.5rem 0;
        }

        /* Tombol dengan gaya modern */
        .btn-custom {
            border-radius: 0.5rem;
            transition: all 0.2s;
            font-weight: 500;
        }
        
        .btn-custom:hover {
            transform: scale(1.02);
        }

        /* Styling untuk progress bar upload */
        .progress {
            background-color: #495057;
            height: 10px;
            border-radius: 5px;
            margin-top: 10px;
        }

        /* Toastr notification style override */
        .toast-top-center {
            top: 20px;
        }
        
        .text-label {
            color: #adb5bd;
            font-weight: 500;
        }
    </style>


 <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Profil</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Profil</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>    

<div class="container pb-5">
        <div class="row">
            <div class="col-lg-4 mb-4">
                <div class="card profile-card p-3 text-center h-100">
                    <div class="card-body">
                        <div class="profile-img-container">
                            <!-- BAGIAN PENTING: Menampilkan variabel PHP $foto_url -->
                            <img src="<?php echo $foto_url; ?>" class="rounded-circle img-fluid" alt="profileImage" id="profileImage">
                        </div>
                        
                        <!-- Menampilkan Nama dari PHP -->
                        <h4 class="mt-3 mb-1 fw-bold text-dark"><?php echo $nama_user; ?></h4> 
                        <p class="badge bg-menu-gradient"><?php echo $level_user; ?></p>

                        <div class="upload-section mt-4">
                            <label for="photo1" class="btn btn-primary btn-custom w-100 mb-2">
                                <i class="fas fa-camera me-2"></i>Ubah Foto
                            </label>
                            
                             <!-- Tombol Modal Ganti Password -->
                             <button type="button" class="btn btn-danger w-100 mb-2" data-bs-toggle="modal" data-bs-target="#gantiPass">
                                <i class="fas fa-lock me-2"></i>Ganti Password
                            </button>
                            
                            <!-- Input File -->
                            <input type="file" id="photo1" class="d-none" accept="image/*" onchange="uploadFile()">                    
                            
                            <div id="progressContainer" style="display:none;">
                                <div class="progress mt-2" style="height: 10px;">
                                    <div id="progressBar" class="progress-bar bg-success" role="progressbar" style="width: 0%"></div>
                                </div>
                                <small id="status" class="d-block mt-2 text-muted">Menunggu...</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card profile-card h-100">
                    <div class="card-header pt-4 px-4">
                        <h5 class="mb-0 text-dark"><i class="fas fa-user-circle me-2 text-primary"></i>Informasi Pribadi</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-sm-4"><p class="mb-0 text-label">Nama Lengkap</p></div>
                            <div class="col-sm-8"><p class="mb-0 fw-bold text-dark"><?php echo $nama_user; ?></p></div>
                        </div>
                        <hr class="hr-custom">
                        <div class="row align-items-center">
                            <div class="col-sm-4"><p class="mb-0 text-label">Level</p></div>
                            <div class="col-sm-8"><p class="mb-0 text-dark"><?php echo $level_user; ?></p></div>
                        </div>
                        <hr class="hr-custom">
                        <div class="row align-items-center">
                            <div class="col-sm-4"><p class="mb-0 text-label">Status</p></div>
                            <div class="col-sm-8"><span class="badge bg-success"><?php echo $status_user; ?></span></div>
                        </div>
                        <hr class="hr-custom">
                        <div class="row align-items-center">
                            <div class="col-sm-4"><p class="mb-0 text-label">Email</p></div>
                            <div class="col-sm-8"><p class="mb-0 text-dark"><?php echo $email; ?></span></div>
                        </div>
                        <hr class="hr-custom">
                        <div class="row align-items-center">
                            <div class="col-sm-4"><p class="mb-0 text-label">Log</p></div>
                            <div class="col-sm-8"><p class="mb-0 text-dark"><?php echo $log; ?></span></div>
                        </div>
                    </div>
                </div>
            </div>
                


            </div>
        </div>
    </div>



    <div class="modal fade" id="gantiPass" tabindex="-1" aria-labelledby="modalGantiPasswordLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <b class="modal-title" id="modalGantiPasswordLabel"><i class="fas fa-lock me-2"></i>Ganti Password</b>
                    <?php if (!isset($_GET['changepass'])): ?>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    <?php endif; ?>
                </div>
                <div class="modal-body">
                    <?php if (isset($_GET['changepass'])): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i> <strong>Peringatan Keamanan!</strong><br>
                        Anda masih menggunakan password default. Silakan ganti password Anda untuk melanjutkan.
                    </div>
                    <?php endif; ?>
                    <form id="formGantiPassword">
                        <div class="mb-3">
                            <label for="old_password" class="form-label">Password Lama</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="old_password" name="old_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('old_password')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="new_password" name="new_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('new_password')"><i class="fas fa-eye"></i></button>
                            </div>
                            <!-- Strength Meter -->
                            <div class="progress mt-1" style="height: 5px;">
                                <div id="passStrengthBar" class="progress-bar" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small id="passStrengthText" class="text-muted" style="font-size: 0.8em;">Strength: -</small>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')"><i class="fas fa-eye"></i></button>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger">Simpan </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-center", "timeOut": "3000" };

        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            if (field.type === "password") {
                field.type = "text";
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = "password";
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Check Password Strength
        $(document).ready(function() {
            // Auto Open Modal if forced
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('changepass')) {
                var myModal = new bootstrap.Modal(document.getElementById('gantiPass'), {
                    backdrop: 'static',
                    keyboard: false
                });
                myModal.show();
            }

            $('#new_password').on('keyup change', function() {
                const password = $(this).val();
                const strengthBar = $('#passStrengthBar');
                const strengthText = $('#passStrengthText');
                let strength = 0;

                // Strength Calculation
                if (password.length > 5) strength += 1;
                if (password.length > 7) strength += 1;
                
                // Matches letters and numbers mixed
                if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
                if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1;
                
                // Matches special characters
                if (password.match(/([!%&@#$^*?_~])/)) strength += 1;

                // Update UI
                if (password.length < 1) {
                    strengthBar.css('width', '0%').removeClass().addClass('progress-bar');
                    strengthText.text('Strength: -').css('color', '#6c757d');
                } else if (strength < 2) {
                    strengthBar.css('width', '20%').removeClass().addClass('progress-bar bg-danger');
                    strengthText.html('Strength: <b>Low</b>').css('color', '#dc3545');
                } else if (strength == 2) {
                    strengthBar.css('width', '40%').removeClass().addClass('progress-bar bg-warning');
                    strengthText.html('Strength: <b>Medium</b>').css('color', '#ffc107');
                } else if (strength >= 3 && strength < 5) {
                    strengthBar.css('width', '60%').removeClass().addClass('progress-bar bg-info');
                    strengthText.html('Strength: <b>Good</b>').css('color', '#17a2b8');
                } else {
                    strengthBar.css('width', '100%').removeClass().addClass('progress-bar bg-success');
                    strengthText.html('Strength: <b>Strong</b>').css('color', '#28a745');
                }
            });
        });

        // Handle Form Ganti Password
        $('#formGantiPassword').on('submit', function(e) {
            e.preventDefault();
            
            const oldPass = $('#old_password').val();
            const newPass = $('#new_password').val();
            const confirmPass = $('#confirm_password').val();

            if(newPass !== confirmPass) {
                toastr["error"]("Konfirmasi password baru tidak cocok!");
                return;
            }

            $.ajax({
                url: 'proses_password.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        toastr["success"](response.message);
                        $('#formGantiPassword')[0].reset();
                        
                        // Cek apakah ini forced change
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.has('changepass')) {
                            setTimeout(function(){
                                window.location.href = 'dashboard.php?page=profil'; // Hapus param changepass
                            }, 1500);
                        } else {
                            // Tutup Modal
                            $('#gantiPass').modal('hide');
                        }
                    } else {
                        toastr["error"](response.message);
                    }
                },
                error: function() {
                    toastr["error"]("Terjadi kesalahan koneksi.");
                }
            });
        });

        function uploadFile() {
   
            const fileInput = document.getElementById("photo1");
            const file = fileInput.files[0];
            if (!file) return;

            if (!file.type.match('image.*')) {
                toastr["error"]("Hanya file gambar yang diperbolehkan!"); return;
            }

            $("#progressContainer").fadeIn();

            // Preview Lokal sebelum upload
            const reader = new FileReader();
            reader.onload = function(e) { document.getElementById('profileImage').src = e.target.result; }
            reader.readAsDataURL(file);

            const formData = new FormData();
            formData.append("photo1", file);

            $.ajax({
                url: 'uploads.php', 
                type: 'POST',
                data: formData,
                contentType: false, cache: false, processData: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            const percent = Math.round((evt.loaded / evt.total) * 100);
                            document.getElementById("progressBar").style.width = percent + "%";
                            document.getElementById("status").innerHTML = `<span class="text-info">Mengupload... ${percent}%</span>`;
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    if(response.status === 'success'){
                        // Update gambar dengan cache busting agar tidak load gambar lama
                        if(response.file_path) {
                            const newSrc = response.file_path + '?t=' + new Date().getTime();
                            document.getElementById('profileImage').src = newSrc;
                        }
                        
                        // Reset Progress
                        document.getElementById("status").innerHTML = "<span class='text-success fw-bold'>Selesai!</span>";
                        toastr["success"]("Foto profil berhasil diperbarui!");
                        setTimeout(() => { $("#progressContainer").fadeOut(); }, 2000);
                    } else {
                        toastr["warning"](response.message);
                        $("#progressContainer").fadeOut();
                    }
                },
                error: function() {
                    toastr["error"]("Gagal koneksi ke server.");
                    $("#progressContainer").fadeOut();
                }
            });
        }
    </script>