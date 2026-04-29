<?php
// Quick database check script
include "dbconn.php";

$sql = "SELECT id, no_dokumen, pdf FROM dokumenmasuk LIMIT 5";
$result = mysqli_query($conn, $sql);

echo "Database PDF column values:\n";
echo str_repeat("-", 80) . "\n";

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        echo "ID: " . $row['id'] . "\n";
        echo "No Dokumen: " . $row['no_dokumen'] . "\n";
        echo "PDF Path: " . ($row['pdf'] ?? 'NULL') . "\n";
        echo str_repeat("-", 80) . "\n";
    }
} else {
    echo "Error: " . mysqli_error($conn) . "\n";
}

mysqli_close($conn);
?>
