<?php
include "dbconn.php";
$res = $conn->query("SHOW COLUMNS FROM pegawai");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>
