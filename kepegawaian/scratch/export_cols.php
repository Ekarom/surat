<?php
include __DIR__ . "/../../dbconn.php";
$r = mysqli_query($conn, "DESCRIBE pegawai");
$cols = [];
while ($row = mysqli_fetch_assoc($r)) {
    $cols[] = $row['Field'];
}
file_put_contents("columns.txt", implode("\n", $cols));
echo "DONE";
?>
