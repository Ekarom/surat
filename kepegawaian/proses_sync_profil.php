<?php
/**
 * Script Sinkronisasi Profil Sekolah
 * Mengambil data dari Master Database ke Database Aktif
 */
include "../dbconn.php";
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// 1. Identifikasi Database Master
// Secara default kita asumsikan 'sas_' atau database yang ada di db.txt sebagai master
$db_master_name = "sas_"; 
$conn_master = @new mysqli($host, $user, $pass, $db_master_name);

if ($conn_master->connect_error) {
    // Jika sas_ tidak ada, gunakan db_initial (master dari dbconn.php)
    $db_master_name = isset($db_initial) ? $db_initial : $db;
    $conn_master = @new mysqli($host, $user, $pass, $db_master_name);
}

if ($conn_master->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Gagal terhubung ke database Master: ' . $conn_master->connect_error]);
    exit;
}

// 2. Ambil data dari Master
$query = "SELECT * FROM profils WHERE id = 1";
$res = $conn_master->query($query);

if ($res && $res->num_rows > 0) {
    $master_data = $res->fetch_assoc();
    $conn_master->close();

    // 3. Update ke Database Saat Ini ($conn dari dbconn.php)
    $updates = [];
    foreach ($master_data as $key => $val) {
        if ($key === 'id') continue; // Jangan update ID
        
        // Escape data
        $clean_val = mysqli_real_escape_string($conn, $val);
        $updates[] = "$key = '$clean_val'";
    }

    if (!empty($updates)) {
        // Pastikan record id=1 ada di tabel tujuan
        $conn->query("INSERT IGNORE INTO profils (id) VALUES (1)");
        
        $sql_sync = "UPDATE profils SET " . implode(", ", $updates) . " WHERE id = 1";
        if ($conn->query($sql_sync)) {
            echo json_encode([
                'status' => 'success', 
                'message' => 'Data sekolah berhasil disinkronkan dari profil utama (' . $db_master_name . ').'
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal memperbarui data lokal: ' . $conn->error]);
        }
    } else {
        echo json_encode(['status' => 'info', 'message' => 'Tidak ada data yang perlu disinkronkan.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Data profil tidak ditemukan di database Master.']);
}
?>
