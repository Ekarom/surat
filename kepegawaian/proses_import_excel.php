<?php
ob_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    include "../dbconn.php";
    if (!$conn)
        throw new Exception("Koneksi database gagal.");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['status' => 'error', 'message' => 'Hanya menerima request POST']);
        exit;
    }

    // Helper: Clean numeric values (handle scientific notation)
    function clean_numeric($val)
    {
        if (empty($val))
            return '';
        $val = trim($val);
        if (strpos(strtoupper($val), 'E+') !== false) {
            $val = sprintf("%.0f", (float) $val);
        }
        return preg_replace('/[^0-9]/', '', $val);
    }

    // Helper: Format date to YYYY-MM-DD
    function format_date_to_db($dateStr)
    {
        if (empty($dateStr)) return NULL;
        $dateStr = trim($dateStr);
        if ($dateStr == '00-00-0000' || $dateStr == '-' || $dateStr == '0000-00-00' || $dateStr == '0')
            return NULL;

        // 1. YYYY-MM-DD
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateStr)) return $dateStr;

        // 2. DD/MM/YYYY or DD-MM-YYYY or DD.MM.YYYY (supports 2 or 4 digit years)
        if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})$/', $dateStr, $matches)) {
            $y = $matches[3];
            if (strlen($y) == 2) $y = ($y > 50 ? '19' : '20') . $y;
            return $y . '-' . sprintf('%02d', $matches[2]) . '-' . sprintf('%02d', $matches[1]);
        }

        // 3. Excel serial number
        if (is_numeric($dateStr) && $dateStr > 10000 && $dateStr < 100000) {
            $unix_date = ($dateStr - 25569) * 86400;
            return date("Y-m-d", $unix_date);
        }

        // 4. Try strtotime but ignore year-only or very short numeric strings
        if (strlen($dateStr) > 4) {
            $time = strtotime($dateStr);
            if ($time && $time > 0) {
                $formatted = date('Y-m-d', $time);
                if ($formatted != '1970-01-01') return $formatted;
            }
        }

        return NULL;
    }

    // Get and Clean Basic Data
    $nip = clean_numeric($_POST['nip'] ?? '');
    if (empty($nip) || ltrim($nip, '0') === '') {
        ob_clean();
        echo json_encode([
            'status' => 'skipped',
            'message' => 'NIP kosong atau hanya nol, dilewati.'
        ]);
        exit;
    }

    // Check if NIP exists
    $stmt_check = $conn->prepare("SELECT * FROM pegawai WHERE nip = ?");
    $stmt_check->bind_param("s", $nip);
    $stmt_check->execute();
    $check = $stmt_check->get_result();
    $existing_data = $check->fetch_assoc();
    $stmt_check->close();

    // Helper: Merge input with existing data if input is empty
    $merge = function($input, $existing) {
        return (!empty($input) || $input === '0') ? $input : ($existing ?? '');
    };

    $merge_date = function($input, $existing) {
        return ($input !== NULL) ? $input : ($existing ?? NULL);
    };

    // Assign and Merge Variables
    $nrk_input = clean_numeric($_POST['nrk'] ?? '');
    $nrk = $merge($nrk_input, $existing_data['nrk'] ?? '0');
    
    $nama_input = trim($_POST['nama'] ?? '');
    if (empty($nama_input) && empty($existing_data['nm_pegawai'])) {
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'Nama (Kolom C) wajib diisi.']);
        exit;
    }
    $nama = $merge($nama_input, $existing_data['nm_pegawai'] ?? '');

    $tempat_lahir = $merge($_POST['tempat_lahir'] ?? '', $existing_data['tempat_lahir'] ?? '');
    $tgl_lahir = $merge_date(format_date_to_db($_POST['tgl_lahir'] ?? ''), $existing_data['tgl_lahir'] ?? NULL);
    $tgl_lulus = $merge_date(format_date_to_db($_POST['tgl_lulus'] ?? ''), $existing_data['tgl_lulus'] ?? NULL);
    $tmt_golongan = $merge_date(format_date_to_db($_POST['tmt_golongan'] ?? ''), $existing_data['tmt_golongan'] ?? NULL);
    $tmt_pangkat = $merge_date(format_date_to_db($_POST['tmt_pangkat'] ?? ''), $existing_data['tmt_pangkat'] ?? NULL);

    $jenis_kelamin = $_POST['jenis_kelamin'] ?? '';
    if (empty($jenis_kelamin)) {
        $jk_val = $existing_data['jenis_kelamin'] ?? 'L';
    } else {
        $jk_upper = strtoupper(trim($jenis_kelamin));
        if (strpos($jk_upper, 'LAKI') !== false || $jk_upper == 'L') {
            $jk_val = 'L';
        } elseif (strpos($jk_upper, 'PEREMPUAN') !== false || $jk_upper == 'P') {
            $jk_val = 'P';
        } else {
            $jk_val = $existing_data['jenis_kelamin'] ?? 'L';
        }
    }

    $pendidikan = $merge($_POST['pendidikan'] ?? '', $existing_data['pendidikan'] ?? '');
    $jabatan = $merge($_POST['jabatan'] ?? '', $existing_data['jabatan'] ?? '');
    $pangkat = $merge($_POST['pangkat'] ?? '', $existing_data['pangkat'] ?? '');
    $golongan = $merge($_POST['golongan'] ?? '', $existing_data['golongan'] ?? '');
    $unit_kerja = $merge($_POST['unit_kerja'] ?? '', $existing_data['unit_kerja'] ?? '');
    $status_pegawai = $merge($_POST['status_pegawai'] ?? '', $existing_data['status_pegawai'] ?? '');
    $no_hp = $merge(clean_numeric($_POST['no_hp'] ?? ''), $existing_data['no_hp'] ?? '');
    $email = $merge($_POST['email'] ?? '', $existing_data['email'] ?? '');
    $rt = $merge(clean_numeric($_POST['rt'] ?? ''), $existing_data['rt'] ?? '');
    $rw = $merge(clean_numeric($_POST['rw'] ?? ''), $existing_data['rw'] ?? '');
    $kelurahan = $merge($_POST['kelurahan'] ?? '', $existing_data['kelurahan'] ?? '');
    $kecamatan = $merge($_POST['kecamatan'] ?? '', $existing_data['kecamatan'] ?? '');
    $nuptk = $merge(clean_numeric($_POST['nuptk'] ?? ''), $existing_data['nuptk'] ?? '');
    $agama = $merge($_POST['agama'] ?? '', $existing_data['agama'] ?? '');
    $alamat = $merge($_POST['alamat'] ?? '', $existing_data['alamat'] ?? '');
    $no_karis_karsu = $merge($_POST['no_karis_karsu'] ?? '', $existing_data['no_karis_karsu'] ?? '');
    $no_karpeg = $merge($_POST['no_karpeg'] ?? '', $existing_data['no_karpeg'] ?? '');
    $status_active = '1';

    $mode = '';
    $pegawai_id = 0;

    if ($check->num_rows > 0) {
        $mode = 'update';
        $pegawai_id = $existing_data['id'];

        // UPDATE existing record
        $stmt = $conn->prepare("UPDATE pegawai SET 
                                    nrk = ?, 
                                    nm_pegawai = ?, 
                                    tempat_lahir = ?, 
                                    tgl_lahir = ?, 
                                    jenis_kelamin = ?, 
                                    pendidikan = ?, 
                                    tgl_lulus = ?, 
                                    jabatan = ?, 
                                    pangkat = ?, 
                                    golongan = ?, 
                                    tmt_golongan = ?,
                                    tmt_pangkat = ?,
                                    unit_kerja = ?, 
                                    status_pegawai = ?, 
                                    no_hp = ?, 
                                    email = ?, 
                                    rt = ?, 
                                    rw = ?, 
                                    kelurahan = ?, 
                                    kecamatan = ?,
                                    status = ?,
                                    nuptk = ?,
                                    agama = ?,
                                    alamat = ?,
                                    no_karis_karsu = ?,
                                    no_karpeg = ?
                                WHERE nip = ?");

        $stmt->bind_param(
            "sssssssssssssssssssssssssss",
            $nrk,
            $nama,
            $tempat_lahir,
            $tgl_lahir,
            $jk_val,
            $pendidikan,
            $tgl_lulus,
            $jabatan,
            $pangkat,
            $golongan,
            $tmt_golongan,
            $tmt_pangkat,
            $unit_kerja,
            $status_pegawai,
            $no_hp,
            $email,
            $rt,
            $rw,
            $kelurahan,
            $kecamatan,
            $status_active,
            $nuptk,
            $agama,
            $alamat,
            $no_karis_karsu,
            $no_karpeg,
            $nip
        );
        $stmt->execute();
        $stmt->close();
    } else {
        $mode = 'insert';
        // INSERT new record
        $stmt = $conn->prepare("INSERT INTO pegawai (
                                    nip, nrk, nm_pegawai, tempat_lahir, tgl_lahir, jenis_kelamin, 
                                    pendidikan, tgl_lulus, jabatan, pangkat, golongan, tmt_golongan, tmt_pangkat,
                                    unit_kerja, status_pegawai, no_hp, email, rt, rw, kelurahan, kecamatan, status, nuptk, agama, alamat, no_karis_karsu, no_karpeg
                                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param(
            "sssssssssssssssssssssssssss",
            $nip,
            $nrk,
            $nama,
            $tempat_lahir,
            $tgl_lahir,
            $jk_val,
            $pendidikan,
            $tgl_lulus,
            $jabatan,
            $pangkat,
            $golongan,
            $tmt_golongan,
            $tmt_pangkat,
            $unit_kerja,
            $status_pegawai,
            $no_hp,
            $email,
            $rt,
            $rw,
            $kelurahan,
            $kecamatan,
            $status_active,
            $nuptk,
            $agama,
            $alamat,
            $no_karis_karsu,
            $no_karpeg
        );
        $stmt->execute();
        $pegawai_id = $conn->insert_id;
        $stmt->close();
    }

    // --- SYNC TO RIWAYAT_KEPEGAWAIAN ---
    if ($pegawai_id > 0) {
        // Sync: Pangkat (tmt_golongan)
        if (!empty($tmt_golongan) && $tmt_golongan != '0000-00-00') {
            $stmt_h = $conn->prepare("SELECT id FROM riwayat_kepegawaian WHERE pegawai_id = ? AND kategori = 'Pangkat' AND tmt = ?");
            $stmt_h->bind_param("is", $pegawai_id, $tmt_golongan);
            $stmt_h->execute();
            if ($stmt_h->get_result()->num_rows == 0) {
                $stmt_h->close();
                $stmt_i = $conn->prepare("INSERT INTO riwayat_kepegawaian (pegawai_id, kategori, deskripsi, tmt) VALUES (?, 'Pangkat', ?, ?)");
                $stmt_i->bind_param("iss", $pegawai_id, $golongan, $tmt_golongan);
                $stmt_i->execute();
                $stmt_i->close();
            } else {
                $stmt_h->close();
            }
        }

        // Sync: Kepangkatan (tmt_pangkat)
        if (!empty($tmt_pangkat) && $tmt_pangkat != '0000-00-00') {
            $stmt_h = $conn->prepare("SELECT id FROM riwayat_kepegawaian WHERE pegawai_id = ? AND kategori = 'Kepangkatan' AND tmt = ?");
            $stmt_h->bind_param("is", $pegawai_id, $tmt_pangkat);
            $stmt_h->execute();
            if ($stmt_h->get_result()->num_rows == 0) {
                $stmt_h->close();
                $stmt_i = $conn->prepare("INSERT INTO riwayat_kepegawaian (pegawai_id, kategori, deskripsi, tmt) VALUES (?, 'Kepangkatan', ?, ?)");
                $stmt_i->bind_param("iss", $pegawai_id, $pangkat, $tmt_pangkat);
                $stmt_i->execute();
                $stmt_i->close();
            } else {
                $stmt_h->close();
            }
        }
    }

    ob_clean();
    echo json_encode(['status' => 'success', 'mode' => $mode]);
    exit;
} catch (mysqli_sql_exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Database Error: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Server Error: ' . $e->getMessage()]);
    exit;
}
?>