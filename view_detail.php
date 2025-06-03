<?php
include './bloging/config.php';

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM blogs WHERE id = $id");

if ($result->num_rows == 0) {
    die("Blog tidak ditemukan.");
}

$row = $result->fetch_assoc();

// Perbaiki path <img src="uploads/..."> dari konten blog
$fixedContent = str_replace('src="uploads/', 'src="bloging/uploads/', $row['content']);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(strip_tags($row['title'])) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fdfdfd;
            padding-top: 40px;
        }

        .blog-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .blog-image {
            width: 100%;
            height: auto;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .blog-content {
            font-size: 1.05rem;
            line-height: 1.8;
            color: #333;
        }

        .back-btn {
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="blog-container">
            <h2><?= strip_tags($row['title']) ?></h2>

            <?php if (!empty($row['image'])): ?>
                <img src="bloging/uploads/<?= basename($row['image']) ?>" class="blog-image" alt="Blog Image">
            <?php endif; ?>

            <div class="blog-content">
                <?= $fixedContent ?>
            </div>

            <a href="daftar-artikel.php" class="btn btn-outline-secondary back-btn">← Kembali</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>