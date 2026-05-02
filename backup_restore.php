<?php



/*
 * Skrip PHP - MySQL Database Toolkit
 * Fitur: Kloning, Backup (Download .sql.gz), dan Restore (Upload .sql/.sql.gz)
 * Versi ini menggunakan UI (HTML/JS/CSS) berbasis Tab dan AJAX.
 *
 * PERUBAHAN:
 * - Tab Backup sekarang menyimpan file ke server di folder '_backups'.
 * - Menambahkan tabel untuk menampilkan, mengunduh, dan menghapus backup.
 */

// Nonaktifkan batas waktu eksekusi untuk proses yang lama
set_time_limit(0);

// Mulai sesi jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Naikkan batas unggah file dan memori
ini_set('upload_max_filesize', '256M');
ini_set('post_max_size', '256M');
ini_set('memory_limit', '512M');

// ======================================================================\
// KONFIGURASI KONEKSI
// ======================================================================\
// ======================================================================
// KONFIGURASI KONEKSI (Include konek.php)
// ======================================================================
require_once __DIR__ . '/cfg/konek.php';
// Jika variabel koneksi tidak didefinisikan di konek.php, gunakan default WAMP/XAMPP
if (!isset($db_host))
    $db_host = 'localhost';
if (!isset($db_user))
    $db_user = 'root';
if (!isset($db_pass))
    $db_pass = '';
// $database sudah didefinisikan di konek.php (via SESSION atau default)

// ======================================================================\
// KONFIGURASI DIREKTORI BACKUP
// ======================================================================\
define('BACKUP_DIR', __DIR__ . '/_backups');

// ======================================================================\
// KONFIGURASI PATH (PENTING!)
// ======================================================================\
// Sesuaikan path ini jika Anda mendapat error 'command not found' or 'Return code: 255'.
//
// Linux/macOS (default): '' (kosong, mengandalkan PATH)
// Linux/macOS (alternatif): '/usr/bin/'
// Windows (XAMPP/WAMP): 'C:/path/to/mysql/bin/' (Gunakan forward slash '/')
// Contoh WAMP: 'C:/wamp64/bin/mariadb/mariadb10.6.5/bin/'
//
// CATATAN: Gunakan PATH sistem (kosong) jika MySQL sudah ada di environment PATH
$mysql_path = 'C:/wamp64/bin/mariadb/mariadb10.6.5/bin/'; // SESUAIKAN DENGAN VERSI ANDA

// Cek otomatis jika path di atas salah (Fallback)
if (!file_exists($mysql_path . 'mysqldump.exe')) {
    // Coba cari di folder mariadb lain (WAMP biasanya punya satu folder di sini)
    $wamp_bin = 'C:/wamp64/bin/mariadb/';
    if (is_dir($wamp_bin)) {
        $folders = glob($wamp_bin . 'mariadb*', GLOB_ONLYDIR);
        if ($folders) {
            $mysql_path = str_replace('\\', '/', $folders[0]) . '/bin/';
        }
    }
}

// ======================================================================
// LOGIKA TAHUN AJARAN & SEMESTER
// ======================================================================
$bulan_sekarang = date('n');
$tahun_sekarang = date('Y');

if ($bulan_sekarang >= 7) {
    // Juli - Desember: Semester 1 (Ganjil), Tahun Ajaran mulai tahun ini
    // Contoh: Jul 2026 -> TA 2026/2027
    $tahun = $tahun_sekarang;
    $semester = 'Ganjil';
} else {
    // Januari - Juni: Semester 2 (Genap), Tahun Ajaran mulai tahun lalu
    // Contoh: Jan 2026 -> TA 2025/2026
    $tahun = $tahun_sekarang - 1;
    $semester = 'Genap';
}

// Untuk default target clone
// UPDATE (18 Jan 2026): Ubah ke $tahun + 1 agar tidak bentrok dengan DB Sumber (Target Sama).
// Jan 2026 (TA 2025/2026) -> Sumber: 2025 -> Target: 2026
$tahun_clone = $tahun + 1;

// ======================================================================
// LOGIKA PHP BACKEND (AJAX HANDLER)
// ======================================================================\

// Pastikan direktori backup ada dan dapat ditulis
if (!is_dir(BACKUP_DIR)) {
    mkdir(BACKUP_DIR, 0755, true);
}
if (!is_writable(BACKUP_DIR)) {
    // Coba ubah izin jika direktori ada tapi tidak dapat ditulis
    @chmod(BACKUP_DIR, 0755);
    if (!is_writable(BACKUP_DIR)) {
        // Jika masih tidak bisa, kirim error (hanya jika ini adalah request AJAX)
        if (!empty($_POST['action'])) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Error: Direktori backup "' . htmlspecialchars(BACKUP_DIR) . '" tidak dapat ditulis. Periksa izin folder.']);
            exit;
        }
        // Jika bukan AJAX, kita akan menampilkannya di HTML nanti
    }
}

// ======================================================================\
// LOGIKA DOWNLOAD (GET REQUEST) - BARU DITAMBAHKAN
// ======================================================================\
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['download'])) {
    $file_to_download = basename($_GET['download']); // Keamanan: basename()
    $file_path = realpath(BACKUP_DIR . '/' . $file_to_download);

    // Validasi keamanan: pastikan file ada di dalam BACKUP_DIR
    if ($file_path && strpos($file_path, realpath(BACKUP_DIR)) === 0 && file_exists($file_path)) {

        header('Content-Description: File Transfer');
        header('Content-Type: application/gzip'); // Kita tahu ini .gz
        header('Content-Disposition: attachment; filename="' . $file_to_download . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));

        // Bersihkan output buffer sebelum membaca file
        ob_clean();
        flush();

        readfile($file_path);
        exit; // PENTING: Hentikan eksekusi skrip agar HTML tidak terkirim

    } else {
        // File tidak ditemukan atau tidak valid
        header("HTTP/1.0 404 Not Found");
        echo "Error 404: File tidak ditemukan atau akses ditolak.";
        exit;
    }
}

