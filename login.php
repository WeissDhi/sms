<?php 
session_start();
include './bloging/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek admin
    $admin_query = $conn->prepare("SELECT * FROM admin WHERE username = ? AND password = ?");
    $admin_query->bind_param("ss", $username, $password);
    $admin_query->execute();
    $admin_result = $admin_query->get_result();

    if ($admin_result->num_rows > 0) {
        $admin = $admin_result->fetch_assoc();
        $_SESSION['author_id'] = $admin['id'];
        $_SESSION['author_type'] = 'admin';
        $_SESSION['username'] = $admin['username'];
        header("Location: index.php");
        exit;
    }

    // Cek user biasa
    $user_query = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $user_query->bind_param("ss", $username, $password);
    $user_query->execute();
    $user_result = $user_query->get_result();

    if ($user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $_SESSION['author_id'] = $user['id'];
        $_SESSION['author_type'] = 'user';
        $_SESSION['username'] = $user['username'];
        header("Location: index.php");
        exit;
    }

    $error = "Username atau password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Bloging</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: rgb(255, 255, 255);
        }

        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .login-form {
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

        .login-form,
        .illustration {
            transition: all 0.8s ease;
        }

        .btn-primary:hover {
            background-color: #75a728;
            border-color: #75a728;
            transform: scale(1.02);
            transition: all 0.3s ease;
        }

        @media (max-width: 992px) {
            .divider {
                display: none;
            }

            .login-form {
                padding: 2rem;
            }

            .logo {
                width: 120px;
            }
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="row w-100 justify-content-center align-items-center">
            <!-- Gambar Ilustrasi -->
            <div class="col-lg-5 d-none d-lg-flex flex-column align-items-center">
                <img src="img/login_blog.png" alt="Ilustrasi Login" class="illustration img-fluid">
            </div>

            <!-- Pembatas -->
            <div class="col-lg-1 d-none d-lg-flex justify-content-center">
                <div class="divider"></div>
            </div>

            <!-- Form Login -->
            <div class="col-lg-5 col-md-8">
                <div class="login-form text-center">
                    <img src="img/sms.png" alt="Logo" class="logo">
                    <h4 class="mb-4">Selamat Datang Kembali</h4>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger text-start"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST" class="text-start">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" id="username" name="username" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" id="password" name="password" class="form-control" required>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                        <div class="text-center">
                            <p class="mb-0">Belum punya akun? <a href="register.php" class="text-decoration-none">Daftar di sini</a>.</p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const illustration = document.querySelector(".illustration");
            const form = document.querySelector(".login-form");

            // Awal kondisi tersembunyi
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
