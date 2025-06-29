<?php
session_start();
include 'bloging/config.php';

if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];
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
        WHERE b.slug = ?
    ");
    $stmt->bind_param("s", $slug);
} else if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
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
} else {
    header("Location: index.php");
    exit;
}
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();

if (!$blog) {
    header("Location: index.php");
    exit;
}

// Gunakan id dari $blog untuk view counter
$id = $blog['id'];

// Prevent duplicate view counting in the same session
$viewed_key = 'viewed_blog_' . $id;
$current_views = $blog['views'];

if (!isset($_SESSION[$viewed_key])) {
    // Increment view count only if not viewed in this session
    $update_stmt = $conn->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
    $update_stmt->bind_param("i", $id);
    $update_stmt->execute();
    
    // Get the updated view count
    $view_stmt = $conn->prepare("SELECT views FROM blogs WHERE id = ?");
    $view_stmt->bind_param("i", $id);
    $view_stmt->execute();
    $view_result = $view_stmt->get_result();
    $view_data = $view_result->fetch_assoc();
    $current_views = $view_data['views'];
    
    // Mark as viewed in this session
    $_SESSION[$viewed_key] = true;
    
    $update_stmt->close();
    $view_stmt->close();
}

// Ambil semua kategori untuk mapping id -> data
$allCategories = [];
$resCat = $conn->query("SELECT id, category, parent_id FROM category");
while ($cat = $resCat->fetch_assoc()) {
    $allCategories[$cat['id']] = $cat;
}

// Setelah $blog sudah didapatkan, ambil dokumen terkait
$documents = [];
$doc_stmt = $conn->prepare("SELECT * FROM documents WHERE blog_id = ?");
$doc_stmt->bind_param("i", $id);
$doc_stmt->execute();
$doc_result = $doc_stmt->get_result();
while ($doc = $doc_result->fetch_assoc()) {
    $documents[] = $doc;
}
$doc_stmt->close();
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Additional client-side view tracking for analytics (optional)
        window.addEventListener('load', function() {
            // Send an additional tracking request for analytics purposes
            fetch('bloging/increment_view.php?id=<?= $id ?>', {
                method: 'GET',
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    console.log('View tracked successfully:', data.views, 'total views');
                } else {
                    console.log('View tracking response:', data);
                }
            })
            .catch(error => {
                console.log('View tracking error:', error);
            });
        });
    </script>
    <style>
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            background: #fff;
            border-radius: 15px;
        }

        .card-body {
            padding: 2rem;
        }

        .blog-content {
            overflow: visible;
            word-wrap: break-word;
        }

        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1rem 0;
        }

        .blog-content p {
            margin-bottom: 1.5rem;
            line-height: 1.8;
            font-size: 1.1rem;
            color: #2c3e50;
        }

        .blog-content h1,
        .blog-content h2,
        .blog-content h3,
        .blog-content h4,
        .blog-content h5,
        .blog-content h6 {
            margin-top: 2rem;
            margin-bottom: 1rem;
            color: #2c3e50;
        }

        .blog-content ul,
        .blog-content ol {
            margin-bottom: 1.5rem;
            padding-left: 2rem;
        }

        .blog-content li {
            margin-bottom: 0.5rem;
        }

        .blog-content blockquote {
            border-left: 4px solid #3498db;
            padding-left: 1rem;
            margin: 1.5rem 0;
            color: #666;
            font-style: italic;
        }

        .blog-content pre {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            margin: 1.5rem 0;
        }

        .blog-content code {
            background: #f8f9fa;
            padding: 0.2rem 0.4rem;
            border-radius: 4px;
            font-family: monospace;
        }

        .blog-content table {
            width: 100%;
            margin: 1.5rem 0;
            border-collapse: collapse;
        }

        .blog-content table th,
        .blog-content table td {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
        }

        .blog-content table th {
            background: #f8f9fa;
        }

        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }

            .blog-content p {
                font-size: 1rem;
            }
        }

        .blog-detail-card {
            border: 3px solid #8fc333;
            border-radius: 24px;
            box-shadow: 0 8px 25px rgba(143, 195, 51, 0.18);
            background: #fff;
            margin-bottom: 2rem;
        }
        .blog-detail-card .card-body {
            padding: 2.5rem 2rem;
        }
        @media (max-width: 768px) {
            .blog-detail-card .card-body {
                padding: 1.2rem 0.7rem;
            }
        }
    </style>
</head>

