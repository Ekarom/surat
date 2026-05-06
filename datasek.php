<?php
include "cfg/konek.php";
?>
<!-- Toastin Assets -->
<link rel="stylesheet" href="css/toastin.css">
<script src="js/toastin.js"></script>
<?php


// --- FUNGSI UPDATE DATA (Global Scope) ---
function updateData($conn, $fields, $id = 1)
{
    $setQuery = [];
    $types = "";
    $values = [];

    foreach ($fields as $col => $val) {
        $setQuery[] = "`$col` = ?";
        $types .= "s";
        $values[] = $val;
    }
    $values[] = $id;
    $types .= "i";

    $sql = "UPDATE profils SET " . implode(", ", $setQuery) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt)
        return false;

    $stmt->bind_param($types, ...$values);
    return $stmt->execute();
}

// --- SEEDING DATA AWAL (PENTING!) ---
$cek_awal = $sqlconn->query("SELECT id FROM profils WHERE id = 1");
if ($cek_awal && $cek_awal->num_rows == 0) {
    $sql_seed = "INSERT INTO profils (
        id, sudin, kop_dinas, nsekolah, npsn, alamat, kecamatan, kelurahan, provinsi, kabupaten, kodepos, no_telp, email, website, youtube, facebook, instagram,
        nipkasudin, nrkkasudin, kasudin, nipkepsek, nrkkepsek, kepsek, nippengawas, nrkpengawas, pengawas, nipkasi, nrkkasi, kasi, nipktu, nrkktu, ktu, logo_sekolah, background_login, logo_pemda
    ) VALUES (
        1, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '',
        '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', 'logo_default.png', 'bg_default.jpg', ''
    )";
    $sqlconn->query($sql_seed);
}

// Ensure social media columns exist
$check_cols = $sqlconn->query("SHOW COLUMNS FROM profils LIKE 'youtube'");
if ($check_cols && $check_cols->num_rows == 0) {
    $sqlconn->query("ALTER TABLE profils ADD youtube VARCHAR(255) AFTER website, ADD facebook VARCHAR(255) AFTER youtube, ADD instagram VARCHAR(255) AFTER facebook");
}

// --- LOGIK POST REQUEST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    // A. Update Data Sekolah
    if ($_POST['action'] == 'update_sekolah') {
        $fields = [
            'sudin' => $_POST['sudin'],
            'kop_dinas' => $_POST['kop_dinas'],
            'nsekolah' => $_POST['nsekolah'],
            'npsn' => $_POST['npsn'],
            'alamat' => $_POST['njalan'],
            'kecamatan' => $_POST['nkec'],
            'kelurahan' => $_POST['nkel'],
            'provinsi' => $_POST['nprovinsi'],
            'kabupaten' => $_POST['nkab'],
            'kodepos' => $_POST['pos'],
            'no_telp' => $_POST['tlp'],
            'email' => $_POST['email'],
            'website' => $_POST['web'],
            'youtube' => $_POST['youtube'],
            'facebook' => $_POST['facebook'],
            'instagram' => $_POST['instagram']
        ];
        if (updateData($sqlconn, $fields)) {
            echo "<script>showToast('Data Sekolah Berhasil Disimpan!', 'success', 7000);</script>";
        } else {
            echo "<script>showToast('Gagal Menyimpan Data Sekolah!', 'error', 8000);</script>";
        }
    }

    // B. Update Data Pejabat
    $pejabatConfig = [
        'kasudin' => ['nama' => 'kasudin', 'nip' => 'nipkasudin', 'nrk' => 'nrkkasudin'],
        'kepsek' => ['nama' => 'kepsek', 'nip' => 'nipkepsek', 'nrk' => 'nrkkepsek'],
        'pengawas' => ['nama' => 'pengawas', 'nip' => 'nippengawas', 'nrk' => 'nrkpengawas'],
        'kasi' => ['nama' => 'kasi', 'nip' => 'nipkasi', 'nrk' => 'nrkkasi'],
        'ktu' => ['nama' => 'ktu', 'nip' => 'nipktu', 'nrk' => 'nrkktu']
    ];

    foreach ($pejabatConfig as $jabatan => $cols) {
        if ($_POST['action'] == 'update_' . $jabatan) {
            $fields = [
                $cols['nama'] => $_POST['nama'],
                $cols['nip'] => $_POST['nip'],
                $cols['nrk'] => $_POST['nrk']
            ];
            if (updateData($sqlconn, $fields)) {
                echo "<script>showToast('Data " . ucfirst($jabatan) . " Berhasil Disimpan!', 'success', 7000);</script>";
            }
        }
    }
}

