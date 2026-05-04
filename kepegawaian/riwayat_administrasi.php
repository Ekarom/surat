<?php
/**
 * Riwayat Kepengurusan Administrasi - PTK Portal
 * Managed by Antigravity AI
 */

if (!isset($conn) || !$conn) {
    include_once "../dbconn.php";
}

$id_pegawai = $_SESSION['id'] ?? 0;
?>

<!-- Reuse style from riwayat_saya.php logic -->
<?php include "riwayat_saya.php"; ?>

<script>
    $(document).ready(function () {
        // Override the loadRiwayat to only show 'Administrasi'
        const originalLoadRiwayat = window.loadRiwayat;
        
        window.loadRiwayat = () => {
            $.get('proses_pegawai.php', { action: 'muatRiwayat', pegawai_id: <?php echo $id_pegawai; ?>, kategori: 'Administrasi' }, (res) => {
                let html = '';
                if (res.status === 'success' && res.data && res.data.length > 0) {
                    res.data.forEach(item => {
                        const fileBtn = item.file_lampiran ? `<a href="../file/datakepegawaian/${item.file_lampiran}" target="_blank" class="btn btn-xs btn-light border p-1 rounded shadow-sm" title="Lihat Dokumen"><i class="fas fa-file-pdf text-danger me-1"></i> Dokumen</a>` : '<span class="text-muted small italic">Tidak ada file</span>';
                        let badgeClass = 'bg-administrasi';
                        let detailHtml = `<div class="fw-medium text-dark">${item.deskripsi}</div>`;
                        let docInfo = item.no_sk || '-';

                        html += `
                        <tr>
                            <td><span class="badge ${badgeClass} border-0 small px-2 py-1">${item.kategori}</span></td>
                            <td>${detailHtml}</td>
                            <td class="text-muted small">${formatDate(item.tmt)}</td>
                            <td>
                                <div class="small text-muted mb-1">${docInfo}</div>
                                ${fileBtn}
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-1">
                                    <button class="btn btn-sm btn-light border shadow-sm edit-riwayat" data-id="${item.id}" title="Edit"><i class="fas fa-edit text-warning"></i></button>
                                    <button class="btn btn-sm btn-light border shadow-sm hapus-riwayat" data-id="${item.id}" title="Hapus"><i class="fas fa-trash text-danger"></i></button>
                                </div>
                            </td>
                        </tr>`;
                    });
                } else {
                    html = '<tr><td colspan="5" class="text-center text-muted py-5 small">Belum ada riwayat kepengurusan administrasi.</td></tr>';
                }
                $('#isiTabelRiwayat').html(html);
            }, 'json');
        };

        // Re-init with filter
        loadRiwayat();
        
        // Adjust titles
        $('.page-title').text('Riwayat Kepengurusan Administrasi');
        $('.breadcrumb-item.active').text('Administrasi');
        
        // Pre-select Administrasi in modal when adding
        $('#tombolTambahRiwayat').off('click').on('click', function() {
            $('#formRiwayat')[0].reset();
            $('#id_riwayat, #file_lama_riwayat').val('');
            $('#riwayat_kategori').val('Administrasi').trigger('change');
            $('#modalFormRiwayatLabel').text('Tambah Riwayat Administrasi');
            new bootstrap.Modal(document.getElementById('modalFormRiwayat')).show();
        });
    });
</script>
