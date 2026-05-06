<?php
include "dbconn.php";

$sql = "CREATE TABLE IF NOT EXISTS tb_activity_log (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    userid VARCHAR(50),
    nama VARCHAR(100),
    level VARCHAR(10),
    waktu DATETIME,
    ip VARCHAR(50),
    modul VARCHAR(50),
    aksi VARCHAR(50),
    info TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    echo "Table tb_activity_log created successfully.\n";
} else {
    echo "Error creating table: " . $conn->error . "\n";
}
?>
