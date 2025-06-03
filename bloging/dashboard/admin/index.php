<?php
session_start();
include '../../config.php';

// Cek apakah login sebagai admin
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Ambil semua artikel
$result = $conn->query("
    SELECT blogs.*, 
           category.category AS category_name,
           users.fname AS user_name,
           admin.first_name AS admin_first,
           admin.last_name AS admin_last
    FROM blogs
    LEFT JOIN category ON blogs.category_id = category.id
    LEFT JOIN users ON blogs.author_type = 'user' AND blogs.author_id = users.id
    LEFT JOIN admin ON blogs.author_type = 'admin' AND blogs.author_id = admin.id
    ORDER BY blogs.created_at DESC
");
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
        <p>Kelola seluruh artikel dan pengguna.</p>

        <div class="mb-4 d-flex justify-content-between">
            <a href="../../add_blog.php" class="btn btn-success">+ Tulis Artikel Baru</a>
            <a href="../../../index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                <i class="bi bi-house-door-fill"></i> HOME
            </a>

        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-secondary">
                    <tr>
                        <th>Judul</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Gambar</th>
                        <th>Penulis</th>
                        <th>Tanggal Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['title']) ?></td>
                            <td><?= htmlspecialchars($row['category_name']) ?></td>
                            <td><?= ucfirst($row['status']) ?></td>
                            <td style="max-width: 150px;">
                                <?php
                                $imagePath = "../../" . $row['image']; // sudah termasuk 'uploads/xxx.png'
                                if (!empty($row['image']) && file_exists($imagePath)): ?>
                                    <img src="<?= $imagePath ?>" alt="Thumbnail" class="img-thumbnail" style="max-width: 100px;">
                                <?php else: ?>
                                    <span class="text-muted">Tidak ada gambar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= $row['author_type'] === 'admin'
                                    ? htmlspecialchars($row['admin_first'] . ' ' . $row['admin_last'])
                                    : htmlspecialchars($row['user_name']) ?>
                            </td>
                            <td><?= $row['created_at'] ?></td>
                            <td>
                                <a href="../../edit_blog.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-warning">Edit</a>
                                <a href="../../delete_blog.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                                <a href="../../../view_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-info">Lihat</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>

            </table>
        </div>
    </div>
</body>

</html>