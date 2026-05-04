<?php
// 1. Sertakan koneksi database
include "dbconn.php";
include "config/secure.php";
// Alias $conn ke $sqlconn because secure.php uses $sqlconn
$sqlconn = $conn;

// Compatibility: Mapping session userid ke skradm jika belum ada
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (isset($_SESSION['userid']) && !isset($_SESSION['skradm'])) {
  $_SESSION['skradm'] = $_SESSION['userid'];
}
// 2. Sertakan file keamanan.

// 3. AMBIL DATA TERBARU DARI DATABASE (Sinkronisasi Real-time)
$id_user_sess = $_SESSION['id'] ?? $_SESSION['user_id'] ?? 0;
$data_user = [];

if ($conn) {
  $query_user = mysqli_query($conn, "SELECT * FROM tb_user WHERE id = '$id_user_sess'");
  if ($query_user) {
    $data_user = mysqli_fetch_array($query_user);
  }
}

$nuser = $data_user['nama'] ?? $_SESSION['nama'] ?? 'User';
$poto_db = $data_user['poto'] ?? '';
$lv = $data_user['level'] ?? $_SESSION['level'] ?? '';


/* * ==========================================
 * 4. LOGIKA NAVIGASI (PAGE CONTROLLER) - DIPINDAHKAN KE ATAS
 * ==========================================
 */
// --- Logika Navigasi ---
if (isset($_GET['dashboard'])) {
  $current_page = 'dashboard';
  $page_to_include = 'load.php';
} else if (isset($_GET['user'])) {
  $current_page = 'user';
  $page_to_include = 'user.php';
} else if (isset($_GET['suratkeluar'])) {
  $current_page = 'suratkeluar';
  $page_to_include = 'suratkeluar.php';
} else if (isset($_GET['suratmasuk'])) {
  $current_page = 'suratmasuk';
  $page_to_include = 'suratmasuk.php';
} else if (isset($_GET['suratedaran'])) {
  $current_page = 'suratedaran';
  $page_to_include = 'suratedaran.php';
} else if (isset($_GET['suratkeputusan'])) {
  $current_page = 'suratkeputusan';
  $page_to_include = 'suratkeputusan.php';
} else if (isset($_GET['profil'])) {
  $current_page = 'profil';
  $page_to_include = 'profil.php';
} else if (isset($_GET['backup_restore'])) {
  $current_page = 'backup_restore';
  $page_to_include = 'backup_restore.php';
} else if (isset($_GET['update'])) {
  $current_page = 'update';
  $page_to_include = 'chckupdate.php';
} else if (isset($_GET['pegawai'])) {
  $current_page = 'pegawai';
  $page_to_include = 'kepegawaian/pegawai.php';
} else if (isset($_GET['kepegawaian_dashboard'])) {
  $current_page = 'kepegawaian_dashboard';
  $page_to_include = 'kepegawaian/dashboard.php';
} else if (isset($_GET['kepegawaian_dashboard_guru'])) {
  $current_page = 'kepegawaian_dashboard_guru';
  $page_to_include = 'kepegawaian/dashboard_guru.php';
} else if (isset($_GET['kepegawaian_biodata_guru'])) {
  $current_page = 'kepegawaian_biodata_guru';
  $page_to_include = 'kepegawaian/pegawai.php';
} else {
  // --- DEFAULT DASHBOARD BY LEVEL ---
  if ($lv == '4') {
    header("Location: kepegawaian/index_ptk.php");
    exit;
  } else {
    $current_page = 'dashboard';
    $page_to_include = 'load.php';
  }
}

// Helper untuk mengecek menu Arsip (Treeview)
$menu_arsip = ['surmas', 'surkel', 'sukep', 'sured'];
$is_arsip_active = in_array($current_page, $menu_arsip);

// --- 5. LOGIKA FORCE CHANGE PASSWORD ---
$force_change_pass = false;
if (isset($data_user['userid']) && isset($data_user['password'])) {
  // Cek apakah password saat ini cocok dengan userid (Default Password)
  if (password_verify($data_user['userid'], $data_user['password'])) {
    $force_change_pass = true;
  }
}

// Jika harus ganti password tapi tidak sedang di halaman profil
if ($force_change_pass && $current_page != 'profil') {
  // echo "<script>window.location.href='?page=profil&changepass=true';</script>"; // OPSI LAMA: Redirect
  // exit;
}

// OPSI BARU (STRICT MODE): Override Content
if ($force_change_pass) {
  $page_to_include = 'ganti_password_awal.php';
}

