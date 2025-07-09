<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'penulis') {
    header("Location: ../../login.php");
    exit;
}

$penulis_id = $_SESSION['author_id'];

// Get penulis details
$penulis_query = $conn->prepare("SELECT * FROM penulis WHERE id = ?");
$penulis_query->bind_param("i", $penulis_id);
$penulis_query->execute();
$penulis = $penulis_query->get_result()->fetch_assoc();

// Get penulis statistics
$stats_query = "
    SELECT 
        (SELECT COUNT(*) FROM blogs WHERE author_id = ? AND author_type = 'penulis') as total_articles,
        (SELECT COUNT(*) FROM blogs WHERE author_id = ? AND author_type = 'penulis' AND status = 'published') as published_articles,
        (SELECT COUNT(*) FROM blogs WHERE author_id = ? AND author_type = 'penulis' AND status = 'draft') as draft_articles,
        (SELECT COUNT(*) FROM comment WHERE penulis_id = ?) as total_comments,
        (SELECT SUM(views) FROM blogs WHERE author_id = ? AND author_type = 'penulis') as total_views
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iiiii", $penulis_id, $penulis_id, $penulis_id, $penulis_id, $penulis_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Get penulis's own comments
$recent_comments_query = "
    SELECT c.*, b.title as blog_title 
    FROM comment c 
    JOIN blogs b ON c.blog_id = b.id 
    WHERE c.penulis_id = ? AND c.status = 'active'
    ORDER BY c.created_at DESC 
    LIMIT 10
";
$recent_comments_stmt = $conn->prepare($recent_comments_query);
$recent_comments_stmt->bind_param("i", $penulis_id);
$recent_comments_stmt->execute();
$recent_comments = $recent_comments_stmt->get_result();

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
$articles_stmt->bind_param("i", $penulis_id);
$articles_stmt->execute();
$recent_articles = $articles_stmt->get_result();

// Get comments on penulis's articles
$article_comments_query = "
    SELECT c.*, b.title as blog_title, 
           CASE WHEN a.id IS NOT NULL THEN a.first_name ELSE p.fname END as commenter_name
    FROM comment c 
    JOIN blogs b ON c.blog_id = b.id 
    LEFT JOIN penulis p ON c.penulis_id = p.id
    LEFT JOIN admin a ON c.penulis_id = a.id
    WHERE b.author_id = ? AND b.author_type = 'penulis' AND c.status = 'active'
    ORDER BY c.created_at DESC 
    LIMIT 10
";
$article_comments_stmt = $conn->prepare($article_comments_query);
$article_comments_stmt->bind_param("i", $penulis_id);
$article_comments_stmt->execute();
$article_comments = $article_comments_stmt->get_result();

// Get replies to penulis's comments
$comment_replies_query = "
    SELECT c.*, b.title as blog_title, 
           CASE WHEN a.id IS NOT NULL THEN a.first_name ELSE p.fname END as replier_name,
           pc.comment as parent_comment
    FROM comment c 
    JOIN blogs b ON c.blog_id = b.id 
    JOIN comment pc ON c.parent_id = pc.comment_id
    LEFT JOIN penulis p ON c.penulis_id = p.id
    LEFT JOIN admin a ON c.penulis_id = a.id
    WHERE pc.penulis_id = ? AND c.status = 'active'
    ORDER BY c.created_at DESC 
    LIMIT 10
";
$comment_replies_stmt = $conn->prepare($comment_replies_query);
$comment_replies_stmt->bind_param("i", $penulis_id);
$comment_replies_stmt->execute();
$comment_replies = $comment_replies_stmt->get_result();

// Get most viewed articles
$top_articles_query = "
    SELECT b.*, c.category as category_name 
    FROM blogs b 
    LEFT JOIN category c ON b.category_id = c.id 
    WHERE b.author_id = ? AND b.author_type = 'penulis' 
    ORDER BY b.views DESC 
    LIMIT 3
";
$top_articles_stmt = $conn->prepare($top_articles_query);
$top_articles_stmt->bind_param("i", $penulis_id);
$top_articles_stmt->execute();
$top_articles = $top_articles_stmt->get_result();

