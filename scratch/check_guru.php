<?php
include 'dbconn.php';
$res = $conn->query("SELECT * FROM tb_user WHERE level = '4'");
if ($res && $res->num_rows > 0) {
    echo "Found " . $res->num_rows . " Guru users.\n";
    while ($row = $res->fetch_assoc()) {
        echo "User: " . $row['userid'] . " | Level: " . $row['level'] . " | Status: " . $row['status'] . "\n";
    }
} else {
    echo "No Guru users found.\n";
}
?>
