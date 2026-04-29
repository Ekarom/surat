<?php
session_start();
require 'koneksi.php';
require 'libs/GoogleAuthenticator.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$ga = new GoogleAuthenticator();
$msg = "";
$msg_type = "";

// Ambil data user
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($koneksi, $query);
$user = mysqli_fetch_assoc($result);

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['enable_2fa'])) {
        $secret = $_POST['secret'];
        $code = $_POST['code'];
        
        $checkResult = $ga->verifyCode($secret, $code, 2);    // 2 = 2*30sec clock tolerance

        if ($checkResult) {
            $query_update = "UPDATE users SET google_auth_secret = '$secret' WHERE id = '$user_id'";
            if (mysqli_query($koneksi, $query_update)) {
                $msg = "Two-Factor Authentication berhasil diaktifkan!";
                $msg_type = "success";
                // Refresh user data
                $user['google_auth_secret'] = $secret;
            } else {
                $msg = "Gagal menyimpan konfigurasi.";
                $msg_type = "danger";
            }
        } else {
            $msg = "Kode verifikasi salah. Silakan coba lagi.";
            $msg_type = "danger";
        }
    } 
}

// Generate secret baru jika belum ada atau untuk setup baru
$secret = $user['google_auth_secret'];
$qrCodeUrl = "";
if (empty($secret)) {
    // Generate temporary secret for setup
    // Jika user refresh page saat setup, secret akan berubah, itu wajar.
    // Kita simpan di session atau hidden field. Di sini kita pakai hidden field.
    $secret = $ga->createSecret();
    $qrCodeUrl = $ga->getQRCodeGoogleUrl('AD Update (' . $user['username'] . ')', $secret);
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup 2FA - AD UPDATE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #0f2027; 
            background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
            color: #f0f0f0; 
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 500px;
            padding: 2rem;
        }
        .form-control { background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: #fff; }
        .form-control:focus { background: rgba(0,0,0,0.3); border-color: #667eea; color: #fff; }
        .qr-code { background: #fff; padding: 10px; border-radius: 8px; display: inline-block; }
    </style>
</head>
<body>
    <div class="glass-card text-center">
        <h3 class="mb-4">Two-Factor Authentication</h3>
        
        <?php if ($msg): ?>
            <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show" role="alert">
                <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($user['google_auth_secret'])): ?>
            <!-- 2FA is Enable -->
            <div class="mb-4">
                <i class="bi bi-shield-check text-success display-1"></i>
                <h5 class="mt-3 text-success">2FA Aktif</h5>
                <p class="text-muted">Akun Anda terlindungi dengan keamanan tambahan.</p>
            </div>
            
            <!-- Disable 2FA removed as per request -->
            
            <a href="settings.php" class="btn btn-outline-light w-100 mt-2">Kembali ke Pengaturan</a>

        <?php else: ?>
            <!-- Setup 2FA -->
             <p class="text-muted mb-4">Scan QR Code di bawah ini menggunakan aplikasi Google Authenticator di HP Anda.</p>
            
            <div class="mb-4">
                <div class="qr-code">
                    <img src="<?php echo $qrCodeUrl; ?>" alt="QR Code" class="img-fluid">
                </div>
            </div>

            <div class="mb-3 text-start">
                <label class="small text-muted mb-1">Backup Key (jika QR gagal):</label>
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm text-center font-monospace" value="<?php echo $secret; ?>" readonly>
                </div>
            </div>

            <form method="POST">
                <input type="hidden" name="secret" value="<?php echo $secret; ?>">
                <div class="mb-3">
                    <label class="form-label">Masukkan Kode Verifikasi</label>
                    <input type="text" name="code" class="form-control text-center fs-4 letter-spacing-2" placeholder="000 000" maxlength="6" required autocomplete="off">
                </div>
                <button type="submit" name="enable_2fa" class="btn btn-primary w-100">Verifikasi & Aktifkan</button>
            </form>
            <a href="settings.php" class="btn btn-outline-light w-100 mt-3">Batal</a>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
