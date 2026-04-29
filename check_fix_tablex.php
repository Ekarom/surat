<?php
include 'dbconn.php';

echo "Checking 'user_devices' table structure...\n";
$res = $conn->query("DESCRIBE user_devices");

$needs_fix = false;
$id_col_exists = false;

if ($res) {
    while ($row = $res->fetch_assoc()) {
        if ($row['Field'] == 'id') {
            $id_col_exists = true;
            echo "Column 'id' found. Extra: " . $row['Extra'] . "\n";
            if (strpos($row['Extra'], 'auto_increment') === false) {
                $needs_fix = true;
                echo "AUTO_INCREMENT missing on 'id'.\n";
            }
        }
    }
} else {
    echo "Error describing table: " . $conn->error . "\n";
}

if ($needs_fix) {
    echo "Applying fix: ALTER TABLE user_devices MODIFY id INT AUTO_INCREMENT...\n";
    if ($conn->query("ALTER TABLE user_devices MODIFY id INT AUTO_INCREMENT")) {
        echo "Fix applied successfully.\n";
    } else {
        echo "Failed to apply fix: " . $conn->error . "\n";
    }
} elseif (!$id_col_exists) {
    echo "Column 'id' not found in table!\n";
} else {
    echo "Table structure looks correct (AUTO_INCREMENT present).\n";
}
?>
