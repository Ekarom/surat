<?php
include "../dbconn.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check (Contoh: Hanya level 1 dan 2 yang bisa akses)
$lv = $_SESSION['level'] ?? '';


$current_page = $_GET['page'] ?? 'dashboard';
$nuser = $_SESSION['nama'] ?? 'User';

// Path mapping
$page_to_include = 'dashboard.php';
if ($current_page == 'data') {
    $page_to_include = 'pegawai.php';
} else if ($current_page == 'import') {
    $page_to_include = 'import_pegawai.php';
}
$nuser = $data_user['nama'] ?? $_SESSION['nama'] ?? 'User';
$poto_db = $data_user['poto'] ?? '';
$lv = $data_user['level'] ?? $_SESSION['level'] ?? '';

?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>S.A.P KEPEGAWAIAN | <?php echo ucfirst($current_page); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #0f172a;
        }

        .main-sidebar {
            background: #1e293b !important;
            border-right: 1px solid rgba(255, 255, 255, 0.05);
        }

        .brand-link {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
            background: #1e293b !important;
        }

        .nav-sidebar .nav-link {
            border-radius: 10px !important;
            margin: 4px 12px;
            color: #94a3b8 !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-sidebar .nav-link.active {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
        }

        .nav-sidebar .nav-link:hover:not(.active) {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #f8fafc !important;
            transform: translateX(4px);
        }

        .main-header {
            background: rgba(15, 23, 42, 0.8) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        }

        .content-wrapper {
            background-color: #0f172a !important;
        }

        .main-footer {
            background: #0f172a !important;
            border-top: 1px solid rgba(255, 255, 255, 0.05) !important;
            color: #64748b;
        }
        
        .user-panel {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        }

        /* Scrollbar Styling */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #0f172a; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed dark-mode">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark shadow-none">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-none d-sm-inline-block">
                    HOME
                </li>
            </ul>
        </nav>

        <!-- Sidebar -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="index.php" class="brand-link text-center py-3">

                <span class="brand-text font-weight-bold">S.A.P KEPEGAWAIAN</span>
            </a>

            <div class="sidebar">
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="../images/default.png" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?php echo $nuser; ?></a>
                    </div>
                </div>

                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="index.php?page=dashboard"
                                class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=data"
                                class="nav-link <?php echo ($current_page == 'data') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Manajemen Data</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=import"
                                class="nav-link <?php echo ($current_page == 'import') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-file-excel"></i>
                                <p>Import Data (Excel)</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="../exit.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Logout</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content -->
        <?php
        // Set flag agar dashboard.php tahu dia sedang dimuat di dalam layout
        $is_embedded = true;
        include $page_to_include;
        ?>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block"><b>Version</b> 1.0.0</div>
            <strong>Copyright &copy; <?php echo date('Y'); ?> Personnel System.</strong>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../plugins/admin-lte/adminlte.min.js"></script>
</body>

</html>