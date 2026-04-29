<?php
ob_start();
session_start();

include 'dbconn.php'; 

// Cek Koneksi Database (Handle jika include gagal atau koneksi false)
if (!isset($conn) || $conn === false) {
    // Redirect atau error handling jika koneksi gagal
    header("Location: index.php?error=db_connection");
    exit;
}

// Load Dependencies dengan Cek File
if (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    // Fallback atau error handling
    if (!class_exists('GoogleAuthenticator')) {
        error_log("Vendor autoload missing and GoogleAuthenticator class not found.");
        // Opsional: Die atau redirect, tapi biarkan lanjut jika tidak pakai 2FA yang butuh lib
    }
}

// Hanya izinkan metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

// =========================================================================================
//                                   RATE LIMITING (LOGIN ATTEMPTS)
// =========================================================================================

$ip_address = $_SERVER['REMOTE_ADDR'];
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip_address = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip_list = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
    $ip_address = trim($ip_list[0]);
}
$ip_address = $conn->real_escape_string($ip_address);

// 1. Buat Tabel login_attempts jika belum ada (Sesuai Request User)
$conn->query("CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    userid VARCHAR(100),
    ip_address VARCHAR(45),
    attempts INT DEFAULT 0,
    last_attempt_time DATETIME,
    UNIQUE KEY unique_ip (ip_address)
)");

$check_limit = $conn->query("SELECT * FROM login_attempts WHERE ip_address = '$ip_address'");
$limit_data = ($check_limit) ? $check_limit->fetch_assoc() : null;

if ($limit_data) {
    // Cek 5-minute lockout logic
    $attempts = $limit_data['attempts'];
    $last_attempt = strtotime($limit_data['last_attempt_time']);
    $lockout_time = 5 * 60; // 5 menit

    if ($attempts >= 3 && (time() - $last_attempt) < $lockout_time) {
        $remaining = $lockout_time - (time() - $last_attempt);
        header("Location: login.php?salah=3&wait=$remaining");
        exit();
    } elseif ((time() - $last_attempt) >= $lockout_time && $attempts >= 3) {
        // Reset jika waktu lockout lewat
        $conn->query("DELETE FROM login_attempts WHERE ip_address = '$ip_address'");
    }
}

// =========================================================================================
//                                   VALIDASI CAPTCHA
// =========================================================================================
$user_captcha = $_POST['captcha'] ?? '';
$session_captcha = $_SESSION['captcha_answer'] ?? null;
unset($_SESSION['captcha_answer']);

if ($session_captcha === null || empty($user_captcha) || intval($user_captcha) !== intval($session_captcha)) {
    header("Location: login.php?salah=5");
    exit;
}

// =========================================================================================
//                                   PROSES LOGIN
// =========================================================================================
$userid = $_POST['userid'] ?? ''; 
$password = $_POST['password'] ?? ''; 

