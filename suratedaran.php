  <?php
  // File ini adalah halaman antarmuka pengguna (UI) untuk manajemen data surat masuk.
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

      // [MODIFIKASI] Ambil tahun aktif dari GET atau session
      $tahun_aktif = $_GET['tahun'] ?? (isset($tahunsklh) ? $tahunsklh : ($_SESSION['tahundb'] ?? ''));

      // 2. Ambil data dari
      // Menambahkan penanganan error untuk memudahkan debugging.
      $sql_dari = mysqli_query($conn, "SELECT nm_pegawai FROM tbl_pegawai ORDER BY id ASC") or die(mysqli_error($conn));
      while ($datanama = mysqli_fetch_array($sql_dari)) {
          // Gunakan htmlspecialchars untuk keamanan dari XSS
          $nama_pegawai = htmlspecialchars($datanama['nm_pegawai']);
          $dari_options .= "<option value=\"$nama_pegawai\">";
      }

      // 3. Ambil data tahun dari database (SHOW DATABASES LIKE 'sas_%')
      $tahun_options = "<option value=''>Semua</option>";
      $sql_dbs = mysqli_query($conn, "SHOW DATABASES LIKE 'sas_%'");
      if ($sql_dbs) {
          while ($row = mysqli_fetch_array($sql_dbs)) {
              $dbname = $row[0];
              if (preg_match('/^sas_(\d+)$/', $dbname, $matches)) {
                  $thn = $matches[1];
                  $selected = ($tahun_aktif == $thn) ? 'selected' : '';
                  $tahun_options .= "<option value=\"$thn\" $selected>$thn</option>";
              }
          }
      } else {
           $tahun_options .= "<!-- Error showing databases: " . mysqli_error($conn) . " -->"; 
      }

      // 4. Ambil Data Surat Edaran secara Statis (Ganti AJAX)
      $dataRows = [];
      if (!empty($tahun_aktif)) {
          try {
              $conn->select_db("sas_" . $tahun_aktif);
              $sql_data = mysqli_query($conn, "SELECT * FROM dokumenedaran ORDER BY id ASC");
              if ($sql_data) {
                  while ($row = mysqli_fetch_assoc($sql_data)) {
                      $dataRows[] = $row;
                  }
              }
          } catch (Exception $e) {
              // Gagal switch db
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

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
      <!-- [TAMBAHAN] Loading Overlay -->
      

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Surat Edaran</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Surat Edaran</li>
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
                      <div class="card card-outline primary sm">
                          <!-- [PERBAIKAN] Merapikan header kontrol -->
                          <div class="card-header">
                              <div class="d-flex justify-content-between align-items-center flex-wrap">
                                  <!-- Tombol Tambah di Kiri -->
                                  <div>
<?php if($lv=="1"||$lv=="2") { ?>
                                      <button class="btn btn-primary btn-sm" id="tombol-tambah">
                                          <i class="fas fa-plus"></i> Tambah
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
                                          <th width="300px">Perihal</th>
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
                                              <td class="text-left fw-bold text-primary"><?php echo $no_surat; ?></td>
                                              <td class="text-left"><?php echo $ditujukan; ?></td>
                                              <td class="text-left" style="max-width: 300px;"><?php echo $perihal; ?></td>
                                              <td class="text-center"><?php echo $tgl_formatted; ?></td>
                                              <td class="text-center">
                                                  <?php if ($lv == '1' || $lv == '2' || $lv == '3'): ?>
                                                      <span class="tombol-view badge badge-info badge-square <?php echo empty($file) ? 'opacity-50' : ''; ?>" 
                                                            data-id="<?php echo $id; ?>" title="Lihat PDF">
                                                          <i class="la la-eye"></i>
                                                      </span>
                                                  <?php endif; ?>
                                              </td>
                                              <td class="text-center">
                                                  <?php if ($lv == '1' || $lv == '2'): ?>
                                                      <span class="tombol-edit badge badge-warning badge-square" 
                                                            data-id="<?php echo $id; ?>" title="Edit">
                                                          <i class="la la-edit"></i>
                                                      </span>
                                                  <?php endif; ?>
                                              </td>
                                              <td class="text-center">
                                                  <?php if ($lv == '1'): ?>
                                                      <span class="tombol-hapus badge badge-danger badge-square" 
                                                            data-id="<?php echo $id; ?>" data-nama="<?php echo $no_surat; ?>">
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
  <!-- |                      MODAL NOTIFIKASI ALERT                     | -->
  <!-- =================================================================== -->
  <div class="modal fade" id="modalNotifikasi" tabindex="-1" aria-labelledby="modalNotifikasiLabel">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header" id="notifikasi-header">
                  <h5 class="modal-title" id="modalNotifikasiLabel">
                      <span id="notifikasi-title">Notifikasi</span>
                  </h5>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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

  <!-- =================================================================== -->
  <!-- |                      MODAL VIEW DETAIL                          | -->
  <!-- =================================================================== -->
  <div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" data-backdrop="static">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header box-shadow-0 bg-gradient-x-info text-white">
                  <b class="modal-title" id="viewModalLabel">Lihat Dokumen</b>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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
                          <tr><th>Tanggal Dokumen</th><td id="view_tgl_dokumen"></td></tr>
                      </tbody>
                  </table>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
  </div>

  <!-- =================================================================== -->
  <!-- |                      MODAL FORM DATA (TAMBAH)                   | -->
  <!-- =================================================================== -->
  <div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" data-backdrop="static">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header box-shadow-0 bg-gradient-x-info text-white">
                  <b class="modal-title" id="formModalLabel">Tambah Surat Edaran</b>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              </div>
              <form id="form-surat" enctype="multipart/form-data">
                  <div class="modal-body">
                      <input type="hidden" name="action" value="simpan">
                      <div class="row">
                          <div class="col-md-6">
                              <div class="mb-3">
                                  <label for="no_surat">No Surat <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="no_surat" name="no_surat" required>
                              </div>
                              <div class="mb-3">
                                  <label for="tgl_dokumen">Tgl Surat <span class="text-danger">*</span></label>
                                  <input type="date" class="form-control" id="tgl_dokumen" name="tgl_dokumen" required>
                              </div>
                              <div class="mb-3">
                                  <label for="ditujukan">Ditujukan <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="ditujukan" name="ditujukan" required>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="mb-3">
                                  <label for="perihal">Perihal <span class="text-danger">*</span></label>
                                  <textarea class="form-control" id="perihal" name="perihal" rows="4" required></textarea>
                              </div>
                              <div class="mb-3">
                                  <label for="pdf">File PDF</label>
                                  <input type="file" class="form-control" id="pdf" name="pdf" accept=".pdf">
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                      <button type="submit" class="btn btn-primary" id="tombol-simpan">Simpan</button>
                  </div>
              </form>
          </div>
      </div>
  </div>

  <!-- =================================================================== -->
  <!-- |                      MODAL EDIT DATA                            | -->
  <!-- =================================================================== -->
  <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" data-backdrop="static">
      <div class="modal-dialog modal-lg">
          <div class="modal-content">
              <div class="modal-header box-shadow-0 bg-gradient-x-primary text-white">
                  <b class="modal-title" id="modalEditLabel">Edit Surat Edaran</b>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              </div>
              <form id="form-surat-edit" enctype="multipart/form-data">
                  <div class="modal-body">
                      <input type="hidden" id="edit_id" name="id">
                      <input type="hidden" name="action" value="edit">
                      <input type="hidden" id="edit_file_lama" name="file_lama">
                      <div class="row">
                          <div class="col-md-6">
                              <div class="mb-3">
                                  <label for="edit_no_surat">No Surat <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="edit_no_surat" name="no_surat" required>
                              </div>
                              <div class="mb-3">
                                  <label for="edit_tgl_dokumen">Tgl Surat <span class="text-danger">*</span></label>
                                  <input type="date" class="form-control" id="edit_tgl_dokumen" name="tgl_dokumen" required>
                              </div>
                              <div class="mb-3">
                                  <label for="edit_ditujukan">Ditujukan <span class="text-danger">*</span></label>
                                  <input type="text" class="form-control" id="edit_ditujukan" name="ditujukan" required>
                              </div>
                          </div>
                          <div class="col-md-6">
                              <div class="mb-3">
                                  <label for="edit_perihal">Perihal <span class="text-danger">*</span></label>
                                  <textarea class="form-control" id="edit_perihal" name="perihal" rows="4" required></textarea>
                              </div>
                              <div class="mb-3">
                                  <label for="edit_pdf">Update File PDF</label>
                                  <input type="file" class="form-control" id="edit_pdf" name="pdf" accept=".pdf">
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
                      <button type="submit" class="btn btn-primary" id="edit_tombol-simpan">Simpan Perubahan</button>
                  </div>
              </form>
          </div>
      </div>
  </div>

  <!-- =================================================================== -->
  <!-- |                      MODAL KONFIRMASI HAPUS                     | -->
  <!-- =================================================================== -->
  <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel">
      <div class="modal-dialog">
          <div class="modal-content">
              <div class="modal-header bg-danger text-white">
                  <h5 class="modal-title" id="modalHapusLabel">Konfirmasi Hapus</h5>
                  <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              </div>
              <div class="modal-body">
                  Apakah Anda yakin ingin menghapus surat: <strong><span id="detail-hapus"></span></strong>?
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                  <button type="button" class="btn btn-danger" id="tombolKonfirmasiHapus">Hapus</button>
              </div>
          </div>
      </div>
  </div>

  <script>
  $(function () {
      $('.content table.table').DataTable({
          scrollY: 450,
          scrollX: true,
          scrollCollapse: true,
          paging: false,
      });

      // Year Filter Logic
      $('#year-filter').on('change', function () {
          const selectedYear = $(this).val();
              const url = new URL(window.location.href);
              url.searchParams.set('tahun', selectedYear);
              window.location.href = url.toString();
          });

          // [VALIDASI] Daftar field mandatory
          const mandatoryFields = ['no_surat', 'tgl_dokumen', 'ditujukan', 'perihal'];

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
                  url: 'proses_surat_edaran.php',
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
              $.ajax({
                  url: 'proses_surat_edaran.php',
                  type: 'GET',
                  data: { action: 'ambil', id: id, tahun: $('#year-filter').val() },
                  dataType: 'json',
                  success: function (response) {
                      if (response.status === 'success') {
                          const data = response.data;
                          $('#edit_id').val(data.id);
                          $('#edit_no_surat').val(data.no_surat);
                          $('#edit_tgl_dokumen').val(data.tgl_dokumen_raw);
                          $('#edit_ditujukan').val(data.ditujukan);
                          $('#edit_perihal').val(data.perihal);
                          $('#edit_file_lama').val(data.pdf);
                          $('#modalEdit').modal('show');
                      }
                  }
              });
          });

          $('#form-surat-edit').on('submit', function (e) {
              e.preventDefault();
              const formData = new FormData(this);
              formData.append('tahun', $('#year-filter').val());
              $.ajax({
                  url: 'proses_surat_edaran.php',
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
              $('#detail-hapus').text(nama);
              $('#tombolKonfirmasiHapus').data('id', id);
              $('#modalHapus').modal('show');
          });

          $('#tombolKonfirmasiHapus').on('click', function () {
              const id = $(this).data('id');
              $.ajax({
                  url: 'proses_surat_edaran.php',
                  type: 'POST',
                  data: { action: 'hapus', id: id, tahun: $('#year-filter').val() },
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
              $.ajax({
                  url: 'proses_surat_edaran.php',
                  type: 'GET',
                  data: { action: 'ambil', id: id, tahun: $('#year-filter').val() },
                  dataType: 'json',
                  success: function (response) {
                      if (response.status === 'success') {
                          const data = response.data;
                          $('#view_no_surat').text(data.no_surat);
                          $('#view_tgl_dokumen').text(data.tgl_dokumen);
                          $('#view_ditujukan').text(data.ditujukan);
                          $('#view_perihal').text(data.perihal);
                          if (data.pdf) {
                              const pdfUrl = encodeURI('file/berkas-edaran/' + data.pdf);
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
