<?php
// Direct database insert test
require_once 'dbconn.php';

echo "=== Direct Database Insert Test ===\n\n";

if ($sqlconn && !$sqlconn->connect_error) {
    echo "✓ Connected to database: " . $db . "\n\n";
    
    // First, check current records
    $check = mysqli_query($sqlconn, "SELECT COUNT(*) as cnt FROM version");
    $row = mysqli_fetch_assoc($check);
    echo "Current record count: " . $row['cnt'] . "\n\n";
    
    // Try to insert
    $sql = "INSERT INTO version (ver, logupdate, ket) VALUES ('1.0.1', 'Initial baseline version', 'Starting point for update system')";
    echo "Executing: " . $sql . "\n";
    
    $result = mysqli_query($sqlconn, $sql);
    
    if ($result) {
        echo "✓ INSERT successful\n";
    } else {
        echo "✗ INSERT failed: " . mysqli_error($sqlconn) . "\n";
    }
    
    // Check again
    $check2 = mysqli_query($sqlconn, "SELECT * FROM version ORDER BY id DESC LIMIT 1");
    if ($check2 && mysqli_num_rows($check2) > 0) {
        $data = mysqli_fetch_assoc($check2);
        echo "\n✓ Latest version record:\n";
        print_r($data);
    } else {
        echo "\n✗ Still no records found\n";
    }
    
} else {
    echo "✗ Database connection failed\n";
}
?>
