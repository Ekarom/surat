<?php
/**
 * S.A.P KEPEGAWAIAN - Personnel Management Module
 * Premium DataTables Implementation with FixedColumns
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$lv = $_SESSION['level'] ?? '';
$nik = $_SESSION['nik'] ?? '';
?>


<style>
    /* CSS Variables & Core Styles */
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

    /* Page Header */
    .page-title {
        font-weight: 800;
        color: var(--sap-dark);
        letter-spacing: -0.025em;
    }

    /* Modern Card */
    .modern-card {
        background: #fff;
        border-radius: var(--sap-border-radius);
        border: none;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }

    .modern-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    .modern-card-header {
        background-color: #fff;
        border-bottom: 1px solid var(--sap-gray-100);
        padding: 1.25rem 1.5rem;
    }

    /* Table Styles - Refined Standard Table */
    .table-standard {
        border-collapse: separate;
        border-spacing: 0;
        width: 100%;
    }

    .table-standard thead th {
        background-color: var(--sap-gray-50);
        color: var(--sap-secondary);
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        padding: 1rem;
        border-bottom: 2px solid var(--sap-gray-100);
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .table-standard tbody td {
        padding: 0.85rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid var(--sap-gray-100);
        font-size: 0.85rem;
    }

    .table-standard tbody tr:hover {
        background-color: var(--sap-gray-50);
    }

    /* Badges Style */
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

    /* Modals */
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

    /* Photo Containers */
    .photo-preview-wrapper {
        position: relative;
        width: 160px;
        height: 160px;
        margin: 0 auto;
    }

    .photo-preview {
        width: 100%;
        height: 100%;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #fff;
        box-shadow: var(--sap-shadow-md);
    }

    .photo-upload-btn {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: var(--sap-primary-gradient);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        border: 3px solid #fff;
        box-shadow: var(--sap-shadow-sm);
        transition: transform 0.2s;
    }

    .photo-upload-btn:hover {
        transform: scale(1.1);
    }

    /* Detail Modal Styles */
    .detail-header {
        background: var(--sap-primary-gradient);
        padding: 3rem 1.5rem 5rem 1.5rem;
        text-align: center;
    }

    .detail-body {
        margin-top: -4rem;
        background: #fff;
        border-radius: 2rem 2rem 0 0;
        padding: 2.5rem 2rem;
        box-shadow: 0 -10px 20px -5px rgba(0, 0, 0, 0.05);
    }

    .detail-label {
        font-size: 0.65rem;
        font-weight: 600;
        text-transform: uppercase;
        color: var(--sap-secondary);
        margin-bottom: 2px;
    }

    .detail-value {
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--sap-dark);
        margin-bottom: 0;
    }

    /* Custom Search Bar */
    #customSearch {
        transition: all 0.3s ease;
        border: 1px solid transparent !important;
    }

    #customSearch:focus {
        width: 300px !important;
        background-color: #fff !important;
        border-color: var(--sap-primary) !important;
        box-shadow: 0 0 0 4px var(--sap-primary-light) !important;
    }

    .bg-soft-primary {
        background-color: var(--sap-primary-light);
        color: var(--sap-primary);
        font-weight: 600;
    }

    .btn-square {
        border-radius: 0.5rem;
    }

    .btn-rounded {
        border-radius: 50px;
    }

    /* Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Pagination Styling */
    .page-link {
        border: none;
        background: transparent;
        color: var(--sap-secondary);
        font-weight: 600;
        font-size: 0.85rem;
        padding: 0.6rem 0.9rem;
        border-radius: 0.75rem !important;
        transition: all 0.2s;
        margin: 0 2px;
    }

    .page-link:hover {
        background-color: var(--sap-gray-100);
        color: var(--sap-primary);
    }

    .page-item.active .page-link {
        background: var(--sap-primary-gradient);
        color: #fff;
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
    }

    .page-item.disabled .page-link {
        background: transparent;
        opacity: 0.5;
    }

    /* Riwayat Card Styles */
    .riwayat-container {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1rem;
        max-height: 500px;
        overflow-y: auto;
        padding: 0.5rem;
    }

    .riwayat-card {
        background: #fff;
        border-radius: 1rem;
        border: 1px solid var(--sap-gray-100);
        padding: 1.25rem;
        transition: all 0.2s;
        position: relative;
    }

    .riwayat-card:hover {
        border-color: var(--sap-primary);
        box-shadow: var(--sap-shadow-md);
        transform: translateY(-2px);
    }

    .riwayat-card-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--sap-gray-50);
        color: var(--sap-primary);
        font-size: 1.2rem;
        margin-bottom: 1rem;
    }

    .riwayat-card-title {
        font-weight: 700;
        font-size: 0.95rem;
        color: var(--sap-dark);
        margin-bottom: 0.25rem;
    }

    .riwayat-card-meta {
        font-size: 0.75rem;
        color: var(--sap-secondary);
        margin-bottom: 0.75rem;
    }

    .riwayat-card-footer {
        border-top: 1px solid var(--sap-gray-100);
        padding-top: 0.75rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
</style>

<div class="container-fluid">
    <!-- === HEADER === -->
    <div class="d-md-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="page-title mb-1">
                <?php echo ($lv == '4') ? 'Profil & Riwayat Mandiri' : 'Manajemen Kepegawaian'; ?></h2>
            <p class="text-muted small mb-0">
                <?php echo ($lv == '4') ? 'Lihat dan verifikasi data profil serta riwayat kepegawaian Anda.' : 'Kelola database profil, jabatan, dan status kepegawaian secara terpusat'; ?>
            </p>
        </div>
        <nav aria-label="breadcrumb" class="mt-2 mt-md-0">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item active text-primary fw-bold">
                    <?php echo ($lv == '4') ? 'Dashboard Guru' : 'Kepegawaian'; ?></li>
            </ol>
        </nav>
    </div>

    <?php if ($lv != '4'): ?>
        <div class="modern-card">
            <div class="modern-card-header d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary btn-sm btn-rounded px-4 shadow-sm"
                        id="tombolTambahPegawai">
                        <i class="fas fa-user-plus me-2"></i> Tambah Pegawai
                    </button>
                </div>

                <div class="position-relative">
                    <i class="fas fa-search position-absolute top-50 translate-middle-y ms-3 text-muted"></i>
                    <input type="text" id="customSearch"
                        class="form-control form-control-sm btn-rounded ps-5 border-0 bg-light"
                        placeholder="Cari data pegawai..." style="width: 250px; height: 36px;">
                </div>
            </div>

            <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th class="text-center" width="50">No</th>
                                <th class="text-center" width="60">Foto</th>
                                <th>Nama & Identitas</th>
                                <th>Jabatan & Unit</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Berkas SK</th>
                                <?php if ($lv != '4'): ?>
                                    <th class="text-center">Aktif</th>
                                <?php endif; ?>
                                <th class="text-center" width="150">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = "SELECT p.*, 
                                 (SELECT COUNT(*) FROM riwayat_kepegawaian r WHERE r.pegawai_id = p.id AND r.file_lampiran IS NOT NULL AND r.file_lampiran != '') as total_sk
                                 FROM pegawai p";
                            if ($lv == '4') {
                                $query .= " WHERE p.nip = '" . $conn->real_escape_string($nik) . "' OR p.nrk = '" . $conn->real_escape_string($nik) . "'";
                            }
                            $query .= " ORDER BY p.nm_pegawai ASC";
                            $result = $conn->query($query);
                            $no = 1;
                            if ($result && $result->num_rows > 0):
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
                                        <td class="text-center">
                                            <span class="fw-bold text-muted small"><?php echo $no++; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <img src="<?php echo $foto_path; ?>" class="rounded-circle shadow-sm"
                                                style="width: 36px; height: 36px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo $row['nm_pegawai'] ?? '-'; ?></div>
                                            <div class="small text-muted">
                                                NIP: <span class="text-primary"><?php echo $row['nip'] ?: '-'; ?></span> |
                                                NRK: <?php echo $row['nrk'] ?: '-'; ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-medium text-dark"><?php echo $row['jabatan'] ?? '-'; ?></div>
                                            <div class="extra-small text-muted"><?php echo $row['unit_kerja'] ?? '-'; ?></div>
                                        </td>
                                        <td class="text-center">
                                            <span
                                                class="badge-soft <?php echo $cls; ?>"><?php echo $row['status_pegawai'] ?? '-'; ?></span>
                                        </td>
                                        <td class="text-center">
                                            <?php if ($row['total_sk'] > 0): ?>
                                                <span class="badge bg-success-soft text-success rounded-pill px-2 py-1 small">
                                                    <i class="fas fa-file-check me-1"></i><?php echo $row['total_sk']; ?> File
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted extra-small italic">Kosong</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php if ($lv != '4'): ?>
                                            <td class="text-center">
                                                <div class="form-check form-switch d-flex justify-content-center">
                                                    <input class="form-check-input status-switch" type="checkbox"
                                                        data-id="<?php echo $row['id']; ?>" <?php echo ($row['status'] == '1') ? 'checked' : ''; ?>>
                                                </div>
                                            </td>
                                        <?php endif; ?>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button class="btn btn-sm btn-light border shadow-sm tombol-edit"
                                                    data-id="<?php echo $row['id']; ?>" title="Edit"><i
                                                        class="fas fa-edit text-warning"></i></button>
                                                <?php if ($lv != '4'): ?>
                                                    <button class="btn btn-sm btn-light border shadow-sm tombol-hapus"
                                                        data-id="<?php echo $row['id']; ?>" title="Hapus"><i
                                                            class="fas fa-trash text-danger"></i></button>
                                                <?php endif; ?>
                                            </div>
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
        // === VIEW KHUSUS GURU (CARD STYLE) ===
        $q_guru = $conn->query("SELECT * FROM pegawai WHERE nip = '$nik' OR nrk = '$nik' LIMIT 1");
        $guru = $q_guru->fetch_assoc();
        if ($guru):
            $foto_guru = !empty($guru['foto']) ? '../file/pegawai/' . $guru['foto'] : '../images/default.png';
            ?>
            <div class="row g-4 mb-5">
                <!-- Identity Card -->
                <div class="col-lg-4">
                    <div class="modern-card h-100">
                        <div class="card-body text-center p-5">
                            <div class="position-relative d-inline-block mb-4">
                                <img src="<?php echo $foto_guru; ?>" class="rounded-circle border border-5 border-white shadow"
                                    style="width: 180px; height: 180px; object-fit: cover;">
                                <span
                                    class="position-absolute bottom-0 end-0 bg-success border border-4 border-white rounded-circle"
                                    style="width: 30px; height: 30px;"></span>
                            </div>
                            <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($guru['nm_pegawai']); ?></h4>
                            <p class="text-muted mb-3">
                                <?php echo htmlspecialchars($guru['jabatan'] ?: 'Jabatan Belum Diatur'); ?></p>
                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <span class="badge bg-primary-soft text-primary rounded-pill px-3">NIP:
                                    <?php echo htmlspecialchars($guru['nip'] ?: '-'); ?></span>
                                <span class="badge bg-indigo-soft text-indigo rounded-pill px-3">NRK:
                                    <?php echo htmlspecialchars($guru['nrk'] ?: '-'); ?></span>
                            </div>
                            <div class="d-grid gap-2">
                                <button class="btn btn-indigo rounded-pill py-2 shadow-sm tombol-edit"
                                    data-id="<?php echo $guru['id']; ?>">
                                    <i class="fas fa-edit me-2"></i> Perbarui Biodata
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Cards -->
                <div class="col-lg-8">
                    <div class="row g-4">
                        <!-- Personal Info -->
                        <div class="col-12">
                            <div class="modern-card">
                                <div class="modern-card-header bg-white border-bottom py-3">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-user me-2 text-indigo"></i> Data Personal</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-1">Tempat,
                                                Tanggal Lahir</label>
                                            <div class="text-dark fw-semibold">
                                                <?php
                                                $tgl = (!empty($guru['tgl_lahir']) && $guru['tgl_lahir'] != '0000-00-00') ? date('d-m-Y', strtotime($guru['tgl_lahir'])) : '-';
                                                echo htmlspecialchars($guru['tempat_lahir'] ?: '-') . ", " . $tgl;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-1">Jenis
                                                Kelamin</label>
                                            <div class="text-dark fw-semibold">
                                                <?php echo ($guru['jenis_kelamin'] == 'L') ? 'Laki-laki' : 'Perempuan'; ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label
                                                class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-1">Pendidikan
                                                Terakhir</label>
                                            <div class="text-dark fw-semibold">
                                                <?php echo htmlspecialchars($guru['pendidikan'] ?: '-'); ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-1">Kontak
                                                (No. HP / Email)</label>
                                            <div class="text-dark fw-semibold">
                                                <?php echo htmlspecialchars($guru['no_hp'] ?: '-'); ?> /
                                                <?php echo htmlspecialchars($guru['email'] ?: '-'); ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employment Info -->
                        <div class="col-12">
                            <div class="modern-card">
                                <div class="modern-card-header bg-white border-bottom py-3">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-briefcase me-2 text-indigo"></i> Status
                                        Kepegawaian</h6>
                                </div>
                                <div class="card-body p-4">
                                    <div class="row g-4">
                                        <div class="col-md-6">
                                            <label
                                                class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-1">Status</label>
                                            <div><span
                                                    class="badge-soft <?php echo (strpos(strtolower($guru['status_pegawai']), 'pns') !== false) ? 'badge-soft-pns' : 'badge-soft-pppk'; ?>"><?php echo htmlspecialchars($guru['status_pegawai'] ?: '-'); ?></span>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-1">Pangkat
                                                / Golongan</label>
                                            <div class="text-dark fw-semibold">
                                                <?php echo htmlspecialchars($guru['pangkat'] ?: '-'); ?>
                                                (<?php echo htmlspecialchars($guru['golongan'] ?: '-'); ?>)</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-1">Unit
                                                Kerja</label>
                                            <div class="text-dark fw-semibold">
                                                <?php echo htmlspecialchars($guru['unit_kerja'] ?: '-'); ?></div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="text-muted small text-uppercase fw-bold letter-spacing-1 mb-1">Alamat
                                                Unit</label>
                                            <div class="text-dark fw-semibold">SMP Negeri 171 Jakarta</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- History & Documents Section (No Modal) -->
                        <div class="col-12 mt-2">
                            <div class="modern-card">
                                <div class="modern-card-header d-flex justify-content-between align-items-center py-3">
                                    <h6 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2 text-indigo"></i> Riwayat &
                                        Berkas SK</h6>
                                    <button class="btn btn-primary btn-sm btn-rounded px-3" id="tombolTambahRiwayatGuru"
                                        data-id="<?php echo $guru['id']; ?>">
                                        <i class="fas fa-plus me-1"></i> Tambah Berkas
                                    </button>
                                </div>
                                <div class="card-body p-4">
                                    <div class="riwayat-container" style="max-height: none; overflow: visible;">
                                        <?php
                                        $id_g = $guru['id'];
                                        $q_riwayat = $conn->query("SELECT * FROM riwayat_kepegawaian WHERE pegawai_id = '$id_g' ORDER BY tmt DESC");
                                        if ($q_riwayat && $q_riwayat->num_rows > 0):
                                            while ($r = $q_riwayat->fetch_assoc()):
                                                $icon = $r['kategori'] == 'Pangkat' ? 'fa-award' : ($r['kategori'] == 'Pendidikan' ? 'fa-graduation-cap' : 'fa-file-signature');
                                                $has_file = !empty($r['file_lampiran']);
                                                ?>
                                                <div class="riwayat-card">
                                                    <div class="riwayat-card-icon"><i class="fas <?php echo $icon; ?>"></i></div>
                                                    <div class="riwayat-card-title"><?php echo htmlspecialchars($r['deskripsi']); ?>
                                                    </div>
                                                    <div class="riwayat-card-meta">
                                                        <span class="me-2"><i class="far fa-calendar-alt me-1"></i>TMT:
                                                            <?php echo date('d-m-Y', strtotime($r['tmt'])); ?></span>
                                                        <div><i class="fas fa-hashtag me-1"></i>SK:
                                                            <?php echo htmlspecialchars($r['no_sk'] ?: '-'); ?></div>
                                                    </div>
                                                    <div class="riwayat-card-footer">
                                                        <?php if ($has_file): ?>
                                                            <a href="../file/riwayat/<?php echo $r['file_lampiran']; ?>" target="_blank"
                                                                class="btn btn-sm btn-soft-danger rounded-pill px-3 py-1 extra-small">
                                                                <i class="fas fa-file-pdf me-1"></i>Lihat SK
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted extra-small italic"><i
                                                                    class="fas fa-exclamation-circle me-1"></i>Belum ada file</span>
                                                        <?php endif; ?>
                                                        <div class="d-flex gap-2">
                                                            <button class="btn btn-link text-warning p-0 edit-riwayat"
                                                                data-id="<?php echo $r['id']; ?>" title="Edit"><i
                                                                    class="fas fa-edit"></i></button>
                                                            <button class="btn btn-link text-danger p-0 hapus-riwayat"
                                                                data-id="<?php echo $r['id']; ?>" title="Hapus"><i
                                                                    class="fas fa-trash"></i></button>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php
                                            endwhile;
                                        else:
                                            ?>
                                            <div class="col-12 text-center py-5 text-muted">
                                                <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                                <p class="small italic">Belum ada riwayat atau berkas yang diunggah.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-warning rounded-4 shadow-sm">Data biodata tidak ditemukan.</div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- === MODAL: FORM PEGAWAI === -->
<div class="modal fade" id="modalPegawai" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content modern-modal border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="modalPegawaiLabel">
                    <i class="fas fa-user-circle me-2 text-primary"></i>Form Pegawai
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formPegawai" enctype="multipart/form-data">
                    <input type="hidden" name="id" id="pegawai_id">
                    <input type="hidden" name="foto_lama" id="foto_lama">

                    <div class="row g-4">
                        <!-- Photo Upload Section -->
                        <div class="col-md-4 text-center">
                            <div class="photo-preview-wrapper">
                                <img id="preview-foto" src="../images/default.png" class="photo-preview">
                                <label for="foto" class="photo-upload-btn">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="foto" name="foto" class="d-none" accept="image/*">
                            </div>
                            <p class="extra-small text-muted mt-3">Format: JPG/PNG, Max 2MB</p>
                        </div>

                        <!-- Basic Info Section -->
                        <div class="col-md-8">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="modern-label">NIP / NIK <span class="text-danger">*</span></label>
                                    <input type="text" name="nip" id="nip" class="form-control modern-input" required
                                        placeholder="NIP Pegawai">
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">NRK</label>
                                    <input type="text" name="nrk" id="nrk" class="form-control modern-input"
                                        placeholder="NRK Pegawai">
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="nm_pegawai" id="nm_pegawai"
                                        class="form-control modern-input" required placeholder="Nama Lengkap">
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Tempat Lahir</label>
                                    <input type="text" name="tempat_lahir" id="tempat_lahir"
                                        class="form-control modern-input" placeholder="Kota Lahir">
                                </div>
                                <div class="col-md-6">
                                    <label class="modern-label">Tanggal Lahir</label>
                                    <input type="date" name="tgl_lahir" id="tgl_lahir"
                                        class="form-control modern-input">
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Jenis Kelamin</label>
                                    <select name="jenis_kelamin" id="jenis_kelamin" class="form-select modern-input">
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Pendidikan</label>
                                    <input type="text" name="pendidikan" id="pendidikan"
                                        class="form-control modern-input" placeholder="S1 - ...">
                                </div>
                                <div class="col-md-4">
                                    <label class="modern-label">Tgl Lulus</label>
                                    <input type="date" name="tgl_lulus" id="tgl_lulus"
                                        class="form-control modern-input">
                                </div>
                            </div>
                        </div>

                        <!-- Employment Info Section -->
                        <div class="col-12">
                            <div class="bg-light p-4 rounded-4">
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="modern-label">Jabatan</label>
                                        <input type="text" name="jabatan" id="jabatan" class="form-control modern-input"
                                            placeholder="Staf">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="modern-label">Pangkat</label>
                                        <input type="text" name="pangkat" id="pangkat" class="form-control modern-input"
                                            placeholder="Pangkat">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="modern-label">Golongan</label>
                                        <input type="text" name="golongan" id="golongan"
                                            class="form-control modern-input" placeholder="III/a">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="modern-label">TMT Golongan</label>
                                        <input type="date" name="tmt_golongan" id="tmt_golongan"
                                            class="form-control modern-input">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="modern-label">Unit Kerja</label>
                                        <input type="text" name="unit_kerja" id="unit_kerja"
                                            class="form-control modern-input" placeholder="Bidang/Unit">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="modern-label">Status Kepegawaian</label>
                                        <select name="status_pegawai" id="status_pegawai"
                                            class="form-select modern-input">
                                            <option value="PNS">PNS</option>
                                            <option value="PPPK">PPPK</option>
                                            <option value="Honorer">Honorer</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Info Section -->
                        <div class="col-md-6">
                            <label class="modern-label">No. HP / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="fab fa-whatsapp text-success"></i></span>
                                <input type="text" name="no_hp" id="no_hp" class="form-control modern-input border-0"
                                    placeholder="08...">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-0"><i
                                        class="fas fa-envelope text-primary"></i></span>
                                <input type="email" name="email" id="email" class="form-control modern-input border-0"
                                    placeholder="email@domain.com">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light btn-rounded px-4 fw-bold"
                    data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formPegawai" class="btn btn-primary btn-rounded px-5 fw-bold">Simpan
                    Data</button>
            </div>
        </div>
    </div>
</div>

<!-- === MODAL: DETAIL PEGAWAI === -->
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 position-absolute end-0 top-0" style="z-index: 10;">
                <button type="button" class="btn-close bg-white rounded-circle p-2 shadow-sm" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="detail-header text-white">
                    <img id="detail-foto" src="../images/default.png" class="photo-preview mb-3"
                        style="width: 120px; height: 120px; border-width: 5px;">
                    <h4 id="detail-nama" class="fw-bold mb-1"></h4>
                    <p id="detail-nip-nrk" class="opacity-75 small mb-0"></p>
                </div>
                <div class="detail-body">
                    <div class="text-center mb-4">
                        <span class="badge bg-soft-primary px-4 py-2 rounded-pill fw-bold" id="detail-jabatan"></span>
                    </div>

                    <div class="row g-4">
                        <div class="col-6">
                            <label class="detail-label">Tempat/Tgl Lahir</label>
                            <p class="detail-value" id="detail-ttl"></p>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Jenis Kelamin</label>
                            <p class="detail-value" id="detail-jk"></p>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Pangkat/Gol & TMT</label>
                            <p class="detail-value" id="detail-pangkat-tmt"></p>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Pendidikan & Lulus</label>
                            <p class="detail-value" id="detail-pendidikan-tgl"></p>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Unit Kerja</label>
                            <p class="detail-value" id="detail-unit"></p>
                        </div>
                        <div class="col-6">
                            <label class="detail-label">Status Pegawai</label>
                            <p class="detail-value" id="detail-status-pegawai"></p>
                        </div>

                        <div class="col-12 pt-3 mt-3 border-top">
                            <div class="row">
                                <div class="col-6">
                                    <label class="detail-label">WhatsApp</label>
                                    <p class="detail-value text-success" id="detail-hp"></p>
                                </div>
                                <div class="col-6">
                                    <label class="detail-label">Email</label>
                                    <p class="detail-value text-primary" id="detail-email"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- === MODAL: FORM RIWAYAT === -->
<div class="modal fade" id="modalFormRiwayat" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modern-modal border-0 shadow-lg">
            <div class="modal-header border-0 pb-0 pt-4 px-4">
                <h5 class="modal-title fw-bold" id="modalFormRiwayatLabel">Form Riwayat</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="formRiwayat" enctype="multipart/form-data">
                    <input type="hidden" name="id_riwayat" id="id_riwayat">
                    <input type="hidden" name="pegawai_id_riwayat" id="pegawai_id_riwayat">
                    <input type="hidden" name="file_lama_riwayat" id="file_lama_riwayat">

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="modern-label">Kategori</label>
                            <select name="kategori" id="riwayat_kategori" class="form-select modern-input" required>
                                <option value="Pangkat">Riwayat Pangkat/Golongan</option>
                                <option value="Jabatan">Riwayat Jabatan</option>
                                <option value="Pendidikan">Riwayat Pendidikan</option>
                                <option value="Diklat">Riwayat Diklat/Pelatihan</option>
                                <option value="Penghargaan">Penghargaan</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="modern-label">Deskripsi / Keterangan</label>
                            <textarea name="deskripsi" id="riwayat_deskripsi" class="form-control modern-input" rows="2"
                                required placeholder="Contoh: Penata Muda / IIIa atau Kepala Seksi..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">TMT (Terhitung Mulai Tanggal)</label>
                            <input type="date" name="tmt" id="riwayat_tmt" class="form-control modern-input">
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">No. SK</label>
                            <input type="text" name="no_sk" id="riwayat_no_sk" class="form-control modern-input"
                                placeholder="Nomor Surat Keputusan">
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">Tanggal SK</label>
                            <input type="date" name="tgl_sk" id="riwayat_tgl_sk" class="form-control modern-input">
                        </div>
                        <div class="col-md-6">
                            <label class="modern-label">Lampiran SK (PDF/JPG)</label>
                            <input type="file" name="file_lampiran" id="riwayat_file" class="form-control modern-input">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light btn-rounded px-4 fw-bold"
                    data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formRiwayat"
                    class="btn btn-primary btn-rounded px-5 fw-bold">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- === SCRIPTS === -->

<script>
    $(document).ready(function () {
        const ajaxUrl = 'proses_pegawai.php';
        let modalPegawai, modalDetail, modalRiwayat, modalFormRiwayat;

        // Initialize Modals
        const initModals = () => {
            if (typeof bootstrap !== 'undefined') {
                modalPegawai = new bootstrap.Modal(document.getElementById('modalPegawai'));
                modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));
                modalFormRiwayat = new bootstrap.Modal(document.getElementById('modalFormRiwayat'));
            } else {
                setTimeout(initModals, 100);
            }
        };
        initModals();

        // DataTable Init
        if ($.fn.DataTable) {
            $('.content table.table').DataTable({
                scrollY: 450,
                scrollX: true,
                scrollCollapse: true,
                paging: false,
            });
        }

        // Helpers
        const formatDate = (dateStr) => {
            if (!dateStr || dateStr === '0000-00-00') return '-';
            const parts = dateStr.split('-');
            return parts.length === 3 ? `${parts[2]}-${parts[1]}-${parts[0]}` : dateStr;
        };

        // Toastr Config
        toastr.options = { "closeButton": true, "progressBar": true, "positionClass": "toast-top-right" };

        // Simple Search
        $('#customSearch').on('keyup', function () {
            const value = $(this).val().toLowerCase();
            $('#tabelPegawai tbody tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
            });
            $('#totalPegawaiBadge').text($('#tabelPegawai tbody tr:visible').length + ' Pegawai');
        });

        // Initialize Total
        $('#totalPegawaiBadge').text($('#tabelPegawai tbody tr').length + ' Pegawai');

        // Refresh
        $('#tombolRefresh').click(function () {
            location.reload();
        });

        // Form: Create
        $('#tombolTambahPegawai').click(function () {
            $('#formPegawai')[0].reset();
            $('#pegawai_id, #foto_lama').val('');
            $('#preview-foto').attr('src', '../images/default.png');
            $('#modalPegawaiLabel').html('<i class="fas fa-user-plus me-2 text-primary"></i>Tambah Pegawai Baru');
            modalPegawai.show();
        });

        // Form: Photo Preview
        $('#foto').change(function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => $('#preview-foto').attr('src', e.target.result);
                reader.readAsDataURL(file);
            }
        });

        // Form: Submit
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

        // Action: Edit
        $(document).on('click', '.tombol-edit', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambil', id: id }, (res) => {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#pegawai_id').val(d.id); $('#nip').val(d.nip); $('#nrk').val(d.nrk); $('#nm_pegawai').val(d.nm_pegawai);
                    $('#tempat_lahir').val(d.tempat_lahir); $('#tgl_lahir').val(d.tgl_lahir); $('#jenis_kelamin').val(d.jenis_kelamin);
                    $('#jabatan').val(d.jabatan); $('#pangkat').val(d.pangkat); $('#golongan').val(d.golongan);
                    $('#unit_kerja').val(d.unit_kerja); $('#status_pegawai').val(d.status_pegawai); $('#pendidikan').val(d.pendidikan);
                    $('#tgl_lulus').val(d.tgl_lulus); $('#tmt_golongan').val(d.tmt_golongan); $('#no_hp').val(d.no_hp);
                    $('#email').val(d.email); $('#foto_lama').val(d.foto);
                    const foto = d.foto ? '../file/pegawai/' + d.foto : '../images/default.png';
                    $('#preview-foto').attr('src', foto);
                    $('#modalPegawaiLabel').html('<i class="fas fa-user-edit me-2 text-primary"></i>Edit Data Pegawai');
                    modalPegawai.show();
                }
            }, 'json');
        });

        // Action: Detail
        $(document).on('click', '.tombol-view', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambil', id: id }, (res) => {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#detail-nama').text(d.nm_pegawai || '-');
                    $('#detail-nip-nrk').text('NIP: ' + (d.nip || '-') + (d.nrk ? ' | NRK: ' + d.nrk : ''));
                    $('#detail-jabatan').text(d.jabatan || 'Staf');

                    let ttl = '-';
                    const tgl = formatDate(d.tgl_lahir);
                    if (d.tempat_lahir && tgl !== '-') ttl = d.tempat_lahir + ', ' + tgl;
                    else ttl = d.tempat_lahir || tgl;
                    $('#detail-ttl').text(ttl);
                    $('#detail-jk').text(d.jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan');

                    const tmt = formatDate(d.tmt_golongan);
                    let pGol = (d.pangkat || '') + (d.pangkat && d.golongan ? ' / ' : '') + (d.golongan || '');
                    if (pGol && tmt !== '-') pGol += ` (TMT: ${tmt})`;
                    $('#detail-pangkat-tmt').text(pGol || '-');

                    const lulus = formatDate(d.tgl_lulus);
                    let pend = d.pendidikan || '';
                    if (pend && lulus !== '-') pend += ` (Lulus: ${lulus})`;
                    $('#detail-pendidikan-tgl').text(pend || '-');

                    $('#detail-unit').text(d.unit_kerja || '-');
                    $('#detail-status-pegawai').text(d.status_pegawai || '-');
                    $('#detail-hp').text(d.no_hp || '-');
                    $('#detail-email').text(d.email || '-');
                    const foto = d.foto ? '../file/pegawai/' + d.foto : '../images/default.png';
                    $('#detail-foto').attr('src', foto);
                    modalDetail.show();
                }
            }, 'json');
        });

        // Action: Status
        $(document).on('change', '.status-switch', function () {
            const el = $(this), id = el.data('id'), status = el.is(':checked') ? '1' : '0';
            $.post(ajaxUrl, { action: 'ubah_status', id: id, status: status }, (res) => {
                if (res.status === 'success') toastr.success(res.message);
                else { el.prop('checked', !el.is(':checked')); toastr.error(res.message); }
            }, 'json');
        });

        // Action: Delete
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

        // === RIWAYAT LOGIC ===

        // Specific for Guru Dashboard (No Modal)
        $('#tombolTambahRiwayatGuru').click(function () {
            const id = $(this).data('id');
            $('#formRiwayat')[0].reset();
            $('#pegawai_id_riwayat').val(id);
            $('#id_riwayat, #file_lama_riwayat').val('');
            $('#modalFormRiwayatLabel').text('Tambah Riwayat');
            modalFormRiwayat.show();
        });

        $('#tombolTambahRiwayat').click(function () {
            $('#formRiwayat')[0].reset();
            $('#id_riwayat, #file_lama_riwayat').val('');
            $('#modalFormRiwayatLabel').text('Tambah Riwayat');
            modalFormRiwayat.show();
        });

        $(document).on('click', '.edit-riwayat', function () {
            const id = $(this).data('id');
            $.get(ajaxUrl, { action: 'ambilRiwayat', id: id }, (res) => {
                const d = res.data;
                $('#id_riwayat').val(d.id);
                $('#riwayat_kategori').val(d.kategori);
                $('#riwayat_deskripsi').val(d.deskripsi);
                $('#riwayat_tmt').val(d.tmt);
                $('#riwayat_no_sk').val(d.no_sk);
                $('#riwayat_tgl_sk').val(d.tgl_sk);
                $('#file_lama_riwayat').val(d.file_lampiran);
                $('#modalFormRiwayatLabel').text('Edit Riwayat');
                modalFormRiwayat.show();
            }, 'json');
        });

        $('#formRiwayat').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            formData.append('action', 'simpanRiwayat');
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        modalFormRiwayat.hide();
                        // For Teacher View (No Modal), we reload to refresh the PHP-rendered cards
                        if ("<?php echo $lv; ?>" == "4") {
                            setTimeout(() => location.reload(), 500);
                        } else {
                            loadRiwayat($('#pegawai_id_riwayat').val());
                        }
                    } else {
                        toastr.error(res.message);
                    }
                }
            });
        });

        $(document).on('click', '.hapus-riwayat', function () {
            const id = $(this).data('id');
            if (confirm('Hapus riwayat ini?')) {
                $.post(ajaxUrl, { action: 'hapusRiwayat', id: id }, (res) => {
                    if (res.status === 'success') {
                        toastr.success(res.message);
                        if ("<?php echo $lv; ?>" == "4") {
                            setTimeout(() => location.reload(), 500);
                        } else {
                            loadRiwayat($('#pegawai_id_riwayat').val());
                        }
                    }
                }, 'json');
            }
        });
    });
</script>