<?php
include "dbconn.php";
$res = $conn->query("DESCRIBE profils");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
?>
