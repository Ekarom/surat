<?php
$host = "localhost";
$user = "root";
$pass = "";
//$host = "localhost";
//$user = "arsip";
//$pass = "BHmD8VlJELecRqw4S5OAYXDpc"; 

mysqli_report(MYSQLI_REPORT_OFF);

// Read base database from db.txt (acting as the master or last active DB)
$db_file = __DIR__ . '/cfg/db.txt';
if (file_exists($db_file)) {
  $db_master = trim(file_get_contents($db_file));
} else {
  $db_master = "sas_";
}

$db_initial = $db_master; // Store initial DB for management tools
$db = $db_master; // Default
$tahun = date('Y'); // Default fallback

// Initial connection
$conn = @new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  // FALLBACK LOGIK: Jika database di db.txt tidak ada, cari yang tersedia
  $temp_conn = @new mysqli($host, $user, $pass);
  if (!$temp_conn->connect_error) {
    $result_dbs = $temp_conn->query("SHOW DATABASES LIKE 'sas_%'");
    $available_dbs = [];
    if ($result_dbs) {
      while ($row_db = $result_dbs->fetch_row()) {
        $available_dbs[] = $row_db[0];
      }
    }
    $temp_conn->close();

    if (!empty($available_dbs)) {
      rsort($available_dbs); // Urutkan dari yang terbaru (misal sas_2026, sas_2025)
      $db_fallback = $available_dbs[0];
      $conn = @new mysqli($host, $user, $pass, $db_fallback);
      if (!$conn->connect_error) {
        $db = $db_fallback;
      } else {
        $conn = false;
      }
    } else {
      $conn = false;
    }
  } else {
    $conn = false;
  }
}

if ($conn && !$conn->connect_error) {
  // Dynamic adjustment based on dbset table
  $query_dbset = $conn->query("SELECT dbname, tahun FROM dbset WHERE aktif = 1 LIMIT 1");
  if ($query_dbset && $query_dbset->num_rows > 0) {
    $dbset_active = $query_dbset->fetch_assoc();
    $db_active = $dbset_active['dbname'];
    $tahun_aktif = $dbset_active['tahun'];

    // If different from current, switch database
    if ($db_active !== $db) {
      if ($conn->select_db($db_active)) {
        $db = $db_active;
      }
    }
    $tahun = $tahun_aktif;
  } else {
    // Fallback tahun dari nama database jika dbset kosong
    if (preg_match('/(\d{4})/', $db, $m)) {
      $tahun = $m[1];
    } else {
      $tahun = date('Y');
    }
  }
}

// Auto-migration: Pastikan kolom last_activity ada di tabel pegawai
if ($conn && !$conn->connect_error) {
  $col_check = @$conn->query("SHOW COLUMNS FROM pegawai LIKE 'last_activity'");
  if ($col_check && $col_check->num_rows === 0) {
    @$conn->query("ALTER TABLE pegawai ADD COLUMN last_activity DATETIME NULL DEFAULT NULL");
  }
}

// Auto-migration: Pastikan kolom last_activity ada di tabel tb_user
if ($conn && !$conn->connect_error) {
  $col_check2 = @$conn->query("SHOW COLUMNS FROM tb_user LIKE 'last_activity'");
  if ($col_check2 && $col_check2->num_rows === 0) {
    @$conn->query("ALTER TABLE tb_user ADD COLUMN last_activity DATETIME NULL DEFAULT NULL");
  }
}

// Update last_activity secara otomatis jika session aktif
if (session_status() === PHP_SESSION_NONE) {
  @session_start();
}

if ($conn && !$conn->connect_error && isset($_SESSION['id']) && isset($_SESSION['level'])) {
  $session_uid = (int) $_SESSION['id'];
  $session_lvl = $_SESSION['level'];
  if ($session_uid > 0) {
    if ($session_lvl == '4') {
      $conn->query("UPDATE pegawai SET last_activity = NOW() WHERE id = $session_uid");
    } else {
      $conn->query("UPDATE tb_user SET last_activity = NOW() WHERE id = $session_uid");
    }
  }
}


// Alias for compatibility
$sqlconn = $conn;

date_default_timezone_set("Asia/Jakarta");

