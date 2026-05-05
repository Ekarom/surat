<?php
/**
 * S.A.P KEPEGAWAIAN - Guru Portal (Specialized Version)
 * Managed by Antigravity AI
 */

include "../dbconn.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Ensure skradm is synced for global compatibility
if (isset($_SESSION['userid']) && !isset($_SESSION['skradm'])) {
    $_SESSION['skradm'] = $_SESSION['userid'];
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
    'dashboard' => 'dashboard_ptk.php',
    'profil' => 'profil_saya.php',
    'isi_data' => 'pengisian_data.php',
    'data_saya' => 'data_saya.php'
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
    <title>Portal PTK -SMP NEGERI 171</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Stylesheets -->
    <link rel="stylesheet"
        href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../plugins/css/bootstrap-extended.min.css">
    <link rel="stylesheet" href="../plugins/css/colors.min.css">
    <link rel="stylesheet" href="../plugins/css/palette-gradient.min.css">

    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8f9fa;
            overflow-x: hidden;
        }

        /* 1. NAVBAR STYLE */
        .navbar {
            min-height: 56px;
            z-index: 1050;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        @media (max-width: 991px) {
            .navbar-collapse {
                background-color: #000000ff;
                margin: 0 -1rem;
                padding: 1rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            }
        }

        .navbar-brand-text {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        /* 2. SIDEBAR STYLE */
        #sidebar-wrapper {
            width: 250px;
            position: fixed;
            top: 56px;
            bottom: 0;
            left: 0;
            margin-left: -250px;
            /* Default hidden on Mobile */
            background-color: #343a40;
            transition: margin 0.25s ease-out;
            z-index: 1000;
            overflow-y: auto;
            border-right: 1px solid #dee2e6;
            -webkit-overflow-scrolling: touch;
        }

        /* Custom Scrollbar for Sidebar */
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
        .list-group-item {
            background: transparent !important;
            color: #ccc;
            border: none;
            padding: 8px 20px;
            /* Tighter padding */
            font-size: 0.9rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .list-group-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1) !important;
        }

        .list-group-item.active {
            color: #fff;
            background: #0d6efd !important;
            font-weight: bold;
        }

        .list-group-item i {
            width: 28px;
            font-size: 1.1rem;
        }

        .sidebar-heading {
            padding: 5px 20px;
            font-size: 0.75rem;
            font-weight: bold;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 5px;
        }

        /* 3. MAIN CONTENT STYLE */
        #page-content-wrapper {
            width: 100%;
            padding: 20px;
            margin-top: 56px;
            margin-left: 0;
            transition: all 0.25s ease-out;
        }

        /* 4. TOGGLE LOGIC (Desktop vs Mobile) */

        /* Desktop Mode (Layar Lebar) */
        @media (min-width: 768px) {
            #sidebar-wrapper {
                margin-left: 0;
                /* Visible by default */
            }

            #page-content-wrapper {
                margin-left: 250px;
            }

            /* Hide Sidebar on Desktop Toggle */
            body.toggled #sidebar-wrapper {
                margin-left: -250px;
            }

            body.toggled #page-content-wrapper {
                margin-left: 0;
            }
        }

        /* Mobile Mode (Layar Kecil) */
        @media (max-width: 768px) {

            /* Show Sidebar on Mobile Toggle */
            body.toggled #sidebar-wrapper {
                margin-left: 0;
            }

            /* Overlay effect on Content */
            body.toggled #page-content-wrapper {
                opacity: 0.5;
                pointer-events: none;
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
    <nav class="navbar navbar-expand-lg navbar-dark border-bottom fixed-top shadow-sm">
        <div class="container-fluid px-3">
            <button class="btn btn-outline-light btn-sm me-3" id="sidebarToggle">
                <i class="las la-bars"></i>
            </button>

            <a class="navbar-brand fw-bold" href="#">Portal PTK | SMPN 171</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarPTKContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarPTKContent">
                <ul class="navbar-nav ms-auto align-items-center mt-2 mt-lg-0">
                    <li class="nav-item d-none d-lg-block me-3">
                        <div class="clock-wrapper"><i class="lar la-clock me-1"></i> <span id="realtime-clock"></span>
                        </div>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link text-white">Halo,
                            <strong><?php echo htmlspecialchars($nuser ?? ''); ?></strong></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn btn-outline-danger btn-sm text-white px-3 ms-lg-2 my-2 my-lg-0"
                            href="./logout.php">
                            Logout <i class="las la-sign-out-alt ms-1"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="d-flex" id="wrapper">
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">Menu</div>
            <div class="list-group list-group-flush py-2">
                <a href="?dashboard"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="las la-chart-line"></i> Dashboard
                </a>
                <a href="?profil"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'profil') ? 'active' : ''; ?>">
                    <i class="las la-id-badge"></i> Profil Saya
                </a>
                <a href="?isi_data"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'isi_data') ? 'active' : ''; ?>">
                    <i class="las la-file-signature"></i> Pengisian Data
                </a>
                <a href="?data_saya"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'data_saya') ? 'active' : ''; ?>">
                    <i class="las la-address-card"></i> Data Saya
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