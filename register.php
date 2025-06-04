<?php
session_start();
include './bloging/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Cek apakah username sudah ada
    $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Insert user baru
        $stmt = $conn->prepare("INSERT INTO users (fname, username, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fname, $username, $password);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: rgb(255, 255, 255);
        }

        .register-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .register-form {
            background: #fff;
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.05);
            border: 3px solid #8fc333;
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
            border-color: #8fc333;
            box-shadow: 0 0 0 0.2rem rgba(143, 195, 51, 0.25);
            transition: 0.3s;
        }

        .divider {
            border-left: 2px solid #dee2e6;
            height: 80%;
            margin: auto;
        }

        .register-form,
        .illustration {
            transition: all 0.8s ease;
        }

        .btn-success {
            background-color: #0d6efd;
            /* warna biru Bootstrap default */
            border-color: #0d6efd;
            color: white;
        }

        .btn-success:hover {
            background-color: #8fc333;
            /* warna hijau */
            border-color: #8fc333;
            transform: scale(1.02);
            transition: all 0.3s ease;
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