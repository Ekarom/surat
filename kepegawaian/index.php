<?php
/**
 * S.A.P KEPEGAWAIAN - Core Layout (Simple Sidebar Version)
 * Managed by Antigravity AI
 */

include "../dbconn.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// User Data & Security
$nuser = $_SESSION['nama'] ?? 'User';
$lv = $_SESSION['level'] ?? '';

// Determine current page from URL keys (e.g., ?dashboard instead of ?page=dashboard)
$current_page = 'dashboard';

// Path mapping with whitelist for security
$pages = [
    'dashboard' => 'dashboard.php',
    'data_pegawai' => 'pegawai.php',
    'import_data_pegawai' => 'import_pegawai.php',
    'data_pensiun' => 'datapensiun.php'
];

foreach ($pages as $key => $file) {
    if (isset($_GET[$key])) {
        $current_page = $key;
        break;
    }
}

$page_to_include = $pages[$current_page] ?? 'dashboard.php';

// Fallback if file doesn't exist
if (!file_exists($page_to_include)) {
    $page_to_include = 'dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>S.A.P KEPEGAWAIAN | <?php echo htmlspecialchars(ucfirst($current_page)); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css">


    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            overflow-x: hidden;
            background-color: #f8f9fa;
        }

        /* NAVBAR STYLE */
        .navbar {
            height: 56px;
            z-index: 1050;
            background-color: #1e293b !important;
        }

        .navbar-brand-text {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        /* 1. SIDEBAR STYLE */
        #sidebar-wrapper {
            margin-left: -250px;
            /* Default disembunyikan di Mobile */
            transition: margin 0.25s ease-out;
            background-color: #343a40;
            border-right: 1px solid #dee2e6;
            position: fixed;
            top: 56px;
            /* Tinggi Navbar */
            bottom: 0;
            width: 250px;
            z-index: 1000;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Custom Scrollbar */
        #sidebar-wrapper::-webkit-scrollbar {
            width: 5px;
        }

        #sidebar-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* Menu Link Style */
        #sidebar-wrapper .list-group {
            background-color: transparent !important;
        }

        #sidebar-wrapper .list-group-item {
            border: none;
            background-color: transparent !important;
            color: #ccc;
            padding: 8px 20px;
            /* Diperkecil dari 12px */
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: all 0.2s;
            font-size: 0.9rem;
        }

        #sidebar-wrapper .list-group-item i {
            width: 25px;
            margin-right: 10px;
        }

        #sidebar-wrapper .list-group-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        #sidebar-wrapper .list-group-item.active {
            background-color: #0d6efd;
            color: #fff;
            font-weight: bold;
        }

        .sidebar-heading {
            padding: 15px 20px 5px 20px;
            /* Diperkecil */
            font-size: 0.75rem;
            /* Sedikit diperkecil */
            text-transform: uppercase;
            color: #6c757d;
            font-weight: bold;
            letter-spacing: 1px;
            margin-top: 5px;
            /* Diperkecil */
        }

        /* 2. MAIN CONTENT STYLE */
        #page-content-wrapper {
            width: 100%;
            padding: 20px;
            margin-top: 56px;
            /* Tinggi Navbar */
            transition: all 0.25s ease-out;
        }

        /* 3. LOGIKA TOGGLE (Desktop vs Mobile) */

        /* Di Desktop (Layar Lebar): Sidebar default MUNCUL */
        @media (min-width: 768px) {
            #sidebar-wrapper {
                margin-left: 0;
            }

            #page-content-wrapper {
                margin-left: 250px;
            }

            /* Class khusus saat tombol ditekan di Desktop (Hide) */
            body.toggled #sidebar-wrapper {
                margin-left: -250px;
            }

            body.toggled #page-content-wrapper {
                margin-left: 0;
            }
        }

        /* Di Mobile (Layar Kecil): Sidebar default SEMBUNYI */
        @media (max-width: 768px) {

            /* Class khusus saat tombol ditekan di Mobile (Show) */
            body.toggled #sidebar-wrapper {
                margin-left: 0;
            }

            /* Overlay hitam saat sidebar muncul di HP */
            body.toggled #page-content-wrapper {
                opacity: 0.5;
                /* Efek redup */
                pointer-events: none;
                /* Cegah klik konten belakang */
            }
        }

        .footer {
            background: #fff;
            border-top: 1px solid #dee2e6;
            padding: 15px 20px;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .logout-btn {
            border-radius: 50px;
            padding: 5px 15px !important;
            font-size: 0.85rem;
        }
    </style>
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand navbar-dark fixed-top shadow-sm">
        <div class="container-fluid px-3">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-white p-0 me-3" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="navbar-brand-text d-none d-sm-inline-block">S.A.P KEPEGAWAIAN</span>
            </div>

            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <span class="nav-link text-white small me-3">
                        <span id="date-display" class="opacity-75"></span>
                        <time id="clock" class="fw-bold"></time>
                    </span>
                </li>
                <li class="nav-item">
                    <span class="nav-link text-white small me-2 d-none d-md-inline-block">Halo,
                        <strong><?php echo htmlspecialchars($nuser); ?></strong></span>
                </li>
                <li class="nav-item d-none d-md-inline-block">
                    <a class="nav-link  text-white logout-btn" href="../logout.php">
                        Logout <i class="fa-solid fa-right-from-bracket ms-1"></i>
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">Manajemen Data</div>
            <div class="list-group list-group-flush">
                <a href="?dashboard"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="?data_pegawai"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'data_pegawai') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Data Pegawai
                </a>
                <a href="?import_data_pegawai"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'import_data_pegawai') ? 'active' : ''; ?>">
                    <i class="fas fa-file-excel"></i> Import (Excel)
                </a>
                <a href="?data_pensiun"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'data_pensiun') ? 'active' : ''; ?>">
                    <i class="fas fa-user-clock"></i> Data Pensiun Pegawai
                </a>
            </div>

            <div class="list-group list-group-flush">
                <a href="../logout.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <div class="container-fluid">
                <?php
                if (file_exists($page_to_include)) {
                    include $page_to_include;
                } else {
                    echo "<div class='text-center p-5'>
                            <div class='alert alert-light border rounded-4 shadow-sm p-4'>
                                <i class='fas fa-exclamation-triangle fa-2x text-warning mb-3'></i>
                                <h4>Halaman Tidak Ditemukan</h4>
                                <p class='text-muted'>Modul tidak tersedia.</p>
                                <a href='index.php' class='btn btn-primary btn-sm rounded-pill px-4'>Dashboard</a>
                            </div>
                          </div>";
                }
                ?>
            </div>


        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Custom Sidebar Toggle Logic
        window.addEventListener('DOMContentLoaded', event => {
            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.classList.toggle('toggled');
                });
            }
        });

        $(document).ready(function () {
            // Real-time Clock Functionality
            function updateClock() {
                const now = new Date();
                const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum\'at', 'Sabtu'];

                const dateString = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()} | `;
                const timeString = now.toTimeString().split(' ')[0];

                $('#date-display').text(dateString);
                $('#clock').text(timeString);
            }

            updateClock();
            setInterval(updateClock, 1000);
        });
    </script>
</body>

</html>