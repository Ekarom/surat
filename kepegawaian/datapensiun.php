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

    $bup = 60; // Default BUP for most functional positions (like Teachers)
    $jabatanUpper = strtoupper($jabatan);

    // BUP rules based on position type
    if (strpos($jabatanUpper, 'UTAMA') !== false || strpos($jabatanUpper, 'PROFESOR') !== false) {
        $bup = 65;
    }
    // BUP 58 for Administrative / Implementation roles (if not a Guru)
    elseif (strpos($jabatanUpper, 'GURU') === false) {
        $adminKeywords = ['STAF', 'TATA USAHA', 'TU', 'ADMIN', 'PELAKSANA', 'PENGADMINISTRASI', 'BENDAHARA', 'CARAKA', 'KEBERSIHAN', 'KEAMANAN'];
        foreach ($adminKeywords as $kw) {
            if (strpos($jabatanUpper, $kw) !== false) {
                $bup = 58;
                break;
            }
        }
    }

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
$query = "SELECT id, nm_pegawai, nip, tgl_lahir, status_pegawai, jabatan, unit_kerja, gelar_depan, gelar_belakang FROM pegawai WHERE status = '1' AND is_pensiun_synced = 1 ORDER BY nm_pegawai ASC";
$res = $conn->query($query);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $row['retirement'] = getRetirementDetails($row['tgl_lahir'], $row['jabatan'], $row['status_pegawai']);
        $employees[] = $row;
    }
}

// Fetch School Profile for Letterhead
$res_g = $conn->query("SELECT * FROM profils LIMIT 1");
$g = ($res_g && $res_g->num_rows > 0) ? $res_g->fetch_assoc() : [];
$base_dir = '../';
?>

<style>
    .card-modern {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
        background: #fff;
        margin-bottom: 20px;
    }

    .card-modern .card-header {
        padding: 16px 24px;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 12px;
        border: none;
        font-size: 1.1rem;
    }

    .header-blue {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    }

    .pensiun-table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 0.05em;
        vertical-align: middle;
        border-bottom: 2px solid #e2e8f0 !important;
    }

    .pensiun-table tbody td {
        font-size: 0.85rem;
        vertical-align: middle;
        padding: 0.75rem !important;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .card-modern {
            border: none !important;
            box-shadow: none !important;
        }

        .card-modern .card-header {
            display: none !important;
        }

        .pensiun-table thead th {
            background-color: #f1f5f9 !important;
            -webkit-print-color-adjust: exact;
        }

        @page {
            size: landscape;
            margin: 1cm;
        }

        body {
            background: white !important;
        }

        #page-content-wrapper {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }

        .kop-surat {
            display: block !important;
            margin-bottom: 20px;
            text-align: center;
        }
    }

    /* Kop Surat Styles */
    .kop-surat {
        display: none;
    }

    .kop-surat img {
        max-width: 100%;
        height: auto;
    }

    .kop-surat hr {
        display: none !important;
    }

    .kop-surat table,
    .kop-surat td,
    .kop-surat tr {
        border: none !important;
    }

    .progress-custom {
        height: 6px;
        border-radius: 10px;
        background-color: #f1f5f9;
        overflow: hidden;
    }
</style>

<div class="container-fluid py-4">
    <!-- Kop Dinas for Print -->
    <?php if (!empty($g['kop_dinas'])): ?>
        <div class="kop-surat">
            <?php echo str_replace('src="images/', 'src="' . $base_dir . 'images/', $g['kop_dinas']); ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="row mb-4 no-print">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-dark mb-1">Masa Kerja & Pensiun</h2>
                <p class="text-muted small mb-0">Estimasi batas usia pensiun pegawai aktif.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="print_pensiun.php" target="_blank" class="btn btn-outline-primary btn-rounded px-4 shadow-sm fw-bold">
                    <i class="las la-print me-2"></i> Cetak Laporan Pensiun
                </a>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card-modern shadow-sm border-0">
        <div class="card-header header-blue no-print">
            <i class="las la-hourglass-end"></i> Daftar Estimasi Pensiun Pegawai
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover pensiun-table mb-0" id="tablePensiun" style="width:100%;">
                <thead>
                    <tr>
                        <th class="text-center" width="50">#</th>
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
                            $full_name = (!empty($emp['gelar_depan']) ? $emp['gelar_depan'] . ' ' : '') . $emp['nm_pegawai'] . (!empty($emp['gelar_belakang']) ? ', ' . $emp['gelar_belakang'] : '');

                            $barClass = $ret['isRetired'] ? 'bg-danger' : ($ret['sisa'] == 'Bulan Ini' ? 'bg-warning' : 'bg-primary');
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?php echo $idx + 1; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($full_name); ?></div>
                                    <div class="text-muted extra-small">NIP: <?php echo $emp['nip'] ?: '-'; ?> |
                                        <?php echo $emp['status_pegawai']; ?></div>
                                </td>
                                <td>
                                    <div class="small fw-bold text-dark"><?php echo htmlspecialchars($emp['jabatan'] ?: '-'); ?>
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
                                        <span class="badge bg-secondary-soft text-muted rounded-pill px-3 extra-small">Tgl Lahir
                                            Kosong</span>
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
                                            <div class="progress-custom flex-grow-1">
                                                <div class="progress-bar <?php echo $barClass; ?>"
                                                    style="width: <?php echo $ret['percent']; ?>%; height: 100%;"></div>
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
            fixedColumns: {
                leftColumns: 2
            },
            paging: false,
            info: true,
            searching: true,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Cari pegawai..."
            }
        });
    });
</script>