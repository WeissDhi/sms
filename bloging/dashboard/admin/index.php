<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../../login.php");
    exit;
}

// Total artikel
$total_artikel = $conn->query("SELECT COUNT(*) AS total FROM blogs")->fetch_assoc()['total'];

// Total komentar
$total_komentar = $conn->query("SELECT COUNT(*) AS total FROM comment")->fetch_assoc()['total'];

// Total likes
$total_likes = $conn->query("SELECT COUNT(*) AS total FROM post_like")->fetch_assoc()['total'];

// Artikel per kategori
$kategori_data = $conn->query("
    SELECT category.category AS nama_kategori, COUNT(blogs.id) AS jumlah
    FROM category
    LEFT JOIN blogs ON blogs.category_id = category.id
    GROUP BY category.id
");

$kategori_labels = [];
$kategori_values = [];
while ($row = $kategori_data->fetch_assoc()) {
    $kategori_labels[] = $row['nama_kategori'];
    $kategori_values[] = $row['jumlah'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-light">
    <?php include '../components/navbar.php'; ?>
    <?php include '../components/sidebar.php'; ?>

    <div class="container py-4">
        <h2 class="mb-4">Dashboard Admin</h2>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-start border-primary border-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Artikel</h5>
                        <p class="display-6 fw-bold text-primary"><?= $total_artikel ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-start border-success border-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Komentar</h5>
                        <p class="display-6 fw-bold text-success"><?= $total_komentar ?></p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-start border-warning border-4 shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title">Total Likes</h5>
                        <p class="display-6 fw-bold text-warning"><?= $total_likes ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Jumlah Artikel per Kategori</h5>
                <canvas id="categoryChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <script>
        const ctx = document.getElementById('categoryChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($kategori_labels) ?>,
                datasets: [{
                    label: 'Jumlah Artikel',
                    data: <?= json_encode($kategori_values) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        precision: 0
                    }
                }
            }
        });
    </script>
</body>
</html>
