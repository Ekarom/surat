<?php
include 'dbconn.php';
echo "--- ALL USERS ---\n";
$res = $conn->query("SELECT * FROM tb_user");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | UserID: " . $row['userid'] . " | Level: " . $row['level'] . " | Nama: " . $row['nama'] . "\n";
}
?>
