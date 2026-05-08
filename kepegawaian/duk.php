<?php
/**
 * Daftar Urut Kepangkatan (DUK) - Seniority List
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

/**
 * Mapping for Golongan seniority
 */
function getGolonganScore($gol)
{
    $map = [
        'IV/e' => 17,
        'IV/d' => 16,
        'IV/c' => 15,
        'IV/b' => 14,
        'IV/a' => 13,
        'III/d' => 12,
        'III/c' => 11,
        'III/b' => 10,
        'III/a' => 9,
        'II/d' => 8,
        'II/c' => 7,
        'II/b' => 6,
        'II/a' => 5,
        'I/d' => 4,
        'I/c' => 3,
        'I/b' => 2,
        'I/a' => 1
    ];
    return $map[$gol] ?? 0;
}

/**
 * Mapping for Education seniority
 */
function getPendidikanScore($edu)
{
    $edu = strtoupper($edu);
    if (strpos($edu, 'S3') !== false)
        return 7;
    if (strpos($edu, 'S2') !== false)
        return 6;
    if (strpos($edu, 'S1') !== false || strpos($edu, 'D4') !== false)
        return 5;
    if (strpos($edu, 'D3') !== false)
        return 4;
    if (strpos($edu, 'D2') !== false)
        return 3;
    if (strpos($edu, 'D1') !== false)
        return 2;
    if (strpos($edu, 'SMA') !== false || strpos($edu, 'SMK') !== false || strpos($edu, 'MA') !== false)
        return 1;
    return 0;
}

// Fetch all active employees
$query = "SELECT * FROM pegawai WHERE status = '1'";
$res = $conn->query($query);
$employees = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $employees[] = $row;
    }
}

