<?php
if (!isset($conn)) {
    if (file_exists('../dbconn.php')) {
        include_once "../dbconn.php";
    } else {
        include_once "dbconn.php";
    }
}

// Helper to get count safely
function getCount($conn, $query) {
    $res = $conn->query($query);
    if (!$res) return 0;
    $row = $res->fetch_assoc();
    return $row['total'] ?? 0;
}

// Fetch Statistics
$total_pegawai = getCount($conn, "SELECT COUNT(*) as total FROM pegawai");
$pegawai_aktif = getCount($conn, "SELECT COUNT(*) as total FROM pegawai WHERE status = '1'");
$pns_count = getCount($conn, "SELECT COUNT(*) as total FROM pegawai WHERE status_pegawai = 'PNS'");
$honorer_count = getCount($conn, "SELECT COUNT(*) as total FROM pegawai WHERE status_pegawai = 'Honorer'");

// Data for Charts
$gender_data = $conn->query("SELECT jenis_kelamin, COUNT(*) as count FROM pegawai GROUP BY jenis_kelamin");
$genders = [];
if ($gender_data) {
    while ($row = $gender_data->fetch_assoc()) {
        $genders[$row['jenis_kelamin']] = $row['count'];
    }
}

$status_data = $conn->query("SELECT status_pegawai, COUNT(*) as count FROM pegawai GROUP BY status_pegawai");
$statuses = [];
$status_labels = [];
$status_values = [];
if ($status_data) {
    while ($row = $status_data->fetch_assoc()) {
        $status_labels[] = $row['status_pegawai'];
        $status_values[] = $row['count'];
    }
}

// Pendidikan Data
$pendidikan_data = $conn->query("SELECT pendidikan, COUNT(*) as count FROM pegawai GROUP BY pendidikan");
$pendidikan_labels = [];
$pendidikan_values = [];
if ($pendidikan_data) {
    while ($row = $pendidikan_data->fetch_assoc()) {
        $pendidikan_labels[] = $row['pendidikan'] ?: 'Tidak Diisi';
        $pendidikan_values[] = $row['count'];
    }
}

// Unit Kerja Data
$unit_data = $conn->query("SELECT unit_kerja, COUNT(*) as count FROM pegawai GROUP BY unit_kerja ORDER BY count DESC LIMIT 5");

