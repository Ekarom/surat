<?php
include "../dbconn.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Update last_activity ke waktu lampau agar tidak terdeteksi 'Online'
if (isset($_SESSION['id']) && isset($_SESSION['level'])) {
    $uid = (int)$_SESSION['id'];
    $level = $_SESSION['level'];
    // Gunakan waktu 10 menit yang lalu (melebihi ambang batas 5 menit di dashboard)
    $offline_time = date('Y-m-d H:i:s', strtotime('-10 minutes'));
    
    if ($level == '4') {
        mysqli_query($conn, "UPDATE pegawai SET last_activity = '$offline_time', last_logout = NOW() WHERE id = $uid");
    } else {
        mysqli_query($conn, "UPDATE tb_user SET last_activity = '$offline_time', last_logout = NOW() WHERE id = $uid");
    }
}

session_unset();
session_destroy();

// Optional: Clear the cookie explicitly for the standardized path
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

header("Location: login_ptk.php");
exit();
?>