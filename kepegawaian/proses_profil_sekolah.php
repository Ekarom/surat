<?php
include "../dbconn.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$id = 1; // Assuming always id 1 for single school system

// Simple sanitation for text fields
$fields = [
    'sudin', 'kop_dinas', 'nsekolah', 'npsn', 'alamat', 'kecamatan', 'kelurahan', 
    'provinsi', 'kabupaten', 'kodepos', 'no_telp', 'email', 'website', 
    'nipkasudin', 'nrkkasudin', 'kasudin', 'kepsek', 'nipkepsek', 'nrkkepsek', 
    'pengawas', 'nippengawas', 'nrkpengawas', 'kasi', 'nipkasi', 'nrkkasi', 
    'ktu', 'nipktu', 'nrkktu', 'kode', 'youtube', 'facebook', 'twitter', 
    'instagram', 'cbt_link'
];

$updates = [];
foreach ($fields as $field) {
    if (isset($_POST[$field])) {
        $val = mysqli_real_escape_string($conn, $_POST[$field]);
        $updates[] = "$field = '$val'";
    }
}

// Handle File Uploads
$upload_dir = "../file/logo/";
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$files = [
    'logo_sekolah', 'background_login', 'logo_pemda', 'stempel_sekolah', 'ttd_kepsek'
];

foreach ($files as $file_field) {
    if (isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES[$file_field]['name'], PATHINFO_EXTENSION);
        $new_name = $file_field . "_" . time() . "." . $ext;
        if (move_uploaded_file($_FILES[$file_field]['tmp_name'], $upload_dir . $new_name)) {
            $updates[] = "$file_field = '$new_name'";
        }
    }
}

if (!empty($updates)) {
    $sql = "UPDATE profils SET " . implode(", ", $updates) . " WHERE id = $id";
    if ($conn->query($sql)) {
        echo json_encode(['status' => 'success', 'message' => 'Profil sekolah berhasil diperbarui.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui database: ' . $conn->error]);
    }
} else {
    echo json_encode(['status' => 'info', 'message' => 'Tidak ada perubahan data.']);
}
?>
