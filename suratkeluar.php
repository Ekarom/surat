<?php
// File ini adalah halaman antarmuka pengguna (UI) untuk manajemen data surat keluar.
// Menggunakan Bootstrap 5, jQuery, dan AJAX untuk operasi CRUD yang dinamis.

// Pastikan conn.php sudah di-include dari file induk
// atau include di sini jika file ini berdiri sendiri.
include_once 'dbconn.php';

// --- Inisialisasi Variabel Global (Safety) ---
if (!isset($lv))
    $lv = $_SESSION['level'] ?? '3';
if (!isset($tahunsklh))
    $tahunsklh = $_SESSION['tahundb'] ?? '2025';
if (!isset($dataRows))
    $dataRows = [];

// --- Tentukan Tahun Aktif ---
if (isset($_GET['tahun'])) {
    $tahun_aktif = $_GET['tahun'];
} else {
    $tahun_aktif = $tahunsklh ?? ($_SESSION['tahundb'] ?? '2025');
}

// [PERBAIKAN] Normalisasi tahun_aktif (Pastikan format 4 digit angka, e.g. 2024/2025 -> 2024)
if ($tahun_aktif !== '' && preg_match('/(\d{4})/', $tahun_aktif, $matches)) {
    $tahun_aktif = $matches[1];
} elseif ($tahun_aktif !== '') {
    $tahun_aktif = '2025';
}

// --- Konstruksi Subfolder Dinamis untuk PDF ---
$sysTapel = $tahun_aktif;
$sysSmt = '1';

// Normalisasi Semester
if (strtolower($sysSmt) === 'ganjil')
    $sysSmt = '1';
if (strtolower($sysSmt) === 'genap')
    $sysSmt = '2';

// Normalisasi Tapel (2024/2025 -> 2024-2025)
$cleanTapel = str_replace(['/', '\\'], '-', $sysTapel);

// Bentuk Subfolder: "2025-1/"
$pdfSubfolder = $cleanTapel . '-' . $sysSmt . '/';

// --- Ambil data untuk datalist di awal ---
$kategori_options = '';
$jenis_options = '';
$dari_options = '';
$unit_options = '';


// Memastikan variabel $conn valid sebelum digunakan.
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {

    // 1. Ambil data jenis dokumen
    // Menambahkan penanganan error untuk memudahkan debugging.
    $sql_jenis = mysqli_query($conn, "SELECT jns_dokumen FROM tbl_jns_dokumen ORDER BY id ASC") or die(mysqli_error($conn));
    while ($datajenis = mysqli_fetch_array($sql_jenis)) {
        // Gunakan htmlspecialchars untuk keamanan dari XSS
        $nama_jenis = htmlspecialchars($datajenis['jns_dokumen']);
        $jenis_options .= "<option value=\"$nama_jenis\">";
    }
    // 2. Ambil data dari
    // Menambahkan penanganan error untuk memudahkan debugging.
    $sql_dari = mysqli_query($conn, "SELECT nm_pegawai FROM tbl_pegawai ORDER BY id ASC") or die(mysqli_error($conn));
    while ($datanama = mysqli_fetch_array($sql_dari)) {
        // Gunakan htmlspecialchars untuk keamanan dari XSS
        $nama_pegawai = htmlspecialchars($datanama['nm_pegawai']);
        $dari_options .= "<option value=\"$nama_pegawai\">";
    }


    // 3. [FIX] Ambil data kategori (Sebelumnya kosong)
    // Asumsi nama tabel adalah 'tbl_kategori_surat' dan kolom 'keterangan'.
    // Sesuaikan jika nama tabel/kolom berbeda.
    $sql_kategori = mysqli_query($conn, "SELECT jns_kategori FROM tbl_kategori ORDER BY id ASC") or die(mysqli_error($conn));
    while ($datakategori = mysqli_fetch_array($sql_kategori)) {
        $nama_kategori = htmlspecialchars($datakategori['jns_kategori']);
        $kategori_options .= "<option value=\"$nama_kategori\">";
    }
    // 4. [FIX] Ambil data unit (Sebelumnya kosong)
    // Asumsi nama tabel adalah 'tbl_kategori_surat' dan kolom 'keterangan'.
    // Sesuaikan jika nama tabel/kolom berbeda.
    $sql_unit = mysqli_query($conn, "SELECT kd_unit FROM tbl_unit ORDER BY id ASC") or die(mysqli_error($conn));
    while ($dataunit = mysqli_fetch_array($sql_unit)) {
        $nama_unit = htmlspecialchars($dataunit['kd_unit']);
        $unit_options .= "<option value=\"$nama_unit\">";
    }

    // 5. Ambil data tahun dari database (SHOW DATABASES LIKE 'sas_%')
    $tahun_options = "<option value=''>Semua</option>";
    $years_list = [];

    $sql_dbs = mysqli_query($conn, "SHOW DATABASES LIKE 'sas_%'");
    if ($sql_dbs) {
        while ($row = mysqli_fetch_array($sql_dbs)) {
            $dbname = $row[0];
            if (preg_match('/^sas_(\d+)$/', $dbname, $matches)) {
                $years_list[] = $matches[1];
            }
        }
    }

    // Pastikan 2025 dan 2026 selalu ada dalam daftar pilihan (Tanpa harus ada DB aktif dulu)
    if (!in_array('2025', $years_list))
        $years_list[] = '2025';
    if (!in_array('2026', $years_list))
        $years_list[] = '2026';

    // Urutkan tahun dari yang terbaru
    rsort($years_list);
    $years_list = array_unique($years_list);

    foreach ($years_list as $thn) {
        $selected = ($tahun_aktif == $thn) ? 'selected' : '';
        $tahun_options .= "<option value=\"$thn\" $selected>$thn</option>";
    }

    // 6. Ambil Data Surat Keluar secara Statis (Ganti AJAX)
    $dataRows = [];
    $fetch_years = ($tahun_aktif !== '') ? [$tahun_aktif] : $years_list;

    foreach ($fetch_years as $thn) {
        try {
            // Gunakan @ untuk meredam warning jika database tidak ada
            if (@mysqli_select_db($conn, "sas_" . $thn)) {
                $sql_data = mysqli_query($conn, "SELECT * FROM dokumenkeluar ORDER BY id ASC");
                if ($sql_data) {
                    while ($row = mysqli_fetch_assoc($sql_data)) {
                        $row['db_year'] = $thn; // Simpan asal tahun
                        $dataRows[] = $row;
                    }
                }
            } elseif ($tahun_aktif !== '') {
                throw new Exception("Database sas_$thn tidak ditemukan.");
            }
        } catch (Exception $e) {
            if ($tahun_aktif !== '') {
                $error_msg = 'Gagal memuat data tahun ' . $thn . ': ' . $e->getMessage();
                echo "<div class='alert alert-warning m-3'>$error_msg</div>";
            }
        }
    }
} else {
    // Tangani kasus jika conn gagal atau $conn tidak terdefinisi
    $error_msg = 'conn database gagal atau tidak terdefinisi.';
    if (isset($conn) && $conn->connect_error) {
        $error_msg .= ' Pesan error: ' . $conn->connect_error;
    }
    // Tampilkan error ini di halaman atau log, agar mudah di-debug
    echo "<div class='alert alert-danger m-3'>$error_msg</div>";
}
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">


