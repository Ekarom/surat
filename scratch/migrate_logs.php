<?php
include "dbconn.php";

echo "Memulai migrasi data dari users_log ke tb_activity_log...\n";

$res = $conn->query("SELECT * FROM users_log");
if (!$res) {
    die("Error: Tabel users_log tidak ditemukan atau gagal diakses.\n");
}

$count = 0;
while ($row = $res->fetch_assoc()) {
    $userid = $row['user'] ?? '';
    $nama = $row['nama'] ?? '';
    $level = $row['level'] ?? '';
    $waktu = $row['waktu'] ?? '';
    $ip = $row['ip'] ?? '';
    $info = $row['info'] ?? '';
    
    // Tentukan modul dan aksi secara cerdas berdasarkan info
    $modul = 'Legacy';
    $aksi = 'Aktivitas';
    
    if (stripos($info, 'Login') !== false) {
        $modul = 'Auth';
        $aksi = 'Login';
    } elseif (stripos($info, 'Logout') !== false) {
        $modul = 'Auth';
        $aksi = 'Logout';
    } elseif (stripos($info, 'Surat Masuk') !== false) {
        $modul = 'Surat Masuk';
        if (stripos($info, 'Tambah') !== false) $aksi = 'Tambah';
        elseif (stripos($info, 'Edit') !== false) $aksi = 'Edit';
        elseif (stripos($info, 'Hapus') !== false) $aksi = 'Hapus';
    } elseif (stripos($info, 'Surat Keluar') !== false) {
        $modul = 'Surat Keluar';
        if (stripos($info, 'Tambah') !== false) $aksi = 'Tambah';
        elseif (stripos($info, 'Edit') !== false) $aksi = 'Edit';
        elseif (stripos($info, 'Hapus') !== false) $aksi = 'Hapus';
    } elseif (stripos($info, 'User') !== false) {
        $modul = 'User';
        if (stripos($info, 'Tambah') !== false) $aksi = 'Tambah';
        elseif (stripos($info, 'Update') !== false || stripos($info, 'Ubah') !== false) $aksi = 'Update';
        elseif (stripos($info, 'Hapus') !== false) $aksi = 'Hapus';
        elseif (stripos($info, 'Reset') !== false) $aksi = 'Reset Password';
    }

    // Cek apakah data sudah ada (mencegah duplikasi jika dijalankan ulang)
    $check = $conn->prepare("SELECT id FROM tb_activity_log WHERE userid = ? AND waktu = ? AND info = ?");
    $check->bind_param("sss", $userid, $waktu, $info);
    $check->execute();
    $check_res = $check->get_result();
    
    if ($check_res->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO tb_activity_log (userid, nama, level, waktu, ip, modul, aksi, info) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssss", $userid, $nama, $level, $waktu, $ip, $modul, $aksi, $info);
        if ($stmt->execute()) {
            $count++;
        }
        $stmt->close();
    }
    $check->close();
}

echo "Migrasi selesai! Berhasil menyalin $count baris data baru ke tb_activity_log.\n";
?>
