<?php
session_start();
include '../../config.php';

// Cek apakah user login dan bertipe 'penulis'
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'penulis') {
    header("Location: ../../login.php");
    exit;
}

$user_id = (int) $_SESSION['author_id'];

// Ambil data profil user
$user_query = $conn->prepare("SELECT * FROM penulis WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

if (!$user_data) {
    session_destroy();
    header("Location: ../../login.php");
    exit;
}

// Get user statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM blogs WHERE author_id = ? AND author_type = 'penulis') as total_blogs,
        (SELECT COUNT(*) FROM blogs WHERE author_id = ? AND author_type = 'penulis' AND status = 'published') as published_blogs,
        (SELECT COUNT(*) FROM blogs WHERE author_id = ? AND author_type = 'penulis' AND status = 'draft') as draft_blogs,
        (SELECT COUNT(*) FROM comment WHERE penulis_id = ?) as total_comments,
        (SELECT COUNT(*) FROM post_like WHERE liked_by = ?) as total_likes,
        (SELECT SUM(views) FROM blogs WHERE author_id = ? AND author_type = 'penulis') as total_views
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iiiiii", $user_id, $user_id, $user_id, $user_id, $user_id, $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get recent articles
$articles_query = "
    SELECT b.*, c.category as category_name 
    FROM blogs b 
    LEFT JOIN category c ON b.category_id = c.id 
    WHERE b.author_id = ? AND b.author_type = 'penulis' 
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
    WHERE c.penulis_id = ? 
    ORDER BY c.created_at DESC 
    LIMIT 5
";
$comments_stmt = $conn->prepare($comments_query);
$comments_stmt->bind_param("i", $user_id);
$comments_stmt->execute();
$recent_comments = $comments_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard User</title>
    <link rel="shortcut icon" href="../../../img/sms.png" />
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

        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color), #0a58ca);
            color: white;
            padding: 3rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .welcome-card {
            background: white;
            border-radius: 1rem;
            padding: 2rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
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

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem;
            border-radius: 0.5rem;
            background: white;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .quick-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .quick-action-btn i {
            font-size: 1.5rem;
            color: var(--primary-color);
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
    <?php include '../components/navbar.php' ?>
    <?php include '../components/penulis-sidebar.php' ?>

    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <?php if (!is_array($user_data)) { var_dump($user_data); die('User data is not array!'); } ?>
                    <h1 class="mb-2">Selamat Datang, <?= htmlspecialchars($user_data['fname']) ?>!</h1>
                    <p class="mb-0">Kelola blog dan artikel Anda di sini</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="../../add_blog.php" class="btn btn-light">
                        <i class="bi bi-plus-lg"></i> Tulis Artikel Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="../../add_blog.php" class="quick-action-btn">
                <i class="bi bi-pencil-square"></i>
                <div>
                    <strong>Tulis Artikel</strong>
                    <div class="text-muted small">Buat artikel baru</div>
                </div>
            </a>
            <a href="blog_management.php" class="quick-action-btn">
                <i class="bi bi-file-text"></i>
                <div>
                    <strong>Kelola Artikel</strong>
                    <div class="text-muted small">Lihat semua artikel</div>
                </div>
            </a>
            <a href="profile.php" class="quick-action-btn">
                <i class="bi bi-person"></i>
                <div>
                    <strong>Profil Saya</strong>
                    <div class="text-muted small">Edit profil</div>
                </div>
            </a>
            <a href="../../../index.php" class="quick-action-btn">
                <i class="bi bi-house"></i>
                <div>
                    <strong>Beranda</strong>
                    <div class="text-muted small">Kembali ke beranda</div>
                </div>
            </a>
        </div>

        <!-- Statistics Cards -->
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="bi bi-file-text"></i>
                    </div>
                    <div class="stats-number"><?= number_format($stats['total_blogs']) ?></div>
                    <div class="stats-label">Total Artikel</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="bi bi-check-circle"></i>
                    </div>
                    <div class="stats-number"><?= number_format($stats['published_blogs']) ?></div>
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
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">
                                            <a href="../../../view_detail.php?id=<?= $article['id'] ?>" class="text-decoration-none">
                                                <?= strip_tags($article['title']) ?>
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
                            <a href="blog_management.php" class="btn btn-outline-primary">
                                Lihat Semua Artikel
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-file-text" style="font-size: 3rem;"></i>
                            <p class="mt-3">Belum ada artikel yang ditulis</p>
                            <a href="../../add_blog.php" class="btn btn-primary">
                                <i class="bi bi-plus-lg"></i> Tulis Artikel Pertama
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Quick Stats -->
                <div class="content-card">
                    <h3 class="card-title">
                        <i class="bi bi-graph-up"></i> Statistik Singkat
                    </h3>
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Artikel Draft</span>
                            <span class="badge bg-warning"><?= number_format($stats['draft_blogs']) ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Total Likes</span>
                            <span class="badge bg-danger"><?= number_format($stats['total_likes']) ?></span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>Rata-rata Views/Artikel</span>
                            <span class="badge bg-info">
                                <?= $stats['total_blogs'] > 0 
                                    ? number_format($stats['total_views'] / $stats['total_blogs'], 1) 
                                    : 0 ?>
                            </span>
                        </div>
                    </div>
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
                                        <p class="mb-1"><?= htmlspecialchars($comment['comment']) ?></p>
                                        <small class="text-muted">
                                            Pada artikel: 
                                            <a href="../../view_detail.php?id=<?= $comment['blog_id'] ?>" class="text-decoration-none">
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
                            <p class="mt-3">Belum ada komentar</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>