// Query Database
$stmt = $conn->prepare('SELECT id, userid, password, email, nama, google_auth_secret, level, status, poto, nik FROM tb_user WHERE userid = ? LIMIT 1');
if ($stmt === false) {
    error_log('Prepare statement failed: ' . htmlspecialchars($conn->error));
    header("Location: login.php?salah=2"); 
    exit;
}
$stmt->bind_param("s", $userid);
$stmt->execute();
$result = $stmt->get_result(); 
$user = $result->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['password'])) {

    // [CEK STATUS]
    if ($user['status'] == '0' || $user['status'] == 'Nonaktif') {
        header("Location: login.php?salah=2");
        exit;
    }

    // Login Sukses - Reset Attempts
    $conn->query("DELETE FROM login_attempts WHERE ip_address = '$ip_address'");
    
    // Update Info User
    $uid = $user['id'];
    $conn->query("UPDATE tb_user SET last_login = NOW(), ip = '$ip_address' WHERE id = '$uid'");

    // =================================================================================
    //                                   LOGIC 2FA & TRUSTED DEVICE
    // =================================================================================
    
    $secret = trim($user['google_auth_secret'] ?? '');
    
    if ($secret !== '') {
        $is_trusted = false;
        
        // Cek Cookie Device Token
        if (isset($_COOKIE['device_token'])) {
            $token = $conn->real_escape_string($_COOKIE['device_token']);
            $check_device = $conn->query("SELECT id FROM user_devices WHERE user_id='$uid' AND device_token='$token' AND expires_at > NOW() LIMIT 1");
            if ($check_device && $check_device->num_rows > 0) {
                $is_trusted = true;
            }
        }

        if (!$is_trusted) {
            // Redirect ke Verifikasi 2FA
            $_SESSION['2fa_user_id'] = $uid;
            if (isset($_POST['remember_device']) && $_POST['remember_device'] == '1') {
                $_SESSION['2fa_remember_me'] = true;
            }
            header("Location: verify2fa.php"); 
            exit();
        }
    } else {
        // =================================================================================
        // MANDATORY 2FA SETUP
        // User WAJIB setup 2FA saat first login jika belum punya secret
        // =================================================================================
        $_SESSION['2fa_user_id'] = $uid;
        if (file_exists('setup_2fa.php')) {
             header('Location: setup_2fa.php');
             exit();
        }
    }

    // =================================================================================
    //                                   LOGIN FINAL (TRUSTED / NO 2FA)
    // =================================================================================
    
    session_regenerate_id(true);
    $_SESSION['authenticated'] = true;
    $_SESSION['id'] = $user['id'];
    $_SESSION['nama'] = $user['nama']; 
    $_SESSION['userid'] = $user['userid']; 
    $_SESSION['email'] = $user['email']; 
    $_SESSION['level'] = $user['level']; 
    $_SESSION['status'] = $user['status']; 
    $_SESSION['poto'] = $user['poto']; 
    $_SESSION['nik'] = $user['nik']; 
    $_SESSION['last_activity'] = time();

    // Set compatibility mapping
    $_SESSION['skradm'] = $user['userid'];
    
    // Sessions (tahundb, tapel, semester) are now handled globally by dbconn.php
    $_SESSION['database_asli'] = $db;

    // Log Login
    $nama = $user['nama'] ?? $userid;
    $waktu = date("Y-m-d H:i:s");
    $info_log = "Login";
    
    $stmt_log = $conn->prepare("INSERT INTO users_log (user, nama, waktu, ip, info) VALUES (?, ?, ?, ?, ?)");
    $stmt_log->bind_param("sssss", $user['userid'], $nama, $waktu, $ip_address, $info_log);
    $stmt_log->execute();

    header("Location: ./?");
    exit();

} else {
    // =================================================================================
    //                                   LOGIN GAGAL
    // =================================================================================
    
    // Insert/Update Login Attempts (Use ON DUPLICATE KEY UPDATE)
    // Need UNIQUE index on ip_address for this to work as intended for IP-based blocking
    $userid_safe = $conn->real_escape_string($userid);
    
    $conn->query("INSERT INTO login_attempts (userid, ip_address, attempts, last_attempt_time) 
                  VALUES ('$userid_safe', '$ip_address', 1, NOW()) 
                  ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt_time = NOW(), userid = '$userid_safe'");

    $q_attempts = $conn->query("SELECT attempts FROM login_attempts WHERE ip_address = '$ip_address'");
    $data_attempts = ($q_attempts) ? $q_attempts->fetch_assoc() : null;
    
    $attempts_count = $data_attempts['attempts'] ?? 1;
    $remaining = 3 - $attempts_count;
    if ($remaining < 0) $remaining = 0;
    
    if ($attempts_count >= 3) {
        header("Location: login.php?salah=3&wait=300");
    } else {
        header("Location: login.php?salah=1&sisa=$remaining");
    }
    exit();
}
?>
