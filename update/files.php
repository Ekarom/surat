<?php
session_start();
require 'koneksi.php';

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// === Logic Database & Upload File ===
$msg = "";
$msg_type = "";
$uploadDir = __DIR__ . '/files/';

// Buat folder jika belum ada
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Ensure 'version' table exists
$queryTable = "CREATE TABLE IF NOT EXISTS version (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    upload_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    size INT DEFAULT 0
)";
mysqli_query($koneksi, $queryTable);

// Handle Upload POST
if (isset($_POST['upload'])) {
    $versionName = trim($_POST['version'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    // Basic Validation
    if (empty($versionName)) {
        $msg = "Versi harus diisi!";
        $msg_type = "warning";
    } elseif (!preg_match('/^[a-zA-Z0-9.-]+$/', $versionName)) {
        $msg = "Format versi tidak valid (hanya huruf, angka, titik, strip).";
        $msg_type = "warning";
    } else {
        // File Upload Error Handling
        if (!isset($_FILES['file_update'])) {
            $msg = "Pilih file terlebih dahulu.";
            $msg_type = "warning";
        } else {
            $errorCode = $_FILES['file_update']['error'];
            
            if ($errorCode !== UPLOAD_ERR_OK) {
                switch ($errorCode) {
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        $msg = "File terlalu besar! Maksimum upload diperbolehkan server: " . ini_get('upload_max_filesize');
                        break;
                    case UPLOAD_ERR_PARTIAL:
                        $msg = "File hanya terupload sebagian.";
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        $msg = "Tidak ada file yang dipilih.";
                        break;
                    case UPLOAD_ERR_NO_TMP_DIR:
                        $msg = "Folder temporary hilang. Hubungi admin server.";
                        break;
                    case UPLOAD_ERR_CANT_WRITE:
                        $msg = "Gagal menulis file ke disk.";
                        break;
                    case UPLOAD_ERR_EXTENSION:
                        $msg = "Upload dihentikan oleh ekstensi PHP.";
                        break;
                    default:
                        $msg = "Terjadi kesalahan upload tidak diketahui (Code: $errorCode).";
                        break;
                }
                $msg_type = "danger";
            } else {
                // File Valid, Process it
                $fileName   = 'update_' . $versionName . '.zip';
                $targetFile = $uploadDir . $fileName;
                $jsonFile   = $uploadDir . 'update_' . $versionName . '.json';
                
                $fileType = strtolower(pathinfo($_FILES['file_update']['name'], PATHINFO_EXTENSION));

                // Validasi ekstensi
                if ($fileType != "zip") {
                    $msg = "Hanya file dengan ekstensi .zip yang diperbolehkan!";
                    $msg_type = "danger";
                } else {
                    // Check if version already exists in DB
                    $check = mysqli_query($koneksi, "SELECT id FROM version WHERE version = '$versionName'");
                    if (mysqli_num_rows($check) > 0) {
                        $msg = "Versi <b>$versionName</b> sudah ada di database. Silakan gunakan versi lain atau hapus yang lama.";
                        $msg_type = "warning";
                    } else {
                        // Proses Upload
                        if (move_uploaded_file($_FILES['file_update']['tmp_name'], $targetFile)) {
                            
                            $fileSize = filesize($targetFile);
                            
                            // Save Description to JSON Sidecar
                            $metaData = [
                                'version' => $versionName,
                                'message' => $description,
                                'date'    => date('Y-m-d H:i:s')
                            ];
                            file_put_contents($jsonFile, json_encode($metaData, JSON_PRETTY_PRINT));

                            // INSERT TO DATABASE
                            $escDesc = mysqli_real_escape_string($koneksi, $description);
                            $escFile = mysqli_real_escape_string($koneksi, $fileName);
                            $escVer  = mysqli_real_escape_string($koneksi, $versionName);
                            
                            $sqlInsert = "INSERT INTO version (version, description, file_name, size, upload_date) 
                                          VALUES ('$escVer', '$escDesc', '$escFile', '$fileSize', NOW())";
                            
                            if (mysqli_query($koneksi, $sqlInsert)) {
                                $msg = "Paket Update versi <b>$versionName</b> berhasil diupload dan disimpan ke database!";
                                $msg_type = "success";
                            } else {
                                $msg = "File terupload tapi gagal simpan ke database: " . mysqli_error($koneksi);
                                $msg_type = "warning";
                            }

                        } else {
                            $msg = "Gagal memindahkan file upload. Cek permission folder.";
                            $msg_type = "danger";
                        }
                    }
                }
            }
        }
    }
}

// Handle Delete File
if (isset($_GET['act']) && $_GET['act'] == 'del' && isset($_GET['file'])) {
    $fileToDelete = basename($_GET['file']); 
    $targetDelete = $uploadDir . $fileToDelete;
    
    // Extract version from filename (remove 'update_' prefix and '.zip' suffix)
    // Filename format: update_VERSION.zip
    $baseName = pathinfo($fileToDelete, PATHINFO_FILENAME); // update_VERSION
    
    // Safer logic to remove 'update_' prefix:
    $versionToDelete = (strpos($baseName, 'update_') === 0) ? substr($baseName, 7) : $baseName;

    $targetJson = $uploadDir . $baseName . '.json';

    if (file_exists($targetDelete)) {
        unlink($targetDelete);
        if (file_exists($targetJson)) unlink($targetJson);
        
        // DELETE FROM DATABASE
        $escVer = mysqli_real_escape_string($koneksi, $versionToDelete);
        mysqli_query($koneksi, "DELETE FROM version WHERE version = '$escVer'");
        
        $msg = "File <b>$fileToDelete</b> berhasil dihapus dari server dan database.";
        $msg_type = "success";
    } else {
        // Try deleting from DB anyway
        $escVer = mysqli_real_escape_string($koneksi, $versionToDelete);
        $del = mysqli_query($koneksi, "DELETE FROM version WHERE version = '$escVer'");
        if ($del && mysqli_affected_rows($koneksi) > 0) {
             $msg = "File fisik tidak ditemukan, tetapi data versi berhasil dihapus dari database.";
             $msg_type = "warning";
        } else {
             $msg = "File tidak ditemukan.";
             $msg_type = "danger";
        }
    }
}

// === Get List Files ===
$files = [];
if (is_dir($uploadDir)) {
    $scanned = scandir($uploadDir);
    sort($scanned);
    
    foreach ($scanned as $f) {
        if ($f == '.' || $f == '..') continue;
        
        // Filter only zip files and preferably those starting with update_
        if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) == 'zip') {
            
            $baseName = pathinfo($f, PATHINFO_FILENAME);
            // If file starts with update_, strip it for display version, otherwise use basename
            $displayVersion = (strpos($baseName, 'update_') === 0) ? substr($baseName, 7) : $baseName;

            $jsonPath = $uploadDir . $baseName . '.json';
            
            $ASC = "-";
            if (file_exists($jsonPath)) {
                $jsonData = json_decode(file_get_contents($jsonPath), true);
                if ($jsonData && isset($jsonData['message'])) {
                    $ASC = $jsonData['message'];
                }
            }

            $files[] = [
                'name' => $f,
                'version' => $displayVersion,
                'desc' => $ASC,
                'size' => filesize($uploadDir . $f),
                'date' => filemtime($uploadDir . $f)
            ];
        }
    }
}?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Files - AD UPDATE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 80px;
        }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #0f2027; 
            background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
            color: #f0f0f0; 
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Glassmorphism Card */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.2);
            border-color: rgba(255,255,255,0.2);
        }

        /* Sidebar */
        .sidebar { 
            height: 100vh; 
            background: rgba(15, 32, 39, 0.85);
            backdrop-filter: blur(15px);
            border-right: 1px solid var(--glass-border); 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            width: var(--sidebar-width);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }

        .nav-link { 
            color: #a0a0a0; 
            padding: 1rem 1.5rem; 
            white-space: nowrap; 
            overflow: hidden; 
            border-left: 3px solid transparent;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .nav-link i {
            font-size: 1.25rem;
            margin-right: 1rem;
            width: 24px;
            text-align: center;
            transition: margin 0.3s;
        }
        
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.03); }
        .nav-link.active { background: rgba(118, 75, 162, 0.15); color: #fff; border-left-color: #764ba2; }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.collapsed { width: var(--sidebar-width-collapsed); }
        .sidebar.collapsed .logo-text, .sidebar.collapsed .nav-link span { display: none; }
        .sidebar.collapsed .sidebar-header { padding: 1.5rem 0; text-align: center; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 1rem 0; }
        .sidebar.collapsed .nav-link i { margin-right: 0; }
        .main-content.expanded { margin-left: var(--sidebar-width-collapsed); }

        /* Tables & Forms */
        .table-custom { margin-bottom: 0; color: #e0e0e0; }
        .table-custom th { background-color: rgba(0,0,0,0.2); color: #aaa; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; border-bottom: 1px solid var(--glass-border); padding: 1rem; }
        .table-custom td { padding: 1rem; border-bottom: 1px solid var(--glass-border); vertical-align: middle; }
        .table-custom tr:last-child td { border-bottom: none; }
        .table-custom tr:hover td { background-color: rgba(255,255,255,0.02); }
        
        .form-control { background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: #fff; }
        .form-control:focus { background: rgba(0,0,0,0.3); border-color: #667eea; color: #fff; box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25); }
        .form-label { font-weight: 500; font-size: 0.9rem; margin-bottom: 0.5rem; }

        /* Animations */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-up { animation: fadeInUp 0.6s ease forwards; opacity: 0; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-text text-nowrap">AD UPDATE</div>
            <div class="small text-muted mt-1 text-center" style="display: none;" id="logoSmall">AD</div>
        </div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>
            <a href="users.php" class="nav-link"><i class="bi bi-people"></i> <span>Pengguna</span></a>
            <a href="files.php" class="nav-link active"><i class="bi bi-folder2-open"></i> <span>Files</span></a>
            <a href="settings.php" class="nav-link"><i class="bi bi-gear"></i> <span>Pengaturan</span></a>
        </div>
        <div class="mt-auto mb-4">
             <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Topbar -->
        <header class="d-flex justify-content-between align-items-center mb-5 animate-up">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light border-0 me-3 rounded-circle p-2" id="sidebarToggle" style="width: 45px; height: 45px; background: rgba(255,255,255,0.05);">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h2 class="mb-0 fw-bold">Manajemen Files</h2>
                    <p class="text-muted mb-0 small">Upload & Manage Updates</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></div>
                    <div class="small text-muted">Administrator</div>
                </div>
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-person-fill fs-5"></i>
                </div>
            </div>
        </header>

        <?php if ($msg != ""): ?>
            <div class="alert alert-<?php echo $msg_type; ?> alert-dismissible fade show animate-up" role="alert">
                <i class="bi bi-info-circle-fill me-2"></i> <?php echo $msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Upload Form -->
            <div class="col-md-5">
                <div class="glass-card p-4 h-100 animate-up delay-1">
                    <h5 class="mb-4 fw-semibold"><i class="bi bi-cloud-upload me-2"></i>Upload Update Baru</h5>
                    <form method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="version" class="form-label text-muted">Versi (Contoh: 1.0.1)</label>
                            <input type="text" class="form-control" id="version" name="version" required placeholder="1.0.0">
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label text-muted">Deskripsi Update</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Apa yang baru di versi ini?"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="file_update" class="form-label text-muted">Pilih File (.zip)</label>
                            <input class="form-control" type="file" id="file_update" name="file_update" accept=".zip" required>
                        </div>
                        
                        <div class="alert alert-secondary p-2 small mb-4 bg-opacity-10 bg-white border-0 text-white-50">
                            <i class="bi bi-lightbulb me-1"></i> File akan otomatis dinamai sesuai versi.
                        </div>
                        
                        <button type="submit" name="upload" class="btn btn-primary w-100 py-2 border-0" style="background: var(--primary-gradient);">
                            <i class="bi bi-upload me-2"></i> Upload File
                        </button>
                    </form>
                </div>
            </div>

            <!-- List Files -->
            <div class="col-md-7">
                <div class="glass-card h-100 animate-up delay-2">
                    <div class="p-4 border-bottom border-white border-opacity-10">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-list-check me-2"></i>Daftar Paket Update</h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom w-100 align-middle">
                            <thead>
                                <tr>
                                    <th>Versi</th>
                                    <th>Deskripsi</th>
                                    <th>Ukuran</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($files) > 0): ?>
                                    <?php foreach ($files as $file): ?>
                                        <tr>
                                            <td><span class="badge rounded-pill bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3"><?php echo $file['version']; ?></span></td>
                                            <td><small class="text-muted d-block text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($file['desc']); ?>"><?php echo htmlspecialchars($file['desc']); ?></small></td>
                                            <td class="small text-muted"><?php echo number_format($file['size'] / 1024, 2); ?> KB</td>
                                            <td class="text-end">
                                                <a href="?act=del&file=<?php echo urlencode($file['name']); ?>" 
                                                   class="btn btn-sm btn-danger rounded-pill px-3"
                                                   onclick="return confirm('Hapus versi <?php echo $file['version']; ?> ini?');">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">Belum ada file update.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const logoText = document.querySelector('.logo-text');
    const logoSmall = document.getElementById('logoSmall');
    
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        
        if(sidebar.classList.contains('collapsed')) {
             logoText.style.display = 'none';
             logoSmall.style.display = 'block';
        } else {
             logoText.style.display = 'block';
             logoSmall.style.display = 'none';
        }
    });

    if(window.innerWidth < 768) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
        logoText.style.display = 'none';
        logoSmall.style.display = 'block';
    }
</script>
</body>
</html>