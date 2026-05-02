<?php
session_start();
require 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) === 1) {
        $row = mysqli_fetch_assoc($result);
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['nama_lengkap'] = $row['nama_lengkap'];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Password salah!";
        }
    } else {
        $error = "Username tidak ditemukan!";
    }
}
?>
<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AD Update Login</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Google Fonts (Poppins) -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #0f2027;
            /* Fallback */
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            color: #f0f0f0;
            /* Light text */
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background: rgba(33, 37, 41, 0.8);
            backdrop-filter: blur(10px);
            padding: 1rem;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        header h1 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .login-card {
            background: rgba(33, 37, 41, 0.7);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .form-floating>.form-control:focus~label,
        .form-floating>.form-control:not(:placeholder-shown)~label {
            color: #a78bfa;
        }

        .form-control:focus {
            border-color: #a78bfa;
            box-shadow: 0 0 0 0.25rem rgba(167, 139, 250, 0.25);
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
        }

        .btn-primary-custom {
            background-color: #a78bfa;
            border: none;
            padding: 12px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            background-color: #8b5cf6;
            box-shadow: 0 5px 15px rgba(139, 92, 246, 0.4);
        }

        .brand-header {
            text-align: center;
            color: #fff;
        }

        .password-field {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            z-index: 10;
        }

        .alert-custom {
            border-radius: 10px;
        }

        footer {
            background-color: #2c2c2c;
            padding: 1rem;
            text-align: center;
            font-size: 0.9rem;
            margin-top: auto;
        }

        header {
            background-color: #2c2c2c;
            padding: 1rem;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        header h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        main {
            flex-grow: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
    </style>
</head>

<body>
    <header>
        <h1>AD UPDATE</h1>
    </header>
    <main>
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-4">
                    <div class="card login-card p-4">
                        <div class="card-body">

                            <!-- Header -->
                            <div class="brand-header mb-4">
                                <h3>Login</h3>
                            </div>

                            <!-- Alert Message -->
                            <?php if ($error): ?>
                                <div id="loginAlert" class="alert alert-danger alert-custom mb-3 d-block" role="alert">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Form Start -->
                            <form id="loginForm" method="POST" action="" class="needs-validation" novalidate>

                                <!-- Username Input with Floating Label -->
                                <div class="form-floating mb-3">
                                    <input type="text" class="form-control" name="username" id="floatingInput"
                                        placeholder="Username" required>
                                    <label for="floatingInput">Username</label>
                                    <div class="invalid-feedback">
                                        Username tidak boleh kosong.
                                    </div>
                                </div>

                                <!-- Password Input with Floating Label & Toggle -->
                                <div class="form-floating mb-3 password-field">
                                    <input type="password" class="form-control" name="password" id="floatingPassword"
                                        placeholder="Password" required>
                                    <label for="floatingPassword">Kata Sandi</label>
                                    <i class="bi bi-eye-slash toggle-password" id="togglePassword"></i>
                                    <div class="invalid-feedback">
                                        Kata sandi tidak boleh kosong.
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button class="btn btn-primary-custom w-100 mb-3" type="submit">
                                    Login
                                </button>
                            </form>
                            <!-- Form End -->


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <footer>
        <p>&copy; <?php echo date('Y'); ?> AD UPDATE || Created by <a>ME</a></p>
    </footer>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Script untuk Toggle Password Visibility
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#floatingPassword');

        togglePassword.addEventListener('click', function (e) {
            // Toggle tipe attribute
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);

            // Toggle ikon mata
            this.classList.toggle('bi-eye');
            this.classList.toggle('bi-eye-slash');
        });

        // Script Validasi Bootstrap
        (() => {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')

            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>

</html>