<?php
session_start();
include '../../config.php';

// Cek apakah login sebagai admin
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Get advanced statistics
$stats = [
    'total_users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'],
    'total_articles' => $conn->query("SELECT COUNT(*) as count FROM blogs")->fetch_assoc()['count'],
    'published_articles' => $conn->query("SELECT COUNT(*) as count FROM blogs WHERE status = 'published'")->fetch_assoc()['count'],
    'total_views' => $conn->query("SELECT SUM(views) as total FROM blogs")->fetch_assoc()['total'] ?? 0,
    'total_comments' => $conn->query("SELECT COUNT(*) as count FROM comment")->fetch_assoc()['count'],
    'total_likes' => $conn->query("SELECT COUNT(*) as count FROM post_like")->fetch_assoc()['count'],
    'active_users_7d' => $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM comment WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'],
    'new_articles_7d' => $conn->query("SELECT COUNT(*) as count FROM blogs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count']
];

// Get top contributors
$top_contributors = $conn->query("
    SELECT 
        CASE 
            WHEN b.author_type = 'admin' THEN a.first_name
            ELSE u.fname 
        END as author_name,
        COUNT(b.id) as article_count,
        SUM(b.views) as total_views,
        b.author_type
    FROM blogs b
    LEFT JOIN admin a ON b.author_type = 'admin' AND b.author_id = a.id
    LEFT JOIN users u ON b.author_type = 'user' AND b.author_id = u.id
    GROUP BY b.author_id, b.author_type
    ORDER BY article_count DESC, total_views DESC
    LIMIT 10
");

// Get articles by month (last 6 months)
$articles_by_month = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as article_count,
        SUM(views) as total_views
    FROM blogs 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
");

// Get most engaging articles
$most_engaging = $conn->query("
    SELECT 
        b.*,
        CASE 
            WHEN b.author_type = 'admin' THEN a.first_name
            ELSE u.fname 
        END as author_name,
        (SELECT COUNT(*) FROM comment WHERE blog_id = b.id AND status = 'active') as comment_count,
        (SELECT COUNT(*) FROM post_like WHERE post_id = b.id) as like_count
    FROM blogs b
    LEFT JOIN admin a ON b.author_type = 'admin' AND b.author_id = a.id
    LEFT JOIN users u ON b.author_type = 'user' AND b.author_id = u.id
    ORDER BY b.views DESC
    LIMIT 10
");

// Calculate engagement metrics
$engagement_rate = $stats['published_articles'] > 0 ? round((($stats['total_comments'] + $stats['total_likes']) / $stats['published_articles']) * 100, 1) : 0;
$avg_views_per_article = $stats['total_articles'] > 0 ? round($stats['total_views'] / $stats['total_articles'], 1) : 0;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Analytics - Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            height: 100%;
        }

        .analytics-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .metric-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .metric-label {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .chart-container {
            position: relative;
            height: 300px;
            padding: 1rem;
        }

        .contributor-rank {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 0.8rem;
        }

        .rank-1 { background: #ffd700; }
        .rank-2 { background: #c0c0c0; }
        .rank-3 { background: #cd7f32; }
        .rank-other { background: #6c757d; }
    </style>
</head>

<body class="bg-light">
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>

    <div class="container-fluid py-5 px-4">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0 fw-bold text-dark">Analytics Dashboard</h2>
                    <p class="text-muted mb-0">Analisis performa website dan user engagement</p>
                </div>
                <div>
                    <a href="index.php" class="btn btn-outline-secondary me-2">
                        <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
                    </a>
                    <a href="../../../index.php" class="btn btn-outline-primary">
                        <i class="bi bi-house-door-fill"></i> Home
                    </a>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card analytics-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-people-fill text-primary me-2 fs-4"></i>
                            <h6 class="mb-0">Active Users (7d)</h6>
                        </div>
                        <div class="metric-value text-primary"><?= number_format($stats['active_users_7d']) ?></div>
                        <div class="metric-label">Pengguna aktif dalam 7 hari terakhir</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card analytics-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-graph-up text-success me-2 fs-4"></i>
                            <h6 class="mb-0">Engagement Rate</h6>
                        </div>
                        <div class="metric-value text-success"><?= $engagement_rate ?>%</div>
                        <div class="metric-label">Rata-rata engagement per artikel</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card analytics-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-eye text-info me-2 fs-4"></i>
                            <h6 class="mb-0">Avg Views/Article</h6>
                        </div>
                        <div class="metric-value text-info"><?= number_format($avg_views_per_article) ?></div>
                        <div class="metric-label">Rata-rata views per artikel</div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-3">
                <div class="card analytics-card">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-file-text text-warning me-2 fs-4"></i>
                            <h6 class="mb-0">New Articles (7d)</h6>
                        </div>
                        <div class="metric-value text-warning"><?= number_format($stats['new_articles_7d']) ?></div>
                        <div class="metric-label">Artikel baru dalam 7 hari</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-5">
            <div class="col-12 col-lg-8">
                <div class="card analytics-card">
                    <div class="card-body p-4">
                        <h5 class="card-title">
                            <i class="bi bi-graph-up me-2 text-primary"></i>
                            Artikel Trend (6 Bulan Terakhir)
                        </h5>
                        <div class="chart-container">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="card analytics-card">
                    <div class="card-body p-4">
                        <h5 class="card-title">
                            <i class="bi bi-trophy-fill me-2 text-warning"></i>
                            Top Contributors
                        </h5>
                        <div class="recent-activity">
                            <?php 
                            $rank = 1;
                            while ($contributor = $top_contributors->fetch_assoc()): 
                            ?>
                                <div class="activity-item mb-3">
                                    <div class="d-flex align-items-center">
                                        <div class="contributor-rank <?= $rank <= 3 ? 'rank-' . $rank : 'rank-other' ?> me-3">
                                            <?= $rank ?>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($contributor['author_name']) ?></h6>
                                            <div class="d-flex gap-2">
                                                <span class="badge bg-primary"><?= $contributor['article_count'] ?> artikel</span>
                                                <span class="badge bg-info"><?= number_format($contributor['total_views']) ?> views</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                $rank++;
                            endwhile; 
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Engaging Articles -->
        <div class="row g-4">
            <div class="col-12">
                <div class="card analytics-card">
                    <div class="card-body p-4">
                        <h5 class="card-title">
                            <i class="bi bi-star-fill me-2 text-warning"></i>
                            Artikel Paling Populer
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Rank</th>
                                        <th>Article</th>
                                        <th>Author</th>
                                        <th>Views</th>
                                        <th>Comments</th>
                                        <th>Likes</th>
                                        <th>Engagement Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $rank = 1;
                                    while ($article = $most_engaging->fetch_assoc()): 
                                        $engagement_score = $article['views'] + $article['comment_count'] + $article['like_count'];
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="contributor-rank <?= $rank <= 3 ? 'rank-' . $rank : 'rank-other' ?>">
                                                    <?= $rank ?>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="fw-semibold"><?= strip_tags($article['title']) ?></div>
                                                <small class="text-muted"><?= date('d M Y', strtotime($article['created_at'])) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($article['author_name']) ?></td>
                                            <td><?= number_format($article['views']) ?></td>
                                            <td><?= number_format($article['comment_count']) ?></td>
                                            <td><?= number_format($article['like_count']) ?></td>
                                            <td>
                                                <span class="badge bg-success"><?= number_format($engagement_score) ?></span>
                                            </td>
                                        </tr>
                                    <?php 
                                        $rank++;
                                    endwhile; 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Monthly Chart
        const monthlyData = {
            labels: [
                <?php
                $articles_by_month->data_seek(0);
                while ($month = $articles_by_month->fetch_assoc()) {
                    echo "'" . date('M Y', strtotime($month['month'] . '-01')) . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Jumlah Artikel',
                data: [
                    <?php
                    $articles_by_month->data_seek(0);
                    while ($month = $articles_by_month->fetch_assoc()) {
                        echo $month['article_count'] . ",";
                    }
                    ?>
                ],
                borderColor: '#36A2EB',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                tension: 0.4
            }, {
                label: 'Total Views',
                data: [
                    <?php
                    $articles_by_month->data_seek(0);
                    while ($month = $articles_by_month->fetch_assoc()) {
                        echo $month['total_views'] . ",";
                    }
                    ?>
                ],
                borderColor: '#FF6384',
                backgroundColor: 'rgba(255, 99, 132, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        };

        new Chart(document.getElementById('monthlyChart').getContext('2d'), {
            type: 'line',
            data: monthlyData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });
    </script>
</body>

</html> 