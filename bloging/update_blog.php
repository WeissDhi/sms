<?php
include 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category_id = $_POST['category'];
    $author_id = $_POST['author_id'];
    $author_type = $_POST['author_type'];
    $old_image = $_POST['old_image'];
    $status = $_POST['status']; // Ambil status dari form

    $image = $old_image;

    if (!empty($_FILES['image']['name'])) {
        $targetDir = "uploads/";
        $fileName = time() . '_' . basename($_FILES["image"]["name"]);
        $targetFilePath = $targetDir . $fileName;
        move_uploaded_file($_FILES["image"]["tmp_name"], $targetFilePath);
        $image = $fileName;

        // Hapus gambar lama jika diganti
        if ($old_image && file_exists("uploads/" . $old_image)) {
            unlink("uploads/" . $old_image);
        }
    }

    $stmt = $conn->prepare("UPDATE blogs SET title = ?, content = ?, image = ?, category_id = ?, author_id = ?, author_type = ?, status = ? WHERE id = ?");
    $stmt->bind_param("sssisssi", $title, $content, $image, $category_id, $author_id, $author_type, $status, $id);

    if ($stmt->execute()) {
        // Redirect based on author type
        if ($author_type === 'admin') {
            header("Location: ./dashboard/admin/blogs_management.php?edit=success");
        } else {
            header("Location: ./dashboard/users/blog_management.php?edit=success");
        }
        exit;
    } else {
        echo "Gagal menyimpan perubahan.";
    }
}
?>