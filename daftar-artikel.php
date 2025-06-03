<?php
session_start();
include './bloging/config.php';

// Set link dashboard sesuai tipe author
$dashboardLink = '#';
$showDashboard = false;

if (isset($_SESSION['author_type'])) {
    $showDashboard = true;
    if ($_SESSION['author_type'] === 'admin') {
        $dashboardLink = './dashboard/admin/index.php';
    } elseif ($_SESSION['author_type'] === 'user') {
        $dashboardLink = './dashboard/users/index.php';
    }
}

// Query untuk mengambil blog dengan kategori dan nama author
$result = $conn->query("
    SELECT 
        blogs.*, 
        category.category AS category_name,
        admin.first_name AS admin_first,
        admin.last_name AS admin_last,
        users.fname AS user_name
    FROM blogs
    LEFT JOIN category ON blogs.category_id = category.id
    LEFT JOIN admin ON blogs.author_type = 'admin' AND blogs.author_id = admin.id
    LEFT JOIN users ON blogs.author_type = 'user' AND blogs.author_id = users.id
    WHERE blogs.status = 'published'
    ORDER BY blogs.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .card {
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 600;
        }

        .card-text {
            font-size: 0.95rem;
            color: #555;
        }
    </style>
    <link

        rel="stylesheet" />
    <!--CSS============================================= -->
    <link rel="stylesheet" href="css/linearicons.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/bootstrap.css" />
    <link rel="stylesheet" href="css/owl.carousel.css" />
    <link rel="stylesheet" href="css/main.css" />
</head>

<body>
    <?php include './components/navbar.php'; ?>
    <div class="container py-4">

        <div class="row">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <?php if ($row['image']): ?>
                            <img src="bloging/uploads/<?= basename($row['image']) ?>" class="card-img-top" alt="Blog Image">

                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5><?= strip_tags($row['title']) ?></h5>

                            <?php if (!empty($row['category_name'])): ?>
                                <p class="text-muted mb-1"><small>Kategori: <?= htmlspecialchars($row['category_name']) ?></small></p>
                            <?php endif; ?>

                            <p class="text-muted mb-1">
                                <small>
                                    Penulis:
                                    <?php if ($row['author_type'] === 'admin'): ?>
                                        <?= htmlspecialchars($row['admin_first'] . ' ' . $row['admin_last']) ?>
                                    <?php elseif ($row['author_type'] === 'user'): ?>
                                        <?= htmlspecialchars($row['user_name']) ?>
                                    <?php else: ?>
                                        Tidak diketahui
                                    <?php endif; ?>
                                </small>
                            </p>

                            <p class="card-text">
                                <?= mb_strimwidth(strip_tags($row['content']), 0, 120, '...') ?>
                            </p>

                            <a href="view_detail.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary mt-auto">Baca Selengkapnya</a>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <?php include './components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>