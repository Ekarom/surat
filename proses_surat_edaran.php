<?php
/**
 * proses_surat_edaran.php
 * * Perbaikan: Logika Hapus File Lama & Proteksi Data Kosong saat Edit
 * * Tabel: dokumenedaran
 */

session_start(); 
ob_start(); 
include "dbconn.php";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// --- KONFIGURASI KHUSUS EDARAN ---
define('UPLOAD_DIR', 'file/berkas-edaran/'); 
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
    if (empty($dataRows)) return '<div class="alert alert-warning text-center">Tidak ada data ditemukan.</div>';

    $html = '<table class="table table-striped table-hover align-middle" style="width:100%">';
    $html .= '<thead><tr>
                <th class="text-center" width="50">No</th>
                <th width="150">No Surat</th>
                <th width="200">Ditujukan</th>
                <th width="300">Perihal</th>
                <th width="120" class="text-center">Tgl Dokumen</th>';
    if ($level == '1' || $level == '2' || $level == '3') $html .= '<th width="120" class="text-center">Aksi</th>';
    $html .= '</tr></thead><tbody>';
    
    $no = $offset + 1;
    foreach ($dataRows as $row) {
        $id = (int)$row['id'];
        $no_surat = htmlspecialchars($row['no_surat'] ?? '', ENT_QUOTES, 'UTF-8');
        $ditujukan = htmlspecialchars($row['ditujukan'] ?? '', ENT_QUOTES, 'UTF-8');
        $perihal = htmlspecialchars($row['perihal'] ?? '', ENT_QUOTES, 'UTF-8');
        $file = htmlspecialchars($row['pdf'] ?? '', ENT_QUOTES, 'UTF-8');

        $tgl_formatted = '-';
        if (!empty($row['tgl_dokumen'])) {
            try { $tgl_formatted = (new DateTime($row['tgl_dokumen']))->format('d/m/Y'); } catch(Exception $e) {}
        }
        
        $html .= "<tr>
                <td class=\"text-center\">{$no}</td>
                <td class=\"fw-bold text-primary\">{$no_surat}</td>
                <td>{$ditujukan}</td>
                <td><div class=\"text-truncate\" style=\"max-width: 300px;\">{$perihal}</div></td>
                <td class=\"text-center\">{$tgl_formatted}</td>";

        if ($level == '1' || $level == '2' || $level == '3') {
            $html .= "<td class=\"text-center\"><div class=\"d-flex justify-content-center gap-1\">";
            
            // View Button
            $opacity = empty($file) ? 'opacity-50' : '';
            $html .= "<span class=\"badge-square badge badge-info text-white tombol-view {$opacity}\" data-id=\"{$id}\" title=\"Lihat PDF\"><i class=\"la la-eye\"></i></span>";

            if ($level == '1' || $level == '2') {
                // Edit Button
                $html .= "<span class=\"badge-square badge badge-warning text-white tombol-edit\" data-id=\"{$id}\" title=\"Edit Data\"><i class=\"la la-edit\"></i></span>";
                
                // Delete Button
                if ($level == '1') {
                    $html .= "<span class=\"badge-square badge badge-danger text-white tombol-hapus\" data-id=\"{$id}\" data-nama=\"{$no_surat}\" title=\"Hapus Data\"><i class=\"la la-trash\"></i></span>";
                }
            }
            $html .= "</div></td>";
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
    
    $searchColumns = ['no_surat', 'ditujukan', 'perihal']; // Sesuaikan kolom tabel dokumenedaran
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

    $totalSql = "SELECT COUNT(*) FROM dokumenedaran" . $whereClause;
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

    $dataSql = "SELECT * FROM dokumenedaran" . $whereClause . " ORDER BY id ASC LIMIT ? OFFSET ?";
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
    $id = (int)($_POST['id'] ?? 0);
    $no_surat = $_POST['no_surat'] ?? '';
    $ditujukan = $_POST['ditujukan'] ?? '';
    $perihal = $_POST['perihal'] ?? '';
    $tgl_dokumen = !empty($_POST['tgl_dokumen']) ? $_POST['tgl_dokumen'] : null;
    
    // Kita TIDAK lagi menggunakan $_POST['pdf'] untuk referensi hapus
    // $file_lama = $_POST['pdf'] ?? ''; <--- INI SUMBER MASALAHNYA
    

    // Variabel untuk handling file
    $fileDiUpload = false;
    $uploadedFiles = [];

    // 1. PROSES UPLOAD FILE (MULTIPLE)
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
                 if (!in_array($fileExt, ALLOWED_EXTENSIONS)) throw new Exception('Ekstensi file tidak diizinkan. Hanya PDF.');
                 
                 // Sanitasi nama file
                 $cleanNoSurat = preg_replace("/[^a-zA-Z0-9_-]/", "_", $no_surat);
                 if(empty($cleanNoSurat)) $cleanNoSurat = 'file';
                 
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
                 $newFileName = time() . '_' . $i . '_' . $cleanNoSurat . '.' . $fileExt;
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
     * Prioritaskan tahun dari input (untuk Edit) atau tentukan dari tanggal (untuk Simpan Baru)
     */
    $dbAsal = $_POST['tahun'] ?? '';
    if ($action === 'edit' && !empty($dbAsal)) {
        if (!$conn->select_db("sas_" . $dbAsal)) {
            throw new Exception("Database sas_$dbAsal tidak ditemukan.");
        }
    } elseif (!empty($tgl_dokumen)) {
        $tahunInput = date('Y', strtotime($tgl_dokumen));
        if ($tahunInput) {
             $dbTarget = "sas_" . $tahunInput;
             try { 
                 if (!$conn->select_db($dbTarget)) throw new Exception("Database $dbTarget tidak ditemukan.");
             } catch (Exception $e) {
                 throw new Exception("Gagal beralih ke database $dbTarget: " . $e->getMessage());
             }
        }
    }

    $conn->begin_transaction();
    try {
        if ($action === 'edit') {
            if ($id === 0) throw new Exception('ID tidak valid untuk edit.');

            $sqlGetOld = "SELECT pdf FROM dokumenedaran WHERE id = ?";
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
                $final_pdf = $file_lama;
            }

            $sql = "UPDATE dokumenedaran SET 
                        no_surat = ?, ditujukan = ?, perihal = ?, tgl_dokumen = ?, pdf = ?
                    WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssssi', $no_surat, $ditujukan, $perihal, $tgl_dokumen, $final_pdf, $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Gagal update database: " . $stmt->error);
            }
            $stmt->close();

            // Hapus fisik file lama jika ada upload baru & file lama ada
            if ($fileDiUpload && !empty($file_lama)) {
                hapusFileLama($file_lama);
            }
            
            $message = '<i> ~ Data berhasil diperbarui.</i>';
        } else {
            // Mode SIMPAN BARU
            $sql = "INSERT INTO dokumenedaran (no_surat, ditujukan, perihal, tgl_dokumen, pdf) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sssss', $no_surat, $ditujukan, $perihal, $tgl_dokumen, $pdf_string);
            $stmt->execute();
            $stmt->close();
        
            $message = '<i> ~ Data berhasil disimpan.</i>';
        }
        $conn->commit();
        kirimResponsSukses(null, $message);
    } catch (Throwable $e) {
        $conn->rollback();
        // Bersihkan file baru jika transaksi DB gagal
        if ($fileDiUpload) hapusFileLama($pdf_string);
        throw new Exception("Error database: " . $e->getMessage());
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
    
    $sql = "SELECT *, DATE(tgl_dokumen) as tgl_dokumen_raw FROM dokumenedaran WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
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
        // Ambil info file sebelum hapus row DB
        $stmtCek = $conn->prepare("SELECT pdf FROM dokumenedaran WHERE id = ?");
        $stmtCek->bind_param('i', $id);
        $stmtCek->execute();
        $stmtCek->bind_result($pdf);
        $stmtCek->fetch();
        $stmtCek->close();

        $stmtHapus = $conn->prepare("DELETE FROM dokumenedaran WHERE id = ?");
        $stmtHapus->bind_param('i', $id);
        $stmtHapus->execute();
        if ($stmtHapus->affected_rows === 0) throw new Exception('<i> ~ Data gagal dihapus.</i>');
        $stmtHapus->close();
        
        // Hapus fisik file
        if (!empty($pdf)) hapusFileLama($pdf);
        
        $conn->commit();
        kirimResponsSukses(null, '<i> ~ Data berhasil dihapus.</i>');
    } catch (Throwable $e) {
        $conn->rollback();
        throw new Exception('Gagal menghapus data: ' . $e->getMessage());
    }
}

// ==================================================================
// BAGIAN 3: ROUTER UTAMA
// ==================================================================

try {
    if (!isset($conn) || $conn->connect_error) throw new Exception("Koneksi database gagal.");

    $level = ''; 
    $id_user = $_SESSION['id'] ?? $_SESSION['id_user'] ?? $_SESSION['user_id'] ?? 0;

    if (!empty($id_user)) {
        $stmtUser = $conn->prepare("SELECT level FROM tb_user WHERE id = ?");
        if ($stmtUser) {
            $stmtUser->bind_param("i", $id_user);
            $stmtUser->execute();
            $resultUser = $stmtUser->get_result();
            if ($resultUser->num_rows > 0) {
                $rowUser = $resultUser->fetch_assoc();
                $level = trim($rowUser['level']); 
            }
            $stmtUser->close();
        }
    }

    $action = $_REQUEST['action'] ?? ''; 

    switch ($action) {
        case 'muatData': muatData($conn, $level); break;
        case 'simpan': case 'edit': simpanData($conn, $action); break;
        case 'ambil': ambilData($conn); break;
        case 'hapus': 
            if ($level != '1') throw new Exception('<i> ~ Anda tidak memiliki izin untuk menghapus data.</i>');
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