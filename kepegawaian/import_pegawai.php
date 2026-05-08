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
                    <button type="button" class="btn btn-outline-info btn-sm btn-rounded px-4 mt-2 fw-bold">
                        Pilih File Excel
                    </button>
                </div>

                <div id="fileInfo" class="d-none mt-3 p-3 rounded-4 bg-light border border-dashed">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="small fw-bold text-dark">
                            <i class="las la-file-excel me-2 text-success"></i>
                            <span id="fileName">file.xlsx</span>
                        </span>
                        <button type="button"
                            class="btn btn-sm btn-danger rounded-circle p-0 d-flex align-items-center justify-content-center"
                            style="width: 24px; height: 24px;" onclick="resetFile()">
                            <i class="las la-times" style="font-size: 10px;"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-4 pt-2 border-top text-center">
                    <button type="button" id="btnImport" class="btn btn-outline-info btn-rounded px-5 fw-bold shadow-sm"
                        disabled onclick="startImport()">
                        <i class="las la-cloud-upload-alt me-2"></i>Upload
                    </button>
                </div>
            </div>

            <!-- Progress Card -->
            <div id="progressCard" class="modern-card mt-4 p-4 d-none">
                <h6 class="fw-bold mb-3">Memproses Data... <span id="progressPercent"
                        class="float-end text-primary">0%</span></h6>
                <div class="progress mb-2" style="height: 8px; border-radius: 10px; background: #f1f5f9;">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary"
                        role="progressbar" style="width: 0%"></div>
                </div>
                <p class="text-muted extra-small mb-0" id="progressStatus">Membaca file excel...</p>
            </div>

            <!-- Log Card -->
            <div id="logCard" class="modern-card mt-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 small uppercase text-muted">Log Aktivitas Import</h6>
                    <span class="badge rounded-pill bg-light text-primary border" id="logCounter" style="font-size: 0.65rem;">0 Entri</span>
                </div>
                <div id="logList" class="overflow-auto pe-2" style="max-height: 400px; scroll-behavior: smooth;">
                    <div class="text-center py-4 text-muted extra-small">Belum ada aktivitas</div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="modern-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-muted extra-small uppercase">Struktur Kolom Excel</h6>
                    <button type="button" onclick="downloadTemplate()"
                        class="btn btn-link text-decoration-none p-0 extra-small fw-bold">
                        <i class="las la-download me-1"></i> Template
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
                            <tr>
                                <td class="fw-bold text-primary">A</td>
                                <td>NIP / NIK</td>
                                <td class="text-center"><span class="text-danger fw-bold">Ya</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">B</td>
                                <td>NRK</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">C</td>
                                <td>Nama Lengkap</td>
                                <td class="text-center"><span class="text-danger fw-bold">Ya</span></td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">D</td>
                                <td>Tempat Lahir</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">E</td>
                                <td>Tanggal Lahir</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">F</td>
                                <td>Gender (L/P)</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">G</td>
                                <td>Pendidikan Terakhir</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">H</td>
                                <td>Jabatan</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">I</td>
                                <td>Pangkat</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">J</td>
                                <td>Golongan</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">K</td>
                                <td>Unit Kerja</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">L</td>
                                <td>Status Pegawai</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">M</td>
                                <td>NUPTK</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">N</td>
                                <td>Agama</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">O</td>
                                <td>Alamat (Jalan)</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">P</td>
                                <td>TMT Golongan</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">Q</td>
                                <td>RT</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">R</td>
                                <td>RW</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">S</td>
                                <td>Kelurahan</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">T</td>
                                <td>Kecamatan</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">U</td>
                                <td>No. HP</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">V</td>
                                <td>Email</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">W</td>
                                <td>TMT Pangkat</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">X</td>
                                <td>No. Karis/Karsu</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                            <tr>
                                <td class="fw-bold text-primary">Y</td>
                                <td>No. Karpeg</td>
                                <td class="text-center text-muted">Tidak</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 alert alert-info bg-soft-primary border-0 rounded-4">
                    <div class="d-flex gap-3">
                        <i class="las la-info-circle mt-1 text-primary"></i>
                        <div class="small">
                            <strong class="d-block mb-1">Informasi Penting</strong>
                            <span>Baris pertama dianggap sebagai Header dan akan diabaikan. Pastikan format tanggal
                                adalah <strong>YYYY-MM-DD</strong>.</span>
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
    let logCount = 0;

    const addLog = (type, message, rowIdx) => {
        logCount++;
        const logList = document.getElementById('logList');
        const item = document.createElement('div');
        item.className = `p-2 mb-2 rounded-3 extra-small d-flex align-items-center gap-2 border-start border-3 transition-all ${type === 'success' ? 'bg-soft-success border-success' : type === 'update' ? 'bg-soft-primary border-primary' : 'bg-soft-danger border-danger'}`;
        
        const icon = type === 'success' ? 'la-check-circle text-success' : type === 'update' ? 'la-sync text-primary' : 'la-exclamation-circle text-danger';
        
        item.innerHTML = `
            <i class="las ${icon} fs-5"></i>
            <div class="flex-grow-1">
                <div class="d-flex justify-content-between align-items-center">
                    <strong class="text-dark">${rowIdx >= 0 ? 'Baris ' + (rowIdx + 2) : 'Sistem'}</strong>
                    <span class="text-muted" style="font-size: 0.6rem;">${new Date().toLocaleTimeString()}</span>
                </div>
                <div class="text-muted text-truncate" style="max-width: 250px;">${message}</div>
            </div>
        `;
        logList.prepend(item);
        document.getElementById('logCounter').textContent = `${logCount} Entri`;
    };

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
            const workbook = XLSX.read(data, {
                type: 'array',
                cellDates: true,
                cellNF: false,
                cellText: false
            });
            const firstSheet = workbook.Sheets[workbook.SheetNames[0]];

            // Convert to JSON with raw:false to prevent scientific notation for NIP
            excelData = XLSX.utils.sheet_to_json(firstSheet, { header: 1, raw: false });

            // Remove header row
            excelData.shift();

            if (excelData.length > 0) {
                document.getElementById('btnImport').disabled = false;
                // Clear log and add initial info
                document.getElementById('logList').innerHTML = '';
                logCount = 0;
                document.getElementById('logCounter').textContent = '0 Entri';
                
                addLog('success', `File <b>${file.name}</b> terpilih. ${excelData.length} baris data siap diunggah.`, -1);
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
        document.getElementById('logCard').classList.remove('d-none');
        document.getElementById('logList').innerHTML = '';

        const total = excelData.length;
        let success = 0;
        let error = 0;
        let errorLog = [];
        logCount = 0;

        // Helper functions
        const cleanValue = (val) => {
            if (!val) return '';
            let str = String(val).trim();
            if (str.toUpperCase().includes('E+')) {
                return Number(str).toLocaleString('fullwide', { useGrouping: false });
            }
            return str;
        };

        const formatDate = (val) => {
            if (!val) return '';
            if (val instanceof Date) {
                const y = val.getFullYear();
                const m = String(val.getMonth() + 1).padStart(2, '0');
                const d = String(val.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            }
            return String(val).trim();
        };

        for (let i = 0; i < total; i++) {
            const row = excelData[i];

            // Validate NIP (Column A) and Nama (Column C)
            const rawNip = cleanValue(row[0]);
            const rawNama = (row[2] || '').toString().trim();

            if (!rawNip || rawNip === '0') {
                continue; // Silent skip for empty or 0 NIP
            }
            
            if (!rawNama) {
                addLog('error', `Baris ${i + 2}: Nama kosong, dilewati.`, i);
                continue;
            }

            const percent = Math.round(((i + 1) / total) * 100);

            document.getElementById('progressBar').style.width = percent + '%';
            document.getElementById('progressPercent').textContent = percent + '%';
            document.getElementById('progressStatus').textContent = `Mengirim data: ${row[2] || row[0] || '...'}`;

            // Add small delay to prevent server overload
            await new Promise(resolve => setTimeout(resolve, 100));

            try {
                const response = await fetch('proses_import_excel.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: new URLSearchParams({
                        nip: rawNip,
                        nrk: cleanValue(row[1]) || '0',
                        nama: row[2] || '',
                        tempat_lahir: row[3] || '',
                        tgl_lahir: formatDate(row[4]),
                        jenis_kelamin: row[5] || '',
                        pendidikan: row[6] || '',
                        tgl_lulus: '', // Not in primary Excel structure
                        jabatan: row[7] || '',
                        pangkat: row[8] || '',
                        golongan: row[9] || '',
                        unit_kerja: row[10] || '',
                        status_pegawai: row[11] || '',
                        nuptk: row[12] || '',
                        agama: row[13] || '',
                        alamat: cleanValue(row[14]) || '',
                        tmt_golongan: formatDate(row[15]),
                        rt: cleanValue(row[16]) || '',
                        rw: cleanValue(row[17]) || '',
                        kelurahan: cleanValue(row[18]) || '',
                        kecamatan: cleanValue(row[19]) || '',
                        no_hp: cleanValue(row[20]) || '',
                        email: cleanValue(row[21]) || '',
                        tmt_pangkat: formatDate(row[22]),
                        no_karis_karsu: cleanValue(row[23]) || '',
                        no_karpeg: cleanValue(row[24]) || ''
                    })
                });

                const text = await response.text();
                let res;
                try {
                    res = JSON.parse(text);
                } catch (e) {
                    throw new Error("Respon Server Bukan JSON: " + text.substring(0, 50));
                }

                if (res.status === 'success') {
                    success++;
                    addLog(res.mode === 'update' ? 'update' : 'success', `${rawNama} (${res.mode === 'update' ? 'Diperbarui' : 'Ditambah'})`, i);
                } else if (res.status === 'skipped') {
                    // Silently ignore skipped status
                    continue;
                } else {
                    error++;
                    errorLog.push(`Baris ${i + 2} (${row[0] || 'NIP Kosong'}): ${res.message}`);
                    addLog('error', `${rawNama || rawNip}: ${res.message}`, i);
                }
            } catch (err) {
                error++;
                errorLog.push(`Baris ${i + 2}: ${err.message || 'Kesalahan Koneksi'}`);
                addLog('error', `Error: ${err.message || 'Kesalahan Koneksi'}`, i);
            }
        }

        let alertMsg = `<div class="d-flex align-items-center mb-2">
                                <i class="las la-check-circle fs-4 me-2 text-success"></i>
                                <span class="fw-bold">Proses Import Selesai!</span>
                            </div>
                            <p class="mb-3">Berhasil: <b>${success}</b>, Gagal: <b>${error}</b>.</p>`;

        if (success > 0) {
            alertMsg += `
                    <div class="d-flex gap-2 mb-2">
                        <a href="?data_pegawai" class="btn btn-light btn-sm btn-rounded px-3 extra-small fw-bold border">
                            <i class="las la-users me-1"></i> Lihat Data
                        </a>
                        <a href="?data_pegawai&import" class="btn btn-primary btn-sm btn-rounded px-3 extra-small fw-bold shadow-sm">
                            <i class="las la-sync me-1"></i> Sinkron Pensiun
                        </a>
                    </div>
                `;
        }

        if (errorLog.length > 0) {
            alertMsg += `<hr><div class="extra-small text-start" style="max-height:150px; overflow-y:auto;"><strong>Detail Error:</strong><br>${errorLog.join('<br>')}</div>`;
        }
        showAlert(error > 0 ? 'warning' : 'success', alertMsg);
        document.getElementById('progressStatus').textContent = 'Proses import selesai.';
        document.getElementById('progressBar').classList.remove('progress-bar-animated');
    }

    function downloadTemplate() {
        const header = [["NIP", "NRK", "Nama Pegawai", "Tempat Lahir", "Tanggal Lahir", "Jenis Kelamin", "Pendidikan Terakhir", "Jabatan", "Pangkat", "Golongan", "Unit Kerja", "Status Pegawai", "NUPTK", "Agama", "Alamat", "TMT Golongan", "RT", "RW", "Kelurahan", "Kecamatan", "No hp", "Email", "TMT Pangkat", "No. Karis/Karsu", "No. Karpeg"]];
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
    .extra-small {
        font-size: 0.75rem;
    }

    .bg-soft-primary {
        background-color: var(--sap-primary-light);
        color: var(--sap-primary);
    }

    .bg-soft-success {
        background-color: rgba(16, 185, 129, 0.1);
        color: #10b981;
    }

    .bg-soft-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .border-dashed {
        border-style: dashed !important;
    }
</style>