<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head><title>Statistik (Coming Soon)</title></head>
<body>
    <h1>Fitur Statistik akan segera hadir!</h1>
    <a href="dashboard.php">Kembali ke Dashboard</a>
</body>
</html>
