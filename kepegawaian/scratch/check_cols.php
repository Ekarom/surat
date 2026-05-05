<?php
include 'c:/wamp64/www/surat/dbconn.php';
$res = $conn->query("SHOW COLUMNS FROM pegawai");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
