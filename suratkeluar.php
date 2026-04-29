<?php
// File ini adalah halaman antarmuka pengguna (UI) untuk manajemen data surat keluar.
// Menggunakan Bootstrap 5, jQuery, dan AJAX untuk operasi CRUD yang dinamis.

// Pastikan conn.php sudah di-include dari file induk
// atau include di sini jika file ini berdiri sendiri.
// include_once 'conn.php';

// --- Konstruksi Subfolder Dinamis untuk PDF ---
$sysTapel = $tahunsklh;
$sysSmt = '1';

// Normalisasi Semester
if (strtolower($sysSmt) === 'ganjil') $sysSmt = '1';
if (strtolower($sysSmt) === 'genap') $sysSmt = '2';

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
    $sql_dbs = mysqli_query($conn, "SHOW DATABASES LIKE 'sas_%'");
    if ($sql_dbs) {
        while ($row = mysqli_fetch_array($sql_dbs)) {
            $dbname = $row[0];
            if (preg_match('/^sas_(\d+)$/', $dbname, $matches)) {
                $thn = $matches[1];
                $activeYear = isset($tahunsklh) ? $tahunsklh : (isset($_SESSION['tahundb']) ? $_SESSION['tahundb'] : '');
                $selected = ($activeYear == $thn) ? 'selected' : '';
                $tahun_options .= "<option value=\"$thn\" $selected>$thn</option>";
            }
        }
    } else {
         $tahun_options .= "<!-- Error showing databases: " . mysqli_error($conn) . " -->";
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

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- [TAMBAHAN] Loading Overlay -->
    

    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <h1>SURAT KELUAR</h1>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline primary sm">
                        <!-- [PERBAIKAN] Merapikan header kontrol -->
                        <div class="card-header bg-menu-gradient">
                            <div class="d-flex justify-content-between align-items-lg flex-wrap gap-2">
                                <!-- Tombol Tambah di Kiri -->
                                <div>
    <?php if($lv=="1"||$lv=="2") { ?>
<button class="btn btn-primary btn-sm" id="tombol-tambah">
                                        <i class="fas fa-plus"></i> Tambah
                                    </button>
<?php } ?>
                                </div>
                                
                                <!-- Kontrol Filter/Pencarian di Kanan -->
                                  <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <label for="year-filter" class="form-label mb-0 text-nowrap">Tahun:</label>
                                    <select class="form-select form-select-sm" id="year-filter" style="width: auto;">
                                        <?php echo $tahun_options; ?>
                                    </select>

                                    <label for="records-per-page" class="form-label mb-0 text-nowrap">Data:</label>
                                    <select class="form-select form-select-sm" id="records-per-page" aria-label="Jumlah data per halaman" style="width: auto;">
                                        <?php echo $records_per_page_options; ?>
                                        <option value="5">5 per page</option>
                                        <option value="10">10 per page</option>
                                        <option value="25">25 per page</option>
                                        <option value="50">50 per page</option>
                                        <option value="100">100 per page</option>
                                        <option value="150">150 per page</option>
                                        <option value="200">200 per page</option>
                                    </select>
                                    <div class="input-group" style="width: auto; max-width: 250px;">
                                              <div class="input-group-prepend">
                                                <div class="input-group-text"><i class="fa fa-search" style="font-size: 15px; color:blue;"></i></div>
                                              </div>
                                      <input type="text" class="form-control form-control-sm" placeholder="Cari data surat..." id="search-box">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.card-header -->

                        <div class="card-body">
                            <div class="table-responsive" id="data-keluar">
                                <!-- Data tabel dimuat di sini oleh AJAX -->
                            </div>
                        </div>
                        <!-- /.card-body -->
                        
                        <div class="card-footer d-flex justify-content-between align-items-lg flex-wrap">
                            <div id="records-info-container" class="text-muted small mb-2 mb-md-0">
                                <!-- Info jumlah data dimuat di sini -->
                            </div>
                            <div id="pagination-container">
                                <!-- Navigasi paginasi dimuat di sini -->
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
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-menu-gradient">
                    <b class="modal-title" id="formModalLabel"><i class="fas fa-plus"></i> Form Data Surat</b>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
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
                                    <label for="no_dokumen" class="form-label"><i class="fas fa-file-alt"></i> No Surat <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="no_dokumen" name="no_dokumen" required>
                                </div>

                                <div class="mb-3">
                                    <label for="jns_dokumen" class="form-label"><i class="fas fa-tag"></i> Jenis Dokumen <span class="text-danger">*</span></label>
                                    <input class="form-control" list="list_jns_dokumen_keluar" id="jns_dokumen" name="jns_dokumen" required>
                                    <datalist id="list_jns_dokumen_keluar">
                                        <?php echo $jenis_options; ?>
                                    </datalist>
                                </div>

                                <div class="mb-3">
                                    <label for="dari" class="form-label"><i class="fas fa-user-circle"></i> Dari (Internal) <span class="text-danger">*</span></label>
                                    <input class="form-control" list="list_dari_keluar" id="dari" name="dari" required>
                                    <datalist id="list_dari_keluar">
                                        <?php echo $dari_options; ?>
                                    </datalist>
                                </div>

                                <div class="mb-3">
                                    <label for="unit_tujuan" class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="fas fa-building"></i> Ditujukan Ke <span class="text-danger">*</span></label>
                                    <input class="form-control" list="list_tujuan_keluar" id="unit_tujuan" name="unit_tujuan" required>
                                    <datalist id="list_tujuan_keluar">
                                        <?php echo $unit_options; ?>
                                    </datalist>
                                </div>

                                <div class="mb-3">
                                    <label for="tgl_dokumen" class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="fas fa-calendar"></i> Tgl Surat <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="tgl_dokumen" name="tgl_dokumen" required>
                                </div>
                            </div>
                            <!-- /.col-md-6 -->

                            <!-- Kolom Kanan: Klasifikasi, Catatan & Lampiran -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="pembuat" class="form-label"><i class="fas fa-user-edit"></i> Pembuat Dokumen</label>
                                    <input class="form-control" list="list_dari_keluar" id="pembuat" name="pembuat">
                                </div>

                                <div class="mb-3">
                                    <label for="perihal" class="form-label"><i class="fas fa-align-left"></i> Perihal <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="perihal" name="perihal" rows="2" required></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="kategori" class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="fas fa-folder-open"></i> Kategori</label>
                                        <input class="form-control form-control-sm" list="list_kategori_keluar" id="kategori" name="kategori">
                                        <datalist id="list_kategori_keluar">
                                            <?php echo $kategori_options; ?>
                                        </datalist>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="catatan" class="form-label small fw-bold text-muted text-uppercase mb-1"><i class="fas fa-sticky-note"></i> Catatan</label>
                                        <textarea class="form-control form-control-sm" id="catatan" name="catatan" rows="1"></textarea>
                                    </div>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-bold"><i class="fas fa-paperclip"></i> Lampiran File (.pdf)</label>
                                    <div id="drop-zone" class="border rounded p-2 bg-white" style="border-style: dashed !important; border-width: 2px !important;">
                                        <input class="d-none" type="file" id="pdf" name="pdf" accept=".pdf" multiple>
                                        
                                        <!-- Header: Button & Drag Text -->
                                        <div class="d-flex align-items-center mb-2 px-1">
                                            <button type="button" class="btn btn-sm btn-light border-0 px-2 py-1 me-2 shadow-sm" id="choose-file-btn" style="background-color: #f3f4f6; color: #4338ca; font-weight: 600; font-size: 0.8rem;">
                                                <i class="fas fa-folder-open me-1"></i> Pilih...
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
                                                <i class="fas fa-chart-pie me-1"></i> Total Berkas:
                                            </div>
                                            <div class="small">
                                                <span id="file-size" class="text-primary fw-bold" style="font-size: 0.9rem;">0 B</span>
                                                <span class="text-muted ms-1" style="font-size: 0.75rem;">(Maks 1MB)</span>
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
                        <button type="button" class="btn btn-secondary custom" data-bs-dismiss="modal"><i class="fas fa-times"></i> Tutup</button>
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


<!-- 2. Modal View Detail -->
<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <b class="modal-title" id="modalHapusLabel"> Lihat Dokumen </b>
<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
                              </div>
            <div class="modal-body">
                <div id="view_pdf_container" class="mb-3">
                    <iframe id="pdf_viewer" style="width: 100%; height: 60vh; border: 1px solid #dee2e6; border-radius: .25rem;" src=""></iframe>
                </div>
                <table class="table table-bordered table-striped">
                    <tbody>
                        <tr><th style="width: 30%;">No Surat</th><td id="view_no_dokumen"></td></tr>
                        <tr><th>Jenis Dokumen</th><td id="view_jns_dokumen"></td></tr>
                        <tr><th>Dari</th><td id="view_dari"></td></tr>
                        <tr><th>Unit Tujuan</th><td id="view_unit_tujuan"></td></tr>
                        <tr><th>Perihal</th><td id="view_perihal"></td></tr>
                        <tr><th>Pembuat</th><td id="view_pembuat"></td></tr>
                        <tr><th>Tanggal</th><td id="view_tgl_dokumen"></td></tr>
                        <tr><th>Kategori</th><td id="view_kategori"></td></tr>
                        <tr><th>Catatan</th><td id="view_catatan"></td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<!-- AKHIR MODAL VIEW -->


<!-- =================================================================== -->
<!-- |                      MODAL KONFIRMASI HAPUS                     | -->
<!-- =================================================================== -->
<div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel">
    <div class="modal-dialog modal-dialog-lged">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalHapusLabel"> Notifikasi</h5>
<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
                      </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data surat:
                <br>
                <strong><span id="detail-hapus"></span></strong>?
                <br><br>
                Tindakan ini tidak dapat diTutupkan.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-danger custom" id="tombolKonfirmasiHapus">Delete</button>
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
<button type="button" class="close" data-bs-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
            </div>
            <div class="modal-body" id="notifikasi-body">
                <!-- Pesan notifikasi akan diisi di sini oleh JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary custom" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function() {

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
                const value = input.val() ? input.val().trim() : '';
                
                if (value === '') {
                    isComplete = false;
                }
            });

            // [VALIDASI] Cek keberadaan file
            const fileLama = $('#file_lama').val();
            const hasNewFile = selectedFiles.length > 0;
            const hasExistingFile = fileLama && fileLama.trim() !== '';
            
            if (!hasNewFile && !hasExistingFile) {
                isComplete = false;
            }

            const btn = $('#tombol-simpan');
            if (isComplete) {
                btn.prop('disabled', false)
                   .removeClass('btn-secondary')
                   .addClass('btn-primary')
                   .html('<i class="fas fa-save"></i> Simpan');
            } else {
                btn.prop('disabled', true)
                   .removeClass('btn-primary')
                   .addClass('btn-secondary')
                   .html('<i class="fas fa-exclamation-circle"></i> Lengkapi Data');
            }
            
            return isComplete;
        }

        // Jalankan pengecekan setiap kali ada input pada field mandatory
        mandatoryFields.forEach(fieldId => {
            $(`#${fieldId}`).on('input change', function() {
                checkFormCompletion();
                
                // Feedback visual real-time
                if ($(this).val() && $(this).val().trim() !== '') {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                } else {
                    $(this).removeClass('is-valid').addClass('is-invalid');
                }
            });
        });
        
        // [PERUBAHAN] Mengganti fungsi placeholder dengan modal notifikasi kustom
        // Buat instance modal Bootstrap sekali saja di luar fungsi
        var modalNotifikasi = new bootstrap.Modal(document.getElementById('modalNotifikasi'));

        /**
         * Menampilkan notifikasi kustom menggunakan modal Bootstrap.
         * @param {string} message - Pesan yang akan ditampilkan.
         * @param {string} status - Tipe notifikasi ('success', 'error', 'warning', 'info').
         */
        function tampilkanNotifikasi(message, status) {
            var header = $('#notifikasi-header');
            var icon = $('#notifikasi-icon');
            var title = $('#notifikasi-title');
            var body = $('#notifikasi-body');

            // Hapus kelas warna dan ikon sebelumnya
            header.removeClass('bg-success bg-danger bg-warning bg-info text-white text-dark');
            icon.removeClass('fa-check-circle fa-times-circle fa-exclamation-triangle fa-info-circle');

            // Atur tampilan modal berdasarkan status
            switch (status) {
                case 'success':
                    header.addClass('bg-success text-white');
                    icon.addClass('fas fa-check-circle');
                    title.text('Notifikasi');
                    break;
                case 'error':
                    header.addClass('bg-danger text-white');
                    icon.addClass('fas fa-times-circle');
                    title.text('Notifikasi');
                    break;
                case 'warning':
                    header.addClass('bg-warning text-dark'); // Teks gelap agar terbaca di background kuning
                    icon.addClass('fas fa-exclamation-triangle');
                    title.text('Notifikasi');
                    break;
                default: // 'info' atau status tidak dikenal
                    header.addClass('bg-info text-white');
                    icon.addClass('fas fa-info-circle');
                    title.text('Notifikasi');
                    break;
            }

            // Set pesan
            body.html(message); // Gunakan .html() agar bisa merender tag seperti <br>

            // Tampilkan modal
            modalNotifikasi.show();
        }
        

        // --- 2. Fungsi Utama: muatData() ---
        // Fungsi ini adalah inti dari aplikasi, memuat data via AJAX
        function muatData(page) {
            currentPage = page; // Simpan halaman saat ini
            var records = $('#records-per-page').val();
            var search = $('#search-box').val();
            var tahun = $('#year-filter').val();

            // Tampilkan loading overlay
            $('#loading-overlay').removeClass('d-none');

            $.ajax({
                url: 'proses_surat_keluar.php',
                type: 'GET',
                data: {
                    action: 'muatData',
                    page: page,
                    limit: records,
                    search: search,
                    tahun: tahun
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        $('#data-keluar').html(response.data.table);
                        $('#pagination-container').html(response.data.pagination);
                        $('#records-info-container').html(response.data.recordsInfo);
                    } else {
                        // Tampilkan error jika server merespon dengan 'error'
                        tampilkanNotifikasi(response.message || 'Gagal memuat data.', 'error');
                        $('#data-keluar').html('<div class="alert alert-danger">Gagal memuat data.</div>');
                        $('#pagination-container').html('');
                        $('#records-info-container').html('');
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    // Tampilkan error jika AJAX request gagal total
                    console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    tampilkanNotifikasi('Terjadi kesalahan server saat memuat data.', 'error');
                    $('#data-keluar').html('<div class="alert alert-danger">Error: ' + errorThrown + '</div>');
                },
                complete: function() {
                    // Sembunyikan loading overlay
                    $('#loading-overlay').addClass('d-none');
                }
            });
        }

    function makeModalsDraggable() {
        let activeModal = null;
        let offset = { x: 0, y: 0 };
        document.addEventListener('mousedown', function(e) {
            const header = e.target.closest('.modal-header');
            if (header && header.closest('.modal-dialog')) {
                activeModal = header.closest('.modal-dialog');
                const rect = activeModal.getBoundingClientRect();
                offset.x = e.clientX - rect.left;
                offset.y = e.clientY - rect.top;
                document.body.style.userSelect = 'none'; // Mencegah seleksi teks saat drag
            }
        });
        document.addEventListener('mousemove', function(e) {
            if (activeModal) {
                e.preventDefault();
                activeModal.style.margin = '0';
                activeModal.style.top = `${e.clientY - offset.y}px`;
                activeModal.style.left = `${e.clientX - offset.x}px`;
            }
        });
        document.addEventListener('mouseup', function() {
            activeModal = null;
            document.body.style.userSelect = '';
        });
    }

        // --- 3. Panggilan Awal ---
        // Panggil muatData saat halaman pertama kali dimuat
        muatData(1);
    makeModalsDraggable();
        // --- 4. Event Listeners (Kontrol Data) ---

        // Listener untuk 'Year Filter'
        $('#year-filter').on('change', function() {
            muatData(1); // Reset ke halaman 1
        });

        // Listener untuk 'Records per Page'
        $('#records-per-page').on('change', function() {
            muatData(1); // Selalu reset ke halaman 1
        });

        // Listener untuk 'Search' (dengan debounce)
        $('#search-box').on('keyup', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function() {
                muatData(1); // Selalu reset ke halaman 1
            }, 300); // Tunggu 300ms setelah user berhenti mengetik
        });

        // Listener untuk Paginasi (Menggunakan Event Delegation)
        $('#pagination-container').on('click', '.page-link', function(e) {
            e.preventDefault(); // Mencegah link # refresh halaman
            var page = $(this).data('page');

            // Cek jika link valid dan bukan 'disabled'
            if (page > 0 && !$(this).parent().hasClass('disabled')) {
                muatData(page);
            }
        });



        // --- 4. Drag and Drop File Upload (Multi-File Support) ---
        const dropZone = document.getElementById('drop-zone');
        const fileInput = document.getElementById('pdf');
        const chooseFileBtn = document.getElementById('choose-file-btn');
        const fileList = document.getElementById('file-list');
        const fileSizeDisplay = document.getElementById('file-size');

        let selectedFiles = [];
        const MAX_FILES = 1;
        const MAX_total_SIZE_MB = 1;
        const MAX_TOTAL_SIZE_BYTES = MAX_total_SIZE_MB * 1024 * 1024;

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 B';
            const k = 1024;
            const sizes = ['B', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function updateUI() {
            fileList.innerHTML = '';
            let totalSize = 0;

            selectedFiles.forEach((file, index) => {
                totalSize += file.size;
                const div = document.createElement('div');
                div.className = 'p-2 bg-light border rounded d-flex align-items-center justify-content-between';
                div.innerHTML = `
                    <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                        <div class="me-3 text-primary bg-white p-2 rounded shadow-sm">
                             <i class="fas fa-file-alt fa-lg"></i>
                        </div>
                        <div class="overflow-hidden">
                             <a href="#" class="text-decoration-none fw-bold text-primary text-truncate d-block" title="${file.name}">${file.name}</a>
                             <div class="text-muted small">Ukuran Berkas: ${formatFileSize(file.size)}</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-link text-danger p-0 ms-2 remove-file-btn" data-index="${index}" title="Hapus file">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                `;
                fileList.appendChild(div);
            });

            fileSizeDisplay.innerHTML = `${formatFileSize(totalSize)} <span class="text-muted fw-normal">/ ${MAX_total_SIZE_MB} MB</span>`;
            
            if (totalSize > MAX_TOTAL_SIZE_BYTES) {
                 fileSizeDisplay.classList.add('text-danger', 'fw-bold');
            } else {
                 fileSizeDisplay.classList.remove('text-danger', 'fw-bold');
            }

            // [VALIDASI] Perbarui status tombol simpan
            checkFormCompletion();

            document.querySelectorAll('.remove-file-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const index = parseInt(this.getAttribute('data-index'));
                    removeFile(index);
                });
            });
        }

        function addFiles(newFiles) {
            let currentTotalSize = selectedFiles.reduce((acc, f) => acc + f.size, 0);
            let addedCount = 0;

            Array.from(newFiles).forEach(file => {
                if (file.type !== 'application/pdf' && !file.name.toLowerCase().endsWith('.pdf')) {
                    tampilkanNotifikasi(`File "${file.name}" bukan PDF.`, 'warning');
                    return;
                }
                if (selectedFiles.length + addedCount >= MAX_FILES) {
                    tampilkanNotifikasi("Maksimal 1 berkas.", 'warning');
                    return;
                }
                if (currentTotalSize + file.size > MAX_TOTAL_SIZE_BYTES) {
                    tampilkanNotifikasi(`Total ukuran melebihi ${MAX_total_SIZE_MB} MB.`, 'warning');
                    return;
                }

                selectedFiles.push(file);
                currentTotalSize += file.size;
                addedCount++;
            });

            if (addedCount > 0) updateUI();
        }

        function removeFile(index) {
            selectedFiles.splice(index, 1);
            updateUI();
        }

        function clearFiles() {
            selectedFiles = [];
            fileInput.value = '';
            updateUI();
        }

        if (dropZone && fileInput) {
             function preventDefaults(e) { e.preventDefault(); e.stopPropagation(); }
             
             ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, preventDefaults, false);
                document.body.addEventListener(eventName, preventDefaults, false);
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.style.backgroundColor = '#e7f3ff';
                    dropZone.style.borderColor = '#0d6efd';
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropZone.addEventListener(eventName, () => {
                    dropZone.style.backgroundColor = '#f8f9fa';
                    dropZone.style.borderColor = '#dee2e6';
                }, false);
            });

            dropZone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                addFiles(dt.files);
            }, false);

            chooseFileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                fileInput.click();
            });

            fileInput.addEventListener('change', function() {
                addFiles(this.files);
                this.value = ''; 
            });

             $('#formModal').on('hidden.bs.modal', function() {
                clearFiles();
            });
        }



        // --- 5. Event Listeners (CRUD) ---

        // Tombol Tambah: Buka modal dan reset form
        $('#tombol-tambah').on('click', function() {
            $('#form-surat')[0].reset(); // Reset form
            $('#action').val('simpan'); // Set aksi ke 'simpan' (tambah baru)
            $('#id').val(''); // Kosongkan ID
            $('#file_lama').val(''); // Kosongkan file lama
            $('#formModalLabel').html('<i class="fas fa-plus"></i> Tambah Data Surat');
            
            // Reset visual feedback
            $('#form-surat').find('.is-valid, .is-invalid').removeClass('is-valid is-invalid');
            
            $('#info_file_lama').addClass('d-none'); // Sembunyikan info file lama
            $('#pdf').prop('required', false); // File tidak wajib saat tambah baru (sesuaikan kebutuhan)
            clearFiles(); // Reset upload zone display
            
            // [VALIDASI] Inisialisasi status tombol
            checkFormCompletion();

            $('#formModal').modal('show');
        });

        // Listener untuk Tombol Edit (Event Delegation)
        $('#data-keluar').on('click', '.tombol-edit', function() {
            var id = $(this).data('id');
            var tahun = $('#year-filter').val();

            // Ambil data untuk di-edit
            $.ajax({
                url: 'proses_surat_keluar.php',
                type: 'GET',
                data: {
                    action: 'ambil',
                    id: id,
                    tahun: tahun
                },
                dataType: 'json',
                success: function(data) {
                    if (data.status === 'success') {
                        // Isi form dengan data
                        $('#action').val('edit');
                        $('#id').val(data.data.id);
                        $('#no_dokumen').val(data.data.no_dokumen);
                        $('#jns_dokumen').val(data.data.jns_dokumen);
                        $('#dari').val(data.data.dari);
                        $('#unit_tujuan').val(data.data.unit_tujuan);
                        $('#pembuat').val(data.data.pembuat);
                        $('#perihal').val(data.data.perihal);
                        // Format tanggal untuk input type="date" (YYYY-MM-DD)
                        $('#tgl_dokumen').val(data.data.tgl_dokumen_raw);
                        $('#kategori').val(data.data.kategori);
                        $('#catatan').val(data.data.catatan);
                        $('#file_lama').val(data.data.pdf);
 
                        // Tampilkan info file lama jika ada

                        // Tampilkan info file lama jika ada
                        if (data.data.pdf) {
                            $('#info_file_lama').removeClass('d-none');
                            var files = data.data.pdf.split('|');
                            var fileLinks = '';
                            
                            files.forEach(function(f, index) {
                                if(f.trim() !== '') {
                                    var pdfPath = encodeURI('file/berkas-keluar/' + f);
                                    var fileName = f.split('/').pop();
                                    fileLinks += `
                                    <div class="p-2 bg-light border rounded d-flex align-items-center justify-content-between mb-2 existing-file-item" data-filename="${f}">
                                        <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                                            <div class="me-3 text-primary bg-white p-2 rounded shadow-sm">
                                                <i class="fas fa-file-alt fa-lg"></i>
                                            </div>
                                            <div class="overflow-hidden">
                                                <a href="${pdfPath}" target="_blank" class="text-decoration-none fw-bold text-primary text-truncate d-block" title="${fileName}">${fileName}</a>
                                                <div class="text-muted small">File Tersimpan</div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-link text-danger p-0 ms-2 hapus-file-lama-btn" title="Hapus file">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>`;
                                }
                            });
                            
                            $('#link_file_lama').html(fileLinks);
                            $('#link_file_lama').removeAttr('href').removeAttr('target'); 

                        } else {
                            $('#info_file_lama').addClass('d-none');
                        }

                        // Saat edit, file tidak wajib di-upload ulang
                        $('#pdf').prop('required', false);
                        
                        // Clear selected files array
                        selectedFiles = [];


                        // Reset upload zone display
                        clearFiles();

                        // [VALIDASI] Cek kelengkapan data saat edit (untuk inisialisasi tombol)
                        checkFormCompletion();

                        // Tampilkan modal
                        $('#formModalLabel').html('<i class="fas fa-edit"></i> Edit Data Surat');
                        $('#formModal').modal('show');
                    } else {
                        tampilkanNotifikasi(data.message || 'Gagal mengambil data.', 'error');
                    }
                },
                error: function() {
                    tampilkanNotifikasi('Gagal mengambil data dari server.', 'error');
                }
            });
        });


        // Proses Simpan / Edit (Form Submit)
        $('#form-surat').on('submit', function(e) {
            e.preventDefault(); // Mencegah submit form standar

            var formData = new FormData(this);
            var button = $('#tombol-simpan');
            var spinner = button.find('.spinner-border');

            // --- APPEND SELECTED FILES MANUALLY ---
            formData.delete('pdf'); 
            formData.delete('pdf[]');
            
            if (selectedFiles.length > 0) {
                selectedFiles.forEach((file) => {
                    formData.append('pdf[]', file);
                });
            }

            // Tampilkan loading di tombol
            button.prop('disabled', true);
            spinner.removeClass('d-none');

            $.ajax({
                url: 'proses_surat_keluar.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                contentType: false, // Wajib false untuk FormData
                processData: false, // Wajib false untuk FormData
                success: function(response) {
                    tampilkanNotifikasi(response.message, response.status);

                    if (response.status === 'success') {
                        $('#formModal').modal('hide');
                        muatData(currentPage); // Muat ulang data di halaman saat ini
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Form Submit Error:", textStatus, errorThrown, jqXHR.responseText);
                    tampilkanNotifikasi('Terjadi kesalahan server. Periksa console.', 'error');
                },
                complete: function() {
                    // Hilangkan loading di tombol
                    button.prop('disabled', false);
                    spinner.addClass('d-none');
                }
            });
        });

        // Listener untuk Tombol Hapus (Event Delegation)
        $('#data-keluar').on('click', '.tombol-hapus', function() {
            var id = $(this).data('id');
            var nama = $(this).data('nama');

            // Isi detail ke modal
            $('#detail-hapus').text(nama);
            // Simpan ID di tombol konfirmasi hapus
            $('#tombolKonfirmasiHapus').data('id', id);

            $('#modalHapus').modal('show');
        });

        // 6. Listener untuk Tombol Hapus File Lama (Existing Files)
        $('#link_file_lama').on('click', '.hapus-file-lama-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();

            if(!confirm('Apakah Anda yakin ingin menghapus file lampiran ini? (Perubahan akan disimpan setelah Anda mengklik tombol Simpan)')) return;

            var itemDiv = $(this).closest('.existing-file-item');
            var filenameToRemove = itemDiv.data('filename');
            
            itemDiv.remove();

            if ($('#link_file_lama').children().length === 0) {
                 $('#info_file_lama').addClass('d-none');
            }

            var currentFilesStr = $('#file_lama').val();
            if (currentFilesStr) {
                var currentFiles = currentFilesStr.split('|');
                var newFiles = currentFiles.filter(function(f) {
                    return f !== filenameToRemove;
                });
                $('#file_lama').val(newFiles.join('|'));
            }

            // [VALIDASI] Perbarui status tombol simpan
            checkFormCompletion();
        });

        // Listener untuk tombol Hapus Data (CRUD)
        $('#tombolKonfirmasiHapus').on('click', function() {
            var id = $(this).data('id');
            var button = $(this);
            var originalButtonText = button.html();

            // Tampilkan spinner
            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status"></span> Menghapus...');

            $.ajax({
                url: 'proses_surat_keluar.php',
                type: 'POST',
                data: {
                    action: 'hapus',
                    id: id,
                    tahun: $('#year-filter').val()
                },
                dataType: 'json',
                success: function(response) {
                    tampilkanNotifikasi(response.message, response.status);
                    if (response.status === 'success') {
                        // Jika halaman saat ini jadi kosong setelah hapus, pindah ke halaman sebelumnya
                        // Cek jumlah baris data yang tersisa
                        var rowCount = $('#data-keluar tbody tr').length;
                        if (rowCount === 1 && currentPage > 1) {
                            currentPage = currentPage - 1;
                        }
                        muatData(currentPage); // Muat ulang data
                    }
                },
                error: function() {
                    tampilkanNotifikasi('Gagal menghapus data karena kesalahan server.', 'error');
                },
                complete: function() {
                    // Kembalikan tombol ke keadaan semula
                    button.prop('disabled', false).html(originalButtonText);
                    $('#modalHapus').modal('hide'); // Tutup modal
                }
            });
        });

        // Listener untuk Tombol View (Event Delegation)
        // [PERUBAHAN TOTAL] Logika diubah untuk mengambil data via AJAX
        $('#data-keluar').on('click', '.tombol-view', function() {
            var id = $(this).data('id');
            var modal = $('#viewModal');
            var iframe = modal.find('#pdf_viewer');
            var tahun = $('#year-filter').val();

            // Cek apakah data-id ada
            // PENTING: Pastikan tombol view di proses_surat_keluar.php memiliki data-id
            if (!id) {
                tampilkanNotifikasi('Error: Tombol view tidak memiliki data-id.', 'error');
                return;
            }

            // 1. Reset Modal ke status "Memuat..."
            modal.find('#view_no_dokumen').text('Memuat...');
            modal.find('#view_jns_dokumen').text('Memuat...');
            modal.find('#view_dari').text('Memuat...');
            modal.find('#view_unit_tujuan').text('Memuat...');
            modal.find('#view_perihal').text('Memuat...');
            modal.find('#view_pembuat').text('Memuat...');
            modal.find('#view_tgl_dokumen').text('Memuat...');
            modal.find('#view_kategori').text('Memuat...');
            modal.find('#view_catatan').text('Memuat...');
            iframe.attr('src', ''); // Kosongkan iframe

            // Tampilkan modal
            modal.modal('show');

            // 2. Ambil data lengkap via AJAX (sama seperti tombol edit)
            $.ajax({
                url: 'proses_surat_keluar.php',
                type: 'GET',
                data: {
                    action: 'ambil',
                    id: id,
                    tahun: tahun
                },
                dataType: 'json',
                success: function(data) {
                    if (data.status === 'success') {
                        // 3. Isi data ke tabel modal
                        modal.find('#view_no_dokumen').text(data.data.no_dokumen || '-');
                        modal.find('#view_jns_dokumen').text(data.data.jns_dokumen || '-');
                        modal.find('#view_dari').text(data.data.dari || '-');
                        modal.find('#view_unit_tujuan').text(data.data.unit_tujuan || '-');
                        modal.find('#view_perihal').text(data.data.perihal || '-');
                        modal.find('#view_pembuat').text(data.data.pembuat || '-');
                        // Gunakan tgl_dokumen_raw (YYYY-MM-DD) atau format lain jika ada
                        modal.find('#view_tgl_dokumen').text(data.data.tgl_dokumen_raw || '-');
                        modal.find('#view_kategori').text(data.data.kategori || '-');
                        modal.find('#view_catatan').text(data.data.catatan || '-');

                        // 4. Handle file (lampiran dan iframe)
                        if (data.data.pdf) {
                            var fullUrl = encodeURI('file/berkas-keluar/' + data.data.pdf);
                            // Set iframe
                            iframe.attr('src', fullUrl);
                            // Set link di tabel lampiran
                            modal.find('#view_pdf').html('<a href="' + fullUrl + '" target="_blank" rel="noopener noreferrer">' + data.data.pdf + '</a>');
                        } else {
                            iframe.attr('src', '');
                            modal.find('#view_pdf').text('Tidak ada file');
                        }

                    } else {
                        tampilkanNotifikasi(data.message || 'Gagal mengambil data.', 'error');
                        modal.find('#view_no_dokumen').text('Gagal memuat data.');
                    }
                },
                error: function() {
                    tampilkanNotifikasi('Gagal mengambil data dari server.', 'error');
                    modal.find('#view_no_dokumen').text('Gagal memuat data. Kesalahan server.');
                }
            });
        });

        // Membersihkan iframe saat modal view ditutup (menghentikan pemutaran/pemuatan)
        $('#viewModal').on('hidden.bs.modal', function() {
            // [PERBAIKAN] Sesuaikan ID iframe
            $(this).find('#pdf_viewer').attr('src', '');
        });

    });
</script>
