<?php
/**
 * Data Kenaikan Pangkat - Optimized
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

/**
 * Function to calculate promotion details
 * Standard 4-year cycle for regular promotion
 */
function getPromotionDetails($tmtGolongan)
{
    if (!$tmtGolongan || $tmtGolongan == '0000-00-00') {
        return [
            'next_promotion' => '-',
            'sisa' => 'Data TMT Kosong',
            'isDue' => false,
            'percent' => 0,
            'error' => true
        ];
    }

    $tmtDate = new DateTime($tmtGolongan);
    $nextPromoDate = clone $tmtDate;
    $nextPromoDate->modify("+4 years");

    $today = new DateTime();
    $interval = $today->diff($nextPromoDate);

    $isDue = ($today > $nextPromoDate);
    $sisa = "";

    if ($isDue) {
        $sisa = "Sudah Waktunya";
    } else {
        if ($interval->y > 0)
            $sisa .= $interval->y . " Th ";
        if ($interval->m > 0)
            $sisa .= $interval->m . " Bln ";
        if ($interval->d > 0 && $interval->y == 0)
            $sisa .= $interval->d . " Hari";
            
        if (trim($sisa) == "")
            $sisa = "Bulan Ini";
    }

    // Calculate percentage of 4-year cycle completed
    $totalDays = 4 * 365.25;
    $passedInterval = $tmtDate->diff($today);
    $passedDays = $passedInterval->days;
    if ($passedInterval->invert) $passedDays = 0;
    
    $percent = min(max(round(($passedDays / $totalDays) * 100), 0), 100);

    return [
        'next_promotion' => $nextPromoDate->format('d-m-Y'),
        'sisa' => trim($sisa),
        'isDue' => $isDue,
        'percent' => $percent,
        'error' => false
    ];
}

// Fetch all active employees
$employees = [];
$query = "SELECT id, nm_pegawai, nip, pangkat, golongan, tmt_golongan, status_pegawai, jabatan, unit_kerja, gelar_depan, gelar_belakang FROM pegawai WHERE status = '1' ORDER BY nm_pegawai ASC";
$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $row['promotion'] = getPromotionDetails($row['tmt_golongan']);
        $employees[] = $row;
    }
}
?>

<link rel="stylesheet" href="../plugins/css/palette-gradient.min.css">

