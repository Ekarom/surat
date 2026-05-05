<?php
/**
 * Guru 2FA Setup Portal
 * Managed by Antigravity AI
 */
ob_start();
include_once "../dbconn.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check: Must be logged in OR coming from login flow (2fa_ptk_user_id)
if (!isset($_SESSION['authenticated']) && !isset($_SESSION['2fa_ptk_user_id'])) {
    header("Location: login_ptk.php");
    exit();
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

$user_id = $_SESSION['id'] ?? $_SESSION['2fa_ptk_user_id'];
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
    die("Error: Data pegawai tidak ditemukan.");
}

$google2fa = new Google2FA();

// Handle Disable 2FA
if (isset($_POST['disable_2fa'])) {
    $stmt_update = $conn->prepare("UPDATE pegawai SET google_auth_secret = NULL WHERE id = ?");
    $stmt_update->bind_param("i", $user_id);
    if ($stmt_update->execute()) {
        $success_message = "2FA telah dinonaktifkan.";
        $pegawai['google_auth_secret'] = null;
    }
    $stmt_update->close();
}

// Generate new secret if not already in session (for new setup)
if (empty($pegawai['google_auth_secret'])) {
    if (!isset($_SESSION['new_2fa_secret'])) {
        $_SESSION['new_2fa_secret'] = $google2fa->generateSecretKey();
    }
    $secret = $_SESSION['new_2fa_secret'];
} else {
    $secret = $pegawai['google_auth_secret'];
}

// Handle Verify & Save Setup
if (isset($_POST['verify_setup'])) {
    $code = trim($_POST['code'] ?? '');
    if ($google2fa->verifyKey($secret, $code, 4)) {
        // Save to database
        $stmt_update = $conn->prepare("UPDATE pegawai SET google_auth_secret = ? WHERE id = ?");
        $stmt_update->bind_param("si", $secret, $user_id);
        if ($stmt_update->execute()) {
            unset($_SESSION['new_2fa_secret']);
            
            // Jika dalam alur login, selesaikan login
            if (!isset($_SESSION['authenticated'])) {
                complete_ptk_login($pegawai, $conn, $db);
            } else {
                $success_message = "Google Authenticator berhasil dikonfigurasi!";
                $pegawai['google_auth_secret'] = $secret;
            }
        } else {
            $error_message = "Gagal menyimpan konfigurasi: " . $conn->error;
        }
        $stmt_update->close();
    } else {
        $error_message = "Kode verifikasi salah. Silakan coba lagi.";
    }
}

// Handle Skip Setup (Optional)
if (isset($_POST['skip_setup'])) {
    if (!isset($_SESSION['authenticated']) && isset($_SESSION['2fa_ptk_user_id'])) {
        complete_ptk_login($pegawai, $conn, $db);
    } else {
        header("Location: index_ptk.php");
        exit();
    }
}

// Helper to complete login session (Sync with proseslogin.php)
function complete_ptk_login($pegawai, $conn, $db) {
    session_regenerate_id(true);
    unset($_SESSION['2fa_ptk_user_id']);
    
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
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $waktu = date("Y-m-d H:i:s");
    $info_log = "Login Guru (2FA Setup Completed/Skipped)";

    $stmt_log = $conn->prepare("INSERT INTO users_log (user, nama, waktu, ip, info) VALUES (?, ?, ?, ?, ?)");
    if ($stmt_log !== false) {
        $user_log_id = $pegawai['nrk'] ?: $pegawai['nip'];
        $stmt_log->bind_param("sssss", $user_log_id, $pegawai['nm_pegawai'], $waktu, $ip_address, $info_log);
        $stmt_log->execute();
        $stmt_log->close();
    }

    header("Location: index_ptk.php");
    exit();
}

