<?php
$host = "localhost";
$user = "root";
$pass = "";

$conn = @new mysqli($host, $user, $pass);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Connected successfully to MySQL<br>";

$result = $conn->query("SHOW DATABASES");
echo "Databases:<br>";
while ($row = $result->fetch_row()) {
    echo "- " . $row[0] . "<br>";
}
$conn->close();
?>
