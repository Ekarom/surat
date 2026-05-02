<?php
session_start();
require 'koneksi.php';

// Cek Login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle CRUD Operations
$message = '';
$messageType = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // ADD USER
    if (isset($_POST['action']) && $_POST['action'] == 'add') {
        $username = mysqli_real_escape_string($koneksi, $_POST['username']);
        $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Cek username exist
        $check = mysqli_query($koneksi, "SELECT id FROM users WHERE username = '$username'");
        if (mysqli_num_rows($check) > 0) {
            $message = "Username sudah digunakan!";
            $messageType = "danger";
        } else {
            $query = "INSERT INTO users (username, password, nama_lengkap) VALUES ('$username', '$password', '$nama_lengkap')";
            if (mysqli_query($koneksi, $query)) {
                $message = "User berhasil ditambahkan!";
                $messageType = "success";
            } else {
                $message = "Gagal menambahkan user: " . mysqli_error($koneksi);
                $messageType = "danger";
            }
        }
    }
    
    // EDIT USER
    elseif (isset($_POST['action']) && $_POST['action'] == 'edit') {
        $id = (int)$_POST['id'];
        $username = mysqli_real_escape_string($koneksi, $_POST['username']);
        $nama_lengkap = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
        
        $query = "UPDATE users SET username='$username', nama_lengkap='$nama_lengkap'";
        
        // Update password jika diisi
        if (!empty($_POST['password'])) {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $query .= ", password='$password'";
        }
        
        $query .= " WHERE id=$id";
        
        if (mysqli_query($koneksi, $query)) {
            $message = "User berhasil diupdate!";
            $messageType = "success";
        } else {
            $message = "Gagal update user: " . mysqli_error($koneksi);
            $messageType = "danger";
        }
    }
    
    // DELETE USER
    elseif (isset($_POST['action']) && $_POST['action'] == 'delete') {
        $id = (int)$_POST['id'];
        // Prevent deleting self (optional but good practice)
        if ($id == $_SESSION['user_id']) {
            $message = "Anda tidak bisa menghapus akun sendiri!";
            $messageType = "warning";
        } else {
            if (mysqli_query($koneksi, "DELETE FROM users WHERE id=$id")) {
                $message = "User berhasil dihapus!";
                $messageType = "success";
            } else {
                $message = "Gagal menghapus user: " . mysqli_error($koneksi);
                $messageType = "danger";
            }
        }
    }
}

// Fetch Users
$result = mysqli_query($koneksi, "SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
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
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-align: center;
            -ms-flex-align: center;
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
            <a href="users.php" class="nav-link active"><i class="bi bi-people"></i> <span>Pengguna</span></a>
            <a href="files.php" class="nav-link"><i class="bi bi-folder2-open"></i> <span>Files</span></a>
            <a href="settings.php" class="nav-link"><i class="bi bi-gear"></i> <span>Pengaturan</span></a>
        </div>
        <div class="mt-auto mb-4">
             <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <button class="btn btn-outline-light mr-3 border-0" id="sidebarToggle">
                    <i class="bi bi-list h4 mb-0"></i>
                </button>
                <h2 class="mb-0">Manajemen Pengguna</h2>
            </div>
            
            <div>
               <button class="btn btn-primary" data-toggle="modal" data-target="#addModal">
                   <i class="bi bi-plus-lg mr-2"></i>Tambah User
               </button>
            </div>
        </div>

            <?php if($message): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            <?php endif; ?>

            <div class="card glass-card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-custom table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nama Lengkap</th>
                                    <th>Username</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_lengkap']); ?></td>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-info btn-action mr-1 text-white" 
                                                data-toggle="modal" 
                                                data-target="#editModal"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-nama="<?php echo htmlspecialchars($row['nama_lengkap']); ?>"
                                                data-username="<?php echo htmlspecialchars($row['username']); ?>">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                        
                                        <?php if($row['id'] != $_SESSION['user_id']): ?>
                                        <button class="btn btn-sm btn-danger btn-action" 
                                                data-toggle="modal" 
                                                data-target="#deleteModal"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-nama="<?php echo htmlspecialchars($row['nama_lengkap']); ?>">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </main>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Tambah User Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" name="nama_lengkap" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control bg-dark text-white border-secondary" name="password" required>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Edit User</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" name="nama_lengkap" id="edit_nama" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control bg-dark text-white border-secondary" name="username" id="edit_username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru <small class="text-muted">(Kosongkan jika tidak ingin mengubah)</small></label>
                        <input type="password" class="form-control bg-dark text-white border-secondary" name="password" placeholder="Isi untuk reset password">
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Konfirmasi Hapus</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_id">
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus user <strong id="delete_nama"></strong>?</p>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
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

    // Script untuk mengisi data ke modal Edit
    $('#editModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget)
        const id = button.data('id')
        const nama = button.data('nama')
        const username = button.data('username')
        
        $('#edit_id').val(id)
        $('#edit_nama').val(nama)
        $('#edit_username').val(username)
    })

    // Script untuk mengisi data ke modal Delete
    $('#deleteModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget)
        const id = button.data('id')
        const nama = button.data('nama')
        
        $('#delete_id').val(id)
        $('#delete_nama').text(nama)
    })
</script>

</body>
</html>
