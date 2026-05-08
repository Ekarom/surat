<?php
session_start();
// Robust Database Connection Include
$konek_path = dirname(__FILE__) . "/dbconn.php";
if (file_exists($konek_path)) {
    include $konek_path;
} else {
    die("Error: dbconn.php not found.");
}

// Ensure dependencies exist
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    die("Error: Vendor autoload not found. Please run composer install.");
}

use PragmaRX\Google2FA\Google2FA;

if (!isset($_SESSION['2fa_user_id'])) {
    header("Location: index.php");
    exit();
}
// Retrieve session vars
$user_id = $_SESSION['2fa_user_id'];

// Ambil data user dari database untuk mendapatkan userid
$stmt = $conn->prepare("SELECT * FROM tb_user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res_user = $stmt->get_result();
$d_user = $res_user->fetch_assoc();

if (!$d_user) {
    die("Error: User not found.");
}

$skradm = $d_user['userid']; // This corresponds to 'userid' in tb_user
$error = "";

// Generate new secret if not already in session
if (!isset($_SESSION['new_secret'])) {
    $google2fa = new Google2FA();
    $_SESSION['new_secret'] = $google2fa->generateSecretKey();
}
$secret = $_SESSION['new_secret'];

// --- HANDLE SKIP SETUP ---
if (isset($_POST['skip_setup'])) {
    // Logik Login Manual (Mirip login_proses.php)
    session_regenerate_id(true);
    $_SESSION['authenticated'] = true;
    $_SESSION['id'] = $d_user['id'];
    $_SESSION['nama'] = $d_user['nama'];
    $_SESSION['userid'] = $d_user['userid'];
    $_SESSION['email'] = $d_user['email'];
    $_SESSION['level'] = $d_user['level'];
    $_SESSION['status'] = $d_user['status'];
    $_SESSION['poto'] = $d_user['poto'];
    $_SESSION['nik'] = $d_user['nik']; // Sync with login_proses.php
    $_SESSION['last_activity'] = time();
    $_SESSION['skradm'] = $d_user['userid'];

    // Set database asli jika variabel tersedia
    if (isset($db)) {
        $_SESSION['database_asli'] = $db;
    }

    // Log Login (Info: 2FA Skipped)
    add_activity_log($conn, 'Auth', '2FA Skip', 'Login (2FA Skipped)');

    // Bersihkan session 2FA temporary
    unset($_SESSION['new_secret']);
    unset($_SESSION['2fa_user_id']);

    // Redirect ke Dashboard via index.php
    if ($d_user['level'] == '4') {
        header("Location: index.php?kepegawaian_dashboard_guru");
    } else {
        header("Location: index.php");
    }
    exit();
}

if (isset($_POST['verify_setup'])) {
    $code = trim($_POST['code']); // Added trim
    $valid = false;

    // Verify Code
    $google2fa = new Google2FA();
    // Increase time window to 4 (approx +/- 2 minutes) to handle drift
    if ($google2fa->verifyKey($secret, $code, 4)) {
        $valid = true;
    } else {
        error_log("Setup 2FA Fail: Secret=$secret, Code=$code, Time=" . date("Y-m-d H:i:s"));
    }

    if ($valid) {
        // Save secret to database (UPDATE to tb_user via $conn)
        $stmt_update = $conn->prepare("UPDATE tb_user SET google_auth_secret=? WHERE userid=?");
        $stmt_update->bind_param("ss", $secret, $skradm);
        $setqr = $stmt_update->execute();

        if (!$setqr) {
            $error = "Database Error: " . $stmt_update->error;
        } else {
            // Clear all temporary sessions
            unset($_SESSION['new_secret']);
            unset($_SESSION['2fa_user_id']);

            // Destroy session and redirect to login
            // User must login again and verify 2FA code
            session_destroy();

            // Redirect to login with success message
            header("Location: index.php?setup2fa=success");
            exit();
        }
    } else {
        $error = "Kode verifikasi salah. Silakan coba lagi.";
    }
}
// Generate QR Code URL (OTP Auth)
$issuer = "SistemSurat";
$label = $skradm;
$otpauth = "otpauth://totp/$issuer:$label?secret=$secret&issuer=$issuer";
// Determine QR Image Source
// Use public API as reliable fallback if local script is missing
$qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($otpauth);
// If local generator exists, use it (faster/private)
// UPDATE: Local generator broken (missing library), forced public API
/* 
if (file_exists("qrcode_gen.php")) {
    $qr_image_url = "qrcode_gen.php?data=" . urlencode($otpauth);
}
*/
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <title>Setup Google Authenticator</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="images/logodik2.png" />
    <link rel="stylesheet" type="text/css" href="plugins/fontawesome-free/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="plugins/css/util.css">
    <link rel="stylesheet" type="text/css" href="plugins/css/main.css">
    <style>
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }

        .input100 {
            background-color: rgba(0, 0, 0, 0.3) !important;
            border-radius: 5px;
            padding: 10px 15px 10px 38px !important;
        }

        .wrap-input100 {
            border-bottom: 2px solid rgba(255, 255, 255, 0.5) !important;
        }
    </style>
</head>

<body>
    <div class="limiter">
        <div class="container-login100" style="background-image: url('images/bg_default.jpg');">
            <div class="wrap-login100">
                <form class="login100-form validate-form" method="post">
                    <span class="login100-form-logo">
                        <i class="zmdi landscape"><img src="images/logodik.png" width="120" height="110" /></i>
                    </span>
                    <span class="login100-form-title p-b-34 p-t-27">
                        <h5>Setup Google Authenticator</h5>
                    </span>
                    <div class="text-center p-b-20" style="color: white !important;">
                        <p style="color: white !important;">Silakan scan QR Code di bawah ini dengan aplikasi Google
                            Authenticator Anda.</p>
                        <p style="color: yellow; font-size: 12px;">Server Time: <?php echo date("Y-m-d H:i:s"); ?></p>
                        <br>

                        <!-- QR CODE DISPLAY -->
                        <img src="<?php echo $qr_image_url; ?>" alt="QR Code"
                            style="background: white; padding: 10px; border-radius: 5px; width:200px; height:200px;" />

                        <br><br>
                        <p style="color: white !important;">Atau masukkan kode manual: <strong
                                style="color: white !important;"><?php echo $secret; ?></strong></p>
                        <br>
                        <p style="color: white !important;">Belum punya aplikasinya?</p>
                        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"
                            target="_blank">
                            <img src="https://play.google.com/intl/en_us/badges/static/images/badges/en_badge_web_generic.png"
                                alt="Get it on Google Play" height="60" style="margin-top: 10px;">
                        </a>
                    </div>
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <div class="wrap-input100 validate-input" data-validate="Masukan Kode">
                        <input class="input100" type="text" name="code" placeholder="Masukan Kode 6 Digit"
                            autocomplete="off">
                        <span class="focus-input100" data-placeholder="G"></span>
                    </div>
                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn" name="verify_setup">
                            Verifikasi & Simpan
                        </button>
                    </div>

                    <div class="text-center p-t-15">
                        <button type="submit" name="skip_setup" class="btn btn-link text-white"
                            style="text-decoration: underline; font-size: 14px; background: transparent; border: none;">
                            Lewati untuk saat ini <i class="fa fa-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>