?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark"><i class="fas fa-chart-line text-primary me-2"></i> Dashboard Kepegawaian</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="?dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard Kepegawaian</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Info Boxes -->
            <div class="row">
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm" style="border-radius: 12px;">
                        <span class="info-box-icon bg-primary elevation-1" style="border-radius: 10px;"><i class="fas fa-users"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text fw-bold text-muted small uppercase">Total Pegawai</span>
                            <span class="info-box-number h4 mb-0"><?php echo $total_pegawai; ?> <small>Orang</small></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm" style="border-radius: 12px;">
                        <span class="info-box-icon bg-success elevation-1" style="border-radius: 10px;"><i class="fas fa-user-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text fw-bold text-muted small uppercase">Pegawai Aktif</span>
                            <span class="info-box-number h4 mb-0"><?php echo $pegawai_aktif; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm" style="border-radius: 12px;">
                        <span class="info-box-icon bg-info elevation-1" style="border-radius: 10px;"><i class="fas fa-id-card"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text fw-bold text-muted small uppercase">Jumlah PNS</span>
                            <span class="info-box-number h4 mb-0"><?php echo $pns_count; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box shadow-sm" style="border-radius: 12px;">
                        <span class="info-box-icon bg-warning elevation-1" style="border-radius: 10px;"><i class="fas fa-user-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text fw-bold text-muted small uppercase">Jumlah Honorer</span>
                            <span class="info-box-number h4 mb-0 text-white"><?php echo $honorer_count; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Gender Chart -->
                <div class="col-md-4">
                    <div class="card card-outline primary shadow-sm" style="border-radius: 15px;">
                        <div class="card-header border-0 pt-3">
                            <h3 class="card-title fw-bold text-muted small uppercase"><i class="fas fa-venus-mars me-2"></i> Distribusi Gender</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="genderChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Status Chart -->
                <div class="col-md-8">
                    <div class="card card-outline primary shadow-sm" style="border-radius: 15px;">
                        <div class="card-header border-0 pt-3">
                            <h3 class="card-title fw-bold text-muted small uppercase"><i class="fas fa-briefcase me-2"></i> Status Kepegawaian</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="statusChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Pendidikan Chart -->
                <div class="col-md-7">
                    <div class="card card-outline primary shadow-sm" style="border-radius: 15px;">
                        <div class="card-header border-0 pt-3">
                            <h3 class="card-title fw-bold text-muted small uppercase"><i class="fas fa-graduation-cap me-2"></i> Tingkat Pendidikan</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="pendidikanChart" style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Unit Kerja Summary -->
                <div class="col-md-5">
                    <div class="card card-outline primary shadow-sm" style="border-radius: 15px;">
                        <div class="card-header border-0 pt-3">
                            <h3 class="card-title fw-bold text-muted small uppercase"><i class="fas fa-building me-2"></i> Sebaran Unit Kerja (Top 5)</h3>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                <?php
                                if ($unit_data && $unit_data->num_rows > 0) {
                                    while ($u = $unit_data->fetch_assoc()) {
                                        $percent = ($total_pegawai > 0) ? round(($u['count'] / $total_pegawai) * 100) : 0;
                                        echo '<div class="list-group-item border-0 px-0 mb-2">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="fw-bold small text-dark">'.($u['unit_kerja'] ?: 'Belum Diatur').'</span>
                                                    <span class="badge bg-primary rounded-pill">'.$u['count'].'</span>
                                                </div>
                                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: '.$percent.'%" aria-valuenow="'.$percent.'" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                              </div>';
                                    }
                                } else {
                                    echo '<p class="text-center text-muted">Data belum tersedia</p>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Recent Employees -->
                <div class="col-12">
                    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
                        <div class="card-header bg-menu-gradient text-white py-3">
                            <h3 class="card-title fw-bold"><i class="fas fa-user-plus me-2"></i> Pegawai Terbaru</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="text-center" width="50">#</th>
                                            <th>NIP</th>
                                            <th>Nama Pegawai</th>
                                            <th>Jabatan</th>
                                            <th>Unit Kerja</th>
                                            <th class="text-center">Tgl Bergabung</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recent = $conn->query("SELECT * FROM pegawai ORDER BY id DESC LIMIT 5");
                                        if ($recent && $recent->num_rows > 0) {
                                            $no = 1;
                                            while ($row = $recent->fetch_assoc()) {
                                                echo "<tr>
                                                        <td class='text-center'>{$no}</td>
                                                        <td class='fw-bold'>".($row['nip'] ?: '-')."</td>
                                                        <td>{$row['nm_pegawai']}</td>
                                                        <td><span class='badge bg-soft-primary px-2 py-1' style='background-color:#e7f1ff; color:#0d6efd;'>{$row['jabatan']}</span></td>
                                                        <td>{$row['unit_kerja']}</td>
                                                        <td class='text-center small text-muted'>".date('d M Y')."</td>
                                                      </tr>";
                                                $no++;
                                            }
                                        } else {
                                            echo "<tr><td colspan='6' class='text-center p-4'>Belum ada data pegawai</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 text-center">
                            <a href="?pegawai" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm fw-bold">Lihat Semua Pegawai</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gender Chart
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        new Chart(genderCtx, {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki', 'Perempuan'],
                datasets: [{
                    data: [<?php echo $genders['L'] ?? 0; ?>, <?php echo $genders['P'] ?? 0; ?>],
                    backgroundColor: ['#007bff', '#ff4d94'],
                    borderWidth: 5,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                },
                cutout: '70%'
            }
        });

        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    label: 'Jumlah Pegawai',
                    data: <?php echo json_encode($status_values); ?>,
                    backgroundColor: 'rgba(0, 123, 255, 0.7)',
                    borderColor: '#007bff',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Pendidikan Chart
        const eduCtx = document.getElementById('pendidikanChart').getContext('2d');
        new Chart(eduCtx, {
            type: 'polarArea',
            data: {
                labels: <?php echo json_encode($pendidikan_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($pendidikan_values); ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 206, 86, 0.7)',
                        'rgba(75, 192, 192, 0.7)',
                        'rgba(153, 102, 255, 0.7)',
                        'rgba(255, 159, 64, 0.7)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right' }
                }
            }
        });
    });
</script>

<style>
.bg-soft-primary { background-color: #e7f1ff; color: #0d6efd; }
.bg-menu-gradient { background: linear-gradient(135deg, #2c3e50 0%, #00d2ff 100%); }
.info-box { border: none; }
.info-box-icon { display: flex; align-items: center; justify-content: center; }
.card-outline.primary { border-top: 3px solid #007bff; }
</style>
