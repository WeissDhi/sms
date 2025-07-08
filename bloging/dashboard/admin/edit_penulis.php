<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'];
$query = $conn->prepare("SELECT * FROM penulis WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$penulis = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $stmt = $conn->prepare("UPDATE penulis SET fname=?, username=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $fname, $username, $password, $id);
    } else {
        $stmt = $conn->prepare("UPDATE penulis SET fname=?, username=? WHERE id=?");
        $stmt->bind_param("ssi", $fname, $username, $id);
    }

    if ($stmt->execute()) {
        header("Location: penulis_management.php");
        exit;
    } else {
        $error = "Gagal mengupdate penulis!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Penulis</title>
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
                            <i class="bi bi-person-lines-fill text-white fs-2"></i>
                        </span>
                        <h2 class="fw-bold mb-0" style="letter-spacing:1px;">Edit Penulis</h2>
                        <p class="text-muted">Perbarui data penulis dengan benar</p>
                    </div>
                    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="fname" class="form-control rounded-3 shadow-sm" value="<?= htmlspecialchars($penulis['fname']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control rounded-3 shadow-sm" value="<?= htmlspecialchars($penulis['username']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password (kosongkan jika tidak ingin mengganti)</label>
                            <input type="password" name="password" class="form-control rounded-3 shadow-sm">
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 rounded-3 fw-semibold shadow-sm" style="transition:0.2s;">Update</button>
                        <a href="penulis_management.php" class="btn btn-outline-secondary w-100 mt-2 rounded-3 shadow-sm" style="transition:0.2s;">Kembali</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
