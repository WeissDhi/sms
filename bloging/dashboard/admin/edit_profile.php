<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Ambil data admin yang sedang login
$admin_id = $_SESSION['author_id'];
$stmt = $conn->prepare("SELECT username, first_name, last_name FROM admin WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$stmt->bind_result($username, $first_name, $last_name);
$stmt->fetch();
$stmt->close();

$success = $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    if (empty($new_password) || empty($confirm_password)) {
        $error = "Password tidak boleh kosong.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        $update = $conn->prepare("UPDATE admin SET password=? WHERE id=?");
        $update->bind_param("si", $new_password, $admin_id);
        if ($update->execute()) {
            $success = "Password berhasil diubah.";
        } else {
            $error = "Gagal mengubah password.";
        }
        $update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="shortcut icon" href="../../../img/sms.png" />
</head>
<body class="bg-light">
    <div class="container py-4">
        <h2>Edit Profile Admin</h2>
        <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
        <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="post">
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($first_name . ' ' . $last_name) ?>" disabled>
            </div>
            <div class="mb-3">
                <label>Username</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($username) ?>" disabled>
            </div>
            <div class="mb-3">
                <label>Password Baru</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Konfirmasi Password Baru</label>
                <input type="password" name="confirm_password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary">Ubah Password</button>
            <a href="index.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>
</html> 