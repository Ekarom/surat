<?php
require 'koneksi.php';

// Check if column exists
$check = mysqli_query($koneksi, "SHOW COLUMNS FROM users LIKE 'google_auth_secret'");

if (mysqli_num_rows($check) == 0) {
    $sql = "ALTER TABLE users ADD COLUMN google_auth_secret VARCHAR(255) NULL DEFAULT NULL";
    if (mysqli_query($koneksi, $sql)) {
        echo "Successfully added 'google_auth_secret' column to 'users' table.<br>";
    } else {
        echo "Error adding column: " . mysqli_error($koneksi) . "<br>";
    }
} else {
    echo "Column 'google_auth_secret' already exists.<br>";
}

echo "Database update completed.";
?>
