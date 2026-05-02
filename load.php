<?php
// Ensure database connection is available
if (!isset($conn) && file_exists('dbconn.php')) {
    include 'dbconn.php';
}
// Alias $conn to $sqlconn for compatibility with secure.php
if (isset($conn)) {
    $sqlconn = $conn;
}

include "config/secure.php"; // Sudah di-include di dashboard.php
mysqli_report(MYSQLI_REPORT_OFF); // Pastikan operator @ berfungsi untuk koneksi
?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <?php if ($lv == 1) { ?>
        <!-- Main content for LEVEL 1 (Admin) -->
        <section class="content">
            <div class="container-fluid">
                <!-- DASHBOARD ADMIN (LEVEL 1) -->
                <div class="row">
                    <!-- Box 1: Surat Masuk -->
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-gradient-x-primary">
                            <div class="inner text-white">
                                <?php
                                // Get active year and switch database
                                $tahun_aktif_card = (!empty($tahunsklh)) ? $tahunsklh : (isset($tahun) ? $tahun : date('Y'));
                                $db_card = "sas_" . $tahun_aktif_card;

                                // Use existing connection if database matches, or try new one silently
                                if (isset($db) && $db === $db_card) {
                                    $conn_card = $conn;
                                } else {
                                    $conn_card = @mysqli_connect('localhost', 'root', '', $db_card);
                                }

                                if (!$conn_card) {
                                    $conn_card = $conn;
                                }

                                $rs1 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenmasuk");
                                $data1 = mysqli_fetch_assoc($rs1);
                                $countgtk = $data1['total'] ?? 0;
                                echo "<h3>$countgtk</h3>";
                                ?>
                                <p>Surat Masuk (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratmasuk" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>

                    <!-- Box 2: Surat Keluar -->
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-gradient-x-success">
                            <div class="inner text-white">
                                <?php
                                $rs2 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenkeluar");
                                $data2 = mysqli_fetch_assoc($rs2);
                                $countkeluar = $data2['total'] ?? 0;
                                echo "<h3>$countkeluar</h3>";
                                ?>
                                <p>Surat Keluar (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratkeluar" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>

                    <!-- Box 3: Surat Keputusan -->
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-gradient-x-danger">
                            <div class="inner text-white">
                                <?php
                                $rs3 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenkeputusan");
                                $data3 = mysqli_fetch_assoc($rs3);
                                $countkeputusan = $data3['total'] ?? 0;
                                echo "<h3>$countkeputusan</h3>";
                                ?>
                                <p>Surat Keputusan (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratkeputusan" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>

                    <!-- Box 4: Surat Edaran -->
                    <div class="col-lg-2 col-6">
                        <div class="small-box bg-gradient-x-warning">
                            <div class="inner text-white">
                                <?php
                                $rs4 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenedaran");
                                $data4 = mysqli_fetch_assoc($rs4);
                                $countedaran = $data4['total'] ?? 0;
                                echo "<h3>$countedaran</h3>";

                                // Close card connection if different from main connection
                                if ($conn_card !== $conn) {
                                    mysqli_close($conn_card);
                                }
                                ?>
                                <p>Surat Edaran (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratedaran" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>
                </div>
                <!-- /.row -->

                <!-- Main row -->

                <div class="row">

                    <!-- Left col -->

                    <section class="col-lg-5 connectedSortable">

                        <!-- Welcome Card -->

                        <div class="card ">

                            <div class="card-header bg-gradient-x-info">
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">

                                        <i class="fas fa-minus"></i>

                                    </button>

                                </div>
                                <h3 class="card-title text-white">

                                    <i class="fas fa-chart-pie mr-1"></i>

                                    Selamat Datang, <b><?php echo $nuser; ?></b>

                                </h3>

                            </div>

                            <div class="card-body border">

                                <div class="card">

                                    <div class="card-header border">

                                        <b class="card-title">Informasi Terbaru</b>

                                    </div>

                                    <div class="card-body border">

                                        Harap Teliti Sebelum Menginput Surat Terima Kasih

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- /.card -->



                        <!-- DIRECT CHAT -->

                        <div class="card direct-chat direct-chat text-white">

                            <div class="card-header bg-gradient-x-info">

                                <h3 class="card-title">

                                    <i class="fas fa-history mr-1"></i>

                                    History Log

                                </h3>



                                <div class="card-tools">

                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">

                                        <i class="fas fa-minus"></i>

                                    </button>

                                </div>

                            </div>

                            <!-- /.card-header -->

                            <div class="card-body">

                                <!-- Conversations are loaded here -->

                                <div class="direct-chat-messages">

                                    <?php

                                    // Check if log variables exist (from secure.php) and are valid
                                
                                    if (isset($log1) && $log1 instanceof mysqli_result && mysqli_num_rows($log1) > 0) {

                                        $i = isset($log5['n1']) ? $log5['n1'] : 0;

                                        // Reset pointer if needed, though usually fresh
                                        mysqli_data_seek($log1, 0);

                                        while ($log2 = mysqli_fetch_array($log1)) {

                                            ?>

                                            <div class="direct-chat-msg">

                                                <div class="direct-chat-infos clearfix">

                                                    <span
                                                        class="direct-chat-name float-left"><?php echo htmlspecialchars($log2['nama']); ?></span>

                                                    <span
                                                        class="direct-chat-timestamp float-right"><?php echo $log2['waktu']; ?></span>

                                                </div>

                                                <img class="direct-chat-img" src="images/info.png" alt="message user image">

                                                <div class="direct-chat-text">

                                                    <?php echo htmlspecialchars($log2['info']); ?>

                                                </div>

                                            </div>

                                            <?php

                                            $i--;

                                        }

                                    } else {

                                        echo '<div class="p-3 text-center text-muted">No history logs available or query failed.</div>';

                                    }

                                    ?>

                                </div>

                            </div>

                        </div>

                        <!-- /.direct-chat -->



                    </section>

                    <!-- /.Left col -->

                    <!-- Right col -->
                    <section class="col-lg-7 connectedSortable">

                        <!-- Chart Card -->
                        <div class="card text-white">
                            <div class="card-header bg-gradient-x-info">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-bar mr-1"></i>
                                    Statistik Laporan Surat
                                </h3>
                                <div class="card-tools">
                                    <!-- Year Filter for Chart -->
                                    <select id="chart-year-filter" class="form-select form-select-sm me-2"
                                        style="width: 100px; display: inline-block;">
                                        <?php
                                        // Get available years from databases
                                        $result_dbs = mysqli_query($conn, "SHOW DATABASES LIKE 'sas_%'");
                                        $years_chart = [];
                                        while ($row_db = mysqli_fetch_array($result_dbs)) {
                                            $db_name = $row_db[0];
                                            if (preg_match('/sas_(\d{4})/', $db_name, $matches)) {
                                                $years_chart[] = $matches[1];
                                            }
                                        }
                                        rsort($years_chart);

                                        $tahun_aktif_select = (!empty($tahunsklh)) ? $tahunsklh : (isset($tahun) ? $tahun : date('Y'));
                                        foreach ($years_chart as $year_opt) {
                                            $selected = ($year_opt == $tahun_aktif_select) ? 'selected' : '';
                                            echo "<option value='$year_opt' $selected>$year_opt</option>";
                                        }
                                        ?>
                                    </select>
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart">
                                    <canvas id="letterStatisticsChart"
                                        style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <!-- /.card -->

                    </section>
                    <!-- /.Right col -->

                </div>

                <!-- /.row -->



        </section>

        <!-- /.content -->

        <!-- Chart.js Script -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script
            src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        <script>
            // Letter Statistics Chart
            let letterChart = null;

            function loadChartData(tahun) {
                $.ajax({
                    url: 'get_chart_data.php',
                    type: 'GET',
                    data: {
                        action: 'get_chart_data',
                        tahun: tahun
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            updateChart(response);
                        }
                    },
                    error: function () {
                        console.error('Failed to load chart data');
                    }
                });
            }

            function updateChart(response) {
                const data = response.data;
                const total = response.total;
                const tahun = response.tahun;

                if (letterChart) {
                    // Update existing chart
                    letterChart.data.datasets[0].data = [data.masuk, data.keluar, data.keputusan, data.edaran];
                    letterChart.options.plugins.title.text = 'Statistik Laporan Surat Tahun ' + tahun + ' (Total: ' + total + ')';
                    letterChart.update();
                } else {
                    // Create new chart
                    initializeChart(data, total, tahun);
                }
            }

            function initializeChart(data, total, tahun) {
                var ctx = document.getElementById('letterStatisticsChart');
                if (ctx) {
                    letterChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Surat Masuk', 'Surat Keluar', 'Surat Keputusan', 'Surat Edaran'],
                            datasets: [{
                                label: 'Jumlah Surat',
                                data: [data.masuk, data.keluar, data.keputusan, data.edaran],
                                backgroundColor: [
                                    'rgba(54, 162, 235, 0.8)',
                                    'rgba(75, 192, 192, 0.8)',
                                    'rgba(255, 99, 132, 0.8)',
                                    'rgba(255, 206, 86, 0.8)'
                                ],
                                borderColor: [
                                    'rgba(54, 162, 235, 1)',
                                    'rgba(75, 192, 192, 1)',
                                    'rgba(255, 99, 132, 1)',
                                    'rgba(255, 206, 86, 1)'
                                ],
                                borderWidth: 2
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: true,
                                    position: 'top',
                                },
                                title: {
                                    display: true,
                                    text: 'Statistik Laporan Surat Tahun ' + tahun + ' (Total: ' + total + ')',
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            var label = context.dataset.label || '';
                                            var value = context.parsed.y;
                                            var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            return label + ': ' + value + ' (' + percentage + '%)';
                                        }
                                    }
                                },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    formatter: function (value, context) {
                                        var percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return value + '\n(' + percentage + '%)';
                                    },
                                    color: '#444',
                                    font: {
                                        weight: 'bold',
                                        size: 11
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Load initial chart data
                const initialYear = $('#chart-year-filter').val() || <?php echo (!empty($tahunsklh)) ? '"' . $tahunsklh . '"' : (isset($tahun) ? '"' . $tahun . '"' : date('Y')); ?>;
                loadChartData(initialYear);

                // Handle year filter change
                $('#chart-year-filter').on('change', function () {
                    const selectedYear = $(this).val();
                    loadChartData(selectedYear);
                });
            });
        </script>

    <?php } ?>

    <?php if ($lv == 2) { ?>
        <!-- Main content for LEVEL 2 (Admin) -->
        <section class="content">
            <div class="container-fluid">
                <!-- DASHBOARD ADMIN (LEVEL 1) -->
                <div class="row">
                    <!-- Box 1: Surat Masuk -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-lightblue">
                            <div class="inner">
                                <?php
                                // Get active year and switch database
                                $tahun_aktif_card = (!empty($tahunsklh)) ? $tahunsklh : (isset($tahun) ? $tahun : date('Y'));
                                $db_card = "sas_" . $tahun_aktif_card;
                                // Use existing connection if database matches, or try new one silently
                                if (isset($db) && $db === $db_card) {
                                    $conn_card = $conn;
                                } else {
                                    $conn_card = @mysqli_connect('localhost', 'root', '', $db_card);
                                }

                                if (!$conn_card) {
                                    $conn_card = $conn;
                                }

                                $rs1 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenmasuk");
                                $data1 = mysqli_fetch_assoc($rs1);
                                $countgtk = $data1['total'] ?? 0;
                                echo "<h3>$countgtk</h3>";
                                ?>
                                <p>Surat Masuk (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratmasuk" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>

                    <!-- Box 2: Surat Keluar -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <?php
                                $rs2 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenkeluar");
                                $data2 = mysqli_fetch_assoc($rs2);
                                $countkeluar = $data2['total'] ?? 0;
                                echo "<h3>$countkeluar</h3>";
                                ?>
                                <p>Surat Keluar (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratkeluar" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>

                    <!-- Box 3: Surat Keputusan -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-danger">
                            <div class="inner">
                                <?php
                                $rs3 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenkeputusan");
                                $data3 = mysqli_fetch_assoc($rs3);
                                $countkeputusan = $data3['total'] ?? 0;
                                echo "<h3>$countkeputusan</h3>";
                                ?>
                                <p>Surat Keputusan (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratkeputusan" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>

                    <!-- Box 4: Surat Edaran -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-warning">
                            <div class="inner">
                                <?php
                                $rs4 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenedaran");
                                $data4 = mysqli_fetch_assoc($rs4);
                                $countedaran = $data4['total'] ?? 0;
                                echo "<h3>$countedaran</h3>";

                                // Close card connection if different from main connection
                                if ($conn_card !== $conn) {
                                    mysqli_close($conn_card);
                                }
                                ?>
                                <p>Surat Edaran (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratedaran" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>
                </div>
                <!-- /.row -->

                <!-- Main row -->

                <div class="row">

                    <!-- Left col -->

                    <section class="col-lg-5 connectedSortable">

                        <!-- Welcome Card -->

                        <div class="card">

                            <div class="card-header bg-menu-gradient">
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">

                                        <i class="fas fa-minus"></i>

                                    </button>

                                </div>
                                <h3 class="card-title">

                                    <i class="fas fa-chart-pie mr-1"></i>

                                    Selamat Datang, <b><?php echo $nuser; ?></b>

                                </h3>

                            </div>

                            <div class="card-body border">

                                <div class="card">

                                    <div class="card-header border">

                                        <b class="card-title">Informasi Terbaru</b>

                                    </div>

                                    <div class="card-body border">

                                        Harap Teliti Sebelum Menginput Surat Terima Kasih

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- /.card -->



                        <!-- DIRECT CHAT -->

                        <div class="card direct-chat direct-chat">

                            <div class="card-header bg-menu-gradient">

                                <h3 class="card-title">

                                    <i class="fas fa-history mr-1"></i>

                                    History Log

                                </h3>



                                <div class="card-tools">

                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">

                                        <i class="fas fa-minus"></i>

                                    </button>

                                </div>

                            </div>

                            <!-- /.card-header -->

                            <div class="card-body">

                                <!-- Conversations are loaded here -->

                                <div class="direct-chat-messages">

                                    <?php

                                    // Check if log variables exist (from secure.php) and are valid
                                
                                    if (isset($log1) && $log1 instanceof mysqli_result && mysqli_num_rows($log1) > 0) {

                                        $i = isset($log5['n1']) ? $log5['n1'] : 0;

                                        // Reset pointer if needed, though usually fresh
                                        mysqli_data_seek($log1, 0);

                                        while ($log2 = mysqli_fetch_array($log1)) {

                                            ?>

                                            <div class="direct-chat-msg">

                                                <div class="direct-chat-infos clearfix">

                                                    <span
                                                        class="direct-chat-name float-left"><?php echo htmlspecialchars($log2['nama']); ?></span>

                                                    <span
                                                        class="direct-chat-timestamp float-right"><?php echo $log2['waktu']; ?></span>

                                                </div>

                                                <img class="direct-chat-img" src="images/info.png" alt="message user image">

                                                <div class="direct-chat-text">

                                                    <?php echo htmlspecialchars($log2['info']); ?>

                                                </div>

                                            </div>

                                            <?php

                                            $i--;

                                        }

                                    } else {

                                        echo '<div class="p-3 text-center text-muted">No history logs available or query failed.</div>';

                                    }

                                    ?>

                                </div>

                            </div>

                        </div>

                        <!-- /.direct-chat -->



                    </section>

                    <!-- /.Left col -->

                    <!-- Right col -->
                    <section class="col-lg-7 connectedSortable">

                        <!-- Chart Card -->
                        <div class="card">
                            <div class="card-header bg-menu-gradient">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-bar mr-1"></i>
                                    Statistik Laporan Surat
                                </h3>
                                <div class="card-tools">
                                    <!-- Year Filter for Chart -->
                                    <select id="chart-year-filter" class="form-select form-select-sm me-2"
                                        style="width: 100px; display: inline-block;">
                                        <?php
                                        // Get available years from databases
                                        $result_dbs = mysqli_query($conn, "SHOW DATABASES LIKE 'sas_%'");
                                        $years_chart = [];
                                        while ($row_db = mysqli_fetch_array($result_dbs)) {
                                            $db_name = $row_db[0];
                                            if (preg_match('/sas_(\d{4})/', $db_name, $matches)) {
                                                $years_chart[] = $matches[1];
                                            }
                                        }
                                        rsort($years_chart);

                                        $tahun_aktif_select = (!empty($tahunsklh)) ? $tahunsklh : (isset($tahun) ? $tahun : date('Y'));
                                        foreach ($years_chart as $year_opt) {
                                            $selected = ($year_opt == $tahun_aktif_select) ? 'selected' : '';
                                            echo "<option value='$year_opt' $selected>$year_opt</option>";
                                        }
                                        ?>
                                    </select>
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart">
                                    <canvas id="letterStatisticsChart"
                                        style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <!-- /.card -->

                    </section>
                    <!-- /.Right col -->

                </div>

                <!-- /.row -->



        </section>

        <!-- /.content -->

        <!-- Chart.js Script -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script
            src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        <script>
            // Letter Statistics Chart
            let letterChart = null;

            function loadChartData(tahun) {
                $.ajax({
                    url: 'get_chart_data.php',
                    type: 'GET',
                    data: {
                        action: 'get_monthly_chart_data',
                        tahun: tahun
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            updateChart(response);
                        }
                    },
                    error: function () {
                        console.error('Failed to load chart data');
                    }
                });
            }

            function updateChart(response) {
                const percentages = response.monthly_percentages;
                const counts = response.monthly_counts;
                const labels = response.labels;
                const total = response.total;
                const tahun = response.tahun;

                if (letterChart) {
                    // Update existing chart
                    letterChart.data.labels = labels;
                    letterChart.data.datasets[0].data = percentages;
                    letterChart.options.plugins.title.text = 'Progress Laporan Surat Per Bulan Tahun ' + tahun + ' (Target: ' + total + ' Surat)';
                    letterChart.update();
                } else {
                    // Create new chart
                    initializeChart(percentages, counts, labels, total, tahun);
                }
            }

            function initializeChart(percentages, counts, labels, total, tahun) {
                var ctx = document.getElementById('letterStatisticsChart');
                if (ctx) {
                    letterChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Progress (%)',
                                data: percentages,
                                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                counts: counts, // Store counts for tooltip
                                minBarLength: 5 // Minimum bar height in pixels
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: 'Progress Laporan Surat Per Bulan Tahun ' + tahun + ' (Target: ' + total + ' Surat)',
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            var progress = context.parsed.y;
                                            var monthCount = counts[context.dataIndex];

                                            // Calculate cumulative count up to this month
                                            var cumulativeCount = 0;
                                            for (var i = 0; i <= context.dataIndex; i++) {
                                                cumulativeCount += counts[i];
                                            }

                                            return [
                                                'Bulan ini: ' + monthCount + ' surat',
                                                'Total s/d bulan ini: ' + cumulativeCount + ' surat',
                                                'Progress: ' + progress + '%'
                                            ];
                                        }
                                    }
                                },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    formatter: function (value, context) {
                                        return value + '%';
                                    },
                                    color: '#444',
                                    font: {
                                        weight: 'bold',
                                        size: 11
                                    },
                                    display: function (context) {
                                        // Always show labels
                                        return true;
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function (value) {
                                            return value + '%';
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Progress (%)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Bulan'
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Load initial chart data
                const initialYear = $('#chart-year-filter').val() || <?php echo (!empty($tahunsklh)) ? '"' . $tahunsklh . '"' : (isset($tahun) ? '"' . $tahun . '"' : date('Y')); ?>;
                loadChartData(initialYear);

                // Handle year filter change
                $('#chart-year-filter').on('change', function () {
                    const selectedYear = $(this).val();
                    loadChartData(selectedYear);
                });
            });
        </script>
    <?php } ?>

    <!---------------------------LEVEL 3 (USER)--------------------------->
    <?php if ($lv == 3) { ?>
    <!-- Main content for LEVEL 3 (USER) -->
        <section class="content">
            <div class="container-fluid">
                <!-- DASHBOARD ADMIN (LEVEL 1) -->
                <div class="row">
                    <!-- Box 1: Surat Masuk -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-lightblue">
                            <div class="inner">
                                <?php
                                // Get active year and switch database
                                $tahun_aktif_card = (!empty($tahunsklh)) ? $tahunsklh : (isset($tahun) ? $tahun : date('Y'));
                                $db_card = "sas_" . $tahun_aktif_card;
                                // Use existing connection if database matches, or try new one silently
                                if (isset($db) && $db === $db_card) {
                                    $conn_card = $conn;
                                } else {
                                    $conn_card = @mysqli_connect('localhost', 'root', '', $db_card);
                                }

                                if (!$conn_card) {
                                    $conn_card = $conn;
                                }

                                $rs1 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenmasuk");
                                $data1 = mysqli_fetch_assoc($rs1);
                                $countgtk = $data1['total'] ?? 0;
                                echo "<h3>$countgtk</h3>";
                                ?>
                                <p>Surat Masuk (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratmasuk" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>

                    <!-- Box 2: Surat Keluar -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-success">
                            <div class="inner">
                                <?php
                                $rs2 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenkeluar");
                                $data2 = mysqli_fetch_assoc($rs2);
                                $countkeluar = $data2['total'] ?? 0;
                                echo "<h3>$countkeluar</h3>";
                                ?>
                                <p>Surat Keluar (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratkeluar" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>

                    <!-- Box 3: Surat Keputusan -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-danger">
                            <div class="inner">
                                <?php
                                $rs3 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenkeputusan");
                                $data3 = mysqli_fetch_assoc($rs3);
                                $countkeputusan = $data3['total'] ?? 0;
                                echo "<h3>$countkeputusan</h3>";
                                ?>
                                <p>Surat Keputusan (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratkeputusan" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>

                    <!-- Box 4: Surat Edaran -->
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-gradient-warning">
                            <div class="inner">
                                <?php
                                $rs4 = mysqli_query($conn_card, "SELECT COUNT(*) as total FROM dokumenedaran");
                                $data4 = mysqli_fetch_assoc($rs4);
                                $countedaran = $data4['total'] ?? 0;
                                echo "<h3>$countedaran</h3>";

                                // Close card connection if different from main connection
                                if ($conn_card !== $conn) {
                                    mysqli_close($conn_card);
                                }
                                ?>
                                <p>Surat Edaran (<?php echo $tahun_aktif_card; ?>)</p>
                            </div>
                            <div class="icon"><i class="ion ion-stats-bars"></i></div>
                            <a href="?page=suratedaran" class="small-box-footer">More info <i
                                    class="bi bi-link-45deg"></i></a>
                        </div>
                    </div>
                </div>
                <!-- /.row -->

                <!-- Main row -->

                <div class="row">

                    <!-- Left col -->

                    <section class="col-lg-5 connectedSortable">

                        <!-- Welcome Card -->

                        <div class="card">

                            <div class="card-header bg-menu-gradient">
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">

                                        <i class="fas fa-minus"></i>

                                    </button>

                                </div>
                                <h3 class="card-title">

                                    <i class="fas fa-chart-pie mr-1"></i>

                                    Selamat Datang, <b><?php echo $nuser; ?></b>

                                </h3>

                            </div>

                            <div class="card-body border">

                                <div class="card">

                                    <div class="card-header border">

                                        <b class="card-title">Informasi Terbaru</b>

                                    </div>

                                    <div class="card-body border">

                                        Harap Teliti Sebelum Menginput Surat Terima Kasih

                                    </div>

                                </div>

                            </div>

                        </div>

                        <!-- /.card -->



                        <!-- DIRECT CHAT -->

                        <div class="card direct-chat direct-chat">

                            <div class="card-header bg-menu-gradient">

                                <h3 class="card-title">

                                    <i class="fas fa-history mr-1"></i>

                                    History Log

                                </h3>



                                <div class="card-tools">

                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">

                                        <i class="fas fa-minus"></i>

                                    </button>

                                </div>

                            </div>

                            <!-- /.card-header -->

                            <div class="card-body">

                                <!-- Conversations are loaded here -->

                                <div class="direct-chat-messages">

                                    <?php

                                    // Check if log variables exist (from secure.php) and are valid
                                
                                    if (isset($log1) && $log1 instanceof mysqli_result && mysqli_num_rows($log1) > 0) {

                                        $i = isset($log5['n1']) ? $log5['n1'] : 0;

                                        // Reset pointer if needed, though usually fresh
                                        mysqli_data_seek($log1, 0);

                                        while ($log2 = mysqli_fetch_array($log1)) {

                                            ?>

                                            <div class="direct-chat-msg">

                                                <div class="direct-chat-infos clearfix">

                                                    <span
                                                        class="direct-chat-name float-left"><?php echo htmlspecialchars($log2['nama']); ?></span>

                                                    <span
                                                        class="direct-chat-timestamp float-right"><?php echo $log2['waktu']; ?></span>

                                                </div>

                                                <img class="direct-chat-img" src="images/info.png" alt="message user image">

                                                <div class="direct-chat-text">

                                                    <?php echo htmlspecialchars($log2['info']); ?>

                                                </div>

                                            </div>

                                            <?php

                                            $i--;

                                        }

                                    } else {

                                        echo '<div class="p-3 text-center text-muted">No history logs available or query failed.</div>';

                                    }

                                    ?>

                                </div>

                            </div>

                        </div>

                        <!-- /.direct-chat -->



                    </section>

                    <!-- /.Left col -->

                    <!-- Right col -->
                    <section class="col-lg-7 connectedSortable">

                        <!-- Chart Card -->
                        <div class="card">
                            <div class="card-header bg-menu-gradient">
                                <h3 class="card-title">
                                    <i class="fas fa-chart-bar mr-1"></i>
                                    Statistik Laporan Surat
                                </h3>
                                <div class="card-tools">
                                    <!-- Year Filter for Chart -->
                                    <select id="chart-year-filter" class="form-select form-select-sm me-2"
                                        style="width: 100px; display: inline-block;">
                                        <?php
                                        // Get available years from databases
                                        $result_dbs = mysqli_query($conn, "SHOW DATABASES LIKE 'sas_%'");
                                        $years_chart = [];
                                        while ($row_db = mysqli_fetch_array($result_dbs)) {
                                            $db_name = $row_db[0];
                                            if (preg_match('/sas_(\d{4})/', $db_name, $matches)) {
                                                $years_chart[] = $matches[1];
                                            }
                                        }
                                        rsort($years_chart);

                                        $tahun_aktif_select = (!empty($tahunsklh)) ? $tahunsklh : (isset($tahun) ? $tahun : date('Y'));
                                        foreach ($years_chart as $year_opt) {
                                            $selected = ($year_opt == $tahun_aktif_select) ? 'selected' : '';
                                            echo "<option value='$year_opt' $selected>$year_opt</option>";
                                        }
                                        ?>
                                    </select>
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart">
                                    <canvas id="letterStatisticsChart"
                                        style="min-height: 300px; height: 300px; max-height: 300px; max-width: 100%;"></canvas>
                                </div>
                            </div>
                        </div>
                        <!-- /.card -->

                    </section>
                    <!-- /.Right col -->

                </div>

                <!-- /.row -->



        </section>

        <!-- /.content -->

        <!-- Chart.js Script -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script
            src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>
        <script>
            // Letter Statistics Chart
            let letterChart = null;

            function loadChartData(tahun) {
                $.ajax({
                    url: 'get_chart_data.php',
                    type: 'GET',
                    data: {
                        action: 'get_monthly_chart_data',
                        tahun: tahun
                    },
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            updateChart(response);
                        }
                    },
                    error: function () {
                        console.error('Failed to load chart data');
                    }
                });
            }

            function updateChart(response) {
                const percentages = response.monthly_percentages;
                const counts = response.monthly_counts;
                const labels = response.labels;
                const total = response.total;
                const tahun = response.tahun;

                if (letterChart) {
                    // Update existing chart
                    letterChart.data.labels = labels;
                    letterChart.data.datasets[0].data = percentages;
                    letterChart.options.plugins.title.text = 'Progress Laporan Surat Per Bulan Tahun ' + tahun + ' (Target: ' + total + ' Surat)';
                    letterChart.update();
                } else {
                    // Create new chart
                    initializeChart(percentages, counts, labels, total, tahun);
                }
            }

            function initializeChart(percentages, counts, labels, total, tahun) {
                var ctx = document.getElementById('letterStatisticsChart');
                if (ctx) {
                    letterChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Progress (%)',
                                data: percentages,
                                backgroundColor: 'rgba(75, 192, 192, 0.8)',
                                borderColor: 'rgba(75, 192, 192, 1)',
                                borderWidth: 2,
                                counts: counts, // Store counts for tooltip
                                minBarLength: 5 // Minimum bar height in pixels
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: true,
                                    text: 'Progress Laporan Surat Per Bulan Tahun ' + tahun + ' (Target: ' + total + ' Surat)',
                                    font: {
                                        size: 16,
                                        weight: 'bold'
                                    }
                                },
                                tooltip: {
                                    callbacks: {
                                        label: function (context) {
                                            var progress = context.parsed.y;
                                            var monthCount = counts[context.dataIndex];

                                            // Calculate cumulative count up to this month
                                            var cumulativeCount = 0;
                                            for (var i = 0; i <= context.dataIndex; i++) {
                                                cumulativeCount += counts[i];
                                            }

                                            return [
                                                'Bulan ini: ' + monthCount + ' surat',
                                                'Total s/d bulan ini: ' + cumulativeCount + ' surat',
                                                'Progress: ' + progress + '%'
                                            ];
                                        }
                                    }
                                },
                                datalabels: {
                                    anchor: 'end',
                                    align: 'top',
                                    formatter: function (value, context) {
                                        return value + '%';
                                    },
                                    color: '#444',
                                    font: {
                                        weight: 'bold',
                                        size: 11
                                    },
                                    display: function (context) {
                                        // Always show labels
                                        return true;
                                    }
                                }
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function (value) {
                                            return value + '%';
                                        }
                                    },
                                    title: {
                                        display: true,
                                        text: 'Progress (%)'
                                    }
                                },
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Bulan'
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Load initial chart data
                const initialYear = $('#chart-year-filter').val() || <?php echo (!empty($tahunsklh)) ? '"' . $tahunsklh . '"' : (isset($tahun) ? '"' . $tahun . '"' : date('Y')); ?>;
                loadChartData(initialYear);

                // Handle year filter change
                $('#chart-year-filter').on('change', function () {
                    const selectedYear = $(this).val();
                    loadChartData(selectedYear);
                });
            });
        </script>
    <?php } ?>

    <?php if ($lv == 3) { ?>
        <!-- Main content for LEVEL 3 (Pegawai/User) -->
        <section class="content">
            <div class="container-fluid">
                <!-- DASHBOARD PEGAWAI (LEVEL 3) -->
                <div class="row pt-3">
                    <div class="col-lg-4">
                        <!-- Profile Card -->
                        <div class="card card-outline primary shadow-lg border-0" style="border-radius: 15px;">
                            <div class="card-body box-profile text-center py-4">
                                <?php
                                $user_nik = $_SESSION['nik'] ?? '';
                                $pegawai = null;
                                if (!empty($user_nik)) {
                                    $stmt_peg = $conn->prepare("SELECT * FROM tbl_pegawai WHERE nip = ? LIMIT 1");
                                    $stmt_peg->bind_param("s", $user_nik);
                                    $stmt_peg->execute();
                                    $pegawai = $stmt_peg->get_result()->fetch_assoc();
                                }

                                $foto_peg = (!empty($pegawai['foto']) && file_exists('file/pegawai/' . $pegawai['foto'])) ? 'file/pegawai/' . $pegawai['foto'] : 'images/default.png';
                                ?>
                                <div class="text-center mb-3">
                                    <img class="profile-user-img img-fluid img-circle shadow-sm"
                                        src="<?php echo $foto_peg; ?>" alt="User profile picture"
                                        style="width: 150px; height: 150px; object-fit: cover; border: 5px solid #fff;">
                                </div>
                                <h3 class="profile-username text-center fw-bold text-primary mb-0">
                                    <?php echo $pegawai['nm_pegawai'] ?? $nuser; ?>
                                </h3>
                                <p class="text-muted text-center small mb-3">
                                    <?php echo $pegawai['nip'] ?? 'NIP tidak tersedia'; ?>
                                </p>
                                <div class="badge bg-soft-primary px-3 py-2 mb-4"
                                    style="background-color: #e7f1ff; color: #0d6efd; border-radius: 10px;">
                                    <?php echo $pegawai['jabatan'] ?? 'Jabatan tidak tersedia'; ?>
                                </div>

                                <ul class="list-group list-group-unbordered mb-3 text-start px-2">
                                    <li class="list-group-item border-0 border-bottom">
                                        <small class="text-muted text-uppercase d-block fw-bold"
                                            style="font-size: 0.7rem;">Pangkat / Golongan</small>
                                        <span
                                            class="fw-bold"><?php echo ($pegawai['pangkat'] ?? '-') . ' / ' . ($pegawai['golongan'] ?? '-'); ?></span>
                                    </li>
                                    <li class="list-group-item border-0 border-bottom">
                                        <small class="text-muted text-uppercase d-block fw-bold"
                                            style="font-size: 0.7rem;">Unit Kerja</small>
                                        <span class="fw-bold"><?php echo $pegawai['unit_kerja'] ?? '-'; ?></span>
                                    </li>
                                    <li class="list-group-item border-0">
                                        <small class="text-muted text-uppercase d-block fw-bold"
                                            style="font-size: 0.7rem;">Status Kepegawaian</small>
                                        <span class="fw-bold"><?php echo $pegawai['status_pegawai'] ?? '-'; ?></span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <!-- Welcome & Quick Stats -->
                        <div class="row">
                            <div class="col-12 mb-4">
                                <div class="card bg-menu-gradient text-white shadow-lg border-0"
                                    style="border-radius: 15px; overflow: hidden;">
                                    <div class="card-body p-4 position-relative">
                                        <div class="position-relative z-index-1">
                                            <h2 class="fw-bold">Selamat Datang,
                                                <?php echo explode(' ', $pegawai['nm_pegawai'] ?? $nuser)[0]; ?>!
                                            </h2>
                                            <p class="mb-0 opacity-75">Ini adalah dashboard sistem kepegawaian pribadi Anda.
                                                Anda dapat melihat data profil dan status kepegawaian Anda di sini.</p>
                                        </div>
                                        <i class="fas fa-user-check position-absolute"
                                            style="right: -20px; bottom: -20px; font-size: 150px; opacity: 0.1;"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Cards -->
                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                                    <div class="card-header bg-white border-0 pt-3">
                                        <h5 class="card-title fw-bold text-muted small text-uppercase"><i
                                                class="fas fa-info-circle me-2"></i> Detail Informasi</h5>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-light rounded-3">
                                                    <div class="bg-white p-2 rounded-circle shadow-sm me-3"><i
                                                            class="fas fa-map-marker-alt text-danger"></i></div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Tempat,
                                                            Tgl Lahir</small>
                                                        <span
                                                            class="fw-bold small"><?php echo ($pegawai['tempat_lahir'] ?? '-') . ', ' . ($pegawai['tgl_lahir'] ?? '-'); ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-light rounded-3">
                                                    <div class="bg-white p-2 rounded-circle shadow-sm me-3"><i
                                                            class="fas fa-graduation-cap text-primary"></i></div>
                                                    <div>
                                                        <small class="text-muted d-block"
                                                            style="font-size: 0.7rem;">Pendidikan</small>
                                                        <span
                                                            class="fw-bold small"><?php echo $pegawai['pendidikan'] ?? '-'; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6 mb-4">
                                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                                    <div class="card-header bg-white border-0 pt-3">
                                        <h5 class="card-title fw-bold text-muted small text-uppercase"><i
                                                class="fas fa-address-book me-2"></i> Kontak Saya</h5>
                                    </div>
                                    <div class="card-body pt-0">
                                        <div class="row g-3">
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-light rounded-3">
                                                    <div class="bg-white p-2 rounded-circle shadow-sm me-3"><i
                                                            class="fas fa-phone-alt text-success"></i></div>
                                                    <div>
                                                        <small class="text-muted d-block" style="font-size: 0.7rem;">No. HP
                                                            / WhatsApp</small>
                                                        <span
                                                            class="fw-bold small"><?php echo $pegawai['no_hp'] ?? '-'; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-flex align-items-center p-2 bg-light rounded-3">
                                                    <div class="bg-white p-2 rounded-circle shadow-sm me-3"><i
                                                            class="fas fa-envelope text-warning"></i></div>
                                                    <div>
                                                        <small class="text-muted d-block"
                                                            style="font-size: 0.7rem;">Email</small>
                                                        <span
                                                            class="fw-bold small"><?php echo $pegawai['email'] ?? '-'; ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    <?php } ?>
    <!---------------------------END LEVEL 3 (USER)-------------------------->