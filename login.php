<?php
// 1. session_start() wajib dipanggil sebelum menggunakan $_SESSION
session_start();
require_once 'dbconn.php';
// 2. Captcha is now handled by captcha_img.php
// (Session will be set when the image is requested)

// --- Logika Pesan Error dari Session (Login Process) ---
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']); // Hapus setelah diambil
}
?>
<style>
    /* Animated gradient border for error messages */
    .error-gradient-border {
        position: relative;
        background-color: transparent;
        padding: 17px;
        border-radius: 10px;
        margin-top: 10px;
        color: #fc0505ff;
        font-size: 14px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .error-gradient-border strong {
        color: #ff0000;
        font-weight: bold;
    }

    .error-gradient-border::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(45deg, #ff0000, #ff6600, #000000ff, #ff0000, #ff6600);
        background-size: 400% 400%;
        border-radius: 10px;
        z-index: -2;
        animation: gradientMove 5s ease infinite;
    }

    .error-gradient-border::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 2px;
        right: 2px;
        bottom: 2px;
        background-color: rgba(0, 0, 0, 0.9);
        border-radius: 8px;
        z-index: -1;
    }

    .error-gradient-border {
        z-index: 1;
    }

    @keyframes gradientMove {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }
</style>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Arsip Persuratan</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Icons -->
    <link rel="icon" type="image/png" href="images/logodik.png">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="plugins/iconic/css/material-design-iconic-font.min.css">

    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="plugins/css/util.css">
    <link rel="stylesheet" type="text/css" href="plugins/css/main.css">

    <style>
        /* Custom Styles complementing the template */
        .container-login100 {
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
        }

        .eye-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #999999;
            z-index: 10;
            font-size: 18px;
            transition: color 0.3s;
        }

        .eye-icon:hover {
            color: #555555;
        }

        /* Animated gradient border for error messages */
        .error-gradient-border {
            position: relative;
            background-color: transparent;
            padding: 17px;
            border-radius: 10px;
            margin-top: 10px;
            color: #fc0505ff;
            font-size: 14px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .error-gradient-border strong {
            color: #ff0000;
            font-weight: bold;
        }

        .error-gradient-border::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, #ff0000, #ff6600, #000000ff, #ff0000, #ff6600);
            background-size: 400% 400%;
            border-radius: 10px;
            z-index: -2;
            animation: gradientMove 5s ease infinite;
        }

        .error-gradient-border::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            right: 2px;
            bottom: 2px;
            background-color: rgba(0, 0, 0, 0.9);
            border-radius: 8px;
            z-index: -1;
        }

        .error-gradient-border {
            z-index: 1;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .form-group {
            margin-bottom: 1rem;
            width: 100%;
        }

        .captcha-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.1);
            padding: 10px 20px;
            border-radius: 25px;
            color: #ffffffff;
            margin-bottom: 20px;
        }

        .captcha-question {
            font-family: Poppins-Bold, sans-serif;
            font-size: 18px;
        }

        .input-captcha {
            width: 80px;
            padding: 5px 10px;
            border-radius: 10px;
            border: none;
            text-align: center;
            font-weight: bold;
        }

        .txt1 {
            color: #ffffff;
        }
    </style>
</head>

