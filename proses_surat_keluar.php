<?php
/**
 * proses_surat_keluar.php
 * * Update: Menyamakan struktur dengan proses_surat_masuk.php
 * * Fitur: Leveling User & Fungsi Helper di atas.
 * * Perbaikan: Logika hapus file lama saat update (mengambil data eksisting dari DB).
 */

session_start(); 
ob_start(); // Buffer output untuk mencegah error JSON
include "dbconn.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep 0 for JSON responses, rely on try-catch

// --- KONFIGURASI ---
define('UPLOAD_DIR', 'file/berkas-keluar/'); 
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2 MB (Synchronized with UI)
define('ALLOWED_EXTENSIONS', ['pdf']);

if (!is_dir(UPLOAD_DIR)) {
    if (!mkdir(UPLOAD_DIR, 0755, true)) {
        die(json_encode(['status'=>'error', 'message'=>'Gagal membuat direktori upload.']));
    }
}

// ==================================================================
// BAGIAN 1: FUNGSI HELPER
// ==================================================================


function kirimResponsSukses($data = null, $message = '') {
    // Bersihkan buffer output agar tidak ada sampah/whitespace sebelum JSON
    ob_clean();
    header('Content-Type: application/json');
    $response = ['status' => 'success'];
    if ($message) $response['message'] = $message;
    if ($data !== null) $response['data'] = $data;
    echo json_encode($response);
    exit;
}

function kirimResponsError($message, $httpStatusCode = 400) {
    ob_clean();
    header('Content-Type: application/json');
    http_response_code($httpStatusCode);
    echo json_encode(['status' => 'error', 'message' => $message]);
    exit;
}

function hapusFileLama($filename) {
    if (empty($filename)) return;
    
    $files = explode('|', $filename);
    
    foreach ($files as $f) {
        $f = trim($f);
        if (empty($f)) continue;
        if (strpos($f, '..') !== false) continue;
        
        $directPath = UPLOAD_DIR . $f;
        if (file_exists($directPath) && is_file($directPath)) {
            unlink($directPath);
            continue;
        }

        // Fallback logic
        $sysTahun = $_SESSION['tahundb'] ?? date('Y');
        $sysSmt = $_SESSION['semester'] ?? '1';
        $possibleFolders = [
            $sysTahun . '-' . $sysSmt . '/',
            (isset($_SESSION['tapel']) ? str_replace(['/', '\\'], '-', $_SESSION['tapel']) : date('Y')) . '-' . $sysSmt . '/'
        ];

        foreach ($possibleFolders as $folder) {
            $filePath = UPLOAD_DIR . $folder . $f;
            if (file_exists($filePath) && is_file($filePath)) {
                unlink($filePath);
                break;
            }
        }
    }
}

function resolvePdfPath($filename, $baseDir) {
    if (empty($filename)) return '';
    
    $files = explode('|', $filename);
    $resolvedFiles = [];

    foreach ($files as $f) {
        $f = trim($f);
        if (empty($f)) continue;

        if (strpos($f, '/') !== false) {
            $resolvedFiles[] = $f;
            continue;
        }

        $pattern = $baseDir . '*/' . $f;
        $matches = glob($pattern);
        
        if (!empty($matches)) {
            $fullPath = str_replace('\\', '/', $matches[0]);
            $baseDirNorm = str_replace('\\', '/', $baseDir);
            $resolvedFiles[] = str_replace($baseDirNorm, '', $fullPath);
        } else {
            $resolvedFiles[] = $f;
        }
    }
    
    return implode('|', $resolvedFiles);
}

// muatData dihapus karena beralih ke static loading di suratkeluar.php

