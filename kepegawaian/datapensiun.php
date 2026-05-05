<?php
/**
 * Data Masa Kerja & Pensiun - Optimized
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

/**
 * Function to calculate retirement details
 * Based on Indonesian Government Regulations
 */
function getRetirementDetails($tglLahir, $jabatan, $statusPegawai)
{
    if (!$tglLahir || $tglLahir == '0000-00-00') {
        return [
            'bup' => '-',
            'tmt' => '-',
            'sisa' => 'Data tgl lahir kosong',
            'isRetired' => false,
            'percent' => 0,
            'error' => true
        ];
    }

    $bup = 60; // Default Batas Usia Pensiun (Updated to 60)
    $jabatanUpper = strtoupper($jabatan);

    // BUP rules based on position type
    if (strpos($jabatanUpper, 'UTAMA') !== false || strpos($jabatanUpper, 'PROFESOR') !== false) {
        $bup = 65;
    }
    // Note: Default is now 60, matching most functional positions

    $tglLahirObj = new DateTime($tglLahir);
    $pensiunDate = clone $tglLahirObj;
    $pensiunDate->modify("+$bup years");

    // TMT Pensiun is usually the 1st of the month AFTER reaching BUP age
    $pensiunDate->modify("first day of next month");

    $today = new DateTime();
    $interval = $today->diff($pensiunDate);

    $isRetired = ($today > $pensiunDate);
    $sisa = "";

    if ($isRetired) {
        $sisa = "Pensiun";
    } else {
        if ($interval->y > 0)
            $sisa .= $interval->y . " Th ";
        if ($interval->m > 0)
            $sisa .= $interval->m . " Bln";
        if ($sisa == "")
            $sisa = "Bulan Ini";
    }

    // Calculate percentage of career completed (assumed 0-BUP years)
    $percent = min(max(round((($bup - ($interval->invert ? 0 : $interval->y)) / $bup) * 100), 0), 100);

    return [
        'bup' => $bup . " Th",
        'tmt' => $pensiunDate->format('d-m-Y'),
        'sisa' => $sisa,
        'isRetired' => $isRetired,
        'percent' => $percent,
        'error' => false
    ];
}

// Fetch all active employees
$employees = [];
$query = "SELECT id, nm_pegawai, nip, tgl_lahir, status_pegawai, jabatan, unit_kerja FROM pegawai WHERE status = '1' ORDER BY nm_pegawai ASC";
$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $row['retirement'] = getRetirementDetails($row['tgl_lahir'], $row['jabatan'], $row['status_pegawai']);
        $employees[] = $row;
    }
}
?>


