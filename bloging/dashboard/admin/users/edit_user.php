<?php
session_start();
include '../../../config.php';

if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

$id = $_GET['id'];
$query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fname = $_POST['fname'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET fname=?, username=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $fname, $username, $hashed, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET fname=?, username=? WHERE id=?");
        $stmt->bind_param("ssi", $fname, $username, $id);
    }

    if ($stmt->execute()) {
        header("Location: user_management.php");
        exit;
    } else {
        $error = "Gagal mengupdate user!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Edit Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-4">
        <h2>Edit Pengguna</h2>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="post">
            <div class="mb-3">
                <label>Nama Lengkap</label>
                <input type="text" name="fname" class="form-control" value="<?= htmlspecialchars($user['fname']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label>Password (kosongkan jika tidak ingin mengganti)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
            <a href="user_management.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>
</body>

</html>
