<?php
include "../dbconn.php";
$res = $conn->query("DESCRIBE profils");
$cols = [];
while($row = $res->fetch_assoc()) {
    $cols[] = $row['Field'];
}
echo "Total Columns: " . count($cols) . "\n";
echo implode("\n", $cols);
?>
