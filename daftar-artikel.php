<?php
session_start();
include './bloging/config.php';

$dashboardLink = '#';
$showDashboard = false;

if (isset($_SESSION['author_type'])) {
    $showDashboard = true;
    if ($_SESSION['author_type'] === 'admin') {
        $dashboardLink = './bloging/dashboard/admin/index.php';
    } elseif ($_SESSION['author_type'] === 'user') {
        $dashboardLink = './bloging/dashboard/users/index.php';
    }
}

$keyword = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// Ambil semua kategori untuk mapping id -> data
$allCategories = [];
$resCat = $conn->query("SELECT id, category, parent_id FROM category");
while ($cat = $resCat->fetch_assoc()) {
    $allCategories[$cat['id']] = $cat;
}

$sql = "
    SELECT 
        blogs.*, 
        category.category AS category_name,
        category.parent_id AS category_parent_id,
        admin.first_name AS admin_first,
        admin.last_name AS admin_last,
        users.fname AS user_name
    FROM blogs
    LEFT JOIN category ON blogs.category_id = category.id
    LEFT JOIN admin ON blogs.author_type = 'admin' AND blogs.author_id = admin.id
    LEFT JOIN users ON blogs.author_type = 'user' AND blogs.author_id = users.id
    WHERE blogs.status = 'published'
";

if (!empty($keyword)) {
    $sql .= " AND (
        blogs.title LIKE '%$keyword%' OR
        blogs.content LIKE '%$keyword%' OR
        category.category LIKE '%$keyword%' OR
        admin.first_name LIKE '%$keyword%' OR
        admin.last_name LIKE '%$keyword%' OR
        users.fname LIKE '%$keyword%'
    )";
}

$sql .= " ORDER BY blogs.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Daftar Blog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/linearicons.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css" />
    <link rel="stylesheet" href="css/owl.carousel.css" />
    <link rel="stylesheet" href="css/main.css" />
</head>

