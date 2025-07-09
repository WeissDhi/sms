<?php
session_start();
include './bloging/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['fname']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);

    // Cek apakah username sudah ada di tabel pengguna
    $check = $conn->prepare("SELECT id FROM pengguna WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Insert pengguna baru
        $stmt = $conn->prepare("INSERT INTO pengguna (nama, username, password, email) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nama, $username, $password, $email);

        if ($stmt->execute()) {
            $success = "Registrasi berhasil! Silakan login.";
        } else {
            $error = "Terjadi kesalahan saat registrasi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Registrasi - Bloging</title>
    <link rel="shortcut icon" href="../img/sms.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #FAFAF0; /* Ivory */
        }

        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .register-form {
            background: #EEEEEE; /* Soft gray */
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(46, 125, 50, 0.08);
            border: 3px solid #2E7D32; /* Forest green */
        }

        .logo {
            width: 160px;
            margin-bottom: 1.2rem;
        }

        .illustration {
            width: 600px !important;
            max-width: unset !important;
            height: auto;
        }

        .form-control:focus {
            border-color: #2E7D32; /* Forest green */
            box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.18);
            transition: 0.3s;
        }

        .divider {
            border-left: 2px solid #C5E1A5; /* Lime */
            height: 80%;
            margin: auto;
        }

        .register-form,
        .illustration {
            transition: all 0.8s ease;
        }

        .btn-success {
            background: linear-gradient(135deg, #2E7D32, #C5E1A5); /* Forest green to lime */
            border: none;
            color: #fff;
            font-weight: 600;
            transition: background 0.3s, transform 0.3s;
        }
        .btn-success:hover {
            background: #2E7D32;
            border: none;
            color: #fff;
            transform: scale(1.02);
        }

        .register-form h4, .register-form label, .register-form p, .register-form .form-label {
            color: #2C2C2C; /* Charcoal */
        }

        .alert-danger {
            background: #F57C00;
            color: #fff;
            border: none;
        }
        .alert-success {
            background: #C5E1A5;
            color: #2E7D32;
            border: none;
        }

        @media (max-width: 992px) {
            .divider {
                display: none;
            }

            .register-form {
                padding: 2rem;
            }

            .logo {
                width: 120px;
            }
        }
    </style>
</head>

<body>

    <div class="container register-container">
        <div class="row w-100 justify-content-center align-items-center">
            <!-- KIRI -->
            <div class="col-lg-5 d-none d-lg-flex flex-column align-items-center">
                <img src="img/login_blog.png" alt="Ilustrasi Register" class="illustration img-fluid">
            </div>

            <!-- PEMBATAS -->
            <div class="col-lg-1 d-none d-lg-flex justify-content-center">
                <div class="divider"></div>
            </div>

            <!-- KANAN -->
            <div class="col-lg-5 col-md-8">
                <div class="register-form text-center">
                    <!-- Logo -->
                    <img src="img/sms.png" alt="Logo" class="logo">

                    <!-- Judul -->
                    <h4 class="mb-4">Buat Akun Baru</h4>

                    <!-- Notifikasi -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger text-start"><?= $error ?></div>
                    <?php elseif (isset($success)): ?>
                        <div class="alert alert-success text-start"><?= $success ?> <a href="login.php">Login di sini</a></div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form method="POST" class="text-start">
                        <div class="mb-3">
                            <label for="fname" class="form-label">Nama Lengkap</label>
                            <input type="text" id="fname" name="fname" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success">Daftar</button>
                        </div>
                        <div class="text-center">
                            <p class="mb-0">Sudah punya akun? <a href="login.php" class="text-decoration-none">Login di sini</a>.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const illustration = document.querySelector(".illustration");
            const form = document.querySelector(".register-form");

            illustration.style.opacity = 0;
            form.style.opacity = 0;
            illustration.style.transform = "translateY(20px)";
            form.style.transform = "translateY(20px)";

            setTimeout(() => {
                illustration.style.transition = "all 1s ease";
                form.style.transition = "all 1s ease";
                illustration.style.opacity = 1;
                illustration.style.transform = "translateY(0)";
                form.style.opacity = 1;
                form.style.transform = "translateY(0)";
            }, 200);
        });
    </script>

</body>

</html>