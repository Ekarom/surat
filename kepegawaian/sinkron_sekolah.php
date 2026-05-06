<?php
/**
 * Page Khusus Sinkronisasi Data Sekolah
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

// 1. Fetch current local data
$query = "SELECT nsekolah, npsn, alamat, updated_at FROM profils WHERE id = 1";
$res = $conn->query($query);
$current = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;

// 2. Fetch master data for preview
$db_master_name = $db_master ?? "sas_";
$conn_master = @new mysqli($host, $user, $pass, $db_master_name);
if ($conn_master->connect_error) {
    // If master db fails, try db_initial as fallback
    $db_master_name = isset($db_initial) ? $db_initial : $db;
    $conn_master = @new mysqli($host, $user, $pass, $db_master_name);
}

$master = null;
if (!$conn_master->connect_error) {
    $res_m = $conn_master->query("SELECT nsekolah, npsn, alamat FROM profils WHERE id = 1");
    $master = ($res_m && $res_m->num_rows > 0) ? $res_m->fetch_assoc() : null;
    $conn_master->close();
}
?>

<style>
    .sync-wrapper {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #00bcd4;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .sync-header {
        background: #00bcd4;
        color: #fff;
        padding: 10px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .sync-header h5 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .source-badge {
        background: #fff;
        color: #333;
        padding: 2px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .mapping-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 15px;
        text-align: left;
    }

    .mapping-label {
        font-size: 0.8rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 5px;
    }

    .mapping-detail {
        font-size: 0.8rem;
        color: #64748b;
    }

    .mapping-detail b {
        color: #e91e63;
    }

    .data-count {
        font-size: 5rem;
        font-weight: 800;
        color: #00bcd4;
        line-height: 1;
    }

    .btn-tarik {
        background: #00bcd4;
        color: #fff;
        border: none;
        padding: 12px;
        border-radius: 8px;
        width: 100%;
        font-weight: 700;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: all 0.2s ease;
        box-shadow: 0 4px 10px rgba(0, 188, 212, 0.3);
    }

    .btn-tarik:hover {
        background: #00acc1;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(0, 188, 212, 0.4);
        color: #fff;
    }
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="sync-wrapper">
                <div class="sync-header">
                    <h5><i class="las la-school"></i> Sinkron Data Sekolah (Tabel Profils)</h5>
                    <div class="source-badge">Sumber: <?php echo $db_master_name; ?></div>
                </div>

                <div class="p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <!-- Left: Status & Count -->
                        <div class="col-md-6 text-center border-end">
                            <div class="text-muted small fw-bold text-uppercase mb-3">Data Profil di Database</div>
                            <div class="data-count mb-2">1</div>
                            <div class="small text-muted">ID Record: 1 (Profil Sekolah Utama)</div>
                        </div>

                        <!-- Right: Mapping & Action -->
                        <div class="col-md-6">
                            <div class="mapping-card mb-4">
                                <div class="mapping-label">Mapping Data:</div>
                                <div class="mapping-detail mb-1">Sumber: <b>profils</b> (Semua Kolom)</div>
                                <div class="mapping-detail">Target: <b>profils</b> (Auto-sync & Auto-add columns)
                                </div>
                            </div>

                            <button id="btnStartSync" class="btn btn-tarik">
                                <i class="las la-sync-alt fs-5"></i> TARIK DATA SEKOLAH
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Detail Comparisons -->
            <div class="row mt-4 g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 15px;">
                        <div class="text-muted small fw-bold text-uppercase mb-3"><i
                                class="las la-map-pin me-1"></i> Data Lokal Saat Ini</div>
                        <?php if ($current): ?>
                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($current['nsekolah']); ?></h5>
                            <div class="small text-muted"><?php echo htmlspecialchars($current['alamat']); ?></div>
                        <?php else: ?>
                            <div class="alert alert-warning py-2 small">Data belum tersedia di modul ini.</div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 15px;">
                        <div class="text-muted small fw-bold text-uppercase mb-3"><i
                                class="las la-database me-1"></i> Data Master (Siap Ditarik)</div>
                        <?php if ($master): ?>
                            <h5 class="fw-bold text-primary mb-1"><?php echo htmlspecialchars($master['nsekolah']); ?>
                            </h5>
                            <div class="small text-muted"><?php echo htmlspecialchars($master['alamat']); ?></div>
                        <?php else: ?>
                            <div class="alert alert-danger py-2 small">Gagal terhubung ke database master.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#btnStartSync').on('click', function () {
            const btn = $(this);
            const originalHtml = btn.html();

            Swal.fire({
                title: 'Konfirmasi Tarik Data',
                text: "Seluruh data profil sekolah di modul ini akan diperbarui sesuai database pusat. Lanjutkan?",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#00bcd4',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Tarik Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    btn.prop('disabled', true).html('<i class="las la-spinner la-spin me-2"></i> SEDANG MENARIK DATA...');

                    $.ajax({
                        url: 'kepegawaian/proses_sync_profil.php',
                        type: 'POST',
                        dataType: 'json',
                        success: function (res) {
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    confirmButtonColor: '#00bcd4'
                                }).then(() => {
                                    window.location.href = '?data_sekolah';
                                });
                            } else {
                                Swal.fire({ icon: 'error', title: 'Gagal', text: res.message });
                                btn.prop('disabled', false).html(originalHtml);
                            }
                        },
                        error: function () {
                            Swal.fire({ icon: 'error', title: 'Error', text: 'Gagal menghubungi server.' });
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    });
                }
            });
        });
    });
</script>