<?php
// processes for personnel management
if (file_exists('../dbconn.php')) {
    include "../dbconn.php";
} else if (file_exists('dbconn.php')) {
    include "dbconn.php";
} else {
    die(json_encode(['status' => 'error', 'message' => 'Database connection file not found.']));
}

// Cek apakah koneksi berhasil
if (!isset($conn) || !$conn) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal!']);
    exit;
}

// Ensure pegawai exists
$createTableQuery = "CREATE TABLE IF NOT EXISTS pegawai (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nip VARCHAR(30) UNIQUE,
    nm_pegawai VARCHAR(100) NOT NULL,
    tempat_lahir VARCHAR(50),
    tgl_lahir DATE,
    jenis_kelamin ENUM('L', 'P'),
    jabatan VARCHAR(100),
    pangkat VARCHAR(100),
    golongan VARCHAR(10),
    unit_kerja VARCHAR(100),
    status_pegawai VARCHAR(50),
    pendidikan VARCHAR(50),
    no_hp VARCHAR(20),
    email VARCHAR(100),
    foto VARCHAR(255),
    status VARCHAR(2) DEFAULT '1'
)";
$conn->query($createTableQuery);

// Fix if ID is not auto_increment (prevent ID=0 issue)
$conn->query("ALTER TABLE pegawai MODIFY id INT AUTO_INCREMENT PRIMARY KEY");
$conn->query("UPDATE pegawai SET id = 1 WHERE id = 0"); // Fix existing 0 id if any

// Migration: Ensure all columns exist and have correct types
$columns_to_ensure = [
    'nip' => "VARCHAR(30)",
    'nm_pegawai' => "VARCHAR(100)",
    'tempat_lahir' => "VARCHAR(50)",
    'tgl_lahir' => "DATE",
    'jenis_kelamin' => "ENUM('L', 'P')",
    'jabatan' => "VARCHAR(100)",
    'pangkat' => "VARCHAR(100)",
    'golongan' => "VARCHAR(10)",
    'unit_kerja' => "VARCHAR(100)",
    'status_pegawai' => "VARCHAR(50)",
    'pendidikan' => "VARCHAR(50)",
    'tgl_lulus' => "DATE",
    'no_hp' => "VARCHAR(20)",
    'email' => "VARCHAR(100)",
    'foto' => "VARCHAR(255)",
    'status' => "VARCHAR(2) DEFAULT '1'",
    'tmt_golongan' => "DATE"
];

foreach ($columns_to_ensure as $col => $type) {
    $check = $conn->query("SHOW COLUMNS FROM pegawai LIKE '$col'");
    if ($check->num_rows == 0) {
        $conn->query("ALTER TABLE pegawai ADD COLUMN $col $type");
    } else {
        // Ensure column has enough length/correct type
        $conn->query("ALTER TABLE pegawai MODIFY COLUMN $col $type");
    }
}


$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// --- FUNGSI HELPER UPLOAD ---
function uploadFoto($file) {
    $target_dir = "../file/pegawai/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = 'pegawai_' . time() . '_' . rand(100, 999) . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    return false;
}

// --- MUAT DATA (HTML - Legacy) ---
if ($action == 'muatData') {
    // ... (keeping for backwards compatibility if needed, but we'll use JSON)
    // (Existing code...)
}

// --- MUAT DATA (JSON for DataTables) ---
if ($action == 'muatDataJSON') {
    header('Content-Type: application/json');
    $query = "SELECT * FROM pegawai ORDER BY nm_pegawai ASC";
    $result = $conn->query($query);
    $data = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => $data
    ]);
    exit;
}

// --- AMBIL SATU DATA ---
if ($action == 'ambil') {
    header('Content-Type: application/json');
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        echo json_encode(['status' => 'success', 'data' => $data]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil data.']);
    }
    exit;
}

// --- SIMPAN (INSERT / UPDATE) ---
if ($action == 'simpan') {
    header('Content-Type: application/json');
    $id = $_POST['id'] ?? '';
    $nip = $_POST['nip'];
    $nm_pegawai = $_POST['nm_pegawai'];
    $tempat_lahir = $_POST['tempat_lahir'];
    $tgl_lahir = $_POST['tgl_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $jabatan = $_POST['jabatan'];
    $pangkat = $_POST['pangkat'];
    $golongan = $_POST['golongan'];
    $unit_kerja = $_POST['unit_kerja'];
    $status_pegawai = $_POST['status_pegawai'];
    $pendidikan = $_POST['pendidikan'];
    $tgl_lulus = !empty($_POST['tgl_lulus']) ? $_POST['tgl_lulus'] : null;
    $tmt_golongan = !empty($_POST['tmt_golongan']) ? $_POST['tmt_golongan'] : null;
    $no_hp = $_POST['no_hp'];
    $email = $_POST['email'];
    $foto_lama = $_POST['foto_lama'] ?? '';
    $status = $_POST['status'] ?? '1';

    $foto = $foto_lama;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $upload = uploadFoto($_FILES['foto']);
        if ($upload) {
            $foto = $upload;
            if (!empty($foto_lama) && file_exists("../file/pegawai/" . $foto_lama)) {
                unlink("../file/pegawai/" . $foto_lama);
            }
        }
    }

    if (empty($id)) {
        // Insert
        $sql = "INSERT INTO pegawai (nip, nm_pegawai, tempat_lahir, tgl_lahir, jenis_kelamin, jabatan, pangkat, golongan, unit_kerja, status_pegawai, pendidikan, tgl_lulus, tmt_golongan, no_hp, email, foto, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssss", $nip, $nm_pegawai, $tempat_lahir, $tgl_lahir, $jenis_kelamin, $jabatan, $pangkat, $golongan, $unit_kerja, $status_pegawai, $pendidikan, $tgl_lulus, $tmt_golongan, $no_hp, $email, $foto, $status);
    } else {
        // Update
        $sql = "UPDATE pegawai SET nip=?, nm_pegawai=?, tempat_lahir=?, tgl_lahir=?, jenis_kelamin=?, jabatan=?, pangkat=?, golongan=?, unit_kerja=?, status_pegawai=?, pendidikan=?, tgl_lulus=?, tmt_golongan=?, no_hp=?, email=?, foto=?, status=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssssssssi", $nip, $nm_pegawai, $tempat_lahir, $tgl_lahir, $jenis_kelamin, $jabatan, $pangkat, $golongan, $unit_kerja, $status_pegawai, $pendidikan, $tgl_lulus, $tmt_golongan, $no_hp, $email, $foto, $status, $id);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Data pegawai berhasil disimpan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $stmt->error]);
    }
    exit;
}

// --- UBAH STATUS ---
if ($action == 'ubah_status') {
    header('Content-Type: application/json');
    $id = $_POST['id'];
    $status = $_POST['status'] == '1' ? '1' : '0';
    $stmt = $conn->prepare("UPDATE pegawai SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Status berhasil diubah!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah status.']);
    }
    exit;
}

// --- HAPUS ---
if ($action == 'hapus') {
    header('Content-Type: application/json');
    $id = $_POST['id'];
    // Get foto to delete file
    $stmt_cek = $conn->prepare("SELECT foto FROM pegawai WHERE id = ?");
    $stmt_cek->bind_param("i", $id);
    $stmt_cek->execute();
    $res = $stmt_cek->get_result()->fetch_assoc();
    if ($res && !empty($res['foto']) && file_exists("../file/pegawai/" . $res['foto'])) {
        unlink("../file/pegawai/" . $res['foto']);
    }

    $stmt = $conn->prepare("DELETE FROM pegawai WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Data pegawai berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    exit;
}
?>
