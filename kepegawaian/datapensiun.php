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
        return null;
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
        if ($interval->y > 0) $sisa .= $interval->y . " Th ";
        if ($interval->m > 0) $sisa .= $interval->m . " Bln";
        if ($sisa == "") $sisa = "Bulan Ini";
    }

    // Calculate percentage of career completed (assumed 0-BUP years)
    $percent = min(max(round((($bup - ($interval->invert ? 0 : $interval->y)) / $bup) * 100), 0), 100);

    return [
        'bup' => $bup,
        'tmt' => $pensiunDate->format('d-m-Y'),
        'sisa' => $sisa,
        'isRetired' => $isRetired,
        'percent' => $percent
    ];
}

// Fetch all active employees
$employees = [];
$query = "SELECT id, nm_pegawai, nip, tgl_lahir, status_pegawai, jabatan, unit_kerja FROM pegawai WHERE status = '1' ORDER BY nm_pegawai ASC";
$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $details = getRetirementDetails($row['tgl_lahir'], $row['jabatan'], $row['status_pegawai']);
        if ($details) {
            $row['retirement'] = $details;
            $employees[] = $row;
        }
    }
}
?>

<div class="py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Masa Kerja & Pensiun</h2>
            <p class="text-muted small mb-0">Estimasi batas usia pensiun pegawai aktif.</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active">Data Pensiun</li>
            </ol>
        </nav>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0">Laporan Estimasi Pensiun</h6>
            <button class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" onclick="window.print()">
                <i class="fas fa-print me-2"></i> Cetak Laporan
            </button>
        </div>

        <div class="card-body p-0">
            <div>
                <table class="table table-hover align-middle mb-0 w-100" id="retirementTable">
                    <thead class="bg-darks">
                        <tr>
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
                            <tr><td colspan="8" class="text-center py-5 text-muted">Tidak ada data pegawai ditemukan.</td></tr>
                        <?php else: ?>
                            <?php foreach ($employees as $idx => $emp):
                                $ret = $emp['retirement'];
                                $rowClass = $ret['isRetired'] ? 'bg-light opacity-75' : '';
                                $barClass = $ret['isRetired'] ? 'bg-danger' : ($ret['sisa'] == 'Bulan Ini' ? 'bg-warning' : 'bg-primary');
                                ?>
                                <tr class="<?php echo $rowClass; ?>">
                                    <td class="text-center text-muted small"><?php echo $idx + 1; ?></td>
                                    <td>
                                        <div class="fw-bold small"><?php echo htmlspecialchars($emp['nm_pegawai']); ?></div>
                                        <div class="text-muted extra-small"><?php echo $emp['nip'] ?: '-'; ?> | <?php echo $emp['status_pegawai']; ?></div>
                                    </td>
                                    <td>
                                        <div class="extra-small fw-bold"><?php echo htmlspecialchars($emp['jabatan'] ?: '-'); ?></div>
                                        <div class="extra-small text-muted text-truncate" style="max-width: 150px;"><?php echo htmlspecialchars($emp['unit_kerja'] ?: '-'); ?></div>
                                    </td>
                                    <td class="text-center small"><?php echo (!empty($emp['tgl_lahir']) && $emp['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($emp['tgl_lahir'])) : '-'; ?></td>
                                    <td class="text-center small fw-bold text-muted"><?php echo $ret['bup']; ?> Th</td>
                                    <td class="text-center small fw-bold text-primary"><?php echo $ret['tmt']; ?></td>
                                    <td class="text-center">
                                        <?php if ($ret['isRetired']): ?>
                                            <span class="badge bg-danger rounded-pill px-3">Pensiun</span>
                                        <?php else: ?>
                                            <span class="small fw-bold <?php echo ($ret['sisa'] == 'Bulan Ini') ? 'text-warning' : 'text-dark'; ?>">
                                                <?php echo $ret['sisa']; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 6px; border-radius: 10px; background-color: #f1f5f9;">
                                                <div class="progress-bar <?php echo $barClass; ?> rounded-pill shadow-none" style="width: <?php echo $ret['percent']; ?>%"></div>
                                            </div>
                                            <span class="extra-small fw-bold text-muted"><?php echo $ret['percent']; ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    .extra-small { font-size: 0.75rem; line-height: 1.2; }
    .table thead th {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        padding: 1rem 0.75rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .table tbody td {
        padding: 0.85rem 0.75rem;
        border-bottom: 1px solid #f8fafc;
    }

    @media print {
        #sidebar-wrapper, .navbar, .breadcrumb, .btn, .dataTables_wrapper .dataTables_filter, .dataTables_wrapper .dataTables_info, .dataTables_wrapper .dataTables_length, .dataTables_wrapper .dataTables_paginate {
            display: none !important;
        }
        body, #page-content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }
        .card { box-shadow: none !important; border: 1px solid #eee !important; }
        .table-responsive { overflow: visible !important; }
        table { width: 100% !important; border-collapse: collapse !important; }
    }
</style>

<!-- DataTables Integration -->
<link rel="stylesheet" href="../plugins/css/datatables.min.css">
<script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.js"></script>

<script>
    $(document).ready(function () {
        const table = $('#retirementTable').DataTable({
            paging: true,
            pageLength: 10,
            autoWidth: false,
            language: {
                search: "",
                searchPlaceholder: "Cari Pegawai...",
                info: "Menampilkan _TOTAL_ data",
                paginate: {
                    previous: "<i class='fas fa-chevron-left'></i>",
                    next: "<i class='fas fa-chevron-right'></i>"
                }
            }
        });

        // Styling search input
        $('.dataTables_filter input').addClass('form-control form-control-sm rounded-pill px-3 shadow-none').css({
            'width': '220px',
            'border': '1px solid #e2e8f0',
            'background-color': '#f8fafc'
        });
    });
</script>