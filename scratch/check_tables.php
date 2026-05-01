<?php
include "../dbconn.php";
$tables = ['tb_user', 'pegawai', 'riwayat_kepegawaian'];
foreach ($tables as $table) {
    echo "--- $table ---\n";
    $res = $conn->query("DESCRIBE $table");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            echo "{$row['Field']} - {$row['Type']} - {$row['Null']} - {$row['Key']} - {$row['Default']} - {$row['Extra']}\n";
        }
    } else {
        echo "Table $table not found\n";
    }
    echo "\n";
}
?>
