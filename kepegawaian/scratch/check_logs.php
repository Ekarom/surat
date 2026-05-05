<?php
include 'c:/wamp64/www/surat/dbconn.php';
$res = $conn->query("SHOW TABLES LIKE 'users_log'");
if ($res->num_rows > 0) {
    echo "users_log table exists\n";
    $res2 = $conn->query("SHOW COLUMNS FROM users_log");
    while($row2 = $res2->fetch_assoc()) {
        echo "  - " . $row2['Field'] . "\n";
    }
} else {
    echo "users_log table DOES NOT exist\n";
}
?>
