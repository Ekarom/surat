<?php
include "../dbconn.php";

// Ambil semua nama kolom kecuali id
$res = $conn->query("SHOW COLUMNS FROM profils");
$updates = [];
while ($row = $res->fetch_assoc()) {
    $col = $row['Field'];
    if ($col !== 'id') {
        $updates[] = "`$col` = ''";
    }
}

if (!empty($updates)) {
    $sql = "UPDATE profils SET " . implode(", ", $updates) . " WHERE id = 1";
    if ($conn->query($sql)) {
        header("Location: index.php?sinkron_sekolah&msg=reset_success");
        exit;
    }
}
echo "Gagal mengosongkan data.";
?>
