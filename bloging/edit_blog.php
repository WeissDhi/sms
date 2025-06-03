<?php 
include 'config.php';
session_start();

if (!isset($_SESSION['author_id']) || !isset($_SESSION['author_type'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "ID tidak ditemukan.";
    exit;
}

$id = intval($_GET['id']);
$query = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
$query->bind_param("i", $id);
$query->execute();
$result = $query->get_result();
$blog = $result->fetch_assoc();

if (!$blog) {
    echo "Blog tidak ditemukan.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Blog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/nj9l4dp2auxgapch64yc16dhhguiiat5xsafdy8dj0g2zsm7/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        tinymce.init({
            selector: '#title',
            menubar: false,
            toolbar: 'undo redo | fontselect fontsizeselect | bold italic underline strikethrough | alignleft aligncenter alignright | removeformat',
            height: 100,
            branding: false,
            statusbar: false,
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:20px; font-weight:bold }'
        });

        tinymce.init({
            selector: '#content',
            plugins: ['advlist', 'autolink', 'lists', 'link', 'image', 'preview', 'fullscreen'],
            toolbar: 'undo redo | formatselect fontselect fontsizeselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link image | fullscreen preview',
            height: 500,
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
        });
    </script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .container { max-width: 100%; padding: 30px; }
        .card { padding: 20px; margin-bottom: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
        .btn-green { background-color: #28a745; color: white; }
        .btn-green:hover { background-color: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 class="text-center mb-4">Edit Blog</h2>
            <form action="update_blog.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $blog['id'] ?>">
                <input type="hidden" name="old_image" value="<?= htmlspecialchars($blog['image']) ?>">
                <div class="mb-3">
                    <label for="title" class="form-label">Judul:</label>
                    <textarea id="title" name="title"><?= htmlspecialchars($blog['title']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="content" class="form-label">Konten:</label>
                    <textarea id="content" name="content"><?= htmlspecialchars($blog['content']) ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="image" class="form-label">Ganti Thumbnail:</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <?php if ($blog['image']): ?>
                        <img src="uploads/<?= htmlspecialchars($blog['image']) ?>" alt="Current Thumbnail" class="img-fluid mt-2" style="max-width: 200px;">
                    <?php endif; ?>
                </div>
                <div class="mb-3">
                    <label for="category" class="form-label">Kategori:</label>
                    <select class="form-select" name="category" id="category" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        $res = $conn->query("SELECT * FROM category ORDER BY category ASC");
                        while ($row = $res->fetch_assoc()) {
                            $selected = ($row['id'] == $blog['category_id']) ? 'selected' : '';
                            echo '<option value="' . $row['id'] . '" ' . $selected . '>' . htmlspecialchars($row['category']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="status" class="form-label">Status:</label>
                    <select class="form-select" id="status" name="status" required>
                        <option value="draft" <?= $blog['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="published" <?= $blog['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                    </select>
                </div>
                <input type="hidden" name="author_id" value="<?= $_SESSION['author_id'] ?>">
                <input type="hidden" name="author_type" value="<?= $_SESSION['author_type'] ?>">
                <button type="submit" class="btn btn-green w-100">Update</button>
            </form>
        </div>
    </div>
</body>
</html>
