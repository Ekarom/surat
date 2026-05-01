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

$info_boxes = [
    ['title' => 'Total Pegawai', 'value' => $stats['total'], 'unit' => 'Orang', 'icon' => 'fa-users', 'color' => 'bg-primary'],
    ['title' => 'Pegawai Aktif', 'value' => $stats['aktif'], 'unit' => '', 'icon' => 'fa-user-check', 'color' => 'bg-success'],
    ['title' => 'Jumlah PNS', 'value' => $stats['pns'], 'unit' => '', 'icon' => 'fa-id-card', 'color' => 'bg-info'],
    ['title' => 'Jumlah PPPK', 'value' => $stats['pppk'], 'unit' => '', 'icon' => 'fa-id-card', 'color' => 'bg-info'],
    ['title' => 'Jumlah PPPK PW', 'value' => $stats['pppk_pw'], 'unit' => '', 'icon' => 'fa-id-card', 'color' => 'bg-info'],
    ['title' => 'Jumlah Honorer', 'value' => $stats['honorer'], 'unit' => '', 'icon' => 'fa-user-clock', 'color' => 'bg-warning'],
];

// Gender Distribution
$genders = ['L' => 0, 'P' => 0];
$gender_res = $conn->query("SELECT jenis_kelamin, COUNT(*) as count FROM pegawai GROUP BY jenis_kelamin");
if ($gender_res) {
    while ($row = $gender_res->fetch_assoc()) {
        $val = strtoupper(trim($row['jenis_kelamin']));
        if ($val === 'L' || strpos($val, 'LAKI') === 0) $genders['L'] += (int)$row['count'];
        elseif ($val === 'P' || strpos($val, 'PEREMPUAN') === 0 || strpos($val, 'WANITA') === 0) $genders['P'] += (int)$row['count'];
    }
}

// Unit Kerja Data
$unit_data = $conn->query("SELECT unit_kerja, COUNT(*) as count FROM pegawai GROUP BY unit_kerja ORDER BY count ASC LIMIT 5");

// Employment Status Chart Data
$status_labels = []; $status_values = [];
$status_res = $conn->query("SELECT status_pegawai, COUNT(*) as count FROM pegawai GROUP BY status_pegawai");
if ($status_res) {
    while ($row = $status_res->fetch_assoc()) {
        $status_labels[] = $row['status_pegawai'] ?: 'Lainnya';
        $status_values[] = (int)$row['count'];
    }
}
?>

<div class="py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard</h2>
            <p class="text-muted small mb-0">Ringkasan data kepegawaian hari ini.</p>
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
                            <span class="text-muted fw-bold small text-uppercase letter-spacing-1"><?php echo $box['title']; ?></span>
                        </div>
                        <div class="h4 fw-bold mb-0">
                            <?php echo number_format($box['value']); ?>
                            <?php if ($box['unit']): ?><span class="fs-6 text-muted fw-normal ms-1"><?php echo $box['unit']; ?></span><?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Charts & Distributions -->
    <div class="row mt-4 g-4">
        <!-- Work Unit Distribution -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h6 class="fw-bold mb-0"><i class="fas fa-building me-2 text-primary"></i> Sebaran Unit Kerja (Top 5)</h6>
                </div>
                <div class="card-body pt-0">
                    <div class="mt-2">
                        <?php if ($unit_data && $unit_data->num_rows > 0): ?>
                            <?php while ($u = $unit_data->fetch_assoc()): 
                                $percent = ($stats['total'] > 0) ? round(($u['count'] / $stats['total']) * 100) : 0;
                            ?>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="small fw-bold"><?php echo htmlspecialchars($u['unit_kerja'] ?: 'N/A'); ?></span>
                                        <span class="small text-muted"><?php echo $u['count']; ?> Org (<?php echo $percent; ?>%)</span>
                                    </div>
                                    <div class="progress" style="height: 8px; border-radius: 20px;">
                                        <div class="progress-bar bg-primary rounded-pill shadow-none" style="width: <?php echo $percent; ?>%"></div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center py-5 opacity-50">
                                <i class="fas fa-inbox fa-3x mb-2"></i>
                                <p class="small">Data tidak ditemukan</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

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
    </div>

    <!-- Recent Employees Table -->
    <div class="mt-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="fas fa-clock me-2 text-warning"></i> Pegawai Terbaru</h6>
                <a href="?data_pegawai" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold">Semua Data <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center px-4" width="70">#</th>
                                <th>NIP</th>
                                <th>Nama Pegawai</th>
                                <th>Jabatan</th>
                                <th>Unit Kerja</th>
                                <th class="text-center px-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $recent = $conn->query("SELECT * FROM pegawai ORDER BY id ASC LIMIT 5");
                            if ($recent && $recent->num_rows > 0):
                                $no = 1;
                                while ($row = $recent->fetch_assoc()): ?>
                                    <tr>
                                        <td class="text-center px-4 text-muted"><?php echo $no++; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($row['nip'] ?: '-'); ?></td>
                                        <td><?php echo htmlspecialchars($row['nm_pegawai']); ?></td>
                                        <td><span class="badge badge-soft-blue"><?php echo htmlspecialchars($row['jabatan']); ?></span></td>
                                        <td><span class="small text-muted"><?php echo htmlspecialchars($row['unit_kerja']); ?></span></td>
                                        <td class="text-center px-4">
                                            <?php if ($row['status'] == '1'): ?>
                                                <span class="badge bg-success-soft text-success rounded-pill px-3">Aktif</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary-soft text-secondary rounded-pill px-3">Non-Aktif</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile;
                            else: ?>
                                <tr><td colspan="6" class="text-center py-5 text-muted">Belum ada data terbaru.</td></tr>
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
    .letter-spacing-1 { letter-spacing: 1px; }
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
    .badge-soft-blue {
        background-color: #eff6ff;
        color: #3b82f6;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 5px 10px;
        border-radius: 6px;
    }
    .bg-success-soft { background-color: #f0fdf4; }
    .bg-secondary-soft { background-color: #f8fafc; }
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
    .progress { background-color: #f1f5f9; overflow: visible; }
    .progress-bar { box-shadow: 0 4px 6px -1px rgba(59, 130, 246, 0.3); }
</style>