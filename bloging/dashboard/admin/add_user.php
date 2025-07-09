<?php
session_start();
include '../../config.php';

// Proses simpan pengguna baru
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password']; // tanpa hash

    // Cek username sudah ada
    $query = "SELECT id FROM pengguna WHERE username = ?";
    $cek = $conn->prepare($query);
    if (!$cek) {
        die("Query error: " . $conn->error . ' | Query: ' . $query);
    }
    $cek->bind_param('s', $username);
    $cek->execute();
    $cek->store_result();
    if ($cek->num_rows > 0) {
        $_SESSION['error'] = 'Username sudah terdaftar!';
    } else {
        $stmt = $conn->prepare("INSERT INTO pengguna (nama, username, password) VALUES (?, ?, ?)");
        if (!$stmt) {
            die("Query error: " . $conn->error);
        }
        $stmt->bind_param('sss', $nama, $username, $password);
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Pengguna berhasil ditambahkan!';
            header('Location: user_management.php');
            exit;
        } else {
            $_SESSION['error'] = 'Gagal menambah pengguna!';
        }
    }
    $cek->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengguna Baru</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../../img/sms.png" />
</head>
<body class="bg-light">
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h3 class="mb-4">Tambah Pengguna Baru</h3>
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <form method="post" autocomplete="off">
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control rounded-start-3 border-end-0" id="password" name="password" required>
                                    <span class="input-group-text bg-white border-start-0 rounded-end-3" id="togglePassword" style="cursor:pointer;">
                                        <i class="bi bi-eye" id="eyeIcon"></i>
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="user_management.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
                                <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
    </style>
</body>
</html> 