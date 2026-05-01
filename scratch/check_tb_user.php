<?php
include 'dbconn.php';
$res = $conn->query("SHOW COLUMNS FROM tb_user");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
?>
