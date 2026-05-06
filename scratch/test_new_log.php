<?php
session_start();
$_SESSION['userid'] = 'TestUser';
$_SESSION['nama'] = 'Test Name';
$_SESSION['level'] = '1';

include "dbconn.php";

if (function_exists('add_activity_log')) {
    $result = add_activity_log($conn, 'Testing', 'Debug', "Test Activity Log in NEW TABLE");
    if ($result) {
        echo "Log entry created in tb_activity_log.\n";
        
        $check = $conn->query("SELECT * FROM tb_activity_log WHERE info = 'Test Activity Log in NEW TABLE'");
        if ($check->num_rows > 0) {
            echo "Verified: Log found in new table.\n";
            $row = $check->fetch_assoc();
            print_r($row);
        } else {
            echo "Error: Log not found in new table.\n";
        }
    } else {
        echo "Error creating log entry.\n";
    }
} else {
    echo "Error: add_activity_log function not found.\n";
}
?>
