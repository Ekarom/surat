<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_REQUEST['action'] = 'muatDataJSON';
// Mock session
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['level'] = '1';
$_SESSION['nik'] = '12345';

// Include the script
include 'kepegawaian/proses_pegawai.php';
?>
