<?php
if (!isset($conn)) {
    include "../dbconn.php";
}
?>

<div class="content-fluid">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-file-excel text-success me-2"></i> Import Data Pegawai (Excel)</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content-fluid">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-success card-outline bg-dark-lighter shadow-sm" style="border-radius: 15px;">
                    <div class="card-header border-0 pt-3">
                        <h3 class="card-title fw-bold">Pilih File Excel (.xlsx / .xls)</h3>
                    </div>
                    <div class="card-body">
                        <div id="alertContainer"></div>

                        <div class="form-group mb-4 text-center p-5 border border-2 border-dashed border-secondary rounded-3"
                            id="dropZone">
                            <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                            <h5>Drag & Drop file di sini</h5>
                            <p class="text-muted small">Atau klik tombol di bawah untuk memilih file</p>
                            <input type="file" id="excelFile" class="d-none" accept=".xlsx, .xls">
                            <button type="button" class="btn btn-success px-4 rounded-pill fw-bold"
                                onclick="document.getElementById('excelFile').click()">
                                Pilih File Excel
                            </button>
                        </div>

                        <div id="fileInfo" class="d-none alert alert-dark bg-dark-darker border-secondary">
                            <div class="d-flex justify-content-between align-items-center">
                                <span><i class="fas fa-file-excel me-2 text-success"></i> <strong
                                        id="fileName">file.xlsx</strong></span>
                                <button type="button" class="btn btn-sm btn-danger rounded-circle"
                                    onclick="resetFile()"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-0 pb-4 text-center">
                        <button type="button" id="btnImport" class="btn btn-primary px-5 rounded-pill fw-bold" disabled
                            onclick="startImport()">
                            <i class="fas fa-cloud-download-alt me-2"></i> Mulai Import Sekarang
                        </button>
                    </div>
                </div>

                <!-- Progress Card (Hidden by default) -->
                <div id="progressCard" class="card bg-dark-lighter shadow-sm d-none" style="border-radius: 15px;">
                    <div class="card-body py-4">
                        <h6 class="fw-bold mb-3">Memproses Data... <span id="progressPercent"
                                class="float-end">0%</span></h6>
                        <div class="progress mb-2" style="height: 10px; border-radius: 10px;">
                            <div id="progressBar"
                                class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                role="progressbar" style="width: 0%"></div>
                        </div>
                        <p class="text-muted small mb-0" id="progressStatus">Membaca file excel...</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card bg-dark-lighter shadow-sm" style="border-radius: 15px;">
                    <div class="card-header border-0 pt-3">
                        <h3 class="card-title fw-bold text-muted small uppercase">Struktur Kolom Excel</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-sm table-dark bg-transparent">
                            <thead>
                                <tr class="text-muted small uppercase">
                                    <th>Kolom</th>
                                    <th>Keterangan</th>
                                    <th>Wajib</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>A</td>
                                    <td>NIP (Tanpa Spasi)</td>
                                    <td>Ya</td>
                                </tr>
                                <tr>
                                    <td>B</td>
                                    <td>Nama Pegawai</td>
                                    <td>Ya</td>
                                </tr>
                                <tr>
                                    <td>C</td>
                                    <td>Tempat Lahir</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>D</td>
                                    <td>Tanggal Lahir (YYYY-MM-DD)</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>E</td>
                                    <td>Jenis Kelamin (L/P)</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>F</td>
                                    <td>Pendidikan Terakhir</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>G</td>
                                    <td>Jabatan</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>H</td>
                                    <td>Pangkat</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>I</td>
                                    <td>Golongan</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>J</td>
                                    <td>Unit Kerja</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>K</td>
                                    <td>Status Pegawai</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>L</td>
                                    <td>Tanggal Lulus</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>M</td>
                                    <td>TMT Golongan</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>N</td>
                                    <td>No. HP / WA</td>
                                    <td>Tidak</td>
                                </tr>
                                <tr>
                                    <td>O</td>
                                    <td>Email</td>
                                    <td>Tidak</td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="alert alert-warning bg-warning-subtle border-0 text-warning-emphasis small mt-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Penting:</strong> Baris pertama (Header) harus ada dan akan diabaikan oleh
                            sistem.
                        </div>
                        <button type="button" onclick="downloadTemplate()"
                            class="btn btn-outline-info btn-sm rounded-pill w-100 mt-2 fw-bold">
                            <i class="fas fa-file-download me-2"></i> Download Template Excel (.xlsx)
                        </button>
                    </div>
                </div>
            </div>
        </div>
