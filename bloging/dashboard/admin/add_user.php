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
        $stmt = $conn->prepare("INSERT INTO users (fname, username, password) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $fname, $username, $password);

        if ($stmt->execute()) {
            header("Location: users_management.php");
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
</head>

<body class="bg-light">
    <div class="container py-4">
        <h2>Tambah Pengguna Baru</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="post">
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="fname" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Tambah User</button>
            <a href="users_management.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>

</html>
