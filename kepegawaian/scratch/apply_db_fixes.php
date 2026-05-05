<?php
include 'c:/wamp64/www/surat/dbconn.php';

echo "Applying database updates...\n";

// 1. Add google_auth_secret to pegawai if not exists
$check_col = $conn->query("SHOW COLUMNS FROM pegawai LIKE 'google_auth_secret'");
if ($check_col->num_rows == 0) {
    echo "Adding 'google_auth_secret' column to 'pegawai' table...\n";
    if ($conn->query("ALTER TABLE pegawai ADD COLUMN google_auth_secret VARCHAR(255) DEFAULT NULL AFTER status")) {
        echo "Successfully added 'google_auth_secret'.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "'google_auth_secret' column already exists.\n";
}

// 2. Create users_log table if not exists
echo "Checking 'users_log' table...\n";
$sql_log = "CREATE TABLE IF NOT EXISTS users_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user VARCHAR(100),
    nama VARCHAR(255),
    waktu DATETIME,
    ip VARCHAR(45),
    info TEXT
)";
if ($conn->query($sql_log)) {
    echo "Table 'users_log' is ready.\n";
} else {
    echo "Error creating table 'users_log': " . $conn->error . "\n";
}

echo "Database updates completed.\n";
?>
