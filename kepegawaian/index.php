<?php
/**
 * S.A.P KEPEGAWAIAN - Admin Portal (Dedicated Version)
 * Managed by Antigravity AI
 */

include "../dbconn.php";
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Check
$lv = $_SESSION['level'] ?? '';
if (empty($lv)) {
    header("Location: ../login.php");
    exit;
}
if ($lv == '4') {
    header("Location: index_guru.php");
    exit;
}

$nuser = $_SESSION['nama'] ?? 'Admin';

// Determine current page
$pages = [
    'dashboard' => 'dashboard.php',
    'data_pegawai' => 'pegawai.php',
    'import_data_pegawai' => 'import_pegawai.php',
    'data_pensiun' => 'datapensiun.php',
    'riwayat_monitor' => 'riwayat_monitor.php'
];

$current_page = 'dashboard';
foreach ($pages as $key => $file) {
    if (isset($_GET[$key])) {
        $current_page = $key;
        break;
    }
}

$page_to_include = $pages[$current_page] ?? 'dashboard.php';

// Fallback
if (!file_exists($page_to_include)) {
    $page_to_include = 'dashboard.php';
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Admin Kepegawaian | SMPN 171</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f1f5f9;
            overflow-x: hidden;
        }

        /* 1. NAVBAR STYLE */
        .navbar {
            height: 60px;
            background-color: #1e293b !important;
            z-index: 1050;
        }

        .navbar-brand-text {
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        /* 2. SIDEBAR STYLE */
        #sidebar-wrapper {
            width: 260px;
            position: fixed;
            top: 60px;
            bottom: 0;
            left: 0;
            margin-left: -260px;
            /* Default hidden on Mobile */
            background-color: #334155;
            transition: margin 0.25s ease-out;
            z-index: 1000;
            overflow-y: auto;
            border-right: 1px solid #e2e8f0;
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
            color: #cbd5e1;
            border: none;
            padding: 10px 24px;
            /* Slightly tighter */
            font-size: 0.9rem;
            transition: all 0.2s;
            display: flex;
            align-items: center;
        }

        .list-group-item:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.05) !important;
        }

        .list-group-item.active {
            color: #fff;
            background: #0284c7 !important;
            font-weight: 600;
        }

        .list-group-item i {
            width: 28px;
            font-size: 1.1rem;
        }

        .sidebar-heading {
            padding: 20px 24px 8px 24px;
            font-size: 0.7rem;
            font-weight: 800;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* 3. MAIN CONTENT STYLE */
        #page-content-wrapper {
            width: 100%;
            padding: 20px;
            margin-top: 60px;
            margin-left: 0;
            transition: all 0.25s ease-out;
        }

        /* 4. TOGGLE LOGIC (Desktop vs Mobile) */

        /* Desktop Mode (Layar Lebar) */
        @media (min-width: 769px) {
            #sidebar-wrapper {
                margin-left: 0;
                /* Visible by default */
            }

            #page-content-wrapper {
                margin-left: 260px;
            }

            /* Hide Sidebar on Desktop Toggle */
            body.toggled #sidebar-wrapper {
                margin-left: -260px;
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
    <nav class="navbar navbar-expand navbar-dark fixed-top shadow-sm">
        <div class="container-fluid px-3">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-white p-0 me-3" id="sidebarToggle"><i class="fas fa-bars"></i></button>
                <span class="navbar-brand-text text-uppercase">Admin Kepegawaian</span>
            </div>
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item d-none d-lg-block me-3">
                    <div class="clock-wrapper"><i class="far fa-clock me-1"></i> <span id="realtime-clock"></span></div>
                </li>
                <li class="nav-item">
                    <span class="text-white text-white">Halo,
                        <strong><?php echo htmlspecialchars($nuser); ?></strong></span>
                <li class="nav-item">
                    <a href="logout.php" class="nav-link btn btn-danger btn-sm text-white px-3 ms-3">Logout
                        <i class="fa-solid fa-right-from-bracket ms-2"></i></a>
                </li>
                </li>
            </ul>
        </div>
    </nav>

    <div class="d-flex" id="wrapper">
        <div id="sidebar-wrapper">
            <div class="sidebar-heading">Data Master</div>
            <div class="list-group list-group-flush">
                <a href="?dashboard"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="?data_pegawai"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'data_pegawai') ? 'active' : ''; ?>">
                    <i class="fas fa-users"></i> Data Pegawai
                </a>
                <a href="?import_data_pegawai"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'import_data_pegawai') ? 'active' : ''; ?>">
                    <i class="fas fa-upload"></i> Import Data
                </a>
                <a href="?data_pensiun"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'data_pensiun') ? 'active' : ''; ?>">
                    <i class="fas fa-user-clock"></i> Data Pensiun
                </a>
                <a href="#" id="menuMonitoring" class="list-group-item list-group-item-action">
                    <i class="fas fa-file-invoice"></i> Monitoring Data Pegawai
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <div class="container-fluid">
                <?php include $page_to_include; ?>
            </div>
        </div>
    </div>

    <!-- === MODAL: SELEKSI MONITORING === -->
    <div class="modal fade" id="modalMonitorSelect" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fas fa-file-medical me-2 text-primary"></i>Monitoring Data
                        Pegawai
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">Silakan pilih nama pegawai untuk memantau kelengkapan Data
                        Pegawai.
                    </p>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Cari Pegawai</label>
                        <select id="selectMonitorPegawai" class="form-select" style="width: 100%;">
                            <option value="">-- Pilih Pegawai --</option>
                        </select>
                    </div>
                    <div class="text-end pt-3">
                        <button type="button" id="btnGoToMonitor"
                            class="btn btn-primary btn-rounded px-4 w-100 py-2 shadow-sm" disabled>
                            Buka Monitoring <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/toastin.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $("#sidebarToggle").click(function (e) { e.preventDefault(); $("body").toggleClass("toggled"); });
        function updateClock() {
            const now = new Date();
            $('#realtime-clock').text(now.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'medium' }));
        }
        setInterval(updateClock, 1000); updateClock();

        // Monitoring Logic
        const modalMonitor = new bootstrap.Modal(document.getElementById('modalMonitorSelect'));

        // Initialize Select2
        $('#selectMonitorPegawai').select2({
            theme: 'bootstrap-5',
            dropdownParent: $('#modalMonitorSelect'),
            placeholder: 'Ketik nama pegawai...'
        });

        $('#menuMonitoring').click(function (e) {
            e.preventDefault();
            modalMonitor.show();
            loadMonitorOptions();
        });

        function loadMonitorOptions() {
            $.get('proses_pegawai.php', { action: 'listPegawai' }, function (res) {
                if (res.status === 'success') {
                    let options = '<option value="">-- Pilih Pegawai --</option>';
                    res.data.forEach(p => {
                        options += `<option value="${p.id}">${p.nm_pegawai} - ${p.nip || p.nrk || ''}</option>`;
                    });
                    $('#selectMonitorPegawai').html(options).trigger('change');
                }
            });
        }

        $('#selectMonitorPegawai').on('change', function () {
            const val = $(this).val();
            if (val) {
                $('#btnGoToMonitor').prop('disabled', false);
            } else {
                $('#btnGoToMonitor').prop('disabled', true);
            }
        });

        $('#btnGoToMonitor').click(function () {
            const id = $('#selectMonitorPegawai').val();
            if (id) {
                window.location.href = `?riwayat_monitor&id=${id}`;
            }
        });
    </script>
</body>

</html>