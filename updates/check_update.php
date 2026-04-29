<?php
header('Content-Type: application/json');
error_reporting(0); 

// Helper untuk fetch URL dengan User-Agent & SSL Bypass
function fetchContent($url) {
    if (empty($url)) return false;
    $arrContextOptions = array(
        "ssl" => array(
            "verify_peer" => false,
            "verify_peer_name" => false,
        ),
        "http" => array(
            "timeout" => 15,
            "user_agent" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
            "ignore_errors" => true // Agar bisa menangkap error code 403/404
        )
    );
    $content = @file_get_contents($url, false, stream_context_create($arrContextOptions));
    return $content;
}

function remoteUrlExists(string $url): bool {
    // Kita percayakan pada fetchContent saja agar lebih efisien (1x request)
    // Fungsi ini kita ganti logikanya: Cek via fetchContent, kalau isi ada berarti exist.
    $content = fetchContent($url);
    if ($content !== false && strlen($content) > 0) return true;
    return false;
}

/**
 * Mengekstrak catatan rilis DAN URL unduhan dari blok log.
 */
function extract_log_data(string $full_log_content, string $latest_version): array {
    $blocks = preg_split('/(?=^\d+\.\d+\.\d+\s+\d+)/m', $full_log_content, -1, PREG_SPLIT_NO_EMPTY);
    $found_block_content = null;

    foreach ($blocks as $block) {
        if (str_starts_with(trim($block), $latest_version)) {
            $found_block_content = trim($block);
            break;
        }
    }

    if ($found_block_content) {
        $download_url = null;
        $release_notes = $found_block_content;
        if (preg_match('/^(https?:\/\/[^\s]+\.zip)$/m', $found_block_content, $url_matches)) {
            $download_url = $url_matches[1];
            $release_notes = preg_replace('/^' . preg_quote($download_url, '/') . '\R?/m', '', $release_notes);
        }
        return ['notes' => trim($release_notes), 'url' => $download_url];
    }

    return ['notes' => "Catatan rilis untuk $latest_version tidak ditemukan.", 'url' => null];
}

// Config
$local_version_file = 'current_version.txt';
$remote_version_url = 'http://localhost/updates/files/version.txt';
$remote_logs_url = 'http://localhost/updates/files/logs.txt';
$default_download_url = 'http://localhost/updates/files/update.zip';

// Versi Lokal
$current_version = '0.0.0';
ob_start(); 
try {
    if (file_exists(dirname(__DIR__) . '/dbconn.php')) {
        include dirname(__DIR__) . '/dbconn.php';
        if (isset($versi)) $current_version = $versi;
    }
} catch (Throwable $t) {}
$output = ob_get_clean(); 
if (empty($current_version)) $current_version = '0.0.0';

$response = [];
$local_logs_path = __DIR__ . '/files/logs.txt';
$all_log_content = false;
$debug_error_msg = "";

// 1. Prioritas REMOTE
$remote_content = fetchContent($remote_logs_url);
if ($remote_content !== false && strlen($remote_content) > 10) {
    $all_log_content = $remote_content;
} else {
    // Tangkap error terakhir kalau gagal
    $err = error_get_last();
    $debug_error_msg = "Remote Fetch Failed: " . ($err['message'] ?? 'Unknown error');
}

// 2. Fallback LOKAL
if ($all_log_content === false && file_exists($local_logs_path)) {
    $all_log_content = file_get_contents($local_logs_path);
}   

if ($all_log_content !== false) {
    if (preg_match('/^(\d+\.\d+\.\d+)/', $all_log_content, $matches)) {
        $latest_version = $matches[1];
    } else {
        $latest_version = '0.0.0';
    }
    
    $response[0] = ['id'=>'2', 'update_available' => false];

    if (version_compare($current_version, $latest_version, '<')) {        
        $log_data = extract_log_data($all_log_content, $latest_version);
        $release_notes = $log_data['notes'];
        $download_url = !empty($log_data['url']) ? $log_data['url'] : $default_download_url;
        
        if (strpos($download_url, 'https://localhost') === 0) {
            $download_url = str_replace('https://', 'http://', $download_url);
        }

        @file_put_contents('release_notes.txt', $release_notes);
        
        $response[0]['update_available'] = true;
        $response[1] = [
            'id'             => '2',
            'latest_version' => $latest_version,
            'download_url'   => $download_url,
            'release_notes'  => $release_notes
        ];
    }
    echo json_encode($response);
} else {   
    // Tampilkan pesan error detail
    echo json_encode([[
        'id' => '4',
        'Error1' => 'Error: Gagal mengambil versi terbaru. Detail: ' . $debug_error_msg,
        'DebugHint' => 'Pastikan server arsip.p171.net bisa diakses dari komputer ini.'
    ]]);
}
?>