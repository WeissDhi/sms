<?php
session_start();
include '../../config.php';

// Cek apakah pengguna login
if (!isset($_SESSION['pengguna_id'])) {
    header('Location: ../../login.php');
    exit;
}

$pengguna_id = $_SESSION['pengguna_id'];

// Inisialisasi variabel agar tidak Notice
$edit_success = false;

// Proses update profil pengguna (PASTIKAN DI ATAS AMBIL DATA PROFIL)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_nama'])) {
    $new_nama = trim($_POST['edit_nama']);
    $new_email = trim($_POST['edit_email']);
    $new_password = trim($_POST['edit_password']);
    $update_sql = "UPDATE pengguna SET nama = ?, email = ?";
    $params = [$new_nama, $new_email];
    $types = "ss";
    if (!empty($new_password)) {
        $update_sql .= ", password = ?";
        $params[] = $new_password;
        $types .= "s";
    }
    $update_sql .= " WHERE id = ?";
    $params[] = $pengguna_id;
    $types .= "i";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param($types, ...$params);
    if ($stmt->execute()) {
        $edit_success = true;
    }
    $stmt->close();
}

// Ambil data profil pengguna TERBARU setelah update
$stmt = $conn->prepare('SELECT * FROM pengguna WHERE id = ?');
$stmt->bind_param('i', $pengguna_id);
$stmt->execute();
$pengguna = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Ambil artikel yang disimpan
$saved_query = $conn->prepare('
    SELECT b.*, sa.saved_at, c.category as category_name
    FROM saved_articles sa
    JOIN blogs b ON sa.blog_id = b.id
    LEFT JOIN category c ON b.category_id = c.id
    WHERE sa.pengguna_id = ?
    ORDER BY sa.saved_at DESC
');
$saved_query->bind_param('i', $pengguna_id);
$saved_query->execute();
$saved_articles = $saved_query->get_result();

// Ambil komentar pengguna
$comment_query = $conn->prepare('
    SELECT c.*, b.title as blog_title, b.slug as blog_slug
    FROM comment c
    JOIN blogs b ON c.blog_id = b.id
    WHERE c.pengguna_id = ? AND c.status = "active"
    ORDER BY c.created_at DESC
    LIMIT 10
');
$comment_query->bind_param('i', $pengguna_id);
$comment_query->execute();
$comments = $comment_query->get_result();

// Ambil balasan ke komentar pengguna
$reply_query = $conn->prepare('
    SELECT c.*, b.title as blog_title, b.slug as blog_slug, pc.comment as parent_comment
    FROM comment c
    JOIN blogs b ON c.blog_id = b.id
    JOIN comment pc ON c.parent_id = pc.comment_id
    WHERE pc.pengguna_id = ? AND c.status = "active"
    ORDER BY c.created_at DESC
    LIMIT 10
');
$reply_query->bind_param('i', $pengguna_id);
$reply_query->execute();
$replies = $reply_query->get_result();

// Statistik
$total_saved = $saved_articles->num_rows;
$total_comments = $comments->num_rows;
$total_replies = $replies->num_rows;

// Modal Edit Profil
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pengguna</title>
    <link rel="shortcut icon" href="../../../img/sms.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .dashboard-header {
            background: linear-gradient(135deg, #0d6efd, #0a58ca);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 1rem 1rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stats-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            border: none;
            height: 100%;
        }
        .stats-icon { font-size: 2rem; margin-bottom: 1rem; color: #0d6efd; }
        .stats-number { font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem; }
        .stats-label { color: #6c757d; font-size: 0.9rem; }
        .content-card {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        .content-card .card-title {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #f0f0f0;
        }
        .article-item, .comment-item {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            background: #f8f9fa;
            transition: transform 0.2s;
        }
        .article-item:hover, .comment-item:hover {
            transform: translateX(5px);
            background: #f0f0f0;
        }
        .badge { padding: 0.5em 0.8em; font-weight: 500; }
        .thumbnail-container { width: 100px; height: 60px; overflow: hidden; border-radius: 0.5rem; }
        .thumbnail-container img { width: 100%; height: 100%; object-fit: cover; }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">Halo, <?= htmlspecialchars($pengguna['nama']) ?>!</h1>
                    <p class="mb-0">Selamat datang di dashboard pengguna. Simpan dan kelola artikel favoritmu, serta pantau aktivitas komentarmu di sini.</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="../../../index.php" class="btn btn-light">
                        <i class="bi bi-house"></i> Beranda
                    </a>
                    <a href="../../../logout.php" class="btn btn-danger ms-2">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <!-- Tambahkan navigasi tab di atas dashboard -->
        <ul class="nav nav-tabs mb-4" id="penggunaTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab" aria-controls="dashboard" aria-selected="true">
                    <i class="bi bi-house"></i> Dashboard
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">
                    <i class="bi bi-person"></i> Profile
                </button>
            </li>
        </ul>

        <div class="tab-content" id="penggunaTabContent">
            <!-- Tab Dashboard -->
            <div class="tab-pane fade show active" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab">
                <!-- Statistik Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-4">
                        <div class="stats-card text-center">
                            <div class="stats-icon"><i class="bi bi-bookmark-heart"></i></div>
                            <div class="stats-number"><?= $total_saved ?></div>
                            <div class="stats-label">Artikel Disimpan</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card text-center">
                            <div class="stats-icon"><i class="bi bi-chat-dots"></i></div>
                            <div class="stats-number"><?= $total_comments ?></div>
                            <div class="stats-label">Komentar Saya</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card text-center">
                            <div class="stats-icon"><i class="bi bi-reply"></i></div>
                            <div class="stats-number"><?= $total_replies ?></div>
                            <div class="stats-label">Balasan ke Komentar Saya</div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <!-- Artikel Disimpan -->
                    <div class="col-md-8">
                        <div class="content-card">
                            <h3 class="card-title"><i class="bi bi-bookmark-heart"></i> Artikel Disimpan</h3>
                            <?php if ($saved_articles->num_rows > 0): ?>
                                <?php while ($article = $saved_articles->fetch_assoc()): ?>
                                    <div class="article-item d-flex align-items-center justify-content-between">
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
                                                <img src="../../uploads/<?= htmlspecialchars(basename($article['image'])) ?>" alt="Thumbnail">
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-bookmark-heart" style="font-size: 3rem;"></i>
                                    <p class="mt-3">Belum ada artikel yang disimpan</p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- Komentar Saya -->
                        <div class="content-card">
                            <h3 class="card-title"><i class="bi bi-chat-dots"></i> Komentar Saya</h3>
                            <?php if ($comments->num_rows > 0): ?>
                                <?php while ($comment = $comments->fetch_assoc()): ?>
                                    <div class="comment-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <p class="mb-1"><?= htmlspecialchars($comment['comment']) ?></p>
                                                <small class="text-muted">
                                                    Pada artikel:
                                                    <a href="../../../<?= htmlspecialchars($comment['blog_slug']) ?>" class="text-decoration-none">
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
                    <!-- Sidebar: Balasan ke Komentar Saya -->
                    <div class="col-md-4">
                        <div class="content-card">
                            <h3 class="card-title"><i class="bi bi-reply"></i> Balasan ke Komentar Saya</h3>
                            <?php if ($replies->num_rows > 0): ?>
                                <?php while ($reply = $replies->fetch_assoc()): ?>
                                    <div class="comment-item">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <div class="mb-1">
                                                    <strong>Balasan:</strong> <?= htmlspecialchars($reply['comment']) ?>
                                                </div>
                                                <small class="text-muted">
                                                    Pada artikel:
                                                    <a href="../../../<?= htmlspecialchars($reply['blog_slug']) ?>" class="text-decoration-none">
                                                        <?= strip_tags($reply['blog_title']) ?>
                                                    </a>
                                                    <br>
                                                    Membalas komentar Anda: "<?= htmlspecialchars(substr($reply['parent_comment'], 0, 50)) ?>..."
                                                </small>
                                            </div>
                                            <small class="text-muted">
                                                <?= date('d M Y H:i', strtotime($reply['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-reply" style="font-size: 3rem;"></i>
                                    <p class="mt-3">Belum ada balasan ke komentar Anda</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tab Profile -->
            <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-success text-white">
                        <i class="bi bi-person-circle me-2"></i> Profil Pengguna
                    </div>
                    <div class="card-body">
                        <?php if ($edit_success): ?>
                            <div class="alert alert-success">Profil berhasil diperbarui!</div>
                        <?php endif; ?>
                        <dl class="row mb-0">
                            <dt class="col-sm-3">Nama</dt>
                            <dd class="col-sm-9"> <?= htmlspecialchars($pengguna['nama']) ?> </dd>
                            <dt class="col-sm-3">Username</dt>
                            <dd class="col-sm-9"> <?= htmlspecialchars($pengguna['username']) ?> </dd>
                            <dt class="col-sm-3">Email</dt>
                            <dd class="col-sm-9"> <?= htmlspecialchars($pengguna['email']) ?: '<span class="text-muted">(Belum diisi)</span>' ?> </dd>
                            <dt class="col-sm-3">Tanggal Daftar</dt>
                            <dd class="col-sm-9"> <?= date('d M Y, H:i', strtotime($pengguna['created_at'])) ?> </dd>
                        </dl>
                        <div class="mt-4 text-end">
                            <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                <i class="bi bi-pencil"></i> Edit Profil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Edit Profil -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" id="formEditProfile">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title" id="editProfileModalLabel"><i class="bi bi-pencil me-2"></i>Edit Profil</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="editNama" class="form-label">Nama</label>
                            <input type="text" class="form-control" id="editNama" name="edit_nama" value="<?= htmlspecialchars($pengguna['nama']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="editEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="editEmail" name="edit_email" value="<?= htmlspecialchars($pengguna['email']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="editPassword" class="form-label">Password Baru <span class="text-muted">(Opsional)</span></label>
                            <input type="password" class="form-control" id="editPassword" name="edit_password" placeholder="Biarkan kosong jika tidak ingin ganti password">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>