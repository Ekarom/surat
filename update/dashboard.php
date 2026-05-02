<?php
/**
 * Dashboard & Update Receiver
 * Handle UI (GET) and API (POST)
 */

session_start();
require 'koneksi.php';

// Ensure sync_logs table exists
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS sync_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    server_name VARCHAR(100),
    version VARCHAR(50),
    sync_type VARCHAR(50),
    data TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Log file for fallback/debugging
$logFile = __DIR__ . '/sync_log.txt';

function writeLog($message)
{
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
}

// --- API HANDLING (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $rawData = file_get_contents('php://input');
    // Minimal file logging just to trace receipt
    writeLog("Received POST request from IP: " . $_SERVER['REMOTE_ADDR']);

    $data = json_decode($rawData, true);

    // Validate JSON
    if (!$data) {
        writeLog("Error: Invalid JSON data");
        echo json_encode(['status' => 'error', 'message' => 'Invalid JSON data']);
        exit;
    }

    try {
        // 1. Save Raw Data as JSON File (Backup/Reference)
        $dataFile = __DIR__ . '/sync_data_' . date('Ymd_His') . '.json';
        file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));

        // 2. Log to Database
        $server_name = isset($data['server_name']) ? mysqli_real_escape_string($koneksi, $data['server_name']) : 'unknown';
        $version = isset($data['version']['versi']) ? mysqli_real_escape_string($koneksi, $data['version']['versi']) : 'unknown';
        $sync_type = isset($data['sync_type']) ? mysqli_real_escape_string($koneksi, $data['sync_type']) : 'unknown';
        $json_data = mysqli_real_escape_string($koneksi, $rawData);

        $logQuery = "INSERT INTO sync_logs (server_name, version, sync_type, data) VALUES ('$server_name', '$version', '$sync_type', '$json_data')";
        mysqli_query($koneksi, $logQuery);

        // 3. Process Files
        $savedFiles = [];
        if (isset($data['files']) && is_array($data['files'])) {
            $filesDir = __DIR__ . '/synced_files';
            if (!is_dir($filesDir))
                mkdir($filesDir, 0755, true);

            $timestamp = date('Ymd_His');
            $versionDir = $filesDir . '/' . $timestamp;
            if (!is_dir($versionDir))
                mkdir($versionDir, 0755, true);

            foreach ($data['files'] as $filePath => $fileInfo) {
                if (isset($fileInfo['content']) && !isset($fileInfo['error'])) {
                    $content = base64_decode($fileInfo['content']);
                    $targetPath = $versionDir . '/' . $filePath;
                    $targetDir = dirname($targetPath);
                    if (!is_dir($targetDir))
                        mkdir($targetDir, 0755, true);

                    if (file_put_contents($targetPath, $content)) {
                        $savedFiles[] = [
                            'path' => $filePath,
                            'saved_to' => $targetPath,
                            'size' => $fileInfo['size'],
                            'md5' => $fileInfo['md5']
                        ];
                    }
                }
            }
        }

        // 4. Send Response
        $response = [
            'status' => 'success',
            'message' => 'Data received and saved successfully',
            'received_at' => date('Y-m-d H:i:s'),
            'data_summary' => [
                'server_name' => $server_name,
                'version' => $version,
                'files_received' => count($savedFiles)
            ]
        ];

        echo json_encode($response);

    } catch (Exception $e) {
        writeLog("Error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'Internal Server Error: ' . $e->getMessage()]);
    }
    exit;
}

// --- UI HANDLING (GET) ---

// Authentication Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch Stats
// 1. Total Syncs (from DB)
$totalSyncs = 0;
$result = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM sync_logs");
if ($row = mysqli_fetch_assoc($result)) {
    $totalSyncs = $row['total'];
}

// 2. Fetch Logs (from DB)
$dbLogs = [];
$logResult = mysqli_query($koneksi, "SELECT * FROM sync_logs ORDER BY created_at DESC LIMIT 50");
while ($row = mysqli_fetch_assoc($logResult)) {
    $dbLogs[] = $row;
}