///////// Profil Sekolah /////////  
$g = []; // Initialize to empty array to prevent warnings if connection fails
if ($sqlconn) {
  // Check if profils table exists before querying
  $table_check = @mysqli_query($sqlconn, "SHOW TABLES LIKE 'profils'");
  $table_exists = ($table_check && mysqli_num_rows($table_check) > 0);

  if ($table_exists) {
    $mysql = @mysqli_query($sqlconn, "select * from profils where id='1'");
    $g = ($mysql && mysqli_num_rows($mysql) > 0) ? mysqli_fetch_array($mysql) : [];
  } else {
    // Log warning for debugging
    error_log("Warning: Table 'profils' does not exist in database '{$db}'");
  }
}

$namasek = $g["nsekolah"] ?? "";
$possek = $g["kodepos"] ?? "";
$alamatsek = $g["alamat"] ?? "";
$kelsek = $g["kelurahan"] ?? "";
$kecsek = $g["kecamatan"] ?? "";
$provsek = $g["provinsi"] ?? "";
$kabsek = $g["kabupaten"] ?? "";
$tlpsek = $g["no_telp"] ?? "";
$emailsek = $g["email"] ?? "";
$website = $g["website"] ?? "";
$kepsek = $g["kepsek"] ?? "";
$nipkepsek = $g["nipkepsek"] ?? "";
$pengawas = $g["pengawas"] ?? "";
$nippengawas = $g["nippengawas"] ?? "";
$kasi = $g["kasi"] ?? "";
$nipkasi = $g["nipkasi"] ?? "";
$sklogo = $g["logo_sekolah"] ?? "logo_default.png";
$skback = $g["background_login"] ?? "bg_default.jpg";
$npsn = $g["npsn"] ?? "";

// --- FUNGSI GENERATOR SEKOLAH ---
if (!function_exists('generateVariasiSekolah')) {
  function generateVariasiSekolah($input)
  {
    // 1. Ubah ke huruf besar semua & hilangkan spasi ganda
    $temp = strtoupper($input);
    $temp = preg_replace('/\s+/', ' ', $temp);

    // 2. Standarisasi kata "SEKOLAH PERTAMA" menjadi "SMP"
    $temp = str_replace("SEKOLAH PERTAMA", "SMP", $temp);

    // 3. Standarisasi singkatan "SMPN" menjadi "SMP NEGERI"
    $temp = preg_replace('/\bSMPN\b/', 'SMP NEGERI', $temp);

    $basis = trim($temp);

    // --- VARIABEL HASIL ---
    $namapendekbesar = $basis;

    $namapendekkecil = ucwords(strtolower($basis));
    $namapendekkecil = str_replace("Smp ", "SMP ", $namapendekkecil);

    $namapanjangbesar = str_replace("SMP", "SEKOLAH MENENGAH PERTAMA", $basis);
    $namapajangkecil = ucwords(strtolower($namapanjangbesar));

    if (strpos($basis, 'NEGERI') !== false) {
      $singkatan = str_replace("SMP NEGERI", "SMPN", $basis);
    } else {
      $singkatan = $basis;
    }
    if (strpos($singkatan, 'SMPN') !== false) {
      //  $namapendeknegerikecil = ucwords(strtolower($singkatan));
      //$namapendeknegerikecil = str_replace("SMPN", "SMP Negeri", $singkatan);

      $namapendeknegeri = str_replace("SMPN", "SMP Negeri", $singkatan);
      $namapendeknegeribesar = strtoupper($namapendeknegeri);
    } else {
      $namapendeknegeri = $basis;
      $namapendeknegeribesar = strtoupper($namapendeknegeri);

    }

    return [
      'namapanjangbesar' => $namapanjangbesar,
      'namapajangkecil' => $namapajangkecil,
      'namapendekbesar' => $namapendekbesar,
      'namapendekkecil' => $namapendekkecil,
      'namapendeknegeri' => $namapendeknegeri,
      'namapendeknegeribesar' => $namapendeknegeribesar,
      'singkatan' => $singkatan
    ];
  }
}

// --- JALANKAN FUNGSI SEKOLAH ---
$smpn = generateVariasiSekolah($namasek);

