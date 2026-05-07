<?php
include_once "dbconn.php";

$columns = [
    'gelar_depan' => "VARCHAR(20)",
    'gelar_belakang' => "VARCHAR(20)"
];

$log = "";
foreach ($columns as $col => $type) {
    $check = $conn->query("SHOW COLUMNS FROM pegawai LIKE '$col'");
    if ($check->num_rows == 0) {
        if ($conn->query("ALTER TABLE pegawai ADD COLUMN $col $type")) {
            $log .= "Added column $col\n";
        } else {
            $log .= "Failed to add column $col: " . $conn->error . "\n";
        }
    } else {
        $log .= "Column $col already exists\n";
    }
}

file_put_contents("migration_log.txt", $log);
echo "Migration finished. Check migration_log.txt";
?>