<style>
    @media print {
        /* Hide UI elements */
        .navbar, #sidebar-wrapper, .btn, .dataTables_filter, .dataTables_info, .dataTables_paginate, .modern-card-header button {
            display: none !important;
        }

        /* Reset Layout */
        body { background: white !important; padding: 0 !important; margin: 0 !important; }
        #page-content-wrapper { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .container-fluid { padding: 0 !important; }
        .modern-card { border: none !important; box-shadow: none !important; }
        .modern-card-header { border-bottom: 2px solid #333 !important; padding-left: 0 !important; }
        .modern-card-header h6 { font-size: 18pt !important; color: black !important; }

        /* Expand DataTable */
        .dataTables_scrollBody {
            height: auto !important;
            max-height: none !important;
            overflow: visible !important;
        }
        .dataTables_scrollHead {
            display: block !important;
        }
        table.table {
            width: 100% !important;
            border-collapse: collapse !important;
            border: 1px solid #dee2e6 !important;
        }
        table.table thead th {
            background-color: #f8f9fa !important;
            color: black !important;
            border: 1px solid #dee2e6 !important;
            -webkit-print-color-adjust: exact;
        }
        table.table td {
            border: 1px solid #dee2e6 !important;
        }
        
        /* Typography */
        .small, .extra-small { font-size: 9pt !important; }
        .fw-bold { font-weight: bold !important; }
        
        /* Progress Bar in Print */
        .progress { border: 1px solid #ccc !important; }
        .progress-bar { -webkit-print-color-adjust: exact; background-color: #4f46e5 !important; }
        
        /* Page margins */
        @page {
            size: A4 landscape;
            margin: 1cm;
        }
    }

    .print-header {
        display: none;
    }

    @media print {
        .print-header {
            display: block;
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px double #000;
            padding-bottom: 10px;
        }
        .print-header h4 { margin: 0; font-weight: bold; text-transform: uppercase; }
        .print-header p { margin: 2px 0; font-size: 10pt; }
    }
</style>

<div class="py-3">
    <!-- Print Header (Hidden on screen) -->
    <div class="print-header">
        <h4>LAPORAN ESTIMASI MASA KERJA & PENSIUN PEGAWAI</h4>
        <p>SMP NEGERI 171 JAKARTA</p>
        <p class="small text-muted">Dicetak pada: <?php echo date('d-m-Y H:i'); ?></p>
    </div>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h2 class="fw-bold mb-1">Masa Kerja & Pensiun</h2>
            <p class="text-muted small mb-0">Estimasi batas usia pensiun pegawai aktif.</p>
        </div>
    </div>

    <!-- Table Card -->
    <div class="modern-card">
        <div class="modern-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h6 class="fw-bold mb-0">Laporan Estimasi Pensiun</h6>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold shadow-sm d-print-none" onclick="window.print()">
                <i class="las la-print me-2"></i> Cetak Laporan
            </button>
        </div>

        <div class="card-body p-0 p-md-3">
            <table class="table table-striped" style="width:100%;">
                <thead class="bg-dark">
                    <tr class="text-white">
                        <th class="text-center px-3" width="50">#</th>
                        <th>Pegawai</th>
                        <th>Jabatan / Unit</th>
                        <th class="text-center">Lahir</th>
                        <th class="text-center">BUP</th>
                        <th class="text-center">TMT Pensiun</th>
                        <th class="text-center">Sisa Waktu</th>
                        <th width="160">Lifecycle</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">Tidak ada data pegawai ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $idx => $emp):
                            $ret = $emp['retirement'];
                            $hasError = $ret['error'] ?? false;
                            
                            $rowClass = $ret['isRetired'] ? 'bg-light opacity-75' : '';
                            if ($hasError) $rowClass = 'bg-white';
                            
                            $barClass = $ret['isRetired'] ? 'bg-danger' : ($ret['sisa'] == 'Bulan Ini' ? 'bg-warning' : 'bg-primary');
                            ?>
                            <tr class="<?php echo $rowClass; ?>">
                                <td class="text-center text-muted small"><?php echo $idx + 1; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($emp['nm_pegawai']); ?></div>
                                    <div class="text-muted extra-small"><?php echo $emp['nip'] ?: '-'; ?> |
                                        <?php echo $emp['status_pegawai']; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="extra-small fw-bold"><?php echo htmlspecialchars($emp['jabatan'] ?: '-'); ?>
                                    </div>
                                    <div class="extra-small text-muted text-truncate" style="max-width: 150px;">
                                        <?php echo htmlspecialchars($emp['unit_kerja'] ?: '-'); ?>
                                    </div>
                                </td>
                                <td class="text-center small">
                                    <?php echo (!empty($emp['tgl_lahir']) && $emp['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($emp['tgl_lahir'])) : '<span class="text-danger small italic">Belum diisi</span>'; ?>
                                </td>
                                <td class="text-center small fw-bold text-muted"><?php echo $ret['bup']; ?></td>
                                <td class="text-center small fw-bold text-primary"><?php echo $ret['tmt']; ?></td>
                                <td class="text-center">
                                    <?php if ($hasError): ?>
                                        <span class="badge bg-secondary-soft text-muted rounded-pill px-3 extra-small">Tgl Lahir Kosong</span>
                                    <?php elseif ($ret['isRetired']): ?>
                                        <span class="badge bg-danger rounded-pill px-3">Pensiun</span>
                                    <?php else: ?>
                                        <span
                                            class="small fw-bold <?php echo ($ret['sisa'] == 'Bulan Ini') ? 'text-warning' : 'text-dark'; ?>">
                                            <?php echo $ret['sisa']; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$hasError): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1"
                                            style="height: 6px; border-radius: 10px; background-color: #f1f5f9;">
                                            <div class="progress-bar <?php echo $barClass; ?> rounded-pill shadow-none"
                                                style="width: <?php echo $ret['percent']; ?>%"></div>
                                        </div>
                                        <span class="extra-small fw-bold text-muted"><?php echo $ret['percent']; ?>%</span>
                                    </div>
                                    <?php else: ?>
                                        <div class="text-muted extra-small italic text-center">-</div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>



<script>
    $(document).ready(function () {
        // DataTable with FixedColumns (Standardized)
        $('.content table.table').DataTable({
            scrollY: 450,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
            // Optimization for printing: if we use DataTables, it adds wrappers. 
            // The CSS above handles the expansion.
        });
    });
</script>