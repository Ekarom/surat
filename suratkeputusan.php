<?php
// File ini adalah halaman antarmuka pengguna (UI) untuk manajemen data surat keputusan.
// Menggunakan Bootstrap 5, jQuery, dan AJAX untuk operasi CRUD yang dinamis.

include_once 'dbconn.php';

// --- Inisialisasi Variabel Global (Safety) ---
if (!isset($lv)) $lv = $_SESSION['level'] ?? '3';
if (!isset($tahunsklh)) $tahunsklh = $_SESSION['tahundb'] ?? '2025';
if (!isset($dataRows)) $dataRows = []; 

// --- Tentukan Tahun Aktif ---
if (isset($_GET['tahun'])) {
    $tahun_aktif = $_GET['tahun'];
} else {
    $tahun_aktif = $tahunsklh ?? ($_SESSION['tahundb'] ?? '2025');
}

// [PERBAIKAN] Normalisasi tahun_aktif (Pastikan format 4 digit angka)
if ($tahun_aktif !== '' && preg_match('/(\d{4})/', $tahun_aktif, $matches)) {
    $tahun_aktif = $matches[1];
} elseif ($tahun_aktif !== '') {
    $tahun_aktif = '2025';
}

// --- Ambil data untuk datalist di awal ---
$dari_options = '';