function simpanData($conn, $action) {
    // Ambil data dari POST
    $id = (int)($_POST['id'] ?? 0);
    $no_dokumen = $_POST['no_dokumen'] ?? '';
    $jns_dokumen = $_POST['jns_dokumen'] ?? '';
    $dari = $_POST['dari'] ?? ''; 
    $unit_tujuan = $_POST['unit_tujuan'] ?? ''; 
    $perihal = $_POST['perihal'] ?? '';
    $pembuat = $_POST['pembuat'] ?? '';
    $tgl_dokumen = !empty($_POST['tgl_dokumen']) ? $_POST['tgl_dokumen'] : null;
    $kategori = $_POST['kategori'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    $lampiran = $_POST['lampiran'] ?? ''; 

    // Variabel untuk handling file
    $fileDiUpload = false;
    $uploadedFiles = [];
    $pdf_string = '';

    // 1. LOGIKA UPLOAD FILE (MULTIPLE)
    if (isset($_FILES['pdf'])) {
        $files = $_FILES['pdf'];
        $count = is_array($files['name']) ? count($files['name']) : 1;
        
        // Normalize
        if (!is_array($files['name'])) {
            $files = [
                'name' => [$files['name']],
                'type' => [$files['type']],
                'tmp_name' => [$files['tmp_name']],
                'error' => [$files['error']],
                'size' => [$files['size']],
            ];
            $count = 1;
        }

        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                 if ($files['size'][$i] > MAX_FILE_SIZE) throw new Exception('File melebihi batas 2MB.');
                 
                 $fileExt = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                 if (!in_array($fileExt, ALLOWED_EXTENSIONS)) throw new Exception('Ekstensi file tidak diizinkan (Hanya PDF).');
                 
                 // Unique Name
                 $cleanNoDokumen = preg_replace("/[^a-zA-Z0-9_-]/", "_", $no_dokumen);
                 if(empty($cleanNoDokumen)) $cleanNoDokumen = 'file';
                 
                 // USE DATE
                 $docDate = !empty($tgl_dokumen) ? strtotime($tgl_dokumen) : time();
                 $sysTahun = date('Y', $docDate);
                 $month = date('n', $docDate);
                 $sysSmt = ($month >= 7) ? '1' : '2';
                 
                 $subFolder = $sysTahun . '-' . $sysSmt . '/';
                 $fullUploadDir = UPLOAD_DIR . $subFolder;
                 
                 if (!is_dir($fullUploadDir)) {
                      if (!mkdir($fullUploadDir, 0755, true)) throw new Exception('Gagal membuat folder upload.');
                 }
                 
                 // Name: timestamp_seq_name.pdf
                 $newFileName = time() . '_' . $i . '_' . $cleanNoDokumen . '.' . $fileExt;
                 $targetPath = $fullUploadDir . $newFileName;
                 
                 if (!move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                     throw new Exception('Gagal menyimpan file ke server.');
                 }
                 
                 $uploadedFiles[] = $subFolder . $newFileName;
                 $fileDiUpload = true;
            }
        }
    }

    $pdf_string = implode('|', $uploadedFiles);

    /**
     * DATABASE SWITCHING CONTEXT
     */
    $dbAsal = $_POST['tahun'] ?? '';
    if ($action === 'edit' && !empty($dbAsal)) {
        if (!$conn->select_db("sas_" . $dbAsal)) {
            throw new Exception("Database sas_$dbAsal tidak ditemukan.");
        }
    } else {
        $tahunInput = !empty($tgl_dokumen) ? date('Y', strtotime($tgl_dokumen)) : null;
        if ($tahunInput) {
             $dbTarget = "sas_" . $tahunInput;
             if (!$conn->select_db($dbTarget)) {
                 throw new Exception("Database $dbTarget tidak ditemukan.");
             }
        }
    }

    // Mulai Transaksi
    $conn->begin_transaction();

    try {
        if ($action === 'edit') {
            if ($id === 0) throw new Exception('ID tidak valid.');

            // Ambil info file lama
            $stmtGet = $conn->prepare("SELECT pdf FROM dokumenkeluar WHERE id = ?");
            $stmtGet->bind_param('i', $id);
            $stmtGet->execute();
            $resGet = $stmtGet->get_result();
            $rowGet = $resGet->fetch_assoc();
            $stmtGet->close();

            if (!$rowGet) throw new Exception('Data lama tidak ditemukan di database target.');
            $file_lama_db = $rowGet['pdf'] ?? '';

            if ($fileDiUpload) {
                $final_pdf = $pdf_string;
                // Jika upload baru, hapus semua file lama yang digantikan
                if (!empty($file_lama_db)) {
                    hapusFileLama($file_lama_db);
                }
            } else {
                // List file yang tetap dipertahankan (dikirim dari UI)
                $posted_files_str = $_POST['file_lama'] ?? '';
                $db_files_arr = !empty($file_lama_db) ? explode('|', $file_lama_db) : [];
                $posted_files_arr = !empty($posted_files_str) ? explode('|', $posted_files_str) : [];
                
                $valid_files_arr = array_intersect($posted_files_arr, $db_files_arr);
                $final_pdf = implode('|', $valid_files_arr);
                
                // Hapus fisik file yang dihapus dari list
                $deleted_files_arr = array_diff($db_files_arr, $valid_files_arr);
                if (!empty($deleted_files_arr)) {
                     hapusFileLama(implode('|', $deleted_files_arr));
                }
            }

            // 2. QUERY UPDATE
            $sql = "UPDATE dokumenkeluar SET 
                        no_dokumen=?, jns_dokumen=?, dari=?, unit_tujuan=?,        
                        perihal=?, pembuat=?, tgl_dokumen=?, kategori=?, catatan=?, pdf=?, lampiran=? 
                    WHERE id=?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            $stmt->bind_param('sssssssssssi', 
                $no_dokumen, $jns_dokumen, $dari, $unit_tujuan, $perihal, $pembuat,
                $tgl_dokumen, $kategori, $catatan, $final_pdf, $lampiran, $id
            );

            if (!$stmt->execute()) throw new Exception("Gagal update database: " . $stmt->error);
            $stmt->close();

            add_activity_log($conn, 'Surat Keluar', 'Edit', "No Dokumen: $no_dokumen");
            $message = '<i>~ Data berhasil diperbarui.</i>';
        
        } else {
            // Action: Simpan Baru
            $sql = "INSERT INTO dokumenkeluar (no_dokumen, jns_dokumen, dari, unit_tujuan, perihal, lampiran, pembuat, tgl_dokumen, kategori, catatan, pdf) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);
            $stmt->bind_param('sssssssssss', $no_dokumen, $jns_dokumen, $dari, $unit_tujuan, $perihal, $lampiran, $pembuat, $tgl_dokumen, $kategori, $catatan, $pdf_string);
            if (!$stmt->execute()) throw new Exception("Gagal simpan database: " . $stmt->error);
            $stmt->close();
            
            add_activity_log($conn, 'Surat Keluar', 'Tambah', "No Dokumen: $no_dokumen");
            $message = '<i>~ Data berhasil disimpan.</i>';
        }
        $conn->commit();
        kirimResponsSukses(null, $message);
    } catch (Throwable $e) {
        $conn->rollback();
        // Jika gagal simpan DB, hapus file baru
        if ($fileDiUpload) hapusFileLama($pdf_string);
        throw new Exception("Error DB: " . $e->getMessage());
    }

}

