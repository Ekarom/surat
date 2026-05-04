<?php
include "../dbconn.php";
$res = $conn->query("SELECT DISTINCT kategori FROM riwayat_kepegawaian");
while($row = $res->fetch_assoc()) {
    echo $row['kategori'] . "\n";
}
?>
