<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = trim($_POST['fname']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Cek apakah username sudah ada
    $check = $conn->prepare("SELECT id FROM penulis WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $error = "Username sudah digunakan!";
    } else {
        // Insert user baru
        $stmt = $conn->prepare("INSERT INTO penulis (fname, username, password) VALUES (?, ?, ?)");
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
    <title>Registrasi User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center">Registrasi</h2>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php elseif (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?> <a href="login.php">Login di sini</a></div>
                <?php endif; ?>
                <form method="POST">
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
                    <button type="submit" class="btn btn-success w-100">Daftar</button>
                </form>
                <p class="mt-3 text-center">Sudah punya akun? <a href="login.php">Login di sini</a>.</p>
            </div>
        </div>
    </div>
</body>
</html>
