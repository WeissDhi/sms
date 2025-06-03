<?php
session_start();
include '../../config.php';

// Cek apakah user login dan bertipe 'user'
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'user') {
    header("Location: ../../login.php");
    exit;
}

$user_id = (int) $_SESSION['author_id']; // PENTING: definisikan user_id dari session

// Ambil data profil user
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();

if (!$user) {
    // Jika user tidak ditemukan, logout atau redirect
    session_destroy();
    header("Location: ../../login.php");
    exit;
}

// Jumlah blog oleh user
$blog_count = $conn->query("SELECT COUNT(*) as total FROM blogs WHERE author_id = $user_id AND author_type = 'user'")->fetch_assoc()['total'];

// Jumlah komentar
$comment_count = $conn->query("SELECT COUNT(*) as total FROM comment WHERE user_id = $user_id")->fetch_assoc()['total'];

// Jumlah like
$like_count = $conn->query("SELECT COUNT(*) as total FROM post_like WHERE liked_by = $user_id")->fetch_assoc()['total'];

// Daftar blog user
$blogs = $conn->query("SELECT * FROM blogs WHERE author_id = $user_id AND author_type = 'user'");

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Dashboard User</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }

        .box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
        }
    </style>
</head>
    <?php include '../components/navbar.php' ?>
    <?php include '../components/user-sidebar.php' ?>
<body>

    <h2>Selamat Datang, <?= htmlspecialchars($user['fname']) ?>!</h2>

    <div class="box">
        <h3>Statistik Anda</h3>
        <p>Jumlah Blog: <strong><?= $blog_count ?></strong></p>
        <p>Jumlah Komentar: <strong><?= $comment_count ?></strong></p>
        <p>Jumlah Like yang Diberikan: <strong><?= $like_count ?></strong></p>
    </div>

    <div class="box">
        <h3>Daftar Blog Anda</h3>
        <?php if ($blogs->num_rows > 0): ?>
            <ul>
                <?php while ($blog = $blogs->fetch_assoc()): ?>
                    <li><strong><?= htmlspecialchars($blog['title']) ?></strong> - Status: <?= $blog['status'] ?></li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>Anda belum memiliki blog.</p>
        <?php endif; ?>
    </div>

</body>

</html>