<?php
session_start();
include '../../config.php';

// Cek apakah login sebagai admin
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../../login.php");
    exit;
}

// Get search and sort parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_display = $search; // For display in input
$search = "%" . $conn->real_escape_string($search) . "%"; // For database query
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'created_at';
$order = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Build the query with search and sort
$query = "
    SELECT blogs.*, 
           category.category AS category_name,
           users.fname AS user_name,
           admin.first_name AS admin_first,
           admin.last_name AS admin_last
    FROM blogs
    LEFT JOIN category ON blogs.category_id = category.id
    LEFT JOIN users ON blogs.author_type = 'user' AND blogs.author_id = users.id
    LEFT JOIN admin ON blogs.author_type = 'admin' AND blogs.author_id = admin.id
    WHERE 1=1
";

if (!empty($search_display)) {
    $query .= " AND (blogs.title LIKE ? OR blogs.content LIKE ? OR category.category LIKE ?)";
}

// Add sorting
$allowed_sort_columns = ['title', 'category_name', 'status', 'views', 'created_at'];
$sort = in_array($sort, $allowed_sort_columns) ? $sort : 'created_at';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

$query .= " ORDER BY " . ($sort === 'category_name' ? 'category.category' : 'blogs.' . $sort) . " " . $order;

// Prepare and execute the query
$stmt = $conn->prepare($query);
if (!empty($search_display)) {
    $search_param = "%" . $search_display . "%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();

// Get statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_blogs,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_blogs,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_blogs,
        SUM(views) as total_views
    FROM blogs
";
$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #0dcaf0;
        }
        
        body {
            background-color: #f8f9fa;
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
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            margin-bottom: 2rem;
        }
        
        .table-container {
            background: white;
            padding: 1.5rem;
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: var(--secondary-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
            letter-spacing: 0.5px;
        }
        
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .sort-icon {
            cursor: pointer;
            color: var(--secondary-color);
            transition: color 0.2s;
        }
        
        .sort-icon:hover {
            color: var(--primary-color);
        }
        
        .sort-icon.active {
            color: var(--primary-color);
        }
        
        .blog-title {
            font-weight: bold;
            font-size: 1.1em;
            color: var(--primary-color);
        }
        
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
        }
        
        .btn-group .btn {
            padding: 0.4rem 0.6rem;
            border-radius: 0.5rem;
            margin: 0 0.2rem;
        }
        
        .btn-group .btn:hover {
            transform: translateY(-2px);
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
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        .action-buttons .btn {
            padding: 0.4rem 0.6rem;
            border-radius: 0.5rem;
        }
    </style>
</head>

<body>
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>
    
    <div class="dashboard-header">
        <div class="container">
            <h2 class="mb-2">Dashboard Admin</h2>
            <p class="mb-0">Kelola seluruh artikel dan pengguna dengan mudah</p>
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

        <!-- Search and Actions -->
        <div class="search-container">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="d-flex gap-2">
                    <a href="../../add_blog.php" class="btn btn-success d-inline-flex align-items-center gap-2">
                        <i class="bi bi-plus-lg"></i> Tulis Artikel Baru
                    </a>
                    <a href="../../../index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                        <i class="bi bi-house-door-fill"></i> HOME
                    </a>
                </div>
                
                <form class="d-flex gap-2 flex-grow-1" method="GET" style="max-width: 500px;">
                    <div class="input-group">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control" placeholder="Cari artikel..." value="<?= htmlspecialchars($search_display) ?>">
                        <button type="submit" class="btn btn-primary">Cari</button>
                        <?php if (!empty($search_display)): ?>
                            <a href="blogs_management.php" class="btn btn-outline-secondary">Reset</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                Judul
                                <a href="?sort=title&order=<?= $sort === 'title' && $order === 'ASC' ? 'DESC' : 'ASC' ?><?= !empty($search_display) ? '&search=' . urlencode($search_display) : '' ?>" class="sort-icon <?= $sort === 'title' ? 'active' : '' ?>">
                                    <i class="bi bi-sort-alpha-<?= $sort === 'title' && $order === 'ASC' ? 'down' : 'up' ?>"></i>
                                </a>
                            </th>
                            <th>
                                Kategori
                                <a href="?sort=category_name&order=<?= $sort === 'category_name' && $order === 'ASC' ? 'DESC' : 'ASC' ?><?= !empty($search_display) ? '&search=' . urlencode($search_display) : '' ?>" class="sort-icon <?= $sort === 'category_name' ? 'active' : '' ?>">
                                    <i class="bi bi-sort-alpha-<?= $sort === 'category_name' && $order === 'ASC' ? 'down' : 'up' ?>"></i>
                                </a>
                            </th>
                            <th>
                                Status
                                <a href="?sort=status&order=<?= $sort === 'status' && $order === 'ASC' ? 'DESC' : 'ASC' ?><?= !empty($search_display) ? '&search=' . urlencode($search_display) : '' ?>" class="sort-icon <?= $sort === 'status' ? 'active' : '' ?>">
                                    <i class="bi bi-sort-alpha-<?= $sort === 'status' && $order === 'ASC' ? 'down' : 'up' ?>"></i>
                                </a>
                            </th>
                            <th>
                                Views
                                <a href="?sort=views&order=<?= $sort === 'views' && $order === 'ASC' ? 'DESC' : 'ASC' ?><?= !empty($search_display) ? '&search=' . urlencode($search_display) : '' ?>" class="sort-icon <?= $sort === 'views' ? 'active' : '' ?>">
                                    <i class="bi bi-sort-numeric-<?= $sort === 'views' && $order === 'ASC' ? 'down' : 'up' ?>"></i>
                                </a>
                            </th>
                            <th>Gambar</th>
                            <th>Penulis</th>
                            <th>
                                Tanggal Dibuat
                                <a href="?sort=created_at&order=<?= $sort === 'created_at' && $order === 'ASC' ? 'DESC' : 'ASC' ?><?= !empty($search_display) ? '&search=' . urlencode($search_display) : '' ?>" class="sort-icon <?= $sort === 'created_at' ? 'active' : '' ?>">
                                    <i class="bi bi-sort-numeric-<?= $sort === 'created_at' && $order === 'ASC' ? 'down' : 'up' ?>"></i>
                                </a>
                            </th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="blog-title"><?= strip_tags($row['title']) ?></td>
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
                                <td>
                                    <?php
                                    $imagePath = !empty($row['image']) ? "../../uploads/" . basename($row['image']) : "";
                                    if (!empty($imagePath) && file_exists($imagePath)): ?>
                                        <div class="thumbnail-container">
                                            <img src="<?= $imagePath ?>" alt="Thumbnail" class="img-fluid">
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">
                                            <i class="bi bi-image"></i> No Image
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= $row['author_type'] === 'admin'
                                            ? htmlspecialchars($row['admin_first'] . ' ' . $row['admin_last'])
                                            : htmlspecialchars($row['user_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted">
                                        <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="../../edit_blog.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="../../delete_blog.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus?')" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <a href="../../../<?= htmlspecialchars($row['slug']) ?>" class="btn btn-info btn-sm" title="Lihat">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>