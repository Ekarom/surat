<?php
// Insert initial version data
require_once 'dbconn.php';

if ($sqlconn && !$sqlconn->connect_error) {
    // Check if version table has data
    $check = mysqli_query($sqlconn, "SELECT COUNT(*) as cnt FROM version");
    $row = mysqli_fetch_assoc($check);
    
    if ($row['cnt'] == 0) {
        // Insert initial version
        $sql = "INSERT INTO version (ver, logupdate, ket) VALUES ('1.0.0', 'Initial version', 'First database version')";
        if (mysqli_query($sqlconn, $sql)) {
            echo "✓ Initial version 1.0.0 inserted successfully\n";
        } else {
            echo "✗ Error inserting version: " . mysqli_error($sqlconn) . "\n";
        }
    } else {
        echo "✓ Version table already has data (count: {$row['cnt']})\n";
    }
} else {
    echo "✗ Database connection failed\n";
}
?>
