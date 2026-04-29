<?php
// list_dbs.php
$host = "localhost";
$user = "root";
$pass = ""; 

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$result = $conn->query("SHOW DATABASES LIKE 'sas_%'");
$dbs = [];
if ($result) {
    echo "Found databases:\n";
    while($row = $result->fetch_row()) {
        echo "- " . $row[0] . "\n";
        $dbs[] = $row[0];
    }
} else {
    echo "Query failed.\n";
}

// Also check db.txt content if exists
$db_file = __DIR__ . '/cfg/db.txt';
if (file_exists($db_file)) {
    echo "\nContent of cfg/db.txt:\n" . file_get_contents($db_file) . "\n";
} else {
    echo "\ncfg/db.txt does not exist.\n";
}
?>
