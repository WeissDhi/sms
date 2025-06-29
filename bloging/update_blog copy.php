<?php
include 'config.php';
session_start();

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

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
    $document = $blog['document']; // Get current document

    $title_clean = strip_tags($title);
    $slug = slugify($title_clean);
    // Pastikan slug unik (kecuali untuk blog ini)
    $base_slug = $slug;
    $counter = 1;
    while ($conn->query("SELECT id FROM blogs WHERE slug='$slug' AND id != $id")->num_rows > 0) {
        $slug = $base_slug . '-' . $counter;
        $counter++;
    }

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

    // Handle document upload
    if (!empty($_FILES['documents']['name'][0])) {
        $target_dir = 'uploads/documents/';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }
        foreach ($_FILES['documents']['name'] as $i => $name) {
            if ($_FILES['documents']['error'][$i] === 0) {
                $originalName = $_FILES['documents']['name'][$i];
                $fileType = $_FILES['documents']['type'][$i];
                $fileTmp = $_FILES['documents']['tmp_name'][$i];
                $safeName = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '_', $originalName);
                move_uploaded_file($fileTmp, $target_dir . $safeName);
                // Insert ke tabel documents
                $stmtDoc = $conn->prepare("INSERT INTO documents (blog_id, file_name, file_type) VALUES (?, ?, ?)");
                $stmtDoc->bind_param("iss", $id, $safeName, $fileType);
                $stmtDoc->execute();
                $stmtDoc->close();
            }
        }
    }
    // Hapus dokumen yang dihapus user
    if (!empty($_POST['delete_documents'])) {
        foreach ($_POST['delete_documents'] as $docId) {
            // Ambil nama file
            $q = $conn->prepare("SELECT file_name FROM documents WHERE id = ? AND blog_id = ?");
            $q->bind_param("ii", $docId, $id);
            $q->execute();
            $q->bind_result($fileName);
            if ($q->fetch() && $fileName && file_exists("uploads/documents/" . $fileName)) {
                unlink("uploads/documents/" . $fileName);
            }
            $q->close();
            // Hapus dari tabel
            $del = $conn->prepare("DELETE FROM documents WHERE id = ? AND blog_id = ?");
            $del->bind_param("ii", $docId, $id);
            $del->execute();
            $del->close();
        }
    }

    $stmt = $conn->prepare("UPDATE blogs SET title = ?, slug = ?, content = ?, image = ?, category_id = ?, author_id = ?, author_type = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ssssisssi", $title, $slug, $content, $image, $category_id, $author_id, $author_type, $status, $id);

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