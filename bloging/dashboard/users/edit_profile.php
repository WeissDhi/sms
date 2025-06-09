<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'user') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['author_id'];

// Ambil data user
$stmt = $conn->prepare("SELECT fname, username FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($fname, $username);
$stmt->fetch();
$stmt->close();

// Handle form update
$update_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $new_fname = trim($_POST['fname']);
        $new_username = trim($_POST['username']);

        $update_stmt = $conn->prepare("UPDATE users SET fname = ?, username = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $new_fname, $new_username, $user_id);
        if ($update_stmt->execute()) {
            $update_message = 'Profil berhasil diperbarui!';
            $fname = $new_fname;
            $username = $new_username;
        } else {
            $update_message = 'Gagal memperbarui profil.';
        }
        $update_stmt->close();
    }

    // Ubah password
    if (isset($_POST['update_password'])) {
        $new_password = password_hash($_POST['new_password'], PASSWORD_BCRYPT);

        $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $pass_stmt->bind_param("si", $new_password, $user_id);
        if ($pass_stmt->execute()) {
            $update_message = 'Password berhasil diubah!';
        } else {
            $update_message = 'Gagal mengubah password.';
        }
        $pass_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">
    <?php include '../components/navbar.php' ?>
    <?php include '../components/user-sidebar.php' ?>
    <div class="container py-4">
        <h2>Profil Saya</h2>
        <?php if ($update_message): ?>
            <div class="alert alert-info"><?= htmlspecialchars($update_message) ?></div>
        <?php endif; ?>

        <form method="POST" class="mb-4">
            <h5>Informasi Profil</h5>
            <div class="mb-3">
                <label for="fname" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" name="fname" id="fname" value="<?= htmlspecialchars($fname) ?>" required>
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Nama Pengguna</label>
                <input type="text" class="form-control" name="username" id="username" value="<?= htmlspecialchars($username) ?>" required>
            </div>
            <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan</button>

        </form>

        <form method="POST">
            <h5>Ubah Password</h5>
            <div class="mb-3">
                <label for="new_password" class="form-label">Password Baru</label>
                <input type="password" class="form-control" name="new_password" id="new_password" required minlength="6">
            </div>
            <button type="submit" name="update_password" class="btn btn-warning">Ubah Password</button>
            <button type="button" onclick="window.history.back();" class="btn btn-secondary">Kembali</button>
        </form>
    </div>
</body>

</html>