<?php
header('Content-Type: application/json');
include "../dbconn.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // FORCE FIX FOR AUTO_INCREMENT (Mencegah Error Duplicate Entry '0')
    $conn->query("ALTER TABLE pegawai MODIFY id INT AUTO_INCREMENT");
    $conn->query("UPDATE pegawai SET id = 1 WHERE id = 0");
    
    $nip = mysqli_real_escape_string($conn, $_POST['nip'] ?? '');
    $nama = mysqli_real_escape_string($conn, $_POST['nama'] ?? '');
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan'] ?? '');
    $status_pegawai = mysqli_real_escape_string($conn, $_POST['status_pegawai'] ?? '');
    $unit_kerja = mysqli_real_escape_string($conn, $_POST['unit_kerja'] ?? '');

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

    $insert = $conn->query("INSERT INTO pegawai (nip, nm_pegawai, jabatan, status_pegawai, unit_kerja, status) 
                           VALUES ('$nip', '$nama', '$jabatan', '$status_pegawai', '$unit_kerja', '1')");

    if ($insert) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
}
?>
