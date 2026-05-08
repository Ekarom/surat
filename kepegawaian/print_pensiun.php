<?php
/**
 * Print Laporan Masa Kerja & Pensiun
 */

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

/**
 * Function to calculate retirement details
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

    $bup = 60;
    $jabatanUpper = strtoupper($jabatan);

    if (strpos($jabatanUpper, 'UTAMA') !== false || strpos($jabatanUpper, 'PROFESOR') !== false) {
        $bup = 65;
    } elseif (strpos($jabatanUpper, 'GURU') === false) {
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
$query = "SELECT id, nm_pegawai, nip, tgl_lahir, status_pegawai, jabatan, unit_kerja FROM pegawai WHERE status = '1' AND is_pensiun_synced = 1 ORDER BY nm_pegawai ASC";
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
$base_dir = file_exists('../dbconn.php') ? '../' : '';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Masa Kerja & Pensiun</title>
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
        <h2>Laporan Estimasi Masa Kerja & Pensiun Pegawai</h2>
        <p>Dicetak pada: <?php echo date('d-m-Y H:i'); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th>Nama Pegawai / NIP</th>
                <th>Jabatan / Unit Kerja</th>
                <th width="100">Tanggal Lahir</th>
                <th width="60">BUP</th>
                <th width="100">TMT Pensiun</th>
                <th width="120">Keterangan Sisa</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $idx => $emp): 
                $ret = $emp['retirement'];
                ?>
                <tr>
                    <td class="text-center"><?php echo $idx + 1; ?></td>
                    <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($emp['nm_pegawai']); ?></div>
                        <div style="font-size: 8pt; color: #555;">NIP: <?php echo $emp['nip'] ?: '-'; ?></div>
                    </td>
                    <td>
                        <div class="fw-bold"><?php echo htmlspecialchars($emp['jabatan'] ?: '-'); ?></div>
                        <div style="font-size: 8pt;"><?php echo htmlspecialchars($emp['unit_kerja'] ?: '-'); ?></div>
                    </td>
                    <td class="text-center">
                        <?php echo (!empty($emp['tgl_lahir']) && $emp['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($emp['tgl_lahir'])) : '-'; ?>
                    </td>
                    <td class="text-center"><?php echo $ret['bup']; ?></td>
                    <td class="text-center fw-bold" style="color: #0056b3;">
                        <?php echo $ret['tmt']; ?>
                    </td>
                    <td class="text-center">
                        <?php 
                        if ($ret['error']) echo "Data Lahir Kosong";
                        elseif ($ret['isRetired']) echo "PENSIUN";
                        else echo $ret['sisa'];
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