// Sorting logic
usort($employees, function ($a, $b) {
    // 1. Golongan (Higher first)
    $scoreA = getGolonganScore($a['golongan']);
    $scoreB = getGolonganScore($b['golongan']);
    if ($scoreA != $scoreB)
        return $scoreB <=> $scoreA;

    // 2. TMT Golongan (Earlier first)
    $tmtA = !empty($a['tmt_golongan']) && $a['tmt_golongan'] != '0000-00-00' ? $a['tmt_golongan'] : '9999-99-99';
    $tmtB = !empty($b['tmt_golongan']) && $b['tmt_golongan'] != '0000-00-00' ? $b['tmt_golongan'] : '9999-99-99';
    if ($tmtA != $tmtB)
        return $tmtA <=> $tmtB;

    // 3. Masa Kerja Total (Higher first)
    $mkA = ($a['masa_kerja_thn'] * 12) + $a['masa_kerja_bln'];
    $mkB = ($b['masa_kerja_thn'] * 12) + $b['masa_kerja_bln'];
    if ($mkA != $mkB)
        return $mkB <=> $mkA;

    // 4. Pendidikan (Higher first)
    $eduA = getPendidikanScore($a['pendidikan']);
    $eduB = getPendidikanScore($b['pendidikan']);
    if ($eduA != $eduB)
        return $eduB <=> $eduA;

    // 5. Usia (Older first -> Earlier birth date)
    $birthA = !empty($a['tgl_lahir']) && $a['tgl_lahir'] != '0000-00-00' ? $a['tgl_lahir'] : '9999-99-99';
    $birthB = !empty($b['tgl_lahir']) && $b['tgl_lahir'] != '0000-00-00' ? $b['tgl_lahir'] : '9999-99-99';
    return $birthA <=> $birthB;
});
?>
<!-- Add this line to include the CSS for fixed columns -->
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/3.2.6/css/fixedColumns.dataTables.min.css">
<?php
// Fetch School Profile
$res_g = $conn->query("SELECT * FROM profils LIMIT 1");
$g = ($res_g && $res_g->num_rows > 0) ? $res_g->fetch_assoc() : [];
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

    .header-slate {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    }

    .duk-table thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.65rem;
        letter-spacing: 0.05em;
        vertical-align: middle;
        border-bottom: 2px solid #e2e8f0 !important;
    }

    .duk-table tbody td {
        font-size: 0.85rem;
        vertical-align: middle;
        padding: 0.75rem !important;
    }

    .seniority-badge {
        font-size: 0.7rem;
        padding: 0.2rem 0.6rem;
        border-radius: 50px;
        font-weight: 600;
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

        .duk-table thead th {
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

        .no-print-kop {
            display: none !important;
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
                <h2 class="fw-bold text-dark mb-1">Daftar Urut Kepangkatan (DUK)</h2>
                <p class="text-muted small mb-0">Urutan senioritas pegawai berdasarkan pangkat dan masa kerja.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="print_duk.php" target="_blank" class="btn btn-outline-primary btn-rounded px-4 shadow-sm fw-bold">
                    <i class="las la-print me-2"></i> Cetak Laporan DUK
                </a>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card-modern shadow-sm border-0">
        <div class="card-header header-slate no-print">
            <i class="las la-sort-amount-down"></i> Senioritas Pegawai Terurut
        </div>
        <div class="card-body p-0">
            <table class="table table-striped table-hover duk-table mb-0" id="tableDUK" style="width:100%;">
                <thead>
                    <tr class="text-center">
                        <th width="40">No</th>
                        <th class="text-start">Nama / NIP / NRK</th>
                        <th>Gol</th>
                        <th>TMT Gol</th>
                        <th>Jabatan</th>
                        <th>Masa Kerja</th>
                        <th>Pendidikan</th>
                        <th>Tgl Lahir</th>
                        <th>Usia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    foreach ($employees as $p):
                        $full_name = (!empty($p['gelar_depan']) ? $p['gelar_depan'] . ' ' : '') . $p['nm_pegawai'] . (!empty($p['gelar_belakang']) ? ', ' . $p['gelar_belakang'] : '');

                        // Calculate Age
                        $usia = '-';
                        if (!empty($p['tgl_lahir']) && $p['tgl_lahir'] != '0000-00-00') {
                            $birthDate = new DateTime($p['tgl_lahir']);
                            $today = new DateTime();
                            $usia = $today->diff($birthDate)->y . ' Th';
                        }
                        ?>
                        <tr>
                            <td class="text-center fw-bold text-muted"><?php echo $no++; ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($full_name); ?></div>
                                <div class="extra-small text-muted">
                                    NIP: <?php echo $p['nip'] ?: '-'; ?> | NRK: <?php echo $p['nrk'] ?: '-'; ?>
                                </div>
                            </td>
                            <td class="text-center">
                                <span
                                    class="badge bg-soft-primary seniority-badge"><?php echo $p['golongan'] ?: '-'; ?></span>
                            </td>
                            <td class="text-center small">
                                <?php echo (!empty($p['tmt_golongan']) && $p['tmt_golongan'] != '0000-00-00') ? date('d/m/Y', strtotime($p['tmt_golongan'])) : '-'; ?>
                            </td>
                            <td>
                                <div class="small fw-medium"><?php echo htmlspecialchars($p['jabatan'] ?: '-'); ?></div>
                            </td>
                            <td class="text-center small">
                                <?php echo ($p['masa_kerja_thn'] ?: 0) . ' Th ' . ($p['masa_kerja_bln'] ?: 0) . ' Bl'; ?>
                            </td>
                            <td class="text-center small">
                                <?php echo htmlspecialchars($p['pendidikan'] ?: '-'); ?>
                            </td>
                            <td class="text-center small">
                                <?php echo (!empty($p['tgl_lahir']) && $p['tgl_lahir'] != '0000-00-00') ? date('d/m/Y', strtotime($p['tgl_lahir'])) : '-'; ?>
                            </td>
                            <td class="text-center fw-bold text-primary">
                                <?php echo $usia; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($employees)): ?>
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">Data tidak tersedia.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
        });
    });
</script>