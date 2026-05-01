<?php
/**
 * S.A.P KEPEGAWAIAN - Guru Portal (Specialized Version)
 * Managed by Antigravity AI
 */

include "../dbconn.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check (Guru Only)
$lv = $_SESSION['level'] ?? '';
if ($lv != '4') {
    header("Location: ../index.php");
    exit;
}

$nuser = $_SESSION['nama'] ?? 'Guru';
$nik = $_SESSION['nik'] ?? '';

// Determine current page
$pages = [
    'dashboard' => 'dashboard_guru.php',
    'profil' => 'profil_saya.php',
    'data_pegawai' => 'pegawai.php',
    'riwayat' => 'riwayat_saya.php'
];

$current_page = 'dashboard';
foreach ($pages as $key => $file) {
    if (isset($_GET[$key])) {
        $current_page = $key;
        break;
    }
}

$page_to_include = $pages[$current_page] ?? 'dashboard_guru.php';

// Fallback
if (!file_exists($page_to_include)) {
    $page_to_include = 'dashboard_guru.php';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Portal Mandiri Guru</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* NAVBAR STYLE */
        .navbar {
            height: 56px;
            background-color: #0f172a !important;
            z-index: 1050;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
            background-color: #1e293b;
            border-right: 1px solid rgba(255, 255, 255, 0.1);
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
            color: #94a3b8;
            padding: 8px 20px;
            /* Diperkecil dari 12px */
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        #sidebar-wrapper .list-group-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        #sidebar-wrapper .list-group-item.active {
            background-color: #4f46e5;
            color: #fff;
            font-weight: bold;
        }

        #sidebar-wrapper .list-group-item i {
            width: 25px;
            margin-right: 10px;
            font-size: 1rem;
        }

        .sidebar-heading {
            padding: 15px 20px 5px 20px;
            /* Diperkecil */
            font-size: 0.7rem;
            /* Sedikit diperkecil */
            text-transform: uppercase;
            color: #64748b;
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

        .clock-wrapper {
            color: #94a3b8;
            font-size: 0.85rem;
        }

        #toastiin-container.top-right {
            top: 20px;
            right: 20px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand navbar-dark fixed-top shadow-sm">
        <div class="container-fluid px-3">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-white p-0 me-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <span class="navbar-brand-text">PORTAL GURU <span class="fw-light opacity-50 ms-2">| SMPN
                        171</span></span>
            </div>
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item d-none d-lg-block me-3">
                    <div class="clock-wrapper"><i class="far fa-clock me-1"></i> <span id="realtime-clock"></span></div>
                </li>
                <li class="nav-item">
                    <span class="text-white text-white">Halo,
                        <strong><?php echo htmlspecialchars($nuser); ?></strong></span>
                <li class="nav-item">
                    <a href="./logout.php" class="nav-link btn btn-danger btn-sm text-white px-3 ms-3">Logout
                        <i class="fa-solid fa-right-from-bracket ms-2"></i></a>
                </li>
                </li>
            </ul>
        </div>
    </nav>

    <div class="d-flex" id="wrapper">
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">Menu</div>
            <div class="list-group list-group-flush">
                <a href="?dashboard"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="?profil"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'profil') ? 'active' : ''; ?>">
                    <i class="fas fa-user-circle"></i> Profil Saya
                </a>
                <a href="?riwayat"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'riwayat') ? 'active' : ''; ?>">
                    <i class="fas fa-history"></i> Riwayat Kepegawaian
                </a>
            </div>
            <div class="list-group list-group-flush mt-auto pb-4">

            </div>
        </div>

        <div id="page-content-wrapper">
            <div class="container-fluid">
                <?php include $page_to_include; ?>
            </div>
        </div>
    </div>

    <script src="../js/toastin.js"></script>
    <script>
        $("#sidebarToggle").click(function (e) { e.preventDefault(); $("body").toggleClass("toggled"); });
        function updateClock() {
            const now = new Date();
            $('#realtime-clock').text(now.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'medium' }));
        }
        setInterval(updateClock, 1000); updateClock();
    </script>
</body>

</html>