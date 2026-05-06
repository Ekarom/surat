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
$db_master_name = $db_master ?? "sas_";
$conn_master = @new mysqli($host, $user, $pass, $db_master_name);

if ($conn_master->connect_error) {
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

    // 3. Persiapkan Kolom - Pastikan tabel lokal memiliki kolom yang sama
    foreach ($master_data as $key => $val) {
        if ($key === 'id') continue;
        // Cek apakah kolom ada di tabel lokal
        $check_col = $conn->query("SHOW COLUMNS FROM profils LIKE '$key'");
        if ($check_col && $check_col->num_rows === 0) {
            // Tambahkan kolom jika belum ada
            $conn->query("ALTER TABLE profils ADD COLUMN `$key` TEXT NULL");
        }
    }

    // 4. Update ke Database Saat Ini ($conn dari dbconn.php)
    $updates = [];
    foreach ($master_data as $key => $val) {
        if ($key === 'id') continue; 
        
        // Escape data
        $clean_val = mysqli_real_escape_string($conn, $val);
        $updates[] = "`$key` = '$clean_val'";
    }

    if (!empty($updates)) {
        // Pastikan record id=1 ada di tabel tujuan
        $conn->query("INSERT IGNORE INTO profils (id) VALUES (1)");
        
        $sql_sync = "UPDATE profils SET " . implode(", ", $updates) . " WHERE id = 1";
        if ($conn->query($sql_sync)) {
            // Log Aktivitas
            if (function_exists('add_activity_log')) {
                add_activity_log($conn, 'Profil', 'Sinkronisasi', 'Sinkronisasi data profil dari ' . $db_master_name);
            }

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
    echo json_encode(['status' => 'error', 'message' => 'Data profil tidak ditemukan di database Master (' . $db_master_name . ').']);
}
?>
