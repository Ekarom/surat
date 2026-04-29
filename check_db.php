<?php
include 'dbconn.php';
$query = "SELECT * FROM tb_user LIMIT 1";
$result = $conn->query($query);
if ($result && $result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo "No data";
}
?>
