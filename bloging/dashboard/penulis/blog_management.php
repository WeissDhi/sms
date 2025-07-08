<?php
session_start();
include '../../config.php';

// Cek apakah user login dan bertipe 'penulis'
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'penulis') {
    header("Location: ../../login.php");
    exit;
}

$penulis_id = $_SESSION['author_id'];

// Get search parameter
$search_display = isset($_GET['search']) ? trim($_GET['search']) : '';
$search = !empty($search_display) ? '%' . $search_display . '%' : '';

// Get penulis's blog statistics
$stats_query = "
    SELECT 
        COUNT(*) as total_blogs,
        SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_blogs,
        SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_blogs,
        SUM(views) as total_views
    FROM blogs 
    WHERE author_id = ? AND author_type = 'penulis'
";
$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("i", $penulis_id);
$stats_stmt->execute();
$stats = $stats_stmt->get_result()->fetch_assoc();

// Build the query - Removed server-side sorting to avoid conflict with DataTables
$query = "
    SELECT b.*, c.category as category_name 
    FROM blogs b 
    LEFT JOIN category c ON b.category_id = c.id 
    WHERE b.author_id = ? AND b.author_type = 'penulis'
";

if (!empty($search_display)) {
    $query .= " AND (b.title LIKE ? OR c.category LIKE ?)";
}

// Default order by created_at DESC
$query .= " ORDER BY b.created_at DESC";

$stmt = $conn->prepare($query);

// Bind parameters based on whether there's a search term
if (!empty($search_display)) {
    $stmt->bind_param("iss", $penulis_id, $search, $search);
} else {
    $stmt->bind_param("i", $penulis_id);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Manajemen Blog</title>
    <link rel="shortcut icon" href="../../../img/sms.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
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

        /* DataTables custom styling */
        .dataTables_wrapper {
            padding: 0;
        }

        .dataTables_filter {
            margin-bottom: 1rem;
        }

        .dataTables_info {
            margin-top: 1rem;
        }

        .dataTables_paginate {
            margin-top: 1rem;
        }
    </style>
</head>

<body class="bg-light">
    <?php include '../components/navbar.php' ?>
    <?php include '../components/penulis-sidebar.php' ?>

    <?php if (isset($_SESSION['success'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '<?php echo $_SESSION['success']; ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
            });
        </script>
    <?php unset($_SESSION['success']);
    endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '<?php echo $_SESSION['error']; ?>',
                    timer: 3000,
                    showConfirmButton: false
                });
            });
        </script>
    <?php unset($_SESSION['error']);
    endif; ?>

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

        <!-- Search Container - Removed manual search, let DataTables handle it -->
        <?php if (!empty($search_display)): ?>
            <div class="search-container">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Menampilkan hasil pencarian untuk: <strong>"<?= htmlspecialchars($search_display) ?>"</strong>
                    <a href="blog_management.php" class="btn btn-outline-secondary btn-sm ms-2">
                        <i class="bi bi-x-lg"></i> Hapus Filter
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="blogsTable">
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Views</th>
                            <th>Gambar</th>
                            <th>Penulis</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <a href="../../../<?= htmlspecialchars($row['slug']) ?>" class="blog-title text-decoration-none">
                                            <?= htmlspecialchars(strip_tags($row['title'])) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= htmlspecialchars($row['category_name'] ?? 'Tidak diketahui') ?>
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
                                        <?php if (!empty($row['image'])): ?>
                                            <div class="thumbnail-container">
                                                <img src="../../uploads/<?= htmlspecialchars(basename($row['image'])) ?>" alt="Thumbnail" loading="lazy">
                                            </div>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark">
                                                <i class="bi bi-image"></i> No Image
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= htmlspecialchars($_SESSION['username'] ?? 'Penulis') ?>
                                        </span>
                                    </td>
                                    <td data-order="<?= strtotime($row['created_at']) ?>">
                                        <?= date('d M Y H:i', strtotime($row['created_at'])) ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="../../edit_blog.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-action" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="../../../<?= htmlspecialchars($row['slug']) ?>" class="btn btn-info btn-action" title="Lihat" target="_blank">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-danger btn-action delete-blog" 
                                                    data-id="<?= $row['id'] ?>" 
                                                    data-title="<?= htmlspecialchars(strip_tags($row['title'])) ?>" 
                                                    title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="bi bi-file-text mb-2 d-block" style="font-size: 2rem;"></i>
                                        <p class="mb-0">Belum ada artikel yang ditulis</p>
                                        <a href="../../add_blog.php" class="btn btn-primary mt-2">
                                            <i class="bi bi-plus-lg"></i> Buat Artikel Pertama
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Fungsi untuk menghilangkan tag HTML dari string
            function stripHtml(html) {
                var tmp = document.createElement('DIV');
                tmp.innerHTML = html;
                return tmp.textContent || tmp.innerText || '';
            }

            // Destroy existing DataTable instance if exists
            if ($.fn.DataTable.isDataTable('#blogsTable')) {
                $('#blogsTable').DataTable().destroy();
            }

            // Initialize DataTable with proper configuration
            const table = $('#blogsTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
                },
                columnDefs: [
                    {
                        targets: 4, // Gambar column
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 7, // Action column
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 6, // Date column
                        type: 'num' // Use numeric sorting for timestamp
                    }
                ],
                order: [[6, 'desc']], // Sort by date (created_at) descending
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rtip',
                drawCallback: function(settings) {
                    // Re-bind delete event handlers after table redraw
                    bindDeleteHandlers();
                }
            });

            // Function to bind delete event handlers
            function bindDeleteHandlers() {
                $('.delete-blog').off('click').on('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const blogId = $(this).data('id');
                    let blogTitle = $(this).data('title');
                    
                    // Clean the title
                    blogTitle = stripHtml(blogTitle);
                    
                    // Show confirmation dialog
                    Swal.fire({
                        title: 'Hapus Artikel?',
                        text: `Anda yakin ingin menghapus artikel "${blogTitle}"?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus!',
                        cancelButtonText: 'Batal',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading
                            Swal.fire({
                                title: 'Menghapus...',
                                text: 'Mohon tunggu sebentar',
                                allowOutsideClick: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            
                            // Redirect to delete script
                            window.location.href = `../../delete_blog.php?id=${blogId}`;
                        }
                    });
                });
            }

            // Initial binding
            bindDeleteHandlers();

            // Handle search from URL parameter
            <?php if (!empty($search_display)): ?>
                table.search('<?= addslashes($search_display) ?>').draw();
            <?php endif; ?>
        });
    </script>
</body>

</html>