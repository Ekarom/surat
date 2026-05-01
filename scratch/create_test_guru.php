<?php
include 'dbconn.php';
$userid = 'guru';
$pass = password_hash('guru123', PASSWORD_DEFAULT);
$nama = 'GURU TESTER';
$level = '4';
$status = '1';
$nik = '12345678';

$stmt = $conn->prepare("INSERT INTO tb_user (userid, password, nama, level, status, nik) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $userid, $pass, $nama, $level, $status, $nik);
if ($stmt->execute()) {
    echo "User 'guru' created with password 'guru123'.\n";
} else {
    echo "Error: " . $stmt->error . "\n";
}
?>
