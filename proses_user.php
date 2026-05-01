
<?php
// Aktifkan error reporting untuk debugging (matikan saat production)
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// PERBAIKAN: Gunakan dbconn.php sesuai file yang Anda miliki
if (file_exists('dbconn.php')) {
    include "dbconn.php";
} else {
    include "koneksi.php"; // Fallback jika dbconn tidak ada
} 

// Cek apakah koneksi berhasil
if (!isset($conn) || !$conn) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal!']);
    exit;
}

// Cek action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// --- FUNGSI HELPER UPLOAD image ---
function uploadimage($file) {
    $target_dir = "file/profil/";
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = 'user_' . time() . '_' . rand(100, 999) . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;

    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $new_filename;
    }
    return false;
}

// =================================================================================
// ACTION: MUAT DATA (READ) -> Output HTML
// =================================================================================
if ($action == 'muat') {
    $query = "SELECT * FROM tb_user ORDER BY id ASC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo '<table class="table table-striped table-hover align-middle">
                <thead class="bg-menu-gradient text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th>Username</th>
                        <th>Nama Lengkap</th>
                        <th class="text-center" hidden>Status</th>
                        <th class="text-center">Level</th>
                        <th class="text-center">Last Login</th>
                        <th class="text-center">IP</th>
                        <th class="text-center">Status</th>
                        <th width="15%" class="text-center">Edit</th>
                        <th width="15%" class="text-center">Del</th>
                        <th width="15%" class="text-center">Reset Pass</th>
                    </tr>
                </thead>
                <tbody>';
        
        $no = 1;
        while ($row = $result->fetch_assoc()) {
            // Fix: Use local data URI for placeholder to avoid DNS errors
            $no_img_svg = "data:image/svg+xml;charset=UTF-8,%3Csvg%20width%3D%2250%22%20height%3D%2250%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2050%2050%22%20preserveAspectRatio%3D%22none%22%3E%3Cdefs%3E%3Cstyle%20type%3D%22text%2Fcss%22%3E%23holder_1%20text%20%7B%20fill%3A%23AAAAAA%3Bfont-weight%3Abold%3Bfont-family%3AArial%2C%20Helvetica%2C%20Open%20Sans%2C%20sans-serif%2C%20monospace%3Bfont-size%3A10pt%20%7D%20%3C%2Fstyle%3E%3C%2Fdefs%3E%3Cg%20id%3D%22holder_1%22%3E%3Crect%20width%3D%2250%22%20height%3D%2250%22%20fill%3D%22%23EEEEEE%22%3E%3C%2Frect%3E%3Cg%3E%3Ctext%20x%3D%224%22%20y%3D%2229%22%3ENo%20Img%3C%2Ftext%3E%3C%2Fg%3E%3C%2Fg%3E%3C%2Fsvg%3E";
            
            $imagePath = !empty($row['poto']) && file_exists('file/profil/' . $row['poto']) ? 'file/profil/' . $row['poto'] : $no_img_svg;
            
            // --- PERUBAHAN DISINI: Logika Status Menyesuaikan Varchar(2) ---
            // Database mungkin berisi '1', 'Ak', atau 'Aktif'. Kita anggap semua itu sebagai status Aktif.
            $st = $row['status'];
            $is_active = ($st == '1' || $st == 'Aktif');
            
            $isChecked = $is_active ? 'checked' : '';
            $statusLabel = $is_active ? 'Aktif' : 'Nonaktif';
            
            // Kita tambahkan class 'status-switch' untuk ditangkap oleh jQuery/JS
            $switchButton = '
            <div class="form-check form-switch d-flex justify-content-center align-items-center gap-2">
                <input class="form-check-input status-switch" type="checkbox" role="switch" 
                    data-id="'.$row['id'].'" 
                    id="switch'.$row['id'].'" 
                    style="cursor: pointer;"
                    '.$isChecked.'>
            </div>';



            echo '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td>' . htmlspecialchars($row['userid']) . '</td>
                    <td>' . htmlspecialchars($row['nama']) . '</td>
                    <td class="text-center">';
                    
                    // Level badge with colors
                    $levelBadge = '';
                    if ($row['level'] == '1') {
                        $levelBadge = '<span class="badge bg-danger">Administrator</span>';
                    } elseif ($row['level'] == '2') {
                        $levelBadge = '<span class="badge bg-warning text-dark">Staff</span>';
                    } elseif ($row['level'] == '4') {
                        $levelBadge = '<span class="badge bg-success">Guru</span>';
                    } else {
                        $levelBadge = '<span class="badge bg-secondary">User</span>';
                    }
                    echo $levelBadge . '</td>
                    
                    <td class="text-center">' . htmlspecialchars($row['last_login']) . '</td>
                    <td class="text-center">' . htmlspecialchars($row['ip']) . '</td>
                    <td class="text-center" hidden>' . $statusLabel . '</td>
                    <td class="text-center">' . $switchButton . '</td>
                    <td class="text-center">
                        <button class="btn btn-info btn-sm tombol-edit" data-id="' . $row['id'] . '" title="Edit">
                            <i class="fas bi-pencil-square"></i>
                        </button>
                        </td>
                        <td class="text-center">
                        <button class="btn btn-danger btn-sm tombol-hapus" data-id="' . $row['id'] . '" title="Hapus">
                            <i class="fas bi-trash"></i>
                        </button>
                        </td>
                        <td class="text-center">
                        <button class="btn btn-warning btn-sm tombol-reset-pass" data-id="' . $row['id'] . '" data-nama="' . htmlspecialchars($row['nama']) . '" title="Reset Password">
                            <i class="fas fa-key"></i>
                        </button>
                        </td>
                  </tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<div class="alert alert-warning text-center">Data pengguna belum ada atau query gagal.</div>';
    }
    exit;
}

