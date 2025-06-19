<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $category_id = $_POST["category"];
    $status = $_POST["status"];

    // Ambil dari session
    $author_id = $_SESSION['author_id'] ?? null;
    $author_type = $_SESSION['author_type'] ?? null;

    if (!$author_id || !$author_type) {
        die("Author tidak terdeteksi. Silakan login ulang.");
    }

    $image = "";
    $document = "";

    // Handle image upload
    if (!empty($_FILES["image"]["name"])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $image = time() . '_' . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image);
    }

    // Handle document upload
    if (!empty($_FILES["document"]["name"])) {
        $target_dir = "uploads/documents/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $document = time() . '_' . basename($_FILES["document"]["name"]);
        move_uploaded_file($_FILES["document"]["tmp_name"], $target_dir . $document);
    }

    function slugify($text) {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
        $text = preg_replace('/-+/', '-', $text);
        $text = trim($text, '-');
        return $text;
    }
    $title_clean = strip_tags($title);
    $slug = slugify($title_clean);
    // Pastikan slug unik
    $base_slug = $slug;
    $counter = 1;
    while ($conn->query("SELECT id FROM blogs WHERE slug='$slug'")->num_rows > 0) {
        $slug = $base_slug . '-' . $counter;
        $counter++;
    }

    // Modify the SQL query to include document field
    $stmt = $conn->prepare("INSERT INTO blogs (title, slug, content, image, document, category_id, author_id, author_type, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssssissss", $title, $slug, $content, $image, $document, $category_id, $author_id, $author_type, $status);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}