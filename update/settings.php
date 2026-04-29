<?php
session_start();
// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - AD UPDATE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
            --sidebar-width: 260px;
            --sidebar-width-collapsed: 80px;
        }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: #0f2027; 
            background: linear-gradient(to right, #2c5364, #203a43, #0f2027);
            color: #f0f0f0; 
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Glassmorphism Card */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 40px rgba(0, 0, 0, 0.2);
            border-color: rgba(255,255,255,0.2);
        }

        /* Sidebar */
        .sidebar { 
            height: 100vh; 
            background: rgba(15, 32, 39, 0.85);
            backdrop-filter: blur(15px);
            border-right: 1px solid var(--glass-border); 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            width: var(--sidebar-width);
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid var(--glass-border);
        }
        
        .logo-text {
            font-weight: 700;
            font-size: 1.5rem;
            background: var(--primary-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            letter-spacing: 1px;
        }

        .nav-link { 
            color: #a0a0a0; 
            padding: 1rem 1.5rem; 
            white-space: nowrap; 
            overflow: hidden; 
            border-left: 3px solid transparent;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .nav-link i {
            font-size: 1.25rem;
            margin-right: 1rem;
            width: 24px;
            text-align: center;
            transition: margin 0.3s;
        }
        
        .nav-link:hover { color: #fff; background: rgba(255,255,255,0.03); }
        .nav-link.active { background: rgba(118, 75, 162, 0.15); color: #fff; border-left-color: #764ba2; }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2.5rem;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar.collapsed { width: var(--sidebar-width-collapsed); }
        .sidebar.collapsed .logo-text, .sidebar.collapsed .nav-link span { display: none; }
        .sidebar.collapsed .sidebar-header { padding: 1.5rem 0; text-align: center; }
        .sidebar.collapsed .nav-link { justify-content: center; padding: 1rem 0; }
        .sidebar.collapsed .nav-link i { margin-right: 0; }
        .main-content.expanded { margin-left: var(--sidebar-width-collapsed); }

        /* Tables & Forms */
        .table-custom { margin-bottom: 0; color: #e0e0e0; }
        .table-custom th { background-color: rgba(0,0,0,0.2); color: #aaa; font-weight: 600; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; border-bottom: 1px solid var(--glass-border); padding: 1rem; }
        .table-custom td { padding: 1rem; border-bottom: 1px solid var(--glass-border); vertical-align: middle; }
        .table-custom tr:last-child td { border-bottom: none; }
        .table-custom tr:hover td { background-color: rgba(255,255,255,0.02); }
        
        .form-control { background: rgba(0,0,0,0.2); border: 1px solid var(--glass-border); color: #fff; }
        .form-control:focus { background: rgba(0,0,0,0.3); border-color: #667eea; color: #fff; box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25); }
        .form-label { font-weight: 500; font-size: 0.9rem; margin-bottom: 0.5rem; }

        /* Animations */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        .animate-up { animation: fadeInUp 0.6s ease forwards; opacity: 0; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-text text-nowrap">AD UPDATE</div>
            <div class="small text-muted mt-1 text-center" style="display: none;" id="logoSmall">AD</div>
        </div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2"></i> <span>Dashboard</span></a>
            <a href="users.php" class="nav-link"><i class="bi bi-people"></i> <span>Pengguna</span></a>
            <a href="files.php" class="nav-link"><i class="bi bi-folder2-open"></i> <span>Files</span></a>
            <a href="settings.php" class="nav-link active"><i class="bi bi-gear"></i> <span>Pengaturan</span></a>
        </div>
        <div class="mt-auto mb-4">
             <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
        </div>
    </nav>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Topbar -->
        <header class="d-flex justify-content-between align-items-center mb-5 animate-up">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light border-0 me-3 rounded-circle p-2" id="sidebarToggle" style="width: 45px; height: 45px; background: rgba(255,255,255,0.05);">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h2 class="mb-0 fw-bold">Pengaturan</h2>
                    <p class="text-muted mb-0 small">Konfigurasi Sistem</p>
                </div>
            </div>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end d-none d-md-block">
                    <div class="fw-bold"><?php echo htmlspecialchars($_SESSION['nama_lengkap'] ?? 'Admin'); ?></div>
                    <div class="small text-muted">Administrator</div>
                </div>
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                    <i class="bi bi-person-fill fs-5"></i>
                </div>
            </div>
        </header>

        <div class="card glass-card">
            <div class="card-body p-5 text-center">
                <i class="bi bi-gear display-1 text-muted mb-3"></i>
                <h5 class="card-title fw-bold">Fitur Pengaturan</h5>
                <p class="card-text text-muted">Halaman ini sedang dalam pengembangan. Silakan kembali lagi nanti.</p>
            </div>
        </div>

    </main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    const logoText = document.querySelector('.logo-text');
    const logoSmall = document.getElementById('logoSmall');
    
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        sidebar.classList.toggle('collapsed');
        mainContent.classList.toggle('expanded');
        
        if(sidebar.classList.contains('collapsed')) {
             logoText.style.display = 'none';
             logoSmall.style.display = 'block';
        } else {
             logoText.style.display = 'block';
             logoSmall.style.display = 'none';
        }
    });

    if(window.innerWidth < 768) {
        sidebar.classList.add('collapsed');
        mainContent.classList.add('expanded');
        logoText.style.display = 'none';
        logoSmall.style.display = 'block';
    }
</script>
</body>
</html>
