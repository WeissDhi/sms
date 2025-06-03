<?php
include 'config.php';
session_start();

// Cek apakah user sudah login dan memiliki akses
if (!isset($_SESSION['author_id']) || !isset($_SESSION['author_type'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    // Ambil nama file gambar sebelum dihapus
    $queryImage = $conn->query("SELECT image FROM blogs WHERE id = $id");
    $row = $queryImage->fetch_assoc();

    // Hapus gambar dari direktori jika ada
    if ($row && !empty($row['image']) && file_exists('uploads/' . $row['image'])) {
        unlink('uploads' . $row['image']);
    }

    // Hapus data blog dari database
    $delete = $conn->query("DELETE FROM blogs WHERE id = $id");

    if ($delete) {
        $_SESSION['success'] = "Blog berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus blog.";
    }
} else {
    $_SESSION['error'] = "ID blog tidak ditemukan.";
}

header("Location: ./dashboard/index.php");
exit;
