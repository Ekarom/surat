<?php
header('Content-Type: application/json');
include "../dbconn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FORCE FIX FOR AUTO_INCREMENT (Mencegah Error Duplicate Entry '0')
    $conn->query("ALTER TABLE pegawai MODIFY id INT AUTO_INCREMENT");
    $conn->query("UPDATE pegawai SET id = 1 WHERE id = 0");

    // Helper function to format date to YYYY-MM-DD
    function format_date_to_db($dateStr)
    {
        if (empty($dateStr) || $dateStr == '00-00-0000' || $dateStr == '-')
            return NULL;

        // If it's already YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr))
            return $dateStr;

        // Try DD/MM/YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateStr, $matches)) {
            return $matches[3] . '-' . sprintf('%02d', $matches[2]) . '-' . sprintf('%02d', $matches[1]);
        }

        // Try Excel serial number (if raw:true was used, but we'll try to handle numeric strings)
        if (is_numeric($dateStr) && $dateStr > 10000) {
            // Excel dates start from 1900-01-01
            $unix_date = ($dateStr - 25569) * 86400;
            return date("d-m-Y", $unix_date);
        }

        // Try strtotime
        $time = strtotime($dateStr);
        if ($time)
            return date('d-m-Y', $time);

        return NULL;
    }

    $nip = mysqli_real_escape_string($conn, $_POST['nip'] ?? '');
    $nama = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
    $tempat_lahir = mysqli_real_escape_string($conn, $_POST['tempat_lahir'] ?? '');

    // Format Dates
    $tgl_lahir = format_date_to_db($_POST['tgl_lahir'] ?? '');
    $tgl_lulus = format_date_to_db($_POST['tgl_lulus'] ?? '');
    $tmt_golongan = format_date_to_db($_POST['tmt_golongan'] ?? '');

    $jenis_kelamin = mysqli_real_escape_string($conn, $_POST['jenis_kelamin'] ?? '');
    $pendidikan = mysqli_real_escape_string($conn, $_POST['pendidikan'] ?? '');
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan'] ?? '');
    $pangkat = mysqli_real_escape_string($conn, $_POST['pangkat'] ?? '');
    $golongan = mysqli_real_escape_string($conn, $_POST['golongan'] ?? '');
    $unit_kerja = mysqli_real_escape_string($conn, $_POST['unit_kerja'] ?? '');
    $status_pegawai = mysqli_real_escape_string($conn, $_POST['status_pegawai'] ?? '');
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp'] ?? '');
    $email = mysqli_real_escape_string($conn, $_POST['email'] ?? '');

    if (empty($nip) || empty($nama)) {
        echo json_encode(['status' => 'error', 'message' => 'NIP dan Nama wajib diisi']);
        exit;
    }

    // Check if NIP exists
    $check = $conn->query("SELECT id FROM pegawai WHERE nip = '$nip'");
    if ($check->num_rows > 0) {
        echo json_encode(['status' => 'error', 'message' => 'NIP sudah terdaftar']);
        exit;
    }

    $insert = $conn->query("INSERT INTO pegawai (
                                nip, nm_pegawai, tempat_lahir, tgl_lahir, jenis_kelamin, 
                                pendidikan, tgl_lulus, jabatan, pangkat, golongan, tmt_golongan,
                                unit_kerja, status_pegawai, no_hp, email, status
                           ) VALUES (
                                '$nip', '$nama', '$tempat_lahir', " . ($tgl_lahir ? "'$tgl_lahir'" : "NULL") . ", '$jenis_kelamin', 
                                '$pendidikan', " . ($tgl_lulus ? "'$tgl_lulus'" : "NULL") . ", '$jabatan', '$pangkat', '$golongan', " . ($tmt_golongan ? "'$tmt_golongan'" : "NULL") . ", 
                                '$unit_kerja', '$status_pegawai', '$no_hp', '$email', '1'
                           )");

    if ($insert) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>