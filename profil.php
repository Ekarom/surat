<?php
if (file_exists("dbconn.php")) {
    include_once "dbconn.php";
} else {
    $conn = mysqli_connect("localhost", "root", "", "surat");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_user = 0;
$possible_keys = ['id', 'user_id', 'id_user', 'admin_id'];
foreach ($possible_keys as $key) {
    if (isset($_SESSION[$key])) {
        $id_user = $_SESSION[$key];
        break;
    }
}

if ($id_user == 0) {
    $id_user = 4;
}

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = $id_user;
}

$query = mysqli_query($conn, "SELECT * FROM tb_user WHERE id = '$id_user'");
$data = mysqli_fetch_array($query);

$nama = $data['nama'] ?? "";
$poto = $data['poto'] ?? "";
$lv = $data['level'] ?? "";
$userid = $data['userid'] ?? "";
$log = $data['last_login'] ?? "-";
?>

<style>
    .profile-img-container {
        position: relative;
        width: 130px;
        height: 130px;
        margin: 0 auto 15px;
        cursor: pointer;
        overflow: hidden;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .profile-img-container:hover {
        border-color: #007bff;
        transform: scale(1.02);
        box-shadow: 0 6px 20px rgba(0, 123, 255, 0.2);
    }

    .profile-img-container .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        opacity: 0;
        transition: opacity 0.3s ease;
        color: #fff;
    }

    .profile-img-container:hover .overlay {
        opacity: 1;
    }

    .profile-img-container .overlay i {
        font-size: 32px;
        margin-bottom: 5px;
    }

    .profile-img-container .overlay span {
        font-size: 10px;
        text-transform: uppercase;
        font-weight: bold;
    }

    .profile-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Profil Saya</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">User Profile</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <!-- KOLOM KIRI -->
                <div class="col-md-4 col-lg-3">
                    <!-- Profile Card -->
                    <div class="card card navy card-outline card-sm">
                        <div class="card-body box-profile">
                            <div class="text-center mb-3">
                                <div class="position-relative d-inline-block">
                                    <label for="photoInput" class="btn btn-sm btn-info position-absolute shadow-sm"
                                        style="bottom: 0; right: 0; border-radius: 50%; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; z-index: 10;"
                                        title="Ubah Foto">
                                        <i class="la la-camera"></i>
                                    </label>
                                    <input type="file" id="photoInput" style="display: none;" accept="image/*"
                                        onchange="uploadFile()">
                                    <button type="button" class="btn btn-link p-0"
                                        onclick="document.getElementById('photoInput').click()">
                                        <div id="exisImage">
                                            <?php
                                            $path_folder = "file/profil/";
                                            if (!empty($poto) && file_exists($path_folder . $poto)) {
                                                echo "<img src='$path_folder$poto' class='profile-user-img img-fluid img-circle shadow-sm border-0' style='width: 120px; height: 120px; object-fit: cover;' alt='User profile picture'>";
                                            } else {
                                                echo "<img src='images/male.png' class='profile-user-img img-fluid img-circle shadow-sm border-0' style='width: 120px; height: 120px; object-fit: cover;' alt='Default profile picture'>";
                                            }
                                            ?>
                                        </div>
                                    </button>
                                </div>
                                <h3 id="status" class="mt-2"></h3>
                            </div>

                            <div class="text-center">
                                <h3 class='profile-username font-weight-bold'>
                                    <?php echo !empty($nama) ? $nama : 'User'; ?>
                                </h3>
                                <p class="text-muted small">
                                    <i class="las la-user-tag"></i>
                                    <?php
                                    if (isset($lv)) {
                                        if ($lv == "1")
                                            echo "Administrator";
                                        elseif ($lv == "2")
                                            echo "Staff";
                                        elseif ($lv == "3")
                                            echo "User";
                                    }
                                    ?>
                                </p>
                            </div>

                            <!-- Upload Progress -->
                            <div class="upload-feedback mb-2 text-center">
                                <div class="progress mb-1" id="progress_wrapper"
                                    style="height: 6px; display: none; border-radius: 10px;">
                                    <div id="progressBar"
                                        class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                                        role="progressbar" style="width: 0%"></div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-secondary btn-block py-2 fw-bold shadow-sm"
                                data-toggle="modal" data-target="#gantiPass"
                                style="border-radius: 10px; background-color: #6c757d;">
                                Ganti Password
                            </button>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0">
                        <div class="card-header box-shadow-0 bg-gradient-x-info">
                            <h5 class="card-title text-white mb-0">About Me</h5>
                        </div>
                        <div class="card-body">
                            <strong><i class="fas fa-book mr-1"></i> Nama</strong>
                            <p class="text-muted"><?php echo !empty($nama) ? $nama : '-'; ?></p>
                            <hr>

                            <strong><i class="fas fa-file-alt mr-1"></i> User Id</strong>
                            <p class="text-muted"><?php echo !empty($userid) ? $userid : '-'; ?></p>
                            <hr>

                            <strong><i class="fas fa-pencil-alt mr-1"></i> Level</strong>
                            <p class="text-muted">
                                <?php
                                if (isset($lv)) {
                                    if ($lv == "1") {
                                        echo "<label>Admin</label>";
                                    } elseif ($lv == "2") {
                                        echo "<label>User</label>";
                                    } elseif ($lv == "3") {
                                        echo "<label>Staff</label>";
                                    }
                                }
                                ?>
                            </p>
                            <hr>

                            <strong><i class="far fa-file-alt mr-1"></i> Log</strong>
                            <p class="text-muted mb-0"><?php echo !empty($log) ? $log : '-'; ?></p>
                        </div>
                    </div>


                </div>
                <!-- END LEFT COLUMN -->

                <!-- ==============================
                 RIGHT COLUMN
                 ============================== -->
                <div class="col-md-9">
                    <div class="card shadow-sm border-0">
                        <div class="card-header box-shadow-0 bg-gradient-x-info p-2">
                            <ul class="nav nav-pills">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#activity" data-toggle="tab">Staff LV</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane active" id="activity">
                                    <div class="post">
                                        <div class="user-block">
                                            <?php if (isset($lv)) {
                                                if ($lv == "1") { ?>
                                                    <span class="username">Administrator</span><br>
                                                    <span class="description">Super Admin</span>
                                                <?php } elseif ($lv == "2") { ?>
                                                    <span class="username">Staff</span><br>
                                                    <span class="description">Staff User</span>
                                                <?php }
                                            } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- END RIGHT COLUMN -->

            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </section>
