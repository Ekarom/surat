<?php
/**
 * Profil Saya - Teacher Portal (Redesigned Style)
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$id_pegawai = $_SESSION['id'] ?? 0;
$query = $conn->prepare("SELECT * FROM pegawai WHERE id = ?");
$query->bind_param("i", $id_pegawai);
$query->execute();
$pegawai = $query->get_result()->fetch_assoc();

if (!$pegawai) {
    echo "<div class='alert alert-danger shadow-sm rounded-4'>Data profil tidak ditemukan.</div>";
    return;
}

$poto_db = $pegawai['foto'] ?? '';
$src_foto = (!empty($poto_db) && file_exists("../file/foto/" . $poto_db)) ? "../file/foto/" . $poto_db : "../images/default.png";
?>

<style>
    .card-modern {
        border: none;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        background: #fff;
        margin-bottom: 20px;
    }

    .card-modern .card-header {
        padding: 12px 20px;
        font-weight: 700;
        color: #fff;
        display: flex;
        align-items: center;
        gap: 10px;
        border: none;
    }

    .header-blue {
        background: #3b82f6;
    }

    .header-red {
        background: #ef4444;
    }

    .header-slate {
        background: #1e293b;
    }

    .form-group-info {
        display: flex;
        padding: 10px 0;
        border-bottom: 1px solid #f1f5f9;
        align-items: center;
    }

    .form-group-info:last-child {
        border-bottom: none;
    }

    .info-label {
        width: 200px;
        font-weight: 700;
        color: #475569;
        font-size: 0.9rem;
    }

    .info-value {
        flex: 1;
        color: #1e293b;
        background: #f8fafc;
        padding: 8px 12px;
        border-radius: 6px;
        border: 1px solid #e2e8f0;
        font-size: 0.9rem;
    }

    .info-value.required::after {
        content: ' (*)';
        color: #ef4444;
    }

    .photo-display-container {
        padding: 20px;
        text-align: center;
    }

    .photo-frame {
        width: 100%;
        max-width: 250px;
        aspect-ratio: 3/4;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }

    .btn-action-group {
        margin-top: 20px;
    }

    @media (max-width: 768px) {
        .form-group-info {
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
    <div class="row">
        <!-- Left Column: Data Pribadi -->
        <div class="col-lg-8">
            <div class="card-modern">
                <div class="card-header header-blue">
                    <i class="fas fa-address-card"></i> Data Pribadi
                </div>
                <div class="card-body p-4">
                    <div class="form-group-info">
                        <div class="info-label">Nomor Induk Pegawai</div>
                        <div class="info-value"><?php echo htmlspecialchars($pegawai['nip'] ?: '-'); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Nomor Registrasi (NRK)</div>
                        <div class="info-value"><?php echo htmlspecialchars($pegawai['nrk'] ?: '-'); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Nama Lengkap</div>
                        <div class="info-value required"><?php echo htmlspecialchars($pegawai['nm_pegawai']); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Jenis Kelamin</div>
                        <div class="info-value required">
                            <?php echo ($pegawai['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Tempat Lahir</div>
                        <div class="info-value required">
                            <?php echo htmlspecialchars($pegawai['tempat_lahir'] ?: '-'); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Tanggal Lahir</div>
                        <div class="info-value required">
                            <?php echo (!empty($pegawai['tgl_lahir']) && $pegawai['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($pegawai['tgl_lahir'])) : '-'; ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Pendidikan Terakhir</div>
                        <div class="info-value"><?php echo htmlspecialchars($pegawai['pendidikan'] ?: '-'); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Alamat</div>
                        <div class="info-value"><?php echo htmlspecialchars($pegawai['alamat'] ?: '-'); ?></div>
                    </div>

                    <div class="btn-action-group d-flex gap-2">
                        <button class="btn btn-primary px-4 fw-bold" id="btnEditProfil">
                            <i class="fas fa-edit me-2"></i> Edit Profil
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-modern">
                <div class="card-header header-slate">
                    <i class="fas fa-briefcase"></i> Data Kepegawaian
                </div>
                <div class="card-body p-4">
                    <div class="form-group-info">
                        <div class="info-label">Jabatan</div>
                        <div class="info-value"><?php echo htmlspecialchars($pegawai['jabatan'] ?: '-'); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Pangkat / Golongan</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($pegawai['pangkat'] ?: '-') . " / " . htmlspecialchars($pegawai['golongan'] ?: '-'); ?>
                        </div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Unit Kerja</div>
                        <div class="info-value">
                            <?php echo htmlspecialchars($pegawai['unit_kerja'] ?: 'SMP Negeri 171 Jakarta'); ?></div>
                    </div>
                    <div class="form-group-info">
                        <div class="info-label">Status Pegawai</div>
                        <div class="info-value"><?php echo htmlspecialchars($pegawai['status_pegawai'] ?: '-'); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Foto Pegawai -->
        <div class="col-lg-4">
            <div class="card-modern">
                <div class="card-header header-red">
                    <i class="fas fa-camera"></i> Foto Pegawai
                </div>
                <div class="photo-display-container">
                    <img src="<?php echo $src_foto; ?>" class="photo-frame" alt="Foto Profil">
                    <div class="mt-4">
                        <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($pegawai['nm_pegawai']); ?></h5>
                        <p class="text-muted small">NIP. <?php echo htmlspecialchars($pegawai['nip'] ?: '-'); ?></p>
                        <hr>
                        <div class="text-start">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-success rounded-circle me-2" style="width: 10px; height: 10px;"></div>
                                <span class="small fw-bold">Akun Terverifikasi</span>
                            </div>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-phone-alt text-muted me-2 small"></i>
                                <span class="small"><?php echo htmlspecialchars($pegawai['no_hp'] ?: '-'); ?></span>
                            </div>
                            <div class="d-flex align-items-center mt-1">
                                <i class="fas fa-envelope text-muted me-2 small"></i>
                                <span class="small"><?php echo htmlspecialchars($pegawai['email'] ?: '-'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Re-including the Modal and Script for Edit functionality -->
<!-- === MODAL: EDIT PROFIL === -->
<div class="modal fade" id="modalEditProfil" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perbarui Profil Saya</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditProfil" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="edit_id" value="<?php echo $id_pegawai; ?>">
                    <input type="hidden" name="foto_lama" id="edit_foto_lama" value="<?php echo $poto_db; ?>">
                    <input type="hidden" name="nip" id="edit_nip" value="<?php echo $pegawai['nip']; ?>">

                    <div class="row g-4">
                        <!-- Photo Section -->
                        <div class="col-md-4 text-center">
                            <div class="position-relative d-inline-block">
                                <img id="preview-foto-edit" src="<?php echo $src_foto; ?>" class="rounded shadow-sm"
                                    style="width: 150px; height: 200px; object-fit: cover; border: 1px solid #e2e8f0;">
                                <label for="foto_edit"
                                    class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                                    style="width: 38px; height: 38px; cursor: pointer; border: 3px solid #fff; margin-bottom: -10px; margin-right: -10px;">
                                    <i class="fas fa-camera"></i>
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
                                        required value="<?php echo htmlspecialchars($pegawai['nm_pegawai']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Tempat Lahir (*)</label>
                                    <input type="text" name="tempat_lahir" id="edit_tempat_lahir" class="form-control"
                                        value="<?php echo htmlspecialchars($pegawai['tempat_lahir']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Tanggal Lahir (*)</label>
                                    <input type="date" name="tgl_lahir" id="edit_tgl_lahir" class="form-control"
                                        value="<?php echo $pegawai['tgl_lahir']; ?>">
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
                                        value="<?php echo htmlspecialchars($pegawai['pendidikan']); ?>">
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
                                            value="<?php echo htmlspecialchars($pegawai['email']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">No. HP</label>
                                        <input type="text" name="no_hp" id="edit_no_hp" class="form-control"
                                            value="<?php echo htmlspecialchars($pegawai['no_hp']); ?>">
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
                <button type="submit" form="formEditProfil" class="btn btn-primary">Simpan</button>
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
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...');

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
    });
</script>