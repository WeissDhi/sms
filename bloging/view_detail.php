<?php
include 'config.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

// Get blog details
$stmt = $conn->prepare("
    SELECT b.*, 
           c.category as category_name,
           CASE 
               WHEN b.author_type = 'admin' THEN a.first_name
               ELSE u.fname 
           END as author_name
    FROM blogs b
    LEFT JOIN category c ON b.category_id = c.id
    LEFT JOIN admin a ON b.author_type = 'admin' AND b.author_id = a.id
    LEFT JOIN users u ON b.author_type = 'user' AND b.author_id = u.id
    WHERE b.id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();

if (!$blog) {
    header("Location: index.php");
    exit;
}

// Increment view count via AJAX
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= strip_tags($blog['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Increment view count when page loads
        window.addEventListener('load', function() {
            fetch('increment_view.php?id=<?= $id ?>', {
                method: 'GET',
                credentials: 'same-origin'
            });
        });
    </script>
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="card">
            <div class="card-body">
                <h1 class="card-title mb-4"><?= strip_tags($blog['title']) ?></h1>
                
                <div class="d-flex gap-3 mb-4">
                    <span class="badge bg-primary">
                        <i class="bi bi-eye-fill"></i> <?= number_format($blog['views']) ?> views
                    </span>
                    <span class="badge bg-secondary">
                        <i class="bi bi-folder-fill"></i> <?= htmlspecialchars($blog['category_name']) ?>
                    </span>
                    <span class="badge bg-<?= $blog['status'] === 'published' ? 'success' : 'warning' ?>">
                        <i class="bi bi-<?= $blog['status'] === 'published' ? 'check-circle' : 'clock' ?>-fill"></i>
                        <?= ucfirst($blog['status']) ?>
                    </span>
                </div>

                <?php if (!empty($blog['image'])): ?>
                    <img src="uploads/<?= htmlspecialchars($blog['image']) ?>" alt="Blog Thumbnail" class="img-fluid rounded mb-4" style="max-height: 400px; width: auto;">
                <?php endif; ?>

                <div class="blog-content mb-4">
                    <?= $blog['content'] ?>
                </div>

                <div class="text-muted">
                    <small>
                        <i class="bi bi-person-fill"></i> <?= htmlspecialchars($blog['author_name']) ?> |
                        <i class="bi bi-calendar3"></i> <?= date('d M Y H:i', strtotime($blog['created_at'])) ?>
                    </small>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="index.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali ke Daftar Artikel
            </a>
        </div>
    </div>
</body>
</html> 