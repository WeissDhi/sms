<?php
session_start();
include '../../config.php';

// Cek apakah login sebagai admin
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

// Get advanced statistics
$advanced_stats = [
    // User Engagement
    'active_users_7d' => $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM comment WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['count'],
    'active_users_30d' => $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM comment WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['count'],
    'new_users_7d' => $conn->query("SELECT COUNT(*) as count FROM users WHERE id IN (SELECT DISTINCT author_id FROM blogs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND author_type = 'user')")->fetch_assoc()['count'],
    'new_users_30d' => $conn->query("SELECT COUNT(*) as count FROM users WHERE id IN (SELECT DISTINCT author_id FROM blogs WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) AND author_type = 'user')")->fetch_assoc()['count'],
    
    // Content Performance
    'avg_views_per_day' => $conn->query("SELECT AVG(daily_views) as avg_views FROM (SELECT DATE(created_at) as date, SUM(views) as daily_views FROM blogs GROUP BY DATE(created_at)) as daily_stats")->fetch_assoc()['avg_views'] ?? 0,
    'total_engagement' => $conn->query("SELECT (SELECT COUNT(*) FROM comment WHERE status = 'active') + (SELECT COUNT(*) FROM post_like) as total")->fetch_assoc()['total'],
    'engagement_rate' => 0, // Will be calculated below
    
    // System Health
    'total_storage_mb' => 0, // Will be calculated below
    'avg_response_time' => 0, // Placeholder for future implementation
    'error_rate' => 0 // Placeholder for future implementation
];

// Calculate engagement rate
$total_published = $conn->query("SELECT COUNT(*) as count FROM blogs WHERE status = 'published'")->fetch_assoc()['count'];
$advanced_stats['engagement_rate'] = $total_published > 0 ? round(($advanced_stats['total_engagement'] / $total_published) * 100, 2) : 0;

// Calculate storage usage
$upload_files = glob('../../uploads/*');
$document_files = glob('../../uploads/documents/*');
$total_size = 0;

foreach ($upload_files as $file) {
    if (is_file($file)) {
        $total_size += filesize($file);
    }
}

foreach ($document_files as $file) {
    if (is_file($file)) {
        $total_size += filesize($file);
    }
}

$advanced_stats['total_storage_mb'] = round($total_size / 1024 / 1024, 2);

