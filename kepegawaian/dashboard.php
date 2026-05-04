<?php
/**
 * Dashboard Kepegawaian - Optimized & Refined
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

if (!isset($conn) || !$conn) {
    echo "<div class='alert alert-danger shadow-sm rounded-3'>Koneksi database tidak tersedia.</div>";
    return;
}

/**
 * 1. DATA FETCHING
 */

// Summary Statistics
$stats_query = "SELECT 
    COUNT(*) as total,
    IFNULL(SUM(CASE WHEN status = '1' THEN 1 ELSE 0 END), 0) as aktif,
    IFNULL(SUM(CASE WHEN status_pegawai = 'PNS' THEN 1 ELSE 0 END), 0) as pns,
    IFNULL(SUM(CASE WHEN status_pegawai = 'PPPK' THEN 1 ELSE 0 END), 0) as pppk,
    IFNULL(SUM(CASE WHEN status_pegawai = 'PPPK PW' THEN 1 ELSE 0 END), 0) as pppk_pw,
    IFNULL(SUM(CASE WHEN status_pegawai = 'HONORER' THEN 1 ELSE 0 END), 0) as honorer
FROM pegawai";

$stats_res = $conn->query($stats_query);
$stats = $stats_res ? $stats_res->fetch_assoc() : ['total' => 0, 'aktif' => 0, 'pns' => 0, 'pppk' => 0, 'pppk_pw' => 0, 'honorer' => 0];

// User Online Logic
if (isset($_SESSION['id'])) {
    $uid = $_SESSION['id'];
    $conn->query("UPDATE pegawai SET last_activity = NOW() WHERE id = $uid");
}
$online_res = $conn->query("SELECT id, nm_pegawai, last_activity, status_pegawai FROM pegawai WHERE last_activity > DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY last_activity DESC");
$online_users = [];
if ($online_res) {
    while ($row = $online_res->fetch_assoc()) {
        $online_users[] = $row;
    }
}
$online_count = count($online_users);

$info_boxes = [
    ['title' => 'Total Pegawai', 'value' => $stats['total'], 'unit' => 'Orang', 'icon' => 'fa-users', 'color' => 'bg-primary'],
    ['title' => 'Pegawai Aktif', 'value' => $stats['aktif'], 'unit' => '', 'icon' => 'fa-user-check', 'color' => 'bg-success'],
    ['title' => 'Jumlah PNS', 'value' => $stats['pns'], 'unit' => '', 'icon' => 'fa-id-card', 'color' => 'bg-info'],
    ['title' => 'Jumlah PPPK', 'value' => $stats['pppk'], 'unit' => '', 'icon' => 'fa-id-card', 'color' => 'bg-info'],
    ['title' => 'Jumlah Honorer', 'value' => $stats['honorer'], 'unit' => '', 'icon' => 'fa-user-clock', 'color' => 'bg-warning'],
];

// Gender Distribution
$genders = ['L' => 0, 'P' => 0];
$gender_res = $conn->query("SELECT jenis_kelamin, COUNT(*) as count FROM pegawai GROUP BY jenis_kelamin");
if ($gender_res) {
    while ($row = $gender_res->fetch_assoc()) {
        $val = strtoupper(trim($row['jenis_kelamin']));
        if ($val === 'L' || strpos($val, 'LAKI') === 0)
            $genders['L'] += (int) $row['count'];
        elseif ($val === 'P' || strpos($val, 'PEREMPUAN') === 0 || strpos($val, 'WANITA') === 0)
            $genders['P'] += (int) $row['count'];
    }
}

// Unit Kerja Data
$unit_data = $conn->query("SELECT unit_kerja, COUNT(*) as count FROM pegawai GROUP BY unit_kerja ORDER BY count ASC LIMIT 5");

// Employment Status Chart Data
$status_labels = [];
$status_values = [];
$status_res = $conn->query("SELECT status_pegawai, COUNT(*) as count FROM pegawai GROUP BY status_pegawai");
if ($status_res) {
    while ($row = $status_res->fetch_assoc()) {
        $status_labels[] = $row['status_pegawai'] ?: 'Lainnya';
        $status_values[] = (int) $row['count'];
    }
}

/**
 * 2. RETIREMENT DATA (Dashboard Summary)
 */
