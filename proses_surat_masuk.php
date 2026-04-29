<?php
/**
 * proses_surat_masuk.php
 * * Update: Menyamakan struktur dengan proses_surat_masuk.php
 * * Fitur: Leveling User & Fungsi Helper di atas.
 * * Perbaikan: Logika hapus file lama saat update (mengambil data eksisting dari DB).
 */

session_start(); 
ob_start(); // Buffer output untuk mencegah error JSON
include "dbconn.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
error_reporting(E_ALL);
ini_set('display_errors', 0); // Keep 0 for JSON responses, rely on log or try-catch

// --- KONFIGURASI ---
define('UPLOAD_DIR', 'file/berkas-masuk/'); 

define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2 MB (Disinkronkan dengan UI)
define('ALLOWED_EXTENSIONS', ['pdf']);

// ... [Existing code] ...


function kirimResponsSukses($data = null, $message = '') {
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
        
        // Mencegah path traversal
        if (strpos($f, '..') !== false) continue;

        // Pattern: SEARCH di DB stored value (full relative path usually: "2025-1/file.pdf")
        // or just filename.
        
        // Strategy: Try direct delete first if path seems full
        $directPath = UPLOAD_DIR . $f;
        if (file_exists($directPath) && is_file($directPath)) {
            unlink($directPath);
            continue; // Next file
        }

        // --- Fallback legacy logic if stored just as filename ---
        $possibleFolders = [];
        $sysTahun = $_SESSION['tahundb'] ?? date('Y');
        $sysSmt = $_SESSION['semester'] ?? '1';
        if (strtolower($sysSmt) === 'ganjil') $sysSmt = '1';
        if (strtolower($sysSmt) === 'genap') $sysSmt = '2';
        
        $possibleFolders[] = $sysTahun . '-' . $sysSmt . '/';
        $possibleFolders[] = (isset($_SESSION['tapel']) ? str_replace(['/', '\\\\'], '-', $_SESSION['tapel']) : date('Y')) . '-' . $sysSmt . '/';

        if (is_dir(UPLOAD_DIR)) {
            $dirs = scandir(UPLOAD_DIR);
            foreach ($dirs as $dir) {
                if ($dir != '.' && $dir != '..' && is_dir(UPLOAD_DIR . $dir)) {
                    $possibleFolders[] = $dir . '/';
                }
            }
        }
        
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
    if (strpos($filename, '/') !== false) return $filename;
    $pattern = $baseDir . '*/' . $filename;
    $matches = glob($pattern);
    if (!empty($matches)) {
        $fullPath = $matches[0];
        $fullPath = str_replace('\\', '/', $fullPath);
        $baseDirNorm = str_replace('\\', '/', $baseDir);
        return str_replace($baseDirNorm, '', $fullPath);
    }
    return $filename;
}

