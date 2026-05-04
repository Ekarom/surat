<?php
/**
 * S.A.P KEPEGAWAIAN - Admin Management Module
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$lv = $_SESSION['level'] ?? '';
if ($lv == '4') {
    header("Location: pegawai.php");
    exit;
}

?>

<style>
    /* Reuse styles from pegawai.php */
    :root {
        --sap-primary: #4f46e5;
        --sap-primary-light: rgba(79, 70, 229, 0.1);
        --sap-primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --sap-secondary: #64748b;
        --sap-success: #10b981;
        --sap-info: #0ea5e9;
        --sap-danger: #ef4444;
        --sap-warning: #f59e0b;
        --sap-dark: #1e293b;
        --sap-gray-50: #f8fafc;
        --sap-gray-100: #f1f5f9;
        --sap-gray-200: #e2e8f0;
        --sap-border-radius: 1rem;
        --sap-shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --sap-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .page-title {
        font-weight: 800;
        color: var(--sap-dark);
        letter-spacing: -0.025em;
    }

    .modern-card {
        background: #fff;
        border-radius: var(--sap-border-radius);
        border: none;
        box-shadow: var(--sap-shadow-sm);
        overflow: hidden;
    }

    .modern-card-header {
        background-color: #fff;
        border-bottom: 1px solid var(--sap-gray-100);
        padding: 1.25rem 1.5rem;
    }

    .badge-soft {
        font-weight: 600;
        padding: 0.35em 0.8em;
        border-radius: 50px;
        font-size: 0.75rem;
    }

    .badge-soft-pns {
        background-color: rgba(79, 70, 229, 0.1);
        color: #4f46e5;
    }

    .badge-soft-pppk {
        background-color: rgba(14, 165, 233, 0.1);
        color: #0ea5e9;
    }

    .badge-soft-honorer {
        background-color: rgba(245, 158, 11, 0.1);
        color: #f59e0b;
    }

    .badge-soft-lainnya {
        background-color: rgba(100, 116, 139, 0.1);
        color: #64748b;
    }

    .modern-modal {
        border-radius: 1.25rem;
        overflow: hidden;
    }

    .modern-label {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--sap-secondary);
        margin-bottom: 0.4rem;
        display: block;
    }

    .modern-input,
    .form-select.modern-input {
        border-radius: 0.75rem;
        border: 1px solid var(--sap-gray-200);
        padding: 0.6rem 1rem;
        font-size: 0.9rem;
        background-color: var(--sap-gray-50);
        transition: all 0.2s;
    }

    .modern-input:focus {
        background-color: #fff;
        border-color: var(--sap-primary);
        box-shadow: 0 0 0 4px var(--sap-primary-light);
    }

    .btn-rounded {
        border-radius: 50px;
    }

    #customSearch:focus {
        width: 300px !important;
        background-color: #fff !important;
        border-color: var(--sap-primary) !important;
        box-shadow: 0 0 0 4px var(--sap-primary-light) !important;
    }
</style>