// Hanya proses jika ini adalah request POST (dari AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {

    // Bersihkan output buffer sebelum kirim JSON untuk hindari error
    while (ob_get_level())
        ob_end_clean();

    // Log error ke file untuk debugging (jangan tampilkan ke output agar tidak merusak JSON)
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', __DIR__ . '/php_errors.log');

    // Set error handler to catch non-exception errors and return as JSON
    set_error_handler(function ($errno, $errstr, $errfile, $errline) {
        if (!(error_reporting() & $errno))
            return false;
        echo json_encode(['success' => false, 'message' => "PHP Error [$errno]: $errstr in $errfile on line $errline"]);
        exit;
    });

    header('Content-Type: application/json');
    $action = $_POST['action'];
    $response = ['success' => false, 'message' => 'Aksi tidak diketahui.'];

    // Amankan nama database
    $source_db = isset($_POST['source_db']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['source_db']) : null;
    $target_db = isset($_POST['target_db']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['target_db']) : null;
    $backup_db = isset($_POST['backup_db']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['backup_db']) : null;
    $file_to_delete = isset($_POST['file_to_delete']) ? basename($_POST['file_to_delete']) : null; // Keamanan: basename()

    // Escape shell arguments untuk keamanan dasar
    $db_pass_escaped = escapeshellarg($db_pass);
    $db_host_escaped = escapeshellarg($db_host);
    $db_user_escaped = escapeshellarg($db_user);
    $source_db_escaped = $source_db ? escapeshellarg($source_db) : null;
    $target_db_escaped = $target_db ? escapeshellarg($target_db) : null;
    $backup_db_escaped = $backup_db ? escapeshellarg($backup_db) : null;

    // Path lengkap ke mysqldump dan mysql
    $mysqldump = escapeshellarg($mysql_path . 'mysqldump');
    $mysql = escapeshellarg($mysql_path . 'mysql');
    $gzip = escapeshellarg('gzip'); // Asumsi gzip ada di PATH
    $gunzip = escapeshellarg('gunzip'); // Asumsi gunzip ada di PATH

    // Tambahkan flag password hanya jika password tidak kosong
    $pass_flag = ($db_pass !== null && $db_pass !== '') ? " -p{$db_pass_escaped}" : "";

    try {
        switch ($action) {
            // ========================
            // Aksi: Ambil Data dbset
            // ========================
            case 'get_dbset':
                // Coba beberapa kemungkinan database yang memiliki tabel dbset
                $possible_dbs = [];

                // Prioritas: database_master > database > sas_2025 > sas_2026
                if (isset($database_master) && !empty($database_master)) {
                    $possible_dbs[] = $database_master;
                }
                if (isset($database) && !empty($database)) {
                    $possible_dbs[] = $database;
                }
                // Fallback ke database yang terlihat di screenshot (prioritas sas_2025 karena biasanya punya data)
                $possible_dbs[] = 'sas_2025';
                $possible_dbs[] = 'sas_2026';

                $mysqli = null;
                $db_to_use = null;
                $data = [];

                // Coba koneksi ke setiap database sampai menemukan yang punya data di tabel dbset
                // Kita juga pastikan tabel dbset BENAR-BENAR ada untuk menghindari error jika belum di-create
                foreach ($possible_dbs as $db_name) {
                    $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
                    if ($mysqli->connect_errno) {
                        continue; // Coba database berikutnya
                    }
                    $mysqli->set_charset("utf8mb4");

                    // Cek apakah tabel dbset ada (jangan hanya COUNT, tapi cek eksistensi tabel secara aman)
                    $check_table = $mysqli->query("SHOW TABLES LIKE 'dbset'");
                    if ($check_table && $check_table->num_rows > 0) {
                        // Tabel dbset ada, cek apakah memiliki data
                        $check_data = $mysqli->query("SELECT COUNT(*) as count FROM dbset");
                        if ($check_data) {
                            $row = $check_data->fetch_assoc();
                            if ($row['count'] > 0) {
                                $db_to_use = $db_name;
                                break; // Database dengan data dbset ditemukan!
                            }
                        }
                    }
                    $mysqli->close();
                    $mysqli = null; // Reset mysqli 
                }

                if (!$db_to_use || !$mysqli || $mysqli->connect_errno) {
                    $err = ($mysqli && $mysqli->connect_error) ? $mysqli->connect_error : "Database dengan data tabel 'dbset' tidak ditemukan.";
                    throw new Exception("Tidak dapat menemukan database dengan tabel 'dbset'. Periksa konfigurasi. Error: " . $err);
                }

                $result = $mysqli->query("SELECT id, dbname, tahun, aktif FROM dbset ORDER BY tahun DESC");
                $data = [];
                if ($result) {
                    while ($row = $result->fetch_assoc()) {
                        $data[] = $row;
                    }
                    $result->free();
                } else {
                    $err = $mysqli->error;
                    $mysqli->close();
                    throw new Exception("Gagal mengambil data dbset: " . $err);
                }
                $mysqli->close();

                // Tambahkan info database yang digunakan untuk debugging
                $response = ['success' => true, 'data' => $data, 'database_used' => $db_to_use];
                break;

            // ========================
            // Aksi: Update Status dbset
            // ========================
            case 'update_dbset_status':
                $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
                $aktif = isset($_POST['aktif']) ? intval($_POST['aktif']) : 0;
                $password_confirm = isset($_POST['password']) ? $_POST['password'] : '';
                // Gunakan SESSION 'skradm' (User ID/Username)
                $skuser = isset($_SESSION['skradm']) ? $_SESSION['skradm'] : '';

                if ($id <= 0) {
                    throw new Exception("ID tidak valid.");
                }

                if (empty($skuser)) {
                    throw new Exception("Sesi kadaluarsa. Silakan login kembali.");
                }

                if (empty($password_confirm)) {
                    throw new Exception("Password wajib diisi.");
                }

                include_once __DIR__ . '/cfg/konek.php'; // Ensure connection

                // 1. Validasi User Authentication
                // Explicitly use sas_2025 for user authentication as requested, 
                // fallback if not available
                $auth_db = 'sas_2025';
                $mysqli_auth = new mysqli($db_host, $db_user, $db_pass, $auth_db);
                if ($mysqli_auth->connect_errno) {
                    $auth_db = (isset($database_master)) ? $database_master : $database;
                    $mysqli_auth = new mysqli($db_host, $db_user, $db_pass, $auth_db);
                    if ($mysqli_auth->connect_errno) {
                        throw new Exception("Gagal koneksi untuk autentikasi ($auth_db): " . $mysqli_auth->connect_error);
                    }
                }
                $mysqli_auth->set_charset("utf8mb4");

                $skuser_safe = $mysqli_auth->real_escape_string($skuser);
                $pass_input = $password_confirm;

                $sql_check_user = "SELECT * FROM tb_user WHERE (userid = '$skuser_safe' OR nama = '$skuser_safe')";
                $result_user = $mysqli_auth->query($sql_check_user);

                if (!$result_user) {
                    $err = $mysqli_auth->error;
                    $mysqli_auth->close();
                    throw new Exception("Error query user: " . $err);
                }

                if ($result_user->num_rows === 0) {
                    $mysqli_auth->close();
                    throw new Exception("User '$skuser' tidak ditemukan di tabel tb_user ($auth_db).");
                }

                $user_data = $result_user->fetch_assoc();

                if ($user_data['status'] != '1') {
                    $mysqli_auth->close();
                    throw new Exception("User '$skuser' tidak aktif (status={$user_data['status']}).");
                }

                $db_password = $user_data['password'];
                if (empty($db_password)) {
                    $mysqli_auth->close();
                    throw new Exception("User '$skuser' tidak memiliki password (kosong).");
                }

                $is_valid = false;
                if (password_verify($pass_input, $db_password) || md5($pass_input) === $db_password) {
                    $is_valid = true;
                }

                if (!$is_valid) {
                    $mysqli_auth->close();
                    throw new Exception("Password salah.");
                }

                $mysqli_auth->close(); // Tutup koneksi auth

                // 2. Temukan database yang berisi tb dbset
                $possible_dbs = [];
                if (isset($database_master) && !empty($database_master))
                    $possible_dbs[] = $database_master;
                if (isset($database) && !empty($database))
                    $possible_dbs[] = $database;
                $possible_dbs[] = 'sas_2025';
                $possible_dbs[] = 'sas_2026';

                $mysqli_dbset = null;
                $dbset_db = null;

                foreach ($possible_dbs as $db_name) {
                    $mysqli_dbset = new mysqli($db_host, $db_user, $db_pass, $db_name);
                    if ($mysqli_dbset->connect_errno)
                        continue;

                    $check_table = $mysqli_dbset->query("SHOW TABLES LIKE 'dbset'");
                    if ($check_table && $check_table->num_rows > 0) {
                        $check_data = $mysqli_dbset->query("SELECT COUNT(*) as count FROM dbset");
                        if ($check_data && $check_data->fetch_assoc()['count'] > 0) {
                            $dbset_db = $db_name;
                            break;
                        }
                    }
                    $mysqli_dbset->close();
                    $mysqli_dbset = null;
                }

                if (!$dbset_db || !$mysqli_dbset || $mysqli_dbset->connect_errno) {
                    throw new Exception("Tidak dapat menemukan database dengan tabel 'dbset' untuk proses update. Hubungi administrator.");
                }
                $mysqli_dbset->set_charset("utf8mb4");

                // 3. Proses Update (Exclusive Activation)
                try {
                    // Mulai transaksi jika didukung
                    $mysqli_dbset->begin_transaction();

                    // Jika menyalakan, matikan yang lain dulu
                    if ($aktif == 1) {
                        if (!$mysqli_dbset->query("UPDATE dbset SET aktif = '0'")) {
                            throw new Exception("Gagal me-reset status aktif dbset: " . $mysqli_dbset->error);
                        }
                    }

                    // Update status target
                    $stmt = $mysqli_dbset->prepare("UPDATE dbset SET aktif = ? WHERE id = ?");
                    if (!$stmt) {
                        throw new Exception("Gagal menyiapkan query update (Database: $dbset_db): " . $mysqli_dbset->error);
                    }

                    $stmt->bind_param("ii", $aktif, $id);
                    if (!$stmt->execute()) {
                        throw new Exception("Gagal update status (Database: $dbset_db): " . $stmt->error);
                    }
                    $stmt->close();

                    // Ambil dbname untuk ditulis kembali ke cfg/db.txt jika diaktifkan
                    $activated_dbname = null;
                    if ($aktif == 1) {
                        $stmt_sel = $mysqli_dbset->prepare("SELECT dbname FROM dbset WHERE id = ?");
                        $stmt_sel->bind_param("i", $id);
                        if ($stmt_sel->execute()) {
                            $res_sel = $stmt_sel->get_result();
                            if ($row_sel = $res_sel->fetch_assoc()) {
                                $activated_dbname = $row_sel['dbname'];
                            }
                        }
                        $stmt_sel->close();
                    }

                    // Commit transaksi
                    $mysqli_dbset->commit();

                    // 4. Update file cfg/db.txt
                    if ($aktif == 1 && $activated_dbname) {
                        $db_txt_path = __DIR__ . '/cfg/db.txt';
                        if (is_writable(dirname($db_txt_path)) || is_writable($db_txt_path)) {
                            if (file_put_contents($db_txt_path, $activated_dbname) === false) {
                                // Meskipun gagal nulis file, DB sudah keupdate. Tampilkan sebagai warning.
                                throw new Exception("Status Database diupdate, TAPI gagal menulis ke file " . htmlspecialchars($db_txt_path) . ". Periksa izin folder cfg.");
                            }
                        } else {
                            throw new Exception("Status Database diupdate, TAPI file " . htmlspecialchars($db_txt_path) . " tidak bisa ditulis (Read-Only).");
                        }
                    }

                    $response = ['success' => true, 'message' => 'Status berhasil diperbarui.'];

                } catch (Exception $e) {
                    // Rollback jika terjadi error
                    $mysqli_dbset->rollback();
                    throw $e;
                } finally {
                    $mysqli_dbset->close();
                }
                break;

            // ========================
            // Aksi: Kloning Database
            // ========================
            case 'clone':
                if (empty($source_db) || empty($target_db)) {
                    throw new Exception("Nama database sumber dan target tidak boleh kosong.");
                }
                if ($source_db === $target_db) {
                    throw new Exception("Database sumber dan target tidak boleh sama.");
                }

                $cmd_create = "{$mysql} -h {$db_host_escaped} -u {$db_user_escaped}{$pass_flag} -e " . escapeshellarg("CREATE DATABASE IF NOT EXISTS `{$target_db}`");
                $cmd_clone = "{$mysqldump} -h {$db_host_escaped} -u {$db_user_escaped}{$pass_flag} --skip-lock-tables --routines --triggers {$source_db_escaped} | {$mysql} -h {$db_host_escaped} -u {$db_user_escaped}{$pass_flag} {$target_db_escaped}";

                $output = [];
                $return_var = 0;

                // 1. Buat database target
                exec($cmd_create . " 2>&1", $output, $return_var);
                if ($return_var !== 0) {
                    throw new Exception("Gagal membuat database target: " . implode("\n", $output));
                }

                // 2. Lakukan kloning
                exec($cmd_clone . " 2>&1", $output, $return_var);
                if ($return_var !== 0) {
                    throw new Exception("Gagal kloning database: " . implode("\n", $output));
                }

                $response = ['success' => true, 'message' => "Database '{$source_db}' berhasil dikloning ke '{$target_db}'."];

                // 3. Update tabel dbset (Permintaan User)
                // Gunakan koneksi baru ke database master (untuk update dbset)
                $db_to_use = (isset($database_master)) ? $database_master : $database;
                $mysqli = new mysqli($db_host, $db_user, $db_pass, $db_to_use);
                if ($mysqli->connect_errno) {
                    // Rollback: Hapus database target jika koneksi gagal
                    $cmd_drop = "{$mysql} -h {$db_host_escaped} -u {$db_user_escaped}{$pass_flag} -e " . escapeshellarg("DROP DATABASE IF EXISTS `{$target_db}`");
                    exec($cmd_drop);
                    throw new Exception("Gagal koneksi ke database master ($db_to_use) untuk update dbset: " . $mysqli->connect_error);
                }
                $mysqli->set_charset("utf8mb4");

                $target_db_sql = $mysqli->real_escape_string($target_db);

                // Coba deteksi tahun dari nama database (misal sas_2025 -> 2025)
                $detected_year = $tahun;
                if (preg_match('/(\d{4})/', $target_db, $matches)) {
                    $detected_year = $matches[1];
                }
                $tahun_sql = $mysqli->real_escape_string($detected_year);

                // Nonaktifkan semua database lain sebelum insert yang baru
                $sql_deactivate = "UPDATE dbset SET aktif = '0'";
                if (!$mysqli->query($sql_deactivate)) {
                    // Log warning jika gagal update, tapi lanjutkan insert
                    // Atau throw exception jika ini kritikal.
                    // Untuk amannya, kita throw exception agar user sadar ada masalah.
                    $error_msg = $mysqli->error;
                    $mysqli->close();
                    // Rollback: Hapus database target
                    $cmd_drop = "{$mysql} -h {$db_host_escaped} -u {$db_user_escaped}{$pass_flag} -e " . escapeshellarg("DROP DATABASE IF EXISTS `{$target_db}`");
                    exec($cmd_drop);
                    throw new Exception("Gagal menonaktifkan database lain: " . $error_msg);
                }

                // Insert ke dbset: id, dbname, tahun, aktif
                $sql_insert = "INSERT INTO dbset (id, dbname, tahun, aktif) VALUES (NULL, '$target_db_sql', '$tahun_sql', '1')";

                if (!$mysqli->query($sql_insert)) {
                    $error_msg = $mysqli->error;
                    $mysqli->close();

                    // Rollback: Hapus database target jika insert gagal
                    $cmd_drop = "{$mysql} -h {$db_host_escaped} -u {$db_user_escaped}{$pass_flag} -e " . escapeshellarg("DROP DATABASE IF EXISTS `{$target_db}`");
                    exec($cmd_drop);

                    throw new Exception("Gagal update tabel dbset (Kloning dibatalkan): " . $error_msg);
                }

                $mysqli->close();

                // Tulis nama database ke file ../cfg/db.txt
                $db_txt_path = __DIR__ . '/cfg/db.txt';
                file_put_contents($db_txt_path, $target_db);

                $response = ['success' => true, 'message' => "Database '{$source_db}' berhasil dikloning ke '{$target_db}' dan dicatat di dbset."];

                break;

            // ========================
            // Aksi: Backup Database
            // ========================
            case 'backup':
                if (empty($backup_db)) {
                    throw new Exception("Nama database untuk backup tidak boleh kosong.");
                }

                // VALIDASI: Pastikan database benar-benar ada sebelum di-dump
                $mysqli_check = new mysqli($db_host, $db_user, $db_pass);
                if ($mysqli_check->connect_errno) {
                    throw new Exception("Gagal koneksi untuk validasi: " . $mysqli_check->connect_error);
                }
                $db_exists = false;
                if ($res = $mysqli_check->query("SHOW DATABASES LIKE '$backup_db'")) {
                    if ($res->num_rows > 0)
                        $db_exists = true;
                    $res->free();
                }
                $mysqli_check->close();

                if (!$db_exists) {
                    throw new Exception("Database '$backup_db' tidak ditemukan di server. Silakan refresh halaman dan pilih database yang ada.");
                }

                // Ambil port jika ada (default 3306)
                $db_port = "3306";
                if (strpos($db_host, ':') !== false) {
                    list($host_part, $port_part) = explode(':', $db_host);
                    $db_host_only = $host_part;
                    $db_port = $port_part;
                } else {
                    $db_host_only = $db_host;
                }

                // Ganti localhost ke 127.0.0.1 untuk stabilitas di Windows
                if ($db_host_only === 'localhost')
                    $db_host_only = '127.0.0.1';

                $timestamp = date('Y-m-d_H-i-s');
                $backup_file = BACKUP_DIR . "/{$backup_db}_{$timestamp}.sql.gz";

                // Perbaikan pemanggilan mysqldump untuk Windows
                $mysqldump_bin = $mysql_path . 'mysqldump.exe';
                if (!file_exists($mysqldump_bin))
                    $mysqldump_bin = 'mysqldump';

                // Cari folder plugin (penting untuk error caching_sha2_password)
                $plugin_flag = "";
                $plugin_dir = realpath($mysql_path . '../lib/plugin');
                if ($plugin_dir) {
                    $plugin_flag = " --plugin-dir=" . escapeshellarg(str_replace('\\', '/', $plugin_dir));
                }

                $cmd_dump_only = "\"$mysqldump_bin\"$plugin_flag -h " . escapeshellarg($db_host_only) . " -P $db_port -u {$db_user_escaped}{$pass_flag} --skip-lock-tables --routines --triggers {$backup_db_escaped}";

                // Debug: Catat perintah yang dijalankan (tanpa password untuk keamanan)
                $debug_cmd = str_replace($db_pass, '******', $cmd_dump_only);
                file_put_contents(__DIR__ . '/debug_backup.txt', "[" . date('Y-m-d H:i:s') . "] Executing: $debug_cmd\n", FILE_APPEND);

                $descriptorspec = [
                    0 => ["pipe", "r"],  // STDIN
                    1 => ["pipe", "w"],  // STDOUT
                    2 => ["pipe", "w"]   // STDERR
                ];

                $gz_file = gzopen($backup_file, 'wb9');
                if (!$gz_file) {
                    throw new Exception("Gagal membuka file backup untuk ditulis: " . htmlspecialchars($backup_file));
                }

                $process = proc_open($cmd_dump_only, $descriptorspec, $pipes);

                if (!is_resource($process)) {
                    gzclose($gz_file);
                    unlink($backup_file);
                    throw new Exception("Gagal memulai proses mysqldump.");
                }

                // Alirkan STDOUT (SQL) dari mysqldump langsung ke file .gz
                stream_set_blocking($pipes[1], true);
                while (!feof($pipes[1])) {
                    gzwrite($gz_file, fread($pipes[1], 8192));
                }

                $error_output = stream_get_contents($pipes[2]);

                // Tutup semua resource
                fclose($pipes[1]);
                fclose($pipes[2]);
                gzclose($gz_file);
                $return_var = proc_close($process);

                if ($return_var !== 0) {
                    if (file_exists($backup_file)) {
                        unlink($backup_file);
                    }
                    // Tambahkan saran jika error 1049
                    $extra_tip = (strpos($error_output, '1049') !== false) ? "\nTip: Pastikan database '$backup_db' sudah dibuat melalui menu 'Kloning DB' sebelum di-backup." : "";
                    throw new Exception("Gagal backup database: " . $error_output . $extra_tip);
                }

                $response = ['success' => true, 'message' => "Database '{$backup_db}' berhasil di-backup ke " . basename($backup_file) . "."];

                break;

            // ========================
            // Aksi: Restore Database
            // ========================
            case 'restore':
                if (empty($target_db)) {
                    throw new Exception("Nama database target untuk restore tidak boleh kosong.");
                }
                if (!isset($_FILES['backup_file']) || $_FILES['backup_file']['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Gagal mengunggah file. Error code: " . ($_FILES['backup_file']['error'] ?? 'N/A'));
                }

                $file_tmp_path = $_FILES['backup_file']['tmp_name'];
                $file_name = $_FILES['backup_file']['name'];
                $file_type = mime_content_type($file_tmp_path);

                // Buat database jika belum ada
                $cmd_create = "{$mysql} -h {$db_host_escaped} -u {$db_user_escaped}{$pass_flag} -e " . escapeshellarg("CREATE DATABASE IF NOT EXISTS `{$target_db}`");
                exec($cmd_create . " 2>&1", $output, $return_var);
                if ($return_var !== 0) {
                    throw new Exception("Gagal membuat database target: " . implode("\n", $output));
                }

                // Tentukan perintah berdasarkan tipe file
                if (strpos($file_name, '.sql.gz') !== false || $file_type === 'application/gzip' || $file_type === 'application/x-gzip') {
                    // File .gz - PERUBAHAN: Gunakan proc_open dan gzread

                    $cmd_mysql_only = "{$mysql} -h {$db_host_escaped} -u {$db_user_escaped}{$pass_flag} {$target_db_escaped}";

                    $descriptorspec = [
                        0 => ["pipe", "r"],  // STDIN
                        1 => ["pipe", "w"],  // STDOUT
                        2 => ["pipe", "w"]   // STDERR
                    ];

                    $gz_file = gzopen($file_tmp_path, 'rb');
                    if (!$gz_file) {
                        throw new Exception("Gagal membuka file backup .gz untuk dibaca.");
                    }

                    $process = proc_open($cmd_mysql_only, $descriptorspec, $pipes);

                    if (!is_resource($process)) {
                        gzclose($gz_file);
                        throw new Exception("Gagal memulai proses mysql.");
                    }

                    // Alirkan data dari file .gz langsung ke STDIN (pipe 0) mysql
                    while (!gzeof($gz_file)) {
                        fwrite($pipes[0], gzread($gz_file, 8192)); // Baca 8KB dari gz, tulis ke STDIN
                    }

                    gzclose($gz_file);
                    fclose($pipes[0]); // Tutup STDIN untuk memberi sinyal EOF ke mysql

                    $error_output = stream_get_contents($pipes[2]); // Tangkap error
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    $return_var = proc_close($process);

                    if ($return_var !== 0) {
                        throw new Exception("Gagal restore database: " . $error_output);
                    }

                } elseif (strpos($file_name, '.sql') !== false || $file_type === 'application/sql' || $file_type === 'text/plain') {
                    // File .sql - Gunakan exec seperti sebelumnya
                    $cmd_restore = "{$mysql} -h {$db_host_escaped} -u {$db_user_escaped}{$pass_flag} {$target_db_escaped} < " . escapeshellarg($file_tmp_path);

                    // Lakukan restore
                    exec($cmd_restore . " 2>&1", $output, $return_var);
                    if ($return_var !== 0) {
                        throw new Exception("Gagal restore database: " . implode("\n", $output));
                    }

                } else {
                    throw new Exception("Format file tidak didukung. Harap unggah file .sql atau .sql.gz.");
                }

                /* Restoration completed successfully */

                $response = ['success' => true, 'message' => "File '" . $file_name . "' berhasil di-restore ke database '{$target_db}'."];
                break;

            // ========================
            // Aksi: Ambil Daftar DB
            // ========================
            case 'get_databases':
                // Gunakan mysqli agar lebih aman dari path mysql yang salah
                $mysqli = new mysqli($db_host, $db_user, $db_pass);
                if ($mysqli->connect_errno) {
                    throw new Exception("Gagal koneksi ke database: " . $mysqli->connect_error);
                }

                $result = $mysqli->query("SHOW DATABASES");
                $databases = [];

                if ($result) {
                    $all_dbs = [];
                    while ($row = $result->fetch_array()) {
                        $line = $row[0];
                        $all_dbs[] = $line;
                        // Filter database: sas* atau dnet* (backward compatibility) - CASE INSENSITIVE
                        if (stripos($line, 'sas_') === 0) {
                            $databases[] = $line;
                        }
                    }
                    $result->free();
                    // DEBUG: Log found databases to file
                    file_put_contents(__DIR__ . '/debug_dbs.txt', "Found: " . implode(", ", $all_dbs) . "\nFiltered: " . implode(", ", $databases));
                } else {
                    $err = $mysqli->error;
                    $mysqli->close();
                    throw new Exception("Gagal mengambil daftar database: " . $err);
                }
                $mysqli->close();

                $response = ['success' => true, 'databases' => $databases];
                break;

            // ========================
            // Aksi: Ambil Daftar Backup
            // ========================
            case 'get_backup_list':
                $files = glob(BACKUP_DIR . '/*.sql.gz');
                $backups = [];
                if ($files) {
                    foreach ($files as $file) {
                        $backups[] = [
                            'name' => basename($file),
                            'size' => filesize($file),
                            'date' => filemtime($file)
                        ];
                    }
                    // Urutkan berdasarkan tanggal, terbaru dulu
                    usort($backups, function ($a, $b) {
                        return $b['date'] - $a['date'];
                    });
                }
                $response = ['success' => true, 'backups' => $backups];
                break;

            // ========================
            // Aksi: Hapus File Backup
            // ========================
            case 'delete_backup':
                if (empty($file_to_delete)) {
                    throw new Exception("Nama file tidak boleh kosong.");
                }
                // Validasi keamanan: pastikan file ada di dalam BACKUP_DIR
                $file_path = realpath(BACKUP_DIR . '/' . $file_to_delete);
                if ($file_path && strpos($file_path, realpath(BACKUP_DIR)) === 0 && file_exists($file_path)) {
                    if (unlink($file_path)) {
                        $response = ['success' => true, 'message' => "File '" . $file_to_delete . "' berhasil dihapus."];
                    } else {
                        throw new Exception("Gagal menghapus file di server.");
                    }
                } else {
                    throw new Exception("File tidak ditemukan atau lokasi tidak valid.");
                }
                break;

            // ========================
            // Aksi: Buka Folder Backup
            // ========================
            case 'open_folder':
                $folder_path = realpath(BACKUP_DIR);
                if ($folder_path && is_dir($folder_path)) {
                    // Menggunakan exec lebih sederhana untuk memanggil explorer.exe langsung
                    // Note: Pada server produksi yang berjalan sebagai service, ini mungkin tidak menampilkan window GUI
                    // Tapi pada WAMP/XAMPP desktop env, biasanya berfungsi.
                    exec("explorer.exe " . escapeshellarg($folder_path));
                    $response = ['success' => true, 'message' => "Folder backup dibuka di server."];
                } else {
                    throw new Exception("Direktori backup tidak ditemukan.");
                }
                break;
        }

    } catch (Throwable $e) {
        $response = ['success' => false, 'message' => 'Error System: ' . $e->getMessage() . ' in ' . basename($e->getFile()) . ':' . $e->getLine()];
    }

    echo json_encode($response);
    exit; // Akhiri skrip PHP setelah menangani request AJAX
}