// =================================================================================
// ACTION: UBAH STATUS (SWITCH) -> Output JSON
// =================================================================================
if ($action == 'ubah_status') {
    header('Content-Type: application/json');

    $id = $_POST['id'];
    $statusInput = $_POST['status']; // Menerima 'Aktif' atau 'Nonaktif' dari JS
    
    // --- PERUBAHAN DISINI: Konversi ke Varchar(2) ---
    // Jika input 'Aktif' -> Simpan '1'
    // Jika input 'Nonaktif' -> Simpan '0'
    $statusDB = ($statusInput == 'Aktif' || $statusInput == '1') ? '1' : '0';

    $stmt = $conn->prepare("UPDATE tb_user SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $statusDB, $id);

    if ($stmt->execute()) {
        // Kembalikan pesan 'Aktif'/'Nonaktif' agar UI tetap user friendly
        $pesanStatus = ($statusDB == '1') ? 'Aktif' : 'Nonaktif';
        echo json_encode(['status' => 'success', 'message' => 'Status berhasil diubah menjadi ' . $pesanStatus]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah status: ' . $stmt->error]);
    }
    exit;
}

// =================================================================================
// ACTION: AMBIL SATU DATA (READ SINGLE) -> Output JSON
// =================================================================================
if ($action == 'ambil') {
    header('Content-Type: application/json');
    
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM tb_user WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        // Normalize status for frontend form (optional)
        // Jika di DB '1', kirim 'Aktif' ke form edit agar dropdown terpilih benar (jika pakai dropdown)
        if($data) {
             if($data['status'] == '1') $data['status'] = 'Aktif';
             else if($data['status'] == '0') $data['status'] = 'Nonaktif';
        }
        
        echo json_encode($data);
    } else {
        echo json_encode(null);
    }
    exit;
}

// =================================================================================
// ACTION: SIMPAN (INSERT / UPDATE) -> Output JSON
// =================================================================================
if ($action == 'simpan') {
    header('Content-Type: application/json');

    $id = $_POST['id']; 
    $nama = $_POST['nama'];
    $userid = $_POST['userid'];
    $password = $_POST['password'] ?? ''; 
    $level = $_POST['level'];
    $nik = $_POST['nik'];       
    $statusInput = $_POST['status']; // Input dari form (biasanya 'Aktif'/'Nonaktif')
    $poto_lama = isset($_POST['poto_lama']) ? $_POST['poto_lama'] : '';

    // --- PERUBAHAN DISINI: Sanitasi Status untuk Varchar(2) ---
    $statusDB = ($statusInput == 'Aktif' || $statusInput == '1') ? '1' : '0';
    $email = $_POST['email'] ?? '';

    // Upload Image Logic
    $image = $poto_lama; 
    if (isset($_FILES['poto']) && $_FILES['poto']['error'] == 0) {
        $upload = uploadimage($_FILES['poto']);
        if ($upload) {
            $image = $upload;
            if (!empty($poto_lama) && file_exists("file/profil/" . $poto_lama)) {
                unlink("file/profil/" . $poto_lama);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal upload image.']);
            exit;
        }
    }

    if (empty($id)) {
        // === INSERT ===
        // Set Default Password = UserID jika kosong
        if (empty($password)) {
            $password = $userid;
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $email_code = "";
        $email_code_expired = date('Y-m-d H:i:s');
        $ip_user = $_SERVER['REMOTE_ADDR'] ?? '';
        // Fix: Use valid default values for strict SQL mode
        $last_login = date('Y-m-d H:i:s'); // Use current time as placeholder if NULL not allowed
        $idu_val = "0"; // Use "0" for integer column, not empty string
        
        $sql = "INSERT INTO tb_user (nama, userid, nik, password, level, status, poto, email, email_code, email_code_expired, ip, last_login, idu) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        // Ensure level is treated as string for bind_param if necessary, but "s" is used for all here.
        $stmt->bind_param("sssssssssssss", $nama, $userid, $nik, $hashed_password, $level, $statusDB, $image, $email, $email_code, $email_code_expired, $ip_user, $last_login, $idu_val);

    } else {
        // === UPDATE ===
        // Bangun query secara dinamis agar tidak menimpa data yang tidak berubah
        $types = "ssssss"; // nama, userid, nik, level, status, email
        $values = [$nama, $userid, $nik, $level, $statusDB, $email];
        $set_clauses = ["nama=?", "userid=?", "nik=?", "level=?", "status=?", "email=?"];
        
        // Cek Password
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $set_clauses[] = "password=?";
            $types .= "s";
            $values[] = $hashed_password;
        }

        // Cek Foto (Hanya update kolom database jika ada upload baru)
        if (isset($_FILES['poto']) && $_FILES['poto']['error'] == 0 && $upload) {
            $set_clauses[] = "poto=?";
            $types .= "s";
            $values[] = $image; // $image contains the new filename from uploadimage()
        }
        // NOTE: Jika tidak ada upload baru, kita JANGAN update kolom poto.
        // Ini mencegah foto lama terhapus jika hidden input poto_lama kosong/tidak terkirim.

        // Finalize Query
        $sql = "UPDATE tb_user SET " . implode(", ", $set_clauses) . " WHERE id=?";
        $types .= "i";
        $values[] = $id;

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$values);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil disimpan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $stmt->error]);
    }
    exit;
}

