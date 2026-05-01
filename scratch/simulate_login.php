<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
session_start();
include 'dbconn.php';

$_SESSION['captcha_answer'] = 10; 

$_POST['userid'] = 'guru';
$_POST['password'] = 'guru123';
$_POST['captcha'] = '10';

ob_start();
include 'login_proses.php';
$output = ob_get_clean();

$headers = xdebug_get_headers(); // Works if xdebug is on, else use another way
if (function_exists('xdebug_get_headers')) {
    foreach($headers as $h) echo $h . "\n";
}
echo "Session Level: " . ($_SESSION['level'] ?? 'NONE') . "\n";
echo "Session skradm: " . ($_SESSION['skradm'] ?? 'NONE') . "\n";
?>
