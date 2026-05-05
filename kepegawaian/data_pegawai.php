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
    header("Location: index_ptk.php?profil");
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
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-size: 0.75rem;
        letter-spacing: 0.3px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .badge-soft-pns {
        background-color: #eff6ff;
        color: #2563eb;
        border: 1px solid #dbeafe;
    }

    .badge-soft-pppk {
        background-color: #f0fdfa;
        color: #0d9488;
        border: 1px solid #ccfbf1;
    }

    .badge-soft-honorer {
        background-color: #fffbeb;
        color: #d97706;
        border: 1px solid #fef3c7;
    }

    .badge-soft-lainnya {
        background-color: #f8fafc;
        color: #475569;
        border: 1px solid #f1f5f9;
    }

    .modern-modal {
        border-radius: 1.5rem;
        overflow: hidden;
    }

    .modern-label {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--sap-secondary);
        margin-bottom: 0.5rem;
        display: block;
        letter-spacing: 0.025em;
    }

    .modern-input,
    .form-select.modern-input {
        border-radius: 0.85rem;
        border: 1.5px solid var(--sap-gray-200);
        padding: 0.75rem 1.25rem;
        font-size: 0.95rem;
        background-color: var(--sap-gray-50);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .modern-input:focus {
        background-color: #fff;
        border-color: var(--sap-primary);
        box-shadow: 0 0 0 4px var(--sap-primary-light);
        outline: none;
    }

    .btn-rounded {
        border-radius: 50px;
        padding-left: 1.5rem;
        padding-right: 1.5rem;
    }

    #customSearch {
        transition: all 0.3s ease;
        border: 1px solid transparent !important;
    }

    #customSearch:focus {
        width: 350px !important;
        background-color: #fff !important;
        border-color: var(--sap-primary) !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
    }

    /* Import Modal Styles */
    .drop-zone {
        border: 2px dashed var(--sap-gray-200);
        border-radius: 1.25rem;
        padding: 2.5rem;
        text-align: center;
        background: var(--sap-gray-50);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .drop-zone:hover,
    .drop-zone.active {
        border-color: var(--sap-primary);
        background: var(--sap-primary-light);
    }

    .drop-zone i {
        font-size: 3rem;
        color: var(--sap-primary);
        margin-bottom: 0.5rem;
    }

    .progress-compact {
        height: 6px;
        border-radius: 10px;
        background: #f1f5f9;
    }
</style>

<div class="py-3"></div>
<div class="container-fluid">
    <div class="d-md-flex justify-content-between align-items-center mb-1">
        <div>
            <h2 class="page-title mb-1">Manajemen Kepegawaian</h2>
            <p class="text-muted small mb-0">Kelola database profil, jabatan, dan status kepegawaian secara terpusat</p>
        </div>
    </div>

    <div class="modern-card">
        <div class="modern-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2" style="position: relative; z-index: 100;">
                <button type="button" class="btn btn-primary btn-sm btn-rounded px-4 shadow-sm"
                    id="tombolTambahPegawai">
                    <i class="las la-user-plus me-2"></i> Tambah Pegawai
                </button>
                <button type="button" class="btn btn-outline-info btn-sm btn-rounded px-4 shadow-sm" id="btnSyncPensiun">
                    <i class="las la-sync me-2"></i> Sinkron Pensiun
                </button>
                <a href="?import_data_pegawai" class="btn btn-outline-success btn-sm btn-rounded px-4 shadow-sm" id="btnShowImport">
                    <i class="las la-file-excel me-2"></i> Import Excel
                </a>
                <a href="proses_pegawai.php?action=download" class="btn btn-success btn-sm btn-rounded px-4 shadow-sm">
                    <i class="las la-file-excel me-2"></i> Download Data
                </a>
            </div>

            <div class="position-relative">
                <i class="las la-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="customSearch" class="form-control form-control-sm modern-input ps-5"
                    placeholder="Cari nama, NIP, atau jabatan..." style="min-width: 280px;">
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tabelPegawai" style="width:100%;">
                    <thead class="box-shadow-0 bg-gradient-x-secondary">
                        <tr class="text-white">
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
                                    <div class="fw-bold text-dark mb-0"><?php echo $row['nm_pegawai']; ?></div>
                                    <div class="small text-muted mt-n1">
                                        NIP: <?php echo $row['nip'] ?: '-'; ?> <span class="mx-1 text-gray-300">|</span>
                                        NRK: <?php echo $row['nrk'] ?: '-'; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-medium text-dark mb-0"><?php echo $row['jabatan'] ?: '-'; ?></div>
                                    <div class="extra-small text-muted mt-n1"><?php echo $row['unit_kerja'] ?: '-'; ?></div>
                                </td>
                                <td class="text-center">
                                    <span class="badge-soft <?php echo $cls; ?>">
                                        <?php echo $row['status_pegawai'] ?: '-'; ?>
                                    </span>
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
                                                class="las la-eye text-primary"></i></button>
                                        <button class="btn btn-sm btn-light border shadow-sm tombol-edit"
                                            data-id="<?php echo $row['id']; ?>" title="Edit Cepat"><i
                                                class="las la-edit text-warning"></i></button>
                                        <button class="btn btn-sm btn-light border shadow-sm tombol-hapus"
                                            data-id="<?php echo $row['id']; ?>" title="Hapus"><i
                                                class="las la-trash text-danger"></i></button>
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
                            <i class="las la-external-link-alt me-2"></i>Lihat Profil Lengkap & Riwayat
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


<!-- SheetJS Library -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<style>
    /* Fixed Columns Robust Styling */
    .DTFC_LeftWrapper,
    .DTFC_RightWrapper {
        z-index: 20 !important;
    }

    /* Ensure fixed cells have a solid background */
    .DTFC_LeftBodyWrapper td,
    .DTFC_LeftHeadWrapper th,
    .DTFC_RightBodyWrapper td,
    .DTFC_RightHeadWrapper th {
        background-color: #fff !important;
        border-right: 1px solid var(--sap-gray-100) !important;
    }

    .DTFC_RightBodyWrapper td,
    .DTFC_RightHeadWrapper th {
        border-right: none !important;
        border-left: 1px solid var(--sap-gray-100) !important;
    }

    /* Shadow effects for separation */
    .DTFC_LeftWrapper {
        box-shadow: 15px 0 30px -15px rgba(0, 0, 0, 0.08) !important;
    }

    .DTFC_RightWrapper {
        box-shadow: -15px 0 30px -15px rgba(0, 0, 0, 0.08) !important;
    }

    #tabelPegawai {
        border-collapse: separate !important;
        border-spacing: 0;
    }

    #tabelPegawai th {
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.075em;
        font-weight: 700;
        color: var(--sap-secondary);
        padding: 1.25rem 1rem;
        border-top: none;
        background-color: var(--sap-gray-50);
    }

    #tabelPegawai td {
        padding: 1.1rem 1rem;
        white-space: nowrap;
        border-bottom: 1px solid var(--sap-gray-100);
        transition: background-color 0.2s ease;
    }

    #tabelPegawai tr:hover td {
        background-color: rgba(79, 70, 229, 0.02) !important;
    }

    /* Fix for header alignment */
    .dataTables_scrollHeadInner,
    .dataTables_scrollHeadInner table {
        width: 100% !important;
    }

    /* Hide standard DT components we replaced */
    .dataTables_filter,
    .dataTables_length {
        display: none;
    }

    .dataTables_info {
        padding: 1.5rem !important;
        font-size: 0.85rem;
        color: var(--sap-secondary);
        font-weight: 500;
    }

    .dataTables_paginate {
        padding: 1rem 1.5rem !important;
    }

    .paginate_button.page-item.active .page-link {
        background-color: var(--sap-primary);
        border-color: var(--sap-primary);
        box-shadow: 0 4px 10px -2px var(--sap-primary-light);
    }

    .page-link {
        border-radius: 8px !important;
        margin: 0 3px;
        color: var(--sap-secondary);
        border: none;
        padding: 0.5rem 0.8rem;
    }
</style>

<script>
    $(document).ready(function () {
        const ajaxUrl = 'proses_pegawai.php';
        const modalPegawai = new bootstrap.Modal(document.getElementById('modalPegawai'));
        const modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));

        // DataTable with FixedColumns
        $('.content table.table').DataTable({
            scrollY: 450,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
        });

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
            table.search($(this).val()).draw();
        });

        // Sync Pensiun Action
        $('#btnSyncPensiun').click(function() {
            const btn = $(this);
            const originalHtml = btn.html();
            
            btn.prop('disabled', true).html('<i class="las la-sync la-spin me-2"></i> Syncing...');
            
            $.post(ajaxUrl, { action: 'syncPensiun' }, function(res) {
                if(res.status === 'success') {
                    toastr.success(res.message);
                    setTimeout(() => {
                        window.location.href = '?data_pensiun&sync=' + new Date().getTime();
                    }, 1500);
                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false).html(originalHtml);
                }
            }, 'json').fail(function() {
                toastr.error('Gagal menghubungi server.');
                btn.prop('disabled', false).html(originalHtml);
            });
        });

    });
</script>