function buatTabelHtml($dataRows, $offset, $level) {
    if (empty($dataRows)) {
        return '<div class="alert alert-warning text-center">Tidak ada data ditemukan.</div>';
    }

    $html = '<table class="table table-striped table-hover align-middle">';
    $html .= '<thead class="bg-menu-gradient text-center"><tr>
                <th>No</th>
                <th>No Dokumen</th>
                <th>Perihal</th>
                <th>Tgl Diterima</th>';
    if ($level == '1' || $level == '2'|| $level == '3') {
        $html .= '<th>View</th>';
    }
    if ($level == '1' || $level == '2') { 
        $html .= '<th>Edit</th>';
        $html .= '<th>Del</th>';
    }

    $html .= '</tr></thead><tbody>';
    
    $no = $offset + 1;
    foreach ($dataRows as $row) {
        $id = (int)$row['id'];
        $no_dokumen = htmlspecialchars($row['no_dokumen'] ?? '', ENT_QUOTES, 'UTF-8');
        $perihal = htmlspecialchars($row['perihal'] ?? '', ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($row['pdf'] ?? '', ENT_QUOTES, 'UTF-8');

        $tgl_formatted = '-';
        if (!empty($row['tgl_diterima'])) {
            try { $tgl_formatted = (new DateTime($row['tgl_diterima']))->format('d M Y'); } catch(Exception $e) {}
        }
        
        $viewDisabled = empty($file) ? 'disabled' : '';
        
        $html .= "<tr>
                <td class=\"text-center\">{$no}</td>
                <td>{$no_dokumen}</td>
                <td>{$perihal}</td>
                <td>{$tgl_formatted}</td>";

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
                                data-id=\"{$id}\" data-nama=\"{$no_dokumen}\" 
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
    $window = 2; 
    $showEllipsis = false;
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

function muatData($conn, $level) {
    $page = (int)($_GET['page'] ?? 1);
    $limit = (int)($_GET['limit'] ?? 10);
    $search = (string)($_GET['search'] ?? '');
    
    $tahun = (int)($_GET['tahun'] ?? 0);

    if ($page < 1) $page = 1;
    if ($limit < 1) $limit = 10;
    
    $offset = ($page - 1) * $limit;
    $searchParam = "%" . $search . "%";
    
    $searchColumns = ['no_dokumen', 'perihal', 'dari']; 
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
        try {
            $dbSelected = $conn->select_db($dbNameValues);
        } catch (Exception $e) {
            // Fallback: tetap di DB eksisting
        }
    }

    $whereClause = "";
    if (!empty($whereConditions)) {
        $whereClause = " WHERE " . implode(" AND ", $whereConditions);
    }

    $totalSql = "SELECT COUNT(*) FROM dokumenmasuk" . $whereClause;
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

    $dataSql = "SELECT * FROM dokumenmasuk" . $whereClause . " ORDER BY id ASC LIMIT ? OFFSET ?";
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
    
    // Level Label Mapping
    $levelMap = [
        '1' => '<span class="badge bg-primary">Admin</span>',
        '2' => '<span class="badge bg-info">Staff</span>',
        '3' => '<span class="badge bg-secondary">User</span>'
    ];
    $levelLabel = $levelMap[$level] ?? '<span class="badge bg-dark">Tamu / Tidak Terdeteksi</span>';
    
    $recordsInfo = buatInfoData($page, $limit, $totalRecords) . " <span class='ms-2'>Level Akses: " . $levelLabel . "</span>";

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
    // ... params ...
    $jns_dokumen = $_POST['jns_dokumen'] ?? '';
    $dari = $_POST['dari'] ?? ''; 
    $perihal = $_POST['perihal'] ?? '';
    $tgl_dokumen = !empty($_POST['tgl_dokumen']) ? $_POST['tgl_dokumen'] : null;
    $tgl_diterima = !empty($_POST['tgl_diterima']) ? $_POST['tgl_diterima'] : null;
    $kategori = $_POST['kategori'] ?? '';
    $catatan = $_POST['catatan'] ?? '';
    $lampiran = ''; 

    $fileDiUpload = false;
    $uploadedFiles = []; // Array to store successful paths

    // 1. LOGIKA UPLOAD FILE (MULTIPLE)
    // Check if pdf[] is array
    if (isset($_FILES['pdf'])) {
        // Re-organize $_FILES array for cleaner looping
        $files = $_FILES['pdf'];
        $count = is_array($files['name']) ? count($files['name']) : 1;
        
        // If it's a single file upload from standard input (fallback)
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

        // Loop through files
        for ($i = 0; $i < $count; $i++) {
            if ($files['error'][$i] === UPLOAD_ERR_OK) {
                // Validation
                 if ($files['size'][$i] > MAX_FILE_SIZE) throw new Exception('File melebihi batas 1MB.');
                 
                 $fileExt = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
                 if (!in_array($fileExt, ALLOWED_EXTENSIONS)) throw new Exception('Ekstensi file tidak diizinkan (Hanya PDF).');
                 
                 // Determination of Subfolder
                 $docDate = !empty($tgl_diterima) ? strtotime($tgl_diterima) : (!empty($tgl_dokumen) ? strtotime($tgl_dokumen) : time());
                 $sysTahun = date('Y', $docDate);
                 $month = date('n', $docDate);
                 $sysSmt = ($month >= 7) ? '1' : '2';
                 
                 $subFolder = $sysTahun . '-' . $sysSmt . '/';
                 $fullUploadDir = UPLOAD_DIR . $subFolder;
                 
                 if (!is_dir($fullUploadDir)) {
                     if (!mkdir($fullUploadDir, 0755, true)) throw new Exception('Gagal membuat folder upload.');
                 }
                 
                 $cleanNoDokumen = preg_replace("/[^a-zA-Z0-9_-]/", "_", $no_dokumen);
                 if(empty($cleanNoDokumen)) $cleanNoDokumen = 'file';
                 
                 // Unique Name: timestamp_index_cleanNo.pdf
                 $newFileName = time() . '_' . $i . '_' . $cleanNoDokumen . '.' . $fileExt;
                 $targetPath = $fullUploadDir . $newFileName;
                 
                 if (!move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                     throw new Exception('Gagal menyimpan file ke server.');
                 }
                 
                 // Standard Storage: "2025-1/file.pdf"
                 $uploadedFiles[] = $subFolder . $newFileName;
                 $fileDiUpload = true;
            }
        }
    }

    // Gabungkan string lampiran (Full string stored in 'pdf' column)
    $pdf_string = implode('|', $uploadedFiles);

    /**
     * DATABASE SWITCHING CONTEXT
     * Penting: Pastikan kita beralih ke database yang sesuai dengan tgl_diterima
     * sebelum melakukan query apa pun (SELECT target record atau UPDATE).
     */
    if (!empty($tgl_diterima)) {
        $tahunInput = date('Y', strtotime($tgl_diterima));
        if ($tahunInput) {
             $dbTarget = "sas_" . $tahunInput;
             try { 
                 $dbExists = $conn->select_db($dbTarget); 
                 if (!$dbExists) throw new Exception("Database $dbTarget tidak ditemukan.");
             } catch (Exception $e) {
                 // Fallback or re-throw
                 throw new Exception("Gagal beralih ke database $dbTarget: " . $e->getMessage());
             }
        }
    } elseif (!empty($tgl_dokumen)) { // Fallback ke tgl_dokumen jika tgl_diterima kosong
        $tahunInput = date('Y', strtotime($tgl_dokumen));
        if ($tahunInput) {
             $dbTarget = "sas_" . $tahunInput;
             try { 
                 $dbExists = $conn->select_db($dbTarget); 
                 if (!$dbExists) throw new Exception("Database $dbTarget tidak ditemukan.");
             } catch (Exception $e) {
                 // Fallback or re-throw
                 throw new Exception("Gagal beralih ke database $dbTarget: " . $e->getMessage());
             }
        }
    }

    $conn->begin_transaction();

    try {
        if ($action === 'edit') {
            if ($id === 0) throw new Exception('ID tidak valid.');

            // Ambil info file lama sebelum diupdate
            $sqlGetOld = "SELECT pdf FROM dokumenmasuk WHERE id = ?";
            $stmtGet = $conn->prepare($sqlGetOld);
            $stmtGet->bind_param('i', $id);
            $stmtGet->execute();
            $stmtGet->bind_result($file_lama_db);
            if (!$stmtGet->fetch()) {
                 $stmtGet->close();
                 throw new Exception('Data lama tidak ditemukan di database target.');
            }
            $stmtGet->close();

            $file_lama = $file_lama_db; 

            if ($fileDiUpload) {
                // Upload baru menggantikan total semua file lama
                $final_pdf = $pdf_string;
            } else {
                // Gunakan list yang dikirim dari UI (mungkin ada yang dihapus via JS)
                $posted_files_str = isset($_POST['file_lama']) ? $_POST['file_lama'] : '';
                
                // Validasi: pastikan file yang diposting memang ada di DB record tersebut
                $db_files_arr = !empty($file_lama_db) ? explode('|', $file_lama_db) : [];
                $posted_files_arr = !empty($posted_files_str) ? explode('|', $posted_files_str) : [];
                
                $valid_files_arr = array_intersect($posted_files_arr, $db_files_arr);
                $final_pdf = implode('|', $valid_files_arr);
                
                // Fisik: hapus file yang hilang dari array
                $deleted_files_arr = array_diff($db_files_arr, $valid_files_arr);
                if (!empty($deleted_files_arr)) {
                     hapusFileLama(implode('|', $deleted_files_arr));
                }
            }

            $sql = "UPDATE dokumenmasuk SET 
                        no_dokumen=?, jns_dokumen=?, dari=?, perihal=?, lampiran=?,        
                        tgl_dokumen=?, tgl_diterima=?, kategori=?, catatan=?, pdf=? 
                    WHERE id=?";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssssssssi', $no_dokumen, $jns_dokumen, $dari, $perihal, $lampiran, 
                              $tgl_dokumen, $tgl_diterima, $kategori, $catatan, $final_pdf, $id);

            if (!$stmt->execute()) throw new Exception("Gagal update data: " . $stmt->error);
            $stmt->close();

            // Jika upload baru, bersihkan semua file lama yang digantikan
            if ($fileDiUpload && !empty($file_lama)) {
                hapusFileLama($file_lama);
            }
            
            $message = '<i>~ Data berhasil diperbarui.</i>';
        
        } else {
            // INSERT
            $sql = "INSERT INTO dokumenmasuk (no_dokumen, jns_dokumen, dari, perihal, lampiran, tgl_dokumen, tgl_diterima, kategori, catatan, pdf) VALUES (?,?,?,?,?,?,?,?,?,?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssssssssss', $no_dokumen, $jns_dokumen, $dari, $perihal, $lampiran, $tgl_dokumen, $tgl_diterima, $kategori, $catatan, $pdf_string);
            
            if (!$stmt->execute()) throw new Exception("Gagal simpan data: " . $stmt->error);
            $stmt->close();
            $message = '<i>~ Data berhasil disimpan.</i>';
        }
        $conn->commit();
        kirimResponsSukses(null, $message);
    } catch (Throwable $e) {
        $conn->rollback();
        // Cleanup newly uploaded files if DB fails
        if ($fileDiUpload) hapusFileLama($pdf_string);
        throw new Exception("Error DB: " . $e->getMessage());
    }
}


function ambilData($conn) {
    $id = (int)($_GET['id'] ?? 0);
    // Tambahan: Switch DB jika ada parameter tahun
    if (isset($_GET['tahun']) && !empty($_GET['tahun'])) {
        $tahun = preg_replace('/[^0-9]/', '', $_GET['tahun']);
        if ($tahun) {
            try {
                $conn->select_db("sas_" . $tahun);
            } catch (Exception $e) { /* Ignore */ }
        }
    }

    if ($id === 0) throw new Exception('ID tidak valid.');
    // Table: dokumenmasuk
        $stmt = $conn->prepare("SELECT *, DATE(tgl_dokumen) as tgl_dokumen_raw, DATE(tgl_diterima) as tgl_diterima_raw FROM dokumenmasuk WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    $stmt->close();

    
    if ($data) {
        // [RESOLVE PDF PATH]
        // Cari path fisik file jika di DB cuma filename
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
    if (isset($_POST['tahun']) && !empty($_POST['tahun'])) {
        $tahun = preg_replace('/[^0-9]/', '', $_POST['tahun']);
        if ($tahun) {
            try {
                $conn->select_db("sas_" . $tahun);
            } catch (Exception $e) { /* Ignore */ }
        }
    }

    if ($id === 0) throw new Exception('ID tidak valid.');
    $conn->begin_transaction();
    try {
        // Table: dokumenmasuk
        $stmtCek = $conn->prepare("SELECT pdf FROM dokumenmasuk WHERE id = ?");
        $stmtCek->bind_param('i', $id);
        $stmtCek->execute();
        $stmtCek->bind_result($pdf);
        $stmtCek->fetch();
        $stmtCek->close();

        $stmtHapus = $conn->prepare("DELETE FROM dokumenmasuk WHERE id = ?");
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
