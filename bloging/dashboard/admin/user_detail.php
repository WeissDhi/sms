<?php
session_start();
include '../../config.php';

// Check if user is logged in
if (!isset($_SESSION['author_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Get user ID from URL or session
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : $_SESSION['author_id'];
$is_admin = isset($_SESSION['author_type']) && $_SESSION['author_type'] === 'admin';
$is_own_profile = $user_id === $_SESSION['author_id'];

// If not admin and trying to view other user's profile, redirect to own profile
if (!$is_admin && !$is_own_profile) {
    header("Location: user_detail.php");
    exit;
}

// Get user details
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user = $user_query->get_result()->fetch_assoc();

if (!$user) {
    header("Location: " . ($is_admin ? "user_management.php" : "../index.php"));
    exit;
}

// Get user statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM blogs WHERE author_id = ? AND author_type = 'user') as total_articles,
        (SELECT COUNT(*) FROM blogs WHERE author_id = ? AND author_type = 'user' AND status = 'published') as published_articles,
        (SELECT COUNT(*) FROM blogs WHERE author_id = ? AND author_type = 'user' AND status = 'draft') as draft_articles,
        (SELECT COUNT(*) FROM comment WHERE user_id = ?) as total_comments,
        (SELECT SUM(views) FROM blogs WHERE author_id = ? AND author_type = 'user') as total_views
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iiiii", $user_id, $user_id, $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get recent articles
$articles_query = "
    SELECT b.*, c.category as category_name 
    FROM blogs b 
    LEFT JOIN category c ON b.category_id = c.id 
    WHERE b.author_id = ? AND b.author_type = 'user' 
    ORDER BY b.created_at DESC 
    LIMIT 5
";
$articles_stmt = $conn->prepare($articles_query);
$articles_stmt->bind_param("i", $user_id);
$articles_stmt->execute();
$recent_articles = $articles_stmt->get_result();

// Get recent comments
$comments_query = "
    SELECT c.*, b.title as blog_title 
    FROM comment c 
    JOIN blogs b ON c.blog_id = b.id 
    WHERE c.user_id = ? 
    ORDER BY c.created_at DESC 
    LIMIT 5
";
$comments_stmt = $conn->prepare($comments_query);
$comments_stmt->bind_param("i", $user_id);
$comments_stmt->execute();
$recent_comments = $comments_stmt->get_result();

// Get most viewed articles
$top_articles_query = "
    SELECT b.*, c.category as category_name 
    FROM blogs b 
    LEFT JOIN category c ON b.category_id = c.id 
    WHERE b.author_id = ? AND b.author_type = 'user' 
    ORDER BY b.views DESC 
    LIMIT 3
";
$top_articles_stmt = $conn->prepare($top_articles_query);
$top_articles_stmt->bind_param("i", $user_id);
$top_articles_stmt->execute();
$top_articles = $top_articles_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Pengguna - <?= htmlspecialchars($user['fname']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary-color), #0a58ca);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        .stats-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
            border: none;
            height: 100%;
        }
        
        .stats-card:hover {
            transform: translateY(-5px);
        }
        
        .stats-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
        
        .content-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        
        .content-card .card-title {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .article-item {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            transition: transform 0.2s;
        }
        
        .article-item:hover {
            transform: translateX(5px);
            background: #f0f0f0;
        }
        
        .comment-item {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            border-left: 4px solid var(--primary-color);
        }
        
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }
        
        .thumbnail-container {
            width: 100px;
            height: 60px;
            overflow: hidden;
            border-radius: 0.5rem;
        }
        
        .thumbnail-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>

<body class="bg-light">
    <?php include '../components/navbar.php'; ?>
    <?php if ($is_admin) include '../components/sidebar.php'; ?>
    
    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2"><?= htmlspecialchars($user['fname']) ?></h1>
                    <p class="mb-0">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($user['username']) ?>
                        <?php if ($is_admin): ?>
                            <a href="edit_user.php?id=<?= $user_id ?>" class="btn btn-light btn-sm ms-3">
                                <i class="bi bi-pencil"></i> Edit Profil
                            </a>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="<?= $is_admin ? 'user_management.php' : '../index.php' ?>" class="btn btn-light">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div class="stats-number"><?= number_format($stats['total_articles']) ?></div>
                    <div class="stats-label">Total Artikel</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stats-number"><?= number_format($stats['published_articles']) ?></div>
                    <div class="stats-label">Artikel Dipublikasi</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="bi bi-chat-dots"></i>
                    </div>
                    <div class="stats-number"><?= number_format($stats['total_comments']) ?></div>
                    <div class="stats-label">Total Komentar</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="bi bi-eye"></i>
                    </div>
                    <div class="stats-number"><?= number_format($stats['total_views']) ?></div>
                    <div class="stats-label">Total Views</div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Articles -->
            <div class="col-md-8">
                <div class="content-card">
                    <h3 class="card-title">
                        <i class="bi bi-file-text"></i> Artikel Terbaru
                    </h3>
                    <?php if ($recent_articles->num_rows > 0): ?>
                        <?php while ($article = $recent_articles->fetch_assoc()): ?>
                            <div class="article-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="mb-1">
                                            <a href="../../../view_detail.php?id=<?= $article['id'] ?>" class="text-decoration-none">
                                                <?= htmlspecialchars(strip_tags($article['title'])) ?>
                                            </a>
                                        </h5>
                                        <div class="mb-2">
                                            <span class="badge bg-light text-dark">
                                                <?= htmlspecialchars($article['category_name']) ?>
                                            </span>
                                            <span class="badge bg-<?= $article['status'] === 'published' ? 'success' : 'warning' ?>">
                                                <?= ucfirst($article['status']) ?>
                                            </span>
                                            <span class="badge bg-info">
                                                <i class="bi bi-eye-fill"></i> <?= number_format($article['views']) ?>
                                            </span>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-calendar"></i> <?= date('d M Y H:i', strtotime($article['created_at'])) ?>
                                        </small>
                                    </div>
                                    <?php if (!empty($article['image'])): ?>
                                        <div class="thumbnail-container ms-3">
                                            <img src="../../uploads/<?= basename($article['image']) ?>" alt="Thumbnail">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <div class="text-center mt-3">
                            <a href="<?= $is_admin ? '../../blogs_management.php' : '../blog_management.php' ?>" class="btn btn-outline-primary">
                                Lihat Semua Artikel
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-file-text" style="font-size: 3rem;"></i>
                            <div class="mt-3">Belum ada artikel yang ditulis</div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Comments -->
                <div class="content-card">
                    <h3 class="card-title">
                        <i class="bi bi-chat-dots"></i> Komentar Terbaru
                    </h3>
                    <?php if ($recent_comments->num_rows > 0): ?>
                        <?php while ($comment = $recent_comments->fetch_assoc()): ?>
                            <div class="comment-item">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="mb-1"><?= htmlspecialchars($comment['comment']) ?></div>
                                        <small class="text-muted">
                                            Pada artikel: 
                                            <a href="../../../view_detail.php?id=<?= $comment['blog_id'] ?>" class="text-decoration-none">
                                                <?= strip_tags($comment['blog_title']) ?>
                                            </a>
                                        </small>
                                    </div>
                                    <small class="text-muted">
                                        <?= date('d M Y H:i', strtotime($comment['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                            <div class="mt-3">Belum ada komentar</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Most Viewed Articles -->
                <div class="content-card">
                    <h3 class="card-title">
                        <i class="bi bi-trophy"></i> Artikel Terpopuler
                    </h3>
                    <?php if ($top_articles->num_rows > 0): ?>
                        <?php while ($article = $top_articles->fetch_assoc()): ?>
                            <div class="article-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-1">
                                        <a href="../../../view_detail.php?id=<?= $article['id'] ?>" class="text-decoration-none">
                                            <?= htmlspecialchars(strip_tags($article['title'])) ?>
                                        </a>
                                    </h6>
                                    <span class="badge bg-info">
                                        <i class="bi bi-eye-fill"></i> <?= number_format($article['views']) ?>
                                    </span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-trophy" style="font-size: 3rem;"></i>
                            <div class="mt-3">Belum ada artikel yang ditulis</div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Stats -->
                <div class="content-card">
                    <h3 class="card-title">
                        <i class="bi bi-graph-up"></i> Statistik Singkat
                    </h3>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Artikel Draft</span>
                            <span class="badge bg-warning"><?= number_format($stats['draft_articles']) ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Rata-rata Views/Artikel</span>
                            <span class="badge bg-info">
                                <?= $stats['total_articles'] > 0 
                                    ? number_format($stats['total_views'] / $stats['total_articles'], 1) 
                                    : 0 ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Rata-rata Komentar/Artikel</span>
                            <span class="badge bg-primary">
                                <?= $stats['total_articles'] > 0 
                                    ? number_format($stats['total_comments'] / $stats['total_articles'], 1) 
                                    : 0 ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 