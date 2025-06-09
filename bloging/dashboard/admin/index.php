<?php
session_start();
include '../../config.php';

// Cek apakah login sebagai admin
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Get statistics
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_admins' => $conn->query("SELECT COUNT(*) as count FROM admin")->fetch_assoc()['count'],
    'total_articles' => $conn->query("SELECT COUNT(*) as count FROM blogs")->fetch_assoc()['count'],
    'published_articles' => $conn->query("SELECT COUNT(*) as count FROM blogs WHERE status = 'published'")->fetch_assoc()['count'],
    'draft_articles' => $conn->query("SELECT COUNT(*) as count FROM blogs WHERE status = 'draft'")->fetch_assoc()['count'],
    'total_comments' => $conn->query("SELECT COUNT(*) as count FROM comment")->fetch_assoc()['count'],
    'total_likes' => $conn->query("SELECT COUNT(*) as count FROM post_like")->fetch_assoc()['count']
];

// Get articles by category
$category_stats = $conn->query("
    SELECT c.category, COUNT(b.id) as article_count 
    FROM category c 
    LEFT JOIN blogs b ON c.id = b.category_id 
    GROUP BY c.id 
    ORDER BY article_count DESC
");

// Get recent comments with user info
$recent_comments = $conn->query("
    SELECT c.*, u.fname as user_name, b.title as post_title 
    FROM comment c 
    JOIN users u ON c.user_id = u.id 
    JOIN blogs b ON c.post_id = b.id 
    ORDER BY c.crated_at DESC 
    LIMIT 5
");

// Get most viewed articles
$most_viewed = $conn->query("
    SELECT b.*, 
           CASE 
               WHEN b.author_type = 'admin' THEN a.first_name
               ELSE u.fname 
           END as author_name
    FROM blogs b
    LEFT JOIN admin a ON b.author_type = 'admin' AND b.author_id = a.id
    LEFT JOIN users u ON b.author_type = 'user' AND b.author_id = u.id
    ORDER BY b.views DESC 
    LIMIT 5
");

// Get recent articles
$recent_articles = $conn->query("
    SELECT b.*, 
           CASE 
               WHEN b.author_type = 'admin' THEN a.first_name
               ELSE u.fname 
           END as author_name
    FROM blogs b
    LEFT JOIN admin a ON b.author_type = 'admin' AND b.author_id = a.id
    LEFT JOIN users u ON b.author_type = 'user' AND b.author_id = u.id
    ORDER BY b.created_at DESC 
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            opacity: 0.8;
        }
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>

<body class="bg-light">
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>

    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="mb-4">Dashboard Admin</h2>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Pengguna</h6>
                                <h2 class="mb-0"><?= $stats['total_users'] ?></h2>
                            </div>
                            <i class="bi bi-people-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Artikel</h6>
                                <h2 class="mb-0"><?= $stats['total_articles'] ?></h2>
                                <small>Published: <?= $stats['published_articles'] ?> | Draft: <?= $stats['draft_articles'] ?></small>
                            </div>
                            <i class="bi bi-file-text-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Komentar</h6>
                                <h2 class="mb-0"><?= $stats['total_comments'] ?></h2>
                            </div>
                            <i class="bi bi-chat-dots-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <div class="card stat-card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title">Total Likes</h6>
                                <h2 class="mb-0"><?= $stats['total_likes'] ?></h2>
                            </div>
                            <i class="bi bi-heart-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Articles by Category Chart -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Artikel per Kategori</h5>
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Comments -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Komentar Terbaru</h5>
                        <div class="recent-activity">
                            <?php while ($comment = $recent_comments->fetch_assoc()): ?>
                                <div class="d-flex mb-3 pb-3 border-bottom">
                                    <div class="flex-shrink-0">
                                        <i class="bi bi-person-circle fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?= htmlspecialchars($comment['user_name']) ?></h6>
                                            <small class="text-muted"><?= date('d M Y H:i', strtotime($comment['crated_at'])) ?></small>
                                        </div>
                                        <p class="mb-1"><?= htmlspecialchars($comment['comment']) ?></p>
                                        <small class="text-muted">Pada artikel: <?= htmlspecialchars($comment['post_title']) ?></small>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Most Viewed Articles -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Artikel Terpopuler</h5>
                        <div class="recent-activity">
                            <?php while ($article = $most_viewed->fetch_assoc()): ?>
                                <div class="d-flex mb-3 pb-3 border-bottom">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?= strip_tags($article['title']) ?></h6>
                                            <span class="badge bg-primary"><?= number_format($article['views']) ?> views</span>
                                        </div>
                                        <p class="mb-1 small text-muted">
                                            Oleh: <?= htmlspecialchars($article['author_name']) ?> | 
                                            Status: <?= ucfirst($article['status']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Articles -->
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Artikel Terbaru</h5>
                        <div class="recent-activity">
                            <?php while ($article = $recent_articles->fetch_assoc()): ?>
                                <div class="d-flex mb-3 pb-3 border-bottom">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between">
                                            <h6 class="mb-1"><?= strip_tags($article['title']) ?></h6>
                                            <small class="text-muted"><?= date('d M Y', strtotime($article['created_at'])) ?></small>
                                        </div>
                                        <p class="mb-1 small text-muted">
                                            Oleh: <?= htmlspecialchars($article['author_name']) ?> | 
                                            Status: <?= ucfirst($article['status']) ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Prepare data for category chart
        const categoryData = {
            labels: [
                <?php 
                $category_stats->data_seek(0);
                while ($cat = $category_stats->fetch_assoc()) {
                    echo "'" . addslashes($cat['category']) . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Jumlah Artikel',
                data: [
                    <?php 
                    $category_stats->data_seek(0);
                    while ($cat = $category_stats->fetch_assoc()) {
                        echo $cat['article_count'] . ",";
                    }
                    ?>
                ],
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40',
                    '#8AC249',
                    '#EA526F',
                    '#23B5D3',
                    '#279AF1',
                    '#7E52A0'
                ]
            }]
        };

        // Create category chart
        const ctx = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: categoryData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });
    </script>
</body>
</html>