// Generate QR Code URL
$issuer = "Arsip171-PTK";
$label = $pegawai['nrk'] ?: $pegawai['nip'];
$otpauth = "otpauth://totp/$issuer:$label?secret=$secret&issuer=$issuer";
$qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauth);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Setup 2FA - Portal PTK</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="../images/logodik.png">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../plugins/css/util.css">
    <link rel="stylesheet" type="text/css" href="../plugins/css/main.css">

    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --glass-bg: rgba(15, 23, 42, 0.8);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-muted: #94a3b8;
            --input-bg: rgba(255, 255, 255, 0.03);
            --success: #10b981;
            --danger: #ef4444;
        }

        body, html {
            height: 100%;
            font-family: 'Outfit', sans-serif !important;
            margin: 0;
            background: #020617 !important;
            color: #fff;
        }

        .container-setup {
            width: 100%;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 15px;
            background: #020617 !important;
            position: relative;
        }

        .wrap-setup {
            width: 500px;
            background: var(--glass-bg) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid var(--glass-border) !important;
            border-radius: 24px !important;
            padding: 40px !important;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5) !important;
        }

        .setup-title {
            font-size: 24px;
            font-weight: 800;
            text-align: center;
            margin-bottom: 30px;
            color: #fff;
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
        }

        .alert-success { background: rgba(16, 185, 129, 0.1); color: var(--success); border: 1px solid rgba(16, 185, 129, 0.2); }
        .alert-danger { background: rgba(239, 68, 68, 0.1); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.2); }

        .qr-container {
            background: #fff;
            padding: 15px;
            border-radius: 16px;
            display: inline-block;
            margin: 0 auto 25px;
        }

        .qr-wrapper { text-align: center; }

        .secret-box {
            background: var(--input-bg);
            border: 1px dashed var(--glass-border);
            border-radius: 12px;
            padding: 12px;
            font-family: monospace;
            font-size: 18px;
            color: #fff;
            margin-bottom: 25px;
            text-align: center;
            letter-spacing: 2px;
        }

        .steps {
            font-size: 14px;
            color: var(--text-muted);
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .steps ol { padding-left: 20px; }
        .steps li { margin-bottom: 8px; }

        .wrap-input100 {
            background: var(--input-bg);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .input100 {
            height: 50px;
            background: transparent;
            border: none;
            width: 100%;
            color: #fff;
            text-align: center;
            font-size: 20px;
            letter-spacing: 5px;
            outline: none;
        }

        .btn-primary {
            background: var(--primary-gradient);
            color: #000;
            font-weight: 600;
            border: none;
            padding: 15px;
            border-radius: 12px;
            width: 100%;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4); }

        .btn-outline {
            background: transparent;
            border: 1px solid var(--glass-border);
            color: var(--text-muted);
            padding: 12px;
            border-radius: 12px;
            width: 100%;
            cursor: pointer;
            margin-top: 15px;
            transition: all 0.3s;
        }

        .btn-outline:hover { border-color: #fff; color: #fff; }

        .status-active {
            color: var(--success);
            text-align: center;
            margin-bottom: 30px;
        }
        
        .status-active i { font-size: 48px; margin-bottom: 15px; display: block; }
    </style>
</head>

<body>
    <div class="container-setup">
        <div class="wrap-setup">
            <div class="setup-title">Two-Factor Authentication (2FA)</div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <?php if (!empty($pegawai['google_auth_secret'])): ?>
                <!-- Status: Active -->
                <div class="status-active">
                    <i class="fas fa-check-circle"></i>
                    <strong>2FA Sedang Aktif</strong>
                    <p style="color: var(--text-muted); margin-top: 10px; font-size: 14px;">Akun Anda dilindungi dengan Google Authenticator.</p>
                </div>

                <form method="post" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan 2FA? Ini akan mengurangi keamanan akun Anda.');">
                    <button type="submit" name="disable_2fa" class="btn-outline" style="color: var(--danger); border-color: rgba(239, 68, 68, 0.2);">
                        Nonaktifkan 2FA
                    </button>
                </form>
                
                <a href="index_ptk.php" class="btn-outline" style="display: block; text-align: center; text-decoration: none;">
                    Kembali ke Dashboard
                </a>

            <?php else: ?>
                <!-- Status: Setup -->
                <div class="steps">
                    <ol>
                        <li>Install aplikasi <strong>Google Authenticator</strong> di ponsel Anda.</li>
                        <li>Scan QR Code di bawah ini menggunakan aplikasi tersebut.</li>
                        <li>Masukkan 6 digit kode yang muncul di aplikasi untuk verifikasi.</li>
                    </ol>
                </div>

                <div class="qr-wrapper">
                    <div class="qr-container">
                        <img src="<?php echo $qr_image_url; ?>" alt="QR Code">
                    </div>
                </div>

                <div class="secret-box">
                    <?php echo $secret; ?>
                </div>

                <form method="post">
                    <div class="wrap-input100">
                        <input class="input100" type="text" name="code" placeholder="000000" maxlength="6" autocomplete="off" required>
                    </div>
                    <button type="submit" name="verify_setup" class="btn-primary">
                        Verifikasi & Aktifkan
                    </button>
                </form>

                <form method="post">
                    <button type="submit" name="skip_setup" class="btn-outline" style="display: block; text-align: center; text-decoration: none;">
                        Lewati untuk saat ini
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script src="../plugins/jquery/jquery.min.js"></script>
</body>

</html>