<body>
    <?php include './components/navbar.php'; ?>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #FAFAF0; /* Ivory */
        }

        .search-bar {
            margin: 30px 0;
        }

        .search-bar input {
            border-radius: 30px 0 0 30px;
            border-color: #2E7D32; /* Forest green */
        }

        .search-bar input:focus {
            border-color: #2E7D32 !important;
            box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
            outline: none;
        }

        .search-bar button {
            border-radius: 0 30px 30px 0;
            background: linear-gradient(135deg, #2E7D32, #C5E1A5); /* Forest green to lime */
            border: none;
            color: #fff;
            font-weight: 600;
            transition: background 0.3s;
        }
        .search-bar button:hover {
            background: #2E7D32;
            color: #fff;
        }

        .card {
            transition: all 0.4s ease;
            border: 3px solid #2E7D32; /* Forest green */
            border-radius: 20px;
            overflow: hidden;
            background: #EEEEEE; /* Soft gray */
            backdrop-filter: blur(6px);
            box-shadow: 0 8px 25px rgba(46, 125, 50, 0.18);
            position: relative;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(46, 125, 50, 0.25);
        }

        .card-img-top {
            height: 180px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .card:hover .card-img-top {
            transform: scale(1.05);
        }

        .card-body {
            padding: 1rem 1.25rem;
        }

        .card-body h5 {
            font-weight: 600;
            font-size: 1.1rem;
            color: #2C2C2C; /* Charcoal */
        }

        .card-body p {
            font-size: 0.95rem;
            color: #555;
        }

        .badge {
            font-size: 0.75rem;
            margin-right: 5px;
        }

        .bg-success {
            background: #2E7D32 !important; /* Forest green */
            color: #fff !important;
        }
        .bg-light {
            background: #C5E1A5 !important; /* Lime */
            color: #2E7D32 !important;
        }
        .text-success {
            color: #2E7D32 !important;
        }

        .meta-info {
            font-size: 0.8rem;
            color: #777;
        }

        .icon {
            margin-right: 5px;
            vertical-align: middle;
            color: #2E7D32; /* Forest green */
        }

        .no-results {
            background: #EEEEEE; /* Soft gray */
            padding: 50px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.2rem;
            color: #777;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-read-more {
            background: linear-gradient(135deg, #2E7D32, #C5E1A5); /* Forest green to lime */
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 30px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-read-more:hover {
            background: #2E7D32;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            color: #fff;
        }

        .btn-read-more span {
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }

        .btn-read-more:hover span {
            transform: translateX(5px);
        }
    </style>

    <div class="container py-4">

        <!-- 🔍 Search Bar -->
        <form method="GET" class="search-bar">
            <div class="input-group shadow-sm">
                <input type="text" name="search" class="form-control" placeholder="Cari artikel berdasarkan judul, penulis, kategori..." value="<?= htmlspecialchars($keyword) ?>">
                <button class="btn btn-primary" type="submit">Cari</button>
            </div>
        </form>

        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100">
                            <?php if ($row['image']): ?>
                                <img src="bloging/uploads/<?= basename($row['image']) ?>" class="card-img-top" alt="Blog Image">
                            <?php else: ?>
                                <img src="https://via.placeholder.com/400x200?text=No+Image" class="card-img-top" alt="No Image">
                            <?php endif; ?>

                            <div class="card-body d-flex flex-column">
                                <!-- Judul -->
                                <h5 class="text-truncate" title="<?= strip_tags($row['title']) ?>">
                                    <?= mb_strimwidth(strip_tags($row['title']), 0, 60, '...') ?>
                                </h5>

                                <!-- Meta Info -->
                                <div class="meta-info mb-2">
                                    <span class="icon">📅</span><?= date('d M Y', strtotime($row['created_at'])) ?><br>
                                    <span class="icon">✍️</span>
                                    <?php if ($row['author_type'] === 'admin'): ?>
                                        <?= htmlspecialchars($row['admin_first'] . ' ' . $row['admin_last']) ?>
                                    <?php elseif ($row['author_type'] === 'user'): ?>
                                        <?= htmlspecialchars($row['user_name']) ?>
                                    <?php else: ?>
                                        Tidak diketahui
                                    <?php endif; ?>
                                </div>

                                <!-- Badge Kategori -->
                                <div class="mb-2">
                                    <?php
                                    if (!empty($row['category_name'])) {
                                        $cat_id = $row['category_id'];
                                        $cat = isset($allCategories[$cat_id]) ? $allCategories[$cat_id] : null;
                                        if ($cat && $cat['parent_id'] && isset($allCategories[$cat['parent_id']])) {
                                            // Ada parent, tampilkan dua badge
                                            $parent = $allCategories[$cat['parent_id']];
                                            ?>
                                            <a href="?search=<?= urlencode($parent['category']) ?>" class="badge bg-success text-white text-decoration-none me-1">
                                                #<?= htmlspecialchars($parent['category']) ?>
                                            </a>
                                            <a href="?search=<?= urlencode($cat['category']) ?>" class="badge bg-light text-success text-decoration-none">
                                                #<?= htmlspecialchars($cat['category']) ?>
                                            </a>
                                            <?php
                                        } else {
                                            // Tidak ada parent, tampilkan satu badge
                                            ?>
                                            <a href="?search=<?= urlencode($cat['category']) ?>" class="badge bg-success text-white text-decoration-none">
                                                #<?= htmlspecialchars($cat['category']) ?>
                                            </a>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div>

                                <!-- Konten Ringkas -->
                                <p class="card-text mb-3"><?= mb_strimwidth(strip_tags($row['content']), 0, 100, '...') ?></p>

                                <!-- Tombol -->
                                <a href="/smsblog/<?= htmlspecialchars($row['slug']) ?>" class="btn btn-read-more mt-auto">
                                    Baca Selengkapnya <span>&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="no-results">Tidak ada artikel ditemukan.</div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include './components/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>