<body>
    <div class="container-login100" style="background-image: url('images/bg_default.jpg');">
        <div class="wrap-login100">
            <form class="login100-form validate-form" method="post" action="login_proses.php"
                onsubmit="return validateForm()">
                <span class="login100-form-logo">
                    <!-- Adjusted path to match typical structure -->
                    <img src="images/logodik.png" width="120" height="110" alt="Logo">
                </span>

                <span class="login100-form-title p-b-34 p-t-27">
                    Arsip Persuratan<br>
                    SMP Negeri 171
                </span>

                <div class="wrap-input100 validate-input" data-validate="Enter userid">
                    <input class="input100" type="text" id="userid" name="userid" placeholder="Username">
                    <span class="focus-input100" data-placeholder="&#xf207;"></span>
                </div>

                <div class="wrap-input100 validate-input" data-validate="Enter password">
                    <input class="input100" type="password" id="password" name="password" placeholder="Password">
                    <span class="focus-input100" data-placeholder="&#xf191;"></span>
                    <i class="fa fa-eye-slash eye-icon" id="toggle-password"></i>
                </div>

                <div class="wrap-input100 validate-input" data-validate="Masukkan Jawaban Captcha"
                    style="display: flex; align-items: center; justify-content: space-between;">
                    <img src="captcha_img.php" alt="CAPTCHA" id="captcha-img" style="border-radius: 5px; height: 40px;">
                    <span style="cursor: pointer; padding: 0 10px; color: #999;"
                        onclick="document.getElementById('captcha-img').src='captcha_img.php?'+Math.random();"
                        title="Refresh Captcha">
                        <i class="fas fa-sync-alt" style="transition: 0.3s;" onmouseover="this.style.color='#0010ff'"
                            onmouseout="this.style.color=''"></i>
                    </span>
                    <input class="input100" type="text" id="captcha" name="captcha" placeholder="Jawaban Penjumlahan"
                        required>
                </div>
                <br>
                <div class="container-login100-form-btn">
                    <button class="login100-form-btn">
                        Login
                    </button>
                </div>

                <?php

                // Pastikan tidak ada spasi sebelum tag php
                
                if (isset($_GET['salah'])) {
                    // --- KASUS 1: user diblokir (salah=3) ---
                    if ($_GET['salah'] == 3) {
                        // Ambil waktu tunggu dari URL parameter 't'
                        $remaining_seconds = isset($_GET['wait']) ? (int) $_GET['wait'] : (isset($_GET['t']) ? (int) $_GET['t'] : 0);
                        $minutes = floor($remaining_seconds / 60);
                        $seconds = $remaining_seconds % 60;
                        $sPadded = $seconds < 10 ? '0' . $seconds : $seconds;

                        echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <strong>AKSES DIBLOKIR!</strong><br>
                        Anda salah memasukkan password sebanyak 3x.<br>
                        Silahkan tunggu: <span id='countdown' style='font-weight:bold; font-size:1.2em;'>$minutes menit $sPadded detik</span>
                      </div>";
                        // Javascript untuk hitung mundur real-time
                        echo "<script>
                    var timeLeft = $remaining_seconds;
                    var elem = document.getElementById('countdown');
                    var timerId = setInterval(function() {
                        if (timeLeft <= 0) {
                            clearInterval(timerId);
                            elem.innerHTML = '0 menit 00 detik';
                            window.location.href = 'index.php';
                        } else {
                            timeLeft--;
                            var m = Math.floor(timeLeft / 60);
                            var s = timeLeft % 60;
                            var sPadded = s < 10 ? '0' + s : s;
                            elem.innerHTML = m + ' menit ' + sPadded + ' detik';
                        }
                    }, 1000);
                </script>";
                    }
                    // --- KASUS 2: Error umum/akses langsung (salah=2) ---
                    elseif ($_GET['salah'] == 2) {
                        echo "<div class='error-gradient-border' style='color: red; padding: 10px;'><strong>Error!</strong> Akses tidak valid atau koneksi gagal.</div>";
                    }
                    // --- KASUS 3: Password salah, tapi belum diblokir (salah=1) ---
                    elseif ($_GET['salah'] == 1) {
                        // Ambil sisa percobaan dari URL parameter 'sisa'
                        $remaining = isset($_GET['attempts']) ? (int) $_GET['attempts'] : (isset($_GET['sisa']) ? (int) $_GET['sisa'] : 0);
                        echo "<div class='error-gradient-border' style='color: orange; padding: 10px; border: 1px solid orange; background: #fff8e1; margin-bottom: 10px;'>
                        <strong>LOGIN GAGAL!</strong><br>
                        Username atau Password salah.<br>
                        Sisa percobaan: <strong>$remaining kali</strong> lagi sebelum diblokir selama 5 menit.
                      </div>";
                    }
                    // --- KASUS 4: reCAPTCHA tidak dicentang (salah=4) ---
                    elseif ($_GET['salah'] == 4) {
                        echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <strong>VERIFIKASI CAPTCHA!</strong><br>
                        Silahkan isi kode captcha dengan benar ' untuk melanjutkan.
                      </div>";
                    }
                    // --- KASUS 5: reCAPTCHA verifikasi gagal (salah=5) ---
                    elseif ($_GET['salah'] == 5) {
                        echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <strong>VERIFIKASI GAGAL!</strong><br>
                        Verifikasi captcha gagal. Silakan coba lagi.
                      </div>";
                    }
                    // --- KASUS 6: connection error (salah=6) ---
                    elseif ($_GET['salah'] == 6) {
                        echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <strong>KONEKSI GAGAL!</strong><br>
                        Tidak dapat menghubungi server verifikasi. Silakan coba lagi.
                      </div>";
                    }
                    // --- KASUS 7: Barcode belum discan (salah=7) ---
                    elseif ($_GET['salah'] == 7) {
                        echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <strong>SCAN BARCODE DIPERLUKAN!</strong><br>
                        Silakan scan barcode terlebih dahulu sebelum login.
                      </div>";
                    }
                }

                // --- SUCCESS: 2FA Setup Complete ---
                if (isset($_GET['setup2fa']) && $_GET['setup2fa'] == 'success') {
                    echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                    <strong>SETUP 2FA BERHASIL!</strong><br>
                    Google Authenticator Anda telah berhasil dikonfigurasi.<br>
                    Silakan login dengan username dan password, lalu masukkan kode 6 digit dari aplikasi Google Authenticator.
                  </div>";
                }


                ?>
                <div class="text-center p-t-90 txt1">
                    SMP Negeri 171<br>
                    <span>S.A.P Versi <?php echo $ver; ?></span><br>
                    Copyright &copy; <?php echo date("Y"); ?>
                </div>

            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var togglePassword = document.getElementById('toggle-password');
            var passwordField = document.getElementById('password');

            if (togglePassword && passwordField) {
                togglePassword.addEventListener('click', function () {
                    var type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);

                    if (type === 'text') {
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    } else {
                        this.classList.remove('fa-eye');
                        this.classList.add('fa-eye-slash');
                    }
                });
            }

            // Reload Captcha functionality
            var reloadCaptcha = document.getElementById('reload-captcha');
            var captchaImg = document.getElementById('captcha-img');
            if (reloadCaptcha && captchaImg) {
                reloadCaptcha.addEventListener('click', function () {
                    // Add rotation animation
                    this.style.transform = 'rotate(360deg)';
                    var self = this;
                    setTimeout(function () {
                        self.style.transform = 'rotate(0deg)';
                    }, 300);

                    // Refresh image source with timestamp to bypass cache
                    captchaImg.src = 'captcha_img.php?' + new Date().getTime();
                    document.getElementById('captcha').value = '';
                });
            }
        });

        function validateForm() {
            var userid = document.getElementById('userid').value;
            var password = document.getElementById('password').value;
            var captcha = document.getElementById('captcha').value;

            if (userid.trim() === '' || password.trim() === '' || captcha.trim() === '') {
                alert('Silakan lengkapi semua kolom!');
                return false;
            }
            return true;
        }
    </script>
</body>

</html>