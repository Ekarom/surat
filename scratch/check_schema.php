<?php
include "dbconn.php";
$res = $conn->query("SHOW COLUMNS FROM profils");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