$namapanjangbesar = $smpn['namapanjangbesar'];
$namapajangkecil = $smpn['namapajangkecil'];
$namapendekbesar = $smpn['namapendekbesar'];
$namapendekkecil = $smpn['namapendekkecil'];
$namapendeknegeri = $smpn['namapendeknegeri'];
$namapendeknegeribesar = $smpn['namapendeknegeribesar'];
$singkatan = $smpn['singkatan'];


// --- FUNGSI BANTUAN (Tetap di luar) ---
if (!function_exists('fixCapitalization')) {
  function fixCapitalization($text)
  {
    $text = ucwords(strtolower($text));
    $text = str_replace("Dki ", "DKI ", $text);
    $text = preg_replace('/\bDi\s/', 'DI ', $text);
    return $text;
  }
}

// --- FUNGSI GENERATOR PROVINSI (UPDATED) ---
if (!function_exists('generateVariasiProvinsi')) {
  function generateVariasiProvinsi($input)
  {
    // 1. Normalisasi awal
    $temp = strtoupper($input);
    $temp = preg_replace('/\s+/', ' ', $temp);
    $temp = trim($temp);

    // Hapus kata PROVINSI
    $temp = preg_replace('/^PROVINSI\s+/', '', $temp);

    // Standarisasi D.I. jadi DI
    $temp = str_replace(['D.I.', 'D.I'], 'DI', $temp);

    // 2. TENTUKAN BASIS PENDEK DULU (Nama Inti)
    // Kita hapus dulu embel-embel "DKI" atau "DI" jika user menuliskannya
    // Supaya kita dapat murni "JAKARTA" atau "YOGYAKARTA"
    $basis_pendek = str_replace(['DKI ', 'DI '], '', $temp);

    // 3. TENTUKAN BASIS LENGKAP (Rekonstruksi)
    // Cek nama intinya, jika Jakarta/Yogya, paksa tambah awalan
    if ($basis_pendek == 'JAKARTA') {
      $basis_lengkap = 'DKI JAKARTA';
    } elseif ($basis_pendek == 'YOGYAKARTA') {
      $basis_lengkap = 'DI YOGYAKARTA';
    } else {
      // Untuk provinsi lain (Jawa Barat, Bali, dll), nama lengkap = nama pendek
      $basis_lengkap = $basis_pendek;
    }

    // 4. OUTPUT
    return [
      // Versi LENGKAP (Otomatis ada DKI jika Jakarta)
      'provinsilengkapbesar' => $basis_lengkap,
      'provinsilengkapkecil' => fixCapitalization($basis_lengkap),

      // Versi PENDEK (Murni nama daerah)
      'provinsibesar' => $basis_pendek,
      'provinsikecil' => fixCapitalization($basis_pendek)
    ];
  }
}

// --- EKSEKUSI ---
// Asumsi $provsek dari database adalah "PROVINSI DKI JAKARTA"
$provinsi = generateVariasiProvinsi($provsek);

// Cara Memanggilnya:
$provinsilengkapbesar = $provinsi['provinsilengkapbesar']; // "DKI JAKARTA"
$provinsilengkapkecil = $provinsi['provinsilengkapkecil']; // "DKI Jakarta"
$provinsibesar = $provinsi['provinsibesar'];        // "JAKARTA"
$provinsikecil = $provinsi['provinsikecil'];        // "Jakarta"

$provinsilengkap = "Provinsi " . $provinsilengkapkecil;


