<?php
include "dbconn.php";

$dbs = ['sas_2025', 'sas_2026'];
$results = [];

foreach ($dbs as $dbName) {
    echo "Checking $dbName...\n";
    try {
        if ($conn->select_db($dbName)) {
            $res = $conn->query("SELECT COUNT(*) as cnt FROM dokumenmasuk");
            if ($res) {
                $row = $res->fetch_assoc();
                echo " - Status: EXISTS\n";
                echo " - Row Count (dokumenmasuk): " . $row['cnt'] . "\n";
            } else {
                echo " - Status: EXISTS but Query Failed: " . $conn->error . "\n";
            }
        } else {
            echo " - Status: NOT FOUND or Access Denied\n";
        }
    } catch (Exception $e) {
        echo " - Status: ERROR - " . $e->getMessage() . "\n";
    }
    echo "-------------------\n";
}
?>
