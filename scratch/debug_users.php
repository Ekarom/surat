<?php
include 'dbconn.php';
$res = $conn->query("DESCRIBE tb_user");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
echo "--- GURU USERS ---\n";
$res = $conn->query("SELECT * FROM tb_user WHERE level = '4'");
while ($row = $res->fetch_assoc()) {
    echo "ID: " . $row['id'] . " | UserID: " . $row['userid'] . " | Level: " . $row['level'] . " | NIK: " . ($row['nik'] ?? 'N/A') . "\n";
}
?>
