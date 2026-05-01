<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$_REQUEST['action'] = 'muatDataJSON';
// Mock session
session_start();
$_SESSION['level'] = '1';
$_SESSION['nik'] = '12345';

// Include the script and capture output
ob_start();
include 'kepegawaian/proses_pegawai.php';
$output = ob_get_clean();

echo "RAW OUTPUT:\n";
echo "-----------\n";
echo $output;
echo "\n-----------\n";
?>
