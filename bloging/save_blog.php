<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $category_id = $_POST["category"];
    $status = $_POST["status"]; // Tambahan

    // Ambil dari session
    $author_id = $_SESSION['author_id'] ?? null;
    $author_type = $_SESSION['author_type'] ?? null;

    if (!$author_id || !$author_type) {
        die("Author tidak terdeteksi. Silakan login ulang.");
    }

    $image = "";

    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $image = time() . '_' . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);
    }

    $stmt = $conn->prepare("INSERT INTO blogs (title, content, image, category_id, author_id, author_type, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("sssisss", $title, $content, $image, $category_id, $author_id, $author_type, $status);

    if ($stmt->execute()) {
        echo "Blog berhasil disimpan. <a href='../daftar-artikel.php'>Lihat Blog</a>";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}