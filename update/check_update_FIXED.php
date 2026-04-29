<?php
/**
 * Update Checker - Fixed Version
 * File: check_update.php
 * 
 * This file checks for application updates from the update server
 */

// ============================================
// CONFIGURATION
// ============================================

// Auto-detect environment
$isLocalhost = (
    isset($_SERVER['HTTP_HOST']) && (
        $_SERVER['HTTP_HOST'] === 'localhost' || 
        $_SERVER['HTTP_HOST'] === '127.0.0.1' ||
        strpos($_SERVER['HTTP_HOST'], 'localhost') !== false
    )
);

// Set update URL based on environment
if ($isLocalhost) {
    // Development - Local Server
    $updateUrl = "http://localhost/update/api_check_update.php";
} else {
    // Production - Remote Server
    $updateUrl = "http://data.p171.net/update/api_check_update.php";
}

// Current application version (UPDATE THIS!)
$currentVersion = "1.0.0";

// ============================================
// FETCH UPDATE FUNCTION
// ============================================

function fetchUpdate($url) {
    // Method 1: Try cURL (most reliable)
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_USERAGENT => 'UpdateChecker/1.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($response !== false && $httpCode === 200) {
            return [
                'success' => true, 
                'data' => $response,
                'method' => 'cURL'
            ];
        }
        
        $errorMsg = $error ?: "HTTP $httpCode";
        return [
            'success' => false, 
            'error' => "cURL failed: $errorMsg"
        ];
    }
    
    // Method 2: Fallback to file_get_contents
    if (ini_get('allow_url_fopen')) {
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true,
                'user_agent' => 'UpdateChecker/1.0'
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            return [
                'success' => true, 
                'data' => $response,
                'method' => 'file_get_contents'
            ];
        }
        
        return [
            'success' => false, 
            'error' => 'file_get_contents failed - server may be unreachable'
        ];
    }
    
    // No method available
    return [
        'success' => false, 
        'error' => 'No HTTP method available (enable cURL or allow_url_fopen in PHP)'
    ];
}

// ============================================
// CHECK FOR UPDATES
// ============================================

$result = fetchUpdate($updateUrl);

if (!$result['success']) {
    // Connection failed
    echo json_encode([
        'status' => 'error',
        'message' => 'Failed to check for updates: ' . $result['error'],
        'current_version' => $currentVersion,
        'update_url' => $updateUrl
    ]);
    exit;
}

// Parse JSON response
$data = json_decode($result['data'], true);

if (!$data || json_last_error() !== JSON_ERROR_NONE) {
    // Invalid JSON
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid response from update server',
        'json_error' => json_last_error_msg(),
        'current_version' => $currentVersion
    ]);
    exit;
}

// Check if update is available
$updateAvailable = version_compare($data['version'], $currentVersion, '>');

// Return response
echo json_encode([
    'status' => 'success',
    'current_version' => $currentVersion,
    'latest_version' => $data['version'],
    'update_available' => $updateAvailable,
    'message' => $data['message'] ?? '',
    'download_url' => $data['download_url'] ?? '',
    'release_date' => $data['release_date'] ?? '',
    'file_size' => $data['file_size'] ?? 0,
    'method' => $result['method']
]);
?>
