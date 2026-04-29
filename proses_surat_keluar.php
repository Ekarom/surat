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
    
    // Support Multiple Files (Split by |)
    $files = explode('|', $filename);
    
    foreach ($files as $f) {
        $f = trim($f);
        if (empty($f)) continue;
        if (strpos($f, '..') !== false) continue;
        
        $filePath = UPLOAD_DIR . $f;
        if (file_exists($filePath) && is_file($filePath)) {
            unlink($filePath);
        }
    }
}

function resolvePdfPath($filename, $baseDir) {
    if (empty($filename)) return '';
    // Jika sudah ada slash (path lengkap), kembalikan as is
    if (strpos($filename, '/') !== false) return $filename;

    // Cari file di semua subfolder menggunakan glob
    // Pattern: UPLOAD_DIR/*/filename.pdf
    $pattern = $baseDir . '*/' . $filename;
    $matches = glob($pattern);
    
    if (!empty($matches)) {
        // Ambil match pertama
        $fullPath = $matches[0];
        // Normalisasi separator ke forward slash
        $fullPath = str_replace('\\', '/', $fullPath);
        // Hapus baseDir dari path untuk mendapatkan relative path
        $baseDirNorm = str_replace('\\', '/', $baseDir);
        return str_replace($baseDirNorm, '', $fullPath);
    }
    
    // Jika tidak ketemu, kembalikan filename as is
    return $filename;
}