<body class="bg-light">
    <div class="container py-4">
        <div class="card blog-detail-card">
            <div class="card-body">
                <h1 class="card-title mb-4" style="font-weight:700; color:#333; font-size:2rem;"><?= strip_tags($blog['title']) ?></h1>

                <!-- Meta Info & Badge Kategori -->
                <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
                  <span class="badge bg-primary">
                    <i class="bi bi-eye-fill"></i> <?= number_format($current_views) ?> views
                  </span>
                  <?php
                  // Badge kategori & subkategori
                  if (!empty($blog['category_id']) && isset($allCategories[$blog['category_id']])) {
                      $cat = $allCategories[$blog['category_id']];
                      if ($cat['parent_id'] && isset($allCategories[$cat['parent_id']])) {
                          $parent = $allCategories[$cat['parent_id']];
                          ?>
                          <a href="category.php?id=<?= $parent['id'] ?>" class="badge bg-success text-white text-decoration-none me-1">
                              #<?= htmlspecialchars($parent['category']) ?>
                          </a>
                          <a href="category.php?id=<?= $cat['id'] ?>" class="badge bg-light text-success text-decoration-none">
                              #<?= htmlspecialchars($cat['category']) ?>
                          </a>
                          <?php
                      } else {
                          ?>
                          <a href="category.php?id=<?= $cat['id'] ?>" class="badge bg-success text-white text-decoration-none">
                              #<?= htmlspecialchars($cat['category']) ?>
                          </a>
                          <?php
                      }
                  }
                  ?>
                  <span class="badge bg-<?= $blog['status'] === 'published' ? 'success' : 'warning' ?>">
                    <i class="bi bi-<?= $blog['status'] === 'published' ? 'check-circle' : 'clock' ?>-fill"></i>
                    <?= ucfirst($blog['status']) ?>
                  </span>
                  <span class="badge bg-secondary">
                    <i class="bi bi-person-fill"></i> <?= htmlspecialchars($blog['author_name']) ?>
                  </span>
                  <span class="badge bg-info text-dark">
                    <i class="bi bi-calendar3"></i> <?= date('d M Y H:i', strtotime($blog['created_at'])) ?>
                  </span>
                </div>

                <?php if (!empty($blog['image'])): ?>
                    <img src="bloging/uploads/<?= htmlspecialchars($blog['image']) ?>" alt="Blog Thumbnail" class="img-fluid rounded mb-4" style="max-height: 400px; width: 100%; object-fit: cover; border-radius:16px; box-shadow:0 4px 16px rgba(143,195,51,0.18);">
                <?php endif; ?>

                <div class="blog-content" style="margin-bottom:2rem;">
                    <?php
                    // Replace image paths in content to include bloging/ prefix
                    $content = $blog['content'];
                    $content = preg_replace('/src="uploads\/([^"]+)"/', 'src="bloging/uploads/$1"', $content);
                    echo $content;
                    ?>
                </div>
                <?php if (count($documents) > 0): ?>
                    <div class="attachment-section mb-4">
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="fas fa-file-alt me-2"></i>
                            <div>
                                <strong>Lampiran:</strong>
                                <ul class="mb-0 mt-1">
                                    <?php foreach ($documents as $doc): ?>
                                        <li>
                                            <a href="bloging/uploads/documents/<?= htmlspecialchars($doc['file_name']) ?>" target="_blank" class="btn btn-sm btn-outline-primary mb-1">
                                                <i class="fas fa-download"></i> Unduh <?= htmlspecialchars($doc['file_name']) ?>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-3">
            <a href="javascript:window.history.back()" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Kembali
            </a>
        </div>

        <!-- Comment Section -->
        <div class="card mt-4">
            <div class="card-header">
                <h5 class="mb-0">Komentar</h5>
            </div>
            <div class="card-body">
                <?php if (isset($_SESSION['author_id'])): ?>
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
                                        <?php if (isset($_SESSION['author_id'])): ?>
                                            <button class="btn btn-sm btn-outline-secondary reply-btn">
                                                <i class="bi bi-reply"></i> Balas
                                            </button>
                                            <?php if ($_SESSION['author_type'] === 'admin' || ($_SESSION['author_type'] === 'user' && $_SESSION['author_id'] == $comment['user_id'])): ?>
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
                    const commentId = this.closest('.comment').dataset.commentId;
                    Swal.fire({
                        title: 'Hapus Komentar?',
                        text: 'Komentar yang dihapus tidak dapat dikembalikan. Lanjutkan?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            deleteComment(commentId);
                        }
                    });
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

            fetch('bloging/comment_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Komentar berhasil ditambahkan',
                            confirmButtonColor: '#3498db'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message || 'Gagal menambahkan komentar',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat mengirim komentar',
                        confirmButtonColor: '#d33'
                    });
                });
        }

        function deleteComment(commentId) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('comment_id', commentId);

            fetch('bloging/comment_actions.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Komentar berhasil dihapus',
                            confirmButtonColor: '#3498db'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message || 'Gagal menghapus komentar',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat menghapus komentar',
                        confirmButtonColor: '#d33'
                    });
                });
        }

        function loadReplies(commentId, container) {
            const formData = new FormData();
            formData.append('action', 'get_replies');
            formData.append('comment_id', commentId);

            fetch('bloging/comment_actions.php', {
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
                            ${(reply.user_id == <?= $_SESSION['author_id'] ?? 0 ?> || <?= $_SESSION['author_type'] === 'admin' ? 'true' : 'false' ?>) ? 
                                `<button class="btn btn-sm btn-outline-danger delete-reply-btn" data-reply-id="${reply.comment_id}">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>` : ''}
                        </div>
                    `).join('');

                        // Add event listeners to delete buttons
                        container.querySelectorAll('.delete-reply-btn').forEach(btn => {
                            btn.addEventListener('click', function() {
                                Swal.fire({
                                    title: 'Hapus Balasan?',
                                    text: 'Balasan yang dihapus tidak dapat dikembalikan. Lanjutkan?',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonColor: '#d33',
                                    cancelButtonColor: '#3085d6',
                                    confirmButtonText: 'Ya, hapus!',
                                    cancelButtonText: 'Batal'
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        deleteComment(this.dataset.replyId);
                                    }
                                });
                            });
                        });

                        container.style.display = 'block';
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: data.message || 'Gagal memuat balasan',
                            confirmButtonColor: '#d33'
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat memuat balasan',
                        confirmButtonColor: '#d33'
                    });
                });
        }
    </script>
</body>

</html>