// ======================================================================\
// TAMPILAN HTML (UI)
// ======================================================================\
?>


<style>
    .blinking-text {
        animation-name: color-blink;
        animation-duration: 1s;
        /* Adjust duration for faster/slower blinking */
        animation-timing-function: linear;
        animation-iteration-count: infinite;
    }

    @keyframes color-blink {
        0% {
            color: white;
        }

        50% {
            color: red;
        }

        100% {
            color: white;
        }
    }

    /* CSS for Logs */
    pre {
        background: #f8f9fa;
        padding: 10px;
        border: 1px solid #ddd;
        border-radius: 4px;
        max-height: 300px;
        overflow-y: auto;
    }

    .log-info {
        color: #17a2b8;
    }

    .log-success {
        color: #28a745;
        font-weight: bold;
    }

    .log-warn {
        color: #ffc107;
    }

    .log-error {
        color: #dc3545;
        font-weight: bold;
    }

    /* CSS for Loading Overlay */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        z-index: 9999;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
</style>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Clone, Backup & Restore DB</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">BRD</li>
                    </ol>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-menu-gradient">

                        <p class="blinking-text">Jangan lupa Backup Database sebelum melakukan hal lain..!!!</p>
                    </div>
                    <!-- /.panel-heading -->

                    <!-- /.card-header -->
                    <div class="card-body">

                        <?php
                        // Tampilkan error jika direktori backup tidak dapat ditulis
                        if (!is_writable(BACKUP_DIR)) {
                            echo '<div class="alert alert-danger" role="alert">';
                            echo '<strong>Error Kritis:</strong> Direktori backup <code>' . htmlspecialchars(BACKUP_DIR) . '</code> tidak dapat ditulis. <br>';
                            echo 'Silakan periksa izin folder di server Anda. Backup dan Restore mungkin tidak berfungsi.';
                            echo '</div>';
                        }
                        ?>

                        <div class="card card-primary card-tabs">
                            <div class="card-header bg-menu-gradient p-0 pt-1">
                                <ul class="nav nav-tabs" id="custom-tabs-three-tab" role="tablist">
                                    <li class="nav-item">
                                        <a class="nav-link" id="dbset-tab-link" data-toggle="tab" href="#brd-dbset"
                                            role="tab" aria-controls="brd-dbset" aria-selected="false"><i
                                                class="las la-users-cog mr-1"></i> Set Database User</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" id="clone-tab-link" data-toggle="tab"
                                            href="#brd-clone" role="tab" aria-controls="brd-clone" aria-selected="true"
                                            title="Clone Database digunakan 1 kali setahun sebelum luluskan kelas 9. Setelah diclone login ulang menggunakan tahun Tapel terbaru kemudian baru luluskan kelas 9 dan Naikan kelas 7 & 8"><i
                                                class="las la-copy mr-1"></i> Kloning DB</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="backup-tab-link" data-toggle="tab" href="#brd-backup"
                                            role="tab" aria-controls="brd-backup" aria-selected="false"><i
                                                class="las la-save mr-1"></i> Backup DB</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" id="restore-tab-link" data-toggle="tab"
                                            href="#brd-restore" role="tab" aria-controls="brd-restore"
                                            aria-selected="false"><i class="las la-upload mr-1"></i> Restore DB</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="card-body">
                                <div class="tab-content" id="nav-tabContent">

                                    <!-- ==================== TAB SET DATABASE USER ==================== -->
                                    <div class="tab-pane fade" id="brd-dbset" role="tabpanel"
                                        aria-labelledby="dbset-tab-link">
                                        <div class="row">
                                            <div class="col-12">
                                                <h5>Pengaturan Database User</h5>
                                                <button type="button" class="btn btn-sm btn-info mb-2"
                                                    id="refreshDbSet">
                                                    <i class="las la-sync mr-1"></i> Refresh Data
                                                </button>
                                                <div>
                                                    <table class="table table-striped" style="width:100%">
                                                        <thead>
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Nama Database</th>
                                                                <th>Tahun</th>
                                                                <th>Status</th>
                                                                <th class="text-center">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="dbsetTableBody">
                                                            <!-- Data loaded via DataTables -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                                <div id="dbsetLogOutput" class="mt-2"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ==================== TAB KLONING ==================== -->
                                    <div class="tab-pane fade show active" id="brd-clone" role="tabpanel"
                                        aria-labelledby="clone-tab-link">
                                        <form id="cloneForm">
                                            <input type="hidden" name="action" value="clone">
                                            <div class="row">
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <label for="source_db_clone">Database Sumber</label>
                                                        <select class="form-control" id="source_db_clone"
                                                            name="source_db" required>
                                                            <option value="">Memuat...</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2 text-center align-self-center">
                                                    <i class="las la-arrow-right la-2x d-none d-md-block mt-3"></i>
                                                    <i class="las la-arrow-down la-2x d-md-none mb-2"></i>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <label for="target_db_clone">Database Tahun Selanjutnya =
                                                            sas_<?php echo $tahun_clone; ?></label>
                                                        <input type="text" class="form-control" id="target_db_clone"
                                                            name="target_db"
                                                            placeholder="sas_<?php echo $tahun_clone; ?>"
                                                            value="sas_<?php echo $tahun_clone; ?>" required>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" id="cloneButton" class="btn btn-primary"><i
                                                    class="las la-copy mr-1"></i> Mulai Kloning</button>
                                        </form>
                                        <hr>
                                        <label>Log Kloning:</label>
                                        <pre id="cloneLogOutput">Menunggu perintah kloning...</pre>
                                    </div>

                                    <!-- ==================== TAB BACKUP ==================== -->
                                    <div class="tab-pane fade" id="brd-backup" role="tabpanel"
                                        aria-labelledby="backup-tab-link">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h5>Buat Backup Baru</h5>
                                                <form id="backupForm">
                                                    <input type="hidden" name="action" value="backup">
                                                    <div class="form-group">
                                                        <label for="backup_db">Database yang akan di-Backup</label>
                                                        <select class="form-control" id="backup_db" name="backup_db"
                                                            required>
                                                            <option value="">Memuat...</option>
                                                        </select>
                                                    </div>
                                                    <button type="submit" id="backupButton" class="btn btn-success"><i
                                                            class="las la-save mr-1"></i> Buat Backup</button>
                                                </form>
                                                <hr>
                                                <label>Log Backup:</label>
                                                <pre id="backupLogOutput">Menunggu perintah backup...</pre>
                                            </div>
                                            <div class="col-md-6">
                                                <h5>Daftar File Backup (di Server)</h5>
                                                <button type="button" class="btn btn-sm btn-info mb-2"
                                                    id="refreshBackupList"><i class="las la-sync mr-1"></i> Refresh
                                                    Daftar</button>
                                                <button type="button" class="btn btn-sm btn-secondary mb-2 ml-1"
                                                    id="openBackupFolder"><i class="las la-folder-open mr-1"></i> Open
                                                    Folder</button>
                                                <div>
                                                    <table class="table table-sm table-bordered table-hover nowrap"
                                                        style="width:100%">
                                                        <thead class="bg-menu-gradient text-white">
                                                            <tr>
                                                                <th>Nama File</th>
                                                                <th>Ukuran</th>
                                                                <th class="text-center">Aksi</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="backupListTableBody">
                                                            <!-- Data loaded via DataTables -->
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ==================== TAB RESTORE ==================== -->
                                    <div class="tab-pane fade" id="brd-restore" role="tabpanel"
                                        aria-labelledby="restore-tab-link">
                                        <form id="restoreForm" enctype="multipart/form-data">
                                            <input type="hidden" name="action" value="restore">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="backup_file">Pilih File Backup (.sql atau
                                                            .sql.gz)</label>
                                                        <div class="custom-file">
                                                            <input type="file" class="custom-file-input"
                                                                id="backup_file" name="backup_file"
                                                                accept=".sql,.gz,.sql.gz" required>
                                                            <label class="custom-file-label" for="backup_file">Pilih
                                                                file...</label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label for="target_db_restore">Restore ke Database</label>
                                                        <input type="text" class="form-control" id="target_db_restore"
                                                            name="target_db"
                                                            placeholder="Contoh target Nama DB : sas_<?php echo $tahun; ?>"
                                                            required>
                                                        <small class="form-text text-muted">Database akan dibuat jika
                                                            belum ada.</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <button type="submit" id="restoreButton" class="btn btn-warning"><i
                                                    class="las la-upload mr-1"></i> Mulai Restore</button>
                                        </form>
                                        <hr>
                                        <label>Log Restore:</label>
                                        <pre id="logOutput">Menunggu file untuk di-restore...</pre>

                                        <!-- 
                            ======================================================================
                            BATAS PINDAH: BLOK SCRIPT SEBELUMNYA BERAKHIR DI SINI.
                            ======================================================================
                            -->

                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->
                            <div id="loadingOverlay" class="loading-overlay" style="display: none;">
                                <i class="las la-spinner la-spin la-3x"></i>
                            </div>
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
                <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->


