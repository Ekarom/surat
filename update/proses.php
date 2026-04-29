<?php
session_start();
require 'koneksi.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            // Check if 2FA is enabled
            if (!empty($row['google_auth_secret'])) {
                $_SESSION['temp_user_id'] = $row['id'];
                $_SESSION['temp_username'] = $row['username'];
                $_SESSION['temp_nama_lengkap'] = $row['nama_lengkap'];
                $_SESSION['temp_secret'] = $row['google_auth_secret']; // Store secret temporarily for verification
                
                header("Location: verify_2fa.php");
                exit();
            }

            // Normal Login (No 2FA)
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];
            
            // Redirect to setup2fa.php to prompt setup
            header("Location: setup2fa.php");
            exit();
        } else {
            $_SESSION['error'] = "Password salah!";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Username tidak ditemukan!";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
