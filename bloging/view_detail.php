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

        <!-- Comment Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Komentar</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])): ?>
                    <!-- Comment Form -->
                    <form id="commentForm" class="mb-4">
                        <input type="hidden" name="blog_id" value="<?= $id ?>">
                        <div class="mb-3">
                            <textarea class="form-control" name="comment" rows="3" placeholder="Tulis komentar..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim Komentar</button>
                    </form>
                <?php else: ?>
                    <div class="alert alert-info">
                        Silakan <a href="login.php">login</a> untuk menambahkan komentar.
                    </div>
                <?php endif; ?>

                <!-- Comments List -->
                <div id="commentsList">
                    <?php
                    $stmt = $conn->prepare("
                        SELECT c.*, 
                               CASE WHEN a.id IS NOT NULL THEN a.first_name ELSE u.fname END as author_name,
                               (SELECT COUNT(*) FROM comment WHERE parent_id = c.comment_id AND status = 'active') as reply_count
                        FROM comment c
                        LEFT JOIN users u ON c.user_id = u.id
                        LEFT JOIN admin a ON c.user_id = a.id
                        WHERE c.blog_id = ? AND c.parent_id IS NULL AND c.status = 'active'
                        ORDER BY c.created_at DESC
                    ");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $comments = $stmt->get_result();
                    
                    while ($comment = $comments->fetch_assoc()):
                    ?>
                        <div class="comment mb-3" data-comment-id="<?= $comment['comment_id'] ?>">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="mb-1"><?= htmlspecialchars($comment['author_name']) ?></h6>
                                        <small class="text-muted">
                                            <?= date('d M Y H:i', strtotime($comment['created_at'])) ?>
                                        </small>
                                    </div>
                                    <p class="mb-1"><?= nl2br(htmlspecialchars($comment['comment'])) ?></p>
                                    <div class="d-flex gap-2">
                                        <?php if (isset($_SESSION['user_id']) || isset($_SESSION['admin_id'])): ?>
                                            <button class="btn btn-sm btn-outline-secondary reply-btn">
                                                <i class="bi bi-reply"></i> Balas
                                            </button>
                                            <?php if (isset($_SESSION['admin_id']) || $_SESSION['user_id'] == $comment['user_id']): ?>
                                                <button class="btn btn-sm btn-outline-danger delete-comment-btn">
                                                    <i class="bi bi-trash"></i> Hapus
                                                </button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php if ($comment['reply_count'] > 0): ?>
                                            <button class="btn btn-sm btn-outline-primary view-replies-btn">
                                                <i class="bi bi-chat"></i> Lihat <?= $comment['reply_count'] ?> Balasan
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <!-- Reply Form (Hidden by default) -->
                                    <div class="reply-form mt-2" style="display: none;">
                                        <form class="replyForm">
                                            <input type="hidden" name="blog_id" value="<?= $id ?>">
                                            <input type="hidden" name="parent_id" value="<?= $comment['comment_id'] ?>">
                                            <div class="mb-2">
                                                <textarea class="form-control form-control-sm" name="comment" rows="2" placeholder="Tulis balasan..." required></textarea>
                                            </div>
                                            <div class="d-flex gap-2">
                                                <button type="submit" class="btn btn-sm btn-primary">Kirim Balasan</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary cancel-reply-btn">Batal</button>
                                            </div>
                                        </form>
                                    </div>
                                    <!-- Replies Container -->
                                    <div class="replies-container mt-2"></div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle comment submission
            document.getElementById('commentForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                submitComment(this);
            });

            // Handle reply buttons
            document.querySelectorAll('.reply-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const commentDiv = this.closest('.comment');
                    const replyForm = commentDiv.querySelector('.reply-form');
                    replyForm.style.display = replyForm.style.display === 'none' ? 'block' : 'none';
                });
            });

            // Handle cancel reply buttons
            document.querySelectorAll('.cancel-reply-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.reply-form').style.display = 'none';
                });
            });

            // Handle reply form submissions
            document.querySelectorAll('.replyForm').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    submitComment(this);
                });
            });

            // Handle delete comment buttons
            document.querySelectorAll('.delete-comment-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (confirm('Apakah Anda yakin ingin menghapus komentar ini?')) {
                        const commentId = this.closest('.comment').dataset.commentId;
                        deleteComment(commentId);
                    }
                });
            });

            // Handle view replies buttons
            document.querySelectorAll('.view-replies-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const commentDiv = this.closest('.comment');
                    const repliesContainer = commentDiv.querySelector('.replies-container');
                    const commentId = commentDiv.dataset.commentId;
                    
                    if (repliesContainer.children.length === 0) {
                        loadReplies(commentId, repliesContainer);
                    } else {
                        repliesContainer.style.display = 
                            repliesContainer.style.display === 'none' ? 'block' : 'none';
                    }
                });
            });
        });

        function submitComment(form) {
            const formData = new FormData(form);
            formData.append('action', 'add');

            fetch('comment_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Komentar berhasil ditambahkan');
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menambahkan komentar');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengirim komentar');
            });
        }

        function deleteComment(commentId) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('comment_id', commentId);

            fetch('comment_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('Komentar berhasil dihapus');
                    location.reload();
                } else {
                    alert(data.message || 'Gagal menghapus komentar');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus komentar');
            });
        }

        function loadReplies(commentId, container) {
            const formData = new FormData();
            formData.append('action', 'get_replies');
            formData.append('comment_id', commentId);

            fetch('comment_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    container.innerHTML = data.replies.map(reply => `
                        <div class="reply ms-4 mt-2 p-2 border-start">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-1">${reply.author_name}</h6>
                                <small class="text-muted">
                                    ${new Date(reply.created_at).toLocaleString('id-ID')}
                                </small>
                            </div>
                            <p class="mb-1">${reply.comment}</p>
                            ${(reply.user_id == <?= $_SESSION['user_id'] ?? 0 ?> || <?= isset($_SESSION['admin_id']) ? 'true' : 'false' ?>) ? 
                                `<button class="btn btn-sm btn-outline-danger delete-reply-btn" data-reply-id="${reply.comment_id}">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>` : ''}
                        </div>
                    `).join('');

                    // Add event listeners to delete buttons
                    container.querySelectorAll('.delete-reply-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            if (confirm('Apakah Anda yakin ingin menghapus balasan ini?')) {
                                deleteComment(this.dataset.replyId);
                            }
                        });
                    });

                    container.style.display = 'block';
                } else {
                    alert(data.message || 'Gagal memuat balasan');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat memuat balasan');
            });
        }
    </script>
</body>
</html> 