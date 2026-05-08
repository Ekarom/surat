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

// Ensure new columns exist for the updated dashboard features
$check_cols = $conn->query("SHOW COLUMNS FROM pegawai LIKE 'gelar_depan'");
if ($check_cols && $check_cols->num_rows == 0) {
    $conn->query("ALTER TABLE pegawai ADD COLUMN gelar_depan VARCHAR(20)");
    $conn->query("ALTER TABLE pegawai ADD COLUMN gelar_belakang VARCHAR(20)");
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






$info_boxes = [
    ['title' => 'Total Pegawai', 'value' => $stats['total'], 'unit' => 'Orang', 'icon' => 'la-users', 'class' => 'bg-primary'],
    ['title' => 'Pegawai Aktif', 'value' => $stats['aktif'], 'unit' => '', 'icon' => 'la-user-check', 'class' => 'bg-success'],
    ['title' => 'Jumlah PNS', 'value' => $stats['pns'], 'unit' => '', 'icon' => 'la-id-card', 'class' => 'bg-info'],
    ['title' => 'Jumlah PPPK', 'value' => $stats['pppk'], 'unit' => '', 'icon' => 'la-id-card', 'class' => 'bg-info'],
    ['title' => 'Jumlah Honorer', 'value' => $stats['honorer'], 'unit' => '', 'icon' => 'la-user-clock', 'class' => 'bg-warning'],
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

// File Upload Stats
$total_emp = $stats['total'] ?: 1;
$file_stats = [
    'foto' => ['count' => $conn->query("SELECT COUNT(*) FROM pegawai WHERE foto IS NOT NULL AND foto != ''")->fetch_row()[0], 'label' => 'Foto Profil', 'icon' => 'la-image', 'color' => 'bg-primary'],
    'sk_pangkat' => ['count' => $conn->query("SELECT COUNT(DISTINCT pegawai_id) FROM riwayat_kepegawaian WHERE kategori = 'Pangkat' AND file_lampiran IS NOT NULL AND file_lampiran != ''")->fetch_row()[0], 'label' => 'SK Pangkat', 'icon' => 'la-file-alt', 'color' => 'bg-success'],
    'sk_jabatan' => ['count' => $conn->query("SELECT COUNT(DISTINCT pegawai_id) FROM riwayat_kepegawaian WHERE kategori = 'Jabatan' AND file_lampiran IS NOT NULL AND file_lampiran != ''")->fetch_row()[0], 'label' => 'SK Jabatan', 'icon' => 'la-file-invoice', 'color' => 'bg-info'],
    'ijazah' => ['count' => $conn->query("SELECT COUNT(DISTINCT pegawai_id) FROM riwayat_kepegawaian WHERE kategori = 'Pendidikan' AND file_lampiran IS NOT NULL AND file_lampiran != ''")->fetch_row()[0], 'label' => 'Ijazah', 'icon' => 'la-graduation-cap', 'color' => 'bg-warning'],
];

// Individual Promotion Progress fetching removed - logic moved to dedicated module

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
    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard Statistik</h1>
        <div class="text-end d-none d-md-block">
            <div class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($GLOBALS['namasek'] ?? 'Unit Kerja'); ?></div>
            <div class="text-muted small">NPSN: <?php echo htmlspecialchars($GLOBALS['npsn'] ?? '-'); ?></div>
        </div>
    </div>
</div>

<!-- Statistics Grid -->
<div class="row g-3 mb-4">
    <?php foreach ($info_boxes as $box): ?>
        <div class="col-6 col-md-4 col-lg mb-2">
            <div
                class="card border-0 h-100 text-white rounded-4 position-relative overflow-hidden stat-card <?php echo $box['class']; ?> shadow-sm">
                <div class="card-body p-3 p-md-4 position-relative z-index-1">
                    <div class="medium font-bold text-white-50 text-uppercase mb-2"
                        style="letter-spacing: 1px; font-size: 0.8rem;">
                        <?php echo $box['title']; ?>
                    </div>
                    <div class="h2 font-weight-bold mb-0" style="font-size: 1.8rem;">
                        <?php echo number_format($box['value']); ?>
                        <?php if ($box['unit']): ?>
                            <span class="small font-weight-normal ml-1 text-white-50 d-none d-sm-inline"
                                style="font-size: 0.8rem;"><?php echo $box['unit']; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="position-absolute" style="right: -15px; bottom: -15px; opacity: 0.12;">
                    <i class="las <?php echo $box['icon']; ?>" style="font-size: 5rem; transform: rotate(-10deg);"></i>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>



<!-- Section: Main Dashboard Body -->
<div class="row g-4 mb-4">
    <!-- Retirement Table -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-2 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-user-clock me-2 text-danger"></i> Estimasi Pensiun Terdekat</h6>
                <a href="?data_pensiun" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">Detail Pensiun <i class="fas fa-arrow-right ms-1"></i></a>
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
                                    if ($row['sisa_th'] > 0) $sisa .= $row['sisa_th'] . " Th ";
                                    if ($row['sisa_bln'] > 0) $sisa .= $row['sisa_bln'] . " Bln";
                                    if ($sisa == "") $sisa = "Bulan Ini";
                                    $is_near = ($row['sisa_th'] == 0);
                                    ?>
                                    <tr>
                                        <td class="text-center px-4 text-muted small"><?php echo $no++; ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['nm_pegawai']); ?></div>
                                            <div class="extra-small text-muted"><?php echo $row['nip'] ?: '-'; ?></div>
                                        </td>
                                        <td><span class="badge badge-soft-blue"><?php echo htmlspecialchars($row['jabatan']); ?></span></td>
                                        <td class="fw-bold text-primary small"><?php echo $row['tmt_pensiun_display']; ?></td>
                                        <td class="text-center px-4">
                                            <span class="badge <?php echo $is_near ? 'bg-danger-soft text-danger' : 'bg-warning-soft text-warning'; ?> rounded-pill px-3 fw-bold">
                                                <?php echo $sisa; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted small">Data pensiun tidak tersedia.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- User Online Panel -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white border-0 py-2 d-flex justify-content-between align-items-center">
                <div class="fw-bold">
                    <i class="las la-globe me-1 text-success"></i> User Online
                    <span id="online-total" class="badge rounded-circle bg-success-soft text-success px-2 py-1" style="font-size: 0.7rem;">0</span>
                </div>
                <div id="online-ping" class="online-dot-pulse" style="opacity: 0; transition: opacity 0.3s;"></div>
            </div>
            <div class="card-body p-0">
                <div id="online-users-list" class="online-user-list">
                    <div class="text-center py-5">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <p class="small text-muted mt-2">Menghubungkan...</p>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white border-0 py-2 d-flex justify-content-between align-items-center">
                <button id="online-prev" class="btn btn-sm btn-outline-secondary rounded-pill px-3 opacity-50" disabled>Prev</button>
                <span id="online-page-info" class="extra-small text-muted fw-bold">Hal 1 / 1</span>
                <button id="online-next" class="btn btn-sm btn-outline-secondary rounded-pill px-3 opacity-50" disabled>Next</button>
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

        // Status Chart removed (replaced by PTK list)

        // --- Real-time Online Users ---
        let currentOnlinePage = 1;
        const onlineList = document.getElementById('online-users-list');
        const onlineTotal = document.getElementById('online-total');
        const onlinePageInfo = document.getElementById('online-page-info');
        const onlinePrev = document.getElementById('online-prev');
        const onlineNext = document.getElementById('online-next');
        const onlinePing = document.getElementById('online-ping');

        async function fetchOnlineUsers(page = 1) {
            try {
                // Show ping indicator
                onlinePing.style.opacity = '1';

                const response = await fetch(`get_online_users.php?page=${page}`);
                const data = await response.json();

                if (data.error) throw new Error(data.error);

                onlineTotal.textContent = data.total;
                onlinePageInfo.textContent = `Hal ${data.current} / ${data.pages}`;

                // Update buttons
                onlinePrev.disabled = (data.current <= 1);
                onlineNext.disabled = (data.current >= data.pages);
                onlinePrev.classList.toggle('opacity-50', onlinePrev.disabled);
                onlineNext.classList.toggle('opacity-50', onlineNext.disabled);

                // Build User List
                if (data.users.length > 0) {
                    let html = '';
                    data.users.forEach(u => {
                        html += `
                            <div class="online-user-item p-2 px-3 d-flex justify-content-between align-items-center border-bottom">
                                <div>
                                    <div class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">${u.nama}</div>
                                    <span class="badge ${u.badge_color} text-white extra-small px-2 py-1" style="font-size: 0.65rem;">
                                        ${u.role_label}
                                    </span>
                                </div>
                                <div class="text-end">
                                    <div class="text-success fw-bold d-flex align-items-center justify-content-end" style="font-size: 0.75rem;">
                                        <span class="online-dot me-1" style="width: 6px; height: 6px;"></span> Online
                                    </div>
                                    <div class="extra-small text-muted" style="font-size: 0.7rem;">${u.last_time}</div>
                                </div>
                            </div>
                        `;
                    });
                    onlineList.innerHTML = html;
                } else {
                    onlineList.innerHTML = `
                        <div class="text-center py-5 opacity-50">
                            <i class="fas fa-user-slash fa-3x mb-2"></i>
                            <p class="small">Tidak ada pengguna aktif</p>
                        </div>
                    `;
                }

                currentOnlinePage = data.current;
            } catch (error) {
                console.error('Failed to fetch online users:', error);
            } finally {
                // Hide ping indicator after a short delay
                setTimeout(() => { onlinePing.style.opacity = '0'; }, 500);
            }
        }

        // Event Listeners
        onlinePrev.addEventListener('click', () => fetchOnlineUsers(currentOnlinePage - 1));
        onlineNext.addEventListener('click', () => fetchOnlineUsers(currentOnlinePage + 1));

        // Initial Load & Polling
        fetchOnlineUsers();
        setInterval(() => fetchOnlineUsers(currentOnlinePage), 15000); // Every 15 seconds
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
        0% {
            opacity: 1;
        }

        50% {
            opacity: 0.4;
        }

        100% {
            opacity: 1;
        }
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
        0% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
        }

        70% {
            box-shadow: 0 0 0 8px rgba(16, 185, 129, 0);
        }

        100% {
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
        }
    }

    .online-dot {
        display: inline-block;
        width: 8px;
        height: 8px;
        background-color: #10b981;
        border-radius: 50%;
    }

    .online-user-item {
        padding: 0.75rem 1rem !important;
    }

    .online-user-list {
        max-height: 250px;
        overflow-y: auto;
    }

    .badge-soft-blue {
        background-color: #eff6ff;
        color: #3b82f6;
        font-weight: 600;
        font-size: 0.7rem;
        padding: 4px 8px;
        border-radius: 6px;
    }

    .bg-success-soft {
        background-color: #f0fdf4;
    }

    .bg-primary-soft {
        background-color: #eff6ff;
    }

    .bg-info-soft {
        background-color: #ecfeff;
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
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        border-bottom: 1px solid #f1f5f9;
        padding: 0.75rem 0.75rem;
    }

    .table tbody td {
        font-size: 0.85rem;
        padding: 0.6rem 0.75rem;
        border-bottom: 1px solid #f8fafc;
    }

    .progress {
        background-color: #f1f5f9;
        overflow: visible;
    }

    .progress-bar {
        box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3);
    }

    .bg-gradient-x-primary {
        background: linear-gradient(90deg, #4f46e5, #3b82f6);
    }

    .bg-gradient-x-success {
        background: linear-gradient(90deg, #10b981, #34d399);
    }

    .bg-gradient-x-warning {
        background: linear-gradient(90deg, #f59e0b, #fbbf24);
    }

    .bg-gradient-x-danger {
        background: linear-gradient(90deg, #ef4444, #f87171);
    }
</style>