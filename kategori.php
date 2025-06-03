<?php
session_start();
include './bloging/config.php';

$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Ambil nama kategori
$categoryQuery = $conn->prepare("SELECT category FROM category WHERE id = ?");
$categoryQuery->bind_param("i", $categoryId);
$categoryQuery->execute();
$categoryResult = $categoryQuery->get_result();
$categoryRow = $categoryResult->fetch_assoc();

$categoryName = $categoryRow ? $categoryRow['category'] : 'Tidak Diketahui';

// Ambil semua artikel yang memiliki kategori ini
$stmt = $conn->prepare("
    SELECT blogs.*, 
           category.category AS category_name,
           admin.first_name AS admin_first,
           admin.last_name AS admin_last,
           users.fname AS user_name
    FROM blogs
    LEFT JOIN category ON blogs.category_id = category.id
    LEFT JOIN admin ON blogs.author_type = 'admin' AND blogs.author_id = admin.id
    LEFT JOIN users ON blogs.author_type = 'user' AND blogs.author_id = users.id
    WHERE blogs.status = 'published' AND blogs.category_id = ?
    ORDER BY blogs.created_at DESC
");
$stmt->bind_param("i", $categoryId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Artikel Kategori <?= htmlspecialchars($categoryName) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/linearicons.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/bootstrap.css" />
    <link rel="stylesheet" href="css/owl.carousel.css" />
    <link rel="stylesheet" href="css/main.css" />
</head>
<body>

<?php include './components/navbar.php'; ?>

<div class="container py-4">
    <h2 class="mb-4">Artikel dalam Kategori: <?= htmlspecialchars($categoryName) ?></h2>
    <div class="row">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <?php if ($row['image']): ?>
                            <img src="bloging/uploads/<?= basename($row['image']) ?>" class="card-img-top" alt="Blog Image">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5><?= strip_tags($row['title']) ?></h5>
                            <p class="text-muted mb-1"><small>Kategori: <?= htmlspecialchars($row['category_name']) ?></small></p>
                            <p class="text-muted mb-1"><small>
                                Penulis: 
                                <?= $row['author_type'] === 'admin' 
                                    ? htmlspecialchars($row['admin_first'] . ' ' . $row['admin_last']) 
                                    : htmlspecialchars($row['user_name']) ?>
                            </small></p>
                            <p class="card-text"><?= mb_strimwidth(strip_tags($row['content']), 0, 120, '...') ?></p>
                            <a href="view_detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary mt-auto">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p class="text-muted">Belum ada artikel di kategori ini.</p>
        <?php endif; ?>
    </div>
</div>

<?php include './components/footer.php'; ?>
</body>
</html>
