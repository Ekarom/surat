<?php
include 'dbconn.php';
$res = $conn->query("SELECT userid, google_auth_secret FROM tb_user LIMIT 5");
while($row = $res->fetch_assoc()) {
    echo "User: " . $row['userid'] . " | Secret: " . ($row['google_auth_secret'] ? $row['google_auth_secret'] : "[EMPTY]") . "\n";
}
?>
