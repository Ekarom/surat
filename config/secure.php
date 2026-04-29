<?php
// Pastikan session dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Cek Apakah User Sudah Login
if (!isset($_SESSION['skradm'])) {
    header("Location: login.php");
    exit; // Wajib exit agar script di bawah tidak dieksekusi
}

// 2. Jika Logged In, Lanjutkan Logika

// IP & Browser Info
if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
}
//whether ip is from proxy
elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
}
//whether ip is from remote address
else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

$browser = $_SERVER['HTTP_USER_AGENT'];

date_default_timezone_set("Asia/Jakarta");
$log = date("Y-m-d H:i:s");

// Variabel Sesi
$usc = $_SESSION['skradm'];
$tapel = isset($_SESSION['tapel']) ? $_SESSION['tapel'] : '';
$tahunsklh = isset($_SESSION['tahundb']) ? $_SESSION['tahundb'] : '';
$semester = isset($_SESSION['semester']) ? $_SESSION['semester'] : '';

// Perhitungan Tahun
$tahun = date("Y");
$tahunb = date("Y", strtotime("+1 year"));
$tahunm = date("Y", strtotime("-3 year"));
$tahunm8 = date("Y", strtotime("-2 year"));
$tahunm7 = date("Y", strtotime("-1 year"));
$tapels = "$tahunm7/$tahun";
$tapelb = "$tahun/$tahunb";
$userc = $_SESSION['skradm'];

// Query Database
// Pastikan $sqlconn tersedia (dari file koneksi yang meng-include ini)
if (isset($sqlconn) && $sqlconn) {
    // Ambil Data User
    $getuser = mysqli_query($sqlconn, "select * from tb_user where userid='$userc'");
    if ($getuser) {
        $test = mysqli_fetch_array($getuser);
        $level = isset($test['level']) ? $test['level'] : '';
        $nama = isset($test['nama']) ? $test['nama'] : '';
        $idu = isset($test['id']) ? $test['id'] : ''; // Corrected column name from 'idu' to 'id'
    } else {
        $level = ''; $nama = ''; $idu = '';
    }

    // Log User count
    // Use $idu (User ID) instead of $userc (Username) because users_log stores ID
    $log4 = mysqli_query($sqlconn, "select COUNT(user) as n1 from users_log where user='$idu' order by waktu desc");
    $log5 = ($log4) ? mysqli_fetch_array($log4) : ['n1' => 0];

    // Log User Data
    $log1 = mysqli_query($sqlconn, "select * from users_log where user='$idu' order by waktu desc limit 25");
}
// Fungsi Helper
if (!function_exists('tgl_indo')) {
    function tgl_indo($tanggal)
    {
        $bulan = array(
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        );
        $pecahkan = explode('-', $tanggal);

        // variabel pecahkan 0 = tanggal
        // variabel pecahkan 1 = bulan
        // variabel pecahkan 2 = tahun

        if (count($pecahkan) === 3) {
            return $pecahkan[2] . ' ' . $bulan[(int) $pecahkan[1]] . ' ' . $pecahkan[0];
        }
        return $tanggal;
    }
}
?>