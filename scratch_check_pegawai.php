<?php
include_once "dbconn.php";
$res = $conn->query("DESCRIBE pegawai");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Query failed: " . $conn->error;
}
?>
