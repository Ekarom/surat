<?php
/**
 * Data Saya - Teacher Portal (Full Administrative Data)
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$base_dir = file_exists('dbconn.php') ? '' : '../';

$id_pegawai = $_SESSION['id'] ?? 0;
$level_user = $_SESSION['level'] ?? '';

// Check if an ID is passed in the URL (e.g. for Admins viewing a profile)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $requested_id = $_GET['id'];

    // SECURITY: Guru (Level 4) can only view their own ID
    if ($level_user == '4' && $requested_id != $id_pegawai) {
        echo "
        <div class='container-fluid py-4'>
            <div class='alert alert-danger shadow-sm rounded-4 p-4'>
                <div class='d-flex align-items-center mb-2'>
                    <i class='las la-exclamation-triangle fa-2x me-3'></i>
                    <h4 class='mb-0 fw-bold'>Akses Ditolak</h4>
                </div>
                <p class='mb-0'>Anda hanya dapat melihat data Anda sendiri!</p>
                <hr>
                <a href='?data_saya' class='btn btn-outline-danger btn-sm'>Kembali ke Data Saya</a>
            </div>
        </div>";
        return;
    }

    // Admins can override the session ID with the GET parameter
    $id_pegawai = $requested_id;
}

$query = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$query->bind_param("i", $id_pegawai);
$query->execute();
$pegawai = $query->get_result()->fetch_assoc();

if (!$pegawai) {
    echo "<div class='container-fluid py-4'><div class='alert alert-danger shadow-sm rounded-4'>Data tidak ditemukan.</div></div>";
    return;
}

$poto_db = $pegawai['foto'] ?? '';
$src_foto = (!empty($poto_db) && file_exists("../file/datakepegawaian/" . $poto_db)) ? "../file/datakepegawaian/" . $poto_db : "../images/default.png";

// Fetch Appointment Data (Earliest record)
$stmt_app = $conn->prepare("SELECT no_sk, tmt FROM riwayat_kepegawaian 
    WHERE pegawai_id = ? 
    AND (kategori IN ('Pangkat', 'Jabatan') OR deskripsi LIKE '%Pengangkatan%' OR deskripsi LIKE '%CPNS%' OR deskripsi LIKE '%PPPK%') 
    ORDER BY tmt ASC LIMIT 1");
$stmt_app->bind_param("i", $id_pegawai);
$stmt_app->execute();
$appointment = $stmt_app->get_result()->fetch_assoc();

// Calculate Retirement
function calculateRetirement($tglLahir, $jabatan)
{
    if (!$tglLahir || $tglLahir == '0000-00-00')
        return null;
    $bup = 60; // Default
    $jabatanUpper = strtoupper($jabatan);
    if (strpos($jabatanUpper, 'UTAMA') !== false || strpos($jabatanUpper, 'PROFESOR') !== false) {
        $bup = 65;
    }
    $tglLahirObj = new DateTime($tglLahir);
    $pensiunDate = clone $tglLahirObj;
    $pensiunDate->modify("+$bup years");
    $pensiunDate->modify("first day of next month");
    return [
        'bup' => $bup,
        'tmt' => $pensiunDate->format('d-m-Y'),
        'tahun' => $pensiunDate->format('Y')
    ];
}
$retirement = calculateRetirement($pegawai['tgl_lahir'], $pegawai['jabatan']);

// Fetch All History Data
$stmt_hist = $conn->prepare("SELECT * FROM riwayat_kepegawaian WHERE pegawai_id = ? ORDER BY tmt DESC");
$stmt_hist->bind_param("i", $id_pegawai);
$stmt_hist->execute();
$history_all = $stmt_hist->get_result();
$hist_rows = [];
while ($row = $history_all->fetch_assoc()) {
    $hist_rows[] = $row;
}

// Fetch School Profile for Letterhead
$res_g = $conn->query("SELECT * FROM profils LIMIT 1");
$g = $res_g->fetch_assoc();
?>

<style>
    .card-modern {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        background: #fff;
        margin-bottom: 24px;
        transition: transform 0.2s;
    }

    .card-modern:hover {
        transform: translateY(-2px);
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

    .header-red {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    .header-slate {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
    }

    .form-group-info {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        align-items: center;
    }

    .form-group-info:last-child {
        border-bottom: none;
    }

    .info-label {
        width: 220px;
        font-weight: 700;
        color: #64748b;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        flex: 1;
        color: #1e293b;
        background: #f8fafc;
        padding: 10px 16px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.2s;
    }

    .info-value:hover {
        background: #fff;
        border-color: #3b82f6;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .info-value.required::after {
        content: ' *';
        color: #ef4444;
        font-weight: bold;
    }

    .border-top {
        border-top: 2px solid #f1f5f9 !important;
    }

    .text-uppercase {
        letter-spacing: 1px;
    }

    .photo-display-container {
        padding: 24px;
        text-align: center;
    }

    .photo-frame {
        width: 100%;
        max-width: 250px;
        aspect-ratio: 3/4;
        object-fit: cover;
        border-radius: 12px;
        border: 4px solid #fff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .photo-frame:hover {
        transform: scale(1.03);
    }

    .btn-action-group {
        margin-top: 24px;
    }

    .btn-action-group .btn {
        border-radius: 10px;
        padding: 10px 24px;
        font-weight: 700;
        transition: all 0.2s;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
        box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
    }

    @media (max-width: 768px) {
        .form-group-info {
            flex-direction: column;
            align-items: flex-start;
            padding: 16px 0;
        }

        .info-label {
            width: 100%;
            margin-bottom: 8px;
            color: #94a3b8;
        }

        .info-value {
            width: 100%;
        }
    }

    }

    /* Print Table Styles - Outside media print for modal visibility */
    .print-table-section {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    .print-table-section td {
        padding: 5px 8px;
        border: 1px solid #000;
    }

    .print-table-section .label {
        background: #f8fafc;
        width: 200px;
        font-weight: bold;
    }

    /* Print Styles */
    @media print {
        body {
            visibility: hidden;
            -webkit-print-color-adjust: exact;
        }

        /* Keep the parent chain visible but hide their specific UI styles */
        #wrapper,
        #page-content-wrapper,
        .container-fluid {
            visibility: visible !important;
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            background: none !important;
        }

        /* Show the printable container and its children */
        #printableProfile,
        #printableProfile * {
            visibility: visible !important;
        }

        #printableProfile {
            display: block !important;
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            padding: 1cm 1.5cm !important;
            background: #fff !important;
            font-family: "Times New Roman", Times, serif;
            color: #000 !important;
            line-height: 1.4;
        }

        .print-section-header {
            background: #f3f4f6 !important;
            padding: 6px 12px;
            border: 1px solid #000;
            margin: 20px 0 10px 0;
            font-weight: bold;
            font-size: 11pt;
            text-transform: uppercase;
        }

        .print-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5pt;
        }

        .print-table td {
            padding: 4px 0;
            vertical-align: top;
        }

        .print-table .label {
            width: 200px;
            font-weight: bold;
        }

        .print-table .colon {
            width: 20px;
        }

        /* Explicitly hide all other major UI components */
        .navbar,
        #sidebar-wrapper,
        .btn-action-group,
        .card-modern,
        footer,
        .modal {
            display: none !important;
        }
    }

    #printableProfile {
        display: none;
    }
