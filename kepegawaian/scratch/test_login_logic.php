<?php
include '../../dbconn.php';

function test_login($userid, $password) {
    global $conn;
    
    $userid = trim($userid);
    $password = trim($password);

    $stmt = $conn->prepare('SELECT id, nrk, nip FROM pegawai WHERE (nrk = ? OR nip = ?) LIMIT 1');
    $stmt->bind_param("ss", $userid, $userid);
    $stmt->execute();
    $pegawai = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($pegawai) {
        $nrk_db = !empty($pegawai['nrk']) ? trim($pegawai['nrk']) : '';
        $nip_db = !empty($pegawai['nip']) ? trim($pegawai['nip']) : '';

        if (($nrk_db !== '' && $password === $nrk_db) || ($nip_db !== '' && $password === $nip_db)) {
            return "SUCCESS";
        }
    }
    return "FAILED";
}

// Get a real user from DB for testing
$res = $conn->query("SELECT nrk, nip FROM pegawai WHERE nrk IS NOT NULL AND nip IS NOT NULL LIMIT 1");
if ($row = $res->fetch_assoc()) {
    $nrk = $row['nrk'];
    $nip = $row['nip'];
    
    echo "Testing User: NRK=$nrk, NIP=$nip\n";
    echo "User=NIP, Pass=NIP: " . test_login($nip, $nip) . "\n";
    echo "User=NIP, Pass=NRK: " . test_login($nip, $nrk) . "\n";
    echo "User=NRK, Pass=NRK: " . test_login($nrk, $nrk) . "\n";
    echo "User=NRK, Pass=NIP: " . test_login($nrk, $nip) . "\n";
    echo "User=NIP, Pass=WRONG: " . test_login($nip, '12345') . "\n";
} else {
    echo "No suitable test user found in 'pegawai' table.\n";
}
?>
