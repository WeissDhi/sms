<?php
session_start();
include '../../config.php';

// Cek apakah user login dan bertipe 'user'
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'user') {
    header("Location: ../../login.php");
    exit;
}

$author_id = $_SESSION['author_id'];

// Ambil artikel milik user ini
$query = $conn->prepare("SELECT * FROM blogs WHERE author_id = ? AND author_type = 'user' ORDER BY created_at DESC");
$query->bind_param("i", $author_id);
$query->execute();
$result = $query->get_result();

// Statistik
$count_query = $conn->prepare("SELECT status, COUNT(*) as total FROM blogs WHERE author_id = ? AND author_type = 'user' GROUP BY status");
$count_query->bind_param("i", $author_id);
$count_query->execute();
$count_result = $count_query->get_result();

$stats = ['draft' => 0, 'published' => 0];
while ($row = $count_result->fetch_assoc()) {
    $stats[$row['status']] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Pengguna</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>


<body class="bg-light">
    <?php include '../components/navbar.php' ?>
    <?php include '../components/user-sidebar.php' ?>
    <div class="container py-4">
        <h2>Dashboard Pengguna</h2>
        <p>Selamat datang! Berikut daftar artikel milikmu:</p>

        <div class="mb-4">
            <a href="../../add_blog.php" class="btn btn-primary">+ Tulis Artikel Baru</a>
            <a href="../../../index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="bi bi-house-door-fill"></i> HOME
            </a>
        </div>

        <!-- Statistik -->
        <div class="mb-3">
            <strong>Statistik:</strong>
            <p>Draft: <?= $stats['draft'] ?> | Published: <?= $stats['published'] ?></p>
        </div>

        <!-- Daftar Artikel -->
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="table-secondary">
                    <tr>
                        <th>Judul</th>
                        <th>Status</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($blog = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($blog['title']) ?></td>
                            <td><?= ucfirst($blog['status']) ?></td>
                            <td><?= $blog['created_at'] ?></td>
                            <td>
                                <a href="../../edit_blog.php?id=<?= $blog['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="../../delete_blog.php?id=<?= $blog['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                <a href="../../../view_detail.php?id=<?= $blog['id'] ?>" class="btn btn-sm btn-info">Lihat</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>