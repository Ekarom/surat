<?php
include "../dbconn.php";
$cols_to_add = [
    'kode' => "VARCHAR(50) NULL",
    'youtube' => "VARCHAR(255) NULL",
    'facebook' => "VARCHAR(255) NULL",
    'twitter' => "VARCHAR(255) NULL",
    'instagram' => "VARCHAR(255) NULL",
    'stempel_sekolah' => "VARCHAR(255) NULL",
    'ttd_kepsek' => "VARCHAR(255) NULL",
    'cbt_link' => "VARCHAR(255) NULL"
];

foreach ($cols_to_add as $col => $type) {
    $check = $conn->query("SHOW COLUMNS FROM profils LIKE '$col'");
    if ($check && $check->num_rows == 0) {
        $conn->query("ALTER TABLE profils ADD COLUMN $col $type");
        echo "Added column: $col\n";
    } else {
        echo "Column already exists: $col\n";
    }
}
?>
