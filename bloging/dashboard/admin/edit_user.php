<?php
session_start();
include '../../config.php';

if (!isset($_GET['id'])) {
    header('Location: user_management.php');
    exit;
}
$id = (int)$_GET['id'];

// Ambil data pengguna
$stmt = $conn->prepare("SELECT username FROM pengguna WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password']; // tanpa hash
    $update = $conn->prepare("UPDATE pengguna SET password = ? WHERE id = ?");
    $update->bind_param('si', $password, $id);
    if ($update->execute()) {
        $_SESSION['success'] = 'Password pengguna berhasil diupdate!';
        header('Location: user_management.php');
        exit;
    } else {
        $_SESSION['error'] = 'Gagal update password!';
    }
    $update->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Password Pengguna</title>
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
                        <h3 class="mb-4">Edit Password Pengguna</h3>
                        <?php if(isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
                        <?php endif; ?>
                        <form method="post" autocomplete="off">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" class="form-control" value="<?= htmlspecialchars($username) ?>" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="text" class="form-control" id="password" name="password" required>
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
</body>
</html> 