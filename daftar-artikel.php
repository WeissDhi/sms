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

$sql = "
    SELECT 
        blogs.*, 
        category.category AS category_name,
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
            background: linear-gradient(to right, #eef2f3, #8e9eab);
        }

        .search-bar {
            margin: 30px 0;
        }

        .search-bar input {
            border-radius: 30px 0 0 30px;
            border-color: #8fc333;
        }

        .search-bar input:focus {
            border-color: #8fc333 !important;
            box-shadow: 0 0 0 0.2rem rgba(143, 195, 51, 0.25);
            outline: none;
        }

        .search-bar button {
            border-radius: 0 30px 30px 0;
        }

        .card {
            transition: all 0.4s ease;
            border: 3px solid #8fc333;
            border-radius: 20px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(6px);
            box-shadow: 0 8px 25px rgba(143, 195, 51, 0.45);
            position: relative;
        }

        .card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 15px 35px rgba(143, 195, 51, 0.6);
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
            color: #333;
        }

        .card-body p {
            font-size: 0.95rem;
            color: #555;
        }

        .badge {
            font-size: 0.75rem;
            margin-right: 5px;
        }

        .meta-info {
            font-size: 0.8rem;
            color: #777;
        }

        .icon {
            margin-right: 5px;
            vertical-align: middle;
            color: #6c757d;
        }

        .no-results {
            background: #fff;
            padding: 50px;
            border-radius: 10px;
            text-align: center;
            font-size: 1.2rem;
            color: #777;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-read-more {
            background: linear-gradient(135deg, #8fc333, #00c6ff);
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
            background: linear-gradient(135deg, #8fc333, #8fc333);
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
                                    <?php if (!empty($row['category_name'])): ?>
                                        <a href="?search=<?= urlencode($row['category_name']) ?>" class="badge bg-gradient bg-info text-dark text-decoration-none">
                                            #<?= htmlspecialchars($row['category_name']) ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <!-- Konten Ringkas -->
                                <p class="card-text mb-3"><?= mb_strimwidth(strip_tags($row['content']), 0, 100, '...') ?></p>

                                <!-- Tombol -->
                                <a href="view_detail.php?id=<?= $row['id'] ?>" class="btn btn-read-more mt-auto">
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