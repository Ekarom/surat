<?php
/**
 * Guru Login Portal
 * Managed by Antigravity AI
 */
ob_start();
include_once "../dbconn.php"; // Standardized session path logic is inside here

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if already logged in
if (isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true && ($_SESSION['level'] ?? '') == '4') {
    header("Location: index_ptk.php");
    exit;
}

// --- Logika Pesan Error dari Session ---
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Portal PTK - SMP Negeri 171</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../images/logodik.png">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../plugins/iconic/css/material-design-iconic-font.min.css">
    <link rel="stylesheet" type="text/css" href="../plugins/css/util.css">
    <link rel="stylesheet" type="text/css" href="../plugins/css/main.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --glass-bg: rgba(15, 23, 42, 0.8);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-muted: #94a3b8;
            --input-bg: rgba(255, 255, 255, 0.03);
        }

        body,
        html {
            height: 100%;
            font-family: 'Outfit', sans-serif !important;
            margin: 0;
            background: #020617 !important;
            color: #fff;
        }

        .container-login100 {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            padding: 15px;
            background: #020617 !important;
            position: relative;
            overflow: hidden;
        }

        /* Ambient Background Orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 0;
            opacity: 0.4;
        }

        .orb-1 {
            width: 400px;
            height: 400px;
            background: #4f46e5;
            top: -100px;
            right: -100px;
        }

        .orb-2 {
            width: 300px;
            height: 300px;
            background: #7c3aed;
            bottom: -50px;
            left: -50px;
        }

        .wrap-login100 {
            width: 420px;
            background: var(--glass-bg) !important;
            backdrop-filter: blur(20px) !important;
            -webkit-backdrop-filter: blur(20px) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: 24px !important;
            padding: 50px 40px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
            position: relative;
            z-index: 1;
            animation: fadeIn 0.8s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .login100-form-logo {
            width: 80px;
            height: 80px;
            background: #fff !important;
            border-radius: 20px !important;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 30px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
        }

        .login100-form-title {
            font-family: 'Outfit', sans-serif !important;
            font-size: 26px !important;
            color: #fff !important;
            line-height: 1.2 !important;
            text-align: center;
            font-weight: 800 !important;
            display: block;
            margin-bottom: 40px;
            text-transform: none !important;
        }

        .login100-form-title small {
            font-size: 13px !important;
            font-weight: 400 !important;
            color: var(--text-muted) !important;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-top: 8px;
            display: block;
        }

        /* Input Styling Fixes */
        .wrap-input100 {
            width: 100% !important;
            position: relative;
            background: var(--input-bg) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: 12px !important;
            margin-bottom: 20px !important;
            transition: all 0.3s;
            overflow: hidden;
        }

        .wrap-input100:focus-within {
            border-color: #6366f1 !important;
            background: rgba(255, 255, 255, 0.05) !important;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }

        .input100 {
            font-family: 'Outfit', sans-serif !important;
            font-size: 15px !important;
            color: #fff !important;
            line-height: 1.2 !important;
            display: block;
            width: 100% !important;
            height: 50px !important;
            background: transparent !important;
            padding: 0 15px 0 45px !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
        }

        .focus-input100 {
            position: absolute;
            display: block;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
        }

        .focus-input100::before {
            content: attr(data-placeholder);
            position: absolute;
            display: block;
            width: 30px;
            height: 100%;
            top: 0;
            left: 15px;
            font-family: Material-Design-Iconic-Font;
            font-size: 20px;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            transition: all 0.4s;
        }

        .input100:focus+.focus-input100::before {
            color: #6366f1;
        }

        .eye-icon {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--text-muted);
            font-size: 18px;
            transition: color 0.3s;
            z-index: 5;
        }

        .eye-icon:hover {
            color: #fff;
        }

        /* Captcha Styling */
        .captcha-area {
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 8px 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
        }

        #captcha-img {
            border-radius: 8px;
            height: 36px;
            filter: invert(0.9) hue-rotate(180deg);
            background: transparent !important;
        }

        .refresh-captcha {
            color: var(--text-muted);
            cursor: pointer;
            font-size: 16px;
            transition: all 0.3s;
        }

        .refresh-captcha:hover {
            color: #fff;
            transform: rotate(180deg);
        }

        .captcha-input {
            background: transparent !important;
            border: none !important;
            color: #fff !important;
            font-family: 'Outfit', sans-serif !important;
            font-size: 15px !important;
            width: 100%;
            outline: none !important;
            padding: 5px 0;
        }

        /* Button Styling */
        .login100-form-btn {
            font-family: 'Outfit', sans-serif !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            color: #000000ff !important;
            text-transform: uppercase;
            width: 100% !important;
            height: 52px !important;
            border-radius: 12px !important;
            background: var(--primary-gradient) !important;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 0 25px;
            transition: all 0.4s;
            box-shadow: 0 10px 20px -10px rgba(13, 0, 255, 1) !important;
            border: none !important;
            cursor: pointer;
            margin-top: 10px;
        }

        .login100-form-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -10px rgba(79, 70, 229, 0.6) !important;
            background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%) !important;
        }

        .login100-form-btn:active {
            transform: translateY(0);
        }

        /* Error Messages */
        .error-container {
            margin-top: 25px;
            padding: 15px;
            background: rgba(239, 68, 68, 0.12) !important;
            border: 1px solid rgba(239, 68, 68, 0.2) !important;
            border-radius: 12px !important;
            color: #fca5a5 !important;
            font-size: 13px;
            text-align: center;
            line-height: 1.5;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .copyright {
            margin-top: 45px;
            text-align: center;
            color: var(--text-muted);
            font-size: 12px;
            letter-spacing: 0.5px;
            opacity: 0.8;
        }

        /* Utility */
        ::placeholder {
            color: rgba(255, 255, 255, 0.3) !important;
        }
    </style>
</head>

<body>
    <div class="container-login100">
        <!-- Decoration Orbs -->
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>

        <div class="wrap-login100">
            <form class="login100-form validate-form" method="post" action="proseslogin.php"
                onsubmit="return validateForm()">
                <div class="login100-form-logo">
                    <img src="../images/logodik.png" width="55" alt="Logo">
                </div>

                <span class="login100-form-title">
                    PORTAL PTK
                    <small>Sistem Manajemen Kepegawaian</small>
                </span>

                <div class="wrap-input100 validate-input" data-validate="Masukkan NIP atau Username">
                    <input class="input100" type="text" id="userid" name="userid" placeholder="NIP / Username"
                        autocomplete="off" required>
                </div>

                <div class="wrap-input100 validate-input" data-validate="Masukkan password">
                    <input class="input100" type="password" id="password" name="password" placeholder="Password" required>
                    <i class="fa fa-eye-slash eye-icon" id="toggle-password"></i>
                </div>

                <div class="captcha-area">
                    <div style="background: white; border-radius: 6px; padding: 2px; display: flex; align-items: center;">
                        <img src="../captcha_img.php" alt="CAPTCHA" id="captcha-img" style="height: 32px; border-radius: 4px; filter: none;">
                    </div>
                    <span class="refresh-captcha"
                        onclick="document.getElementById('captcha-img').src='../captcha_img.php?'+Math.random();"
                        title="Refresh Captcha">
                        <i class="las la-sync-alt"></i>
                    </span>
                    <input class="captcha-input" type="text" id="captcha" name="captcha" placeholder="Hasil Captcha"
                        autocomplete="off" required>
                </div>

                <div class="container-login100-form-btn">
                    <button class="login100-form-btn" type="submit">
                        Login
                    </button>
                </div>

                <?php
                if (!empty($error_message)) {
                    echo "<div class='error-container'><strong>Pesan:</strong><br>" . htmlspecialchars($error_message) . "</div>";
                }

                if (isset($_GET['salah'])) {
                    echo "<div class='error-container'>";
                    $salah = $_GET['salah'];
                    if ($salah == 1) {
                        $sisa = htmlspecialchars($_GET['sisa'] ?? 0);
                        echo "<strong>Login Gagal</strong><br>Username atau Password salah.<br>Sisa percobaan: $sisa kali.";
                    } elseif ($salah == 5) {
                        echo "<strong>Captcha Salah</strong><br>Silakan periksa kembali jawaban matematika Anda.";
                    } elseif ($salah == 3) {
                        $wait = isset($_GET['wait']) ? ceil((int) $_GET['wait'] / 60) : 5;
                        echo "<strong>Akun Terkunci</strong><br>Terlalu banyak percobaan login.<br>Silakan tunggu sekitar $wait menit.";
                    } elseif ($salah == 4) {
                        echo "<strong>Akses Ditolak</strong><br>Portal ini khusus untuk akun Pegawai/Guru.";
                    } elseif ($salah == 6) {
                        echo "<strong>User Tidak Aktif</strong><br>Akun Anda dinonaktifkan. Silakan hubungi admin.";
                    } elseif ($salah == 2) {
                        echo "<strong>Kesalahan Sistem</strong><br>Gagal menghubungi database. Silakan hubungi tim IT.";
                    } else {
                        echo "<strong>Terjadi Kesalahan</strong><br>Silakan coba lagi nanti.";
                    }
                    echo "</div>";
                }
                ?>

                <div class="copyright">
                    SMP Negeri 171<br>
                    Versi : <?php echo htmlspecialchars($ver); ?> | &copy; <?php echo date("Y"); ?>
                </div>
            </form>
        </div>
    </div>

    <script src="../plugins/jquery/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            // Toggle Password Visibility
            $('#toggle-password').click(function () {
                const passwordField = $('#password');
                const type = passwordField.attr('type') === 'password' ? 'text' : 'password';
                passwordField.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });

            // Focus animation for input containers
            $('.input100').focus(function(){
                $(this).parent().addClass('shadow-sm');
            }).blur(function(){
                $(this).parent().removeClass('shadow-sm');
            });
        });

        function validateForm() {
            var userid = document.getElementById('userid').value;
            var password = document.getElementById('password').value;
            var captcha = document.getElementById('captcha').value;
            
            if (userid.trim() === '' || password.trim() === '' || captcha.trim() === '') {
                return false;
            }
            
            // Show loading state on button
            const btn = document.querySelector('.login100-form-btn');
            btn.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i> Memproses...';
            btn.style.pointerEvents = 'none';
            btn.style.opacity = '0.8';
            
            return true;
        }
    </script>
</body>

</html>
