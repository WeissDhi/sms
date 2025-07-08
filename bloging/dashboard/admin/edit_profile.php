<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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
<body class="bg-light" style="background: linear-gradient(135deg, #e0eafc 0%, #cfdef3 100%); min-height:100vh;">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0 rounded-4 p-4 animate__animated animate__fadeInDown" style="background:rgba(255,255,255,0.98);">
                    <div class="text-center mb-4">
                        <span class="d-inline-block bg-primary bg-gradient rounded-circle p-3 mb-2">
                            <i class="bi bi-person-gear text-white fs-2"></i>
                        </span>
                        <h2 class="fw-bold mb-0" style="letter-spacing:1px;">Edit Profil Admin</h2>
                        <p class="text-muted">Ubah password admin dengan aman</p>
                    </div>
                    <?php if ($success) echo "<div class='alert alert-success'>$success</div>"; ?>
                    <?php if ($error) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control rounded-3 shadow-sm" value="<?= htmlspecialchars($first_name . ' ' . $last_name) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control rounded-3 shadow-sm" value="<?= htmlspecialchars($username) ?>" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="new_password" class="form-control rounded-3 shadow-sm" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control rounded-3 shadow-sm" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold shadow-sm" style="transition:0.2s;">Ubah Password</button>
                        <a href="index.php" class="btn btn-outline-secondary w-100 mt-2 rounded-3 shadow-sm" style="transition:0.2s;">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 