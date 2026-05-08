<?php
/**
 * Print Laporan Kenaikan Pangkat
 */

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

/**
 * Function to calculate promotion details
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
    if ($passedInterval->invert)
        $passedDays = 0;

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

// Fetch School Profile for Letterhead (Optional based on previous request, but usually needed for print)
$res_g = $conn->query("SELECT * FROM profils LIMIT 1");
$g = ($res_g && $res_g->num_rows > 0) ? $res_g->fetch_assoc() : [];
$base_dir = file_exists('../dbconn.php') ? '../' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Kenaikan Pangkat</title>
    <style>
        body { font-family: 'Arial', sans-serif; line-height: 1.4; color: #333; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 5px 0; text-transform: uppercase; font-size: 16pt; }
        .header p { margin: 0; font-size: 10pt; color: #666; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 9pt; }
        th, td { border: 1px solid #000; padding: 8px 5px; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        
        @media print {
            @page { size: landscape; margin: 1cm; }
            .no-print { display: none; }
            body { padding: 0; }
        }
        
        .kop-surat { margin-bottom: 20px; border: none !important; }
        .kop-surat img { max-width: 100%; height: auto; }
        .kop-surat hr { display: none !important; }
        .kop-surat table, .kop-surat td, .kop-surat tr { border: none !important; }
    </style>
</head>
<body onload="window.print()">

    <!-- Kop Dinas -->
    <?php if (!empty($g['kop_dinas'])): ?>
        <div class="kop-surat text-center">
            <?php echo str_replace('src="images/', 'src="' . $base_dir . 'images/', $g['kop_dinas']); ?>
        </div>
    <?php endif; ?>

    <div class="header">
        <h2>Laporan Estimasi Kenaikan Pangkat Pegawai</h2>
        <p>Dicetak pada: <?php echo date('d-m-Y H:i'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama Pegawai / NIP</th>
                <th>Pangkat / Golongan</th>
                <th>Jabatan</th>
                <th width="100">TMT Golongan</th>
                <th width="100">Estimasi Naik</th>
                <th width="120">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $idx => $emp): 
                $promo = $emp['promotion'];
                ?>
                <tr>
                    <td class="text-center"><?php echo $idx + 1; ?></td>
                    <td>
                        <div class="fw-bold">
                            <?php 
                            $full_name = (!empty($emp['gelar_depan']) ? $emp['gelar_depan'] . ' ' : '') . $emp['nm_pegawai'] . (!empty($emp['gelar_belakang']) ? ', ' . $emp['gelar_belakang'] : '');
                            echo htmlspecialchars($full_name); 
                            ?>
                        </div>
                        <div style="font-size: 8pt; color: #555;">NIP: <?php echo $emp['nip'] ?: '-'; ?></div>
                    </td>
                    <td class="text-center">
                        <?php echo htmlspecialchars($emp['pangkat'] ?: '-'); ?><br>
                        (<?php echo htmlspecialchars($emp['golongan'] ?: '-'); ?>)
                    </td>
                    <td><?php echo htmlspecialchars($emp['jabatan'] ?: '-'); ?></td>
                    <td class="text-center">
                        <?php echo (!empty($emp['tmt_golongan']) && $emp['tmt_golongan'] != '0000-00-00') ? date('d-m-Y', strtotime($emp['tmt_golongan'])) : '-'; ?>
                    </td>
                    <td class="text-center fw-bold" style="color: #0056b3;">
                        <?php echo $promo['next_promotion']; ?>
                    </td>
                    <td class="text-center">
                        <?php 
                        if ($promo['error']) echo "TMT Kosong";
                        elseif ($promo['isDue']) echo "WAKTUNYA NAIK";
                        else echo $promo['sisa'];
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top: 30px; text-align: right; font-size: 10pt; padding-right: 50px;">
        Jakarta, <?php echo date('d F Y'); ?><br>
        Mengetahui,<br><br><br><br>
        <strong>(..........................................)</strong>
    </div>


</body>
</html>
