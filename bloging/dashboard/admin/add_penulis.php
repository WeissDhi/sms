<?php
session_start();
include '../../config.php';

// Cek apakah login sebagai admin
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($fname) || empty($username) || empty($password)) {
        $error = "Semua kolom harus diisi!";
    } else {
        // Insert user ke database tanpa hashing password
        $stmt = $conn->prepare("INSERT INTO penulis (fname, username, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fname, $username, $password);

        if ($stmt->execute()) {
            header("Location: penulis_management.php");
            exit;
        } else {
            $error = "Gagal menambahkan user baru!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Tambah Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../../img/sms.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light" style="background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%); min-height:100vh;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0 rounded-4 p-4 animate__animated animate__fadeInDown" style="background:rgba(255,255,255,0.98);">
                    <div class="text-center mb-4">
                        <span class="icon-circle mb-2">
                            <i class="bi bi-person-plus text-white fs-2"></i>
                        </span>
                        <h2 class="fw-bold mb-0" style="letter-spacing:1px;">Tambah Penulis Baru</h2>
                        <p class="text-muted">Isi data penulis dengan lengkap</p>
                    </div>
                    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="fname" class="form-control rounded-3 shadow-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control rounded-3 shadow-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password" class="form-control rounded-start-3 shadow-sm border-end-0" required>
                                <span class="input-group-text bg-white border-start-0 rounded-end-3" id="togglePassword" style="cursor:pointer;">
                                    <i class="bi bi-eye" id="eyeIcon"></i>
                                </span>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold shadow-sm" style="transition:0.2s;">Tambah Penulis</button>
                        <a href="penulis_management.php" class="btn btn-secondary w-100 mt-2 rounded-3 shadow-sm" style="transition:0.2s;">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.classList.toggle('bi-eye');
            eyeIcon.classList.toggle('bi-eye-slash');
        });
    </script>
    <style>
        .input-group .input-group-text {
            border-left: 0 !important;
        }
        .input-group .form-control:focus {
            z-index: 2;
        }
        .icon-circle {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #2196f3, #1976d2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem auto;
        }
        .icon-circle i {
            color: #fff;
            font-size: 2.5rem;
        }
    </style>
</body>

</html>