// --- FETCH DATA UTAMA ---
$query = "SELECT * FROM profils WHERE id = 1 LIMIT 1";
$result = $sqlconn->query($query);
$data = ($result) ? $result->fetch_assoc() : null;

// Inisialisasi data kosong jika belum ada record (Fallback)
if (!$data) {
    $columns = [
        'sudin',
        'kop_dinas',
        'nsekolah',
        'npsn',
        'alamat',
        'kecamatan',
        'kelurahan',
        'provinsi',
        'kabupaten',
        'kodepos',
        'no_telp',
        'email',
        'website',
        'youtube',
        'facebook',
        'instagram',
        'nipkasudin',
        'nrkkasudin',
        'kasudin',
        'nipkepsek',
        'nrkkepsek',
        'kepsek',
        'nippengawas',
        'nrkpengawas',
        'pengawas',
        'nipkasi',
        'nrkkasi',
        'kasi',
        'nipktu',
        'nrkktu',
        'ktu',
        'logo_sekolah',
        'background_login',
        'logo_pemda'
    ];
    $data = array_fill_keys($columns, '');
}
?>

<!-- UI HTML -->
<style>
    .img-preview-container {
        width: 100%;
        height: 200px;
        border: 2px dashed #ccc;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 10px;
        overflow: hidden;
        background: #f4f6f9;
        position: relative;
    }

    .img-preview-container img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform 0.3s ease;
    }

    .nav-tabs .nav-link {
        color: rgba(255, 255, 255, 0.7) !important;
        border: none !important;
    }

    .nav-tabs .nav-link.active {
        color: #fff !important;
        background: rgba(255, 255, 255, 0.2) !important;
        font-weight: bold;
        border-radius: 5px 5px 0 0;
    }

    .progress {
        height: 20px;
        display: none;
        margin-top: 10px;
        border-radius: 10px;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="m-0">Data Sekolah</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">Profil Sekolah</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- KOLOM KIRI: Data Identitas & Pejabat -->
                <div class="col-md-7">
                    <!-- Data Identitas Sekolah -->
                    <div class="card card-primary card-outline">
                        <div class="card-header bg-menu-gradient">
                            <h3 class="card-title text-white">Data Identitas Sekolah</h3>
                        </div>
                        <form method="POST" action="">
                            <input type="hidden" name="action" value="update_sekolah">
                            <div class="card-body">
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Kop Dinas (Header)</label>
                                    <div class="col-sm-9">
                                        <textarea id="summernote" name="kop_dinas"><?= $data['kop_dinas'] ?></textarea>
                                        <div class="mt-2">
                                            <small class="text-muted">Gunakan template untuk hasil presisi.</small>
                                            <button type="button" class="btn btn-outline-success btn-sm ml-2"
                                                id="useTemplate">
                                                <i class="fas fa-file-alt"></i> Gunakan Template Kop SMPN 171
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Sudin</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="sudin"
                                            value="<?= $data['sudin'] ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Nama Sekolah</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="nsekolah"
                                            value="<?= $data['nsekolah'] ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">NPSN</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="npsn"
                                            value="<?= isset($data['npsn']) ? $data['npsn'] : '' ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Jalan</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="njalan"
                                            value="<?= isset($data['alamat']) ? $data['alamat'] : '' ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Kelurahan</label>
                                            <input type="text" class="form-control" name="nkel"
                                                value="<?= isset($data['kelurahan']) ? $data['kelurahan'] : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Kecamatan</label>
                                            <input type="text" class="form-control" name="nkec"
                                                value="<?= isset($data['kecamatan']) ? $data['kecamatan'] : '' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Kabupaten/Kota</label>
                                            <input type="text" class="form-control" name="nkab"
                                                value="<?= isset($data['kabupaten']) ? $data['kabupaten'] : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Provinsi</label>
                                            <input type="text" class="form-control" name="nprovinsi"
                                                value="<?= isset($data['provinsi']) ? $data['provinsi'] : '' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Kode Pos</label>
                                            <input type="text" class="form-control" name="pos"
                                                value="<?= isset($data['kodepos']) ? $data['kodepos'] : '' ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Telepon</label>
                                            <input type="text" class="form-control" name="tlp"
                                                value="<?= isset($data['no_telp']) ? $data['no_telp'] : '' ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Email</label>
                                    <div class="col-sm-9">
                                        <input type="email" class="form-control" name="email"
                                            value="<?= $data['email'] ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Website</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="web"
                                            value="<?= isset($data['website']) ? $data['website'] : '' ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Youtube</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-danger text-white border-0"><i class="fab fa-youtube"></i></span>
                                            </div>
                                            <input type="text" class="form-control" name="youtube"
                                                value="<?= isset($data['youtube']) ? $data['youtube'] : '' ?>" placeholder="URL Channel Youtube">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Facebook</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-primary text-white border-0"><i class="fab fa-facebook"></i></span>
                                            </div>
                                            <input type="text" class="form-control" name="facebook"
                                                value="<?= isset($data['facebook']) ? $data['facebook'] : '' ?>" placeholder="URL Profile Facebook">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label class="col-sm-3 col-form-label">Instagram</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text bg-purple text-white border-0"><i class="fab fa-instagram"></i></span>
                                            </div>
                                            <input type="text" class="form-control" name="instagram"
                                                value="<?= isset($data['instagram']) ? $data['instagram'] : '' ?>" placeholder="Username Instagram">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i>
                                    Simpan</button>
                            </div>
                        </form>
                    </div>

                    <!-- TABEL PEJABAT (TABS) Card -->
                    <div class="card card-navy card-outline card-tabs">
                        <div class="card-header bg-menu-gradient p-0 pt-1 border-bottom-0">
                            <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" id="tab-kasudin" data-toggle="pill"
                                        href="#content-kasudin" role="tab" style="color: #fff;">Kasudin</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-kepsek" data-toggle="pill" href="#content-kepsek"
                                        role="tab" style="color: #fff;">Kepsek</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-pengawas" data-toggle="pill" href="#content-pengawas"
                                        role="tab" style="color: #fff;">Pengawas</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-kasi" data-toggle="pill" href="#content-kasi" role="tab"
                                        style="color: #fff;">Kasi</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" id="tab-ktu" data-toggle="pill" href="#content-ktu" role="tab"
                                        style="color: #fff;">KTU</a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-three-tabContent">
                                <?php
                                $pejabatMap = [
                                    'kasudin' => ['label' => 'Kepala Suku Dinas', 'cols' => ['kasudin', 'nipkasudin', 'nrkkasudin']],
                                    'kepsek' => ['label' => 'Kepala Sekolah', 'cols' => ['kepsek', 'nipkepsek', 'nrkkepsek']],
                                    'pengawas' => ['label' => 'Pengawas Sekolah', 'cols' => ['pengawas', 'nippengawas', 'nrkpengawas']],
                                    'kasi' => ['label' => 'Kepala Seksi', 'cols' => ['kasi', 'nipkasi', 'nrkkasi']],
                                    'ktu' => ['label' => 'Kepala Tata Usaha', 'cols' => ['ktu', 'nipktu', 'nrkktu']]
                                ];

                                foreach ($pejabatMap as $key => $info) {
                                    $active = ($key == 'kasudin') ? 'show active' : '';
                                    ?>
                                    <div class="tab-pane fade <?= $active ?>" id="content-<?= $key ?>" role="tabpanel">
                                        <form method="POST" action="">
                                            <input type="hidden" name="action" value="update_<?= $key ?>">
                                            <div class="form-group">
                                                <label>Nama <?= $info['label'] ?></label>
                                                <input type="text" class="form-control" name="nama"
                                                    value="<?= isset($data[$info['cols'][0]]) ? $data[$info['cols'][0]] : '' ?>">
                                            </div>
                                            <div class="form-group">
                                                <label>NIP</label>
                                                <input type="text" class="form-control" name="nip"
                                                    value="<?= isset($data[$info['cols'][1]]) ? $data[$info['cols'][1]] : '' ?>"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                    inputmode="numeric" placeholder="Hanya Angka">
                                            </div>
                                            <div class="form-group">
                                                <label>NRK</label>
                                                <input type="text" class="form-control" name="nrk"
                                                    value="<?= isset($data[$info['cols'][2]]) ? $data[$info['cols'][2]] : '' ?>"
                                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                    inputmode="numeric" placeholder="Hanya Angka">
                                            </div>
                                            <div class="card-footer text-right">
                                                <button type="submit" class="btn btn-success"><i
                                                        class="fas fa-save"></i>&nbsp;Simpan</button>
                                            </div>
                                        </form>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: Upload Gambar -->
                <div class="col-md-5">
                    <?php
                    $uploads = [
                        'logo_sekolah' => 'Logo Sekolah',
                        'logo_pemda' => 'Logo Pemda',
                        'background_login' => 'Background Login',
                    ];

                    foreach ($uploads as $field => $title) {
                        $currentImg = !empty($data[$field]) ? "images/" . $data[$field] : "images/noimage.png";
                        ?>
                        <div class="card card-danger card-outline">
                            <div class="card-header bg-menu-gradient">
                                <h3 class="card-title text-white"><?= $title ?></h3>
                            </div>
                            <div class="card-body text-center">
                                <div class="img-preview-container">
                                    <img id="preview_<?= $field ?>" src="<?= $currentImg ?>" alt="Preview">
                                </div>
                                <div class="btn-group mb-2" id="controls_<?= $field ?>" style="display:none;">
                                    <button type="button" class="btn btn-sm btn-default"
                                        onclick="rotateImage('<?= $field ?>', -90)"><i class="fas fa-undo"></i></button>
                                    <button type="button" class="btn btn-sm btn-default"
                                        onclick="rotateImage('<?= $field ?>', 90)"><i class="fas fa-redo"></i></button>
                                </div>
                                <div class="form-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file_<?= $field ?>"
                                            accept="image/jpeg, image/png" onchange="previewFile('<?= $field ?>')">
                                        <label class="custom-file-label text-left" for="file_<?= $field ?>">Pilih
                                            file...</label>
                                    </div>
                                    <small class="text-muted">Format: JPG/PNG</small>
                                </div>
                                <div class="progress" id="progress_wrap_<?= $field ?>">
                                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated"
                                        id="progress_<?= $field ?>" role="progressbar" style="width: 0%">0%</div>
                                </div>
                                <button type="button" class="btn btn-warning btn-block mt-2"
                                    onclick="uploadFile('<?= $field ?>')">
                                    <i class="fas fa-upload"></i> Upload
                                </button>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- JAVASCRIPT UNTUK PREVIEW, ROTATE & UPLOAD -->
<script>
    $(document).ready(function () {
        function doInitSummernote() {
            const $editor = $('#summernote');
            if ($editor.length === 0) return;
            $editor.summernote({
                height: 300,
                placeholder: 'Tulis kop dinas di sini...',
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onInit: function () {
                        console.log('Summernote Initialized Successfully');
                    }
                }
            });
        }

        function loadSummernoteAssets(callback) {
            var cssId = 'summernote-css';
            var jsId = 'summernote-js';
            var cssLoaded = document.getElementById(cssId);
            var jsLoaded = document.getElementById(jsId);

            function loadJS() {
                if (jsLoaded) {
                    callback();
                    return;
                }
                var script = document.createElement('script');
                script.id = jsId;
                script.src = 'https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.js';
                script.onload = callback;
                script.onerror = function () {
                    console.error('Gagal memuat Summernote JS dari CDN.');
                };
                document.head.appendChild(script);
            }

            if (!cssLoaded) {
                var link = document.createElement('link');
                link.id = cssId;
                link.rel = 'stylesheet';
                link.href = 'https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-bs4.min.css';
                link.onload = loadJS;
                document.head.appendChild(link);
            } else {
                loadJS();
            }
        }

        if (typeof $.fn.summernote !== 'undefined') {
            doInitSummernote();
        } else {
            loadSummernoteAssets(function () {
                doInitSummernote();
            });
        }

        $('#useTemplate').click(function () {
            if (typeof $.fn.summernote === 'undefined') return;

            var nsekolah = $('input[name="nsekolah"]').val() || 'NAMA SEKOLAH';
            var alamat = $('input[name="njalan"]').val() || 'ALAMAT SEKOLAH';
            var kelurahan = $('input[name="nkel"]').val() || 'KELURAHAN';
            var kecamatan = $('input[name="nkec"]').val() || 'KECAMATAN';
            var kabupaten = $('input[name="nkab"]').val() || 'KABUPATEN/KOTA';
            var web = $('input[name="web"]').val() || 'website.sch.id';
            var email = $('input[name="email"]').val() || 'email@sch.id';
            var pos = $('input[name="pos"]').val() || '00000';

            var logoPemda = "<?= !empty($data['logo_pemda']) ? 'images/' . $data['logo_pemda'] : 'images/logo_pemda_default.png' ?>";
            var logoSekolah = "<?= !empty($data['logo_sekolah']) ? 'images/' . $data['logo_sekolah'] : 'images/logo_sekolah_default.png' ?>";

            var template = `
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
                    <tbody>
                        <tr>
                            <td style="width: 15%; text-align: center; vertical-align: middle;">
                                <img src="${logoPemda}" style="width: 80px; height: auto;" alt="Logo DKI">
                            </td>
                            <td style="width: 70%; text-align: center; vertical-align: middle;">
                                <h4 style="margin: 0; font-size: 13px; font-weight: bold; font-family: Arial, sans-serif;
                                           text-transform: uppercase; line-height: 1.2;">PEMERINTAH PROVINSI DAERAH KHUSUS IBUKOTA JAKARTA</h4>
                                <h4 style="margin: 0; font-size: 13px; font-weight: bold; font-family: Arial, sans-serif;
                                           text-transform: uppercase; line-height: 1.2;">DINAS PENDIDIKAN</h4>
                                <h2 style="margin: 3px 0; font-size: 20px; font-weight: bold; font-family: Arial, sans-serif;
                                           text-transform: uppercase;">${nsekolah}</h2>
                                <p style="margin: 0; font-size: 11px; font-family: Arial, sans-serif; line-height: 1.3;">${alamat} Kel. ${kelurahan} Kec. ${kecamatan} ${kabupaten}</p>
                                <p style="margin: 0; font-size: 11px; font-family: Arial, sans-serif; line-height: 1.3;">Website: ${web} | Email: ${email}</p>
                                <h3 style="margin: 6px 0 0 0; font-size: 18px; font-weight: bold; font-family: Arial, sans-serif;
                                           letter-spacing: 5px; text-transform: uppercase; line-height: 1;">J A K A R T A</h3>
                            </td>
                            <td style="width: 15%; text-align: center; vertical-align: middle;">
                                <img src="${logoSekolah}" style="width: 80px; height: auto; opacity: 1;" alt="Logo Sekolah" onerror="this.style.display='none'">
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p style="text-align: right; font-style: italic; font-size: 11px; margin: 3px 0 0 0; font-family: Arial, sans-serif;">Kode Pos ${pos}</p>
                <hr class="garis-tebal" style="border: 0; border-top: 3px solid black; margin-top: 5px; opacity: 1;">
            `;
            $('#summernote').summernote('code', template);
        });
    });

    var rotations = { 'logo_pemda': 0, 'logo_sekolah': 0, 'background_login': 0 };

    function previewFile(field) {
        var preview = document.querySelector('#preview_' + field);
        var file = document.querySelector('#file_' + field).files[0];
        var controls = document.querySelector('#controls_' + field);
        var reader = new FileReader();

        if (file) {
            reader.onloadend = function () {
                preview.src = reader.result;
                rotations[field] = 0;
                updateRotation(field);
                controls.style.display = 'inline-block';
            }
            reader.readAsDataURL(file);
        }
    }

    function rotateImage(field, degree) {
        rotations[field] += degree;
        updateRotation(field);
    }

    function updateRotation(field) {
        var preview = document.querySelector('#preview_' + field);
        preview.style.transform = 'rotate(' + rotations[field] + 'deg)';
    }

    function uploadFile(field) {
        var fileInput = document.getElementById('file_' + field);
        if (fileInput.files.length === 0) {
            showToast("Pilih gambar terlebih dahulu!", 'warning', 7000);
            return;
        }

        var file = fileInput.files[0];
        var formData = new FormData();
        formData.append("file_upload", file);
        formData.append("target_column", field);

        var finalRotation = rotations[field] % 360;
        formData.append("rotation", finalRotation);

        var xhr = new XMLHttpRequest();

        xhr.upload.addEventListener("progress", function (evt) {
            if (evt.lengthComputable) {
                var percentComplete = parseInt((evt.loaded / evt.total) * 100);
                var progressWrap = document.getElementById('progress_wrap_' + field);
                var progressBar = document.getElementById('progress_' + field);

                progressWrap.style.display = 'flex';
                progressBar.style.width = percentComplete + '%';
                progressBar.innerHTML = percentComplete + '%';
            }
        }, false);

        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    try {
                        var resp = JSON.parse(xhr.responseText);
                        if (resp.status === 'success') {
                            showToast(resp.msg, 'success', 7000);
                            setTimeout(function () {
                                document.getElementById('progress_wrap_' + field).style.display = 'none';
                                document.getElementById('controls_' + field).style.display = 'none';
                            }, 2000);
                        } else {
                            showToast(resp.msg, 'error', 8000);
                        }
                    } catch (e) {
                        showToast("Respon tidak valid (bukan JSON).", 'error', 8000);
                        console.log("Error Parsing JSON:", e);
                        console.log("Raw Data:", xhr.responseText);
                    }
                } else {
                    showToast("Gagal menghubungi server upload.", 'error', 8000);
                }
            }
        };

        xhr.open("POST", "upload_datasek.php", true);
        xhr.send(formData);
    }
</script>