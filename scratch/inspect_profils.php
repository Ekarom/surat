<?php
include 'dbconn.php';
$table = 'profils';
echo "Table: $table\n";
$res = $conn->query("DESCRIBE $table");
if ($res) {
    while($row = $res->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "  Error: " . $conn->error . "\n";
}
?>
