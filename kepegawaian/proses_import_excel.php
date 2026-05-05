<?php
ob_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    include "../dbconn.php";
    if (!$conn)
        throw new Exception("Koneksi database gagal.");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Hanya menerima request POST']);
        exit;
    }

    // Helper: Clean numeric values (handle scientific notation)
    function clean_numeric($val)
    {
        if (empty($val))
            return '';
        $val = trim($val);
        if (strpos(strtoupper($val), 'E+') !== false) {
            $val = sprintf("%.0f", (float) $val);
        }
        return preg_replace('/[^0-9]/', '', $val);
    }

    // Helper: Format date to YYYY-MM-DD
    function format_date_to_db($dateStr)
    {
        if (empty($dateStr) || $dateStr == '00-00-0000' || $dateStr == '-' || $dateStr == '0000-00-00')
            return NULL;

        $dateStr = trim($dateStr);

        // If it's already YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr))
            return $dateStr;

        // Try DD/MM/YYYY or DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateStr, $matches)) {
            return $matches[3] . '-' . sprintf('%02d', $matches[2]) . '-' . sprintf('%02d', $matches[1]);
        }

        // Try Excel serial number
        if (is_numeric($dateStr) && $dateStr > 10000 && $dateStr < 100000) {
            $unix_date = ($dateStr - 25569) * 86400;
            return date("Y-m-d", $unix_date);
        }

        // Try strtotime
        $time = strtotime($dateStr);
        if ($time)
            return date('Y-m-d', $time);

        return NULL;
    }

    // Get and Clean Basic Data
    $nip = clean_numeric($_POST['nip'] ?? '');
    if (empty($nip)) $nip = '0'; // Default to 0 if empty
    
    $nrk = clean_numeric($_POST['nrk'] ?? '');
    if (empty($nrk)) $nrk = '0'; // Default to 0 if empty

    $nama = trim(mysqli_real_escape_string($conn, $_POST['nama'] ?? ''));
    $tempat_lahir = mysqli_real_escape_string($conn, $_POST['tempat_lahir'] ?? '');

    // Format Dates
    $tgl_lahir = format_date_to_db($_POST['tgl_lahir'] ?? '');
    $tgl_lulus = format_date_to_db($_POST['tgl_lulus'] ?? '');
    $tmt_golongan = format_date_to_db($_POST['tmt_golongan'] ?? '');

    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin'] ?? '');
    // Normalisasi Jenis Kelamin (Laki-laki -> L, Perempuan -> P)
    $jk_upper = strtoupper(trim($jenis_kelamin));
    if (strpos($jk_upper, 'LAKI') !== false || $jk_upper == 'L') {
        $jenis_kelamin = 'L';
    } elseif (strpos($jk_upper, 'PEREMPUAN') !== false || $jk_upper == 'P') {
        $jenis_kelamin = 'P';
    } else {
        $jenis_kelamin = 'L'; // Default L
    }

    $pendidikan = mysqli_real_escape_string($conn, $_POST['pendidikan'] ?? '');
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan'] ?? '');
    $pangkat = mysqli_real_escape_string($conn, $_POST['pangkat'] ?? '');
    $golongan = mysqli_real_escape_string($conn, $_POST['golongan'] ?? '');
    $unit_kerja = mysqli_real_escape_string($conn, $_POST['unit_kerja'] ?? '');
    $status_pegawai = mysqli_real_escape_string($conn, $_POST['status_pegawai'] ?? '');
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');
    $rt = mysqli_real_escape_string($conn, $_POST['rt'] ?? '');
    $rw = mysqli_real_escape_string($conn, $_POST['rw'] ?? '');
    $kelurahan = mysqli_real_escape_string($conn, $_POST['kelurahan'] ?? '');
    $kecamatan = mysqli_real_escape_string($conn, $_POST['kecamatan'] ?? '');
    $nuptk = mysqli_real_escape_string($conn, $_POST['nuptk'] ?? '');
    $agama = mysqli_real_escape_string($conn, $_POST['agama'] ?? '');
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat'] ?? '');

    if (empty($nama)) {
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => 'Data tidak lengkap: Nama (Kolom C) wajib diisi.'
        ]);
        exit;
    }

    // Check if NIP exists
    $check = $conn->query("SELECT id FROM pegawai WHERE nip = '$nip'");

    if ($check->num_rows > 0) {
        // UPDATE existing record
        $conn->query("UPDATE pegawai SET 
                                    nrk = '$nrk', 
                                    nm_pegawai = '$nama', 
                                    tempat_lahir = '$tempat_lahir', 
                                    tgl_lahir = " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ", 
                                    jenis_kelamin = '$jenis_kelamin', 
                                    pendidikan = '$pendidikan', 
                                    tgl_lulus = " . ($tgl_lulus ? "'$tgl_lulus'" : "NULL") . ", 
                                    jabatan = '$jabatan', 
                                    pangkat = '$pangkat', 
                                    golongan = '$golongan', 
                                    tmt_golongan = " . ($tmt_golongan ? "'$tmt_golongan'" : "NULL") . ",
                                    unit_kerja = '$unit_kerja', 
                                    status_pegawai = '$status_pegawai', 
                                    no_hp = '$no_hp', 
                                    email = '$email', 
                                    rt = '$rt', 
                                    rw = '$rw', 
                                    kelurahan = '$kelurahan', 
                                    kecamatan = '$kecamatan',
                                    nuptk = '$nuptk',
                                    agama = '$agama',
                                    alamat = '$alamat'
                                WHERE nip = '$nip'");

        ob_clean();
        echo json_encode(['status' => 'success', 'mode' => 'update']);
        exit;
    } else {
        // INSERT new record
        $conn->query("INSERT INTO pegawai (
                                    nip, nrk, nm_pegawai, tempat_lahir, tgl_lahir, jenis_kelamin, 
                                    pendidikan, tgl_lulus, jabatan, pangkat, golongan, tmt_golongan,
                                    unit_kerja, status_pegawai, no_hp, email, rt, rw, kelurahan, kecamatan, status, nuptk, agama, alamat
                               ) VALUES (
                                    '$nip', '$nrk', '$nama', '$tempat_lahir', " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ", '$jenis_kelamin', 
                                    '$pendidikan', " . ($tgl_lulus ? "'$tgl_lulus'" : "NULL") . ", '$jabatan', '$pangkat', '$golongan', " . ($tmt_golongan ? "'$tmt_golongan'" : "NULL") . ", 
                                    '$unit_kerja', '$status_pegawai', '$no_hp', '$email', '$rt', '$rw', '$kelurahan', '$kecamatan', '1', '$nuptk', '$agama', '$alamat'
                               )");

        ob_clean();
        echo json_encode(['status' => 'success', 'mode' => 'insert']);
        exit;
    }
} catch (mysqli_sql_exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
    exit;
}
?>