function getRetirementSummary($tglLahir, $jabatan)
{
    if (!$tglLahir || $tglLahir == '0000-00-00')
        return null;
    $bup = 60;
    $jabatanUpper = strtoupper($jabatan);
    if (strpos($jabatanUpper, 'UTAMA') !== false || strpos($jabatanUpper, 'PROFESOR') !== false)
        $bup = 65;
    $tglLahirObj = new DateTime($tglLahir);
    $pensiunDate = clone $tglLahirObj;
    $pensiunDate->modify("+$bup years");
    $pensiunDate->modify("first day of next month");
    $today = new DateTime();
    $interval = $today->diff($pensiunDate);
    $isRetired = ($today > $pensiunDate);
    return [
        'tmt' => $pensiunDate->format('Y-m-d'),
        'tmt_display' => $pensiunDate->format('d-m-Y'),
        'sisa_th' => $isRetired ? -1 : $interval->y,
        'sisa_bln' => $isRetired ? -1 : $interval->m,
        'isRetired' => $isRetired
    ];
}

$pensiun_list = [];
$res_pensiun = $conn->query("SELECT id, nm_pegawai, nip, tgl_lahir, jabatan FROM pegawai WHERE status = '1'");
if ($res_pensiun) {
    while ($row = $res_pensiun->fetch_assoc()) {
        $ret = getRetirementSummary($row['tgl_lahir'], $row['jabatan']);
        if ($ret && !$ret['isRetired']) {
            $row['tmt_pensiun'] = $ret['tmt'];
            $row['tmt_pensiun_display'] = $ret['tmt_display'];
            $row['sisa_th'] = $ret['sisa_th'];
            $row['sisa_bln'] = $ret['sisa_bln'];
            $pensiun_list[] = $row;
        }
    }
    usort($pensiun_list, function ($a, $b) {
        return strcmp($a['tmt_pensiun'], $b['tmt_pensiun']);
    });
    $pensiun_list = array_slice($pensiun_list, 0, 5);
}
?>

