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
    SELECT c.*, 
           CASE 
               WHEN a.id IS NOT NULL THEN a.first_name
               ELSE u.fname 
           END as user_name,
           b.title as post_title,
           c.created_at
    FROM comment c 
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN admin a ON c.user_id = a.id
    JOIN blogs b ON c.blog_id = b.id 
    WHERE c.status = 'active'
    ORDER BY c.created_at DESC 
    LIMIT 5
");

if (!$recent_comments) {
    die("Error in recent comments query: " . $conn->error);
}

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
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.9;
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 12px;
        }

        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
            scrollbar-width: thin;
        }

        .recent-activity::-webkit-scrollbar {
            width: 6px;
        }

        .recent-activity::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        .chart-container {
            position: relative;
            height: 350px;
            padding: 1rem;
        }

        .dashboard-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .dashboard-card .card-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f8f9fa;
        }

        .activity-item {
            transition: all 0.2s ease;
            border-radius: 10px;
            padding: 0.75rem;
        }

        .activity-item:hover {
            background-color: #f8f9fa;
        }

        .badge-view {
            font-size: 0.85rem;
            padding: 0.5rem 0.75rem;
            border-radius: 20px;
        }

        .home-btn {
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-bottom: 2rem;
        }

        .home-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="bg-light">
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>

    <div class="container-fluid py-5 px-4">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <h2 class="mb-0 fw-bold text-dark">Dashboard Admin</h2>
                <a href="../../../index.php" class="btn btn-outline-primary home-btn d-inline-flex align-items-center gap-2">
                    <i class="bi bi-house-door-fill"></i> Kembali ke Home
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card stat-card bg-primary bg-gradient text-white h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-2">Total Pengguna</h6>
                                <h2 class="mb-0 fw-bold"><?= number_format($stats['total_users']) ?></h2>
                            </div>
                            <i class="bi bi-people-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card stat-card bg-success bg-gradient text-white h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-2">Total Artikel</h6>
                                <h2 class="mb-0 fw-bold"><?= number_format($stats['total_articles']) ?></h2>
                                <div class="mt-2">
                                    <span class="badge bg-white bg-opacity-25 me-2">Published: <?= number_format($stats['published_articles']) ?></span>
                                    <span class="badge bg-white bg-opacity-25">Draft: <?= number_format($stats['draft_articles']) ?></span>
                                </div>
                            </div>
                            <i class="bi bi-file-text-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card stat-card bg-info bg-gradient text-white h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-2">Total Komentar</h6>
                                <h2 class="mb-0 fw-bold"><?= number_format($stats['total_comments']) ?></h2>
                            </div>
                            <i class="bi bi-chat-dots-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card stat-card bg-warning bg-gradient text-white h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-white-50 mb-2">Total Likes</h6>
                                <h2 class="mb-0 fw-bold"><?= number_format($stats['total_likes']) ?></h2>
                            </div>
                            <i class="bi bi-heart-fill stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Articles by Category Chart -->
            <div class="col-12 col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title d-flex align-items-center">
                            <i class="bi bi-pie-chart-fill me-2 text-primary"></i>
                            Artikel per Kategori
                        </h5>
                        <div class="chart-container">
                            <canvas id="categoryChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Comments -->
            <div class="col-12 col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title d-flex align-items-center">
                            <i class="bi bi-chat-square-text-fill me-2 text-primary"></i>
                            Komentar Terbaru
                        </h5>
                        <div class="recent-activity">
                            <?php while ($comment = $recent_comments->fetch_assoc()): ?>
                                <div class="activity-item mb-3">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <div class="bg-light rounded-circle p-2">
                                                <i class="bi bi-person-circle fs-4 text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($comment['user_name']) ?></h6>
                                                <small class="text-muted"><?= date('d M Y H:i', strtotime($comment['created_at'])) ?></small>
                                            </div>
                                            <p class="mb-1 text-dark"><?= htmlspecialchars($comment['comment']) ?></p>
                                            <small class="text-muted d-block">
                                                <i class="bi bi-link-45deg"></i>
                                                <?= strip_tags($comment['post_title']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Most Viewed Articles -->
            <div class="col-12 col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title d-flex align-items-center">
                            <i class="bi bi-graph-up-arrow me-2 text-primary"></i>
                            Artikel Terpopuler
                        </h5>
                        <div class="recent-activity">
                            <?php while ($article = $most_viewed->fetch_assoc()): ?>
                                <div class="activity-item mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-semibold"><?= strip_tags($article['title']) ?></h6>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-primary badge-view">
                                                    <i class="bi bi-eye-fill me-1"></i>
                                                    <?= number_format($article['views']) ?> views
                                                </span>
                                                <span class="badge bg-<?= $article['status'] === 'published' ? 'success' : 'warning' ?> bg-opacity-10 text-<?= $article['status'] === 'published' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($article['status']) ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                <i class="bi bi-person-fill me-1"></i>
                                                <?= htmlspecialchars($article['author_name']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Articles -->
            <div class="col-12 col-lg-6">
                <div class="card dashboard-card h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title d-flex align-items-center">
                            <i class="bi bi-clock-history me-2 text-primary"></i>
                            Artikel Terbaru
                        </h5>
                        <div class="recent-activity">
                            <?php while ($article = $recent_articles->fetch_assoc()): ?>
                                <div class="activity-item mb-3">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-semibold"><?= strip_tags($article['title']) ?></h6>
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar3 me-1"></i>
                                                    <?= date('d M Y', strtotime($article['created_at'])) ?>
                                                </small>
                                                <span class="badge bg-<?= $article['status'] === 'published' ? 'success' : 'warning' ?> bg-opacity-10 text-<?= $article['status'] === 'published' ? 'success' : 'warning' ?>">
                                                    <?= ucfirst($article['status']) ?>
                                                </span>
                                            </div>
                                            <small class="text-muted">
                                                <i class="bi bi-person-fill me-1"></i>
                                                <?= htmlspecialchars($article['author_name']) ?>
                                            </small>
                                        </div>
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