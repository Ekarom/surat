<?php
/**
 * Update Server Endpoint (Dynamic Scanner)
 * URL: http://localhost/update/api_check_update.php
 * Production: http://data.p171.net/update/api_check_update.php
 * 
 * Scans the 'files' directory for update packages
 * and returns the latest version information.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Error response helper
function sendError($message, $details = '') {
    $response = array(
        'success' => false,
        'version' => '0.0.0',
        'message' => $message,
        'download_url' => '',
        'release_date' => date('Y-m-d')
    );
    if ($details) {
        $response['error_details'] = $details;
    }
    echo json_encode($response);
    exit;
}

// Validate required server variables
if (!isset($_SERVER['DOCUMENT_ROOT']) || !isset($_SERVER['HTTP_HOST'])) {
    sendError('Server configuration error', 'Required server variables not available');
}

$filesDir = __DIR__ . '/files/';

// Check if files directory exists
if (!is_dir($filesDir)) {
    sendError('Files directory not found', "Directory '{$filesDir}' does not exist. Please create it first.");
}

// Check if directory is readable
if (!is_readable($filesDir)) {
    sendError('Permission denied', "Cannot read directory '{$filesDir}'. Please check file permissions.");
}

// Detect URL path relative to server root
$scriptDir = str_replace('\\', '/', __DIR__);
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$relPath = str_replace($docRoot, '', $scriptDir);
$baseUrl = "http://" . $_SERVER['HTTP_HOST'] . $relPath . "/files/";

// Scan directory for zip files (matching allowed upload pattern: a-zA-Z0-9.-)
$files = glob($filesDir . '*.zip');

// Check if glob failed
if ($files === false) {
    sendError('Failed to scan directory', 'glob() function returned false');
}

$latestVersion = '0.0.0';
$latestFile = '';

foreach ($files as $file) {
    $basename = basename($file);
    // Extract version from filename: 1.2.3.zip -> 1.2.3
    // Matching what was allowed in upload: a-zA-Z0-9.-
    if (preg_match('/^([a-zA-Z0-9.-]+)\.zip$/', $basename, $matches)) {
        $version = $matches[1];
        
        if (version_compare($version, $latestVersion, '>')) {
            $latestVersion = $version;
            $latestFile = $basename;
        }
    }
}

if ($latestFile) {
    $filePath = $filesDir . $latestFile;
    
    // Verify file still exists and is readable
    if (!file_exists($filePath)) {
        sendError('File not found', "Update file '{$latestFile}' no longer exists");
    }
    
    if (!is_readable($filePath)) {
        sendError('File not readable', "Cannot read update file '{$latestFile}'");
    }
    
    // Check file size - ZIP files should be at least 1KB
    $fileSize = filesize($filePath);
    if ($fileSize < 1024) {
        sendError('Invalid update file', "Update file '{$latestFile}' is too small ({$fileSize} bytes). File may be corrupted.");
    }
    
    // Verify it's a valid ZIP file
    $zip = new ZipArchive();
    $zipCheck = $zip->open($filePath, ZipArchive::CHECKCONS);
    if ($zipCheck !== true) {
        sendError('Corrupted update file', "Update file '{$latestFile}' is not a valid ZIP archive.");
    }
    $zip->close();
    
    // Check for sidecar JSON metadata
    $message = "Update version $latestVersion is available.";
    $jsonFile = $filesDir . $latestVersion . '.json';
    if (file_exists($jsonFile)) {
        $jsonData = json_decode(file_get_contents($jsonFile), true);
        if ($jsonData && isset($jsonData['message'])) {
            $message = $jsonData['message'];
        }
    }

    $response = array(
        'version' => $latestVersion,
        'message' => $message,
        'download_url' => $baseUrl . $latestFile,
        'release_date' => date('Y-m-d', filemtime($filePath)),
        'file_size' => $fileSize,
        'file_name' => $latestFile
    );
} else {
    $response = array(
        'version' => '0.0.0',
        'message' => 'No updates available.',
        'download_url' => '',
        'release_date' => date('Y-m-d')
    );
}

echo json_encode($response);
exit;
?>