// 3. Synced Versions (from Folder)
$versions = [];
$filesDir = __DIR__ . '/synced_files';
if (is_dir($filesDir)) {
    $scanned = scandir($filesDir);
    foreach ($scanned as $folder) {
        if ($folder !== '.' && $folder !== '..' && is_dir($filesDir . '/' . $folder)) {
            $versions[] = $folder;
        }
    }
    rsort($versions);
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - AD UPDATE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
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
            /* fallback for old browsers */
            background: -webkit-linear-gradient(to right, #2c5364, #203a43, #0f2027);
            /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
            /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
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
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Sidebar */
        .sidebar {
            height: 100vh;
            background: rgba(15, 32, 39, 0.85);
            /* Slightly clearer for readability */
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

        .nav-link:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.03);
        }

        .nav-link.active {
            background: rgba(118, 75, 162, 0.15);
            color: #fff;
            border-left-color: #764ba2;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Sidebar Collapsed */
        .sidebar.collapsed {
            width: var(--sidebar-width-collapsed);
        }

        .sidebar.collapsed .logo-text,
        .sidebar.collapsed .nav-link span {
            display: none;
        }

        .sidebar.collapsed .sidebar-header {
            padding: 1.5rem 0;
            text-align: center;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center;
            padding: 1rem 0;
        }

        .sidebar.collapsed .nav-link i {
            margin-right: 0;
        }

        .main-content.expanded {
            margin-left: var(--sidebar-width-collapsed);
        }

        /* Tables */
        .table-custom {
            margin-bottom: 0;
            color: #e0e0e0;
        }

        .table-custom th {
            background-color: rgba(0, 0, 0, 0.2);
            color: #aaa;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--glass-border);
            padding: 1rem;
        }

        .table-custom td {
            padding: 1rem;
            border-bottom: 1px solid var(--glass-border);
            vertical-align: middle;
        }

        .table-custom tr:last-child td {
            border-bottom: none;
        }

        .table-custom tr:hover td {
            background-color: rgba(255, 255, 255, 0.02);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-up {
            animation: fadeInUp 0.6s ease forwards;
            opacity: 0;
        }

        .delay-1 {
            animation-delay: 0.1s;
        }

        .delay-2 {
            animation-delay: 0.2s;
        }

        .delay-3 {
            animation-delay: 0.3s;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #0f2027;
        }

        ::-webkit-scrollbar-thumb {
            background: #3a4b55;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #764ba2;
        }
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
            <a href="dashboard.php" class="nav-link active"><i class="bi bi-speedometer2"></i>
                <span>Dashboard</span></a>
            <a href="users.php" class="nav-link"><i class="bi bi-people"></i> <span>Pengguna</span></a>
            <a href="files.php" class="nav-link"><i class="bi bi-folder2-open"></i> <span>Files</span></a>
            <a href="settings.php" class="nav-link"><i class="bi bi-gear"></i> <span>Pengaturan</span></a>
        </div>

        <div class="mt-auto mb-4">
            <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i>
                <span>Logout</span></a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Topbar -->
        <header class="d-flex justify-content-between align-items-center mb-5 animate-up">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light border-0 me-3 rounded-circle p-2" id="sidebarToggle"
                    style="width: 45px; height: 45px; background: rgba(255,255,255,0.05);">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h2 class="mb-0 fw-bold">Dashboard</h2>
                    <p class="text-muted mb-0 small">Overview & Statistics</p>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></div>
                    <div class="small text-muted">Administrator</div>
                </div>
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                    style="width: 45px; height: 45px;">
                    <i class="bi bi-person-fill fs-5"></i>
                </div>
            </div>
        </header>

        <!-- Stats Cards -->
        <div class="row g-4 mb-5">
            <!-- Total Syncs -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100 animate-up delay-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">Total Sync Requests</p>
                            <h2 class="display-5 fw-bold mb-0"><?php echo number_format($totalSyncs); ?></h2>
                        </div>
                        <div class="p-3 rounded-circle" style="background: rgba(118, 75, 162, 0.2); color: #764ba2;">
                            <i class="bi bi-activity fs-4"></i>
                        </div>
                    </div>
                    <div
                        class="mt-4 pt-3 border-top border-white border-opacity-10 d-flex align-items-center small text-success">
                        <i class="bi bi-arrow-up-short fs-5 me-1"></i> <span>Live Monitoring</span>
                    </div>
                </div>
            </div>

            <!-- Active Folders -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100 animate-up delay-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">Synced Folders</p>
                            <h2 class="display-5 fw-bold mb-0"><?php echo count($versions); ?></h2>
                        </div>
                        <div class="p-3 rounded-circle" style="background: rgba(102, 126, 234, 0.2); color: #667eea;">
                            <i class="bi bi-folder-check fs-4"></i>
                        </div>
                    </div>
                    <div
                        class="mt-4 pt-3 border-top border-white border-opacity-10 d-flex align-items-center small text-info">
                        <i class="bi bi-clock me-2"></i> <span>Latest:
                            <?php echo !empty($versions) ? $versions[0] : '-'; ?></span>
                    </div>
                </div>
            </div>

            <!-- System Status (Static for now) -->
            <div class="col-md-6 col-lg-4">
                <div class="glass-card p-4 h-100 animate-up delay-3">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted text-uppercase small fw-semibold mb-2">Server Status</p>
                            <h2 class="fs-2 fw-bold mb-0 text-success">Online</h2>
                        </div>
                        <div class="p-3 rounded-circle" style="background: rgba(25, 135, 84, 0.2); color: #198754;">
                            <i class="bi bi-hdd-network fs-4"></i>
                        </div>
                    </div>
                    <div
                        class="mt-4 pt-3 border-top border-white border-opacity-10 d-flex align-items-center small text-muted">
                        <i class="bi bi-cpu me-2"></i> <span>System Healthy</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Sync Logs Table -->
            <div class="col-lg-8">
                <div class="glass-card h-100 animate-up delay-1">
                    <div
                        class="p-4 border-bottom border-white border-opacity-10 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-list-ul me-2"></i>Recent Sync Logs</h5>
                        <button class="btn btn-sm btn-outline-secondary rounded-pill px-3">View All</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-custom w-100">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>Server Name</th>
                                    <th>Version</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($dbLogs)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-5">No sync activity recorded yet.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($dbLogs as $log): ?>
                                        <tr>
                                            <td class="text-nowrap text-muted small"><i
                                                    class="bi bi-clock me-2"></i><?php echo date('H:i, d M', strtotime($log['created_at'])); ?>
                                            </td>
                                            <td class="fw-medium text-warning">
                                                <?php echo htmlspecialchars($log['server_name']); ?></td>
                                            <td>
                                                <span
                                                    class="badge rounded-pill bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3">
                                                    <?php echo htmlspecialchars($log['version']); ?>
                                                </span>
                                            </td>
                                            <td class="text-capitalize small"><?php echo htmlspecialchars($log['sync_type']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- File History -->
            <div class="col-lg-4">
                <div class="glass-card h-100 animate-up delay-2">
                    <div class="p-4 border-bottom border-white border-opacity-10">
                        <h5 class="mb-0 fw-semibold"><i class="bi bi-clock-history me-2"></i>Latest Snapshots</h5>
                    </div>
                    <div class="p-4">
                        <div class="timeline">
                            <?php if (empty($versions)): ?>
                                <p class="text-center text-muted py-4">No snapshots found.</p>
                            <?php else: ?>
                                <ul class="list-unstyled mb-0">
                                    <?php foreach (array_slice($versions, 0, 7) as $ver): ?>
                                        <li class="d-flex align-items-center mb-3">
                                            <div class="me-3 d-flex flex-column align-items-center">
                                                <div
                                                    class="rounded-circle bg-success bg-opacity-25 p-1 border border-success border-opacity-50">
                                                    <div class="bg-success rounded-circle" style="width: 8px; height: 8px;">
                                                    </div>
                                                </div>
                                                <!-- Line could go here -->
                                            </div>
                                            <div class="flex-grow-1 p-2 rounded" style="background: rgba(255,255,255,0.03);">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-medium text-white"><?php echo $ver; ?></span>
                                                    <i class="bi bi-check2 text-success"></i>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
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

        document.getElementById('sidebarToggle').addEventListener('click', function () {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');

            // Toggle logo
            if (sidebar.classList.contains('collapsed')) {
                logoText.style.display = 'none';
                logoSmall.style.display = 'block';
            } else {
                logoText.style.display = 'block';
                logoSmall.style.display = 'none';
            }
        });

        // Mobile check
        if (window.innerWidth < 768) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
            logoText.style.display = 'none';
            logoSmall.style.display = 'block';
        }
    </script>
</body>

</html>