<style>
    /* Premium Table Styling */
    .table thead th {
        padding: 1rem 0.75rem !important;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.7rem;
        letter-spacing: 0.5px;
        border: none !important;
    }

    .table tbody td {
        padding: 0.75rem 0.75rem !important;
        vertical-align: middle;
    }

    @media print {
        .navbar, #sidebar-wrapper, .btn, .dataTables_filter, .dataTables_info, .dataTables_paginate, .modern-card-header button, .d-print-none {
            display: none !important;
        }
        body { background: white !important; padding: 0 !important; margin: 0 !important; }
        #page-content-wrapper { padding: 0 !important; margin: 0 !important; width: 100% !important; }
        .container-fluid { padding: 0 !important; }
        .modern-card { border: none !important; box-shadow: none !important; }
        .modern-card-header { border-bottom: 2px solid #333 !important; padding-left: 0 !important; }
        .modern-card-header h6 { font-size: 18pt !important; color: black !important; }
        .dataTables_scrollBody { height: auto !important; max-height: none !important; overflow: visible !important; }
        table.table { width: 100% !important; border-collapse: collapse !important; border: 1px solid #dee2e6 !important; }
        table.table thead th { background-color: #f8f9fa !important; color: black !important; border: 1px solid #dee2e6 !important; -webkit-print-color-adjust: exact; }
        table.table td { border: 1px solid #dee2e6 !important; }
        .small, .extra-small { font-size: 9pt !important; }
        .fw-bold { font-weight: bold !important; }
        .progress { border: 1px solid #ccc !important; }
        .progress-bar { -webkit-print-color-adjust: exact; background-color: #4f46e5 !important; }
        @page { size: A4 landscape; margin: 1cm; }
    }

    .print-header { display: none; }
    @media print {
        .print-header { display: block; text-align: center; margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 10px; }
        .print-header h4 { margin: 0; font-weight: bold; text-transform: uppercase; }
        .print-header p { margin: 2px 0; font-size: 10pt; }
    }
</style>

<div class="py-3">
    <!-- Print Header -->
    <div class="print-header">
        <h4>LAPORAN ESTIMASI KENAIKAN PANGKAT PEGAWAI</h4>
        <p>SMP NEGERI 171 JAKARTA</p>
        <p class="small text-muted">Dicetak pada: <?php echo date('d-m-Y H:i'); ?></p>
    </div>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
        <div>
            <h2 class="fw-bold mb-1">Kenaikan Pangkat</h2>
            <p class="text-muted small mb-0">Estimasi jadwal kenaikan pangkat berkala pegawai.</p>
        </div>
    </div>

    <!-- Table Card -->
    <div class="modern-card">
        <div class="modern-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h6 class="fw-bold mb-0">Daftar Estimasi Kenaikan Pangkat</h6>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold shadow-sm d-print-none"
                onclick="window.print()">
                <i class="las la-print me-2"></i> Cetak Laporan
            </button>
        </div>

        <div class="card-body p-0 p-md-3">
            <table class="table table-striped" style="width:100%;">
                <thead class="bg-gradient-x-primary">
                    <tr class="text-white">
                        <th class="text-center px-3" width="50">#</th>
                        <th>Pegawai</th>
                        <th>Pangkat / Golongan</th>
                        <th class="text-center">TMT Golongan</th>
                        <th class="text-center">Estimasi Kenaikan</th>
                        <th class="text-center">Sisa Waktu</th>
                        <th width="160">Progress Berkala</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">Tidak ada data pegawai ditemukan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($employees as $idx => $emp):
                            $promo = $emp['promotion'];
                            $hasError = $promo['error'] ?? false;

                            $rowClass = $promo['isDue'] ? 'bg-light-warning' : '';
                            if ($hasError)
                                $rowClass = 'bg-white';

                            $barClass = $promo['isDue'] ? 'bg-gradient-x-danger' : ($promo['percent'] > 90 ? 'bg-gradient-x-warning' : 'bg-gradient-x-primary');
                            ?>
                            <tr class="<?php echo $rowClass; ?>">
                                <td class="text-center text-muted small"><?php echo $idx + 1; ?></td>
                                <td>
                                    <div class="fw-bold text-dark">
                                        <?php 
                                        $full_name = (!empty($emp['gelar_depan']) ? $emp['gelar_depan'] . ' ' : '') . $emp['nm_pegawai'] . (!empty($emp['gelar_belakang']) ? ', ' . $emp['gelar_belakang'] : '');
                                        echo htmlspecialchars($full_name); 
                                        ?>
                                    </div>
                                    <div class="small text-muted"><?php echo $emp['nip'] ?: '-'; ?> | <?php echo $emp['status_pegawai']; ?></div>
                                </td>
                                <td>
                                    <div class="extra-small fw-bold"><?php echo htmlspecialchars($emp['pangkat'] ?: '-'); ?>
                                    </div>
                                    <div class="extra-small text-muted">
                                        Golongan: <?php echo htmlspecialchars($emp['golongan'] ?: '-'); ?>
                                    </div>
                                </td>
                                <td class="text-center small">
                                    <?php echo (!empty($emp['tmt_golongan']) && $emp['tmt_golongan'] != '0000-00-00') ? date('d-m-Y', strtotime($emp['tmt_golongan'])) : '<span class="text-danger small italic">Belum diisi</span>'; ?>
                                </td>
                                <td class="text-center small fw-bold text-primary"><?php echo $promo['next_promotion']; ?></td>
                                <td class="text-center">
                                    <?php if ($hasError): ?>
                                        <span class="badge bg-secondary-soft text-muted rounded-pill px-3 extra-small">TMT Kosong</span>
                                    <?php elseif ($promo['isDue']): ?>
                                        <span class="badge bg-danger rounded-pill px-3">Waktunya Naik</span>
                                    <?php else: ?>
                                        <span class="small fw-bold <?php echo ($promo['percent'] > 90) ? 'text-warning' : 'text-dark'; ?>">
                                            <?php echo $promo['sisa']; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!$hasError): ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1"
                                                style="height: 6px; border-radius: 10px; background-color: #f1f5f9;">
                                                <div class="progress-bar <?php echo $barClass; ?> rounded-pill shadow-none"
                                                    style="width: <?php echo $promo['percent']; ?>%"></div>
                                            </div>
                                            <span class="extra-small fw-bold text-muted"><?php echo $promo['percent']; ?>%</span>
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
        $('.content table.table').DataTable({
            scrollY: 450,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
        });
    });
</script>
