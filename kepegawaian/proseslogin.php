<?php
ob_start(); // Output buffering HARUS pertama agar header() tidak error

// Include dbconn FIRST to handle session configuration before session_start
if (file_exists('../dbconn.php')) {
    include '../dbconn.php';
} else {
    die("Database connection file missing.");
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check database connection
if (!isset($conn) || $conn === false) {
    header("Location: login_ptk.php?error=db_connection");
    exit;
}

// Load Dependencies with adjusted path
if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login_ptk.php");
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

// Ensure login_attempts table exists
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
    $attempts = $limit_data['attempts'];
    $last_attempt = strtotime($limit_data['last_attempt_time'] ?? '0');
    $lockout_time = 5 * 60; // 5 minutes

    if ($attempts >= 3 && (time() - $last_attempt) < $lockout_time) {
        $remaining = $lockout_time - (time() - $last_attempt);
        header("Location: login_ptk.php?salah=3&wait=$remaining");
        exit();
    } elseif ((time() - $last_attempt) >= $lockout_time && $attempts >= 3) {
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
    header("Location: login_ptk.php?salah=5");
    exit;
}

// =========================================================================================
//                                   PROSES LOGIN (GURU - DARI TABEL PEGAWAI)
// =========================================================================================
$userid = trim($_POST['userid'] ?? '');
$password = trim($_POST['password'] ?? '');

// Query Database - TABEL PEGAWAI
$stmt = $conn->prepare('SELECT id, nrk, nip, nm_pegawai, email, foto, status, google_auth_secret FROM pegawai WHERE (nrk = ? OR nip = ?) LIMIT 1');
if ($stmt === false) {
    header("Location: login_ptk.php?salah=2");
    exit;
}
$stmt->bind_param("ss", $userid, $userid);
$stmt->execute();
$result = $stmt->get_result();
$pegawai = $result->fetch_assoc();
$stmt->close();

// Authentication Logic: Check if password matches either NRK or NIP
$is_authenticated = false;
if ($pegawai) {
    // Both NIP and NRK can serve as the password
    $nrk_db = !empty($pegawai['nrk']) ? trim($pegawai['nrk']) : '';
    $nip_db = !empty($pegawai['nip']) ? trim($pegawai['nip']) : '';

    // Verify password against either NRK or NIP
    if (($nrk_db !== '' && $password === $nrk_db) || ($nip_db !== '' && $password === $nip_db)) {
        $is_authenticated = true;
    }
}

if ($is_authenticated) {
    // [CEK STATUS]
    if ($pegawai['status'] == '0' || $pegawai['status'] == 'Nonaktif') {
        header("Location: login_ptk.php?salah=6");
        exit;
    }

    // Login Sukses - Reset Attempts
    $conn->query("DELETE FROM login_attempts WHERE ip_address = '$ip_address'");

    // =========================================================================================
    //                                   TWO-FACTOR AUTH (2FA) FLOW
    // =========================================================================================
    $_SESSION['2fa_ptk_user_id'] = $pegawai['id'];

    if (empty($pegawai['google_auth_secret'])) {
        // Belum setup 2FA -> Redirect ke Setup
        header("Location: setup_2fa_ptk.php");
        exit();
    } else {
        // Sudah setup 2FA -> Redirect ke Verifikasi
        
        // Log activity: Attempting 2FA
        add_activity_log($conn, 'Auth', 'Login Guru', 'Login Guru (Waiting 2FA)');

        header("Location: verify2fa_ptk.php");
        exit();
    }

    // --- Session Registration ---
    // Reachable if 2FA is bypassed due to missing vendor or other reasons.
    
    session_regenerate_id(true);
    unset($_SESSION['2fa_ptk_user_id']); // Clear 2FA session since we're bypassing
    
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

    // Log Login (Bypassed if applicable)
    $info_log = isset($bypass_flag) ? "Login Guru (2FA BYPASSED - Vendor Missing)" : "Login Guru (Direct)";
    add_activity_log($conn, 'Auth', 'Login Guru', $info_log);

    // Redirect to specialized teacher portal
    header("Location: index_ptk.php");
    exit();

} else {
    // LOGIN GAGAL
    $userid_safe = $conn->real_escape_string($userid);
    $conn->query("INSERT INTO login_attempts (userid, ip_address, attempts, last_attempt_time) 
                  VALUES ('$userid_safe', '$ip_address', 1, NOW()) 
                  ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt_time = NOW(), userid = '$userid_safe'");

    $q_attempts = $conn->query("SELECT attempts FROM login_attempts WHERE ip_address = '$ip_address'");
    $data_attempts = ($q_attempts) ? $q_attempts->fetch_assoc() : null;

    $attempts_count = $data_attempts['attempts'] ?? 1;
    $remaining = 3 - $attempts_count;
    if ($remaining < 0)
        $remaining = 0;

    if ($attempts_count >= 3) {
        header("Location: login_ptk.php?salah=3&wait=300");
    } else {
        header("Location: login_ptk.php?salah=1&sisa=$remaining");
    }
    exit();
}
?>