<?php
$_REQUEST['action'] = 'muatDataJSON';
$_GET['action'] = 'muatDataJSON';
session_start();
$_SESSION['level'] = '1'; // Admin

ob_start();
include 'c:\wamp64\www\surat\kepegawaian\proses_pegawai.php';
$output = ob_get_clean();

echo "OUTPUT START\n";
echo $output;
echo "\nOUTPUT END\n";

$json = json_decode($output);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo "JSON ERROR: " . json_last_error_msg() . "\n";
} else {
    echo "JSON OK\n";
}
?>
