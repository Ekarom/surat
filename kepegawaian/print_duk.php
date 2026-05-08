<?php
/**
 * Print Laporan Daftar Urut Kepangkatan (DUK)
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

/**
 * Mapping for Golongan seniority
 */
function getGolonganScore($gol) {
    $map = [
        'IV/e' => 17, 'IV/d' => 16, 'IV/c' => 15, 'IV/b' => 14, 'IV/a' => 13,
        'III/d' => 12, 'III/c' => 11, 'III/b' => 10, 'III/a' => 9,
        'II/d' => 8, 'II/c' => 7, 'II/b' => 6, 'II/a' => 5,
        'I/d' => 4, 'I/c' => 3, 'I/b' => 2, 'I/a' => 1
    ];
    return $map[$gol] ?? 0;
}

/**
 * Mapping for Education seniority
 */
function getPendidikanScore($edu) {
    $edu = strtoupper($edu);
    if (strpos($edu, 'S3') !== false) return 7;
    if (strpos($edu, 'S2') !== false) return 6;
    if (strpos($edu, 'S1') !== false || strpos($edu, 'D4') !== false) return 5;
    if (strpos($edu, 'D3') !== false) return 4;
    if (strpos($edu, 'D2') !== false) return 3;
    if (strpos($edu, 'D1') !== false) return 2;
    if (strpos($edu, 'SMA') !== false || strpos($edu, 'SMK') !== false || strpos($edu, 'MA') !== false) return 1;
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
usort($employees, function($a, $b) {
    // 1. Golongan (Higher first)
    $scoreA = getGolonganScore($a['golongan']);
    $scoreB = getGolonganScore($b['golongan']);
    if ($scoreA != $scoreB) return $scoreB <=> $scoreA;

    // 2. TMT Golongan (Earlier first)
    $tmtA = !empty($a['tmt_golongan']) && $a['tmt_golongan'] != '0000-00-00' ? $a['tmt_golongan'] : '9999-99-99';
    $tmtB = !empty($b['tmt_golongan']) && $b['tmt_golongan'] != '0000-00-00' ? $b['tmt_golongan'] : '9999-99-99';
    if ($tmtA != $tmtB) return $tmtA <=> $tmtB;

    // 3. Masa Kerja Total (Higher first)
    $mkA = ($a['masa_kerja_thn'] * 12) + $a['masa_kerja_bln'];
    $mkB = ($b['masa_kerja_thn'] * 12) + $b['masa_kerja_bln'];
    if ($mkA != $mkB) return $mkB <=> $mkA;

    // 4. Pendidikan (Higher first)
    $eduA = getPendidikanScore($a['pendidikan']);
    $eduB = getPendidikanScore($b['pendidikan']);
    if ($eduA != $eduB) return $eduB <=> $eduA;

    // 5. Usia (Older first -> Earlier birth date)
    $birthA = !empty($a['tgl_lahir']) && $a['tgl_lahir'] != '0000-00-00' ? $a['tgl_lahir'] : '9999-99-99';
    $birthB = !empty($b['tgl_lahir']) && $b['tgl_lahir'] != '0000-00-00' ? $b['tgl_lahir'] : '9999-99-99';
    return $birthA <=> $birthB;
});

// Fetch School Profile
$res_g = $conn->query("SELECT * FROM profils LIMIT 1");
$g = ($res_g && $res_g->num_rows > 0) ? $res_g->fetch_assoc() : [];
$base_dir = '../';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Daftar Urut Kepangkatan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 9pt; margin: 0; padding: 20px; }
        .kop-surat { text-align: center; margin-bottom: 20px; }
        .kop-surat img { max-width: 100%; height: auto; }
        .kop-surat table, .kop-surat td, .kop-surat tr { border: none !important; }
        .kop-surat hr { display: none !important; }

        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 5px 0; text-transform: uppercase; font-size: 14pt; }
        .header p { margin: 0; font-size: 10pt; }

        table.main-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.main-table th, table.main-table td { border: 1px solid black; padding: 6px 4px; }
        table.main-table th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 8pt; }
        .text-center { text-align: center; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }

        @media print {
            @page { size: landscape; margin: 1cm; }
            body { padding: 0; }
        }

        .footer { margin-top: 30px; float: right; width: 250px; text-align: center; }
    </style>
</head>
<body onload="window.print()">

    <!-- Kop Dinas -->
    <?php if (!empty($g['kop_dinas'])): ?>
        <div class="kop-surat">
            <?php echo str_replace('src="images/', 'src="' . $base_dir . 'images/', $g['kop_dinas']); ?>
        </div>
    <?php endif; ?>

    <div class="header">
        <h2>DAFTAR URUT KEPANGKATAN PEGAWAI</h2>
        <p>Keadaan Per: <?php echo date('d F Y'); ?></p>
    </div>

    <table class="main-table">
        <thead>
            <tr class="text-center">
                <th width="30">NO</th>
                <th>NAMA / NIP / NRK</th>
                <th width="40">GOL</th>
                <th width="80">TMT GOL</th>
                <th>JABATAN</th>
                <th width="80">MASA KERJA</th>
                <th>PENDIDIKAN</th>
                <th width="80">TGL LAHIR</th>
                <th width="40">USIA</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            foreach ($employees as $p): 
                $full_name = (!empty($p['gelar_depan']) ? $p['gelar_depan'] . ' ' : '') . $p['nm_pegawai'] . (!empty($p['gelar_belakang']) ? ', ' . $p['gelar_belakang'] : '');
                $usia = '-';
                if (!empty($p['tgl_lahir']) && $p['tgl_lahir'] != '0000-00-00') {
                    $birthDate = new DateTime($p['tgl_lahir']);
                    $today = new DateTime();
                    $usia = $today->diff($birthDate)->y;
                }
            ?>
            <tr>
                <td class="text-center"><?php echo $no++; ?></td>
                <td>
                    <div class="fw-bold"><?php echo htmlspecialchars($full_name); ?></div>
                    <div style="font-size: 7pt;">NIP: <?php echo $p['nip'] ?: '-'; ?> | NRK: <?php echo $p['nrk'] ?: '-'; ?></div>
                </td>
                <td class="text-center"><?php echo $p['golongan'] ?: '-'; ?></td>
                <td class="text-center"><?php echo (!empty($p['tmt_golongan']) && $p['tmt_golongan'] != '0000-00-00') ? date('d-m-Y', strtotime($p['tmt_golongan'])) : '-'; ?></td>
                <td><?php echo htmlspecialchars($p['jabatan'] ?: '-'); ?></td>
                <td class="text-center"><?php echo ($p['masa_kerja_thn'] ?: 0) . ' th ' . ($p['masa_kerja_bln'] ?: 0) . ' bl'; ?></td>
                <td><?php echo htmlspecialchars($p['pendidikan'] ?: '-'); ?></td>
                <td class="text-center"><?php echo (!empty($p['tgl_lahir']) && $p['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($p['tgl_lahir'])) : '-'; ?></td>
                <td class="text-center"><?php echo $usia; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer">
        Jakarta, <?php echo date('d F Y'); ?><br>
        Mengetahui,<br>
        Kepala Sekolah<br><br><br><br><br>
        <strong>(..........................................)</strong><br>
        NIP. ......................................
    </div>

</body>
</html>