// Handle form update
$update_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $new_fname = trim($_POST['fname']);
        $new_username = trim($_POST['username']);

        $update_stmt = $conn->prepare("UPDATE penulis SET fname = ?, username = ? WHERE id = ?");
        $update_stmt->bind_param("ssi", $new_fname, $new_username, $penulis_id);
        if ($update_stmt->execute()) {
            $update_message = 'Profil berhasil diperbarui!';
            $penulis['fname'] = $new_fname;
            $penulis['username'] = $new_username;
        } else {
            $update_message = 'Gagal memperbarui profil.';
        }
        $update_stmt->close();
    }

    // Ubah password
    if (isset($_POST['update_password'])) {
        $new_password = $_POST['new_password'];

        $pass_stmt = $conn->prepare("UPDATE penulis SET password = ? WHERE id = ?");
        $pass_stmt->bind_param("si", $new_password, $penulis_id);
        if ($pass_stmt->execute()) {
            $update_message = 'Password berhasil diubah!';
        } else {
            $update_message = 'Gagal mengubah password.';
        }
        $pass_stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="shortcut icon" href="../../../img/sms.png" />
    <meta charset="UTF-8">
    <title>Profil Saya</title>
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

        .edit-profile-modal .modal-content {
            border-radius: 1rem;
        }

        .edit-profile-modal .modal-header {
            border-bottom: 2px solid #f0f0f0;
        }

        .edit-profile-modal .modal-title {
            color: var(--primary-color);
            font-weight: 600;
        }
    </style>
</head>

<body class="bg-light">
    <?php include '../components/navbar.php' ?>
    <?php include '../components/penulis-sidebar.php' ?>

    <?php if ($update_message): ?>
        <div class="alert alert-info alert-dismissible fade show m-3" role="alert">
            <?= htmlspecialchars($update_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2"><?= htmlspecialchars($penulis['fname']) ?></h1>
                    <p class="mb-0">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($penulis['username']) ?>
                        <button type="button" class="btn btn-light btn-sm ms-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="bi bi-pencil"></i> Edit Profil
                        </button>
                    </p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="../index.php" class="btn btn-light">
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
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="mb-1">
                                            <a href="../../../<?= htmlspecialchars($article['slug']) ?>" class="text-decoration-none">
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
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Recent Comments -->
                <div class="content-card">
                    <h3 class="card-title">
                        <i class="bi bi-chat-dots"></i> Komentar
                    </h3>

                    <!-- Tabs -->
                    <ul class="nav nav-tabs mb-3" id="commentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="my-comments-tab" data-bs-toggle="tab" data-bs-target="#my-comments" type="button" role="tab">
                                Komentar Saya
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="article-comments-tab" data-bs-toggle="tab" data-bs-target="#article-comments" type="button" role="tab">
                                Komentar di Artikel Saya
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="comment-replies-tab" data-bs-toggle="tab" data-bs-target="#comment-replies" type="button" role="tab">
                                Balasan Komentar Saya
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content" id="commentTabsContent">
                        <!-- My Comments Tab -->
                        <div class="tab-pane fade show active" id="my-comments" role="tabpanel">
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

                        <!-- Article Comments Tab -->
                        <div class="tab-pane fade" id="article-comments" role="tabpanel">
                            <?php if ($article_comments->num_rows > 0): ?>
                                <?php while ($comment = $article_comments->fetch_assoc()): ?>
                                    <div class="comment-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center mb-1">
                                                    <strong class="me-2"><?= htmlspecialchars($comment['commenter_name']) ?></strong>
                                                    <small class="text-muted">
                                                        <?= date('d M Y H:i', strtotime($comment['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <p class="mb-1"><?= htmlspecialchars($comment['comment']) ?></p>
                                                <small class="text-muted">
                                                    Pada artikel:
                                                    <a href="../../view_detail.php?id=<?= $comment['blog_id'] ?>" class="text-decoration-none">
                                                        <?= strip_tags($comment['blog_title']) ?>
                                                    </a>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                                    <p class="mt-3">Belum ada komentar di artikel Anda</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Comment Replies Tab -->
                        <div class="tab-pane fade" id="comment-replies" role="tabpanel">
                            <?php if ($comment_replies->num_rows > 0): ?>
                                <?php while ($reply = $comment_replies->fetch_assoc()): ?>
                                    <div class="comment-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center mb-1">
                                                    <strong class="me-2"><?= htmlspecialchars($reply['replier_name']) ?></strong>
                                                    <small class="text-muted">
                                                        <?= date('d M Y H:i', strtotime($reply['created_at'])) ?>
                                                    </small>
                                                </div>
                                                <p class="mb-1"><?= htmlspecialchars($reply['comment']) ?></p>
                                                <small class="text-muted">
                                                    Membalas komentar Anda: "<?= htmlspecialchars(substr($reply['parent_comment'], 0, 50)) ?>..."
                                                    <br>
                                                    Pada artikel:
                                                    <a href="../../view_detail.php?id=<?= $reply['blog_id'] ?>" class="text-decoration-none">
                                                        <?= strip_tags($reply['blog_title']) ?>
                                                    </a>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-chat-dots" style="font-size: 3rem;"></i>
                                    <p class="mt-3">Belum ada balasan untuk komentar Anda</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
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
                                <h6 class="mb-1">
                                    <a href="../../../<?= htmlspecialchars($article['slug']) ?>" class="text-decoration-none">
                                        <?= strip_tags($article['title']) ?>
                                    </a>
                                </h6>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge bg-info">
                                        <i class="bi bi-eye-fill"></i> <?= number_format($article['views']) ?> views
                                    </span>
                                    <small class="text-muted">
                                        <?= date('d M Y', strtotime($article['created_at'])) ?>
                                    </small>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-trophy" style="font-size: 3rem;"></i>
                            <p class="mt-3">Belum ada artikel yang ditulis</p>
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

    <!-- Edit Profile Modal -->
    <div class="modal fade edit-profile-modal" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" id="profileForm">
                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" name="fname" value="<?= htmlspecialchars($penulis['fname']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($penulis['username']) ?>" required>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan</button>
                    </form>

                    <hr class="my-4">

                    <form method="POST" id="passwordForm">
                        <h6 class="mb-3">Ubah Password</h6>
                        <div class="mb-3">
                            <label class="form-label">Password Baru</label>
                            <input type="password" class="form-control" name="new_password" required minlength="6">
                        </div>
                        <button type="submit" name="update_password" class="btn btn-warning">Ubah Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>