</style>

<div class="container-fluid py-4">
    <div class="row">
        <!-- Left Column: Data Pribadi -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header header-blue">
                    <i class="las la-address-card"></i> Data Pribadi Pegawai
                </div>
                <div class="card-body p-4">
                    <div class="form-group-info">
                        <div class="info-label">Nomor Induk Pegawai</div>
                        <div class="info-value"><?php echo htmlspecialchars(($pegawai['nip'] ?? '') ?: '-'); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Nomor Registrasi (NRK)</div>
                        <div class="info-value"><?php echo htmlspecialchars(($pegawai['nrk'] ?? '') ?: '-'); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">NUPTK</div>
                        <div class="info-value"><?php echo htmlspecialchars(($pegawai['nuptk'] ?? '') ?: '-'); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-value required"><?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Jenis Kelamin</div>
                        <div class="info-value required">
                            <?php echo ($pegawai['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Tempat Lahir</div>
                        <div class="info-value required">
                            <?php echo htmlspecialchars(($pegawai['tempat_lahir'] ?? '') ?: '-'); ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Agama</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars(($pegawai['agama'] ?? '') ?: '-'); ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Tanggal Lahir</div>
                        <div class="info-value required">
                            <?php echo (!empty($pegawai['tgl_lahir']) && $pegawai['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($pegawai['tgl_lahir'])) : '-'; ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Pendidikan Terakhir</div>
                        <div class="info-value"><?php echo htmlspecialchars($pegawai['pendidikan'] ?? ''); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Alamat</div>
                        <div class="info-value"><?php echo htmlspecialchars($pegawai['alamat'] ?? ''); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Nama Ibu Kandung</div>
                        <div class="info-value"><?php echo htmlspecialchars(($pegawai['nama_ibu'] ?? '') ?: '-'); ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Suami / Istri</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars(($pegawai['nama_pasangan'] ?? '') ?: '-'); ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">NPWP</div>
                        <div class="info-value"><?php echo htmlspecialchars(($pegawai['npwp'] ?? '') ?: '-'); ?></div>
                    </div>

                    <div class="btn-action-group d-flex gap-2">
                        <button class="btn btn-primary px-4 fw-bold" id="btnEditProfil">
                            <i class="las la-edit me-2"></i> Perbarui Data
                        </button>
                        <button class="btn btn-outline-dark px-4 fw-bold" data-bs-toggle="modal"
                            data-bs-target="#modalCetak">
                            <i class="las la-print me-2"></i> Cetak Bio-Data
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-modern">
                <div class="card-header header-slate">
                    <i class="las la-briefcase"></i> Data Rinci Kepegawaian
                </div>
                <div class="card-body p-4">
                    <div class="form-group-info">
                        <div class="info-label"><i class="las la-id-badge me-2"></i> Jabatan Saat Ini</div>
                        <div class="info-value fw-bold">
                            <?php echo htmlspecialchars(($pegawai['jabatan'] ?? '') ?: '-'); ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label"><i class="las la-layer-group me-2"></i> Pangkat / Golongan
                        </div>
                        <div class="info-value">
                            <?php echo htmlspecialchars(($pegawai['pangkat'] ?? '') ?: '-') . " (" . htmlspecialchars(($pegawai['golongan'] ?? '') ?: '-') . ")"; ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label"><i class="lar la-calendar-check me-2"></i> TMT Golongan
                        </div>
                        <div class="info-value">
                            <?php echo (!empty($pegawai['tmt_golongan']) && $pegawai['tmt_golongan'] != '0000-00-00') ? date('d-m-Y', strtotime($pegawai['tmt_golongan'])) : '-'; ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label"><i class="las la-school me-2"></i> Unit Kerja</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars(($pegawai['unit_kerja'] ?? '') ?: 'SMP Negeri 171 Jakarta'); ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label"><i class="las la-user-tag me-2"></i> Status Pegawai</div>
                        <div class="info-value">
                            <span class="badge <?php
                            $sp = strtolower($pegawai['status_pegawai'] ?? '');
                            echo (strpos($sp, 'pns') !== false) ? 'bg-primary' : (strpos($sp, 'pppk') !== false ? 'bg-info' : 'bg-secondary');
                            ?>">
                                <?php echo htmlspecialchars($pegawai['status_pegawai'] ?: '-'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold mb-3 text-muted small text-uppercase"><i class="las la-id-card me-2"></i>
                            Dokumen Kepegawaian</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-group-info border-0 py-1">
                                    <div class="info-label" style="width: 140px;">No. Karpeg</div>
                                    <div class="info-value py-1 px-2 small">
                                        <?php echo htmlspecialchars(($pegawai['no_karpeg'] ?? '') ?: '-'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-info border-0 py-1">
                                    <div class="info-label" style="width: 140px;">No. Taspen</div>
                                    <div class="info-value py-1 px-2 small">
                                        <?php echo htmlspecialchars(($pegawai['no_taspen'] ?? '') ?: '-'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-info border-0 py-1">
                                    <div class="info-label" style="width: 140px;">No. BPJS</div>
                                    <div class="info-value py-1 px-2 small">
                                        <?php echo htmlspecialchars(($pegawai['no_bpjs'] ?? '') ?: '-'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-info border-0 py-1">
                                    <div class="info-label" style="width: 140px;">Karis / Karsu</div>
                                    <div class="info-value py-1 px-2 small">
                                        <?php echo htmlspecialchars(($pegawai['no_karis_karsu'] ?? '') ?: '-'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-info border-0 py-1">
                                    <div class="info-label" style="width: 140px;">Masa Kerja</div>
                                    <div class="info-value py-1 px-2 small">
                                        <?php echo ($pegawai['masa_kerja_thn'] ?: '0') . ' Thn ' . ($pegawai['masa_kerja_bln'] ?: '0') . ' Bln'; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group-info border-0 py-1">
                                    <div class="info-label" style="width: 140px;">Gaji Pokok</div>
                                    <div class="info-value py-1 px-2 small fw-bold text-primary">Rp.
                                        <?php echo number_format($pegawai['gaji_pokok'] ?: 0, 0, ',', '.'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Integrated Appointment & Retirement Info -->
                    <div class="mt-4 pt-3 border-top">
                        <h6 class="fw-bold mb-3 text-muted small text-uppercase"><i class="las la-history me-2"></i>
                            Pengangkatan & Pensiun</h6>
                        <div class="form-group-info">
                            <div class="info-label">SK Pengangkatan Pertama</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars(($appointment['no_sk'] ?? '') ?: '-'); ?>
                            </div>
                        </div>
                        <div class="form-group-info">
                            <div class="info-label">TMT Pengangkatan</div>
                            <div class="info-value">
                                <?php echo (!empty($appointment['tmt'] ?? '') && $appointment['tmt'] != '0000-00-00') ? date('d-m-Y', strtotime($appointment['tmt'])) : '-'; ?>
                            </div>
                        </div>
                        <div class="form-group-info">
                            <div class="info-label">Estimasi Pensiun</div>
                            <div class="info-value">
                                <span class="fw-bold text-danger"><?php echo $retirement['tmt'] ?? "-"; ?></span>
                                <span class="small text-muted ms-2">(BUP <?php echo $retirement['bup'] ?? "-"; ?>
                                    Tahun)</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-modern">
                <div class="card-header header-red">
                    <i class="las la-tasks"></i> Monitoring Berkas & Riwayat
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Kategori</th>
                                    <th>Keterangan</th>
                                    <th>TMT / Tgl</th>
                                    <th class="text-center">Berkas</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($hist_rows)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">Belum ada data riwayat
                                            terunggah.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($hist_rows as $h): ?>
                                        <tr>
                                            <td class="ps-4"><span
                                                    class="badge bg-light text-dark border"><?php echo htmlspecialchars($h['kategori'] ?? ''); ?></span>
                                            </td>
                                            <td>
                                                <div class="fw-bold small">
                                                    <?php echo htmlspecialchars($h['deskripsi'] ?? ''); ?>
                                                </div>
                                                <?php if ($h['institusi']): ?>
                                                    <div class="text-muted" style="font-size: 0.75rem;">
                                                        <?php echo htmlspecialchars($h['institusi'] ?? ''); ?>
                                                    </div><?php endif; ?>
                                            </td>
                                            <td class="small">
                                                <?php echo (!empty($h['tmt']) && $h['tmt'] != '0000-00-00') ? date('d-m-Y', strtotime($h['tmt'])) : '-'; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if (!empty($h['file_lampiran'])): ?>
                                                    <a href="../file/datakepegawaian/<?php echo $h['file_lampiran']; ?>"
                                                        target="_blank" class="btn btn-sm btn-outline-danger py-0 px-2">
                                                        <i class="las la-file-pdf"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-danger small"><i class="las la-times-circle"></i></span>
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
        </div>

        <!-- Right Column: Foto & Kontak -->
        <div class="col-lg-4">
            <div class="card-modern">
                <div class="card-header header-red">
                    <i class="las la-camera"></i> Identitas Visual
                </div>
                <div class="photo-display-container">
                    <img src="<?php echo $src_foto; ?>" class="photo-frame" alt="Foto Profil">
                    <div class="mt-4">
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?></h5>
                        <p class="text-muted small">NIP. <?php echo htmlspecialchars(($pegawai['nip'] ?? '') ?: '-'); ?>
                        </p>
                        <hr>
                        <div class="text-start">
                            <div class="d-flex align-items-center mb-3">
                                <i class="las la-phone-alt text-primary me-3"></i>
                                <div>
                                    <div class="small text-muted">Nomor Telepon</div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($pegawai['no_hp'] ?? ''); ?></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="las la-envelope text-primary me-3"></i>
                                <div>
                                    <div class="small text-muted">Alamat Email</div>
                                    <div class="fw-bold"><?php echo htmlspecialchars($pegawai['email'] ?? ''); ?></div>
                                </div>
                            </div>
                            <div class="d-flex align-items-center mb-3">
                                <i class="las la-map-marker-alt text-primary me-3"></i>
                                <div>
                                    <div class="small text-muted">Domisili</div>
                                    <div class="fw-bold">
                                        <?php echo htmlspecialchars(($pegawai['alamat'] ?? '') ?: '-'); ?>
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

<!-- === MODAL: EDIT DATA === -->
<div class="modal fade" id="modalEditProfil" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perbarui Data Saya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditProfil" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id" value="<?php echo $id_pegawai; ?>">
                    <input type="hidden" name="foto_lama" id="edit_foto_lama" value="<?php echo $poto_db; ?>">
                    <input type="hidden" name="nip" id="edit_nip"
                        value="<?php echo htmlspecialchars($pegawai['nip'] ?? ''); ?>">

                    <div class="row g-4">
                        <!-- Photo Section -->
                        <div class="col-md-4 text-center">
                            <div class="position-relative d-inline-block">
                                <img id="preview-foto-edit" src="<?php echo $src_foto; ?>" class="rounded shadow-sm"
                                    style="width: 150px; height: 200px; object-fit: cover; border: 1px solid #e2e8f0;">
                                <label for="foto_edit"
                                    class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 38px; height: 38px; cursor: pointer; border: 3px solid #fff; margin-bottom: -10px; margin-right: -10px;">
                                    <i class="las la-camera"></i>
                                </label>
                                <input type="file" id="foto_edit" name="foto" class="d-none" accept="image/*">
                            </div>
                        </div>

                        <!-- Info Section -->
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="form-label small fw-bold">Nama Lengkap (*)</label>
                                    <input type="text" name="nm_pegawai" id="edit_nm_pegawai" class="form-control"
                                        required value="<?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Tempat Lahir (*)</label>
                                    <input type="text" name="tempat_lahir" id="edit_tempat_lahir" class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['tempat_lahir'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Tanggal Lahir (*)</label>
                                    <input type="date" name="tgl_lahir" id="edit_tgl_lahir" class="form-control"
                                        value="<?php echo $pegawai['tgl_lahir'] ?? ''; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Jenis Kelamin (*)</label>
                                    <select name="jenis_kelamin" id="edit_jenis_kelamin" class="form-select">
                                        <option value="L" <?php echo ($pegawai['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>Laki-laki</option>
                                        <option value="P" <?php echo ($pegawai['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Pendidikan</label>
                                    <input type="text" name="pendidikan" id="edit_pendidikan" class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['pendidikan'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">NUPTK</label>
                                    <input type="text" name="nuptk" id="edit_nuptk" class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['nuptk'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Agama</label>
                                    <select name="agama" id="edit_agama" class="form-select">
                                        <option value="">- Pilih Agama -</option>
                                        <?php
                                        $agamas = ['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];
                                        foreach ($agamas as $a):
                                            $sel = ($pegawai['agama'] == $a) ? 'selected' : '';
                                            echo "<option value='$a' $sel>$a</option>";
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">NPWP</label>
                                    <input type="text" name="npwp" id="edit_npwp" class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['npwp'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Nama Ibu Kandung</label>
                                    <input type="text" name="nama_ibu" id="edit_nama_ibu" class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['nama_ibu'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Nama Suami/Istri</label>
                                    <input type="text" name="nama_pasangan" id="edit_nama_pasangan" class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['nama_pasangan'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">No. Karpeg</label>
                                    <input type="text" name="no_karpeg" id="edit_no_karpeg" class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['no_karpeg'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">No. Taspen</label>
                                    <input type="text" name="no_taspen" id="edit_no_taspen" class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['no_taspen'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">No. BPJS</label>
                                    <input type="text" name="no_bpjs" id="edit_no_bpjs" class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['no_bpjs'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">No. Karis/Karsu</label>
                                    <input type="text" name="no_karis_karsu" id="edit_no_karis_karsu"
                                        class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['no_karis_karsu'] ?? ''); ?>">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Masa Kerja (Thn/Bln)</label>
                                    <div class="input-group">
                                        <input type="number" name="masa_kerja_thn" id="edit_masa_kerja_thn"
                                            class="form-control" value="<?php echo $pegawai['masa_kerja_thn']; ?>"
                                            placeholder="Th">
                                        <input type="number" name="masa_kerja_bln" id="edit_masa_kerja_bln"
                                            class="form-control" value="<?php echo $pegawai['masa_kerja_bln']; ?>"
                                            placeholder="Bl">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Gaji Pokok</label>
                                    <input type="text" name="gaji_pokok" id="edit_gaji_pokok" class="form-control"
                                        value="<?php echo number_format($pegawai['gaji_pokok'] ?: 0, 0, ',', '.'); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Contact Section -->
                        <div class="col-12">
                            <div class="bg-light p-3 rounded">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Email</label>
                                        <input type="email" name="email" id="edit_email" class="form-control"
                                            value="<?php echo htmlspecialchars($pegawai['email'] ?? ''); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">No. HP</label>
                                        <input type="text" name="no_hp" id="edit_no_hp" class="form-control"
                                            value="<?php echo htmlspecialchars($pegawai['no_hp'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold">Alamat</label>
                                        <textarea name="alamat" id="edit_alamat" class="form-control"
                                            rows="2"><?php echo htmlspecialchars($pegawai['alamat'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formEditProfil" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const modalEdit = new bootstrap.Modal(document.getElementById('modalEditProfil'));

        $('#btnEditProfil').click(function () {
            modalEdit.show();
        });

        // Preview Photo
        $('#foto_edit').change(function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    $('#preview-foto-edit').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        // Submit Form
        $('#formEditProfil').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpan');

            const btn = $(this).closest('.modal-content').find('button[type="submit"]');
            const oldHtml = btn.html();
            btn.prop('disabled', true).html('<i class="las la-spinner fa-spin me-2"></i> Menyimpan...');

            $.ajax({
                url: 'proses_pegawai.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        if (typeof showToast === 'function') showToast(res.message, 'success');
                        else alert(res.message);
                        setTimeout(() => { location.reload(); }, 1500);
                    } else {
                        if (typeof showToast === 'function') showToast(res.message, 'error');
                        else alert(res.message);
                        btn.prop('disabled', false).html(oldHtml);
                    }
                },
                error: function () {
                    if (typeof showToast === 'function') showToast('Terjadi kesalahan sistem.', 'error');
                    btn.prop('disabled', false).html(oldHtml);
                }
            });
        });

        // Print Preview Logic
        document.getElementById('modalCetak').addEventListener('show.bs.modal', function () {
            const printable = document.getElementById('printableProfile');
            const preview = document.getElementById('printPreviewContent');
            preview.innerHTML = printable.innerHTML;
        });
    });
</script>

<!-- === MODAL: CETAK PREVIEW === -->
<div class="modal fade" id="modalCetak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title fw-bold">Cetak Profil
                    (<?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?>)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0 bg-light">
                <div class="d-flex flex-column align-items-center py-4">
                    <button type="button" class="btn btn-primary px-5 mb-4 shadow-sm fw-bold" onclick="window.print()">
                        Cetak Dokumen
                    </button>
                    <div id="printPreviewContent" class="bg-white shadow-lg p-5"
                        style="width: 210mm; min-height: 297mm; font-family: 'Times New Roman', Times, serif; color: #000; position: relative;">
                        <!-- Preview content injected here -->
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- === PRINTABLE LAYOUT === -->
<div id="printableProfile">
    <?php
    if (!empty($g['kop_dinas'])) {
        echo str_replace('src="images/', 'src="' . $base_dir . 'images/', $g['kop_dinas']);
    } else {
        ?>
        <div
            style="display: flex; align-items: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 5px;">
            <div style="width: 80px; text-align: center;">
                <img src="../images/logo.png" style="width: 70px;" onerror="this.style.display='none'">
            </div>
            <div style="flex: 1; text-align: center; padding-right: 80px;">
                <h4 style="margin: 0; text-transform: uppercase; font-weight: bold; font-size: 14pt;">PEMERINTAH PROVINSI
                    DKI JAKARTA</h4>
                <h4 style="margin: 0; text-transform: uppercase; font-weight: bold; font-size: 14pt;">DINAS PENDIDIKAN</h4>
                <h3 style="margin: 5px 0; text-transform: uppercase; font-weight: bold; font-size: 16pt;">SMP NEGERI 171
                    JAKARTA</h3>
                <p style="margin: 0; font-size: 9pt;">Jl. Tipar No. 49, RT.4/RW.7, Pekayon, Kec. Ps. Rebo, Kota Jakarta
                    Timur, 13710</p>
            </div>
        </div>
        <div style="border-top: 1px solid #000; margin-top: 2px; margin-bottom: 20px;"></div>
    <?php } ?>

    <div style="text-align: center; margin: 20px 0 30px 0;">
        <h3
            style="text-decoration: underline; text-transform: uppercase; font-weight: bold; margin: 0; font-size: 16pt;">
            BIO DATA PEGAWAI</h3>
    </div>

    <table class="print-table-section">
        <tr>
            <td rowspan="10" style="width: 160px; text-align: center; vertical-align: top; padding: 15px;">
                <div style="border: 1px solid #000; padding: 2px; display: inline-block;">
                    <img src="<?php echo $src_foto; ?>" style="width: 130px; height: 170px; object-fit: cover;">
                </div>
                <div style="margin-top: 10px; font-size: 8pt; color: #444;">Pas Foto 3x4</div>
            </td>
            <td class="label">Nama Lengkap</td>
            <td><strong><?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?></strong></td>
        </tr>
        <tr>
            <td class="label">NIP / NRK</td>
            <td><?php echo htmlspecialchars(($pegawai['nip'] ?? '') ?: '-'); ?> /
                <?php echo htmlspecialchars(($pegawai['nrk'] ?? '') ?: '-'); ?>
            </td>
        </tr>
        <tr>
            <td class="label">NUPTK</td>
            <td><?php echo htmlspecialchars(($pegawai['nuptk'] ?? '') ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="label">Tempat, Tgl Lahir</td>
            <td><?php echo htmlspecialchars(($pegawai['tempat_lahir'] ?? '') ?: '-'); ?>,
                <?php echo (!empty($pegawai['tgl_lahir']) && $pegawai['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($pegawai['tgl_lahir'])) : '-'; ?>
            </td>
        </tr>
        <tr>
            <td class="label">Jenis Kelamin</td>
            <td><?php echo ($pegawai['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></td>
        </tr>
        <tr>
            <td class="label">Agama</td>
            <td><?php echo htmlspecialchars(($pegawai['agama'] ?? '') ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="label">Pendidikan Terakhir</td>
            <td><?php echo htmlspecialchars(($pegawai['pendidikan'] ?? '') ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="label">Alamat Tinggal</td>
            <td><?php echo htmlspecialchars(($pegawai['alamat'] ?? '') ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="label">Nomor HP / Email</td>
            <td><?php echo htmlspecialchars($pegawai['no_hp'] ?? '-'); ?> /
                <?php echo htmlspecialchars($pegawai['email'] ?? '-'); ?>
            </td>
        </tr>
        <tr>
            <td class="label">NPWP</td>
            <td><?php echo htmlspecialchars(($pegawai['npwp'] ?? '') ?: '-'); ?></td>
        </tr>
    </table>

    <div class="print-section-header">I. DATA KEPEGAWAIAN SAAT INI</div>
    <table class="print-table-section">
        <tr>
            <td class="label">Jabatan</td>
            <td><strong><?php echo htmlspecialchars(($pegawai['jabatan'] ?? '') ?: '-'); ?></strong></td>
        </tr>
        <tr>
            <td class="label">Pangkat / Golongan</td>
            <td><?php echo htmlspecialchars(($pegawai['pangkat'] ?? '') ?: '-') . " (" . htmlspecialchars(($pegawai['golongan'] ?? '') ?: '-') . ")"; ?>
            </td>
        </tr>
        <tr>
            <td class="label">TMT Golongan</td>
            <td><?php echo (!empty($pegawai['tmt_golongan']) && $pegawai['tmt_golongan'] != '0000-00-00') ? date('d-m-Y', strtotime($pegawai['tmt_golongan'])) : '-'; ?>
            </td>
        </tr>
        <tr>
            <td class="label">Status Pegawai</td>
            <td><?php echo htmlspecialchars(($pegawai['status_pegawai'] ?? '') ?: '-'); ?></td>
        </tr>
        <tr>
            <td class="label">Masa Kerja</td>
            <td><?php echo ($pegawai['masa_kerja_thn'] ?: '0') . ' Tahun ' . ($pegawai['masa_kerja_bln'] ?: '0') . ' Bulan'; ?>
            </td>
        </tr>
        <tr>
            <td class="label">Unit Kerja</td>
            <td><?php echo htmlspecialchars(($pegawai['unit_kerja'] ?? '') ?: 'SMP Negeri 171 Jakarta'); ?></td>
        </tr>
        <tr>
            <td class="label">Gaji Pokok</td>
            <td>Rp. <?php echo number_format($pegawai['gaji_pokok'] ?: 0, 0, ',', '.'); ?></td>
        </tr>
    </table>

    <div class="print-section-header">II. RIWAYAT PENGANGKATAN & PENSIUN</div>
    <table class="print-table-section">
        <tr>
            <td class="label">SK Pengangkatan Pertama</td>
            <td><?php echo htmlspecialchars(($appointment['no_sk'] ?? '-')); ?></td>
        </tr>
        <tr>
            <td class="label">TMT Pengangkatan</td>
            <td><?php echo (!empty($appointment['tmt'] ?? '') && $appointment['tmt'] != '0000-00-00') ? date('d-m-Y', strtotime($appointment['tmt'])) : '-'; ?>
            </td>
        </tr>
        <tr>
            <td class="label">Estimasi TMT Pensiun</td>
            <td><strong><?php echo $retirement ? $retirement['tmt'] : "-"; ?></strong> (BUP
                <?php echo $retirement ? $retirement['bup'] : "-"; ?> Tahun)
            </td>
        </tr>
    </table>

    <div class="print-section-header" style="page-break-before: auto;">III. RIWAYAT KEPEGAWAIAN TERAKHIR</div>
    <table style="width: 100%; border-collapse: collapse; font-size: 10pt; border: 1px solid #000;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th style="border: 1px solid #000; padding: 8px; width: 120px; text-align: center;">KATEGORI</th>
                <th style="border: 1px solid #000; padding: 8px;">URAIAN / KETERANGAN</th>
                <th style="border: 1px solid #000; padding: 8px; width: 100px; text-align: center;">TMT</th>
                <th style="border: 1px solid #000; padding: 8px; width: 180px;">NOMOR SK / IJAZAH</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($hist_rows)): ?>
                <tr>
                    <td colspan="4" style="border: 1px solid #000; padding: 15px; text-align: center; font-style: italic;">
                        Data riwayat tidak ditemukan.</td>
                </tr>
            <?php else: ?>
                <?php foreach (array_slice($hist_rows, 0, 10) as $h): ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                            <?php echo htmlspecialchars($h['kategori'] ?? ''); ?>
                        </td>
                        <td style="border: 1px solid #000; padding: 6px;">
                            <strong><?php echo htmlspecialchars($h['deskripsi'] ?? ''); ?></strong>
                            <?php if ($h['institusi']): ?>
                                <div style="font-size: 9pt; color: #444;"><?php echo htmlspecialchars($h['institusi'] ?? ''); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td style="border: 1px solid #000; padding: 6px; text-align: center;">
                            <?php echo (!empty($h['tmt']) && $h['tmt'] != '0000-00-00') ? date('d-m-Y', strtotime($h['tmt'])) : '-'; ?>
                        </td>
                        <td style="border: 1px solid #000; padding: 6px;">
                            <?php echo htmlspecialchars($h['no_sk'] ?: $h['no_ijazah'] ?: '-'); ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <div style="margin-top: 40px; width: 100%;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 60%;"></td>
                <td style="width: 40%; text-align: center; font-size: 11pt; line-height: 1.3;">
                    <p style="margin: 0;">Jakarta, <?php
                    $months = [
                        'January' => 'Januari',
                        'February' => 'Februari',
                        'March' => 'Maret',
                        'April' => 'April',
                        'May' => 'Mei',
                        'June' => 'Juni',
                        'July' => 'Juli',
                        'August' => 'Agustus',
                        'September' => 'September',
                        'October' => 'Oktober',
                        'November' => 'November',
                        'December' => 'Desember'
                    ];
                    $date = date('d F Y');
                    foreach ($months as $en => $id)
                        $date = str_replace($en, $id, $date);
                    echo $date;
                    ?></p>
                    <p style="margin: 0 0 80px 0;">Pegawai Yang Bersangkutan,</p>
                    <p style="font-weight: bold; text-decoration: underline; margin: 0; font-size: 12pt;">
                        <?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?>
                    </p>
                    <p style="margin: 0;">NIP. <?php echo htmlspecialchars(($pegawai['nip'] ?? '') ?: '-'); ?></p>
                </td>
            </tr>
        </table>
    </div>
</div>