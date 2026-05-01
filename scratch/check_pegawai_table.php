<?php
include 'dbconn.php';
$res = $conn->query("SHOW TABLES LIKE 'pegawai'");
if ($res->num_rows == 0) {
    echo "Table 'pegawai' does NOT exist.\n";
} else {
    echo "Table 'pegawai' exists. Columns:\n";
    $res = $conn->query("SHOW COLUMNS FROM pegawai");
    while($row = $res->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}
?>
