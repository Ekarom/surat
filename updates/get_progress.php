<?php
/**
 * Update Progress Tracker
 * Modified: 2025-12-27
 */

header('Content-Type: application/json');

$progress_file = __DIR__ . '/progress.json';
$progress = 0;
$message = 'Menunggu...';

if (file_exists($progress_file)) {
    $data = @json_decode(file_get_contents($progress_file), true);
    if ($data) {
        $progress = $data['progress'] ?? 0;
        $message = $data['message'] ?? 'Memproses...';
    }
}

echo json_encode([
    'progress' => $progress,
    'message' => $message
]);
?>