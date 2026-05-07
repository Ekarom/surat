<?php
include_once "dbconn.php";
$res = $conn->query("SELECT status_pegawai, COUNT(*) as count FROM pegawai GROUP BY status_pegawai");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['status_pegawai'] . ": " . $row['count'] . "\n";
    }
} else {
    echo "Query failed: " . $conn->error;
}
?>
