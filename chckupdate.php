  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Pemeriksaan Pembaruan System</h1>
          </div>
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="index.php">Home</a></li>
              <li class="breadcrumb-item active">update</li>
            </ol>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
 <style>
        
        #update-info, #progress-container { margin-top: 20px; }
        progress { width: 100%; height: 25px; }
        .error { color: red; font-weight: bold; }
    </style>
    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
			<h4>Versi Saat Ini : <b><?php echo $ver; ?></b></h4>
                </div>

            <!-- /.card-header -->
            <div class="card-body">
			<h1></h1>
    <button id="check-btn" class="btn btn-primary">Check Update</button>

    <div id="update-info"></div><span id="wait" style="display:none;">.</span>
    
    <div id="progress-container" style="display:none;">
        <h3>Downloading Update...</h3>
        <progress id="progress-bar" value="0" max="100"></progress>
        <p id="progress-text">0%</p>
    </div>
             
                      
                 </div>
                <!-- /.card-body -->
                <div id="loadingOverlay" class="loading-overlay" style="display: none;">
                    <i class="fas fa-spinner fa-spin fa-3x"></i>
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
  
  <!-- /.content-wrapper -->
 

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->
<script>
    const checkBtn = document.getElementById('check-btn');
    const updateInfo = document.getElementById('update-info');
    const progressContainer = document.getElementById('progress-container');
    const progressBar = document.getElementById('progress-bar');
    const progressText = document.getElementById('progress-text');
    const waitSpan = document.getElementById('wait');
    
    let downloadUrl = '';
    let latestVersion = ''; // Simpan versi terbaru
    let progressInterval;

    function cekdot(show = false) {
        if (show) {
            waitSpan.style.display = 'inline';
        } else {
            waitSpan.style.display = 'none';
        }
    }
    
    // 1. Check for updates
    checkBtn.addEventListener('click', async () => {
        updateInfo.textContent = 'Memeriksa,';
        cekdot(true);
        let dots = window.setInterval(() => {
            if (waitSpan.innerHTML.length > 50) waitSpan.innerHTML = "";
            else waitSpan.innerHTML += ".";
        }, 200); 

        try {
            const response = await fetch('updates/check_update.php');
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            
            const data = await response.json();
            console.log(data);
            clearInterval(dots);
            cekdot(false);

            // --- PERBAIKAN LOGIKA JAVASCRIPT ---
            // Kita cek elemen PERTAMA dari array
            if (data[0] && data[0].update_available) {
                // Ambil data dari elemen KEDUA
                const updateDetails = data[1]; 
                
                downloadUrl = updateDetails.download_url; // Simpan URL
                latestVersion = updateDetails.latest_version; // Simpan Versi

                updateInfo.innerHTML = `
                    <p><strong>New Version Found: ${updateDetails.latest_version}</strong></p>
                    <p>Release Notes:<br><pre>${updateDetails.release_notes}</pre></p>
                    <button id="download-btn">Download Update</button>`;
                document.getElementById('download-btn').addEventListener('click', startDownload);
                
            } else if(data[0] && data[0].id == "1") {
                updateInfo.textContent = `${data[0].Error1}`;
                console.log(data[0].Error1);
            } else {
                updateInfo.textContent = 'You are on the latest version.';
                console.log(data[0].id);
            }
            // --- AKHIR PERBAIKAN ---

        } catch (error) {
            clearInterval(dots);
            cekdot(false);
            console.error("Failed to fetch update data:", error);
            updateInfo.innerHTML = `<p class="error">Failed to check for updates. Check console for details.</p>`;
        }
    });

    // 2. Start the download process
    function startDownload() {
        if (!downloadUrl) return;

        progressContainer.style.display = 'block';
        checkBtn.disabled = true;
        document.getElementById('download-btn').disabled = true;
        
        // --- PERBAIKAN: Kirim 'versi' ke skrip download ---
        // Ini adalah fallback jika aplikasi Anda tidak mengirimkannya
        const fullDownloadUrl = `update/download_update.php?url=${encodeURIComponent(downloadUrl)}&versi=${encodeURIComponent(latestVersion)}`;
        
        // Mulai download, kita tidak peduli dengan responsnya di sini
        fetch(fullDownloadUrl);

        // Mulai polling untuk progress
        progressInterval = setInterval(get_progress, 1000); // Poll setiap 1 detik
    }

    // 3. Poll the server for progress
    async function get_progress() {
        try {
            const response = await fetch('update/get_progress.php');
            const data = await response.json();

            if (data && data.progress !== undefined) {
                const percent = Math.round(data.progress);
                
                if (percent < 0) { // Menangani error
                    clearInterval(progressInterval);
                    progressText.textContent = `Error: ${data.message || 'Gagal mengunduh.'}`;
                    progressText.className = 'error';
                    checkBtn.disabled = false;
                    return;
                }

                progressBar.value = percent;
                progressText.textContent = `${percent}% - ${data.message || ''}`;
                progressText.className = '';

                if (percent >= 100) {
                    clearInterval(progressInterval);
                    progressText.textContent = 'Update Completed!';
                    checkBtn.disabled = false;
                    // Reset tombol download
                    updateInfo.innerHTML += "<p>Update successfully installed. Please restart the application.</p>";
                }
            }
        } catch (error) {
            console.error("Failed to get progress:", error);
            // Jangan hentikan interval jika hanya satu poll gagal, mungkin hanya timeout
        }
    }
</script>