<div class="container-fluid">
    <div class="d-md-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title mb-1">Manajemen Kepegawaian</h2>
            <p class="text-muted small mb-0">Kelola database profil, jabatan, dan status kepegawaian secara terpusat</p>
        </div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item active text-primary fw-bold">Admin Kepegawaian</li>
            </ol>
        </nav>
    </div>

    <div class="modern-card">
        <div class="modern-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-primary btn-sm btn-rounded px-4 shadow-sm"
                    id="tombolTambahPegawai">
                    <i class="fas fa-user-plus me-2"></i> Tambah Pegawai
                </button>
                <a href="proses_pegawai.php?action=download" class="btn btn-success btn-sm btn-rounded px-4 shadow-sm">
                    <i class="fas fa-file-excel me-2"></i> Download Data
                </a>
            </div>
            <div class="position-relative">
                <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="customSearch"
                    class="form-control form-control-sm btn-rounded ps-5 border-0 bg-light"
                    placeholder="Cari data pegawai..." style="width: 250px; height: 36px;">
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabelPegawai">
                    <thead class="bg-light">
                        <tr>
                            <th class="text-center px-3" width="50">No</th>
                            <th width="60">Foto</th>
                            <th>Nama & Identitas</th>
                            <th>Jabatan & Unit</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aktif</th>
                            <th class="text-center" width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM pegawai ORDER BY nm_pegawai ASC";
                        $result = $conn->query($query);
                        $no = 1;
                        while ($row = $result->fetch_assoc()):
                            $foto_path = !empty($row['foto']) ? "../file/datakepegawaian/" . $row['foto'] : "../images/default.png";
                            $s = strtolower($row['status_pegawai'] ?? '');
                            $cls = 'badge-soft-lainnya';
                            if (strpos($s, 'pns') !== false)
                                $cls = 'badge-soft-pns';
                            else if (strpos($s, 'pppk') !== false)
                                $cls = 'badge-soft-pppk';
                            else if (strpos($s, 'honorer') !== false)
                                $cls = 'badge-soft-honorer';
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-bold"><?php echo $no++; ?></td>
                                <td><img src="<?php echo $foto_path; ?>" class="rounded-circle shadow-sm"
                                        style="width: 38px; height: 38px; object-fit: cover;"></td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo $row['nm_pegawai']; ?></div>
                                    <div class="small text-muted">NIP: <?php echo $row['nip'] ?: '-'; ?> NRK:
                                        <?php echo $row['nrk'] ?: '-'; ?></div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark"><?php echo $row['jabatan'] ?: '-'; ?></div>
                                    <div class="extra-small text-muted"><?php echo $row['unit_kerja'] ?: '-'; ?></div>
                                </td>
                                <td class="text-center"><span
                                        class="badge-soft <?php echo $cls; ?>"><?php echo $row['status_pegawai'] ?: '-'; ?></span>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input status-switch" type="checkbox"
                                            data-id="<?php echo $row['id']; ?>" <?php echo ($row['status'] == '1') ? 'checked' : ''; ?>>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-light border shadow-sm tombol-view"
                                            data-id="<?php echo $row['id']; ?>" title="Lihat Detail"><i
                                                class="fas fa-eye text-primary"></i></button>
                                        <button class="btn btn-sm btn-light border shadow-sm tombol-edit"
                                            data-id="<?php echo $row['id']; ?>" title="Edit Cepat"><i
                                                class="fas fa-edit text-warning"></i></button>
                                        <button class="btn btn-sm btn-light border shadow-sm tombol-hapus"
                                            data-id="<?php echo $row['id']; ?>" title="Hapus"><i
                                                class="fas fa-trash text-danger"></i></button>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- === MODAL: DETAIL PEGAWAI (FULL VIEW) === -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 position-absolute end-0 top-0" style="z-index: 10;">
                <button type="button" class="btn-close bg-white rounded-circle p-2 shadow-sm" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="detail-header text-white"
                    style="background: var(--sap-primary-gradient); padding: 3rem 1.5rem 5rem 1.5rem; text-align: center;">
                    <img id="detail-foto" src="../images/default.png" class="rounded-circle mb-3 shadow"
                        style="width: 120px; height: 120px; border: 5px solid #fff; object-fit: cover;">
                    <h4 id="detail-nama" class="fw-bold mb-1"></h4>
                    <p id="detail-nip-nrk" class="opacity-75 small mb-0"></p>
                </div>
                <div class="detail-body"
                    style="margin-top: -4rem; background: #fff; border-radius: 2rem 2rem 0 0; padding: 2.5rem 2rem; box-shadow: 0 -10px 20px -5px rgba(0,0,0,0.05);">
                    <div class="text-center mb-4">
                        <span class="badge bg-light text-primary px-4 py-2 rounded-pill fw-bold border"
                            id="detail-jabatan"></span>
                    </div>
                    <div class="row g-4" id="detail-content-area">
                        <!-- Content loaded via JS -->
                    </div>
                    <div class="mt-4 pt-3 border-top text-center">
                        <a href="#" id="linkFullProfile" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                            <i class="fas fa-external-link-alt me-2"></i>Lihat Profil Lengkap & Riwayat
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- === MODAL: TAMBAH/EDIT PEGAWAI (SIMPLIFIED FOR ADMIN) === -->
<div class="modal fade" id="modalPegawai" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content modern-modal border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="modalPegawaiLabel">Tambah Pegawai Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formPegawai">
                    <input type="hidden" name="id" id="pegawai_id">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="modern-label">NIP / NIK <span class="text-danger">*</span></label>
                            <input type="text" name="nip" id="nip" class="form-control modern-input" required
                                placeholder="Contoh: 19800101...">
                        </div>
                        <div class="col-12">
                            <label class="modern-label">NRK (Opsional)</label>
                            <input type="text" name="nrk" id="nrk" class="form-control modern-input"
                                placeholder="Nomor Registrasi">
                        </div>
                        <div class="col-12">
                            <label class="modern-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nm_pegawai" id="nm_pegawai" class="form-control modern-input"
                                required placeholder="Nama Lengkap Tanpa Gelar">
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">Jabatan</label>
                            <input type="text" name="jabatan" id="jabatan" class="form-control modern-input"
                                placeholder="Guru / Staf">
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">Unit Kerja</label>
                            <input type="text" name="unit_kerja" id="unit_kerja" class="form-control modern-input"
                                value="SMP Negeri 171 Jakarta">
                        </div>
                        <div class="col-12">
                            <label class="modern-label">Status Kepegawaian</label>
                            <select name="status_pegawai" id="status_pegawai" class="form-select modern-input">
                                <option value="PNS">PNS</option>
                                <option value="PPPK">PPPK</option>
                                <option value="Honorer">Honorer</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-12 mt-4 text-center">
                            <p class="text-muted small italic">Data rinci lainnya (Tgl Lahir, Pendidikan, dll) akan
                                diinput mandiri oleh pegawai melalui portal mereka.</p>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light btn-rounded px-4 fw-bold"
                    data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formPegawai" class="btn btn-primary btn-rounded px-5 fw-bold">Simpan
                    Akun</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const ajaxUrl = 'proses_pegawai.php';
        const modalPegawai = new bootstrap.Modal(document.getElementById('modalPegawai'));
        const modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));

        // DataTable
        if ($.fn.DataTable) {
            $('#tabelPegawai').DataTable({
                pageLength: 25,
                language: { search: "", searchPlaceholder: "Cari..." }
            });
        }

        // Add
        $('#tombolTambahPegawai').click(function () {
            $('#formPegawai')[0].reset();
            $('#pegawai_id').val('');
            $('#modalPegawaiLabel').text('Tambah Pegawai Baru');
            modalPegawai.show();
        });

        // View Detail
        $(document).on('click', '.tombol-view', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambil', id: id }, (res) => {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#detail-nama').text(d.nm_pegawai || '-');
                    $('#detail-nip-nrk').text('NIP: ' + (d.nip || '-') + (d.nrk ? ' | NRK: ' + d.nrk : ''));
                    $('#detail-jabatan').text(d.jabatan || 'Staf');
                    $('#detail-foto').attr('src', d.foto ? '../file/datakepegawaian/' + d.foto : '../images/default.png');

                    let html = `
                        <div class="col-6"><label class="modern-label">Status</label><p class="fw-bold mb-0">${d.status_pegawai || '-'}</p></div>
                        <div class="col-6"><label class="modern-label">Unit Kerja</label><p class="fw-bold mb-0">${d.unit_kerja || '-'}</p></div>
                        <div class="col-6"><label class="modern-label">Pangkat/Gol</label><p class="fw-bold mb-0">${d.pangkat || '-'} (${d.golongan || '-'})</p></div>
                        <div class="col-6"><label class="modern-label">Pendidikan</label><p class="fw-bold mb-0">${d.pendidikan || '-'}</p></div>
                        <div class="col-12"><label class="modern-label">Kontak</label><p class="fw-bold mb-0">${d.no_hp || '-'} / ${d.email || '-'}</p></div>
                    `;
                    $('#detail-content-area').html(html);
                    $('#linkFullProfile').attr('href', `index_ptk.php?data_saya&id=${d.id}`);
                    modalDetail.show();
                }
            }, 'json');
        });

        // Edit (Simplified)
        $(document).on('click', '.tombol-edit', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambil', id: id }, (res) => {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#pegawai_id').val(d.id);
                    $('#nip').val(d.nip);
                    $('#nrk').val(d.nrk);
                    $('#nm_pegawai').val(d.nm_pegawai);
                    $('#jabatan').val(d.jabatan);
                    $('#unit_kerja').val(d.unit_kerja);
                    $('#status_pegawai').val(d.status_pegawai);
                    $('#modalPegawaiLabel').text('Edit Akun Pegawai');
                    modalPegawai.show();
                }
            }, 'json');
        });

        // Submit
        $('#formPegawai').submit(function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpan');
            $.ajax({
                url: ajaxUrl, type: 'POST', data: formData, processData: false, contentType: false, dataType: 'json',
                success: (res) => {
                    if (res.status === 'success') {
                        modalPegawai.hide();
                        toastr.success(res.message);
                        setTimeout(() => location.reload(), 800);
                    } else toastr.error(res.message);
                }
            });
        });

        // Status Switch
        $(document).on('change', '.status-switch', function () {
            const el = $(this), id = el.data('id'), status = el.is(':checked') ? '1' : '0';
            $.post(ajaxUrl, { action: 'ubah_status', id: id, status: status }, (res) => {
                if (res.status === 'success') toastr.success(res.message);
                else { el.prop('checked', !el.is(':checked')); toastr.error(res.message); }
            }, 'json');
        });

        // Delete
        $(document).on('click', '.tombol-hapus', function () {
            const id = $(this).data('id');
            if (confirm('Hapus data pegawai ini?')) {
                $.post(ajaxUrl, { action: 'hapus', id: id }, (res) => {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        setTimeout(() => location.reload(), 800);
                    }
                }, 'json');
            }
        });

        // Search
        $('#customSearch').on('keyup', function () {
            $('#tabelPegawai').DataTable().search($(this).val()).draw();
        });
    });
</script>