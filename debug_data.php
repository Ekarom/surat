<?php
include_once "dbconn.php";
$sql = "SELECT id, nm_pegawai, status_pegawai, gelar_depan, gelar_belakang FROM pegawai WHERE status = '1' AND status_pegawai = 'PNS' LIMIT 10";
$res = $conn->query($sql);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "ID: " . $row['id'] . " | Name: " . $row['nm_pegawai'] . " | Status: " . $row['status_pegawai'] . " | Title Front: [" . $row['gelar_depan'] . "] | Title Back: [" . $row['gelar_belakang'] . "]\n";
    }
} else {
    echo "Error: " . $conn->error;
}
?>