</div>

<!-- Modal Ganti Password -->
<div class="modal fade" id="gantiPass" tabindex="-1" role="dialog" aria-labelledby="gantiPassLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header box-shadow-0 bg-gradient-x-info text-white">
                <b id="gantiPassLabel">Ganti Password</b>
            </div>
            <form id="formGantiPassword">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="old_password">Password Saat Ini</label>
                        <input type="password" class="form-control" id="old_password" name="old_password" required
                            placeholder="Masukkan password saat ini">
                    </div>
                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required
                            placeholder="Masukkan password baru">
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                            required placeholder="Ulangi password baru">
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // ==========================================
        // PASSWORD CHANGE HANDLER
        // ==========================================
        $('#formGantiPassword').on('submit', function (e) {
            e.preventDefault();
            if ($('#new_password').val() !== $('#confirm_password').val()) {
                toastr["error"]("Konfirmasi password tidak cocok!");
                return;
            }

            const btn = $(this).find('button[type="submit"]');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Memproses...');

            $.ajax({
                url: 'proses_password.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        toastr["success"](res.message);
                        $('#gantiPass').modal('hide');
                        $('#formGantiPassword')[0].reset();
                    } else {
                        toastr["error"](res.message);
                    }
                },
                error: function () { toastr["error"]("Terjadi kesalahan koneksi."); },
                complete: function () { btn.prop('disabled', false).text('Simpan Perubahan'); }
            });
        });

        // ==========================================
        // PHOTO UPLOAD HANDLER
        // ==========================================
        window.uploadFile = function () {
            const fileInput = document.getElementById("photoInput");
            const file = fileInput.files[0];
            if (!file) return;

            $("#progress_wrapper").show();
            $("#status").html("<span class='text-muted small'>Memproses...</span>");

            const formdata = new FormData();
            formdata.append("photo1", file);

            $.ajax({
                url: 'uploads.php',
                type: 'POST',
                data: formdata,
                contentType: false,
                processData: false,
                xhr: function () {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (e) {
                        if (e.lengthComputable) {
                            const percent = (e.loaded / e.total) * 100;
                            $("#progressBar").css("width", Math.round(percent) + "%");
                            $("#status").html("<span class='text-primary small'>Mengunggah: " + Math.round(percent) + "%</span>");
                            $("#loaded_n_total").html((e.loaded / 1024).toFixed(1) + " / " + (e.total / 1024).toFixed(1) + " KB");
                        }
                    }, false);
                    return xhr;
                },
                success: function (response) {
                    if (response.status === 'success') {
                        const newImgUrl = response.url + "?t=" + new Date().getTime();
                        $("#exisImage").html("<img src='" + newImgUrl + "' alt='Photo'>");
                        $("#status").html("<span class='text-success small'>Berhasil Diperbarui!</span>");
                        toastr.success("Foto profil berhasil diperbarui.");
                        setTimeout(function () {
                            $("#progress_wrapper").fadeOut();
                            $("#loaded_n_total").html("");
                            $("#status").html("");
                        }, 2000);
                    } else {
                        $("#status").html("<span class='text-danger small'>" + response.message + "</span>");
                        toastr.error(response.message);
                        $("#progressBar").addClass("bg-danger");
                    }
                },
                error: function () {
                    $("#status").html("<span class='text-danger small'>Gagal terhubung</span>");
                    toastr.error("Gagal terhubung ke server.");
                }
            });
        };

    });
</script>