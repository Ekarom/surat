<?php
include 'c:/wamp64/www/surat/dbconn.php';
$res = $conn->query("SHOW COLUMNS FROM users_log");
if ($res) {
    while($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . $conn->error;
}
?>