// Memastikan variabel $conn valid sebelum digunakan.
if (isset($conn) && $conn instanceof mysqli && !$conn->connect_error) {

    // 1. Ambil data penanggung jawab (pegawai)
    $sql_dari = mysqli_query($conn, "SELECT nm_pegawai FROM tbl_pegawai ORDER BY id ASC") or die(mysqli_error($conn));
    while ($datanama = mysqli_fetch_array($sql_dari)) {
        $nama_pegawai = htmlspecialchars($datanama['nm_pegawai']);
        $dari_options .= "<option value=\"$nama_pegawai\">";
    }

    // 2. Ambil data tahun dari database (SHOW DATABASES LIKE 'sas_%')
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
    
    if (!in_array('2025', $years_list)) $years_list[] = '2025';
    if (!in_array('2026', $years_list)) $years_list[] = '2026';
    
    rsort($years_list);
    $years_list = array_unique($years_list);
    
    foreach ($years_list as $thn) {
        $selected = ($tahun_aktif == $thn) ? 'selected' : '';
        $tahun_options .= "<option value=\"$thn\" $selected>$thn</option>";
    }

    // 3. Ambil Data Surat Keputusan
    $dataRows = [];
    $fetch_years = ($tahun_aktif !== '') ? [$tahun_aktif] : $years_list;

    foreach ($fetch_years as $thn) {
        try {
            if (@mysqli_select_db($conn, "sas_" . $thn)) {
                $sql_data = mysqli_query($conn, "SELECT * FROM dokumenkeputusan ORDER BY id ASC");
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
                echo "<div class='alert alert-warning m-3'>Gagal memuat data tahun $thn: " . $e->getMessage() . "</div>";
            }
        }
    }
} else {
    $error_msg = 'Koneksi database gagal atau tidak terdefinisi.';
    if (isset($conn) && $conn->connect_error) {
        $error_msg .= ' Pesan error: ' . $conn->connect_error;
    }
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
                    <h1>Surat Keputusan</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Surat Keputusan</li>
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
                      <div class="card card-outline">
                          <!-- [PERBAIKAN] Merapikan header kontrol -->
                          <div class="card-header">
                              <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                  <!-- Tombol Tambah di Kiri -->
                                  <div>
<?php if($lv=="1"|| $lv=="2") { ?>
                                      <button class="btn btn-outline-info btn-sm" id="tombol-tambah">
                                          Tambah Surat
                                      </button>
<?php } ?>
                                  </div>
                                  
                                  <!-- Kontrol Filter/Pencarian di Kanan -->
                                  <div class="d-flex align-items-center flex-wrap">
                                      <label for="year-filter" class="mr-2 mb-0 text-nowrap">Tahun:</label>
                                      <select class="custom-select custom-select-sm" id="year-filter" style="width: auto;">
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
                                          <th width="200px">Ditujukan</th>
                                          <th width="250px">Perihal</th>
                                          <th width="200px">Penanggung Jawab</th>
                                          <th width="150px" class="text-center">Tgl Dokumen</th>
                                          <th class="text-center">View</th>
                                          <th class="text-center">Edit</th>
                                          <th class="text-center">Hapus</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      <?php
                                      $no = 1;
                                      foreach ($dataRows as $row):
                                          $id = (int)$row['id'];
                                          $no_surat = htmlspecialchars($row['no_surat'] ?? '', ENT_QUOTES, 'UTF-8');
                                          $ditujukan = htmlspecialchars($row['ditujukan'] ?? '', ENT_QUOTES, 'UTF-8');
                                          $perihal = htmlspecialchars($row['perihal'] ?? '', ENT_QUOTES, 'UTF-8');
                                          $penanggung = htmlspecialchars($row['penanggung'] ?? '', ENT_QUOTES, 'UTF-8');
                                          $file = htmlspecialchars($row['pdf'] ?? '', ENT_QUOTES, 'UTF-8');
                                          $tgl_dokumen = $row['tgl_dokumen'] ?? '';

                                          $tgl_formatted = '-';
                                          if (!empty($tgl_dokumen)) {
                                              try {
                                                  $tgl_formatted = (new DateTime($tgl_dokumen))->format('d M Y');
                                              } catch (Exception $e) { }
                                          }
                                      ?>
                                          <tr>
                                              <td class="text-center"><?php echo $no++; ?></td>
                                              <td class="text-left"><?php echo $no_surat; ?></td>
                                              <td class="text-left"><?php echo $ditujukan; ?></td>
                                              <td class="text-left" style="max-width: 250px;"><?php echo $perihal; ?></td>
                                              <td class="text-left"><?php echo $penanggung; ?></td>
                                              <td class="text-center"><?php echo $tgl_formatted; ?></td>
                                              <td class="text-center">
                                                  <?php if ($lv == '1' || $lv == '2' || $lv == '3'): ?>
                                                      <span class="tombol-view badge badge-info badge-square <?php echo empty($file) ? 'opacity-50' : ''; ?>" 
                                                            data-id="<?php echo $id; ?>" data-tahun="<?php echo $row['db_year']; ?>" title="Lihat PDF">
                                                          <i class="la la-eye"></i>
                                                      </span>
                                                  <?php endif; ?>
                                              </td>
                                              <td class="text-center">
                                                  <?php if ($lv == '1' || $lv == '2'): ?>
                                                      <span class="tombol-edit badge badge-warning badge-square" 
                                                            data-id="<?php echo $id; ?>" data-tahun="<?php echo $row['db_year']; ?>" title="Edit">
                                                          <i class="la la-edit"></i>
                                                      </span>
                                                  <?php endif; ?>
                                              </td>
                                              <td class="text-center">
                                                  <?php if ($lv == '1'): ?>
                                                      <span class="tombol-hapus badge badge-danger badge-square" 
                                                            data-id="<?php echo $id; ?>" data-nama="<?php echo $no_surat; ?>" 
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
                          <!-- /.card-body -->
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
  <!-- |                      MODAL KONFIRMASI HAPUS                     | -->
  <!-- =================================================================== -->
  <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header box-shadow-0 bg-gradient-x-danger text-white">
                  <h5 class="modal-title" id="modalHapusLabel"> Notifikasi</h5>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
              </div>
              <div class="modal-body">
                  Apakah Anda yakin ingin menghapus data surat dengan NO:
                  <strong><span id="detail-hapus"></span></strong>?
                  <br>
   
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                  <button type="button" class="btn btn-danger" id="tombolKonfirmasiHapus">Hapus</button>
              </div>
          </div>
      </div>
  </div>

  <!-- AKHIR MODAL FORM -->


  <!-- 2. Modal View Detail -->
  <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" data-backdrop="static">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header box-shadow-0 bg-gradient-x-info text-white">
                  <b>Lihat Dokumen</b>
              </div>
              <div class="modal-body">
                  <div id="view_pdf_container" class="mb-3">
                      <iframe id="pdf_viewer" style="width: 100%; height: 60vh; border: 1px solid #dee2e6; border-radius: .25rem;" src=""></iframe>
                  </div>
                  <table class="table table-bordered table-striped">
                      <tbody>
                          <tr><th style="width: 30%;">No Surat</th><td id="view_no_surat"></td></tr>
                          <tr><th>Di Tujukan</th><td id="view_ditujukan"></td></tr>
                          <tr><th>Perihal</th><td id="view_perihal"></td></tr>
                          <tr><th>Penanggung Jawab</th><td id="view_penanggung"></td></tr>
                          <tr><th>Tanggal</th><td id="view_tgl_dokumen"></td></tr>
                      </tbody>
                  </table>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
  </div>
  <!-- AKHIR MODAL VIEW -->

<!-- =================================================================== -->
<!-- |                      MODAL EDIT DATA                            | -->
<!-- =================================================================== -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header box-shadow-0 bg-gradient-x-primary text-white">
                <b class="modal-title" id="modalEditLabel"><i class="fas fa-edit"></i> Edit Data Surat</b>
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
                                <label for="edit_no_surat"><i class="fas fa-file-alt"></i> No Surat <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_no_surat" name="no_surat" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_tgl_dokumen" class="small fw-bold text-muted text-uppercase mb-1"><i class="fas fa-calendar"></i> Tgl Surat <span class="text-danger">*</span></label>
                                <input type="text" class="form-control datepicker" id="edit_tgl_dokumen" name="tgl_dokumen" required>

                            </div>
                            <div class="mb-3">
                                <label for="edit_ditujukan"><i class="fas fa-user-tag"></i> Ditujukan <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_ditujukan" name="ditujukan" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_penanggung"><i class="fas fa-user-tie"></i> Penanggung Jawab <span class="text-danger">*</span></label>
                                <input class="custom-select w-100" list="list_dari_keputusan_edit" id="edit_penanggung" name="penanggung" required>
                                <datalist id="list_dari_keputusan_edit">
                                    <?php echo $dari_options; ?>
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_perihal"><i class="fas fa-align-left"></i> Perihal <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="edit_perihal" name="perihal" rows="3" required></textarea>
                            </div>
                            <div class="mb-0">
                                <label class="form-label fw-bold"><i class="fas fa-paperclip"></i> Update Lampiran (.pdf)</label>
                                <div id="edit_drop-zone" class="border rounded p-2 bg-white" style="border-style: dashed !important; border-width: 2px !important;">
                                    <input class="d-none" type="file" id="edit_pdf" name="pdf[]" accept=".pdf" multiple>
                                    <div class="d-flex align-items-center mb-2 px-1">
                                        <button type="button" class="btn btn-sm btn-light border-0 px-2 py-1 mr-2 shadow-sm" id="edit_choose-file-btn" style="background-color: #f3f4f6; color: #4338ca; font-weight: 600; font-size: 0.8rem;">
                                            <i class="fas fa-folder-open mr-1"></i> Pilih...
                                        </button>
                                        <div class="text-muted" style="font-size: 0.75rem;">atau drag file kesini.</div>
                                    </div>
                                    <div id="edit_info_file_lama" class="mb-2 d-none">
                                        <div id="edit_link_file_lama" class="d-flex flex-column gap-1"></div>
                                    </div>
                                    <div id="edit_file-list" class="mb-2 d-flex flex-column gap-1"></div>
                                    <div class="border-top pt-2 d-flex justify-content-between align-items-center px-1">
                                        <div class="small fw-bold text-secondary"><i class="la la-chart-pie mr-2"></i> Total:</div>
                                        <div class="small"><span id="edit_file-size" class="text-primary fw-bold" style="font-size: 0.9rem;">0 B</span> <span class="text-muted ml-1" style="font-size: 0.75rem;">(Maks 2MB)</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary" id="edit_tombol-simpan">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- AKHIR MODAL EDIT -->
  

<!-- =================================================================== -->
<!-- |                      MODAL NOTIFIKASI ALERT                     | -->
<!-- =================================================================== -->
<div class="modal fade" id="modalNotifikasi" tabindex="-1" aria-labelledby="modalNotifikasiLabel">
    <div class="modal-dialog ">
        <div class="modal-content">
            <div class="modal-header" id="notifikasi-header">
                <h5 class="modal-title" id="modalNotifikasiLabel">
                    
                    <span id="notifikasi-title">Notifikasi</span>
                </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body" id="notifikasi-body">
                <!-- Pesan notifikasi akan diisi di sini oleh JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
  <!-- =================================================================== -->
  <!-- |                      MODAL FORM DATA                            | -->
  <!-- =================================================================== -->
    <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header box-shadow-0 bg-gradient-x-info text-white">
                    <b>Form Data Surat</b>
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
                                    <label for="no_surat"><i class="fas fa-file-alt"></i> No Surat <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="no_surat" name="no_surat" required>
                                </div>

                                <div class="mb-3">
                                    <label for="tgl_dokumen" class="small fw-bold text-muted text-uppercase mb-1"><i class="fas fa-calendar"></i> Tgl Surat <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control datepicker" id="tgl_dokumen" name="tgl_dokumen" required>

                                </div>

                                <div class="mb-3">
                                    <label for="ditujukan"><i class="fas fa-user-tag"></i> Ditujukan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="ditujukan" name="ditujukan" required>
                                </div>

                                <div class="mb-3">
                                    <label for="penanggung"><i class="fas fa-user-tie"></i> Penanggung Jawab <span class="text-danger">*</span></label>
                                    <input class="custom-select w-100" list="list_dari_keputusan" id="penanggung" name="penanggung" required>
                                    <datalist id="list_dari_keputusan">
                                        <?php echo $dari_options; ?>
                                    </datalist>
                                </div>
                            </div>
                            <!-- /.col-md-6 -->

                            <!-- Kolom Kanan: Klasifikasi, Catatan & Lampiran -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="perihal"><i class="fas fa-align-left"></i> Perihal <span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="perihal" name="perihal" rows="3" required></textarea>
                                </div>

                                <div class="mb-0">
                                    <label class="form-label fw-bold"><i class="fas fa-paperclip"></i> Lampiran File (.pdf)</label>
                                    <div id="drop-zone" class="border rounded p-2 bg-white" style="border-style: dashed !important; border-width: 2px !important;">
                                        <input class="d-none" type="file" id="pdf" name="pdf" accept=".pdf" multiple>
                                        
                                        <!-- Header: Button & Drag Text -->
                                        <div class="d-flex align-items-center mb-2 px-1">
                                            <button type="button" class="btn btn-sm btn-light border-0 px-2 py-1 mr-2 shadow-sm" id="choose-file-btn" style="background-color: #f3f4f6; color: #4338ca; font-weight: 600; font-size: 0.8rem;">
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
                                                <span id="file-size" class="text-primary fw-bold" style="font-size: 0.9rem;">0 B</span>
                                                <span class="text-muted ml-1" style="font-size: 0.75rem;">(Maks 1MB)</span>
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
                        <button type="button" class="btn btn-secondary custom" data-dismiss="modal"><i class="fas fa-times"></i> Tutup</button>
                        <button type="submit" class="btn btn-primary custom" id="tombol-simpan">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <i class="fas fa-save"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- /.formModal -->

  <script>
  $(function () {
      // --- 1. Variabel Global ---
      let existingFilesMap = {};
      const SELECTOR_EDIT = '.tombol-edit';

      // --- 2. Helper Functions ---
      function updateUI(isEdit = false) {
          const prefix = isEdit ? 'edit_' : '';
          const list = $(`#${prefix}file-list`);
          const sizeInfo = $(`#${prefix}file-size`);
          const btn = $(`#${prefix}tombol-simpan`);
          
          let totalSize = 0;
          // Add existing file sizes if applicable
          if (isEdit) {
              Object.values(existingFilesMap).forEach(size => totalSize += size);
          }
          
          sizeInfo.text((totalSize / 1024 / 1024).toFixed(2) + ' MB');
          // Validation can be added here if needed
      }

      function initDragDrop(zoneId, inputId, btnId) {
          const dz = document.getElementById(zoneId);
          const input = document.getElementById(inputId);
          const btn = document.getElementById(btnId);
          if (!dz || !input || !btn) return;

          ['dragenter', 'dragover'].forEach(name => {
              dz.addEventListener(name, (e) => { 
                  e.preventDefault(); 
                  dz.classList.add('bg-light', 'border-primary'); 
              });
          });
          ['dragleave', 'drop'].forEach(name => {
              dz.addEventListener(name, (e) => { 
                  e.preventDefault(); 
                  dz.classList.remove('bg-light', 'border-primary'); 
              });
          });
          dz.addEventListener('drop', (e) => {
              input.files = e.dataTransfer.files;
              $(input).trigger('change');
          });
          btn.addEventListener('click', () => input.click());
      }

      initDragDrop('drop-zone', 'pdf', 'choose-file-btn');
      initDragDrop('edit_drop-zone', 'edit_pdf', 'edit_choose-file-btn');

      $('.content table.table').DataTable({

            scrollY: 450,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
      });

          // Year Filter Logic
          $('#year-filter').on('change', function () {
              const selectedYear = $(this).val();
              window.location.href = '?suratkeputusan&tahun=' + selectedYear;
          });

          // [VALIDASI] Daftar field mandatory
          const mandatoryFields = ['no_surat', 'tgl_dokumen', 'ditujukan', 'penanggung', 'perihal'];

          function checkFormCompletion() {
              let isComplete = true;
              mandatoryFields.forEach(fieldId => {
                  const input = $(`#${fieldId}`);
                  if (!input.val() || input.val().trim() === '') isComplete = false;
              });
              const btn = $('#tombol-simpan');
              btn.prop('disabled', !isComplete);
          }

          function checkEditFormCompletion() {
              let isComplete = true;
              mandatoryFields.forEach(fieldId => {
                  const input = $(`#edit_${fieldId}`);
                  if (!input.val() || input.val().trim() === '') isComplete = false;
              });
              const btn = $('#edit_tombol-simpan');
              btn.prop('disabled', !isComplete);
          }

          mandatoryFields.forEach(fieldId => {
              $(`#${fieldId}`).on('input change', checkFormCompletion);
              $(`#edit_${fieldId}`).on('input change', checkEditFormCompletion);
          });

          function tampilkanNotifikasi(message, status) {
              const header = $('#notifikasi-header'), title = $('#notifikasi-title'), body = $('#notifikasi-body');
              header.removeClass('bg-success bg-danger bg-warning bg-info text-white text-dark');
              if (status === 'success') { header.addClass('bg-success text-white'); title.text('Sukses'); }
              else if (status === 'error') { header.addClass('bg-danger text-white'); title.text('Error'); }
              else { header.addClass('bg-info text-white'); title.text('Informasi'); }
              body.html(message);
              $('#modalNotifikasi').modal('show');
          }

          // Tambah Data
          $('#tombol-tambah').on('click', function () {
              $('#form-surat')[0].reset();
              $('#action').val('simpan');
              $('#formModal').modal('show');
          });

          $('#form-surat').on('submit', function (e) {
              e.preventDefault();
              const formData = new FormData(this);
              formData.append('tahun', $('#year-filter').val());
              
              $.ajax({
                  url: 'proses_surat_keputusan.php',
                  type: 'POST',
                  data: formData,
                  dataType: 'json',
                  contentType: false,
                  processData: false,
                  success: function (response) {
                      tampilkanNotifikasi(response.message, response.status);
                      if (response.status === 'success') {
                          $('#formModal').modal('hide');
                          setTimeout(() => location.reload(), 1000);
                      }
                  }
              });
          });

          // Edit Data
          $('.table').on('click', '.tombol-edit', function () {
              const id = $(this).data('id');
              const thn_row = $(this).data('tahun');
              $.ajax({
                  url: 'proses_surat_keputusan.php',
                  type: 'GET',
                  data: { action: 'ambil', id: id, tahun: thn_row || $('#year-filter').val() },
                  dataType: 'json',
                  success: function (response) {
                      if (response.status === 'success') {
                          const data = response.data;
                          $('#edit_id').val(data.id);
                          $('#edit_tahun').val(thn_row); // Set tahun asal untuk edit
                          $('#edit_no_surat').val(data.no_surat);
                          if(document.querySelector("#edit_tgl_dokumen")._flatpickr) {
                              document.querySelector("#edit_tgl_dokumen")._flatpickr.setDate(data.tgl_dokumen_raw);
                          }
                          $('#edit_ditujukan').val(data.ditujukan);

                          $('#edit_penanggung').val(data.penanggung);
                          $('#edit_perihal').val(data.perihal);
                          $('#edit_file_lama').val(data.pdf);

                          // Handle Lampiran List
                          existingFilesMap = data.file_sizes_map || {};
                          if (data.pdf) {
                              $('#edit_info_file_lama').removeClass('d-none');
                              const files = data.pdf.toString().split('|');
                              let fileLinks = '';
                              files.forEach(f => {
                                  if (f.trim() !== '') {
                                      const path = encodeURI('file/berkas-keputusan/' + f);
                                      const fileName = f.split('/').pop();
                                      fileLinks += `
                                      <div class="p-2 bg-light border rounded d-flex align-items-center justify-content-between mb-2 existing-file-item" data-filename="${f}">
                                          <div class="d-flex align-items-center flex-grow-1 overflow-hidden">
                                              <div class="me-3 text-primary bg-white p-2 rounded shadow-sm"><i class="fas fa-file-pdf fa-lg"></i></div>
                                              <div class="overflow-hidden">
                                                  <a href="${path}" target="_blank" class="text-decoration-none fw-bold text-primary text-truncate d-block">${fileName}</a>
                                              </div>
                                          </div>
                                          <button type="button" class="btn btn-link text-danger p-0 ms-2 hapus-file-lama-btn" title="Hapus file"><i class="fas fa-trash-alt"></i></button>
                                      </div>`;
                                  }
                              });
                              $('#edit_link_file_lama').html(fileLinks);
                          } else {
                              $('#edit_info_file_lama').addClass('d-none');
                          }
                          
                          updateUI(true);
                          $('#modalEdit').modal('show');

                      }
                  }
              });
          });

          $('#form-surat-edit').on('submit', function (e) {
              e.preventDefault();
              const formData = new FormData(this);
              // Gunakan tahun dari hidden input (tahun asal) atau filter sebagai fallback
              if (!formData.has('tahun')) {
                  formData.append('tahun', $('#edit_tahun').val() || $('#year-filter').val());
              }
              $.ajax({
                  url: 'proses_surat_keputusan.php',
                  type: 'POST',
                  data: formData,
                  dataType: 'json',
                  contentType: false,
                  processData: false,
                  success: function (response) {
                      tampilkanNotifikasi(response.message, response.status);
                      if (response.status === 'success') {
                          $('#modalEdit').modal('hide');
                          setTimeout(() => location.reload(), 1000);
                      }
                  }
              });
          });

          // Hapus Data
          $('.table').on('click', '.tombol-hapus', function () {
              const id = $(this).data('id');
              const nama = $(this).data('nama');
              const thn = $(this).data('tahun');
              $('#detail-hapus').text(nama);
              $('#tombolKonfirmasiHapus').data('id', id);
              $('#tombolKonfirmasiHapus').data('tahun', thn);
              $('#modalHapus').modal('show');
          });

          // Listener Hapus File Lama
          $('#edit_link_file_lama').on('click', '.hapus-file-lama-btn', function (e) {
              e.preventDefault();
              if (!confirm('Hapus lampiran ini?')) return;
              
              const item = $(this).closest('.existing-file-item');
              const filename = item.data('filename');
              item.remove();
              
              let currentFiles = $('#edit_file_lama').val().split('|').filter(f => f !== filename && f !== '');
              $('#edit_file_lama').val(currentFiles.join('|'));
              
              delete existingFilesMap[filename];
              if (currentFiles.length === 0) $('#edit_info_file_lama').addClass('d-none');
              updateUI(true);
          });


          $('#tombolKonfirmasiHapus').on('click', function () {
              const id = $(this).data('id');
              const thn_row = $(this).data('tahun');
              $.ajax({
                  url: 'proses_surat_keputusan.php',
                  type: 'POST',
                  data: { action: 'hapus', id: id, tahun: thn_row || $('#year-filter').val() },
                  dataType: 'json',
                  success: function (response) {
                      tampilkanNotifikasi(response.message, response.status);
                      if (response.status === 'success') {
                          $('#modalHapus').modal('hide');
                          setTimeout(() => location.reload(), 1000);
                      }
                  }
              });
          });

          // View PDF
          $('.table').on('click', '.tombol-view', function () {
              const id = $(this).data('id');
              const thn_row = $(this).data('tahun');
              $.ajax({
                  url: 'proses_surat_keputusan.php',
                  type: 'GET',
                  data: { action: 'ambil', id: id, tahun: thn_row || $('#year-filter').val() },
                  dataType: 'json',
                  success: function (response) {
                      if (response.status === 'success') {
                          const data = response.data;
                          $('#view_no_surat').text(data.no_surat);
                          $('#view_tgl_dokumen').text(data.tgl_dokumen);
                          $('#view_ditujukan').text(data.ditujukan);
                          $('#view_perihal').text(data.perihal);
                          $('#view_penanggung').text(data.penanggung);
                          if (data.pdf) {
                              const pdfUrl = encodeURI('file/berkas-keputusan/' + data.pdf);
                              $('#pdf_viewer').attr('src', pdfUrl);
                          } else {
                              $('#pdf_viewer').attr('src', '');
                          }
                          $('#viewModal').modal('show');
                      }
                  }
              });
          });
  });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>
  <script>
      $(function() {
          $(".datepicker").flatpickr({
              altInput: true,
              altFormat: "d-m-Y",
              dateFormat: "Y-m-d",
              locale: "id"
          });
      });
  </script>

