<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = @new mysqli('localhost', 'root', '', 'sas_');
if ($conn->connect_error) {
    $c = @new mysqli('localhost','root','');
    $r = $c->query("SHOW DATABASES LIKE 'sas_%'");
    $db = '';
    while ($row = $r->fetch_row()) { $db = $row[0]; }
    $conn = @new mysqli('localhost','root','',$db);
    echo "Using DB: $db\n";
}

echo "=== COLUMNS pegawai ===\n";
$res = $conn->query('SHOW COLUMNS FROM pegawai');
$has_last_activity = false;
$has_jabatan = false;
if ($res) {
    while ($r = $res->fetch_assoc()) {
        echo $r['Field'] . ' | ' . $r['Type'] . "\n";
        if ($r['Field'] === 'last_activity') $has_last_activity = true;
        if ($r['Field'] === 'jabatan') $has_jabatan = true;
    }
} else {
    echo 'ERROR: ' . $conn->error . "\n";
}

echo "\n=== STATUS ===\n";
echo "last_activity exists: " . ($has_last_activity ? "YES" : "NO - PERLU ALTER TABLE!") . "\n";
echo "jabatan exists: " . ($has_jabatan ? "YES" : "NO") . "\n";

// Try the actual query
echo "\n=== TEST QUERY ===\n";
$test = $conn->query("SELECT id, nm_pegawai, last_activity, status_pegawai, jabatan FROM pegawai LIMIT 1");
if ($test) {
    echo "Query OK. Rows: " . $test->num_rows . "\n";
} else {
    echo "Query ERROR: " . $conn->error . "\n";
}
?>
