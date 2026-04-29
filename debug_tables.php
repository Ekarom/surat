<?php
include "dbconn.php";
if ($conn->select_db("sas_2026")) {
    $res = $conn->query("SHOW TABLES");
    while($row = $res->fetch_row()) {
        echo $row[0] . "\n";
    }
} else {
    echo "Failed to select sas_2026";
}
?>
