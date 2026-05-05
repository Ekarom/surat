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
    header("Location: index_ptk.php");
    exit;
}

$nuser = $_SESSION['nama'] ?? 'Admin';

// Determine current page
$pages = [
    'dashboard' => 'dashboard.php',
    'data_pegawai' => 'data_pegawai.php',
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
    <link rel="stylesheet"
        href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

    <link rel="stylesheet" href="../plugins/css/bootstrap-extended.min.css">
    <link rel="stylesheet" href="../plugins/css/colors.min.css">
    <link rel="stylesheet" href="../plugins/css/palette-gradient.min.css">
    <!-- === EXTERNAL ASSETS === -->
    <link rel="stylesheet" href="../plugins/css/datatables.min.css">

    <!-- Core Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>

    <style>
        :root {
            --sap-primary: #4f46e5;
            --sap-primary-light: rgba(79, 70, 229, 0.1);
            --sap-primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --sap-secondary: #64748b;
            --sap-success: #10b981;
            --sap-info: #0ea5e9;
            --sap-danger: #ef4444;
            --sap-warning: #f59e0b;
            --sap-dark: #1e293b;
        }

        body {
            overflow-x: hidden;
            background-color: #f8f9fa;
            font-family: 'Outfit', sans-serif;
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
        }

        #sidebar-wrapper .list-group-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        #navbar {
            background: var(--sap-primary-gradient);
            color: #fff;
            border-bottom: none !important;
        }


        #sidebar-wrapper .list-group-item.active {
            background-color: var(--sap-primary);
            color: #fff;
            font-weight: bold;
        }

        .sidebar-heading {
            padding: 5px 20px;
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

        /* 3. MAIN CONTENT STYLE */
        #page-content-wrapper {
            width: 100%;
            padding: 0;
            margin-top: 56px;
            transition: all 0.3s ease;
        }

        /* 4. TOGGLE LOGIC (REFINED) */
        @media (min-width: 992px) {
            #sidebar-wrapper {
                margin-left: 0;
            }

            #page-content-wrapper {
                padding-left: 250px;
            }

            body.toggled #sidebar-wrapper {
                margin-left: -250px;
            }

            body.toggled #page-content-wrapper {
                padding-left: 0;
            }
        }

        @media (max-width: 991px) {
            #sidebar-wrapper {
                margin-left: -250px;
            }

            body.toggled #sidebar-wrapper {
                margin-left: 0;
                box-shadow: 10px 0 30px rgba(0, 0, 0, 0.2);
            }

            body.toggled #page-content-wrapper {
                opacity: 0.4;
                pointer-events: none;
            }
        }

        .clock-wrapper {
            color: #f8f8f8ff;
            font-size: 0.95rem;
        }

        #toastiin-container.top-right {
            top: 20px;
            right: 20px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom fixed-top shadow-sm">
        <div class="container-fluid px-3">
            <button class="btn btn-outline-light btn-sm me-3" id="sidebarToggle">
                <i class="las la-bars"></i>
            </button>

            <a class="navbar-brand fw-bold" href="#">Admin Kepegawaian</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarAdminContent">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarAdminContent">
                <ul class="navbar-nav ms-auto align-items-center mt-2 mt-lg-0">
                    <li class="nav-item d-none d-lg-block me-3">
                        <div class="clock-wrapper"><i class="las la-clock me-1"></i> <span id="realtime-clock"></span>
                        </div>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link text-white">Halo,
                            <strong><?php echo htmlspecialchars($nuser); ?></strong></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-danger btn-sm text-white px-3 ms-lg-2 my-2 my-lg-0"
                            href="logout.php">
                            Logout <i class="las la-sign-out-alt ms-1"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <div id="sidebar-wrapper">
            <div class="list-group list-group-flush py-2">
                <a href="?dashboard"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                    <i class="las la-chart-line"></i> Dashboard
                </a>
            </div>

            <div class="sidebar-heading">Manajemen Pegawai</div>
            <div class="list-group list-group-flush">
                <a href="?data_pegawai"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'data_pegawai') ? 'active' : ''; ?>">
                    <i class="las la-users"></i> Data Pegawai
                </a>
                <a href="?data_pensiun"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'data_pensiun') ? 'active' : ''; ?>">
                    <i class="las la-user-clock"></i> Data Pensiun
                </a>
                <a href="#" id="menuMonitoring" class="list-group-item list-group-item-action">
                    <i class="las la-file-invoice"></i> Monitoring Data Pegawai
                </a>
            </div>

            <div class="sidebar-heading">Sistem & Data</div>
            <div class="list-group list-group-flush">
                <a href="?data_pegawai&import"
                    class="list-group-item list-group-item-action <?php echo ($current_page == 'import_data_pegawai') ? 'active' : ''; ?>">
                    <i class="las la-upload"></i> Upload Data Pegawai
                </a>
            </div>
        </div>
        <!-- /#sidebar-wrapper -->
    </div>

    <div id="page-content-wrapper">
        <div class="container-fluid content">
            <?php include $page_to_include; ?>
        </div>
    </div>
    </div>

    <!-- === MODAL: SELEKSI MONITORING === -->
    <div class="modal fade" id="modalMonitorSelect" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="fw-bold">Monitoring Data
                        Pegawai
                    </h5>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Silakan pilih nama pegawai untuk memantau kelengkapan Data
                        Pegawai.
                    </p>
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-muted text-uppercase">Cari Pegawai</label>
                        <select id="selectMonitorPegawai" class="form-select" style="width: 100%;">
                            <option value="">-- Pilih Pegawai --</option>
                        </select>
                    </div>
                    <div class="text-end pt-1">
                        <button type="button" id="btnGoToMonitor"
                            class="btn btn-primary btn-rounded px-4 w-100 py-2 shadow-sm" disabled>
                            Buka Monitoring <i class="las la-arrow-right ms-2"></i>
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