<div class="py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard</h2>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <!-- Statistics Grid -->
    <div class="row g-3">
        <?php foreach ($info_boxes as $box): ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm h-100 stat-card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center mb-2">
                            <div class="icon-shape <?php echo $box['color']; ?> text-white rounded-3 me-2">
                                <i class="fas <?php echo $box['icon']; ?> fa-xs"></i>
                            </div>
                            <span
                                class="text-muted fw-bold small text-uppercase letter-spacing-1"><?php echo $box['title']; ?></span>
                        </div>
                        <div class="h4 fw-bold mb-0">
                            <?php echo number_format($box['value']); ?>
                            <?php if ($box['unit']): ?><span
                                    class="fs-6 text-muted fw-normal ms-1"><?php echo $box['unit']; ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts & Distributions -->
    <div class="row mt-4 g-4">

        <!-- Gender Distribution Chart -->
        <div class="col-lg-3">
            <div class="card border-0 shadow-sm h-100 text-center">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-venus-mars me-2 text-danger"></i> Komposisi Gender</h6>
                </div>
                <div class="card-body">
                    <div style="height: 180px; position: relative;">
                        <canvas id="genderChart"></canvas>
                    </div>
                    <div class="d-flex justify-content-around mt-3 small fw-bold">
                        <div><i class="fas fa-circle text-primary me-1"></i> L: <?php echo $genders['L']; ?></div>
                        <div><i class="fas fa-circle text-danger me-1"></i> P: <?php echo $genders['P']; ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employment Status Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-chart-bar me-2 text-info"></i> Status Pegawai</h6>
                </div>
                <div class="card-body">
                    <div style="height: 220px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Online Panel -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <h6 class="fw-bold mb-0 me-2">User Online</h6>
                        <span class="badge rounded-circle bg-success-soft text-success px-2 py-1" style="font-size: 0.7rem;"><?php echo $online_count; ?></span>
                    </div>
                    <div class="online-dot-pulse"></div>
                </div>
                <div class="card-body p-0">
                    <div class="online-user-list">
                        <?php if (!empty($online_users)): ?>
                            <?php foreach ($online_users as $u): 
                                $is_admin = (stripos($u['status_pegawai'], 'ADMIN') !== false || stripos($u['jabatan'], 'ADMIN') !== false);
                                $role_label = $is_admin ? 'ADMIN' : 'GURU';
                                $last_time = date('H:i', strtotime($u['last_activity']));
                            ?>
                                <div class="online-user-item p-3 d-flex justify-content-between align-items-center border-bottom">
                                    <div>
                                        <div class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($u['nm_pegawai']); ?></div>
                                        <span class="badge bg-success text-white extra-small px-2 py-1"><?php echo $role_label; ?></span>
                                    </div>
                                    <div class="text-end">
                                        <div class="small text-success fw-bold d-flex align-items-center justify-content-end">
                                            <span class="online-dot me-1"></span> Online
                                        </div>
                                        <div class="extra-small text-muted"><?php echo $last_time; ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center py-5 opacity-50">
                                <i class="fas fa-user-slash fa-3x mb-2"></i>
                                <p class="small">Tidak ada pengguna aktif</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 opacity-50" disabled>Prev</button>
                    <span class="extra-small text-muted">Hal 1 / 1</span>
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 opacity-50" disabled>Next</button>
                </div>
            </div>
        </div>
    </div>



    <!-- Retirement Table -->
    <div class="mt-4 pb-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-user-clock me-2 text-danger"></i> Estimasi Pensiun Terdekat
                </h6>
                <a href="?datapensiun" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">Detail Pensiun <i
                        class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center px-4" width="70">#</th>
                                <th>Nama Pegawai</th>
                                <th>Jabatan</th>
                                <th>TMT Pensiun</th>
                                <th class="text-center px-4">Sisa Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($pensiun_list)):
                                $no = 1;
                                foreach ($pensiun_list as $row):
                                    $sisa = "";
                                    if ($row['sisa_th'] > 0)
                                        $sisa .= $row['sisa_th'] . " Th ";
                                    if ($row['sisa_bln'] > 0)
                                        $sisa .= $row['sisa_bln'] . " Bln";
                                    if ($sisa == "")
                                        $sisa = "Bulan Ini";

                                    $is_near = ($row['sisa_th'] == 0);
                                    ?>
                                    <tr>
                                        <td class="text-center px-4 text-muted"><?php echo $no++; ?></td>
                                        <td>
                                            <div class="fw-bold"><?php echo htmlspecialchars($row['nm_pegawai']); ?></div>
                                            <div class="extra-small text-muted"><?php echo $row['nip'] ?: '-'; ?></div>
                                        </td>
                                        <td><span
                                                class="badge badge-soft-blue"><?php echo htmlspecialchars($row['jabatan']); ?></span>
                                        </td>
                                        <td class="fw-bold text-primary"><?php echo $row['tmt_pensiun_display']; ?></td>
                                        <td class="text-center px-4">
                                            <span
                                                class="badge <?php echo $is_near ? 'bg-danger-soft text-danger' : 'bg-warning-soft text-warning'; ?> rounded-pill px-3 fw-bold">
                                                <?php echo $sisa; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Data pensiun tidak tersedia.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const commonOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        };

        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [<?php echo $genders['L']; ?>, <?php echo $genders['P']; ?>],
                    backgroundColor: ['#3b82f6', '#f43f5e'],
                    borderWidth: 0,
                    hoverOffset: 10
                }]
            },
            options: { ...commonOptions, cutout: '70%' }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    label: 'Org',
                    data: <?php echo json_encode($status_values); ?>,
                    backgroundColor: '#0ea5e9',
                    borderRadius: 5
                }]
            },
            options: {
                ...commonOptions,
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { font: { size: 10 } } },
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } }
                }
            }
        });
    });
</script>

<style>
    .letter-spacing-1 {
        letter-spacing: 1px;
    }

    .icon-shape {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card {
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-3px);
    }

    .online-pulse-icon {
        animation: pulse-red 2s infinite;
    }

    @keyframes pulse-red {
        0% { opacity: 1; }
        50% { opacity: 0.4; }
        100% { opacity: 1; }
    }

    .online-dot-pulse {
        width: 10px;
        height: 10px;
        background-color: #10b981;
        border-radius: 50%;
        box-shadow: 0 0 0 rgba(16, 185, 129, 0.4);
        animation: pulse-green 2s infinite;
    }

    @keyframes pulse-green {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
        70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    .online-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
    }

    .online-user-item:last-child {
        border-bottom: none !important;
    }

    .online-user-list {
        max-height: 250px;
        overflow-y: auto;
    }

    .badge-soft-blue {
        background-color: #eff6ff;
        color: #3b82f6;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 5px 10px;
        border-radius: 6px;
    }

    .bg-success-soft {
        background-color: #f0fdf4;
    }

    .bg-secondary-soft {
        background-color: #f8fafc;
    }

    .bg-danger-soft {
        background-color: #fef2f2;
    }

    .bg-warning-soft {
        background-color: #fffbeb;
    }

    .table thead th {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        border-bottom: 1px solid #f1f5f9;
    }

    .table tbody td {
        font-size: 0.9rem;
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #f8fafc;
    }

    .progress {
        background-color: #f1f5f9;
        overflow: visible;
    }

    .progress-bar {
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }
</style>