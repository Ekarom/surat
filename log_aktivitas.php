<?php
// log_aktivitas.php
if (!isset($conn)) {
    include "dbconn.php";
}

// Proteksi: Admin (1) dan Staff (2) bisa akses
$lv_sess = $_SESSION['level'] ?? '';
if ($lv_sess != '1' && $lv_sess != '2' && $lv_sess != '4') {
    echo "
    <div class='content-wrapper'>
        <section class='content-header'>
            <div class='container-fluid'>
                <div class='alert alert-danger shadow-sm'>
                    <h5 class='alert-heading'><i class='icon las la-ban'></i> Akses Ditolak</h5>
                    Anda tidak memiliki izin untuk mengakses halaman ini.
                </div>
            </div>
        </section>
    </div>";
    return;
}

// Ambil data log sesuai level
$userid_sess = $_SESSION['userid'] ?? '';
if ($lv_sess == '1') {
    // Admin melihat semua log
    $query = "SELECT * FROM tb_activity_log ORDER BY waktu DESC LIMIT 2000";
} else {
    // User lain hanya melihat log miliknya sendiri
    $query = "SELECT * FROM tb_activity_log WHERE userid = '$userid_sess' ORDER BY waktu DESC LIMIT 1000";
}
$result = ($conn) ? $conn->query($query) : false;
?>

<div class="content-wrapper">
    <!-- Content Header -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 text-dark">
                        <i class="las la-history mr-2 text-primary"></i>Log Aktivitas
                    </h1>
                </div>
                <div class="col-sm-6 text-right">
                   <?php if ($lv_sess == '1') : ?>
                   <button type="button" class="btn btn-outline-danger btn-sm shadow-sm" id="btnHapusLog">
                       <i class="las la-trash-alt mr-1"></i>Bersihkan Log Lama
                   </button>
                   <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table id="tableLog" class="table table-bordered table-striped table-hover align-middle mb-0" style="width:100%">
                            <thead class="bg-menu-gradient text-white">
                                <tr>
                                    <th width="50" class="text-center">No</th>
                                    <th width="150" class="text-center">Waktu</th>
                                    <th>User</th>
                                    <th class="text-center">Modul</th>
                                    <th class="text-center">Aksi</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">IP Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if ($result && $result->num_rows > 0) {
                                    $no = 1;
                                    while ($row = $result->fetch_assoc()) {
                                        $aksi_badge = 'secondary';
                                        $aksi = $row['aksi'] ?? '';
                                        
                                        if($aksi == 'Tambah') $aksi_badge = 'success';
                                        elseif($aksi == 'Edit' || $aksi == 'Update') $aksi_badge = 'info';
                                        elseif($aksi == 'Hapus' || $aksi == 'Bersihkan Log') $aksi_badge = 'danger';
                                        elseif($aksi == 'Login' || $aksi == 'Logout') $aksi_badge = 'primary';

                                        $waktu_log = !empty($row['waktu']) ? date('d/m/Y H:i:s', strtotime($row['waktu'])) : '-';

                                        echo "<tr>
                                            <td class='text-center'>{$no}</td>
                                            <td class='text-center small'>{$waktu_log}</td>
                                            <td>
                                                <div class='font-weight-bold'>".htmlspecialchars($row['userid'] ?? '')."</div>
                                                <div class='small text-muted'>".htmlspecialchars($row['nama'] ?? '')."</div>
                                            </td>
                                            <td class='text-center'>
                                                <span class='badge badge-light border rounded-pill px-3'>".htmlspecialchars($row['modul'] ?? '')."</span>
                                            </td>
                                            <td class='text-center'>
                                                <span class='badge badge-{$aksi_badge} rounded-pill px-3'>".htmlspecialchars($aksi)."</span>
                                            </td>
                                            <td>".htmlspecialchars($row['info'] ?? '')."</td>
                                            <td class='text-center small'>".htmlspecialchars($row['ip'] ?? '')."</td>
                                        </tr>";
                                        $no++;
                                    }
                                } else {
                                    echo "<tr><td colspan='7' class='text-center text-muted py-4'>
                                        <i class='las la-info-circle fs-2 d-block mb-2 text-warning'></i>
                                        Belum ada rekaman aktivitas pada tabel baru.
                                    </td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    $('#tableLog').DataTable({
        "order": [[ 1, "desc" ]],
        "pageLength": 25,
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.19/i18n/Indonesian.json"
        }
    });

    $('#btnHapusLog').on('click', function() {
        if(confirm('Apakah Anda yakin ingin menghapus log aktivitas yang lebih lama dari 30 hari?')) {
            $.post('proses_user.php', {action: 'bersihkan_log'}, function(response) {
                if(response.status == 'success') {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Gagal membersihkan log: ' + response.message);
                }
            }, 'json');
        }
    });
});
</script>