<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>
<!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->


<!-- 
======================================================================
BLOK SCRIPT DIPINDAHKAN KE SINI
Ini adalah lokasi yang benar. Di luar struktur konten utama, 
tetapi masih di dalam body sehingga bisa dieksekusi setelah
semua elemen HTML di atasnya selesai di-parsing oleh browser.
======================================================================
-->
<script>
    // Pastikan jQuery sudah dimuat
    if (typeof $ === 'undefined') {
        console.error("jQuery tidak dimuat. Skrip tidak akan berjalan.");
    }

    // Debugging: Tangkap error global
    window.onerror = function (message, source, lineno, colno, error) {
        console.error("JS Error:", message, "at", lineno + ":" + colno);
        // alert("JS Error: " + message + "\nLine: " + lineno); // Uncomment jika perlu debug visual
    };

    // Seluruh skrip dibungkus dalam event DOMContentLoaded
    // Gunakan jQuery ready handler agar kompatibel dengan AJAX load
    $(function () {
        console.log("backup_restore.php script started");

        // ... variable definitions ...

        const cloneForm = document.getElementById('cloneForm');
        const cloneButton = document.getElementById('cloneButton');
        const targetDbInput = document.getElementById('target_db_clone'); // <--- ELEMEN BARU DIAMBIL
        const cloneLog = document.getElementById('cloneLogOutput');

        const backupForm = document.getElementById('backupForm');
        const backupButton = document.getElementById('backupButton');
        const backupLog = document.getElementById('backupLogOutput');

        const restoreForm = document.getElementById('restoreForm');
        const restoreButton = document.getElementById('restoreButton');
        const targetDbRestoreInput = document.getElementById('target_db_restore'); // <--- ELEMEN BARU
        const restoreLog = document.getElementById('logOutput');
        const backupFileInput = document.getElementById('backup_file');
        const backupFileLabel = document.querySelector('.custom-file-label[for="backup_file"]');

        const loadingOverlay = document.getElementById('loadingOverlay');

        const refreshBackupListButton = document.getElementById('refreshBackupList');

        const thisScriptUrl = '<?php echo htmlspecialchars(basename(__FILE__)); ?>';

        let dtDbSet = null;
        let dtBackup = null;
        let cachedDatabases = null; // Cache untuk daftar database

        // Inisialisasi DataTables secara global (Standardisasi)
        $('table.table').DataTable({
            scrollY: 450,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
            // fixedColumns:   {
            //     leftColumns: 3
            // }
        });

        // Ambil instance untuk manipulasi data
        dtDbSet = $('#brd-dbset table.table').DataTable();
        dtBackup = $('#brd-backup table.table').DataTable();

        // --- Fungsi Logging ---
        const logClone = (message, type = 'info') => logToEl(cloneLog, message, type);
        const logBackup = (message, type = 'info') => logToEl(backupLog, message, type);
        const logRestore = (message, type = 'info') => logToEl(restoreLog, message, type);

        function logToEl(el, message, type) {
            if (el.textContent.startsWith('Menunggu')) {
                el.innerHTML = ''; // Hapus pesan 'Menunggu...'
            }

            // Hindari pesan duplikat berurutan (terutama untuk warning validasi)
            const lastLog = el.lastElementChild;
            if (lastLog && lastLog.textContent.includes(message)) {
                return;
            }

            const timestamp = new Date().toLocaleTimeString();
            el.innerHTML += `<span class="log-${type}">[${timestamp}] ${escapeHTML(message)}</span>\n`;
            el.scrollTop = el.scrollHeight; // Auto-scroll ke bawah
        }

        // --- Fungsi Utilitas ---
        function showLoading(show = true) {
            loadingOverlay.style.display = show ? 'flex' : 'none';
        }

        function escapeHTML(str) {
            if (typeof str !== 'string') return '';
            return str.replace(/[&<>\"']/g, m => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '\"': '&quot;', "'": '&#039;'
            }[m]));
        }

        // --- Fungsi AJAX ---
        async function sendAjax(formData) {
            try {
                const response = await fetch(thisScriptUrl, {
                    method: 'POST',
                    body: formData
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error('Fetch error:', error);
                return { success: false, message: `Error koneksi atau parsing JSON: ${error.message}` };
            }
        }

        // --- Pengisian Dropdown Database ---
        async function populateDatabaseDropdowns() {
            logClone('Clone Database digunakan 1 kali setahun sebelum luluskan kelas 9. Setelah diclone login ulang menggunakan tahun Tapel terbaru kemudian baru luluskan kelas 9 dan Naikan kelas 7 & 8');
            logClone('Memuat daftar database...', 'info');
            logBackup('Memuat daftar database...', 'info');

            const databases = await getDatabases();
            const selects = document.querySelectorAll('#source_db_clone, #backup_db');

            selects.forEach(select => {
                select.innerHTML = '<option value="">Pilih database...</option>'; // Reset
                if (databases && databases.length > 0) {
                    databases.forEach(db => {
                        const option = document.createElement('option');
                        option.value = db;
                        option.textContent = db;
                        select.appendChild(option);
                    });
                    logClone('Daftar database berhasil dimuat.', 'success');
                    logBackup('Daftar database berhasil dimuat.', 'success');
                } else {
                    select.innerHTML = '<option value="">Gagal memuat / Tidak ada database</option>';
                    logClone('Gagal memuat daftar database atau daftar kosong.', 'error');
                    logBackup('Gagal memuat daftar database atau daftar kosong.', 'error');
                }
            });
        }

        async function getDatabases(forceRefresh = false) {
            if (cachedDatabases && !forceRefresh) {
                return cachedDatabases;
            }

            const formData = new FormData();
            formData.append('action', 'get_databases');
            const data = await sendAjax(formData);

            if (data.success && data.databases) {
                cachedDatabases = data.databases;
                return cachedDatabases;
            }
            return null;
        }

        // --- Validasi Target DB Dynamic ---
        async function checkTargetDb(inputEl, buttonEl, logFn) {
            const targetDbName = inputEl.value;
            const sourceDb = document.getElementById('source_db_clone') ? document.getElementById('source_db_clone').value : null;

            if (!targetDbName) {
                buttonEl.disabled = true;
                return;
            }

            // Cek jika sumber dan target sama (khusus tab clone)
            if (inputEl.id === 'target_db_clone' && sourceDb === targetDbName) {
                logFn(`PERINGATAN: Database sumber dan target sama (${targetDbName}).`, 'warn');
                buttonEl.innerHTML = '<i class="las la-ban mr-1"></i> Target Sama';
                buttonEl.classList.add('btn-secondary');
                buttonEl.classList.remove('btn-primary', 'btn-danger', 'btn-warning');
                buttonEl.disabled = true;
                return;
            }

            const databases = await getDatabases();

            if (databases) {
                const exists = databases.includes(targetDbName);
                if (exists) {
                    logFn(`PERINGATAN: Database target '${targetDbName}' sudah ada. Operasi akan menimpa data!`, 'warn');
                    buttonEl.innerHTML = buttonEl.id === 'cloneButton'
                        ? '<i class="las la-copy mr-1"></i> Mulai Kloning (Timpa)'
                        : '<i class="las la-upload mr-1"></i> Mulai Restore (Timpa)';

                    buttonEl.classList.remove('btn-primary', 'btn-secondary', 'btn-warning');
                    buttonEl.classList.add('btn-danger');
                } else {
                    buttonEl.innerHTML = buttonEl.id === 'cloneButton'
                        ? '<i class="las la-copy mr-1"></i> Mulai Kloning'
                        : '<i class="las la-upload mr-1"></i> Mulai Restore';

                    buttonEl.classList.remove('btn-danger', 'btn-secondary');
                    buttonEl.classList.add(buttonEl.id === 'cloneButton' ? 'btn-primary' : 'btn-warning');
                }
                buttonEl.disabled = false;
            }
        }

        if (targetDbInput) {
            targetDbInput.addEventListener('input', debounce(() => checkTargetDb(targetDbInput, cloneButton, logClone), 500));
        }

        if (targetDbRestoreInput) {
            targetDbRestoreInput.addEventListener('input', debounce(() => checkTargetDb(targetDbRestoreInput, restoreButton, logRestore), 500));
        }

        const sourceDbSelect = document.getElementById('source_db_clone');
        if (sourceDbSelect) {
            sourceDbSelect.addEventListener('change', () => checkTargetDb(targetDbInput, cloneButton, logClone));
        }

        function debounce(func, wait) {
            let timeout;
            return function (...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        // --- Fungsi Tab Backup ---
        async function loadBackupList() {
            const formData = new FormData();
            formData.append('action', 'get_backup_list');

            const data = await sendAjax(formData);
            dtBackup.clear();

            if (data.success && data.backups) {
                data.backups.forEach(file => {
                    const sizeMB = (file.size / 1024 / 1024).toFixed(2);
                    const fileDate = new Date(file.date * 1000).toLocaleString('id-ID');

                    dtBackup.row.add([
                        `<strong>${escapeHTML(file.name)}</strong><br><small class="text-muted">${fileDate}</small>`,
                        `<span class="badge bg-light text-dark border">${sizeMB} MB</span>`,
                        `<div class="text-center">
                            <a href="${thisScriptUrl}?download=${encodeURIComponent(file.name)}" class="btn btn-xs btn-success shadow-sm" download title="Download">
                                <i class="las la-download"></i>
                            </a>
                            <button class="btn btn-xs btn-danger shadow-sm delete-backup-btn" data-file="${escapeHTML(file.name)}" title="Hapus">
                                <i class="las la-trash"></i>
                            </button>
                        </div>`
                    ]);
                });
                dtBackup.draw();
            } else {
                logBackup(data.message || 'Gagal memuat daftar backup', 'error');
            }
        }

        // --- Event Listener untuk Hapus Backup ---
        $('#brd-backup table.table').on('click', '.delete-backup-btn', async function (e) {
            e.preventDefault();
            const fileName = $(this).data('file');
            if (!confirm(`Apakah Anda yakin ingin menghapus file "${fileName}"?`)) {
                return;
            }

            showLoading(true);
            logBackup(`Menghapus file ${fileName}...`, 'warn');

            const formData = new FormData();
            formData.append('action', 'delete_backup');
            formData.append('file_to_delete', fileName);

            const data = await sendAjax(formData);

            if (data.success) {
                logBackup(data.message, 'success');
                loadBackupList(); // Muat ulang daftar setelah hapus
            } else {
                logBackup(data.message, 'error');
            }
            showLoading(false);
        });

        // --- Event Listener Refresh Daftar Backup ---
        refreshBackupListButton.addEventListener('click', loadBackupList);

        // --- Event Listener Buka Folder Backup ---
        const openBackupFolderButton = document.getElementById('openBackupFolder');
        if (openBackupFolderButton) {
            openBackupFolderButton.addEventListener('click', async () => {
                const formData = new FormData();
                formData.append('action', 'open_folder');

                // Tidak perlu showLoading karena prosesnya cepat dan asynchronous (fire & forget)
                const data = await sendAjax(formData);

                if (data.success) {
                    logBackup(data.message, 'success');
                } else {
                    logBackup(data.message, 'error');
                }
            });
        }

        // --- Event Listener untuk link tab ---
        $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
            const targetTab = $(e.target).attr("href"); // Tab yang baru aktif
            if (targetTab === '#brd-backup') {
                loadBackupList();
            }
        });


        // --- Event Handler: Kloning ---
        if (cloneForm) {
            cloneForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (cloneButton.disabled) return;

                const sourceDb = document.getElementById('source_db_clone').value;
                const targetDb = document.getElementById('target_db_clone').value;

                if (!sourceDb || !targetDb) {
                    logClone('Harap pilih database sumber dan isi nama database target.', 'error');
                    return;
                }
                if (sourceDb === targetDb) {
                    logClone('Database sumber dan target tidak boleh sama.', 'error');
                    return;
                }
                if (!confirm(`Yakin ingin mengkloning '${sourceDb}' ke '${targetDb}'? \nJIKA '${targetDb}' SUDAH ADA, ISINYA AKAN DITIMPA!`)) {
                    return;
                }

                cloneButton.disabled = true;
                cloneButton.innerHTML = '<i class="las la-spinner la-spin mr-1"></i> Mengkloning...';
                showLoading(true);
                logClone(`Memulai kloning dari '${sourceDb}' ke '${targetDb}'...`, 'info');

                const formData = new FormData(cloneForm);
                const data = await sendAjax(formData);

                if (data.success) {
                    logClone(data.message, 'success');
                } else {
                    logClone(data.message || 'Terjadi error yang tidak diketahui.', 'error');
                }

                cloneButton.disabled = false;
                cloneButton.innerHTML = '<i class="las la-copy mr-1"></i> Mulai Kloning';
                showLoading(false);
            });
        }

        // --- Event Handler: Backup ---
        if (backupForm) {
            backupForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (backupButton.disabled) return;

                const backupDb = document.getElementById('backup_db').value;
                if (!backupDb) {
                    logBackup('Harap pilih database untuk di-backup.', 'error');
                    return;
                }
                if (!confirm(`Yakin ingin mem-backup database '${backupDb}'?`)) {
                    return;
                }

                backupButton.disabled = true;
                backupButton.innerHTML = '<i class="las la-spinner la-spin mr-1"></i> Membackup...';
                showLoading(true);
                logBackup(`Memulai backup database '${backupDb}'...`, 'info');

                const formData = new FormData(backupForm);
                const data = await sendAjax(formData);

                if (data.success) {
                    logBackup(data.message, 'success');
                    loadBackupList(); // Muat ulang daftar file backup
                } else {
                    logBackup(data.message || 'Terjadi error yang tidak diketahui.', 'error');
                }

                backupButton.disabled = false;
                backupButton.innerHTML = '<i class="las la-save mr-1"></i> Buat Backup';
                showLoading(false);
            });
        }

        // --- Event Handler: Restore ---
        if (restoreForm) {
            // Update label file input
            backupFileInput.addEventListener('change', () => {
                const fileName = backupFileInput.files.length > 0 ? backupFileInput.files[0].name : 'Pilih file...';
                backupFileLabel.textContent = fileName;
            });

            restoreForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                if (restoreButton.disabled) return;

                const targetDb = document.getElementById('target_db_restore').value;
                if (backupFileInput.files.length === 0) {
                    logRestore('Harap pilih file backup.', 'error');
                    return;
                }
                if (!targetDb) {
                    logRestore('Harap isi nama database target.', 'error');
                    return;
                }
                if (!confirm(`Yakin ingin me-restore file ini ke database '${targetDb}'? \nPERINGATAN: DATA YANG ADA DI '${targetDb}' AKAN DITIMPA!`)) {
                    return;
                }

                restoreButton.disabled = true;
                restoreButton.innerHTML = '<i class="las la-spinner la-spin mr-1"></i> Merestore...';
                showLoading(true);
                logRestore(`Mengunggah file dan memulai restore ke '${targetDb}'...`, 'info');

                const formData = new FormData(restoreForm);

                // Kita tidak bisa pakai sendAjax biasa karena perlu kirim file
                // jadi kita pakai fetch langsung di sini
                fetch(thisScriptUrl, {
                    method: 'POST',
                    body: formData
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            logRestore(data.message, 'success');
                        } else {
                            logRestore(data.message || 'Terjadi error yang tidak diketahui.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Restore error:', error);
                        logRestore(`Error saat restore: ${error.message}`, 'error');
                    })
                    .finally(() => {
                        restoreButton.disabled = false;
                        restoreButton.innerHTML = '<i class="las la-upload mr-1"></i> Mulai Restore';
                        showLoading(false);
                        // Reset form
                        restoreForm.reset();
                        backupFileLabel.textContent = 'Pilih file...';
                    });
            });
        }

        // --- Fungsi Tab Set Database User ---
        const dbsetTableBody = document.getElementById('dbsetTableBody');
        const refreshDbSetButton = document.getElementById('refreshDbSet');
        const dbsetLog = document.getElementById('dbsetLogOutput');

        const logDbSet = (message, type = 'info') => {
            dbsetLog.innerHTML = `<div class="alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show" role="alert">
                ${escapeHTML(message)}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>`;
        };

        async function loadDbSet() {
            const formData = new FormData();
            formData.append('action', 'get_dbset');

            const data = await sendAjax(formData);
            dtDbSet.clear();

            if (data.success && data.data) {
                data.data.forEach(row => {
                    const isAktif = row.aktif == 1;
                    const statusBadge = isAktif
                        ? '<span class="badge badge-success badge-pill px-3">Aktif</span>'
                        : '<span class="badge badge-secondary badge-pill px-3">Tidak Aktif</span>';

                    const btnClass = isAktif ? 'btn-success' : 'btn-outline-primary';
                    const btnText = isAktif ? '<i class="las la-check-circle"></i> Selected' : '<i class="las la-power-off"></i> Set Aktif';
                    const nextStatus = isAktif ? 0 : 1;

                    dtDbSet.row.add([
                        row.id,
                        `<span class="font-weight-bold text-primary">${escapeHTML(row.dbname)}</span>`,
                        row.tahun,
                        statusBadge,
                        `<div class="text-center">
                            <button class="btn btn-sm ${btnClass} shadow-sm toggle-aktif-btn" 
                                data-id="${row.id}" 
                                data-next-status="${nextStatus}">
                                ${btnText}
                            </button>
                        </div>`
                    ]);
                });
                dtDbSet.draw();
            } else {
                logDbSet(data.message || 'Gagal memuat data dbset', 'error');
            }
        }

        // Event Listener untuk tombol toggle aktif
        $('#brd-dbset table.table').on('click', '.toggle-aktif-btn', async function (e) {
            e.preventDefault();
            const id = $(this).data('id');
            const nextStatus = $(this).data('nextStatus');

            // Dialog Peringatan dan Input Password
            const password = prompt("PERINGATAN: Mengubah database aktif akan mempengaruhi seluruh sistem.\n\nMasukkan password Anda untuk konfirmasi:");

            if (password === null) {
                return; // User cancel
            }
            if (password.trim() === "") {
                alert("Password tidak boleh kosong.");
                return;
            }

            showLoading(true);
            const formData = new FormData();
            formData.append('action', 'update_dbset_status');
            formData.append('id', id);
            formData.append('aktif', nextStatus);
            formData.append('password', password);

            const data = await sendAjax(formData);

            if (data.success) {
                logDbSet(data.message, 'success');
                loadDbSet(); // Reload data
            } else {
                logDbSet(data.message, 'error');
            }
            showLoading(false);
        });

        // Refresh button
        if (refreshDbSetButton) {
            refreshDbSetButton.addEventListener('click', loadDbSet);
        }

        // Load data when tab is shown
        $('a[href="#brd-dbset"]').on('shown.bs.tab', function (e) {
            loadDbSet();
        });

        // Load on initial page load if it's the active tab (optional, but good practice)
        const dbSetTabLink = document.querySelector('a[href="#brd-dbset"]');
        if (dbSetTabLink && dbSetTabLink.classList.contains('active')) {
            loadDbSet();
        }

        // --- Inisialisasi ---
        populateDatabaseDropdowns().then(() => {
            if (targetDbInput) {
                checkTargetDb(targetDbInput, cloneButton, logClone); // Periksa status target DB setelah dropdown dimuat
            }
        });

    });
</script>