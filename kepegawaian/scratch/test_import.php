<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = [
    'nip' => '123456789',
    'nrk' => 'NRK123',
    'nama' => 'Test Pegawai',
    'tempat_lahir' => 'Jakarta',
    'tgl_lahir' => '1990-01-01',
    'jenis_kelamin' => 'L'
];
ob_start();
include "proses_import_excel.php";
$output = ob_get_clean();
echo "OUTPUT: " . $output . PHP_EOL;
?>