function ambilData($conn) {
    $id = (int)($_GET['id'] ?? 0);
    $tahun = preg_replace('/[^0-9]/', '', $_GET['tahun'] ?? '');
    
    if ($tahun) {
        $dbTarget = "sas_" . $tahun;
        if (!$conn->select_db($dbTarget)) {
            // Silently fail if DB doesn't exist to allow standard fallback if needed
        }
    }

    if ($id === 0) throw new Exception('ID tidak valid.');
    
    $stmt = $conn->prepare("SELECT *, DATE(tgl_dokumen) as tgl_dokumen_raw FROM dokumenkeluar WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    $stmt->close();

    if ($data) {
        header('Content-Type: application/json');
        $data['pdf'] = resolvePdfPath($data['pdf'], UPLOAD_DIR);

        // Map ukuran file untuk sinkronisasi di UI
        $file_sizes = [];
        if (!empty($data['pdf'])) {
            $files = explode('|', $data['pdf']);
            foreach ($files as $f) {
                $f = trim($f);
                if (empty($f)) continue;
                $path = UPLOAD_DIR . $f;
                $file_sizes[$f] = file_exists($path) ? filesize($path) : 0;
            }
        }
        $data['file_sizes_map'] = $file_sizes;

        echo json_encode($data);
        exit;
    } else {
        throw new Exception('Data tidak ditemukan.');
    }
}

function hapusData($conn) {
    $id = (int)($_POST['id'] ?? 0);
    $tahun = preg_replace('/[^0-9]/', '', $_POST['tahun'] ?? '');
    
    if ($tahun) {
        $dbTarget = "sas_" . $tahun;
        if (!$conn->select_db($dbTarget)) {
            // Ignore error
        }
    }

    if ($id === 0) throw new Exception('ID tidak valid.');
    
    $conn->begin_transaction();
    try {
        $stmtCek = $conn->prepare("SELECT no_dokumen, pdf FROM dokumenkeluar WHERE id = ?");
        $stmtCek->bind_param('i', $id);
        $stmtCek->execute();
        $resCek = $stmtCek->get_result();
        $rowCek = $resCek->fetch_assoc();
        $stmtCek->close();

        if (!$rowCek) throw new Exception('Data tidak ditemukan.');
        $no_dokumen = $rowCek['no_dokumen'];
        $pdf = $rowCek['pdf'];

        $stmtHapus = $conn->prepare("DELETE FROM dokumenkeluar WHERE id = ?");
        $stmtHapus->bind_param('i', $id);
        $stmtHapus->execute();
        if ($stmtHapus->affected_rows === 0) throw new Exception('Gagal hapus data.');
        $stmtHapus->close();
        
        if (!empty($pdf)) hapusFileLama($pdf);
        
        $conn->commit();
        add_activity_log($conn, 'Surat Keluar', 'Hapus', "No Dokumen: $no_dokumen");
        kirimResponsSukses(null, 'Data berhasil dihapus.');
    } catch (Throwable $e) {
        $conn->rollback();
        throw new Exception('Error: ' . $e->getMessage());
    }
}

// ==================================================================
// BAGIAN 3: ROUTER
// ==================================================================

try {
    if (!isset($conn) || !$conn || $conn->connect_error) throw new Exception("Koneksi DB gagal.");

    $id_user = $_SESSION['id'] ?? $_SESSION['user_id'] ?? 0;
    $level = ''; 

    if (!empty($id_user)) {
        $stmt = $conn->prepare("SELECT level FROM tb_user WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_user);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $level = trim($row['level']); 
            }
            $stmt->close();
        }
    }

    $action = $_REQUEST['action'] ?? ''; 
    switch ($action) {
        case 'simpan': 
        case 'edit': 
            if ($level != '1' && $level != '2') throw new Exception('Akses ditolak (Hanya Admin/Staff).');
            simpanData($conn, $action); 
            break;
        case 'ambil': 
            ambilData($conn); 
            break;
        case 'hapus': 
            if ($level != '1' && $level != '2') throw new Exception('Akses ditolak (Hanya Admin/Staff).');
            hapusData($conn); 
            break;
        default: throw new Exception('Aksi tidak valid.');
    }
} catch (Throwable $e) {
    kirimResponsError('Error: ' . $e->getMessage(), 500);
} finally {
    if (isset($conn) && $conn instanceof mysqli) $conn->close();
}
?>