</div>
</section>
</div>

<!-- SheetJS Library -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
    let excelData = [];

    document.getElementById('excelFile').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) handleFile(file);
    });

    function handleFile(file) {
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileInfo').classList.remove('d-none');
        document.getElementById('dropZone').classList.add('d-none');

        const reader = new FileReader();
        reader.onload = function (e) {
            const data = new Uint8Array(e.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];

            // Convert to JSON with raw:false to prevent scientific notation for NIP
            excelData = XLSX.utils.sheet_to_json(firstSheet, { header: 1, raw: false });

            // Remove header row
            excelData.shift();

            if (excelData.length > 0) {
                document.getElementById('btnImport').disabled = false;
                showAlert('success', `File terbaca! Ditemukan ${excelData.length} baris data.`);
            } else {
                showAlert('danger', 'File kosong atau tidak memiliki data.');
            }
        };
        reader.readAsArrayBuffer(file);
    }

    function resetFile() {
        document.getElementById('excelFile').value = '';
        document.getElementById('fileInfo').classList.add('d-none');
        document.getElementById('dropZone').classList.remove('d-none');
        document.getElementById('btnImport').disabled = true;
        excelData = [];
    }

    async function startImport() {
        document.getElementById('btnImport').disabled = true;
        document.getElementById('progressCard').classList.remove('d-none');

        const total = excelData.length;
        let success = 0;
        let error = 0;

        for (let i = 0; i < total; i++) {
            const row = excelData[i];
            const percent = Math.round(((i + 1) / total) * 100);

            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressPercent').textContent = percent + '%';
            document.getElementById('progressStatus').textContent = `Mengirim data: ${row[1] || '...'}`;

            try {
                const response = await fetch('proses_import_excel.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        nip: row[0] || '',
                        nama: row[1] || '',
                        tempat_lahir: row[2] || '',
                        tgl_lahir: row[3] || '',
                        jenis_kelamin: row[4] || '',
                        pendidikan: row[5] || '',
                        jabatan: row[6] || '',
                        pangkat: row[7] || '',
                        golongan: row[8] || '',
                        unit_kerja: row[9] || '',
                        status_pegawai: row[10] || '',
                        tgl_lulus: row[11] || '',
                        tmt_golongan: row[12] || '',
                        no_hp: row[13] || '',
                        email: row[14] || ''
                    })
                });
                const res = await response.json();
                if (res.status === 'success') success++; else error++;
            } catch (err) {
                error++;
            }
        }

        showAlert('success', `Selesai! Berhasil: ${success}, Gagal/Duplikat: ${error}.`);
        document.getElementById('progressStatus').textContent = 'Proses import selesai.';
        document.getElementById('progressBar').classList.remove('progress-bar-animated');
    }

    function downloadTemplate() {
        const header = [["NIP", "Nama Pegawai", "Tempat Lahir", "Tanggal Lahir", "Jenis Kelamin", "Pendidikan Terakhir", "Jabatan", "Pangkat", "Golongan", "Unit Kerja", "Status Pegawai", "Tanggal Lulus", "TMT Golongan", "No hp", "Email"]];
        const worksheet = XLSX.utils.aoa_to_sheet(header);
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, "Template");
        XLSX.writeFile(workbook, "template_pegawai.xlsx");
    }

    function showAlert(type, msg) {
        const container = document.getElementById('alertContainer');
        container.innerHTML = `<div class="alert alert-${type} alert-dismissible fade show" role="alert">
        ${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>`;
    }
</script>

<style>
    .bg-dark-lighter {
        background-color: #1e293b !important;
    }

    .bg-dark-darker {
        background-color: #0f172a !important;
    }

    .border-dashed {
        border-style: dashed !important;
    }

    .btn-primary {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        border: none;
    }
</style>