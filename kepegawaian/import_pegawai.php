<?php
if (!isset($conn)) {
    include "../dbconn.php";
}
?>

<style>
    /* CSS Variables from Main System */
    :root {
        --sap-primary: #4f46e5;
        --sap-primary-light: rgba(79, 70, 229, 0.1);
        --sap-primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        --sap-secondary: #64748b;
        --sap-success: #10b981;
        --sap-gray-50: #f8fafc;
        --sap-gray-100: #f1f5f9;
        --sap-gray-200: #e2e8f0;
        --sap-border-radius: 1rem;
        --sap-shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    }

    .page-title {
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.025em;
    }

    .modern-card {
        background: #fff;
        border-radius: var(--sap-border-radius);
        border: none;
        box-shadow: var(--sap-shadow-sm);
        transition: all 0.3s ease;
    }

    .drop-zone {
        border: 2px dashed var(--sap-gray-200);
        border-radius: 1.25rem;
        padding: 3rem;
        text-align: center;
        background: var(--sap-gray-50);
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .drop-zone:hover,
    .drop-zone.active {
        border-color: var(--sap-primary);
        background: var(--sap-primary-light);
    }

    .drop-zone i {
        font-size: 3.5rem;
        color: var(--sap-primary);
        margin-bottom: 1rem;
    }

    .table-sm thead th {
        font-size: 0.7rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--sap-secondary);
        letter-spacing: 0.05em;
        border-bottom: 1px solid var(--sap-gray-100);
        padding: 0.75rem 0.5rem;
    }

    .table-sm tbody td {
        font-size: 0.85rem;
        padding: 0.6rem 0.5rem;
        color: #334155;
    }

    .btn-rounded {
        border-radius: 50px;
    }
</style>

<div class="container-fluid py-2">
    <div class="row align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="page-title mb-1">Import Data Pegawai</h2>
            <p class="text-muted small mb-0">Unggah berkas Excel untuk memperbarui database pegawai secara massal</p>
        </div>
        <div class="col-md-6 text-md-end mt-3 mt-md-0">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-md-end bg-transparent p-0 m-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none text-muted">Home</a></li>
                    <li class="breadcrumb-item active text-primary fw-bold" aria-current="page">Import Data</li>
                </ol>
            </nav>
        </div>
    </div>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="modern-card p-4">
                    <h5 class="fw-bold mb-4">Pilih File Excel</h5>
                    <div id="alertContainer"></div>

                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('excelFile').click()">
                        <i class="la la-cloud-upload"></i>
                        <h5 class="fw-bold">Drag & Drop file di sini</h5>
                        <p class="text-muted small">Atau klik untuk memilih file dari komputer Anda</p>
                        <input type="file" id="excelFile" class="d-none" accept=".xlsx, .xls">
                        <button type="button" class="btn btn-primary btn-sm btn-rounded px-4 mt-2 fw-bold">
                            Pilih File Excel
                        </button>
                    </div>

                    <div id="fileInfo" class="d-none mt-3 p-3 rounded-4 bg-light border border-dashed">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small fw-bold text-dark">
                                <i class="fas fa-file-excel me-2 text-success"></i>
                                <span id="fileName">file.xlsx</span>
                            </span>
                            <button type="button" class="btn btn-sm btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center"
                                style="width: 24px; height: 24px;" onclick="resetFile()">
                                <i class="fas fa-times" style="font-size: 10px;"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-top text-center">
                        <button type="button" id="btnImport" class="btn btn-primary btn-rounded px-5 fw-bold shadow-sm"
                            disabled onclick="startImport()">
                            <i class="fas fa-cloud-upload-alt me-2"></i> Mulai Import Sekarang
                        </button>
                    </div>
                </div>

                <!-- Progress Card -->
                <div id="progressCard" class="modern-card mt-4 p-4 d-none">
                    <h6 class="fw-bold mb-3">Memproses Data... <span id="progressPercent" class="float-end text-primary">0%</span></h6>
                    <div class="progress mb-2" style="height: 8px; border-radius: 10px; background: #f1f5f9;">
                        <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                            role="progressbar" style="width: 0%"></div>
                    </div>
                    <p class="text-muted extra-small mb-0" id="progressStatus">Membaca file excel...</p>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="modern-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0 text-muted extra-small uppercase">Struktur Kolom Excel</h6>
                        <button type="button" onclick="downloadTemplate()" class="btn btn-link text-decoration-none p-0 extra-small fw-bold">
                            <i class="fas fa-download me-1"></i> Template
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead>
                                <tr>
                                    <th width="60">Kolom</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Wajib</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td class="fw-bold text-primary">A</td><td>NIP / NIK</td><td class="text-center"><span class="text-danger fw-bold">Ya</span></td></tr>
                                <tr><td class="fw-bold text-primary">B</td><td>Nama Lengkap</td><td class="text-center"><span class="text-danger fw-bold">Ya</span></td></tr>
                                <tr><td class="fw-bold text-primary">C</td><td>Tempat Lahir</td><td class="text-center text-muted">Tidak</td></tr>
                                <tr><td class="fw-bold text-primary">D</td><td>Tanggal Lahir</td><td class="text-center text-muted">Tidak</td></tr>
                                <tr><td class="fw-bold text-primary">E</td><td>Gender (L/P)</td><td class="text-center text-muted">Tidak</td></tr>
                                <tr><td class="fw-bold text-primary">F</td><td>Pendidikan</td><td class="text-center text-muted">Tidak</td></tr>
                                <tr><td class="fw-bold text-primary">G</td><td>Jabatan</td><td class="text-center text-muted">Tidak</td></tr>
                                <tr><td class="fw-bold text-primary">H</td><td>Pangkat</td><td class="text-center text-muted">Tidak</td></tr>
                                <tr><td class="fw-bold text-primary">I</td><td>Golongan</td><td class="text-center text-muted">Tidak</td></tr>
                                <tr><td class="fw-bold text-primary">J</td><td>Unit Kerja</td><td class="text-center text-muted">Tidak</td></tr>
                                <tr><td class="fw-bold text-primary">K</td><td>Status Pegawai</td><td class="text-center text-muted">Tidak</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 alert alert-info bg-soft-primary border-0 rounded-4">
                        <div class="d-flex gap-3">
                            <i class="fas fa-info-circle mt-1 text-primary"></i>
                            <div class="small">
                                <strong class="d-block mb-1">Informasi Penting</strong>
                                <span>Baris pertama dianggap sebagai Header dan akan diabaikan. Pastikan format tanggal adalah <strong>YYYY-MM-DD</strong>.</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    .extra-small { font-size: 0.75rem; }
    .bg-soft-primary { background-color: var(--sap-primary-light); color: var(--sap-primary); }
    .border-dashed { border-style: dashed !important; }
</style>