function buatTabelHtml($dataRows, $offset, $level) {
    if (empty($dataRows)) {
        return '<div class="alert alert-warning text-center">Tidak ada data ditemukan.</div>';
    }

    $html = '<table class="table table-striped table-hover align-middle">';
    $html .= '<thead class="bg-menu-gradient text-center"><tr>
                <th>No</th>
                <th>No Surat</th>
                <th>Jenis Dokumen</th>
                <th>Dari</th>
                <th>Unit</th>
                <th>Perihal</th>
                <th>Pembuat</th>
                <th>Tgl Dokumen</th>
                <th>Kategori</th>
                <th>Catatan</th>';

    // Header View (Semua Level)
    if ($level == '1'||$level == '2' || $level == '3') { 
    $html .= '<th>View</th>';
    }

    // Header Edit & Hapus (Level 1 Only)
    if ($level == '1'||$level == '2') { 
        $html .= '<th>Edit</th>';
        $html .= '<th>Del</th>';
    }

    $html .= '</tr></thead><tbody>';
    
    $no = $offset + 1;
    foreach ($dataRows as $row) {
        $id = (int)$row['id'];
        $no_dok = htmlspecialchars($row['no_dokumen'] ?? '', ENT_QUOTES, 'UTF-8');
        $jns_dok = htmlspecialchars($row['jns_dokumen'] ?? '', ENT_QUOTES, 'UTF-8');
        $dari = htmlspecialchars($row['dari'] ?? '', ENT_QUOTES, 'UTF-8'); 
        $unit_tujuan = htmlspecialchars($row['unit_tujuan'] ?? '', ENT_QUOTES, 'UTF-8'); 
        $perihal = htmlspecialchars($row['perihal'] ?? '', ENT_QUOTES, 'UTF-8');
        $pembuat = htmlspecialchars($row['pembuat'] ?? '', ENT_QUOTES, 'UTF-8');
        $kat = htmlspecialchars($row['kategori'] ?? '', ENT_QUOTES, 'UTF-8');
        $cat = htmlspecialchars($row['catatan'] ?? '', ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($row['pdf'] ?? '', ENT_QUOTES, 'UTF-8');

        $tgl_formatted = !empty($row['tgl_dokumen']) ? (new DateTime($row['tgl_dokumen']))->format('d M Y') : '-';
        $viewDisabled = empty($file) ? 'disabled' : '';
        
        $html .= "<tr>
                <td class=\"text-center\">{$no}</td>
                <td>{$no_dok}</td>
                <td>{$jns_dok}</td>
                <td>{$dari}</td>
                <td>{$unit_tujuan}</td>
                <td>{$perihal}</td>
                <td>{$pembuat}</td>
                <td>{$tgl_formatted}</td>
                <td>{$kat}</td>
                <td>{$cat}</td>";

        // Tombol View
        $html .= "<td class=\"text-center\">";
        if ($level == '1' || $level == '2' || $level == '3') {
            $html .= "<button class=\"btn btn-info btn-sm tombol-view\" 
                            data-id=\"{$id}\" data-file=\"{$file}\" 
                            title=\"Lihat Detail\" {$viewDisabled}>
                        <i class=\"fas fa-eye\"></i>
                      </button>";
        }
        $html .= "</td>";

        // Tombol Edit
        if ($level == '1' || $level == '2') {
            $html .= "<td class=\"text-center\">
                        <button class=\"btn btn-warning btn-sm tombol-edit\" 
                                data-id=\"{$id}\" title=\"Edit\">
                            <i class=\"fas fa-pencil-alt\"></i>
                        </button>
                      </td>";
        }

        // Tombol Hapus
        if ($level == '1' || $level == '2') {
            $html .= "<td class=\"text-center\">
                        <button class=\"btn btn-danger btn-sm tombol-hapus\" 
                                data-id=\"{$id}\" data-nama=\"{$no_dok}\" 
                                title=\"Hapus\">
                            <i class=\"fas fa-trash-alt\"></i>
                        </button>
                      </td>";
        }

        $html .= "</tr>";
        $no++;
    }
    
    $html .= '</tbody></table>';
    return $html;
}

function buatPaginasi($currentPage, $totalPages) {
    if ($totalPages <= 1) return '';
    $html = '<nav aria-label="Navigasi Halaman"><ul class="pagination justify-content-center justify-content-md-end mb-0">';
    $prevPage = $currentPage - 1;
    $prevDisabled = ($currentPage <= 1) ? 'disabled' : '';
    $html .= '<li class="page-item ' . $prevDisabled . '"><a class="page-link" href="#" data-page="' . $prevPage . '">Sebelumnya</a></li>';
    
    $window = 2; $showEllipsis = false;
    for ($i = 1; $i <= $totalPages; $i++) {
        if ($i == 1 || $i == $totalPages || ($i >= $currentPage - $window && $i <= $currentPage + $window)) {
            $active = ($i == $currentPage) ? 'active' : '';
            $html .= '<li class="page-item ' . $active . '"><a class="page-link" href="#" data-page="' . $i . '">' . $i . '</a></li>';
            $showEllipsis = true;
        } elseif ($showEllipsis) {
            $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            $showEllipsis = false;
        }
    }
    $nextPage = $currentPage + 1;
    $nextDisabled = ($currentPage >= $totalPages) ? 'disabled' : '';
    $html .= '<li class="page-item ' . $nextDisabled . '"><a class="page-link" href="#" data-page="' . $nextPage . '">Berikutnya</a></li>';
    $html .= '</ul></nav>';
    return $html;
}

function buatInfoData($page, $limit, $totalRecords) {
    if ($totalRecords == 0) return 'Tidak ada data';
    $start = ($page - 1) * $limit + 1;
    $end = $start + $limit - 1;
    if ($end > $totalRecords) $end = $totalRecords;
    return "Menampilkan {$start} - {$end} dari {$totalRecords} data";
}

// ==================================================================
// BAGIAN 2: FUNGSI UTAMA (DB Logic)
// ==================================================================

function muatData($conn, $level) {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $search = (string)($_GET['search'] ?? '');
    
    $tahun = (int)($_GET['tahun'] ?? 0);

    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    
    $offset = ($page - 1) * $limit;
    $searchParam = "%" . $search . "%";
    
    $searchColumns = ['no_dokumen', 'perihal']; // Sesuaikan kolom tabel dokumenkeputusan
    $whereConditions = [];
    $params = [];
    $types = '';

    // 1. Filter Search (OR condition grouped)
    if (!empty($search)) {
        $searchParts = [];
        foreach ($searchColumns as $col) {
            $searchParts[] = "$col LIKE ?";
            $params[] = $searchParam;
            $types .= 's';
        }
        $whereConditions[] = "(" . implode(" OR ", $searchParts) . ")";
    }

    // 2. Filter Tahun (DATABASE SWITCHING)
    if ($tahun > 0) {
        $dbNameValues = "sas_" . $tahun;
    } else {
        // Jika 'Semua', gunakan database utama (sas)
        $dbNameValues = "sas";
    }

    try {
        $conn->select_db($dbNameValues);
    } catch (Exception $e) {
        // Fallback: tetap di DB eksisting jika sas tidak ditemukan
    }

    $whereClause = "";
    if (!empty($whereConditions)) {
        $whereClause = " WHERE " . implode(" AND ", $whereConditions);
    }

    $totalSql = "SELECT COUNT(*) FROM dokumenkeluar" . $whereClause;
    $stmtTotal = $conn->prepare($totalSql);
    if (!empty($search)) {
        $stmtTotal->bind_param($types, ...$params);
    }
    $stmtTotal->execute();
    $totalRecords = 0;
    $stmtTotal->bind_result($totalRecords);
    $stmtTotal->fetch();
    $stmtTotal->close();

    $totalPages = ceil($totalRecords / $limit);

    $dataSql = "SELECT * FROM dokumenkeluar" . $whereClause . " ORDER BY id ASC LIMIT ? OFFSET ?";
    $dataTypes = $types . 'ii';
    $dataParams = [...$params, $limit, $offset];

    $stmtData = $conn->prepare($dataSql);
    $stmtData->bind_param($dataTypes, ...$dataParams);
    $stmtData->execute();
    $result = $stmtData->get_result();
    
    $dataRows = [];
    while ($row = $result->fetch_assoc()) {
        $dataRows[] = $row;
    }
    $stmtData->close();

    $tableHtml = buatTabelHtml($dataRows, $offset, $level);
    $paginationHtml = buatPaginasi($page, $totalPages);
    
    // Level Detection Feedback
    $levelBadge = '<span class="badge bg-secondary">Guest</span>';
    if ($level == "1") $levelBadge = '<span class="badge bg-danger">Admin</span>';
    elseif ($level == "2") $levelBadge = '<span class="badge bg-primary">Staff</span>';
    elseif ($level == "3") $levelBadge = '<span class="badge bg-info text-dark">User</span>';

    $recordsInfo = buatInfoData($page, $limit, $totalRecords) . " <span class='ms-2'>" . $levelBadge . "</span>";

    kirimResponsSukses([
        'table' => $tableHtml,
        'pagination' => $paginationHtml,
        'recordsInfo' => $recordsInfo
    ]);
}

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
    $lampiran = ''; // Variabel lampiran belum ada di input form, diset kosong default.


    // Variabel untuk handling file
    $fileDiUpload = false;
    $uploadedFiles = [];

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
                 if ($files['size'][$i] > MAX_FILE_SIZE) throw new Exception('File melebihi batas 1MB.');
                 
                 $fileExt = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                 if (!in_array($fileExt, ALLOWED_EXTENSIONS)) throw new Exception('Ekstensi file tidak diizinkan. Hanya PDF.');
                 
                 // Unique Name
                 $cleanNoDokumen = preg_replace("/[^a-zA-Z0-9_-]/", "_", $no_dokumen . '_' . $perihal);
                 if(empty($cleanNoDokumen)) $cleanNoDokumen = 'file';
                 
                 // USE DATE (Same logic)
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

    if ($fileDiUpload) {
        $pdf_string = implode('|', $uploadedFiles);
    } else {
        $pdf_string = '';
    }

    /**
     * DATABASE SWITCHING CONTEXT
     * Penting: Pastikan kita beralih ke database yang sesuai dengan tgl_dokumen
     * sebelum melakukan query apa pun (SELECT target record atau UPDATE).
     */
    if (!empty($tgl_dokumen)) {
        $tahunInput = date('Y', strtotime($tgl_dokumen));
        if ($tahunInput) {
             $dbTarget = "sas_" . $tahunInput;
             try { 
                 $dbExists = $conn->select_db($dbTarget); 
                 if (!$dbExists) throw new Exception("Database $dbTarget tidak ditemukan.");
             } catch (Exception $e) {
                 throw new Exception("Gagal beralih ke database $dbTarget: " . $e->getMessage());
             }
        }
    }

    // Mulai Transaksi
    $conn->begin_transaction();

    try {
        if ($action === 'edit') {
            if ($id === 0) throw new Exception('ID tidak valid.');

            $sqlGetOld = "SELECT pdf FROM dokumenkeluar WHERE id = ?";
            $stmtGet = $conn->prepare($sqlGetOld);
            $stmtGet->bind_param('i', $id);
            $stmtGet->execute();
            $stmtGet->bind_result($file_lama_db);
            $stmtGet->fetch();
            $stmtGet->close();

            $file_lama = $file_lama_db; 

            if ($fileDiUpload) {
                $final_pdf = $pdf_string;
            } else {
                $posted_files_str = isset($_POST['file_lama']) ? $_POST['file_lama'] : '';
                
                $db_files_arr = explode('|', $file_lama_db);
                $posted_files_arr = explode('|', $posted_files_str);
                
                $valid_files_arr = array_intersect($posted_files_arr, $db_files_arr);
                $final_pdf = implode('|', $valid_files_arr);
                
                $deleted_files_arr = array_diff($db_files_arr, $valid_files_arr);
                if (!empty($deleted_files_arr)) {
                     hapusFileLama(implode('|', $deleted_files_arr));
                }
            }

            // 2. QUERY UPDATE
            $sql = "UPDATE dokumenkeluar SET 
                        no_dokumen=?, jns_dokumen=?, dari=?, unit_tujuan=?,        
                        perihal=?, pembuat=?, tgl_dokumen=?, kategori=?, catatan=?, pdf=? 
                    WHERE id=?";

            $stmt = $conn->prepare($sql);
            if (!$stmt) throw new Exception("Prepare failed: " . $conn->error);

            $stmt->bind_param('ssssssssssi', 
                $no_dokumen, $jns_dokumen, $dari, $unit_tujuan, $perihal, $pembuat,
                $tgl_dokumen, $kategori, $catatan, $final_pdf, $id
            );

            if (!$stmt->execute()) throw new Exception("Gagal update database: " . $stmt->error);
            $stmt->close();

            // 3. HAPUS FILE LAMA
            if ($fileDiUpload && !empty($file_lama)) {
                hapusFileLama($file_lama);
            }
            
            $message = '<i> ~ Data berhasil diperbarui.</i>';
        
        } else {
            // Action: Simpan Baru
            $sql = "INSERT INTO dokumenkeluar (no_dokumen, jns_dokumen, dari, unit_tujuan, perihal, lampiran, pembuat, tgl_dokumen, kategori, catatan, pdf) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssssssss', $no_dokumen, $jns_dokumen, $dari, $unit_tujuan, $perihal, $lampiran, $pembuat, $tgl_dokumen, $kategori, $catatan, $pdf_string);
            $stmt->execute();
            $stmt->close();
            $message = '<i> ~ Data berhasil disimpan.</i>';
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
    
    // Tambahan: Switch DB jika ada parameter tahun
    if (isset($_GET['tahun'])) {
        $tahun = preg_replace('/[^0-9]/', '', $_GET['tahun']);
        $dbTarget = ($tahun > 0) ? "sas_" . $tahun : "sas";
        try {
            $conn->select_db($dbTarget);
        } catch (Exception $e) { /* Ignore */ }
    }

    if ($id === 0) throw new Exception('ID tidak valid.');
    // Table: dokumenkeluar
    $stmt = $conn->prepare("SELECT *,  DATE(tgl_dokumen) as tgl_dokumen_raw FROM dokumenkeluar WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    $stmt->close();

    
    if ($data) {
        // [RESOLVE PDF PATH]
        $data['pdf'] = resolvePdfPath($data['pdf'], UPLOAD_DIR);

        // [TAMBAHAN] Ambil ukuran file fisik untuk sinkronisasi total size di UI
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

        kirimResponsSukses($data);
    }
    else throw new Exception('Data tidak ditemukan.');
}

function hapusData($conn) {
    $id = (int)($_POST['id'] ?? 0);
    
    // Switch DB jika ada parameter tahun
    if (isset($_POST['tahun'])) {
        $tahun = preg_replace('/[^0-9]/', '', $_POST['tahun']);
        $dbTarget = ($tahun > 0) ? "sas_" . $tahun : "sas";
        try {
            $conn->select_db($dbTarget);
        } catch (Exception $e) { /* Ignore */ }
    }

    if ($id === 0) throw new Exception('ID tidak valid.');
    $conn->begin_transaction();
    try {
        // Table: dokumenkeluar
        $stmtCek = $conn->prepare("SELECT pdf FROM dokumenkeluar WHERE id = ?");
        $stmtCek->bind_param('i', $id);
        $stmtCek->execute();
        $stmtCek->bind_result($pdf);
        $stmtCek->fetch();
        $stmtCek->close();

        $stmtHapus = $conn->prepare("DELETE FROM dokumenkeluar WHERE id = ?");
        $stmtHapus->bind_param('i', $id);
        $stmtHapus->execute();
        if ($stmtHapus->affected_rows === 0) throw new Exception('Gagal hapus data.');
        $stmtHapus->close();
        
        if (!empty($pdf)) hapusFileLama($pdf);
        
        $conn->commit();
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
    if (!isset($conn) || $conn->connect_error) throw new Exception("Koneksi DB gagal.");

    $level = ''; 
    $id_user = $_SESSION['id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 0;

    if (!empty($id_user)) {
        $stmt = $conn->prepare("SELECT level FROM tb_user WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $id_user);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $level = trim($row['level']); 
            }
            $stmt->close();
        }
    }

    $action = $_REQUEST['action'] ?? ''; 
    switch ($action) {
        case 'muatData': muatData($conn, $level); break;
        case 'simpan': case 'edit': simpanData($conn, $action); break;
        case 'ambil': ambilData($conn); break;
        case 'hapus': 
            if ($level != '1') throw new Exception('Akses ditolak (Hanya Admin).');
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
