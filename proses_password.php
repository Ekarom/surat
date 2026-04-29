<?php
session_start();

// Konfigurasi Header JSON
header('Content-Type: application/json');

// Cek Koneksi Database
if (file_exists("dbconn.php")) {
    include "dbconn.php";
} else {
    echo json_encode(['status' => 'error', 'message' => 'File database tidak ditemukan.']);
    exit;
}

// Cek Login User
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesi habis, silakan login kembali.']);
    exit;
}

$id_user = $_SESSION['id'];

// Ambil Data Input
$old_password = $_POST['old_password'] ?? '';
$new_password = $_POST['new_password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Validasi Input Kosong
if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
    echo json_encode(['status' => 'error', 'message' => 'Semua kolom harus diisi!']);
    exit;
}

// Validasi Password Baru Match
if ($new_password !== $confirm_password) {
    echo json_encode(['status' => 'error', 'message' => 'Konfirmasi password baru tidak cocok!']);
    exit;
}

// Ambil Password Lama dari DB
$stmt = $conn->prepare("SELECT password FROM tb_user WHERE id = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
    exit;
}

// Verifikasi Password Lama
if (!password_verify($old_password, $user['password'])) {
    echo json_encode(['status' => 'error', 'message' => 'Password lama salah!']);
    exit;
}

// Hash Password Baru
$new_hash = password_hash($new_password, PASSWORD_DEFAULT);

// Update Password di DB
$update_stmt = $conn->prepare("UPDATE tb_user SET password = ? WHERE id = ?");
$update_stmt->bind_param("si", $new_hash, $id_user);

if ($update_stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Password berhasil diubah!']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Gagal mengupdate password: ' . $conn->error]);
}
?>
