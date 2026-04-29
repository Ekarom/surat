<?php
include "../dbconn.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// User Data & Security
$nuser = $_SESSION['nama'] ?? 'User';
$lv = $_SESSION['level'] ?? '';
$current_page = $_GET['page'] ?? 'dashboard';

// Path mapping
$page_to_include = 'dashboard.php';
if ($current_page == 'data') {
    $page_to_include = 'pegawai.php';
} else if ($current_page == 'import') {
    $page_to_include = 'import_pegawai.php';
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>S.A.P KEPEGAWAIAN | <?php echo ucfirst($current_page); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        :root {
            --bg-deep: #f4f6f9;
            --bg-dark: #1e293b;
            --accent-blue: #3b82f6;
            --text-main: #334155;
            --text-muted: #64748b;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-deep);
            color: var(--text-main);
        }

        /* Dark Navbar */
        .main-header {
            background-color: var(--bg-dark) !important;
            border-bottom: none !important;
            padding: 0.5rem 1rem;
        }

        .main-header .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .main-header .nav-link:hover {
            color: #fff !important;
        }

        .navbar-brand-text {
            color: #fff;
            font-weight: 600;
            font-size: 1.1rem;
            margin-left: 10px;
        }

        /* Dark Sidebar */
        .main-sidebar {
            background-color: var(--bg-dark) !important;
            box-shadow: none !important;
        }

        .sidebar-dark-primary .nav-sidebar>.nav-item>.nav-link.active {
            background-color: rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
            box-shadow: none;
        }

        .nav-sidebar .nav-link {
            border-radius: 0 !important;
            margin: 0 !important;
            padding: 10px 20px !important;
            color: #b1b1b1 !important;
        }

        .nav-sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.05) !important;
            color: #fff !important;
        }

        .nav-header {
            background-color: transparent !important;
            color: #6c757d !important;
            font-size: 0.75rem !important;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 1.5rem 1rem 0.5rem 1.5rem !important;
        }

        .content-wrapper {
            background-color: var(--bg-deep) !important;
            padding: 20px;
        }

        /* Card Adjustments */
        .card {
            border: none !important;
            border-radius: 8px !important;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075) !important;
        }

        .main-footer {
            background: #fff !important;
            border-top: 1px solid #dee2e6 !important;
            padding: 1rem !important;
            font-size: 0.85rem;
        }

        /* Navbar Right Info */
        .user-info-nav {
            color: #fff;
            font-size: 0.9rem;
            margin-right: 20px;
        }

        .logout-link {
            color: #fff !important;
            font-weight: 500;
        }

        .logout-link i {
            margin-left: 5px;
        }
    </style>
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-dark shadow-none">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
                <li class="nav-item d-flex align-items-center">
                    <span class="navbar-brand-text">
                        <script type='text/javascript'>
                            var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                            var myDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum&#39;at', 'Sabtu'];
                            var date = new Date();
                            var day = date.getDate();
                            var month = date.getMonth();
                            var thisDay = date.getDay(),
                                thisDay = myDays[thisDay];
                            var yy = date.getYear();
                            var year = (yy < 1000) ? yy + 1900 : yy;
                            document.write(thisDay + ', ' + day + ' ' + months[month] + ' ' + year + ' | ');
                        </script>
                        <time id="clock"></time>
                    </span>
                </li>
                <script>
                    (function () {
                        var clock = document.getElementById('clock');
                        setInterval(function () {
                            var time = new Date().toString().split(' ')[4];
                            clock.innerHTML = time;
                        }, 1000);
                    })();
                </script>
            </ul>

            <ul class="navbar-nav ml-auto align-items-center">
                <li class="nav-item d-none d-sm-inline-block">
                    <span class="user-info-nav">Halo, <?php echo $nuser; ?></span>
                </li>
                <li class="nav-item">
                    <a href="../exit.php" class="nav-link logout-link">
                        Logout <i class="fas fa-sign-out-alt"></i>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Sidebar -->

        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="?" class="brand-link d-flex flex-column align-items-center text-center py-3">
                <span class="brand-text font-weight-bold">S.A.P KEPEGAWAIAN</span>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <li class="nav-item">
                            <a href="index.php?page=dashboard"
                                class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        <li class="nav-header">MANAJEMEN DATA</li>
                        <li class="nav-item">
                            <a href="index.php?page=data"
                                class="nav-link <?php echo ($current_page == 'data') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-users"></i>
                                <p>Data Pegawai</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="index.php?page=import"
                                class="nav-link <?php echo ($current_page == 'import') ? 'active' : ''; ?>">
                                <i class="nav-icon fas fa-file-excel"></i>
                                <p>Import (Excel)</p>
                            </a>
                        </li>

                        <li class="nav-header">SISTEM</li>
                        <li class="nav-item">
                            <a href="../exit.php" class="nav-link">
                                <i class="nav-icon fas fa-sign-out-alt"></i>
                                <p>Keluar Aplikasi</p>
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content -->
        <div class="content-wrapper">
            <?php
            $is_embedded = true;
            include $page_to_include;
            ?>
        </div>

        <footer class="main-footer">
            <div class="float-right d-none d-sm-block"><b>Version</b> 1.0.0</div>
            <strong>Copyright &copy; <?php echo date('Y'); ?> Personnel System.</strong>
        </footer>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../plugins/admin-lte/adminlte.min.js"></script>
</body>

</html>