// =================================================================================
// ACTION: HAPUS -> Output JSON
// =================================================================================
if ($action == 'hapus') {
    header('Content-Type: application/json');

    $id = $_POST['id'];
    
    $stmt_cek = $conn->prepare("SELECT poto FROM tb_user WHERE id = ?");
    $stmt_cek->bind_param("i", $id);
    $stmt_cek->execute();
    $result = $stmt_cek->get_result();
    $row = $result->fetch_assoc();

    if ($row && !empty($row['poto']) && file_exists("file/profil/" . $row['poto'])) {
        unlink("file/profil/" . $row['poto']);
    }

    $stmt = $conn->prepare("DELETE FROM tb_user WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Data berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    exit;
}

// =================================================================================
// ACTION: RESET PASSWORD -> Output JSON
// =================================================================================
if ($action == 'reset_password') {
    header('Content-Type: application/json');

    $id = $_POST['id'];
    
    // Get user data to use userid as default password
    $stmt_get = $conn->prepare("SELECT userid, nama FROM tb_user WHERE id = ?");
    $stmt_get->bind_param("i", $id);
    $stmt_get->execute();
    $result = $stmt_get->get_result();
    $user = $result->fetch_assoc();
    
    if (!$user) {
        echo json_encode(['status' => 'error', 'message' => 'User tidak ditemukan.']);
        exit;
    }
    
    // Reset password to userid (username)
    $default_password = $user['userid'];
    $hashed_password = password_hash($default_password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("UPDATE tb_user SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $id);

    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success', 
            'message' => 'Password untuk <strong>' . htmlspecialchars($user['nama']) . '</strong> berhasil direset!<br><small class="text-muted">Password default: <strong>' . htmlspecialchars($default_password) . '</strong></small>'
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal mereset password: ' . $stmt->error]);
    }
    exit;
}
?>