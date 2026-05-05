<?php
include "../dbconn.php";
$res = $conn->query("DESCRIBE tbl_config");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
