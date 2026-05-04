<?php
include '../dbconn.php';
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_array()) {
    echo $row[0] . "\n";
    $res2 = $conn->query("DESCRIBE " . $row[0]);
    while($row2 = $res2->fetch_assoc()) {
        echo "  - " . $row2['Field'] . " (" . $row2['Type'] . ")\n";
    }
}
?>
