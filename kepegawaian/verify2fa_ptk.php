<?php
/**
 * Guru 2FA Verification Portal
 * Managed by Antigravity AI
 */
ob_start();
include_once "../dbconn.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Load Dependencies
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
} else {
    $_SESSION['error_message'] = "Library 2FA (vendor) tidak ditemukan. Silakan hubungi admin.";
    header("Location: login_ptk.php");
    exit();
}

use PragmaRX\Google2FA\Google2FA;

// Redirect jika session 2FA tidak ada
if (!isset($_SESSION['2fa_ptk_user_id'])) {
    header("Location: login_ptk.php");
    exit();
}

$user_id = $_SESSION['2fa_ptk_user_id'];
$error_message = "";
$success_message = "";

// Ambil data pegawai
$stmt = $conn->prepare("SELECT id, nrk, nip, nm_pegawai, email, foto, status, google_auth_secret FROM pegawai WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$pegawai = $result->fetch_assoc();
$stmt->close();

if (!$pegawai) {
    session_destroy();
    header("Location: login_ptk.php");
    exit();
}

$google_secret = $pegawai['google_auth_secret'];

// --- VERIFIKASI KODE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
    $code = trim($_POST['code'] ?? '');
    $is_valid = false;

    if (!empty($google_secret) && !empty($code)) {
        $google2fa = new Google2FA();
        try {
            if ($google2fa->verifyKey($google_secret, $code, 4)) {
                $is_valid = true;
            }
        } catch (Exception $e) {
            $error_message = "Terjadi kesalahan pada sistem 2FA.";
        }
    }

    if ($is_valid) {
        // --- LOGIN BERHASIL ---
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_regenerate_id(true);
        unset($_SESSION['2fa_ptk_user_id']);

        // Set session utama (Consistent with proseslogin.php)
        $_SESSION['authenticated'] = true;
        $_SESSION['id'] = $pegawai['id'];
        $_SESSION['nama'] = $pegawai['nm_pegawai'];
        $_SESSION['userid'] = $pegawai['nrk'] ?: $pegawai['nip'];
        $_SESSION['skradm'] = $_SESSION['userid'];
        $_SESSION['email'] = $pegawai['email'];
        $_SESSION['level'] = '4';
        $_SESSION['status'] = $pegawai['status'] == '1' ? 'Aktif' : $pegawai['status'];
        $_SESSION['poto'] = $pegawai['foto'];
        $_SESSION['nik'] = $pegawai['nrk'] ?: $pegawai['nip'];
        $_SESSION['last_activity'] = time();
        $_SESSION['login_time'] = time();
        $_SESSION['database_asli'] = $db;

        // Log Login
        add_activity_log($conn, 'Auth', '2FA Verify Guru', 'Login Guru (2FA Verified)');

        header("Location: index_ptk.php");
        exit();
    } else {
        $error_message = "Kode verifikasi salah atau kadaluarsa.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Verifikasi 2FA - Portal PTK</title>
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
            justify-content: center;
            align-items: center;
            padding: 15px;
            background: #020617 !important;
            position: relative;
            overflow: hidden;
        }

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
        }

        .login100-form-title {
            font-size: 26px !important;
            color: #fff !important;
            text-align: center;
            font-weight: 800 !important;
            display: block;
            margin-bottom: 20px;
        }

        .login100-form-title small {
            font-size: 14px !important;
            color: var(--text-muted) !important;
            font-weight: 400 !important;
            display: block;
            margin-top: 10px;
        }

        .wrap-input100 {
            width: 100% !important;
            position: relative;
            background: var(--input-bg) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: 12px !important;
            margin-bottom: 25px !important;
            transition: all 0.3s;
        }

        .wrap-input100:focus-within {
            border-color: #6366f1 !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }

        .input100 {
            font-family: 'Outfit', sans-serif !important;
            font-size: 24px !important;
            color: #fff !important;
            text-align: center;
            letter-spacing: 10px;
            display: block;
            width: 100% !important;
            height: 60px !important;
            background: transparent !important;
            border: none !important;
            outline: none !important;
        }

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
            border: none !important;
            cursor: pointer;
            transition: all 0.4s;
            box-shadow: 0 10px 20px -10px #94a3b8 !important;
        }

        .login100-form-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px -10px #94a3b8 !important;
        }

        .error-container {
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(239, 68, 68, 0.12) !important;
            border: 1px solid rgba(239, 68, 68, 0.2) !important;
            border-radius: 12px;
            color: #fca5a5;
            font-size: 13px;
            text-align: center;
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: var(--text-muted);
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s;
        }

        .back-link:hover {
            color: #fff;
        }
    </style>
</head>

<body>
    <div class="container-login100">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>

        <div class="wrap-login100">
            <form class="login100-form validate-form" method="post" id="form2fa">
                <div class="login100-form-logo">
                    <img src="../images/logodik.png" width="55" alt="Logo">
                </div>

                <span class="login100-form-title">
                    Verifikasi 2FA
                    <small>Masukkan 6 digit kode dari aplikasi Google Authenticator Anda.</small>
                </span>

                <?php if ($error_message): ?>
                    <div class="error-container">
                        <i class="fas fa-exclamation-circle me-1"></i> <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <div class="wrap-input100 validate-input" data-validate="Masukkan Kode">
                    <input class="input100" type="text" name="code" id="code-input" autocomplete="off" autofocus
                        maxlength="6" required>
                </div>

                <div class="container-login100-form-btn">
                    <button class="login100-form-btn" type="submit">
                        Verifikasi
                    </button>
                </div>


            </form>
        </div>
    </div>

    <script src="../plugins/jquery/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            const $input = $('#code-input');
            const $form = $('#form2fa');
            const $btn = $('.login100-form-btn');

            // Sanitize input: only numbers
            $input.on('input', function () {
                const val = $(this).val().replace(/[^0-9]/g, '');
                $(this).val(val);
            });

            $form.on('submit', function () {
                if ($input.val().length === 6) {
                    $btn.html('<i class="fas fa-circle-notch fa-spin me-2"></i> Memverifikasi...');
                    $btn.css('pointer-events', 'none');
                    $btn.css('opacity', '0.8');
                } else {
                    alert('Silakan masukkan 6 digit kode verifikasi.');
                    return false;
                }
            });
        });
    </script>
</body>

</html>