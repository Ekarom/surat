<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Ganti Password</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Ganti Password</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>    

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="error-gradient-border" style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <center><h5 style='color: red;'><i class="icon fas fa-exclamation-triangle"></i> &nbsp; Peringatan Keamanan</h5>
                        Anda menggunakan password default. Demi keamanan, Anda <strong>wajib</strong> mengganti password sebelum melanjutkan.</center>
                    </div>

                    <div class="card">
                        <div class="card-header bg-menu-gradient text-center">
                            <i class="icon fas fa-key"></i>&nbsp;<b>Form Ganti Password</b>
                        </div>
                        <div class="card-body">
                            <form id="formGantiPasswordAwal">
                                <div class="mb-3">
                                    <label for="old_password" class="form-label">Password Default</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="old_password" name="old_password" required placeholder="Masukkan password saat ini">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('old_password')"><i class="fas fa-eye"></i></button>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Password Baru</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="Masukkan password baru yang kuat">
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
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Ulangi password baru">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')"><i class="fas fa-eye"></i></button>
                                    </div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i> Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
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

    $(document).ready(function() {
        // Strength Meter Logic
        $('#new_password').on('keyup change', function() {
            const password = $(this).val();
            const strengthBar = $('#passStrengthBar');
            const strengthText = $('#passStrengthText');
            let strength = 0;

            if (password.length > 5) strength += 1;
            if (password.length > 7) strength += 1;
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 1;
            if (password.match(/([a-zA-Z])/) && password.match(/([0-9])/)) strength += 1;
            if (password.match(/([!%&@#$^*?_~])/)) strength += 1;

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

        // Form Submit
        $('#formGantiPasswordAwal').on('submit', function(e) {
            e.preventDefault();
            
            const newPass = $('#new_password').val();
            const confirmPass = $('#confirm_password').val();

            if(newPass !== confirmPass) {
                toastr["error"]("Konfirmasi password tidak cocok!");
                return;
            }

            $.ajax({
                url: 'proses_password.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        toastr["success"]("Password berhasil diubah! Mengarahkan ke dashboard...");
                        setTimeout(function(){
                            // Reload halaman agar status force change hilang dan menu muncul kembali
                            window.location.href = 'dashboard.php'; 
                        }, 1500);
                    } else {
                        toastr["error"](response.message);
                    }
                },
                error: function() {
                    toastr["error"]("Terjadi kesalahan koneksi.");
                }
            });
        });
    });
</script>
