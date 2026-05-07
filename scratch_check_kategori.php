<?php
include_once "dbconn.php";
$res = $conn->query("SELECT DISTINCT kategori FROM riwayat_kepegawaian");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo $row['kategori'] . "\n";
    }
} else {
    echo "Query failed: " . $conn->error;
}
?>
