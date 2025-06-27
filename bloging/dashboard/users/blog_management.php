<?php
session_start();
include '../../config.php';

// Cek apakah user login dan bertipe 'user'
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'user') {
    header("Location: ../../login.php");
    exit;
}

$user_id = $_SESSION['author_id'];

// Get search parameter
$search_display = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = !empty($search_display) ? '%' . $search_display . '%' : '';

// Get sort parameters
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Validate sort column
$allowed_sort_columns = ['title', 'category', 'status', 'views', 'created_at'];
if (!in_array($sort, $allowed_sort_columns)) {
    $sort = 'created_at';
}

// Validate order
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

// Get user's blog statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_blogs,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_blogs,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_blogs,
        SUM(views) as total_views
    FROM blogs 
    WHERE author_id = ? AND author_type = 'user'
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $user_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Build the query
$query = "
    SELECT b.*, c.category as category_name 
    FROM blogs b 
    LEFT JOIN category c ON b.category_id = c.id 
    WHERE b.author_id = ? AND b.author_type = 'user'
";

if (!empty($search_display)) {
    $query .= " AND (b.title LIKE ? OR c.category LIKE ?)";
}

// Add sorting
$query .= " ORDER BY " . ($sort === 'category' ? 'c.category' : 'b.' . $sort) . " " . $order;

$stmt = $conn->prepare($query);

// Bind parameters based on whether there's a search term
if (!empty($search_display)) {
    $stmt->bind_param("iss", $user_id, $search, $search);
} else {
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
            padding: 2rem 0;
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

        .search-container {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }

        .table-container {
            background: white;
            border-radius: 1rem;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }

        .table td {
            vertical-align: middle;
        }

        .blog-title {
            font-weight: 600;
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

        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }

        .btn-action {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            margin: 0 0.2rem;
        }

        .sort-link {
            color: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .sort-link:hover {
            color: var(--primary-color);
        }

        .sort-icon {
            font-size: 0.8rem;
        }
    </style>
</head>

<body class="bg-light">
    <?php include '../components/navbar.php' ?>
    <?php include '../components/user-sidebar.php' ?>

    <?php if(isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '<?php echo $_SESSION['success']; ?>',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: '<?php echo $_SESSION['error']; ?>',
            timer: 3000,
            showConfirmButton: false
        });
    </script>
    <?php unset($_SESSION['error']); endif; ?>

    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-2">Manajemen Blog</h1>
                    <p class="mb-0">Kelola artikel blog Anda di sini</p>
                </div>
                <div class="col-md-4 text-md-end mt-3 mt-md-0">
                    <a href="../../add_blog.php" class="btn btn-light">
                        <i class="bi bi-plus-lg"></i> Tambah Artikel
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
                        <i class="bi bi-pencil-square"></i>
                    </div>
                    <div class="stats-number"><?= number_format($stats['draft_blogs']) ?></div>
                    <div class="stats-label">Artikel Draft</div>
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

        <!-- Search Container -->
        <div class="search-container">
            <form method="GET" class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search_display) ?>" placeholder="Cari artikel...">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                </div>
                <?php if (!empty($search_display)): ?>
                    <div class="col-md-6 text-md-end">
                        <a href="blog_management.php" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg"></i> Hapus Filter
                        </a>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table" id="blogsTable">
                    <thead>
                        <tr>
                            <th>
                                <a href="?sort=title&order=<?= $sort === 'title' && $order === 'ASC' ? 'DESC' : 'ASC' ?><?= !empty($search_display) ? '&search=' . urlencode($search_display) : '' ?>" class="sort-link">
                                    Judul
                                    <?php if ($sort === 'title'): ?>
                                        <i class="bi bi-arrow-<?= $order === 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Thumbnail</th>
                            <th>
                                <a href="?sort=category&order=<?= $sort === 'category' && $order === 'ASC' ? 'DESC' : 'ASC' ?><?= !empty($search_display) ? '&search=' . urlencode($search_display) : '' ?>" class="sort-link">
                                    Kategori
                                    <?php if ($sort === 'category'): ?>
                                        <i class="bi bi-arrow-<?= $order === 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?sort=status&order=<?= $sort === 'status' && $order === 'ASC' ? 'DESC' : 'ASC' ?><?= !empty($search_display) ? '&search=' . urlencode($search_display) : '' ?>" class="sort-link">
                                    Status
                                    <?php if ($sort === 'status'): ?>
                                        <i class="bi bi-arrow-<?= $order === 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?sort=views&order=<?= $sort === 'views' && $order === 'ASC' ? 'DESC' : 'ASC' ?><?= !empty($search_display) ? '&search=' . urlencode($search_display) : '' ?>" class="sort-link">
                                    Views
                                    <?php if ($sort === 'views'): ?>
                                        <i class="bi bi-arrow-<?= $order === 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>
                                <a href="?sort=created_at&order=<?= $sort === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC' ?><?= !empty($search_display) ? '&search=' . urlencode($search_display) : '' ?>" class="sort-link">
                                    Tanggal Dibuat
                                    <?php if ($sort === 'created_at'): ?>
                                        <i class="bi bi-arrow-<?= $order === 'ASC' ? 'up' : 'down' ?> sort-icon"></i>
                                    <?php endif; ?>
                                </a>
                            </th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="../../../<?= htmlspecialchars($row['slug']) ?>" class="blog-title text-decoration-none">
                                            <?= strip_tags($row['title']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if (!empty($row['image'])): ?>
                                            <div class="thumbnail-container">
                                                <img src="../../uploads/<?= basename($row['image']) ?>" alt="Thumbnail">
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Tidak ada gambar</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= htmlspecialchars($row['category_name']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $row['status'] === 'published' ? 'success' : 'warning' ?>">
                                            <?= ucfirst($row['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <i class="bi bi-eye-fill"></i> <?= number_format($row['views']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="../../edit_blog.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-action" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="../../../<?= htmlspecialchars($row['slug']) ?>" class="btn btn-info btn-action" title="Lihat">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="../../delete_blog.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm delete-blog" data-id="<?php echo $row['id']; ?>" data-title="<?php echo htmlspecialchars($row['title']); ?>" title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <?php if (!empty($search_display)): ?>
                                        <p class="mb-0">Tidak ada artikel yang ditemukan untuk pencarian "<?= htmlspecialchars($search_display) ?>"</p>
                                    <?php else: ?>
                                        <p class="mb-0">Belum ada artikel yang ditulis</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#blogsTable').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                }
            });

            // Sweet Alert Delete Confirmation
            $('.delete-blog').on('click', function(e) {
                e.preventDefault();
                const blogId = $(this).data('id');
                const blogTitle = $(this).data('title');
                
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: `Anda akan menghapus blog "${blogTitle}"`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = `../../delete_blog.php?id=${blogId}`;
                    }
                });
            });
        });
    </script>
</body>

</html>