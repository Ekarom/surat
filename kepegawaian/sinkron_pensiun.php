<?php
/**
 * Page Khusus Sinkronisasi Data Pensiun
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    $db_path = file_exists('../dbconn.php') ? '../dbconn.php' : 'dbconn.php';
    include_once $db_path;
}

// Fetch stats for overview
$q_total = $conn->query("SELECT COUNT(*) FROM pegawai WHERE status = '1'");
$total_count = $q_total ? ($q_total->fetch_row()[0] ?? 0) : 0;

$q_synced = $conn->query("SELECT COUNT(*) FROM pegawai WHERE is_pensiun_synced = 1 AND status = '1'");
$synced_count = $q_synced ? ($q_synced->fetch_row()[0] ?? 0) : 0;

$q_missing = $conn->query("SELECT COUNT(*) FROM pegawai WHERE (tgl_lahir IS NULL OR tgl_lahir = '0000-00-00') AND status = '1'");
$missing_count = $q_missing ? ($q_missing->fetch_row()[0] ?? 0) : 0;

// Fetch master data count from sas_2026
$db_source = "sas_2026";
$conn_source = @new mysqli($host, $user, $pass, $db_source);
$master_count = 0;
if ($conn_source && !$conn_source->connect_error) {
    $q_m = $conn_source->query("SELECT COUNT(*) FROM pegawai");
    $master_count = $q_m ? ($q_m->fetch_row()[0] ?? 0) : 0;
    $conn_source->close();
}
?>

<style>
    .sync-wrapper {
        background: #fff;
        border-radius: 12px;
        border: 1px solid #f59e0b; /* Using amber/warning color for pensiun */
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    }

    .sync-header {
        background: #f59e0b;
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
        color: #f59e0b;
    }

    .data-count {
        font-size: 5rem;
        font-weight: 800;
        color: #f59e0b;
        line-height: 1;
    }

    .btn-tarik {
        background: #f59e0b;
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
        box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
    }

    .btn-tarik:hover {
        background: #d97706;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(245, 158, 11, 0.4);
        color: #fff;
    }

    .btn-tarik:disabled {
        background: #9ca3af;
        box-shadow: none;
        transform: none;
    }

    .extra-small {
        font-size: 0.75rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="sync-wrapper">
                <div class="sync-header">
                    <h5><i class="las la-user-clock"></i> Sinkronisasi Data Pensiun</h5>
                    <div class="source-badge">Sumber: sas_2026</div>
                </div>

                <div class="p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <!-- Left: Status & Count -->
                        <div class="col-md-6 text-center border-end">
                            <div class="text-muted small fw-bold text-uppercase mb-3">Data di sas_2026</div>
                            <div class="data-count mb-2"><?php echo $master_count; ?></div>
                            <div class="small text-muted">Pegawai ditemukan di database sumber</div>
                        </div>

                        <!-- Right: Mapping & Action -->
                        <div class="col-md-6">
                            <div class="mapping-card mb-4">
                                <div class="mapping-label">Aturan Sinkronisasi:</div>
                                <div class="mapping-detail mb-1">Kalkulasi: <b>Usia 58 & 60 Tahun</b></div>
                                <div class="mapping-detail">Target: <b>Tabel Pensiun & Status Pegawai</b></div>
                            </div>

                            <div class="d-flex gap-2">
                                <button id="btnStartSync" class="btn btn-tarik flex-grow-1">
                                    <i class="las la-sync-alt fs-5"></i> JALANKAN SINKRONISASI
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Detail Comparisons -->
            <div class="row mt-4 g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 15px;">
                        <div class="text-muted small fw-bold text-uppercase mb-3">
                            <i class="las la-check-circle me-1 text-success"></i> Status Sinkronisasi Terakhir
                        </div>
                        <h5 class="fw-bold mb-1"><?php echo $synced_count; ?> Pegawai</h5>
                        <div class="small text-muted">Sudah terdata dalam sistem estimasi pensiun.</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm p-4 h-100" style="border-radius: 15px;">
                        <div class="text-muted small fw-bold text-uppercase mb-3">
                            <i class="las la-exclamation-triangle me-1 text-danger"></i> Data Belum Lengkap
                        </div>
                        <h5 class="fw-bold <?php echo $missing_count > 0 ? 'text-danger' : 'text-success'; ?> mb-1">
                            <?php echo $missing_count; ?> Pegawai
                        </h5>
                        <div class="small text-muted">Tanpa tanggal lahir (Tidak dapat dihitung pensiunnya).</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Sinkronisasi -->
<div class="modal fade" id="modalConfirmSync" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark"><i class="las la-info-circle text-warning me-2"></i> Konfirmasi Sinkronisasi</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <p class="mb-0 text-muted">Sistem akan menghitung ulang estimasi tanggal pensiun untuk seluruh pegawai aktif berdasarkan tanggal lahir mereka. <br><br><strong>Lanjutkan proses sinkronisasi?</strong></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="button" id="btnConfirmSyncAction" class="btn btn-tarik rounded-pill px-4" style="width: auto;">Ya, Jalankan Sekarang</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        const syncModal = new bootstrap.Modal(document.getElementById('modalConfirmSync'));
        let originalHtml = '';
        const btnStart = $('#btnStartSync');

        btnStart.on('click', function () {
            originalHtml = btnStart.html();
            syncModal.show();
        });

        $('#btnConfirmSyncAction').on('click', function () {
            syncModal.hide();
            btnStart.prop('disabled', true).html('<i class="las la-spinner la-spin me-2"></i> SEDANG MEMPROSES...');

            $.ajax({
                url: 'proses_pegawai.php',
                type: 'POST',
                data: { action: 'syncPensiun' },
                dataType: 'json',
                success: function (res) {
                    if (res.status === 'success') {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: res.message,
                                confirmButtonColor: '#f59e0b'
                            }).then(() => {
                                window.location.href = '?data_pensiun';
                            });
                        } else {
                            alert(res.message);
                            window.location.href = '?data_pensiun';
                        }
                    } else {
                        alert('Gagal: ' + res.message);
                        btnStart.prop('disabled', false).html(originalHtml);
                    }
                },
                error: function () {
                    alert('Gagal menghubungi server.');
                    btnStart.prop('disabled', false).html(originalHtml);
                }
            });
        });
    });
</script>