?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE:edge">
  <title>Staff Surat | <?php echo ucfirst($current_page); ?> </title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <link rel="stylesheet"
    href="https://maxst.icons8.com/vue-static/landings/line-awesome/line-awesome/1.3.0/css/line-awesome.min.css">

  <link rel="stylesheet" href="plugins/css/bootstrap-extended.min.css">
  <link rel="stylesheet" href="plugins/css/colors.min.css">
  <link rel="stylesheet" href="plugins/css/palette-gradient.min.css">
  <link rel="stylesheet" href="plugins/css/datatables.min.css">
  <link rel="stylesheet" href="custom.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed" style="height: auto;">
  <div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand bg-menu-gradient">
      <!-- Left navbar links -->
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="las la-bars"></i></a>
        </li>
      </ul>
      <li class="nav-item d-none d-sm-inline-block">
        Home </li>
      </ul>

      <ul class="navbar-nav ml-auto">
        <li class="nav-item">
          <script type='text/javascript'>

            var months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            var myDays = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jum&#39;at', 'Sabtu'];
            var date = new Date();
            var day = date.getDate();
            var month = date.getMonth();
            var thisDay = date.getDay(),
              thisDay = myDays[thisDay];
            var yy = date.getYear();
            var year = (yy < 1000) ? yy + 1900 : yy;
            document.write(thisDay + ', ' + day + ' ' + months[month] + ' ' + year + ',');
          </script>
          <time id="clock"></time>

          <script>
            (function () {
              var clock = document.getElementById('clock');
              setInterval(function () {
                var time = new Date().toString().split(' ')[4];
                clock.innerHTML = time;
              }, 13);
            })();
          </script>
      </ul>
    </nav>

    <!-- Sidebar -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a href="?" class="brand-link d-flex flex-column align-items-center text-center py-3">
        <img src="images/logosurat.png" alt="smpn171" class="brand-image img-circle elevation-3 mb-2"
          style="opacity: .7; float: none; margin-left: 0;">
        <span class="brand-text font-weight-light">Sistem Arsip Persuratan</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- User Panel -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
          <div class="image">
            <?php
            $folder_foto = "file/profil/";
            $path_file_server = $folder_foto . $poto_db;
            if (!empty($poto_db) && file_exists($path_file_server)) {
              $src_foto = $path_file_server;
            } else {
              $src_foto = "images/default.png";
            }
            ?>
            <img src="<?php echo $src_foto; ?>" class="img-circle elevation-2" alt="Photo"
              onerror="this.onerror=null; this.src='images/male.png';">
          </div>
          <div class="info">
            <?php
            if ($nuser !== "") {
              echo "<a class='d-block'>$nuser</a>";
            } else {
              echo "<a class='d-block'>Error</a>";
            }
            ?>
          </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

            <?php if (!$force_change_pass): // HIDE MENU IF FORCED CHANGE PASS ?>

              <!------------------------------------------ MENU ADMIN SURAT (LEVEL 1) ---------------------------------->
              <?php if ($lv == "1") { ?>

                <li class="nav-item">
                  <a href="?user" class="nav-link <?php echo ($current_page == 'user') ? 'active' : ''; ?>">
                    <i class="las la-user nav-icon" style="font-size:20px;"></i>
                    <p>Manajemen User</p>
                  </a>
                </li>

                <li class="nav-item">
                  <a href="kepegawaian/" target=_blank class="nav-link">
                    <i class="nav-icon las la-clipboard-check"></i>
                    <p>
                      Kepegawaian
                    </p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="?profil"
                    class="nav-link <?php echo ($current_page == 'profil' || $current_page == 'profile') ? 'active' : ''; ?>">
                    <i class="nav-icon las la-id-badge"></i>
                    <p>Profil</p>
                  </a>
                </li>
                <li class="nav-item has-treeview">
                  <a href="#" class="nav-link">
                    <i class="nav-icon las la-server"></i>
                    <p>
                      System
                      <i class="las la-angle-left right"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <a href="?backup_restore"
                        class="nav-link <?php echo ($current_page == 'backup_restore') ? 'active' : ''; ?>">
                        <i class="nav-icon las la-database"></i>
                        <p>Backup / Restore</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="?update" class="nav-link <?php echo ($current_page == 'update') ? 'active' : ''; ?>">
                        <i class="nav-icon las la-arrow-circle-up"></i>
                        <p>Update</p>
                      </a>
                    </li>
                  </ul>
                </li>

                <!-- Treeview Arsip Surat -->
                <li class="nav-item has-treeview <?php echo $is_arsip_active ? 'menu-open' : ''; ?>">
                  <a href="#" class="nav-link <?php echo $is_arsip_active ? 'active' : ''; ?>">
                    <i class="nav-icon las la-envelope"></i>
                    <p>
                      Arsip Surat
                      <i class="las la-angle-left right"></i>
                    </p>
                  </a>
                  <ul class="nav nav-treeview">
                    <li class="nav-item">
                      <a href="?suratmasuk" class="nav-link <?php echo ($current_page == 'suratmasuk') ? 'active' : ''; ?>">
                        <i class="nav-icon las la-file-upload"></i>
                        <p>Surat Masuk</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="?suratkeluar"
                        class="nav-link <?php echo ($current_page == 'suratkeluar') ? 'active' : ''; ?>">
                        <i class="nav-icon las la-file-download"></i>
                        <p>Surat Keluar</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="?suratkeputusan"
                        class="nav-link <?php echo ($current_page == 'suratkeputusan') ? 'active' : ''; ?>">
                        <i class="nav-icon las la-certificate"></i>
                        <p>Surat Keputusan</p>
                      </a>
                    </li>
                    <li class="nav-item">
                      <a href="?suratedaran"
                        class="nav-link <?php echo ($current_page == 'suratedaran') ? 'active' : ''; ?>">
                        <i class="nav-icon las la-bullhorn"></i>
                        <p>Surat Edaran</p>
                      </a>
                    </li>
                  </ul>
                </li>
              <?php } ?>

              <!------------------------------------------ MENU USER (LEVEL 2) ----------------------------------------->
            <?php if ($lv == "2" || $lv == "3") { ?>

            <li class="nav-item">
              <a href="?dashboard" class="nav-link <?php echo ($current_page == 'dashboard') ? 'active' : ''; ?>">
                <i class="nav-icon las la-tachometer-alt"></i>
                <p>Dashboard</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="?profil"
                class="nav-link <?php echo ($current_page == 'profil' || $current_page == 'profile') ? 'active' : ''; ?>">
                <i class="nav-icon las la-id-badge"></i>
                <p>Profil</p>
              </a>
            </li>
            <li class="nav-item has-treeview <?php echo $is_arsip_active ? 'menu-open' : ''; ?>">
              <a href="#" class="nav-link <?php echo $is_arsip_active ? 'active' : ''; ?>">
                <i class="nav-icon las la-envelope"></i>
                <p>
                  Arsip Surat
                  <i class="las la-angle-left right"></i>
                </p>
              </a>
              <ul class="nav nav-treeview">
                <li class="nav-item">
                  <a href="?suratmasuk" class="nav-link <?php echo ($current_page == 'suratmasuk') ? 'active' : ''; ?>">
                    <i class="nav-icon las la-file-upload"></i>
                    <p>Surat Masuk</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="?suratkeluar"
                    class="nav-link <?php echo ($current_page == 'suratkeluar') ? 'active' : ''; ?>">
                    <i class="nav-icon las la-file-download"></i>
                    <p>Surat Keluar</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="?suratkeputusan"
                    class="nav-link <?php echo ($current_page == 'suratkeputusan') ? 'active' : ''; ?>">
                    <i class="nav-icon las la-certificate"></i>
                    <p>Surat Keputusan</p>
                  </a>
                </li>
                <li class="nav-item">
                  <a href="?suratedaran"
                    class="nav-link <?php echo ($current_page == 'suratedaran') ? 'active' : ''; ?>">
                    <i class="nav-icon las la-bullhorn"></i>
                    <p>Surat Edaran</p>
                  </a>
                </li>
              </ul>
            </li>
            <?php } ?>

            <!------------------------------------------ MENU GURU (LEVEL 4) ----------------------------------------->
            <?php if ($lv == "4") { ?>
            <li class="nav-item">
              <a href="?kepegawaian_dashboard_guru"
                class="nav-link <?php echo ($current_page == 'kepegawaian_dashboard_guru') ? 'active' : ''; ?>">
                <i class="nav-icon las la-tachometer-alt"></i>
                <p>Dashboard Guru</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="?kepegawaian_biodata_guru"
                class="nav-link <?php echo ($current_page == 'kepegawaian_biodata_guru') ? 'active' : ''; ?>">
                <i class="nav-icon las la-id-card"></i>
                <p>Biodata Mandiri</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="?profil" class="nav-link <?php echo ($current_page == 'profil') ? 'active' : ''; ?>">
                <i class="nav-icon las la-id-badge"></i>
                <p>Profil Akun</p>
              </a>
            </li>
            <?php } ?>

            <?php endif; // END IF (!force_change_pass) ?>

            <li class="nav-item">
              <a href="exit.php" class="nav-link" id=btn1>
                <i class="nav-icon las la-sign-out-alt"></i>
                <p>Exit</p>
              </a>
            </li>
          </ul>
          <br>
          <center><i class="las la-database text-warning"> Database :
              <?php echo $db ?? $db_master; ?>
            </i></center>
          <br />
        </nav>
        <!-- /.sidebar-menu -->
      </div>
    </aside>



    <!-- Main content -->
    <!-- section class="content" removed because included files have content-wrapper -->
    <!-- div class="container-fluid" removed -->
    <?php
    if (file_exists($page_to_include)) {
      include $page_to_include;
    } else {
      echo "
                <div class='content-wrapper'>
                    <section class='content'>
                        <div class='alert alert-danger shadow-sm'>
                            <h5 class='alert-heading'><i class='icon las la-ban'></i> 404 Page Not Found</h5>
                            Halaman '{$current_page}' belum tersedia di server.
                        </div>
                    </section>
                </div>";
    }
    ?>
    <!-- /div -->
    <!-- /section -->
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <footer class="main-footer">
    <center><strong> S.A.P <?php echo $ver; ?> - Copyright &copy; 2025 </strong></center>
  </footer>
  </div>


  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/fixedcolumns/3.2.6/js/dataTables.fixedColumns.min.js"></script>
  <script src="plugins/admin-lte/adminlte.min.js"></script>




</body>

</html>