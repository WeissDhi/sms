<?php
session_start();
include '../../config.php';

// Cek apakah login sebagai admin
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Ambil semua user
$result = $conn->query("SELECT * FROM users ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body class="bg-light">
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>
    <div class="container py-4">
        <h2>Dashboard Admin</h2>
        <p>Kelola seluruh pengguna dan artikel.</p>

        <div class="mb-4 d-flex justify-content-between">
            <!-- Tombol Add User -->
            <a href="add_user.php" class="btn btn-success">+ Tambah User Baru</a>
            <a href="../../../index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="bi bi-house-door-fill"></i> HOME
            </a>
        </div>

        <!-- Daftar Pengguna -->
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-secondary">
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Username</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= htmlspecialchars($row['fname']) ?></td>
                            <td><?= htmlspecialchars($row['username']) ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="delete_user.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>