<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- [TAMBAHAN] Loading Overlay -->


    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Surat Keluar</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Surat Keluar</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>


    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <!-- [PERBAIKAN] Merapikan header kontrol -->
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-lg flex-wrap">
                                <!-- Tombol Tambah di Kiri -->
                                <div>
                                    <?php if ($lv == "1" || $lv == "2") { ?>
                                        <button class="btn btn-outline-primary btn-sm" id="tombol-tambah">
                                            Tambah Surat
                                        </button>
                                    <?php } ?>
                                </div>

                                <!-- Kontrol Filter/Pencarian di Kanan -->
                                <div class="d-flex align-items-center flex-wrap">
                                    <label for="year-filter" class="mr-2 mb-0 text-nowrap">Tahun:</label>
                                    <select class="custom-select custom-select-sm" id="year-filter"
                                        style="width: auto;">
                                        <?php echo $tahun_options; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->

                        <div class="card-body">
                            <table class="table table-striped" style="width:100%">
                                <thead class="box-shadow-0 bg-gradient-x-secondary">
                                    <tr class="text-white">
                                        <th width="50">No</th>
                                        <th width="200px">No Surat</th>
                                        <th width="100px">Tgl Dokumen</th>
                                        <th width="200px">Dari</th>
                                        <th width="200px">Perihal</th>
                                        <th width="100px">Jenis</th>
                                        <th width="100px">Kategori</th>
                                        <th class="text-center">View</th>
                                        <th class="text-center">Edit</th>
                                        <th class="text-center">Hapus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    foreach ($dataRows as $row):
                                        $id = (int) $row['id'];
                                        $no_dokumen = htmlspecialchars($row['no_dokumen'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $perihal = htmlspecialchars($row['perihal'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $file = htmlspecialchars($row['pdf'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $dari = htmlspecialchars($row['dari'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $jns_dokumen = htmlspecialchars($row['jns_dokumen'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $kategori = htmlspecialchars($row['kategori'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $unit_tujuan = htmlspecialchars($row['unit_tujuan'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $pembuat = htmlspecialchars($row['pembuat'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $catatan = htmlspecialchars($row['catatan'] ?? '', ENT_QUOTES, 'UTF-8');
                                        $tgl_dokumen = $row['tgl_dokumen'] ?? '';

                                        $tgl_formatted = '-';
                                        if (!empty($tgl_dokumen)) {
                                            try {
                                                $tgl_formatted = (new DateTime($tgl_dokumen))->format('d M Y');
                                            } catch (Exception $e) {
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td class="text-center"><?php echo $no++; ?></td>
                                            <td class="text-left"><?php echo $no_dokumen; ?></td>
                                            <td class="text-left"><?php echo $tgl_formatted; ?></td>
                                            <td class="text-left"><?php echo $dari; ?></td>
                                            <td class="text-left" style="max-width: 250px;"><?php echo $perihal; ?></td>
                                            <td class="text-left"><span
                                                    class="badge bg-light text-dark border"><?php echo $jns_dokumen; ?></span>
                                            </td>
                                            <td class="text-left"><span
                                                    class="badge bg-info text-white"><?php echo $kategori; ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($lv == '1' || $lv == '2' || $lv == '3'): ?>
                                                    <span
                                                        class="btn-view-pdf badge badge-info badge-square <?php echo empty($file) ? 'opacity-50' : ''; ?>"
                                                        data-id="<?php echo $id; ?>" data-tahun="<?php echo $row['db_year']; ?>"
                                                        title="Lihat PDF">
                                                        <i class="la la-eye"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($lv == '1' || $lv == '2'): ?>
                                                    <span class="btn-edit badge badge-warning badge-square"
                                                        data-id="<?php echo $id; ?>" data-tahun="<?php echo $row['db_year']; ?>"
                                                        title="Edit">
                                                        <i class="la la-edit"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($lv == '1' || $lv == '2'): ?>
                                                    <span class="btn-hapus badge badge-danger badge-square"
                                                        data-id="<?php echo $id; ?>" data-nama="<?php echo $no_dokumen; ?>"
                                                        data-tahun="<?php echo $row['db_year']; ?>">
                                                        <i class="la la-trash"></i>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- /.card-footer -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
</div>
<!-- /.container-fluid -->
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->


<!-- =================================================================== -->
<!-- |                      MODAL FORM DATA                            | -->
<!-- =================================================================== -->
<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-menu-gradient">
                <b class="modal-title" id="formModalLabel"><i class="las la-plus-circle"></i> Form Data Surat</b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <!-- Pastikan 'enctype' ada untuk upload file -->
            <form id="form-surat" enctype="multipart/form-data">
                <div class="modal-body">
                    <!-- Input tersembunyi untuk ID dan aksi -->
                    <input type="hidden" id="id" name="id">
                    <input type="hidden" id="action" name="action" value="simpan">
                    <input type="hidden" id="file_lama" name="file_lama"> <!-- Untuk menyimpan nama file saat edit -->

                    <div class="row">
                        <!-- Kolom Kiri: Informasi Utama & Perihal -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="no_dokumen"><i class="fas fa-file-alt"></i> No Surat
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="no_dokumen" name="no_dokumen" required>
                            </div>

                            <div class="mb-3">
                                <label for="jns_dokumen"><i class="fas fa-tag"></i> Jenis Dokumen
                                    <span class="text-danger">*</span></label>
                                <input class="custom-select w-100" list="list_jns_dokumen_keluar" id="jns_dokumen"
                                    name="jns_dokumen" required>
                                <datalist id="list_jns_dokumen_keluar">
                                    <?php echo $jenis_options; ?>
                                </datalist>
                            </div>

                            <div class="mb-3">
                                <label for="dari"><i class="fas fa-user-circle"></i> Dari (Internal)
                                    <span class="text-danger">*</span></label>
                                <input class="custom-select w-100" list="list_dari_keluar" id="dari" name="dari"
                                    required>
                                <datalist id="list_dari_keluar">
                                    <?php echo $dari_options; ?>
                                </datalist>
                            </div>

                            <div class="mb-3">
                                <label for="unit_tujuan" class="small fw-bold text-muted text-uppercase mb-1"><i
                                        class="fas fa-building"></i> Ditujukan Ke <span
                                        class="text-danger">*</span></label>
                                <input class="custom-select w-100" list="list_tujuan_keluar" id="unit_tujuan"
                                    name="unit_tujuan" required>
                                <datalist id="list_tujuan_keluar">
                                    <?php echo $unit_options; ?>
                                </datalist>
                            </div>

                            <div class="mb-3">
                                <label for="tgl_dokumen" class="small fw-bold text-muted text-uppercase mb-1"><i
                                        class="fas fa-calendar"></i> Tgl Surat <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker" id="tgl_dokumen" name="tgl_dokumen"
                                    required>

                            </div>
                        </div>
                        <!-- /.col-md-6 -->

                        <!-- Kolom Kanan: Klasifikasi, Catatan & Lampiran -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="pembuat"><i class="fas fa-user-edit"></i> Pembuat
                                    Dokumen</label>
                                <input class="custom-select w-100" list="list_dari_keluar" id="pembuat" name="pembuat">
                            </div>

                            <div class="mb-3">
                                <label for="perihal"><i class="fas fa-align-left"></i> Perihal <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control" id="perihal" name="perihal" rows="2" required></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="kategori" class="small fw-bold text-muted text-uppercase mb-1"><i
                                            class="fas fa-folder-open"></i> Kategori</label>
                                    <input class="custom-select custom-select-sm w-100" list="list_kategori_keluar"
                                        id="kategori" name="kategori">
                                    <datalist id="list_kategori_keluar">
                                        <?php echo $kategori_options; ?>
                                    </datalist>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="catatan" class="small fw-bold text-muted text-uppercase mb-1"><i
                                            class="fas fa-sticky-note"></i> Catatan</label>
                                    <textarea class="form-control form-control-sm" id="catatan" name="catatan"
                                        rows="1"></textarea>
                                </div>
                            </div>

                            <div class="mb-0">
                                <label class="form-label fw-bold"><i class="fas fa-paperclip"></i> Lampiran File
                                    (.pdf)</label>
                                <div id="drop-zone" class="border rounded p-2 bg-white"
                                    style="border-style: dashed !important; border-width: 2px !important;">
                                    <input class="d-none" type="file" id="pdf" name="pdf" accept=".pdf" multiple>

                                    <!-- Header: Button & Drag Text -->
                                    <div class="d-flex align-items-center mb-2 px-1">
                                        <button type="button"
                                            class="btn btn-sm btn-light border-0 px-2 py-1 mr-2 shadow-sm"
                                            id="choose-file-btn"
                                            style="background-color: #f3f4f6; color: #4338ca; font-weight: 600; font-size: 0.8rem;">
                                            <i class="fas fa-folder-open mr-1"></i> Pilih...
                                        </button>
                                        <div class="text-muted" style="font-size: 0.75rem;">
                                            atau drag file kesini.
                                        </div>
                                    </div>

                                    <!-- Existing Files -->
                                    <div id="info_file_lama" class="mb-2 d-none">
                                        <div id="link_file_lama" class="d-flex flex-column gap-1"></div>
                                    </div>

                                    <!-- New Files List -->
                                    <div id="file-list" class="mb-2 d-flex flex-column gap-1"></div>

                                    <!-- Footer Info -->
                                    <div class="border-top pt-2 d-flex justify-content-between align-items-center px-1">
                                        <div class="small fw-bold text-secondary">
                                            <i class="fas fa-chart-pie mr-1"></i> Total Berkas:
                                        </div>
                                        <div class="small">
                                            <span id="file-size" class="text-primary fw-bold"
                                                style="font-size: 0.9rem;">0 B</span>
                                            <span class="text-muted ml-1" style="font-size: 0.75rem;">(Maks 2MB)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.col-md-6 -->
                    </div>
                    <!-- /.row -->
                </div>
                <!-- /.modal-body -->

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary custom" data-dismiss="modal"><i
                            class="fas fa-times"></i> Tutup</button>
                    <button type="submit" class="btn btn-primary custom" id="tombol-simpan">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- AKHIR MODAL FORM -->

<!-- =================================================================== -->
<!-- |                      MODAL EDIT DATA                            | -->
<!-- =================================================================== -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header box-shadow-0 bg-gradient-x-primary text-white">
                <b class="modal-title" id="modalEditLabel"><i class="fas fa-edit"></i> Edit Data Surat</b>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <form id="form-surat-edit" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="edit_id" name="id">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" id="edit_file_lama" name="file_lama">
                    <input type="hidden" id="edit_tahun" name="tahun">

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_no_dokumen"><i class="fas fa-file-alt"></i> No Surat
                                    <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_no_dokumen" name="no_dokumen" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_jns_dokumen"><i class="fas fa-tag"></i> Jenis
                                    Dokumen <span class="text-danger">*</span></label>
                                <input class="custom-select w-100" list="list_jns_dokumen_keluar" id="edit_jns_dokumen"
                                    name="jns_dokumen" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_dari"><i class="fas fa-user-circle"></i> Dari
                                    (Internal) <span class="text-danger">*</span></label>
                                <input class="custom-select w-100" list="list_dari_keluar" id="edit_dari" name="dari"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_unit_tujuan" class="small fw-bold text-muted text-uppercase mb-1"><i
                                        class="fas fa-building"></i> Ditujukan Ke <span
                                        class="text-danger">*</span></label>
                                <input class="custom-select w-100" list="list_tujuan_keluar" id="edit_unit_tujuan"
                                    name="unit_tujuan" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_tgl_dokumen" class="small fw-bold text-muted text-uppercase mb-1"><i
                                        class="fas fa-calendar"></i> Tgl Surat <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker" id="edit_tgl_dokumen"
                                    name="tgl_dokumen" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_pembuat"><i class="fas fa-user-edit"></i> Pembuat
                                    Dokumen</label>
                                <input class="custom-select w-100" list="list_dari_keluar" id="edit_pembuat"
                                    name="pembuat">
                            </div>
                            <div class="mb-3">
                                <label for="edit_perihal"><i class="fas fa-align-left"></i> Perihal
                                    <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_perihal" name="perihal" rows="2"
                                    required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_kategori" class="small fw-bold text-muted text-uppercase mb-1"><i
                                            class="fas fa-folder-open"></i> Kategori</label>
                                    <input class="custom-select custom-select-sm w-100" list="list_kategori_keluar"
                                        id="edit_kategori" name="kategori">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="edit_catatan" class="small fw-bold text-muted text-uppercase mb-1"><i
                                            class="fas fa-sticky-note"></i> Catatan</label>
                                    <textarea class="form-control form-control-sm" id="edit_catatan" name="catatan"
                                        rows="1"></textarea>
                                </div>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold"><i class="fas fa-paperclip"></i> Update Lampiran
                                    (.pdf)</label>
                                <div id="edit_drop-zone" class="border rounded p-2 bg-white"
                                    style="border-style: dashed !important; border-width: 2px !important;">
                                    <input class="d-none" type="file" id="edit_pdf" name="pdf[]" accept=".pdf" multiple>
                                    <div class="d-flex align-items-center mb-2 px-1">
                                        <button type="button"
                                            class="btn btn-sm btn-light border-0 px-2 py-1 mr-2 shadow-sm"
                                            id="edit_choose-file-btn"
                                            style="background-color: #f3f4f6; color: #4338ca; font-weight: 600; font-size: 0.8rem;">
                                            <i class="fas fa-folder-open mr-1"></i> Pilih...
                                        </button>
                                        <div class="text-muted" style="font-size: 0.75rem;">atau drag file kesini.</div>
                                    </div>
                                    <div id="edit_info_file_lama" class="mb-2 d-none">
                                        <div id="edit_link_file_lama" class="d-flex flex-column gap-1"></div>
                                    </div>
                                    <div id="edit_file-list" class="mb-2 d-flex flex-column gap-1"></div>
                                    <div class="border-top pt-2 d-flex justify-content-between align-items-center px-1">
                                        <div class="small fw-bold text-secondary"><i class="fas fa-chart-pie mr-1"></i>
                                            Total:</div>
                                        <div class="small"><span id="edit_file-size" class="text-primary fw-bold"
                                                style="font-size: 0.9rem;">0 B</span> <span class="text-muted ml-1"
                                                style="font-size: 0.75rem;">(Maks 2MB)</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary custom" id="edit_tombol-simpan">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- AKHIR MODAL EDIT -->


<!-- 2. Modal View Detail -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <b class="modal-title" id="viewModalLabel"><i class="las la-eye"></i> Lihat Dokumen</b>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="view_pdf_container" class="mb-3">
                    <iframe id="pdf_viewer"
                        style="width: 100%; height: 60vh; border: 1px solid #dee2e6; border-radius: .25rem;"
                        src=""></iframe>
                </div>
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr>
                            <th style="width: 30%;">No Surat</th>
                            <td id="view_no_dokumen"></td>
                        </tr>
                        <tr>
                            <th>Jenis Dokumen</th>
                            <td id="view_jns_dokumen"></td>
                        </tr>
                        <tr>
                            <th>Dari</th>
                            <td id="view_dari"></td>
                        </tr>
                        <tr>
                            <th>Unit Tujuan</th>
                            <td id="view_unit_tujuan"></td>
                        </tr>
                        <tr>
                            <th>Perihal</th>
                            <td id="view_perihal"></td>
                        </tr>
                        <tr>
                            <th>Pembuat</th>
                            <td id="view_pembuat"></td>
                        </tr>
                        <tr>
                            <th>Tanggal</th>
                            <td id="view_tgl_dokumen"></td>
                        </tr>
                        <tr>
                            <th>Kategori</th>
                            <td id="view_kategori"></td>
                        </tr>
                        <tr>
                            <th>Catatan</th>
                            <td id="view_catatan"></td>
                        </tr>
                        <tr>
                            <th>Unit Pengirim</th>
                            <td>
                                <strong><?php echo htmlspecialchars($namasek); ?></strong><br>
                                <small class="text-muted">NPSN: <?php echo htmlspecialchars($npsn); ?></small>
                            </td>
                        </tr>
                        <tr>
                            <th>File</th>
                            <td id="view_pdf"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom" data-dismiss="modal"><i class="fas fa-times"></i>
                    Tutup</button>
            </div>
        </div>
    </div>
</div>
<!-- AKHIR MODAL VIEW -->


<!-- =================================================================== -->
<!-- |                      MODAL KONFIRMASI HAPUS                     | -->
<!-- =================================================================== -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalHapusLabel"><i class="fas fa-exclamation-triangle"></i> Notifikasi</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data surat:
                <br>
                <strong><span id="detail-hapus"></span></strong>?
                <br><br>
                Tindakan ini tidak dapat dibatalkan.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i>
                    Tutup</button>
                <button type="button" class="btn btn-danger" id="tombolKonfirmasiHapus"><i class="fas fa-trash"></i>
                    Hapus</button>
            </div>
        </div>
    </div>
</div>
<!-- AKHIR MODAL HAPUS -->

<!-- =================================================================== -->
<!-- |                      MODAL NOTIFIKASI ALERT                     | -->
<!-- =================================================================== -->
<div class="modal fade" id="modalNotifikasi" tabindex="-1" aria-labelledby="modalNotifikasiLabel">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" id="notifikasi-header">
                <h5 class="modal-title" id="modalNotifikasiLabel">
                    <span id="notifikasi-title">Notifikasi</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="notifikasi-body">
                <!-- Pesan notifikasi akan diisi di sini oleh JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // [SYNC] Update selectors to be more flexible (supporting both tombol-* and btn-*)
        const SELECTOR_VIEW = '.btn-view-pdf';
        const SELECTOR_EDIT = '.btn-edit';
        const SELECTOR_HAPUS = '.btn-hapus';

        // --- 1. Variabel Global ---
        var currentPage = 1;
        var searchTimer; // Untuk debounce pencarian

        // [VALIDASI] Daftar field mandatory
        const mandatoryFields = ['no_dokumen', 'jns_dokumen', 'dari', 'unit_tujuan', 'tgl_dokumen', 'perihal'];

        /**
         * Mengecek apakah semua field mandatory sudah terisi.
         * Mengupdate UI tombol simpan dan memberikan feedback visual.
         */
        function checkFormCompletion() {
            let isComplete = true;
            mandatoryFields.forEach(fieldId => {
                const input = $(`#${fieldId}`);
                if (!input.val() || input.val().trim() === '') isComplete = false;
            });

            const hasNewFile = selectedFiles.length > 0;
            if (!hasNewFile) isComplete = false;

            const btn = $('#tombol-simpan');
            if (isComplete) {
                btn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary').html('<i class="fas fa-save"></i> Simpan');
            } else {
                btn.prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary').html('<i class="fas fa-exclamation-circle"></i> Lengkapi Data');
            }
        }

        function checkEditFormCompletion() {
            let isComplete = true;
            mandatoryFields.forEach(fieldId => {
                const input = $(`#edit_${fieldId}`);
                if (!input.val() || input.val().trim() === '') isComplete = false;
            });

            const fileLama = $('#edit_file_lama').val();
            const hasNewFile = selectedFiles.length > 0;
            const hasExistingFile = fileLama && fileLama.trim() !== '';

            if (!hasNewFile && !hasExistingFile) isComplete = false;

            const btn = $('#edit_tombol-simpan');
            if (isComplete) {
                btn.prop('disabled', false).removeClass('btn-secondary').addClass('btn-primary').html('<i class="fas fa-save"></i> Simpan Perubahan');
            } else {
                btn.prop('disabled', true).removeClass('btn-primary').addClass('btn-secondary').html('<i class="fas fa-exclamation-circle"></i> Lengkapi Data');
            }
        }

        // Jalankan pengecekan setiap kali ada input pada field mandatory
        mandatoryFields.forEach(fieldId => {
            $(`#${fieldId}`).on('input change', function () {
                checkFormCompletion();
                if ($(this).val() && $(this).val().trim() !== '') $(this).removeClass('is-invalid').addClass('is-valid');
                else $(this).removeClass('is-valid').addClass('is-invalid');
            });
            $(`#edit_${fieldId}`).on('input change', function () {
                checkEditFormCompletion();
                if ($(this).val() && $(this).val().trim() !== '') $(this).removeClass('is-invalid').addClass('is-valid');
                else $(this).removeClass('is-valid').addClass('is-invalid');
            });
        });

        // [PERUBAHAN] Mengganti fungsi placeholder dengan modal notifikasi kustom
        function tampilkanNotifikasi(message, status) {
            var header = $('#notifikasi-header'), icon = $('#notifikasi-icon'), title = $('#notifikasi-title'), body = $('#notifikasi-body');
            header.removeClass('bg-success bg-danger bg-warning bg-info text-white text-dark');
            switch (status) {
                case 'success': header.addClass('bg-success text-white'); title.text('Sukses'); break;
                case 'error': header.addClass('bg-danger text-white'); title.text('Error'); break;
                case 'warning': header.addClass('bg-warning text-dark'); title.text('Peringatan'); break;
                default: header.addClass('bg-info text-white'); title.text('Informasi'); break;
            }
            body.html(message);
            $('#modalNotifikasi').modal('show');
        }

        // --- Drag and Drop File Upload ---
        let selectedFiles = [];
        var existingFilesMap = {};
        const MAX_FILES = 1;
        const MAX_total_SIZE_MB = 2;
        const MAX_TOTAL_SIZE_BYTES = MAX_total_SIZE_MB * 1024 * 1024;

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function updateUI(forceIsEdit = null) {
            const isEditModal = forceIsEdit !== null ? forceIsEdit : $('#modalEdit').hasClass('show');
            const targetList = isEditModal ? document.getElementById('edit_file-list') : document.getElementById('file-list');
            const targetSize = isEditModal ? document.getElementById('edit_file-size') : document.getElementById('file-size');

            if (!targetList || !targetSize) return;

            targetList.innerHTML = '';
            let totalSize = 0;

            if (isEditModal) {
                Object.values(existingFilesMap).forEach(size => { totalSize += size; });
            }

            selectedFiles.forEach((file, index) => {
                totalSize += file.size;
                const div = document.createElement('div');
                div.className = 'p-2 bg-light border rounded d-flex align-items-center justify-content-between mb-2';
                div.innerHTML = `
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <div class="me-3 text-primary bg-white p-2 rounded shadow-sm"><i class="las la-file-alt fa-lg"></i></div>
                        <div class="overflow-hidden">
                            <span class="fw-bold text-primary text-truncate d-block" title="${file.name}">${file.name}</span>
                            <div class="text-muted small">Ukuran: ${formatFileSize(file.size)}</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-link text-danger p-0 ms-2 remove-file-btn" data-index="${index}"><i class="las la-trash-alt"></i></button>
                `;
                targetList.appendChild(div);
            });

            targetSize.innerHTML = `${formatFileSize(totalSize)} <span class="text-muted fw-normal">/ ${MAX_total_SIZE_MB} MB</span>`;
            if (totalSize > MAX_TOTAL_SIZE_BYTES) targetSize.classList.add('text-danger', 'fw-bold');
            else targetSize.classList.remove('text-danger', 'fw-bold');

            // Bind remove events
            $(targetList).find('.remove-file-btn').on('click', function (e) {
                e.stopPropagation();
                removeFile(parseInt($(this).data('index')));
            });

            if (isEditModal) checkEditFormCompletion();
            else checkFormCompletion();
        }

        function addFiles(newFiles) {
            const isEditModal = $('#modalEdit').hasClass('show');
            let existingSize = 0;
            if (isEditModal) {
                Object.values(existingFilesMap).forEach(size => { existingSize += size; });
            }

            let currentTotalSize = selectedFiles.reduce((acc, f) => acc + f.size, 0) + existingSize;

            Array.from(newFiles).forEach(file => {
                if (!file.name.toLowerCase().endsWith('.pdf')) { tampilkanNotifikasi(`File "${file.name}" bukan PDF.`, 'warning'); return; }
                if (selectedFiles.length >= MAX_FILES) { tampilkanNotifikasi("Maksimal 1 berkas baru.", 'warning'); return; }
                if (currentTotalSize + file.size > MAX_TOTAL_SIZE_BYTES) { tampilkanNotifikasi(`Total ukuran melebihi ${MAX_total_SIZE_MB} MB (termasuk file lama).`, 'warning'); return; }
                selectedFiles.push(file);
                currentTotalSize += file.size;
            });
            updateUI();
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            updateUI();
        }

        function clearFiles() {
            selectedFiles = [];
            existingFilesMap = {};
            $('#pdf').val('');
            $('#edit_pdf').val('');
            updateUI();
        }

        function initDragDrop(dzId, inputId, btnId) {
            const dz = document.getElementById(dzId);
            const input = document.getElementById(inputId);
            const btn = document.getElementById(btnId);
            if (!dz || !input || !btn) return;

            function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dz.addEventListener(eventName, preventDefaults, false);
            });
            ['dragenter', 'dragover'].forEach(eventName => {
                dz.addEventListener(eventName, () => { dz.style.backgroundColor = '#e7f3ff'; dz.style.borderColor = '#0d6efd'; }, false);
            });
            ['dragleave', 'drop'].forEach(eventName => {
                dz.addEventListener(eventName, () => { dz.style.backgroundColor = '#f8f9fa'; dz.style.borderColor = '#dee2e6'; }, false);
            });
            dz.addEventListener('drop', (e) => { addFiles(e.dataTransfer.files); }, false);
            btn.addEventListener('click', (e) => { e.stopPropagation(); input.click(); });
            input.addEventListener('change', function () { addFiles(this.files); this.value = ''; });
        }
        // --- 2. Inisialisasi DataTables ---
        $('.content table.table').DataTable({
            scrollY: 450,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
        });

        function makeModalsDraggable() {
            let activeModal = null;
            let offset = { x: 0, y: 0 };
            document.addEventListener('mousedown', function (e) {
                const header = e.target.closest('.modal-header');
                if (header && header.closest('.modal-dialog')) {
                    activeModal = header.closest('.modal-dialog');
                    const rect = activeModal.getBoundingClientRect();
                    offset.x = e.clientX - rect.left;
                    offset.y = e.clientY - rect.top;
                    document.body.style.userSelect = 'none'; // Mencegah seleksi teks saat drag
                }
            });
            document.addEventListener('mousemove', function (e) {
                if (activeModal) {
                    e.preventDefault();
                    activeModal.style.margin = '0';
                    activeModal.style.top = `${e.clientY - offset.y}px`;
                    activeModal.style.left = `${e.clientX - offset.x}px`;
                }
            });
            document.addEventListener('mouseup', function () {
                activeModal = null;
                document.body.style.userSelect = '';
            });
        }

        // --- 3. Panggilan Awal ---
        makeModalsDraggable();
        initDragDrop('drop-zone', 'pdf', 'choose-file-btn');
        initDragDrop('edit_drop-zone', 'edit_pdf', 'edit_choose-file-btn');

        // --- 4. Event Listeners (UI & Global) ---

        // Listener untuk 'Year Filter'
        $('#year-filter').on('change', function () {
            var thn = $(this).val();
            window.location.href = '?suratkeluar&tahun=' + thn;
        });

        // Reset state saat modal ditutup
        $('#formModal, #modalEdit').on('hidden.bs.modal', function () {
            clearFiles();
            $(this).find('form')[0].reset();
            $(this).find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
        });


        // Tombol Tambah
        $('#tombol-tambah').on('click', function () {
            $('#form-surat')[0].reset();
            $('#form-surat').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
            $('#action').val('simpan');
            $('#id').val('');
            $('#file_lama').val('');
            $('#formModalLabel').html('<i class="las la-plus-circle"></i> Tambah Data Surat');
            checkFormCompletion();
            $('#info_file_lama').addClass('d-none');
            clearFiles();
            $('#formModal').modal('show');
        });

        // Listener Edit
        $('.content table.table').on('click', SELECTOR_EDIT, function () {
            var id = $(this).data('id');
            var thn_row = $(this).data('tahun');
            $.ajax({
                url: 'proses_surat_keluar.php',
                type: 'GET',
                data: { action: 'ambil', id: id, tahun: thn_row || $('#year-filter').val() },
                dataType: 'json',
                success: function (data) {
                    if (data.error) {
                        tampilkanNotifikasi(data.error, 'error');
                        return;
                    }
                    $('#edit_id').val(data.id);
                    $('#edit_tahun').val(thn_row); // Set tahun asal untuk edit
                    $('#edit_no_dokumen').val(data.no_dokumen);
                    $('#edit_jns_dokumen').val(data.jns_dokumen);
                    $('#edit_dari').val(data.dari);
                    $('#edit_unit_tujuan').val(data.unit_tujuan);
                    $('#edit_perihal').val(data.perihal);
                    $('#edit_pembuat').val(data.pembuat);
                    if(document.querySelector("#edit_tgl_dokumen")._flatpickr) {
                        document.querySelector("#edit_tgl_dokumen")._flatpickr.setDate(data.tgl_dokumen_raw);
                    }
                    $('#edit_kategori').val(data.kategori);

                    $('#edit_catatan').val(data.catatan);
                    $('#edit_file_lama').val(data.pdf);

                    existingFilesMap = {};

                    if (data.pdf) {
                        $('#edit_info_file_lama').removeClass('d-none');
                        var files = data.pdf.toString().split('|');
                        var fileLinks = '';
                        files.forEach(function (f) {
                            if (f.trim() !== '') {
                                var path = encodeURI('file/berkas-keluar/' + f);
                                var fileName = f.split('/').pop();
                                fileLinks += `
                                <div class="p-2 bg-light border rounded d-flex align-items-center justify-content-between mb-2 existing-file-item" data-filename="${f}">
                                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                                        <div class="me-3 text-primary bg-white p-2 rounded shadow-sm"><i class="las la-file-alt fa-lg"></i></div>
                                        <div class="overflow-hidden">
                                            <a href="${path}" target="_blank" class="text-decoration-none fw-bold text-primary text-truncate d-block">${fileName}</a>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-link text-danger p-0 ms-2 hapus-file-lama-btn" title="Hapus file"><i class="las la-trash-alt"></i></button>
                                </div>`;
                            }
                        });
                        $('#edit_link_file_lama').html(fileLinks);
                    } else {
                        $('#edit_info_file_lama').addClass('d-none');
                    }

                    updateUI(true);
                    $('#modalEdit').modal('show');

                    mandatoryFields.forEach(fieldId => {
                        const input = $(`#edit_${fieldId}`);
                        if (input.val() && input.val().trim() !== '') input.addClass('is-valid').removeClass('is-invalid');
                        else input.addClass('is-invalid').removeClass('is-valid');
                    });
                }
            });
        });

        // Submit Form Edit
        $('#form-surat-edit').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            var button = $('#edit_tombol-simpan'),
                spinner = button.find('.spinner-border');

            // Gunakan tahun dari hidden input (tahun asal) atau filter sebagai fallback
            if (!formData.has('tahun')) {
                formData.append('tahun', $('#edit_tahun').val() || $('#year-filter').val());
            }
            if (selectedFiles.length > 0) {
                selectedFiles.forEach((f) => {
                    formData.append('pdf[]', f);
                });
            }

            button.prop('disabled', true);
            spinner.removeClass('d-none');

            $.ajax({
                url: 'proses_surat_keluar.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (response) {
                    tampilkanNotifikasi(response.message, response.status);
                    if (response.status === 'success') {
                        $('#modalEdit').modal('hide');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                },
                complete: function () {
                    button.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        });

        // Submit Form Tambah
        $('#form-surat').on('submit', function (e) {
            e.preventDefault();
            var formData = new FormData(this);
            var button = $('#tombol-simpan'),
                spinner = button.find('.spinner-border');
            formData.append('tahun', $('#year-filter').val());
            if (selectedFiles.length > 0) {
                selectedFiles.forEach((f) => {
                    formData.append('pdf[]', f);
                });
            }

            button.prop('disabled', true);
            spinner.removeClass('d-none');

            $.ajax({
                url: 'proses_surat_keluar.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function (response) {
                    tampilkanNotifikasi(response.message, response.status);
                    if (response.status === 'success') {
                        $('#formModal').modal('hide');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                },
                complete: function () {
                    button.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        });

        // Tombol Hapus
        $('.content table.table').on('click', SELECTOR_HAPUS, function () {
            var id = $(this).data('id');
            var nama = $(this).data('nama') || $(this).data('name');
            var thn = $(this).data('tahun');

            $('#detail-hapus').text(nama);
            $('#tombolKonfirmasiHapus').data('id', id);
            $('#tombolKonfirmasiHapus').data('tahun', thn);
            $('#modalHapus').modal('show');
        });

        // Konfirmasi Hapus Data
        $('#tombolKonfirmasiHapus').on('click', function () {
            var id = $(this).data('id');
            var thn_row = $(this).data('tahun');
            var button = $(this);
            var originalButtonText = button.html();

            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Menghapus...');

            $.ajax({
                url: 'proses_surat_keluar.php',
                type: 'POST',
                data: {
                    action: 'hapus',
                    id: id,
                    tahun: thn_row || $('#year-filter').val()
                },
                dataType: 'json',
                success: function (response) {
                    tampilkanNotifikasi(response.message, response.status);
                    if (response.status === 'success') {
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);
                    }
                },
                error: function () {
                    tampilkanNotifikasi('Gagal menghapus data karena kesalahan server.', 'error');
                },
                complete: function () {
                    button.prop('disabled', false).html(originalButtonText);
                    $('#modalHapus').modal('hide');
                }
            });
        });

        // Listener Hapus File Lama (Edit Modal)
        $('#edit_link_file_lama').on('click', '.hapus-file-lama-btn', function (e) {
            if (!confirm('Hapus file lampiran ini?')) return;
            var itemDiv = $(this).closest('.existing-file-item'),
                filename = itemDiv.data('filename');
            itemDiv.remove();
            if ($('#edit_link_file_lama').children().length === 0) $('#edit_info_file_lama').addClass('d-none');
            var current = $('#edit_file_lama').val().split('|').filter(f => f !== filename);
            $('#edit_file_lama').val(current.join('|'));
            delete existingFilesMap[filename];
            updateUI();
        });

        // Listener Hapus File Lama (Add Modal - Jika ada)
        $('#link_file_lama').on('click', '.hapus-file-lama-btn', function (e) {
            if (!confirm('Hapus file lampiran ini?')) return;
            var itemDiv = $(this).closest('.existing-file-item'),
                filename = itemDiv.data('filename');
            itemDiv.remove();
            if ($('#link_file_lama').children().length === 0) $('#info_file_lama').addClass('d-none');
            var currentFilesStr = $('#file_lama').val();
            if (currentFilesStr) {
                var currentFiles = currentFilesStr.split('|');
                var newFiles = currentFiles.filter(f => f !== filename);
                $('#file_lama').val(newFiles.join('|'));
            }
            checkFormCompletion();
        });

        // Tombol View Detail
        $('.content table.table').on('click', SELECTOR_VIEW, function () {
            var id = $(this).data('id'),
                thn_row = $(this).data('tahun'),
                modal = $('#viewModal'),
                iframe = modal.find('#pdf_viewer');

            $.ajax({
                url: 'proses_surat_keluar.php',
                type: 'GET',
                data: { action: 'ambil', id: id, tahun: thn_row || $('#year-filter').val() },
                dataType: 'json',
                success: function (data) {
                    if (data.error) {
                        tampilkanNotifikasi(data.error, 'error');
                        return;
                    }
                    modal.find('#view_no_dokumen').text(data.no_dokumen || '-');
                    modal.find('#view_jns_dokumen').text(data.jns_dokumen || '-');
                    modal.find('#view_dari').text(data.dari || '-');
                    modal.find('#view_unit_tujuan').text(data.unit_tujuan || '-');
                    modal.find('#view_perihal').text(data.perihal || '-');
                    modal.find('#view_pembuat').text(data.pembuat || '-');
                    modal.find('#view_tgl_dokumen').text(data.tgl_dokumen || '-');
                    modal.find('#view_kategori').text(data.kategori || '-');
                    modal.find('#view_catatan').text(data.catatan || '-');

                    var file = data.pdf;
                    if (file) {
                        var fullUrl = encodeURI('file/berkas-keluar/' + file);
                        iframe.attr('src', fullUrl);
                        modal.find('#view_pdf').html(`<a href="${fullUrl}" target="_blank" class="btn btn-xs btn-primary"><i class="las la-download"></i> ${file}</a>`);
                    } else {
                        iframe.attr('src', '');
                        modal.find('#view_pdf').text('Tidak ada lampiran');
                    }
                    modal.modal('show');
                }
            });
        });

        // Reset iframe saat modal ditutup
        $('#viewModal').on('hidden.bs.modal', function () {
            $(this).find('#pdf_viewer').attr('src', '');
        });

    });
</script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
<script>
    $(function () {
        $(".datepicker").flatpickr({
            altInput: true,
            altFormat: "d-m-Y",
            dateFormat: "Y-m-d",
            locale: "id"
        });
    });
</script>