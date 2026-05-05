<?php
ob_start();
try {
    if (file_exists('../dbconn.php')) {
        include "../dbconn.php";
    } else if (file_exists('dbconn.php')) {
        include "dbconn.php";
    } else {
        throw new Exception('Database connection file not found.');
    }

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Debugging: Log all requests
    $log_file = 'error_log.txt';
    $log_entry = date('Y-m-d H:i:s') . " - Action: " . ($_REQUEST['action'] ?? 'none') . " - User: " . ($_SESSION['id'] ?? 'none') . "\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);

    set_error_handler(function ($errno, $errstr, $errfile, $errline) use ($log_file) {
        $msg = "PHP Error [$errno]: $errstr in $errfile on line $errline\n";
        file_put_contents($log_file, $msg, FILE_APPEND);
        return false;
    });

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    // Check connection
    if (!isset($conn) || !$conn || $conn->connect_error) {
        echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
        exit;
    }

    // --- AJAX ACTIONS FIRST (to prevent migration overhead and output interference) ---
// Ensure pegawai table and columns exist before any actions
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
    nrk VARCHAR(30),
    status VARCHAR(2) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
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
        'nrk' => "VARCHAR(30)",
        'status' => "VARCHAR(2) DEFAULT '1'",
        'tmt_golongan' => "DATE",
        'nuptk' => "VARCHAR(30)",
        'agama' => "VARCHAR(30)",
        'nama_ibu' => "VARCHAR(100)",
        'nama_pasangan' => "VARCHAR(100)",
        'npwp' => "VARCHAR(30)",
        'nik' => "VARCHAR(30)",
        'no_kk' => "VARCHAR(30)",
        'no_karpeg' => "VARCHAR(30)",
        'no_taspen' => "VARCHAR(30)",
        'no_bpjs' => "VARCHAR(30)",
        'no_karis_karsu' => "VARCHAR(30)",
        'masa_kerja_thn' => "INT",
        'masa_kerja_bln' => "INT",
        'gaji_pokok' => "DECIMAL(15,2)",
        'alamat' => "TEXT",
        'rt' => "VARCHAR(10)",
        'rw' => "VARCHAR(10)",
        'kelurahan' => "VARCHAR(100)",
        'kecamatan' => "VARCHAR(100)",
        'hobby' => "TEXT",
        'pengalaman_kerja' => "TEXT",
        'last_activity' => "DATETIME"
    ];

    foreach ($columns_to_ensure as $col => $type) {
        $check = $conn->query("SHOW COLUMNS FROM pegawai LIKE '$col'");
        if ($check->num_rows == 0) {
            $conn->query("ALTER TABLE pegawai ADD COLUMN $col $type");
        } else {
            $conn->query("ALTER TABLE pegawai MODIFY COLUMN $col $type");
        }
    }

    // Migration for riwayat_kepegawaian
    $createRiwayatTable = "CREATE TABLE IF NOT EXISTS riwayat_kepegawaian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pegawai_id INT NOT NULL,
    kategori VARCHAR(50),
    deskripsi TEXT,
    tmt DATE,
    no_sk VARCHAR(100),
    tgl_sk DATE,
    file_lampiran VARCHAR(255),
    institusi VARCHAR(255),
    jurusan VARCHAR(100),
    no_ijazah VARCHAR(100),
    tgl_ijazah DATE,
    gelar_depan VARCHAR(20),
    gelar_belakang VARCHAR(20),
    tempat VARCHAR(255),
    durasi VARCHAR(50),
    masa_kerja_thn INT,
    masa_kerja_bln INT,
    gaji_pokok DECIMAL(15,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pegawai_id) REFERENCES pegawai(id) ON DELETE CASCADE
)";
    $conn->query($createRiwayatTable);

    // Ensure new columns exist for existing tables
    $riwayat_cols = [
        'institusi' => "VARCHAR(255)",
        'jurusan' => "VARCHAR(100)",
        'no_ijazah' => "VARCHAR(100)",
        'tgl_ijazah' => "DATE",
        'gelar_depan' => "VARCHAR(20)",
        'gelar_belakang' => "VARCHAR(20)",
        'tempat' => "VARCHAR(255)",
        'durasi' => "VARCHAR(50)",
        'masa_kerja_thn' => "INT",
        'masa_kerja_bln' => "INT",
        'gaji_pokok' => "DECIMAL(15,2)"
    ];
    foreach ($riwayat_cols as $col => $type) {
        $check = $conn->query("SHOW COLUMNS FROM riwayat_kepegawaian LIKE '$col'");
        if ($check->num_rows == 0) {
            $conn->query("ALTER TABLE riwayat_kepegawaian ADD COLUMN $col $type");
        }
    }

    // --- DOWNLOAD DATA PEGAWAI ---
    if ($action == 'download') {
        if (ob_get_level()) ob_end_clean();
        $filename = "Data_Pegawai_" . date('Y-m-d_His') . ".xls";
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo '<table border="1">';
        echo '<tr>
            <th style="background-color: #f2f2f2;">NO</th>
            <th style="background-color: #f2f2f2;">NIP</th>
            <th style="background-color: #f2f2f2;">NRK</th>
            <th style="background-color: #f2f2f2;">NAMA PEGAWAI</th>
            <th style="background-color: #f2f2f2;">NUPTK</th>
            <th style="background-color: #f2f2f2;">AGAMA</th>
            <th style="background-color: #f2f2f2;">IBU KANDUNG</th>
            <th style="background-color: #f2f2f2;">SUAMI/ISTRI</th>
            <th style="background-color: #f2f2f2;">NPWP</th>
            <th style="background-color: #f2f2f2;">NIK</th>
            <th style="background-color: #f2f2f2;">NO KK</th>
            <th style="background-color: #f2f2f2;">NO KARPEG</th>
            <th style="background-color: #f2f2f2;">NO TASPEN</th>
            <th style="background-color: #f2f2f2;">NO BPJS</th>
            <th style="background-color: #f2f2f2;">NO KARIS/KARSU</th>
            <th style="background-color: #f2f2f2;">MASA KERJA (THN)</th>
            <th style="background-color: #f2f2f2;">MASA KERJA (BLN)</th>
            <th style="background-color: #f2f2f2;">GAJI POKOK</th>
            <th style="background-color: #f2f2f2;">TEMPAT LAHIR</th>
            <th style="background-color: #f2f2f2;">TGL LAHIR</th>
            <th style="background-color: #f2f2f2;">JENIS KELAMIN</th>
            <th style="background-color: #f2f2f2;">JABATAN</th>
            <th style="background-color: #f2f2f2;">PANGKAT</th>
            <th style="background-color: #f2f2f2;">GOLONGAN</th>
            <th style="background-color: #f2f2f2;">UNIT KERJA</th>
            <th style="background-color: #f2f2f2;">STATUS PEGAWAI</th>
            <th style="background-color: #f2f2f2;">PENDIDIKAN</th>
            <th style="background-color: #f2f2f2;">TGL LULUS</th>
            <th style="background-color: #f2f2f2;">TMT GOLONGAN</th>
            <th style="background-color: #f2f2f2;">NO HP</th>
            <th style="background-color: #f2f2f2;">EMAIL</th>
            <th style="background-color: #f2f2f2;">ALAMAT</th>
            <th style="background-color: #f2f2f2;">STATUS AKTIF</th>
        </tr>';
        
        $query = "SELECT * FROM pegawai ORDER BY nm_pegawai ASC";
        $result = $conn->query($query);
        $no = 1;
        
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $no++ . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['nip'] ?: '-') . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['nrk'] ?: '-') . '</td>';
                echo '<td>' . ($row['nm_pegawai'] ?: '-') . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['nuptk'] ?: '-') . '</td>';
                echo '<td>' . ($row['agama'] ?: '-') . '</td>';
                echo '<td>' . ($row['nama_ibu'] ?: '-') . '</td>';
                echo '<td>' . ($row['nama_pasangan'] ?: '-') . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['npwp'] ?: '-') . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['nik'] ?: '-') . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['no_kk'] ?: '-') . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['no_karpeg'] ?: '-') . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['no_taspen'] ?: '-') . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['no_bpjs'] ?: '-') . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['no_karis_karsu'] ?: '-') . '</td>';
                echo '<td>' . ($row['masa_kerja_thn'] ?: '0') . '</td>';
                echo '<td>' . ($row['masa_kerja_bln'] ?: '0') . '</td>';
                echo '<td style="mso-number-format:\'#,##0\';">' . ($row['gaji_pokok'] ?: '0') . '</td>';
                echo '<td>' . ($row['tempat_lahir'] ?: '-') . '</td>';
                echo '<td>' . ($row['tgl_lahir'] ?: '-') . '</td>';
                echo '<td>' . ($row['jenis_kelamin'] == 'L' ? 'Laki-laki' : 'Perempuan') . '</td>';
                echo '<td>' . ($row['jabatan'] ?: '-') . '</td>';
                echo '<td>' . ($row['pangkat'] ?: '-') . '</td>';
                echo '<td>' . ($row['golongan'] ?: '-') . '</td>';
                echo '<td>' . ($row['unit_kerja'] ?: '-') . '</td>';
                echo '<td>' . ($row['status_pegawai'] ?: '-') . '</td>';
                echo '<td>' . ($row['pendidikan'] ?: '-') . '</td>';
                echo '<td>' . ($row['tgl_lulus'] ?: '-') . '</td>';
                echo '<td>' . ($row['tmt_golongan'] ?: '-') . '</td>';
                echo '<td style="mso-number-format:\'@\';">' . ($row['no_hp'] ?: '-') . '</td>';
                echo '<td>' . ($row['email'] ?: '-') . '</td>';
                echo '<td>' . ($row['alamat'] ?: '-') . '</td>';
                echo '<td>' . ($row['status'] == '1' ? 'Aktif' : 'Non-Aktif') . '</td>';
                echo '</tr>';
            }
        }
        echo '</table>';
        exit;
    }

    // --- SYNC PENSIUN ---
    if ($action == 'syncPensiun') {
        header('Content-Type: application/json');
        
        // Audit data: check employees with missing birth dates
        $q_missing = $conn->query("SELECT COUNT(*) FROM pegawai WHERE (tgl_lahir IS NULL OR tgl_lahir = '0000-00-00') AND status = '1'");
        $missing_count = $q_missing->fetch_row()[0];
        
        $q_total = $conn->query("SELECT COUNT(*) FROM pegawai WHERE status = '1'");
        $total_count = $q_total->fetch_row()[0];
        
        $valid_count = $total_count - $missing_count;
        
        ob_clean();
        echo json_encode([
            'status' => 'success', 
            'message' => "Sinkronisasi berhasil! $valid_count data pegawai siap dihitung masa pensiunnya. ($missing_count data tidak memiliki tgl lahir).",
            'stats' => [
                'total' => $total_count,
                'valid' => $valid_count,
                'missing' => $missing_count
            ]
        ]);
        exit;
    }

    // --- AJAX ACTIONS ---
    if ($action == 'listPegawai') {
        $q = $conn->query("SELECT id, nm_pegawai, nip, nrk FROM pegawai WHERE status = '1' ORDER BY nm_pegawai ASC");
        $data = [];
        while ($r = $q->fetch_assoc()) {
            $data[] = $r;
        }
        header('Content-Type: application/json');
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    if ($action == 'muatDataJSON') {
        // Temporarily enabled for debugging
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        // if (ob_get_level()) ob_end_clean(); // Commented for debug
        header('Content-Type: application/json');
        if (session_status() === PHP_SESSION_NONE)
            session_start();
        $lv = $_SESSION['level'] ?? '';
        $nik = $_SESSION['nik'] ?? '';

        $query = "SELECT * FROM pegawai";
        if ($lv == '4') {
            $query .= " WHERE id = '" . $conn->real_escape_string($_SESSION['id']) . "'";
        }
        $query .= " ORDER BY nm_pegawai ASC";

        $result = $conn->query($query);
        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        } else {
            die(json_encode(['status' => 'error', 'message' => 'SQL Error: ' . $conn->error]));
        }
        echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
        exit;
    }



    // --- FUNGSI HELPER UPLOAD ---
    function uploadFoto($file)
    {
        $target_dir = "../file/datakepegawaian/";
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

    function uploadSK($file)
    {
        $target_dir = "../file/datakepegawaian/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $new_filename = 'sk_' . time() . '_' . rand(100, 999) . '.' . $file_extension;
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


    // --- AMBIL SATU DATA ---
    if ($action == 'ambil') {
        header('Content-Type: application/json');
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $result = $stmt->get_result();
            $data = $result->fetch_assoc();
            ob_clean();
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengambil data.']);
        }
        exit;
    }

    // --- SIMPAN (INSERT / UPDATE) ---
    if ($action == 'simpan') {
        header('Content-Type: application/json');
        $lv = $_SESSION['level'] ?? '';
        $nik_session = $_SESSION['nik'] ?? '';

        $id = $_POST['id'] ?? '';
        $current_data = null;
        
        // Fetch current data if updating to prevent data loss on partial forms
        if (!empty($id)) {
            $stmt_check = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
            $stmt_check->bind_param("i", $id);
            $stmt_check->execute();
            $current_data = $stmt_check->get_result()->fetch_assoc();
        }

        // Security check for Guru
        if ($lv == '4') {
            if (!empty($id)) {
                if (!$current_data || ($current_data['nip'] != $nik_session && $current_data['nrk'] != $nik_session)) {
                    ob_clean();
                    echo json_encode(['status' => 'error', 'message' => 'Anda hanya dapat mengedit data Anda sendiri!']);
                    exit;
                }
                $nip = $current_data['nip'];
                $nrk_final = $current_data['nrk'];
            } else {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Akses ditolak untuk menambah pegawai baru.']);
                exit;
            }
        } else {
            $nip = $_POST['nip'] ?? ($current_data['nip'] ?? '');
            $nrk_final = $_POST['nrk'] ?? ($current_data['nrk'] ?? '');
        }

        // Use helper to preserve existing data if field is missing from POST (Partial Update)
        $nm_pegawai = $_POST['nm_pegawai'] ?? ($current_data['nm_pegawai'] ?? '');
        $tempat_lahir = $_POST['tempat_lahir'] ?? ($current_data['tempat_lahir'] ?? '');
        $tgl_lahir = !empty($_POST['tgl_lahir']) ? $_POST['tgl_lahir'] : ($current_data['tgl_lahir'] ?? null);
        $jenis_kelamin = $_POST['jenis_kelamin'] ?? ($current_data['jenis_kelamin'] ?? '');
        
        // Fields that might be missing from Teacher Profile form
        $jabatan = $_POST['jabatan'] ?? ($current_data['jabatan'] ?? '');
        $pangkat = $_POST['pangkat'] ?? ($current_data['pangkat'] ?? '');
        $golongan = $_POST['golongan'] ?? ($current_data['golongan'] ?? '');
        $unit_kerja = $_POST['unit_kerja'] ?? ($current_data['unit_kerja'] ?? '');
        $status_pegawai = $_POST['status_pegawai'] ?? ($current_data['status_pegawai'] ?? '');
        $pendidikan = $_POST['pendidikan'] ?? ($current_data['pendidikan'] ?? '');
        $tgl_lulus = !empty($_POST['tgl_lulus']) ? $_POST['tgl_lulus'] : ($current_data['tgl_lulus'] ?? null);
        $tmt_golongan = !empty($_POST['tmt_golongan']) ? $_POST['tmt_golongan'] : ($current_data['tmt_golongan'] ?? null);
        
        $no_hp = $_POST['no_hp'] ?? ($current_data['no_hp'] ?? '');
        $email = $_POST['email'] ?? ($current_data['email'] ?? '');
        $alamat = $_POST['alamat'] ?? ($current_data['alamat'] ?? '');
        $nrk = $nrk_final;
        $nuptk = $_POST['nuptk'] ?? ($current_data['nuptk'] ?? '');
        $agama = $_POST['agama'] ?? ($current_data['agama'] ?? '');
        $nama_ibu = $_POST['nama_ibu'] ?? ($current_data['nama_ibu'] ?? '');
        $nama_pasangan = $_POST['nama_pasangan'] ?? ($current_data['nama_pasangan'] ?? '');
        $npwp = $_POST['npwp'] ?? ($current_data['npwp'] ?? '');
        $nik_val = $_POST['nik'] ?? ($current_data['nik'] ?? '');
        $no_kk = $_POST['no_kk'] ?? ($current_data['no_kk'] ?? '');
        $no_karpeg = $_POST['no_karpeg'] ?? ($current_data['no_karpeg'] ?? '');
        $no_taspen = $_POST['no_taspen'] ?? ($current_data['no_taspen'] ?? '');
        $no_bpjs = $_POST['no_bpjs'] ?? ($current_data['no_bpjs'] ?? '');
        $no_karis_karsu = $_POST['no_karis_karsu'] ?? ($current_data['no_karis_karsu'] ?? '');
        $masa_kerja_thn = $_POST['masa_kerja_thn'] ?? ($current_data['masa_kerja_thn'] ?? 0);
        $masa_kerja_bln = $_POST['masa_kerja_bln'] ?? ($current_data['masa_kerja_bln'] ?? 0);
        $gaji_pokok = !empty($_POST['gaji_pokok']) ? floatval(str_replace(['.', ','], ['', '.'], $_POST['gaji_pokok'])) : ($current_data['gaji_pokok'] ?? 0);
        
        $rt = $_POST['rt'] ?? ($current_data['rt'] ?? '');
        $rw = $_POST['rw'] ?? ($current_data['rw'] ?? '');
        $kelurahan = $_POST['kelurahan'] ?? ($current_data['kelurahan'] ?? '');
        $kecamatan = $_POST['kecamatan'] ?? ($current_data['kecamatan'] ?? '');
        $hobby = $_POST['hobby'] ?? ($current_data['hobby'] ?? '');
        $pengalaman_kerja = $_POST['pengalaman_kerja'] ?? ($current_data['pengalaman_kerja'] ?? '');
        
        $foto_lama = $_POST['foto_lama'] ?? ($current_data['foto'] ?? '');
        $status = $_POST['status'] ?? ($current_data['status'] ?? '1');

        $foto = $foto_lama;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $upload = uploadFoto($_FILES['foto']);
            if ($upload) {
                $foto = $upload;
                if (!empty($foto_lama) && file_exists("../file/datakepegawaian/" . $foto_lama)) {
                    unlink("../file/datakepegawaian/" . $foto_lama);
                }
            }
        }

        if (empty($id)) {
            // Insert - Fixed placeholder mismatch (39 fields)
            $sql = "INSERT INTO pegawai (nip, nm_pegawai, tempat_lahir, tgl_lahir, jenis_kelamin, jabatan, pangkat, golongan, unit_kerja, status_pegawai, pendidikan, tgl_lulus, tmt_golongan, no_hp, email, alamat, foto, nrk, status, nuptk, agama, nama_ibu, nama_pasangan, npwp, nik, no_kk, no_karpeg, no_taspen, no_bpjs, no_karis_karsu, masa_kerja_thn, masa_kerja_bln, gaji_pokok, rt, rw, kelurahan, kecamatan, hobby, pengalaman_kerja) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssssssssssssssssssssssiidssssss", $nip, $nm_pegawai, $tempat_lahir, $tgl_lahir, $jenis_kelamin, $jabatan, $pangkat, $golongan, $unit_kerja, $status_pegawai, $pendidikan, $tgl_lulus, $tmt_golongan, $no_hp, $email, $alamat, $foto, $nrk, $status, $nuptk, $agama, $nama_ibu, $nama_pasangan, $npwp, $nik_val, $no_kk, $no_karpeg, $no_taspen, $no_bpjs, $no_karis_karsu, $masa_kerja_thn, $masa_kerja_bln, $gaji_pokok, $rt, $rw, $kelurahan, $kecamatan, $hobby, $pengalaman_kerja);
        } else {
            // Update - Added missing fields
            $sql = "UPDATE pegawai SET nip=?, nm_pegawai=?, tempat_lahir=?, tgl_lahir=?, jenis_kelamin=?, jabatan=?, pangkat=?, golongan=?, unit_kerja=?, status_pegawai=?, pendidikan=?, tgl_lulus=?, tmt_golongan=?, no_hp=?, email=?, alamat=?, foto=?, nrk=?, status=?, nuptk=?, agama=?, nama_ibu=?, nama_pasangan=?, npwp=?, nik=?, no_kk=?, no_karpeg=?, no_taspen=?, no_bpjs=?, no_karis_karsu=?, masa_kerja_thn=?, masa_kerja_bln=?, gaji_pokok=?, rt=?, rw=?, kelurahan=?, kecamatan=?, hobby=?, pengalaman_kerja=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssssssssssssssssssssssssssssiidssssssi", $nip, $nm_pegawai, $tempat_lahir, $tgl_lahir, $jenis_kelamin, $jabatan, $pangkat, $golongan, $unit_kerja, $status_pegawai, $pendidikan, $tgl_lulus, $tmt_golongan, $no_hp, $email, $alamat, $foto, $nrk, $status, $nuptk, $agama, $nama_ibu, $nama_pasangan, $npwp, $nik_val, $no_kk, $no_karpeg, $no_taspen, $no_bpjs, $no_karis_karsu, $masa_kerja_thn, $masa_kerja_bln, $gaji_pokok, $rt, $rw, $kelurahan, $kecamatan, $hobby, $pengalaman_kerja, $id);
        }

        if ($stmt->execute()) {
            ob_clean();
            echo json_encode(['status' => 'success', 'message' => 'Data pegawai berhasil disimpan!']);
        } else {
            ob_clean();
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
            ob_clean();
            echo json_encode(['status' => 'success', 'message' => 'Status berhasil diubah!']);
        } else {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'Gagal mengubah status.']);
        }
        exit;
    }

    // --- HAPUS ---
    if ($action == 'hapus') {
        header('Content-Type: application/json');
        if ($_SESSION['level'] == '4') {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'Akses ditolak untuk menghapus data.']);
            exit;
        }
        $id = $_POST['id'];
        // Get foto to delete file
        $stmt_cek = $conn->prepare("SELECT foto FROM pegawai WHERE id = ?");
        $stmt_cek->bind_param("i", $id);
        $stmt_cek->execute();
        $res = $stmt_cek->get_result()->fetch_assoc();
        if ($res && !empty($res['foto']) && file_exists("../file/datakepegawaian/" . $res['foto'])) {
            unlink("../file/datakepegawaian/" . $res['foto']);
        }

        $stmt = $conn->prepare("DELETE FROM pegawai WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            ob_clean();
            echo json_encode(['status' => 'success', 'message' => 'Data pegawai berhasil dihapus!']);
        } else {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
        }
        exit;
    }

    // --- RIWAYAT: MUAT DATA ---
    if ($action == 'muatRiwayat') {
        header('Content-Type: application/json');
        $pegawai_id = $_GET['pegawai_id'] ?? 0;
        $kategori_filter = $_GET['kategori'] ?? '';

        if ($_SESSION['level'] == '4') {
            if ($pegawai_id != $_SESSION['id']) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
                exit;
            }
        }
        $query = "SELECT * FROM riwayat_kepegawaian WHERE pegawai_id = ?";
        if (!empty($kategori_filter)) {
            $query .= " AND kategori = ?";
        }
        $query .= " ORDER BY tmt DESC, created_at DESC";

        $stmt = $conn->prepare($query);
        if (!empty($kategori_filter)) {
            $stmt->bind_param("is", $pegawai_id, $kategori_filter);
        } else {
            $stmt->bind_param("i", $pegawai_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // --- RIWAYAT: AMBIL SATU ---
    if ($action == 'ambilRiwayat') {
        header('Content-Type: application/json');
        $id = $_GET['id'] ?? 0;

        $stmt = $conn->prepare("SELECT * FROM riwayat_kepegawaian WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_assoc();

        if ($data && $_SESSION['level'] == '4') {
            if ($data['pegawai_id'] != $_SESSION['id']) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
                exit;
            }
        }

        echo json_encode(['status' => 'success', 'data' => $data]);
        exit;
    }

    // --- RIWAYAT: SIMPAN ---
    if ($action == 'simpanRiwayat') {
        header('Content-Type: application/json');
        $id = $_POST['id_riwayat'] ?? '';
        $pegawai_id = $_POST['pegawai_id_riwayat'] ?? '';
        $kategori = $_POST['kategori'] ?? 'Lainnya';

        // Security check
        if (($_SESSION['level'] ?? '') == '4') {
            // Teacher portal always uses their own ID
            $pegawai_id = $_SESSION['id'];
        }

        $deskripsi = $_POST['deskripsi'] ?? '';
        $tmt = !empty($_POST['tmt']) ? $_POST['tmt'] : null;
        $no_sk = $_POST['no_sk'] ?? '';
        $tgl_sk = !empty($_POST['tgl_sk']) ? $_POST['tgl_sk'] : null;
        $file_lama = $_POST['file_lama_riwayat'] ?? '';

        // New Fields
        $institusi = $_POST['institusi'] ?? '';
        $jurusan = $_POST['jurusan'] ?? '';
        $no_ijazah = $_POST['no_ijazah'] ?? '';
        $tgl_ijazah = !empty($_POST['tgl_ijazah']) ? $_POST['tgl_ijazah'] : null;
        $gelar_depan = $_POST['gelar_depan'] ?? '';
        $gelar_belakang = $_POST['gelar_belakang'] ?? '';
        $tempat = $_POST['tempat'] ?? '';
        $durasi = $_POST['durasi'] ?? '';
        $masa_kerja_thn = !empty($_POST['masa_kerja_thn']) ? intval($_POST['masa_kerja_thn']) : null;
        $masa_kerja_bln = !empty($_POST['masa_kerja_bln']) ? intval($_POST['masa_kerja_bln']) : null;
        $gaji_pokok = !empty($_POST['gaji_pokok']) ? floatval(str_replace(['.', ','], ['', '.'], $_POST['gaji_pokok'])) : null;

        $file_lampiran = $file_lama;
        if (isset($_FILES['file_lampiran']) && $_FILES['file_lampiran']['error'] == 0) {
            $upload = uploadSK($_FILES['file_lampiran']);
            if ($upload) {
                $file_lampiran = $upload;
                if (!empty($file_lama) && file_exists("../file/datakepegawaian/" . $file_lama)) {
                    unlink("../file/datakepegawaian/" . $file_lama);
                }
            }
        }

        if (empty($id)) {
            $sql = "INSERT INTO riwayat_kepegawaian (pegawai_id, kategori, deskripsi, tmt, no_sk, tgl_sk, file_lampiran, institusi, jurusan, no_ijazah, tgl_ijazah, gelar_depan, gelar_belakang, tempat, durasi, masa_kerja_thn, masa_kerja_bln, gaji_pokok) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Prepare Error: ' . $conn->error]);
                exit;
            }
            $pegawai_id_int = intval($pegawai_id);
            $stmt->bind_param("issssssssssssssiid", $pegawai_id_int, $kategori, $deskripsi, $tmt, $no_sk, $tgl_sk, $file_lampiran, $institusi, $jurusan, $no_ijazah, $tgl_ijazah, $gelar_depan, $gelar_belakang, $tempat, $durasi, $masa_kerja_thn, $masa_kerja_bln, $gaji_pokok);
        } else {
            $sql = "UPDATE riwayat_kepegawaian SET kategori=?, deskripsi=?, tmt=?, no_sk=?, tgl_sk=?, file_lampiran=?, institusi=?, jurusan=?, no_ijazah=?, tgl_ijazah=?, gelar_depan=?, gelar_belakang=?, tempat=?, durasi=?, masa_kerja_thn=?, masa_kerja_bln=?, gaji_pokok=? WHERE id=?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Prepare Error: ' . $conn->error]);
                exit;
            }
            $id_int = intval($id);
            $stmt->bind_param("ssssssssssssssiidi", $kategori, $deskripsi, $tmt, $no_sk, $tgl_sk, $file_lampiran, $institusi, $jurusan, $no_ijazah, $tgl_ijazah, $gelar_depan, $gelar_belakang, $tempat, $durasi, $masa_kerja_thn, $masa_kerja_bln, $gaji_pokok, $id_int);
        }

        if ($stmt->execute()) {
            ob_clean();
            echo json_encode(['status' => 'success', 'message' => 'Riwayat berhasil disimpan!']);
        } else {
            ob_clean();
            echo json_encode(['status' => 'error', 'message' => 'Gagal menyimpan riwayat: ' . $stmt->error]);
        }
        exit;
    }

    // --- RIWAYAT: HAPUS ---
    if ($action == 'hapusRiwayat') {
        header('Content-Type: application/json');
        $id = $_POST['id'] ?? '';

        if (($_SESSION['level'] ?? '') == '4') {
            $stmt_cek = $conn->prepare("SELECT pegawai_id FROM riwayat_kepegawaian WHERE id = ?");
            $stmt_cek->bind_param("i", $id);
            $stmt_cek->execute();
            $r = $stmt_cek->get_result()->fetch_assoc();

            if (!$r || $r['pegawai_id'] != $_SESSION['id']) {
                ob_clean();
                echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
                exit;
            }
        }
        $stmt_cek = $conn->prepare("SELECT file_lampiran FROM riwayat_kepegawaian WHERE id = ?");
        $stmt_cek->bind_param("i", $id);
        $stmt_cek->execute();
        $res = $stmt_cek->get_result()->fetch_assoc();
        if ($res && !empty($res['file_lampiran']) && file_exists("../file/datakepegawaian/" . $res['file_lampiran'])) {
            unlink("../file/datakepegawaian/" . $res['file_lampiran']);
        }

        $stmt = $conn->prepare("DELETE FROM riwayat_kepegawaian WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Riwayat berhasil dihapus!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus riwayat.']);
        }
        exit;
    }
} catch (Exception $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'System Error: ' . $e->getMessage()]);
    exit;
} catch (Error $e) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'System Fatal Error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()]);
    exit;
}
?>
