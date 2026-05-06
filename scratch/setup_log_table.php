<?php
include "dbconn.php";

if (!$conn || $conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS users_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(100) NOT NULL,
    nama VARCHAR(255),
    waktu DATETIME NOT NULL,
    ip VARCHAR(45),
    info TEXT,
    level VARCHAR(10),
    INDEX (user),
    INDEX (waktu)
)";

if ($conn->query($sql) === TRUE) {
    echo "Table users_log created or already exists successfully\n";
    
    // Check if 'level' column exists, add if not
    $check_level = $conn->query("SHOW COLUMNS FROM users_log LIKE 'level'");
    if ($check_level->num_rows == 0) {
        $conn->query("ALTER TABLE users_log ADD COLUMN level VARCHAR(10) AFTER info");
        echo "Column 'level' added to users_log\n";
    }
} else {
    echo "Error creating table: " . $conn->error . "\n";
}

$conn->close();
?>
