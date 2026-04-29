<?php
// Hapus semua 'session_start()'
@set_time_limit(0); 

function extract_zip($zip_file, $extractPath) {
    if (!class_exists('ZipArchive')) {
        echo "Error: PHP's ZipArchive class tidak tersedia.\n";
        return false;
    }
    
    echo "Mengekstrak update...\n";
    $zip = new ZipArchive;
    $res = $zip->open($zip_file); 

    if ($res === TRUE) {
        if ($zip->extractTo($extractPath) === TRUE) {
            echo "File ZIP berhasil diekstrak ke: " . $extractPath . "\n";
            $zip->close();
            return true;
        } else {
            echo "Error mengekstrak file ZIP.\n";
            $zip->close();
            return false;
        }
    } else {
        echo "Error membuka file ZIP. Kode: " . $res . "\n";
        return false;
    }
}

// --- File Progress ---
$progress_file = __DIR__ . '/progress.json';

function update_progress($file, $percent, $message = '') {
    @file_put_contents($file, json_encode([
        'progress' => round($percent),
        'message' => $message
    ]));
}

// --- Mulai ---
update_progress($progress_file, 0, 'Memulai...');

if (empty($_GET['url']) || !filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    update_progress($progress_file, -1, 'Error: URL unduhan tidak valid.');
    http_response_code(400);
    die('URL unduhan tidak valid.');
}

$remote_file_url = $_GET['url'];
// Localhost fix
if (strpos($remote_file_url, 'localhost') !== false || strpos($remote_file_url, '127.0.0.1') !== false) {
    $remote_file_url = str_replace('localhost', '127.0.0.1', $remote_file_url);
    if (strpos($remote_file_url, 'https://') === 0) {
        $remote_file_url = str_replace('https://', 'http://', $remote_file_url);
    }
}

$local_file = __DIR__ . '/update.zip'; 

$context_options = [
    "ssl" => ["verify_peer" => false, "verify_peer_name" => false],
    "http" => ["timeout" => 15, "ignore_errors" => true]
];
$context = stream_context_create($context_options);

// --- INTELLIGENT LOCAL COPY ---
$is_local_copy = false;
$filename = basename(parse_url($remote_file_url, PHP_URL_PATH) ?: 'update.zip');
$local_candidate_1 = __DIR__ . '/files/' . $filename;
$local_candidate_2 = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $_SERVER['DOCUMENT_ROOT'] . parse_url($remote_file_url, PHP_URL_PATH));

$target_source = '';
if (is_file($local_candidate_1)) $target_source = $local_candidate_1;
elseif (is_file($local_candidate_2)) $target_source = $local_candidate_2;

if ($target_source && is_file($target_source)) {
    update_progress($progress_file, 10, "Menyalin file lokal...");
    if (copy($target_source, $local_file)) {
        update_progress($progress_file, 100, 'Salin selesai.');
        $is_local_copy = true;
    }
} 

if (!$is_local_copy) {
    update_progress($progress_file, 1, 'Menghubungi server...');
    $remote_file = @fopen($remote_file_url, 'rb', false, $context);
    $local_handle = @fopen($local_file, 'wb');

    if (!$remote_file || !$local_handle) {
        $msg = "Error Download: Gagal membuka koneksi atau file lokal.";
        update_progress($progress_file, -1, $msg);
        die($msg);
    }

    $total_size = 0;
    if (isset($http_response_header)) {
        foreach ($http_response_header as $header) {
            if (stripos($header, 'Content-Length:') === 0) {
                $total_size = (int)trim(substr($header, 15));
                break;
            }
        }
    }

    $chunk_size = 8192;
    $bytes_downloaded = 0;
    $next_update = 0;
    
    while (!feof($remote_file)) {
        $chunk = fread($remote_file, $chunk_size);
        if ($chunk === false) break;
        fwrite($local_handle, $chunk);
        $bytes_downloaded += strlen($chunk);

        if ($total_size > 0) {
            $percent = ($bytes_downloaded / $total_size) * 100;
            if ($percent >= $next_update) {
                update_progress($progress_file, $percent, 'Mengunduh...');
                $next_update += 1; 
            }
        }
    }
    fclose($remote_file);
    fclose($local_handle);
}

update_progress($progress_file, 95, 'Unduhan selesai. Mengekstrak...');

$new_version_display = isset($new_version) ? $new_version : '(Versi Baru)';
echo "Berhasil update ke versi $new_version_display (File ZIP tersimpan).\n";

// --- SIMPAN LOG KE DATABASE ---
echo "Menyimpan log update ke database...\n";
try {
    define('NO_SESSION', true);
    if (file_exists(dirname(__DIR__) . "/cfg/konek.php")) {
        include_once dirname(__DIR__) . "/cfg/konek.php";
    }

    if (isset($sqlconn) && $sqlconn instanceof mysqli) {
        $log_content = @file_get_contents('http://arsip.p171.net/update/files/logs.txt');
        if ($log_content === false) {
             $log_path_local = __DIR__ . '/files/logs.txt';
             if (file_exists($log_path_local)) {
                $log_content = file_get_contents($log_path_local);
             }
        }

        $new_version_db = '0.0.0';
        if ($log_content && preg_match('/^(\d+\.\d+\.\d+)/', $log_content, $matches)) {
            $new_version_db = $matches[1];
        }

        // --- QUERY FIX: USE BACKTICKS FOR 'date' COLUMN ---
        $sql = "INSERT INTO `version` (`id`, `versi`, `date`) VALUES (NULL, ?, NOW())";
        $stmt = $sqlconn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("s", $new_version_db);
            if ($stmt->execute()) {
                echo "Log update berhasil disimpan ke database (Versi: $new_version_db).\n";
            } else {
                echo "Error: Gagal menyimpan log: " . $stmt->error . "\n";
            }
            $stmt->close();
        } else {
            echo "Error: Gagal prepare statement: " . $sqlconn->error . "\n";
        }
    } else {
        echo "Error: Koneksi database tidak valid.\n";
    }
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

update_progress($progress_file, 100, 'Pembaruan selesai (ZIP Tersimpan).');
?>