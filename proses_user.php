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
function uploadimage($file)
{
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
// ACTION: MUAT DATA (READ) -> Output HTML Table
// =================================================================================
if ($action == 'muat') {
    $query = "SELECT * FROM tb_user ORDER BY id ASC";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        echo '<div class="table-responsive">
                <table id="tableUser" class="table table-bordered table-striped table-hover align-middle mb-0 nowrap" style="width:100%">
                    <thead class="bg-menu-gradient text-white text-center">
                        <tr>
                            <th width="50">No</th>
                            <th>Username</th>
                            <th>Nama Lengkap</th>
                            <th>Level</th>
                            <th>Last Login</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>';

        $no = 1;
        while ($row = $result->fetch_assoc()) {
            // Level badge mapping
            $level_map = [
                '1' => ['label' => 'Administrator', 'class' => 'bg-danger'],
                '2' => ['label' => 'Staff', 'class' => 'bg-warning text-dark'],
                '4' => ['label' => 'Guru', 'class' => 'bg-success'],
            ];
            $lvl = $level_map[$row['level']] ?? ['label' => 'User', 'class' => 'bg-secondary'];
            $levelBadge = '<span class="badge ' . $lvl['class'] . ' rounded-pill px-3">' . $lvl['label'] . '</span>';

            // Status Switch Logic
            $is_active = ($row['status'] == '1' || $row['status'] == 'Aktif');
            $isChecked = $is_active ? 'checked' : '';
            $switchButton = '
                <div class="form-check form-switch d-flex justify-content-center">
                    <input class="form-check-input status-switch" type="checkbox" role="switch" 
                        data-id="' . $row['id'] . '" 
                        id="switch' . $row['id'] . '" 
                        style="cursor: pointer; width: 40px; height: 20px;"
                        ' . $isChecked . '>
                </div>';

            echo '<tr>
                    <td class="text-center">' . $no++ . '</td>
                    <td class="fw-bold">' . htmlspecialchars($row['userid']) . '</td>
                    <td>' . htmlspecialchars($row['nama']) . '</td>
                    <td class="text-center">' . $levelBadge . '</td>
                    <td class="text-center small text-muted">' . ($row['last_login'] ?: '-') . '</td>
                    <td class="text-center small">' . ($row['ip'] ?: '-') . '</td>
                    <td class="text-center">' . $switchButton . '</td>
                    <td class="text-center">
                        <div class="btn-group shadow-sm">
                            <span class="badge badge-warning badge-square tombol-edit" data-id="' . $row['id'] . '" title="Edit Data">
                                <i class="las la-edit fs-5"></i>
                            </span>
                            <span class="badge badge-danger badge-square tombol-hapus" data-id="' . $row['id'] . '" title="Hapus User">
                                <i class="las la-trash fs-5"></i>
                            </span>
                            <span class="badge badge-info text-white badge-square tombol-reset-pass" data-id="' . $row['id'] . '" data-nama="' . htmlspecialchars($row['nama']) . '" title="Reset Password">
                                <i class="las la-key fs-5"></i>
                            </span>
                        </div>
                    </td>
                  </tr>';
        }
        echo '</tbody></table></div>';
    } else {
        echo '<div class="alert alert-light border text-center my-3">
                <i class="las la-info-circle fs-3 text-warning d-block mb-2"></i>
                Belum ada data pengguna ditemukan.
              </div>';
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
        add_activity_log($conn, 'User', 'Ubah Status', "ID: $id menjadi $pesanStatus");
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

    $id = $_GET['id'] ?? 0;
    $stmt = $conn->prepare("SELECT * FROM tb_user WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();

        if ($data) {
            // Normalize status for frontend form
            $data['status'] = ($data['status'] == '1' || $data['status'] == 'Aktif') ? 'Aktif' : 'Nonaktif';
        }

        echo json_encode($data);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query error']);
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
        $password = !empty($password) ? $password : $userid; // Default password = userid
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $email_code = "";
        $email_code_expired = date('Y-m-d H:i:s');
        $ip_user = $_SERVER['REMOTE_ADDR'] ?? '';
        $last_login = date('Y-m-d H:i:s');
        $idu_val = 0;

        $sql = "INSERT INTO tb_user (nama, userid, nik, password, level, status, poto, email, email_code, email_code_expired, ip, last_login, idu) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssssssssssi", $nama, $userid, $nik, $hashed_password, $level, $statusDB, $image, $email, $email_code, $email_code_expired, $ip_user, $last_login, $idu_val);

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
        $aksi = empty($id) ? "Tambah" : "Update";
        add_activity_log($conn, 'User', $aksi, "User ID: $userid");
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
        add_activity_log($conn, 'User', 'Hapus', "ID: $id");
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
        add_activity_log($conn, 'User', 'Reset Password', "User: " . $user['userid']);
        echo json_encode([
            'status' => 'success',
            'message' => 'Password untuk <strong>' . htmlspecialchars($user['nama']) . '</strong> berhasil direset!<br><small class="text-muted">Password default: <strong>' . htmlspecialchars($default_password) . '</strong></small>'
        ]);
    }
    exit;
}

// =================================================================================
// ACTION: BERSIHKAN LOG -> Output JSON
// =================================================================================
if ($action == 'bersihkan_log') {
    header('Content-Type: application/json');

    $lv_sess = $_SESSION['level'] ?? '';
    if ($lv_sess != '1') {
        echo json_encode(['status' => 'error', 'message' => 'Akses ditolak!']);
        exit;
    }

    // Hapus log yang lebih lama dari 30 hari
    $stmt = $conn->prepare("DELETE FROM tb_activity_log WHERE waktu < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    
    if ($stmt->execute()) {
        $deleted = $stmt->affected_rows;
        add_activity_log($conn, 'System', 'Bersihkan Log', "Membersihkan log lama ($deleted baris dihapus)");
        echo json_encode(['status' => 'success', 'message' => "$deleted baris log lama berhasil dibersihkan."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal membersihkan log: ' . $stmt->error]);
    }
    exit;
}
?>