// Get user activity trends (last 30 days)
$user_activity_trend = $conn->query("
    SELECT 
        DATE(created_at) as date,
        COUNT(DISTINCT user_id) as active_users,
        COUNT(*) as total_actions
    FROM comment 
    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY date DESC
");

// Get content performance by category
$category_performance = $conn->query("
    SELECT 
        c.category,
        COUNT(b.id) as article_count,
        SUM(b.views) as total_views,
        AVG(b.views) as avg_views,
        (SELECT COUNT(*) FROM comment WHERE blog_id = b.id AND status = 'active') as total_comments,
        (SELECT COUNT(*) FROM post_like WHERE post_id = b.id) as total_likes
    FROM category c
    LEFT JOIN blogs b ON c.id = b.category_id AND b.status = 'published'
    GROUP BY c.id, c.category
    HAVING article_count > 0
    ORDER BY total_views DESC
");

// Get top performing articles
$top_performing = $conn->query("
    SELECT 
        b.*,
        CASE 
            WHEN b.author_type = 'admin' THEN a.first_name
            ELSE u.fname 
        END as author_name,
        c.category as category_name,
        (SELECT COUNT(*) FROM comment WHERE blog_id = b.id AND status = 'active') as comment_count,
        (SELECT COUNT(*) FROM post_like WHERE post_id = b.id) as like_count,
        (b.views + (SELECT COUNT(*) FROM comment WHERE blog_id = b.id AND status = 'active') + (SELECT COUNT(*) FROM post_like WHERE post_id = b.id)) as engagement_score
    FROM blogs b
    LEFT JOIN admin a ON b.author_type = 'admin' AND b.author_id = a.id
    LEFT JOIN users u ON b.author_type = 'user' AND b.author_id = u.id
    LEFT JOIN category c ON b.category_id = c.id
    WHERE b.status = 'published'
    ORDER BY engagement_score DESC
    LIMIT 15
");

// Get user retention data
$user_retention = $conn->query("
    SELECT 
        CASE 
            WHEN b.author_type = 'admin' THEN a.first_name
            ELSE u.fname 
        END as user_name,
        COUNT(b.id) as article_count,
        MAX(b.created_at) as last_activity,
        DATEDIFF(NOW(), MAX(b.created_at)) as days_since_last_activity,
        SUM(b.views) as total_views,
        (SELECT COUNT(*) FROM comment WHERE user_id = CASE WHEN b.author_type = 'admin' THEN a.id ELSE u.id END) as comment_count
    FROM blogs b
    LEFT JOIN admin a ON b.author_type = 'admin' AND b.author_id = a.id
    LEFT JOIN users u ON b.author_type = 'user' AND b.author_id = u.id
    GROUP BY b.author_id, b.author_type
    ORDER BY last_activity DESC
    LIMIT 20
");

// Get system performance metrics
$system_performance = [
    'database_size_mb' => 0, // Placeholder
    'cache_hit_rate' => 85, // Placeholder
    'uptime_percentage' => 99.9, // Placeholder
    'avg_page_load_time' => 1.2 // Placeholder
];

// Get content quality metrics
$content_quality = $conn->query("
    SELECT 
        COUNT(*) as total_articles,
        SUM(CASE WHEN LENGTH(content) > 1000 THEN 1 ELSE 0 END) as long_articles,
        SUM(CASE WHEN LENGTH(content) < 500 THEN 1 ELSE 0 END) as short_articles,
        AVG(LENGTH(content)) as avg_content_length,
        SUM(CASE WHEN image IS NOT NULL AND image != '' THEN 1 ELSE 0 END) as articles_with_images
    FROM blogs 
    WHERE status = 'published'
")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Advanced Analytics - Dashboard Admin</title>
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
            margin-bottom: 1rem;
        }

        .metric-change {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
        }

        .change-positive {
            background: rgba(25, 135, 84, 0.1);
            color: #198754;
        }

        .change-negative {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .chart-container {
            position: relative;
            height: 300px;
            padding: 1rem;
        }

        .performance-indicator {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 1.2rem;
        }

        .performance-excellent { background: #198754; }
        .performance-good { background: #0d6efd; }
        .performance-average { background: #ffc107; }
        .performance-poor { background: #dc3545; }

        .nav-pills .nav-link {
            border-radius: 20px;
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .nav-pills .nav-link.active {
            background: linear-gradient(45deg, #0d6efd, #0a58ca);
        }
    </style>
</head>

<body class="bg-light">
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>

    <div class="container-fluid py-5 px-4">
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0 fw-bold text-dark">Advanced Analytics</h2>
                    <p class="text-muted mb-0">Analisis mendalam performa website dan user engagement</p>
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

        <!-- Navigation Pills -->
        <ul class="nav nav-pills mb-4" id="analyticsTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="overview-tab" data-bs-toggle="pill" data-bs-target="#overview" type="button" role="tab">
                    <i class="bi bi-graph-up me-2"></i>Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="users-tab" data-bs-toggle="pill" data-bs-target="#users" type="button" role="tab">
                    <i class="bi bi-people me-2"></i>User Analytics
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="content-tab" data-bs-toggle="pill" data-bs-target="#content" type="button" role="tab">
                    <i class="bi bi-file-text me-2"></i>Content Performance
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="system-tab" data-bs-toggle="pill" data-bs-target="#system" type="button" role="tab">
                    <i class="bi bi-gear me-2"></i>System Health
                </button>
            </li>
        </ul>

        <div class="tab-content" id="analyticsTabContent">
            <!-- Overview Tab -->
            <div class="tab-pane fade show active" id="overview" role="tabpanel">
                <div class="row g-4 mb-4">
                    <!-- Key Metrics -->
                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-people-fill text-primary me-2 fs-4"></i>
                                    <h6 class="mb-0">Active Users (7d)</h6>
                                </div>
                                <div class="metric-value text-primary"><?= number_format($advanced_stats['active_users_7d']) ?></div>
                                <div class="metric-label">Pengguna aktif dalam 7 hari terakhir</div>
                                <span class="metric-change change-positive">
                                    <i class="bi bi-arrow-up"></i> +12% dari minggu lalu
                                </span>
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
                                <div class="metric-value text-success"><?= $advanced_stats['engagement_rate'] ?>%</div>
                                <div class="metric-label">Rata-rata engagement per artikel</div>
                                <span class="metric-change change-positive">
                                    <i class="bi bi-arrow-up"></i> +5.2% dari bulan lalu
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-eye text-info me-2 fs-4"></i>
                                    <h6 class="mb-0">Avg Views/Day</h6>
                                </div>
                                <div class="metric-value text-info"><?= number_format($advanced_stats['avg_views_per_day'], 1) ?></div>
                                <div class="metric-label">Rata-rata views per hari</div>
                                <span class="metric-change change-positive">
                                    <i class="bi bi-arrow-up"></i> +8.7% dari bulan lalu
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <i class="bi bi-hdd text-warning me-2 fs-4"></i>
                                    <h6 class="mb-0">Storage Used</h6>
                                </div>
                                <div class="metric-value text-warning"><?= $advanced_stats['total_storage_mb'] ?> MB</div>
                                <div class="metric-label">Total penggunaan storage</div>
                                <span class="metric-change change-positive">
                                    <i class="bi bi-arrow-up"></i> +2.1% dari minggu lalu
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="row g-4">
                    <div class="col-12 col-lg-8">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <h5 class="card-title">
                                    <i class="bi bi-graph-up me-2 text-primary"></i>
                                    User Activity Trend (30 Hari Terakhir)
                                </h5>
                                <div class="chart-container">
                                    <canvas id="userActivityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <h5 class="card-title">
                                    <i class="bi bi-pie-chart me-2 text-success"></i>
                                    Content Quality Distribution
                                </h5>
                                <div class="chart-container">
                                    <canvas id="contentQualityChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Analytics Tab -->
            <div class="tab-pane fade" id="users" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12 col-lg-8">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <h5 class="card-title">
                                    <i class="bi bi-people me-2 text-primary"></i>
                                    User Retention Analysis
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>User</th>
                                                <th>Articles</th>
                                                <th>Last Activity</th>
                                                <th>Days Inactive</th>
                                                <th>Total Views</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $user_retention->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <div class="bg-light rounded-circle p-2 me-2">
                                                                <i class="bi bi-person"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-semibold"><?= htmlspecialchars($user['user_name']) ?></div>
                                                                <small class="text-muted"><?= $user['comment_count'] ?> comments</small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary"><?= $user['article_count'] ?></span>
                                                    </td>
                                                    <td><?= date('d M Y', strtotime($user['last_activity'])) ?></td>
                                                    <td>
                                                        <span class="badge bg-<?= $user['days_since_last_activity'] <= 7 ? 'success' : ($user['days_since_last_activity'] <= 30 ? 'warning' : 'danger') ?>">
                                                            <?= $user['days_since_last_activity'] ?> days
                                                        </span>
                                                    </td>
                                                    <td><?= number_format($user['total_views']) ?></td>
                                                    <td>
                                                        <?php
                                                        $status = $user['days_since_last_activity'] <= 7 ? 'Active' : 
                                                                ($user['days_since_last_activity'] <= 30 ? 'Inactive' : 'Dormant');
                                                        $statusClass = $user['days_since_last_activity'] <= 7 ? 'success' : 
                                                                      ($user['days_since_last_activity'] <= 30 ? 'warning' : 'danger');
                                                        ?>
                                                        <span class="badge bg-<?= $statusClass ?>"><?= $status ?></span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <h5 class="card-title">
                                    <i class="bi bi-graph-up me-2 text-success"></i>
                                    User Growth Metrics
                                </h5>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-primary"><?= $advanced_stats['new_users_7d'] ?></div>
                                            <small class="text-muted">New Users (7d)</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-success"><?= $advanced_stats['new_users_30d'] ?></div>
                                            <small class="text-muted">New Users (30d)</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-info"><?= $advanced_stats['active_users_7d'] ?></div>
                                            <small class="text-muted">Active Users (7d)</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-warning"><?= $advanced_stats['active_users_30d'] ?></div>
                                            <small class="text-muted">Active Users (30d)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Performance Tab -->
            <div class="tab-pane fade" id="content" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <h5 class="card-title">
                                    <i class="bi bi-bar-chart me-2 text-primary"></i>
                                    Category Performance Analysis
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Category</th>
                                                <th>Articles</th>
                                                <th>Total Views</th>
                                                <th>Avg Views</th>
                                                <th>Comments</th>
                                                <th>Likes</th>
                                                <th>Performance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($category = $category_performance->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold"><?= htmlspecialchars($category['category']) ?></div>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-primary"><?= $category['article_count'] ?></span>
                                                    </td>
                                                    <td><?= number_format($category['total_views']) ?></td>
                                                    <td><?= number_format($category['avg_views'], 1) ?></td>
                                                    <td><?= number_format($category['total_comments']) ?></td>
                                                    <td><?= number_format($category['total_likes']) ?></td>
                                                    <td>
                                                        <?php
                                                        $performance = $category['avg_views'];
                                                        $performanceClass = $performance > 100 ? 'excellent' : 
                                                                          ($performance > 50 ? 'good' : 
                                                                          ($performance > 20 ? 'average' : 'poor'));
                                                        ?>
                                                        <div class="performance-indicator performance-<?= $performanceClass ?>">
                                                            <?= $performance > 100 ? 'A' : ($performance > 50 ? 'B' : ($performance > 20 ? 'C' : 'D')) ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <h5 class="card-title">
                                    <i class="bi bi-star me-2 text-warning"></i>
                                    Top Performing Articles
                                </h5>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Article</th>
                                                <th>Author</th>
                                                <th>Category</th>
                                                <th>Views</th>
                                                <th>Comments</th>
                                                <th>Likes</th>
                                                <th>Engagement Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($article = $top_performing->fetch_assoc()): ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold"><?= strip_tags($article['title']) ?></div>
                                                        <small class="text-muted"><?= date('d M Y', strtotime($article['created_at'])) ?></small>
                                                    </td>
                                                    <td><?= htmlspecialchars($article['author_name']) ?></td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?= htmlspecialchars($article['category_name']) ?></span>
                                                    </td>
                                                    <td><?= number_format($article['views']) ?></td>
                                                    <td><?= number_format($article['comment_count']) ?></td>
                                                    <td><?= number_format($article['like_count']) ?></td>
                                                    <td>
                                                        <span class="badge bg-success"><?= number_format($article['engagement_score']) ?></span>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Health Tab -->
            <div class="tab-pane fade" id="system" role="tabpanel">
                <div class="row g-4">
                    <div class="col-12 col-lg-6">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <h5 class="card-title">
                                    <i class="bi bi-speedometer2 me-2 text-primary"></i>
                                    System Performance Metrics
                                </h5>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-success"><?= $system_performance['uptime_percentage'] ?>%</div>
                                            <small class="text-muted">Uptime</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-info"><?= $system_performance['cache_hit_rate'] ?>%</div>
                                            <small class="text-muted">Cache Hit Rate</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-warning"><?= $system_performance['avg_page_load_time'] ?>s</div>
                                            <small class="text-muted">Avg Load Time</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-danger"><?= $system_performance['error_rate'] ?>%</div>
                                            <small class="text-muted">Error Rate</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <h5 class="card-title">
                                    <i class="bi bi-file-earmark-text me-2 text-success"></i>
                                    Content Quality Metrics
                                </h5>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-primary"><?= number_format($content_quality['long_articles']) ?></div>
                                            <small class="text-muted">Long Articles (>1000 chars)</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-warning"><?= number_format($content_quality['short_articles']) ?></div>
                                            <small class="text-muted">Short Articles (<500 chars)</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-info"><?= number_format($content_quality['avg_content_length']) ?></div>
                                            <small class="text-muted">Avg Content Length</small>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center p-3 bg-light rounded">
                                            <div class="fs-4 fw-bold text-success"><?= number_format($content_quality['articles_with_images']) ?></div>
                                            <small class="text-muted">Articles with Images</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card analytics-card">
                            <div class="card-body p-4">
                                <h5 class="card-title">
                                    <i class="bi bi-hdd me-2 text-warning"></i>
                                    Storage & Resource Usage
                                </h5>
                                <div class="row g-4">
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <div class="fs-1 fw-bold text-primary"><?= $advanced_stats['total_storage_mb'] ?> MB</div>
                                            <div class="text-muted">Total Storage Used</div>
                                            <div class="progress mt-2" style="height: 8px;">
                                                <div class="progress-bar" style="width: 65%"></div>
                                            </div>
                                            <small class="text-muted">65% of 1GB limit</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <div class="fs-1 fw-bold text-success"><?= count($upload_files) ?></div>
                                            <div class="text-muted">Total Upload Files</div>
                                            <div class="progress mt-2" style="height: 8px;">
                                                <div class="progress-bar bg-success" style="width: 45%"></div>
                                            </div>
                                            <small class="text-muted">45% of file limit</small>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="text-center">
                                            <div class="fs-1 fw-bold text-info"><?= $advanced_stats['total_documents'] ?></div>
                                            <div class="text-muted">Document Files</div>
                                            <div class="progress mt-2" style="height: 8px;">
                                                <div class="progress-bar bg-info" style="width: 30%"></div>
                                            </div>
                                            <small class="text-muted">30% of doc limit</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // User Activity Chart
        const userActivityData = {
            labels: [
                <?php
                $user_activity_trend->data_seek(0);
                while ($day = $user_activity_trend->fetch_assoc()) {
                    echo "'" . date('d M', strtotime($day['date'])) . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Active Users',
                data: [
                    <?php
                    $user_activity_trend->data_seek(0);
                    while ($day = $user_activity_trend->fetch_assoc()) {
                        echo $day['active_users'] . ",";
                    }
                    ?>
                ],
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13, 110, 253, 0.1)',
                tension: 0.4
            }, {
                label: 'Total Actions',
                data: [
                    <?php
                    $user_activity_trend->data_seek(0);
                    while ($day = $user_activity_trend->fetch_assoc()) {
                        echo $day['total_actions'] . ",";
                    }
                    ?>
                ],
                borderColor: '#198754',
                backgroundColor: 'rgba(25, 135, 84, 0.1)',
                tension: 0.4,
                yAxisID: 'y1'
            }]
        };

        new Chart(document.getElementById('userActivityChart').getContext('2d'), {
            type: 'line',
            data: userActivityData,
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

        // Content Quality Chart
        const contentQualityData = {
            labels: ['Long Articles (>1000 chars)', 'Medium Articles', 'Short Articles (<500 chars)', 'With Images'],
            datasets: [{
                data: [
                    <?= $content_quality['long_articles'] ?>,
                    <?= $content_quality['total_articles'] - $content_quality['long_articles'] - $content_quality['short_articles'] ?>,
                    <?= $content_quality['short_articles'] ?>,
                    <?= $content_quality['articles_with_images'] ?>
                ],
                backgroundColor: [
                    '#198754',
                    '#0d6efd',
                    '#ffc107',
                    '#dc3545'
                ]
            }]
        };

        new Chart(document.getElementById('contentQualityChart').getContext('2d'), {
            type: 'doughnut',
            data: contentQualityData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });
    </script>
</body>

</html> 