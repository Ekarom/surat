<?php
/**
 * Monitoring Kepegawaian (Admin View - DataTable Version)
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$pegawai_id = (int) ($_GET['id'] ?? 0);

// Shared Styles
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

    .info-row {
        display: flex;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
        align-items: center;
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        width: 160px;
        font-weight: 700;
        color: #64748b;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-box {
        flex: 1;
        color: #1e293b;
        background: #f8fafc;
        padding: 8px 15px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        font-size: 0.9rem;
        min-height: 40px;
        display: flex;
        align-items: center;
    }

    .photo-monitor {
        width: 130px;
        height: 130px;
        object-fit: cover;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }

    /* DataTable Customization */


    .badge-status {
        font-size: 0.65rem;
        font-weight: 800;
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
    }

    .bg-soft-success {
        background-color: #dcfce7;
        color: #15803d;
    }

    .bg-soft-warning {
        background-color: #fef9c3;
        color: #854d0e;
    }

    .bg-soft-danger {
        background-color: #fee2e2;
        color: #b91c1c;
    }

    .bg-soft-primary {
        background-color: #e0e7ff;
        color: #4338ca;
    }

    .btn-action-view {
        width: 32px;
        height: 32px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        transition: all 0.2s;
        text-decoration: none !important;
    }

    .btn-action-view:hover {
        transform: scale(1.1);
        background: #3b82f6;
        color: #fff !important;
    }

    /* Callout Style */
    .callout {
        padding: 1.25rem;
        margin-top: 1rem;
        margin-bottom: 1rem;
        border: 1px solid #e2e8f0;
        border-left-width: 0.35rem;
        border-radius: 10px;
        background: #fff;
    }

    .callout-primary {
        border-left-color: #3b82f6;
        background: linear-gradient(to right, #f0f7ff, #fff);
    }

    .callout-title {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
        display: block;
    }

    .callout-text {
        color: #64748b;
        font-size: 0.85rem;
        margin-bottom: 0;
    }

    @media (max-width: 768px) {
        .info-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .info-label {
            width: 100%;
            margin-bottom: 5px;
        }
    }
</style>

<div class="container-fluid py-4">
    <?php if ($pegawai_id > 0):
        // ==========================================
        // VIEW: INDIVIDUAL MONITORING
        // ==========================================
        $stmt = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
        $stmt->bind_param("i", $pegawai_id);
        $stmt->execute();
        $pegawai = $stmt->get_result()->fetch_assoc();

        if (!$pegawai):
            echo '<div class="alert alert-danger rounded-4 shadow-sm m-4"><i class="las la-exclamation-triangle me-2"></i>Data pegawai tidak ditemukan.</div>';
            echo '<div class="px-4"><a href="?data_kepegawaian" class="btn btn-primary btn-rounded px-4"><i class="las la-arrow-left me-2"></i>Kembali ke Monitoring</a></div>';
            return;
        endif;

        $foto_path = (!empty($pegawai['foto']) && file_exists("../file/datakepegawaian/" . $pegawai['foto'])) ? "../file/datakepegawaian/" . $pegawai['foto'] : "../images/default.png";
        $q_riwayat = $conn->query("SELECT * FROM riwayat_kepegawaian WHERE pegawai_id = '$pegawai_id' ORDER BY tmt DESC");
        ?>

        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Detail Monitoring Pegawai</h2>
                    <p class="text-muted small mb-0">Pemantauan riwayat dan dokumen lampiran secara spesifik.</p>
                </div>
                <a href="?data_kepegawaian" class="btn btn-outline-info">
                    <i class="las la-arrow-left me-2 text-primary"></i> Kembali
                </a>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-lg-12">
                <div class="card-modern shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-auto text-center mb-3 mb-md-0">
                                <img src="<?php echo $foto_path; ?>" class="photo-monitor" alt="Foto">
                                <div class="mt-2">
                                    <span class="badge bg-soft-success rounded-pill px-3 py-1 extra-small">
                                        <i class="las la-check-circle me-1"></i> Terverifikasi
                                    </span>
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <div class="info-label">Nama Lengkap</div>
                                            <div class="info-box fw-bold text-primary">
                                                <?php echo htmlspecialchars($pegawai['nm_pegawai']); ?>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">NIP / NRK</div>
                                            <div class="info-box text-muted">
                                                <?php echo htmlspecialchars(($pegawai['nip'] ?: '-') . ' / ' . ($pegawai['nrk'] ?: '-')); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <div class="info-label">Jabatan</div>
                                            <div class="info-box">
                                                <?php echo htmlspecialchars($pegawai['jabatan'] ?: '-'); ?>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">Unit Kerja</div>
                                            <div class="info-box text-truncate">
                                                <?php echo htmlspecialchars($pegawai['unit_kerja'] ?: 'SMP Negeri 171 Jakarta'); ?>
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

        <div class="mb-4">
            <h5 class="fw-bold d-flex align-items-center text-dark">
                <span class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                    style="width: 36px; height: 36px; box-shadow: 0 4px 10px rgba(59, 130, 246, 0.3);">
                    <i class="las la-table fs-5"></i>
                </span>
                Tabel Riwayat & Dokumen
            </h5>
        </div>

        <div class="card-modern border-0 shadow-sm mb-5">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped align-middle nowrap mb-0" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th>Kategori</th>
                                <th>Keterangan / Institusi</th>
                                <th>No. SK / Ijazah</th>
                                <th class="text-center">TMT</th>
                                <th class="text-end">Gaji Pokok</th>
                                <th class="text-center">Masa Kerja</th>
                                <th>Jurusan / Tempat</th>
                                <th class="text-center">Status</th>
                                <th class="text-center" width="80">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($q_riwayat && $q_riwayat->num_rows > 0):
                                $no = 1;
                                while ($r = $q_riwayat->fetch_assoc()):
                                    $has_file = !empty($r['file_lampiran']);
                                    ?>
                                    <tr>
                                        <td class="text-center text-muted fw-bold"><?php echo $no++; ?></td>
                                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($r['kategori']); ?></td>
                                        <td>
                                            <?php
                                            if ($r['kategori'] == 'Pendidikan' && !empty($r['institusi'])) {
                                                echo htmlspecialchars($r['institusi']);
                                            } else {
                                                echo htmlspecialchars($r['deskripsi'] ?: '-');
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($r['kategori'] == 'Pendidikan' && !empty($r['no_ijazah'])) {
                                                echo '<span class="text-muted extra-small d-block">No. Ijazah:</span>';
                                                echo '<code>' . htmlspecialchars($r['no_ijazah']) . '</code>';
                                            } else {
                                                echo '<span class="text-muted extra-small d-block">No. SK:</span>';
                                                echo '<code>' . htmlspecialchars($r['no_sk'] ?: '-') . '</code>';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php echo (!empty($r['tmt']) && $r['tmt'] != '0000-00-00') ? date('d/m/Y', strtotime($r['tmt'])) : '-'; ?>
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            <?php echo ($r['kategori'] == 'KGB' && !empty($r['gaji_pokok'])) ? 'Rp ' . number_format($r['gaji_pokok'], 0, ',', '.') : '-'; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php
                                            if ($r['kategori'] == 'KGB' || $r['kategori'] == 'Pangkat') {
                                                echo ($r['masa_kerja_thn'] ?: 0) . 'th ' . ($r['masa_kerja_bln'] ?: 0) . 'bln';
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($r['kategori'] == 'Pendidikan' && !empty($r['jurusan'])) {
                                                echo htmlspecialchars($r['jurusan']);
                                            } else if (($r['kategori'] == 'Diklat' || $r['kategori'] == 'Seminar') && !empty($r['tempat'])) {
                                                echo htmlspecialchars($r['tempat']);
                                            } else {
                                                echo '-';
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($has_file): ?>
                                                <span class="badge-status bg-soft-success"><i class="las la-check me-1"></i> ADA</span>
                                            <?php else: ?>
                                                <span class="badge-status bg-soft-warning"><i class="las la-times me-1"></i>
                                                    TIDAK</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($has_file): ?>
                                                <a href="../file/datakepegawaian/<?php echo $r['file_lampiran']; ?>" target="_blank"
                                                    class="btn-action-view text-primary border" title="Lihat Dokumen">
                                                    <i class="las la-external-link-alt fs-5"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn-action-view text-muted border opacity-50" disabled>
                                                    <i class="las la-file-excel fs-5"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php
                                endwhile;
                            endif;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else:
        // ==========================================
        // VIEW: GLOBAL MONITORING (ALL EMPLOYEES)
        // ==========================================
        $q_global = $conn->query("SELECT p.*, 
            (SELECT COUNT(*) FROM riwayat_kepegawaian WHERE pegawai_id = p.id AND kategori = 'Pangkat') as count_pangkat,
            (SELECT COUNT(*) FROM riwayat_kepegawaian WHERE pegawai_id = p.id AND kategori = 'Jabatan') as count_jabatan,
            (SELECT COUNT(*) FROM riwayat_kepegawaian WHERE pegawai_id = p.id AND kategori = 'Pendidikan') as count_pendidikan,
            (SELECT COUNT(*) FROM riwayat_kepegawaian WHERE pegawai_id = p.id AND kategori = 'KGB') as count_kgb,
            (SELECT COUNT(*) FROM riwayat_kepegawaian WHERE pegawai_id = p.id) as count_total
        FROM pegawai p 
        ORDER BY nm_pegawai ASC");
        ?>

        <div class="row mb-4">
            <div class="col-12">
                <h2 class="fw-bold text-dark mb-1">Data Kepegawaian</h2>
                <p class="text-muted small mb-0">Daftar seluruh personil dengan status verifikasi data kepegawaian.</p>
            </div>
        </div>

        <div class="callout callout-primary shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                    style="width: 40px; height: 40px; flex-shrink: 0;">
                    <i class="las la-sync fs-4"></i>
                </div>
                <div>
                    <span class="callout-title">Informasi Sinkronisasi</span>
                    <p class="callout-text">Seluruh data di bawah ini ditarik langsung dari database portal mandiri
                        personil. Setiap pembaruan yang dilakukan oleh personil akan langsung terlihat secara real-time.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center" width="40">No</th>
                            <th>Nama & Identitas</th>
                            <th>Jabatan</th>
                            <th class="text-center">Pangkat</th>
                            <th class="text-center">Jabatan</th>
                            <th class="text-center">Pendidikan</th>
                            <th class="text-center">KGB</th>
                            <th class="text-center">Total Dok</th>
                            <th class="text-center" width="80">Edit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($r = $q_global->fetch_assoc()):
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?php echo $no++; ?></td>
                                <td>
                                    <div class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($r['nm_pegawai']); ?></div>
                                    <div class="extra-small text-muted mt-n1">NIP: <?php echo $r['nip'] ?: '-'; ?></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-muted"><?php echo $r['jabatan'] ?: '-'; ?></div>
                                </td>
                                <td class="text-center">
                                    <?php if ($r['count_pangkat'] > 0): ?>
                                        <span class="badge-status bg-soft-success"><i class="las la-check me-1"></i> ADA</span>
                                    <?php else: ?>
                                        <span class="badge-status bg-soft-danger"><i class="las la-times me-1"></i> KOSONG</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($r['count_jabatan'] > 0): ?>
                                        <span class="badge-status bg-soft-success"><i class="las la-check me-1"></i> ADA</span>
                                    <?php else: ?>
                                        <span class="badge-status bg-soft-danger"><i class="las la-times me-1"></i> KOSONG</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($r['count_pendidikan'] > 0): ?>
                                        <span class="badge-status bg-soft-success"><i class="las la-check me-1"></i> ADA</span>
                                    <?php else: ?>
                                        <span class="badge-status bg-soft-danger"><i class="las la-times me-1"></i> KOSONG</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($r['count_kgb'] > 0): ?>
                                        <span class="badge-status bg-soft-success"><i class="las la-check me-1"></i> ADA</span>
                                    <?php else: ?>
                                        <span class="badge-status bg-soft-danger"><i class="las la-times me-1"></i> KOSONG</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-soft-primary px-3 py-1 rounded-pill fw-bold">
                                        <?php echo $r['count_total']; ?> File
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="?data_kepegawaian&id=<?php echo $r['id']; ?>"
                                        class="btn btn-sm btn-outline-info shadow-sm">
                                        <i class="las la-edit"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // DataTable with FixedColumns
            const table = $('.content table.table').DataTable({
                scrollY: 450,
                scrollX: true,
                scrollCollapse: true,
                paging: false,
            });
        });
    </script>
<?php endif; ?>
</div>