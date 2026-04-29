<?php
// Chart Data AJAX Handler
session_start();
include 'dbconn.php';
include 'config/secure.php';

header('Content-Type: application/json');

// Handle monthly percentage chart data
if (isset($_GET['action']) && $_GET['action'] === 'get_monthly_chart_data') {
    $tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : (isset($_SESSION['tahundb']) ? $_SESSION['tahundb'] : date('Y'));
    
    // Switch to the year-specific database
    $db_chart = "sas_" . $tahun;
    $conn_chart = @mysqli_connect('localhost', 'arsip', 'BHmD8VlJELecRqw4S5OAYXDpc', $db_chart);
    
    if (!$conn_chart) {
        // Fallback to default connection
        $conn_chart = $conn;
    }
    
    // Initialize monthly data array
    $monthly_data = [];
    $monthly_percentages = [];
    $bulan_indonesia = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    
    // Get total count for the year from all tables
    $total_year = 0;
    
    // Query each month for all document types
    for ($bulan = 1; $bulan <= 12; $bulan++) {
        $count_bulan = 0;
        
        // Count from dokumenmasuk
        $query_masuk = "SELECT COUNT(*) as total FROM dokumenmasuk WHERE MONTH(tgl_dokumen) = $bulan AND YEAR(tgl_dokumen) = $tahun";
        $rs_masuk = mysqli_query($conn_chart, $query_masuk);
        if ($rs_masuk) {
            $data = mysqli_fetch_assoc($rs_masuk);
            $count_bulan += $data['total'] ?? 0;
        }
        
        // Count from dokumenkeluar
        $query_keluar = "SELECT COUNT(*) as total FROM dokumenkeluar WHERE MONTH(tgl_dokumen) = $bulan AND YEAR(tgl_dokumen) = $tahun";
        $rs_keluar = mysqli_query($conn_chart, $query_keluar);
        if ($rs_keluar) {
            $data = mysqli_fetch_assoc($rs_keluar);
            $count_bulan += $data['total'] ?? 0;
        }
        
        // Count from dokumenkeputusan
        $query_keputusan = "SELECT COUNT(*) as total FROM dokumenkeputusan WHERE MONTH(tgl_dokumen) = $bulan AND YEAR(tgl_dokumen) = $tahun";
        $rs_keputusan = mysqli_query($conn_chart, $query_keputusan);
        if ($rs_keputusan) {
            $data = mysqli_fetch_assoc($rs_keputusan);
            $count_bulan += $data['total'] ?? 0;
        }
        
        // Count from dokumenedaran
        $query_edaran = "SELECT COUNT(*) as total FROM dokumenedaran WHERE MONTH(tgl_dokumen) = $bulan AND YEAR(tgl_dokumen) = $tahun";
        $rs_edaran = mysqli_query($conn_chart, $query_edaran);
        if ($rs_edaran) {
            $data = mysqli_fetch_assoc($rs_edaran);
            $count_bulan += $data['total'] ?? 0;
        }
        
        $monthly_data[$bulan] = $count_bulan;
        $total_year += $count_bulan;
    }
    
    // Calculate cumulative percentages (progress)
    $cumulative_count = 0;
    for ($bulan = 1; $bulan <= 12; $bulan++) {
        $cumulative_count += $monthly_data[$bulan];
        
        if ($total_year > 0) {
            $percentage = ($cumulative_count / $total_year) * 100;
            $monthly_percentages[$bulan] = round($percentage, 1);
        } else {
            $monthly_percentages[$bulan] = 0;
        }
    }
    
    // Close chart connection if different
    if ($conn_chart !== $conn) {
        mysqli_close($conn_chart);
    }
    
    echo json_encode([
        'success' => true,
        'tahun' => $tahun,
        'monthly_counts' => array_values($monthly_data),
        'monthly_percentages' => array_values($monthly_percentages),
        'labels' => array_values($bulan_indonesia),
        'total' => $total_year
    ]);
    exit;
}

// Handle existing bar chart data (by letter type)
if (isset($_GET['action']) && $_GET['action'] === 'get_chart_data') {
    $tahun = isset($_GET['tahun']) ? intval($_GET['tahun']) : (isset($_SESSION['tahundb']) ? $_SESSION['tahundb'] : date('Y'));
    
    // Switch to the year-specific database
    $db_chart = "sas_" . $tahun;
    $conn_chart = @mysqli_connect('localhost', 'arsip', 'BHmD8VlJELecRqw4S5OAYXDpc', $db_chart);
    
    if (!$conn_chart) {
        // Fallback to default connection
        $conn_chart = $conn;
    }
    
    // Query data for chart
    $query_masuk = "SELECT COUNT(*) as total FROM dokumenmasuk WHERE YEAR(tgl_dokumen) = $tahun";
    $rs_masuk = mysqli_query($conn_chart, $query_masuk);
    $count_masuk = ($rs_masuk && $data = mysqli_fetch_assoc($rs_masuk)) ? ($data['total'] ?? 0) : 0;

    $query_keluar = "SELECT COUNT(*) as total FROM dokumenkeluar WHERE YEAR(tgl_dokumen) = $tahun";
    $rs_keluar = mysqli_query($conn_chart, $query_keluar);
    $count_keluar = ($rs_keluar && $data = mysqli_fetch_assoc($rs_keluar)) ? ($data['total'] ?? 0) : 0;

    $query_keputusan = "SELECT COUNT(*) as total FROM dokumenkeputusan WHERE YEAR(tgl_dokumen) = $tahun";
    $rs_keputusan = mysqli_query($conn_chart, $query_keputusan);
    $count_keputusan = ($rs_keputusan && $data = mysqli_fetch_assoc($rs_keputusan)) ? ($data['total'] ?? 0) : 0;

    $query_edaran = "SELECT COUNT(*) as total FROM dokumenedaran WHERE YEAR(tgl_dokumen) = $tahun";
    $rs_edaran = mysqli_query($conn_chart, $query_edaran);
    $count_edaran = ($rs_edaran && $data = mysqli_fetch_assoc($rs_edaran)) ? ($data['total'] ?? 0) : 0;
    
    $total_all = $count_masuk + $count_keluar + $count_keputusan + $count_edaran;
    
    // Close chart connection if different
    if ($conn_chart !== $conn) {
        mysqli_close($conn_chart);
    }
    
    echo json_encode([
        'success' => true,
        'tahun' => $tahun,
        'data' => [
            'masuk' => $count_masuk,
            'keluar' => $count_keluar,
            'keputusan' => $count_keputusan,
            'edaran' => $count_edaran
        ],
        'total' => $total_all
    ]);
    exit;
}
?>
