<?php
include __DIR__ . "/../../dbconn.php";
if(!$conn) die("Fail connection via dbconn.php");

$r = mysqli_query($conn, "SELECT * FROM pegawai WHERE nip = '123456789'");
if(mysqli_num_rows($r) == 0) {
    echo "DATA_NOT_FOUND: 123456789" . PHP_EOL;
} else {
    $row = mysqli_fetch_assoc($r);
    echo "DATA_FOUND: " . $row['nm_pegawai'] . " (" . $row['nip'] . ")" . PHP_EOL;
    echo "TMT_PANGKAT: " . ($row['tmt_pangkat'] ?? 'NULL') . PHP_EOL;
    echo "AGAMA: " . ($row['agama'] ?? 'NULL') . PHP_EOL;
    echo "NUPTK: " . ($row['nuptk'] ?? 'NULL') . PHP_EOL;
}
?>