if (!function_exists('generateVariasiWebsite')) {
  function generateVariasiWebsite($input)
  {
    // 1. Normalisasi & Regex (Sama seperti sebelumnya)
    $text = strtolower($input);
    $pattern = '/(?:https?:\/\/)?(?:www\.)?([a-z0-9.-]+\.[a-z]{2,})/i';
    preg_match_all($pattern, $text, $matches);

    $domains_found = $matches[1]; // Array hasil domain bersih

    // 2. Tentukan Website 1
    $web1 = isset($domains_found[0]) ? $domains_found[0] : "";

    // 3. Tentukan Website 2 (LOGIKA BARU)
    if (isset($domains_found[1])) {
      // Jika user memasukkan 2 website, ambil yang kedua
      $web2 = $domains_found[1];
    } else {
      // Jika user HANYA isi 1, maka website2 = website1
      $web2 = $web1;
    }

    // 4. Susun Websitelengkap (Untuk Tampilan)
    // Kita gunakan $domains_found asli untuk tampilan agar rapi.
    // Jika input cuma 1, tampilan tetap 1 link (tidak duplikat "link - link").
    // Tapi jika Anda ingin tampilannya juga duplikat, ubah logika di bawah ini.

    $list_lengkap = [];

    // Cek domain yang ditemukan secara unik untuk tampilan string
    if (!empty($domains_found)) {
      foreach ($domains_found as $d) {
        $list_lengkap[] = "https://" . $d;
      }
    } else {
      // Jika kosong sama sekali (input user ngaco)
      $list_lengkap[] = "-";
    }

    $websitelengkap = implode(" - ", $list_lengkap);

    // Perbaikan kecil: Jika web1 kosong, web2 juga harus string kosong (bukan null)
    if (empty($web1)) {
      $websitelengkap = "-";
      $web2 = "";
    }

    return [
      'websitelengkap' => $websitelengkap,
      'website1' => $web1,
      'website2' => $web2
    ];
  }
}

// --- IMPLEMENTASI KE VARIABLE FINAL ---
// Panggil fungsi menggunakan data dari database
$data_web = generateVariasiWebsite($website);

$websitelengkap = $data_web['websitelengkap'];
$website1 = $data_web['website1'];
$website2 = $data_web['website2'];


// --- DYNAMIC TAPEL & SEMESTER (For Organized Upload Folders) ---
if (session_status() === PHP_SESSION_NONE) {
    // Standardize session path to project root to ensure unified logout/session
    // This prevents subdirectories from starting independent sessions
    $current_script_path = str_replace('\\', '/', __DIR__);
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $session_path = '/' . trim(str_replace($doc_root, '', $current_script_path), '/') . '/';
    
    if (PHP_VERSION_ID >= 70300) {
        session_set_cookie_params([
            'path' => $session_path,
            'samesite' => 'Lax'
        ]);
    } else {
        session_set_cookie_params(0, $session_path);
    }
    
    session_start();
}

// Calculate Academic Year (Tapel) and Semester based on $tahun (active year from DB)
// Standard: The active year database (e.g. sas_2024) covers the period 2024/2025.
// July-Dec = Semester 1, Jan-June = Semester 2.
$currentMonth = (int) date('n');
$tapel_val = $tahun . "/" . ($tahun + 1);
$semester_val = ($currentMonth >= 7) ? "1" : "2";

// Set Session for use in upload processes if not already set or to keep synced
$_SESSION['tapel'] = $tapel_val;
$_SESSION['semester'] = $semester_val;
$_SESSION['tahundb'] = $tahun; // Sync with global $tahun

// Global Online Tracking: Update last_activity setiap request (HARUS setelah session_start)
if ($conn && !$conn->connect_error && isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true) {
  $sess_level = $_SESSION['level'] ?? '';
  $sess_id    = (int)($_SESSION['id'] ?? 0);

  if ($sess_level == '4') {
    // GURU: session id = pegawai.id langsung
    if ($sess_id > 0) {
      @$conn->query("UPDATE pegawai SET last_activity = NOW() WHERE id = $sess_id");
    }
  } else {
    // ADMIN / STAFF: session id = tb_user.id, update langsung di tb_user
    if ($sess_id > 0) {
      @$conn->query("UPDATE tb_user SET last_activity = NOW() WHERE id = $sess_id");
    }
  }
}

// --- DATABASE VERSION TRACKING ---
// Check if version table exists and get current version
$ver = "1.0.0"; // Default version
if ($sqlconn && !$sqlconn->connect_error) {
  // Check if version table exists
  $table_check = @mysqli_query($sqlconn, "SHOW TABLES LIKE 'version'");
  if ($table_check && mysqli_num_rows($table_check) > 0) {
    // Table exists, get latest version
    // Using 'ver' column as shown in database structure
    $mysqlv = mysqli_query($sqlconn, "select * from version order by id desc limit 1");
    $v = ($mysqlv && mysqli_num_rows($mysqlv) > 0) ? mysqli_fetch_array($mysqlv) : null;
    $ver = $v["ver"] ?? "1.0";
  }
}
?>