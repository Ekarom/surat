<?php
/**
 * Monitoring Kepegawaian (Admin View - DataTable Version)
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$pegawai_id = (int) ($_GET['id'] ?? 0);

// External Assets for Editing
if ($pegawai_id > 0) {
    echo '<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />';
    echo '<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />';
    echo '<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>';
    echo '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">';
    echo '<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>';
    echo '<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>';
}

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
        cursor: pointer;
        transition: all 0.3s;
    }

    .photo-monitor:hover {
        opacity: 0.8;
        filter: brightness(0.9);
    }

    /* Modern Input Styles */
    .modern-input {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 8px 15px;
        font-size: 0.9rem;
        background-color: #f8fafc;
        width: 100%;
        transition: all 0.2s;
        color: #1e293b;
    }

    .modern-input:focus {
        background-color: #fff;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        outline: none;
    }

    .info-box-edit {
        flex: 1;
        display: flex;
        align-items: center;
    }

    /* Select2 Bootstrap 5 Theme Override */
    .select2-container--bootstrap-5 .select2-selection {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        min-height: 40px !important;
        display: flex !important;
        align-items: center !important;
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
                    <h2 class="fw-bold text-dark mb-1">Edit Data Kepegawaian</h2>
                    <p class="text-muted small mb-0">Pemantauan riwayat dan dokumen lampiran secara spesifik.</p>
                </div>
                <a href="?data_kepegawaian" class="btn btn-outline-info">
                    <i class="las la-arrow-left me-2 text-primary"></i> Kembali
                </a>
            </div>
        </div>

        <form id="formPegawaiAdmin" enctype="multipart/form-data">
            <input type="hidden" name="id" value="<?php echo $pegawai_id; ?>">
            <input type="hidden" name="foto_lama" value="<?php echo $pegawai['foto']; ?>">

            <div class="row g-4 mb-5">
                <div class="col-lg-12">
                    <div class="card-modern shadow-sm border-0">
                        <div class="card-body p-4">
                            <div class="row align-items-center">
                                <div class="col-md-auto text-center mb-3 mb-md-0">
                                    <label for="foto_input" style="cursor: pointer;">
                                        <img src="<?php echo $foto_path; ?>" class="photo-monitor" id="preview-foto"
                                            alt="Foto" title="Klik untuk ubah foto">
                                    </label>
                                    <input type="file" name="foto" id="foto_input" class="d-none" accept="image/*">
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
                                                <div class="info-box-edit">
                                                    <input type="text" name="nm_pegawai"
                                                        class="modern-input fw-bold text-primary"
                                                        value="<?php echo htmlspecialchars($pegawai['nm_pegawai'] ?? ''); ?>"
                                                        required>
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">NIP</div>
                                                <div class="info-box-edit">
                                                    <input type="text" name="nip" class="modern-input"
                                                        value="<?php echo htmlspecialchars($pegawai['nip'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">NRK</div>
                                                <div class="info-box-edit">
                                                    <input type="text" name="nrk" class="modern-input"
                                                        value="<?php echo htmlspecialchars($pegawai['nrk'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-row">
                                                <div class="info-label">Jabatan</div>
                                                <div class="info-box-edit">
                                                    <input type="text" name="jabatan" class="modern-input"
                                                        value="<?php echo htmlspecialchars($pegawai['jabatan'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="info-row">
                                                <div class="info-label">Unit Kerja</div>
                                                <div class="info-box-edit">
                                                    <input type="text" name="unit_kerja" class="modern-input"
                                                        value="<?php echo htmlspecialchars(($pegawai['unit_kerja'] ?? '') ?: 'SMP Negeri 171 Jakarta'); ?>">
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
                    <span class="bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2"
                        style="width: 36px; height: 36px; box-shadow: 0 4px 10px rgba(13, 202, 240, 0.3);">
                        <i class="las la-id-card fs-5"></i>
                    </span>
                    Biodata & Profil Lengkap
                </h5>
            </div>

            <div class="row g-4 mb-5">
                <!-- IDENTITAS PRIBADI -->
                <div class="col-lg-4">
                    <div class="card-modern shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h6 class="fw-bold text-primary mb-0"><i class="las la-user me-2"></i>Identitas Pribadi</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="info-row">
                                <div class="info-label">Jenis Kelamin</div>
                                <div class="info-box-edit">
                                    <select name="jenis_kelamin" class="form-select modern-input">
                                        <option value="L" <?php echo ($pegawai['jenis_kelamin'] == 'L') ? 'selected' : ''; ?>>
                                            Laki-laki</option>
                                        <option value="P" <?php echo ($pegawai['jenis_kelamin'] == 'P') ? 'selected' : ''; ?>>
                                            Perempuan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Agama</div>
                                <div class="info-box-edit">
                                    <select name="agama" class="form-select modern-input select2-edit">
                                        <option value="">- Pilih -</option>
                                        <?php
                                        $agamas = ['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];
                                        foreach ($agamas as $a):
                                            $sel = ($pegawai['agama'] == $a) ? 'selected' : '';
                                            echo "<option value='$a' $sel>$a</option>";
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Tempat Lahir</div>
                                <div class="info-box-edit">
                                    <input type="text" name="tempat_lahir" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['tempat_lahir'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Tanggal Lahir</div>
                                <div class="info-box-edit">
                                    <input type="text" name="tgl_lahir" class="modern-input datepicker"
                                        value="<?php echo $pegawai['tgl_lahir']; ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">NUPTK</div>
                                <div class="info-box-edit">
                                    <input type="text" name="nuptk" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['nuptk'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">NIK (KTP)</div>
                                <div class="info-box-edit">
                                    <input type="text" name="nik" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['nik'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">No. KK</div>
                                <div class="info-box-edit">
                                    <input type="text" name="no_kk" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['no_kk'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Nama Ibu</div>
                                <div class="info-box-edit">
                                    <input type="text" name="nama_ibu" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['nama_ibu'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Hobby</div>
                                <div class="info-box-edit">
                                    <input type="text" name="hobby" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['hobby'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KEPEGAWAIAN & DOKUMEN -->
                <div class="col-lg-4">
                    <div class="card-modern shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h6 class="fw-bold text-success mb-0"><i class="las la-briefcase me-2"></i>Kepegawaian & Dokumen
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="info-row">
                                <div class="info-label">Status</div>
                                <div class="info-box-edit">
                                    <select name="status_pegawai" class="form-select modern-input select2-edit">
                                        <option value="PNS" <?php echo ($pegawai['status_pegawai'] == 'PNS') ? 'selected' : ''; ?>>PNS</option>
                                        <option value="PPPK" <?php echo ($pegawai['status_pegawai'] == 'PPPK') ? 'selected' : ''; ?>>PPPK</option>
                                        <option value="Honorer" <?php echo ($pegawai['status_pegawai'] == 'Honorer') ? 'selected' : ''; ?>>Honorer</option>
                                        <option value="Lainnya" <?php echo ($pegawai['status_pegawai'] == 'Lainnya') ? 'selected' : ''; ?>>Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Pendidikan</div>
                                <div class="info-box-edit">
                                    <input type="text" name="pendidikan" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['pendidikan'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Golongan</div>
                                <div class="info-box-edit">
                                    <select name="golongan" class="form-select modern-input select2-edit">
                                        <option value="">- Pilih -</option>
                                        <?php
                                        $golongans = ['I/a', 'I/b', 'I/c', 'I/d', 'II/a', 'II/b', 'II/c', 'II/d', 'III/a', 'III/b', 'III/c', 'III/d', 'IV/a', 'IV/b', 'IV/c', 'IV/d', 'IV/e', 'V', 'IX'];
                                        foreach ($golongans as $g):
                                            $sel = ($pegawai['golongan'] == $g) ? 'selected' : '';
                                            echo "<option value='$g' $sel>$g</option>";
                                        endforeach;
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">TMT Golongan</div>
                                <div class="info-box-edit">
                                    <input type="text" name="tmt_golongan" class="modern-input datepicker"
                                        value="<?php echo $pegawai['tmt_golongan']; ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Masa Kerja</div>
                                <div class="info-box-edit">
                                    <div class="input-group">
                                        <input type="number" name="masa_kerja_thn" class="form-control modern-input"
                                            value="<?php echo $pegawai['masa_kerja_thn']; ?>" placeholder="Thn">
                                        <input type="number" name="masa_kerja_bln" class="form-control modern-input"
                                            value="<?php echo $pegawai['masa_kerja_bln']; ?>" placeholder="Bln">
                                    </div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Gaji Pokok</div>
                                <div class="info-box-edit">
                                    <input type="text" name="gaji_pokok" class="modern-input fw-bold text-success"
                                        value="<?php echo number_format($pegawai['gaji_pokok'] ?: 0, 0, ',', '.'); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">No. Karpeg</div>
                                <div class="info-box-edit">
                                    <input type="text" name="no_karpeg" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['no_karpeg'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">No. Taspen</div>
                                <div class="info-box-edit">
                                    <input type="text" name="no_taspen" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['no_taspen'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">No. BPJS</div>
                                <div class="info-box-edit">
                                    <input type="text" name="no_bpjs" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['no_bpjs'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KONTAK & ALAMAT -->
                <div class="col-lg-4">
                    <div class="card-modern shadow-sm border-0 h-100">
                        <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                            <h6 class="fw-bold text-warning mb-0"><i class="las la-map-marker me-2"></i>Kontak & Alamat</h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="info-row">
                                <div class="info-label">No. HP</div>
                                <div class="info-box-edit">
                                    <input type="text" name="no_hp" class="modern-input fw-bold text-primary"
                                        value="<?php echo htmlspecialchars($pegawai['no_hp'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Email</div>
                                <div class="info-box-edit">
                                    <input type="email" name="email" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['email'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Alamat</div>
                                <div class="info-box-edit">
                                    <textarea name="alamat" class="modern-input"
                                        rows="3"><?php echo htmlspecialchars($pegawai['alamat'] ?? ''); ?></textarea>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">RT / RW</div>
                                <div class="info-box-edit">
                                    <div class="input-group">
                                        <input type="text" name="rt" class="form-control modern-input" placeholder="RT"
                                            value="<?php echo htmlspecialchars($pegawai['rt'] ?? ''); ?>">
                                        <input type="text" name="rw" class="form-control modern-input" placeholder="RW"
                                            value="<?php echo htmlspecialchars($pegawai['rw'] ?? ''); ?>">
                                    </div>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Kelurahan</div>
                                <div class="info-box-edit">
                                    <input type="text" name="kelurahan" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['kelurahan'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Kecamatan</div>
                                <div class="info-box-edit">
                                    <input type="text" name="kecamatan" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['kecamatan'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">NPWP</div>
                                <div class="info-box-edit">
                                    <input type="text" name="npwp" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['npwp'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-label">Pasangan</div>
                                <div class="info-box-edit">
                                    <input type="text" name="nama_pasangan" class="modern-input"
                                        value="<?php echo htmlspecialchars($pegawai['nama_pasangan'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-n4 mb-5 text-end">
                <button type="submit" class="btn btn-primary btn-rounded px-5 py-2 shadow">
                    <i class="las la-save me-2"></i> Simpan Perubahan Data
                </button>
            </div>
        </form>

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
                                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($r['kategori'] ?? ''); ?></td>
                                        <td>
                                            <?php
                                            if ($r['kategori'] == 'Pendidikan' && !empty($r['institusi'])) {
                                                echo htmlspecialchars($r['institusi'] ?? '');
                                            } else {
                                                echo htmlspecialchars(($r['deskripsi'] ?? '') ?: '-');
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php
                                            if ($r['kategori'] == 'Pendidikan' && !empty($r['no_ijazah'])) {
                                                echo '<span class="text-muted extra-small d-block">No. Ijazah:</span>';
                                                echo '<code>' . htmlspecialchars($r['no_ijazah'] ?? '') . '</code>';
                                            } else {
                                                echo '<span class="text-muted extra-small d-block">No. SK:</span>';
                                                echo '<code>' . htmlspecialchars(($r['no_sk'] ?? '') ?: '-') . '</code>';
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
                                                echo htmlspecialchars($r['jurusan'] ?? '');
                                            } else if (($r['kategori'] == 'Diklat' || $r['kategori'] == 'Seminar') && !empty($r['tempat'])) {
                                                echo htmlspecialchars($r['tempat'] ?? '');
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

        <script>
            $(document).ready(function () {
                // DataTable with FixedColumns
                const table = $('.content table.table').DataTable({
                    scrollY: 450,
                    scrollX: true,
                    scrollCollapse: true,
                    paging: false,
                });

                // Initialize Select2
                $('.select2-edit').select2({
                    theme: 'bootstrap-5',
                    width: '100%'
                });

                // Initialize Flatpickr
                $(".datepicker").flatpickr({
                    altInput: true,
                    altFormat: "d-m-Y",
                    dateFormat: "Y-m-d",
                    locale: "id",
                    allowInput: true
                });

                // Photo Preview
                $('#foto_input').change(function () {
                    const file = this.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            $('#preview-foto').attr('src', e.target.result);
                        }
                        reader.readAsDataURL(file);
                    }
                });

                // Form Submit Logic
                $('#formPegawaiAdmin').on('submit', function (e) {
                    e.preventDefault();
                    const formData = new FormData(this);
                    formData.append('action', 'simpan');

                    const btn = $(this).find('button[type="submit"]');
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
                                showToast(res.message, 'success');
                                setTimeout(() => { location.reload(); }, 1500);
                            } else {
                                showToast(res.message, 'error');
                                btn.prop('disabled', false).html(oldHtml);
                            }
                        },
                        error: function () {
                            showToast('Gagal menghubungi server.', 'error');
                            btn.prop('disabled', false).html(oldHtml);
                        }
                    });
                });
            });
        </script>
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

        <div class="callout callout-success shadow-sm mb-4">
            <div class="d-flex align-items-center">
                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                    style="width: 40px; height: 40px; flex-shrink: 0;">
                    <i class="las la-info-circle fs-4"></i>
                </div>
                <div>
                    <span class="callout-title">Monitoring Riwayat & Dokumen</span>
                    <p class="callout-text">Angka pada kolom Pangkat, Jabatan, Pendidikan, dan KGB menunjukkan jumlah
                        rekaman riwayat yang telah diunggah oleh personil. Gunakan tombol edit untuk melakukan verifikasi
                        detail dokumen pendukung.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <table class="table table-striped duk-table" id="tableKepegawaian" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center" width="40">No</th>
                            <th>Nama & Gelar</th>
                            <th>NIP / NRK</th>
                            <th>Tempat, Tgl Lahir</th>
                            <th class="text-center">JK</th>
                            <th>Jabatan</th>
                            <th class="text-center">Gol</th>
                            <th>Pendidikan</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="60">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        while ($r = $q_global->fetch_assoc()):
                            $full_name = (!empty($r['gelar_depan']) ? $r['gelar_depan'] . ' ' : '') . $r['nm_pegawai'] . (!empty($r['gelar_belakang']) ? ', ' . $r['gelar_belakang'] : '');
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?php echo $no++; ?></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo htmlspecialchars($full_name); ?></div>
                                </td>
                                <td>
                                    <div class="extra-small text-muted">
                                        NIP: <?php echo $r['nip'] ?: '-'; ?><br>
                                        NRK: <?php echo $r['nrk'] ?: '-'; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="small">
                                        <?php echo htmlspecialchars($r['tempat_lahir'] ?: '-'); ?>,<br>
                                        <?php echo (!empty($r['tgl_lahir']) && $r['tgl_lahir'] != '0000-00-00') ? date('d/m/Y', strtotime($r['tgl_lahir'])) : '-'; ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge <?php echo ($r['jenis_kelamin'] == 'L') ? 'bg-soft-primary' : 'bg-soft-danger'; ?> rounded-pill">
                                        <?php echo $r['jenis_kelamin'] ?: '-'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="extra-small fw-medium"><?php echo htmlspecialchars($r['jabatan'] ?: '-'); ?>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-soft-info rounded-pill"><?php echo $r['golongan'] ?: '-'; ?></span>
                                </td>
                                <td>
                                    <div class="extra-small"><?php echo htmlspecialchars($r['pendidikan'] ?: '-'); ?></div>
                                </td>
                                <td class="text-center">
                                    <span
                                        class="badge <?php echo ($r['status_pegawai'] == 'PNS') ? 'bg-success' : 'bg-info'; ?> badge-status">
                                        <?php echo $r['status_pegawai'] ?: '-'; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="?data_kepegawaian&id=<?php echo $r['id']; ?>"
                                        class="btn btn-sm btn-outline-primary shadow-sm">
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