<?php
ob_start();
session_start();

// Include koneksi database
include 'dbconn.php';

// Load Dependencies dengan Cek File
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    die("Error: Library dependencies (vendor/autoload.php) not found. Please run 'composer install' or contact administrator.");
}

use PragmaRX\Google2FA\Google2FA;

// Redirect jika session 2FA tidak ada
if (!isset($_SESSION['2fa_user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['2fa_user_id'];
$error_message = "";
$success_message = "";

// Ambil data user
$stmt = $conn->prepare("SELECT id, userid, password, email, nama, google_auth_secret, level, status, poto, email_code, email_code_expired FROM tb_user WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    session_destroy();
    header("Location: index.php");
    exit();
}

$google_secret = $user['google_auth_secret'];

// --- Helper Functions ---
function get_client_ip() {
    $ipaddress = '';
    if (isset($_SERVER['HTTP_CLIENT_IP'])) $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    else if (isset($_SERVER['HTTP_X_FORWARDED'])) $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    else if (isset($_SERVER['REMOTE_ADDR'])) $ipaddress = $_SERVER['REMOTE_ADDR'];
    else $ipaddress = 'UNKNOWN';
    return explode(',', $ipaddress)[0];
}

// --- VERIFIKASI KODE ---
if (isset($_POST['verify_code'])) {
    $code = trim($_POST['code']);
    $is_valid = false;

    // 1. Coba TOTP (Google Authenticator)
    if (!empty($google_secret)) {
        $google2fa = new Google2FA();
        // Increase usage window to 4 (approx +/- 2 minutes)
        if ($google2fa->verifyKey($google_secret, $code, 4)) {
             $is_valid = true;
        } else {
             // Log debug info
             error_log("Verify2FA Fail: UserID=$user_id, Code=$code, Time=" . date("Y-m-d H:i:s"));
        }
    }

    // 2. Coba Email Code (Fallback)
    if (!$is_valid && isset($user['email_code']) && !empty($user['email_code'])) {
        if ($code == $user['email_code'] && strtotime($user['email_code_expired']) > time()) {
            $is_valid = true;
            // Clear email code setelah dipakai
            mysqli_query($conn, "UPDATE tb_user SET email_code=NULL, email_code_expired=NULL WHERE id='$user_id'");
        }
    }

    if ($is_valid) {
        // --- LOGIN BERHASIL ---
        session_regenerate_id(true);
        unset($_SESSION['2fa_user_id']); // Hapus session temp
        unset($_SESSION['2fa_remember_me']); // Hapus remember_me temp

        // Set session utama
        $_SESSION['authenticated'] = true;
        $_SESSION['id'] = $user['id']; // ID Numeric
        $_SESSION['userid'] = $user['userid']; // UserID String (NIP/Username)
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['level'] = $user['level'];
        $_SESSION['status'] = $user['status'];
        $_SESSION['poto'] = $user['poto'];
        $_SESSION['last_activity'] = time();
        $_SESSION['skradm'] = $user['userid']; // Required for secure.php

        // --- Remember Device Logic ---
        if (isset($_POST['remember_device'])) {
            try {
                $token = bin2hex(random_bytes(32));
            } catch (Exception $e) {
                // Fallback jika random_bytes gagal (PHP versi lama)
                $token = bin2hex(openssl_random_pseudo_bytes(32));
            }
            $expires = date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)); // 30 hari

            // Buat tabel user_devices jika belum ada
            mysqli_query($conn, "CREATE TABLE IF NOT EXISTS user_devices (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT(11) NOT NULL,
                device_token VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX (user_id),
                INDEX (device_token)
            )");

            $token_safe = mysqli_real_escape_string($conn, $token);
            $user_id_safe = mysqli_real_escape_string($conn, $user_id);
            
            // Simpan token ke database
            mysqli_query($conn, "INSERT INTO user_devices (user_id, device_token, expires_at) VALUES ('$user_id_safe', '$token_safe', '$expires')");
            
            // Set cookie
            setcookie('device_token', $token, time() + (30 * 24 * 60 * 60), "/", "", false, true); // HttpOnly
        }

        // --- Logging ---
        $user_ip = get_client_ip();
        $current_time = date('Y-m-d H:i:s');
        
        // Update user setup
        $update_stmt = $conn->prepare("UPDATE tb_user SET ip = ?, last_login = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $user_ip, $current_time, $user_id);
        $update_stmt->execute();
        
        // Insert Log
        $log_info = 'Login';
        $log_stmt = $conn->prepare("INSERT INTO users_log (user, nama, waktu, ip, info) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->bind_param("sssss", $user['userid'], $user['nama'], $current_time, $user_ip, $log_info);
        $log_stmt->execute();

        header("Location: index.php");
        exit();

    } else {
        $error_message = "Kode salah atau kadaluarsa.";
    }
}

// --- KIRIM KODE EMAIL ---
if (isset($_POST['send_email'])) {
    if (!empty($user['email'])) {
        $code = rand(100000, 999999);
        $expired = date('Y-m-d H:i:s', time() + (15 * 60)); // 15 menit
        
        // Simpan kode ke database (asumsi kolom email_code dan email_code_expired ada di tb_user)
        // Jika belum ada, Anda perlu ALTER TABLE tb_user ADD email_code VARCHAR(6), ADD email_code_expired DATETIME;
        
        $stmt_email = $conn->prepare("UPDATE tb_user SET email_code=?, email_code_expired=? WHERE id=?");
        $stmt_email->bind_param("ssi", $code, $expired, $user_id);
        
        if($stmt_email->execute()){
            
            // Coba kirim email (gunakan library yang tersedia atau fungsi mail bawaan)
            // Di sini saya pakai mail() standar sebagai contoh, sebaiknya pakai PHPMailer
            $to = $user['email'];
            $subject = "Kode Verifikasi Login - Sistem Arsip";
            $message = "Kode verifikasi Anda adalah: " . $code . "\n\nKode ini berlaku selama 15 menit.";
            $headers = "From: noreply@smpn171.jkt.sch.id";

            // Cek jika helper function ada (opsional)
            if (file_exists('send_email_helper.php')) {
                require_once 'send_email_helper.php';
                if (function_exists('sendEmailCode')) {
                     $send = sendEmailCode($to, $code, $conn);
                     if ($send === true) {
                         $success_message = "Kode telah dikirim ke " . substr($to, 0, 3) . "***" . substr($to, strpos($to, '@'));
                     } else {
                         $error_message = "Gagal kirim via Helper: " . $send;
                     }
                }
            } else {
                // Fallback mail biasa (sering masuk spam tapi ok untuk default)
                if(mail($to, $subject, $message, $headers)){
                    $success_message = "Kode telah dikirim ke email " . substr($to, 0, 3) . "...";
                } else {
                    $error_message = "Gagal mengirim email. Server tidak mendukung mail().";
                }
            }
        } else {
             $error_message = "Gagal update database user code.";
        }
    } else {
        $error_message = "Email tidak terdaftar pada akun ini.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Verifikasi 2FA</title>
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
        .container-login100 {
            background-repeat: no-repeat;
            background-position: center;
            background-size: cover;
        }
        
        /* Custom Alert Styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
            width: 100%;
            text-align: center;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }

        /* Checkbox Styling */
        .contact100-form-checkbox .input-checkbox100 {
            display: inline-block !important;
            width: 18px;
            height: 18px;
            margin-right: 8px;
            cursor: pointer;
            vertical-align: middle;
            position: relative;
            top: -1px;
        }
        .contact100-form-checkbox .label-checkbox100 {
            color: white !important;
            cursor: pointer;
            display: inline;
            padding-left: 0 !important;
            font-size: 14px;
        }
        .contact100-form-checkbox .label-checkbox100::before {
            display: none !important;
        }

        .txt1 {
            color: #e0e0e0;
        }
        .txt2 {
            font-size: 14px;
            transition: all 0.3s;
        }
        .txt2:hover {
            color: #007bff !important;
        }
    </style>
</head>
<body>
    <div class="container-login100" style="background-image: url('images/bg_default.jpg');">
        <div class="wrap-login100">
            <form class="login100-form validate-form" method="post">
                <span class="login100-form-logo">
                    <img src="images/logodik.png" width="120" height="110" alt="Logo">
                </span>

                <span class="login100-form-title p-b-34 p-t-27">
                    Verifikasi Keamanan
                </span>
                
                <?php if ($error_message): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
                <?php endif; ?>
                
                <div class="text-center p-b-20" style="color: white; font-size: 13px;">
                    Masukkan kode dari Google Authenticator<br>atau kode yang dikirim ke Email.
                    <br><br>
                    <span style="color: yellow; font-size: 11px;">Server Time: <?php echo date("Y-m-d H:i:s"); ?></span>
                </div>

                <div class="wrap-input100 validate-input" data-validate="Masukkan Kode">
                    <input class="input100" type="text" name="code" autocomplete="off" autofocus>
                    <span class="focus-input100" data-placeholder="G"></span>
                </div>
                
                <div class="contact100-form-checkbox text-center" style="padding-top: 15px; padding-bottom: 20px;">
<input class="input-checkbox100" id="ckb1" type="checkbox" name="remember_device" <?php echo (isset($_SESSION['2fa_remember_me']) && $_SESSION['2fa_remember_me']) ? 'checked' : ''; ?>>                    <label class="label-checkbox100" for="ckb1">
                        Ingat browser ini selama 30 hari
                    </label>
                </div>
                
                <div class="container-login100-form-btn">
                    <button class="login100-form-btn" name="verify_code">
                        Verifikasi
                    </button>
                </div>
                
                <div class="text-center p-t-50">
                    <span class="txt1">
                        Tidak punya akses ke Authenticator?
                    </span>
                    <br>
                    <button type="submit" name="send_email" class="txt2" style="background: none; border: none; cursor: pointer; color: white; text-decoration: underline; margin-top: 5px;">
                        Kirim kode verifikasi ke Email
                    </button>
                </div>

            </form>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="plugins/jquery/jquery.min.js"></script>
    <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="plugins/js/main.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>
