<?php
session_start();
include 'config.php';

// Check if user is logged in
if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Get blog ID from URL
$blog_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get blog data
$stmt = $conn->prepare("SELECT * FROM blogs WHERE id = ? AND author_id = ? AND author_type = 'user'");
$stmt->bind_param("ii", $blog_id, $_SESSION['author_id']);
$stmt->execute();
$result = $stmt->get_result();
$blog = $result->fetch_assoc();

if (!$blog) {
    header("Location: dashboard/users/blog_management.php");
    exit;
}

// Get categories for dropdown
$categories_query = "SELECT * FROM category WHERE parent_id IS NULL ORDER BY category";
$categories = $conn->query($categories_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Artikel - CobainBlog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/nj9l4dp2auxgapch64yc16dhhguiiat5xsafdy8dj0g2zsm7/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #ffc107;
            --info-color: #0dcaf0;
        }

        body {
            background-color: #f8f9fa;
        }

        .navbar {
            background: linear-gradient(135deg, var(--primary-color), #0a58ca);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .navbar-brand {
            font-weight: 600;
            color: white !important;
        }

        .nav-link {
            color: rgba(255,255,255,0.9) !important;
        }

        .nav-link:hover {
            color: white !important;
        }

        .main-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .blog-form-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .form-title {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #f8f9fa;
        }

        .form-label {
            font-weight: 500;
            color: #495057;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
            padding: 0.75rem 1rem;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color), #0a58ca);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .btn-secondary {
            background: linear-gradient(135deg, var(--secondary-color), #5a6268);
            border: none;
        }

        .btn-secondary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .tox-tinymce {
            border-radius: 0.5rem !important;
            border: 1px solid #dee2e6 !important;
        }

        .image-preview {
            max-width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        .image-preview-container {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            padding: 1rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .image-preview-container:hover {
            border-color: var(--primary-color);
            background: #f1f3f5;
        }

        .image-upload-icon {
            font-size: 2rem;
            color: var(--secondary-color);
            margin-bottom: 0.5rem;
        }

        .form-text {
            color: #6c757d;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        .status-badge.draft {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-badge.published {
            background-color: #d4edda;
            color: #155724;
        }

        .current-image {
            position: relative;
            margin-bottom: 1rem;
        }

        .current-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 0.5rem;
        }

        .current-image .remove-image {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: rgba(255,255,255,0.9);
            border: none;
            border-radius: 50%;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }

        .current-image .remove-image:hover {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">CobainBlog</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard/users/index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard/users/blog_management.php">
                            <i class="bi bi-file-text"></i> Artikel Saya
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard/users/profile.php">
                            <i class="bi bi-person"></i> Profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="main-container">
        <div class="blog-form-card">
            <h2 class="form-title">
                <i class="bi bi-pencil-square"></i> Edit Artikel
            </h2>
            
            <form action="update_blog.php" method="POST" enctype="multipart/form-data" id="blogForm">
                <input type="hidden" name="blog_id" value="<?= $blog['id'] ?>">
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- Title -->
                        <div class="mb-4">
                            <label for="title" class="form-label">Judul Artikel</label>
                            <input type="text" class="form-control" id="title" name="title" value="<?= htmlspecialchars($blog['title']) ?>" required>
                        </div>

                        <!-- Content -->
                        <div class="mb-4">
                            <label for="content" class="form-label">Konten Artikel</label>
                            <textarea class="form-control" id="content" name="content" rows="15"><?= htmlspecialchars($blog['content']) ?></textarea>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Category -->
                        <div class="mb-4">
                            <label for="category" class="form-label">Kategori</label>
                            <select class="form-select" id="category" name="category" required>
                                <option value="">Pilih Kategori</option>
                                <?php while ($category = $categories->fetch_assoc()): ?>
                                    <option value="<?= $category['id'] ?>" <?= $category['id'] == $blog['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['category']) ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <!-- Thumbnail -->
                        <div class="mb-4">
                            <label class="form-label">Thumbnail Artikel</label>
                            <?php if ($blog['thumbnail']): ?>
                                <div class="current-image">
                                    <img src="uploads/<?= htmlspecialchars($blog['thumbnail']) ?>" alt="Current thumbnail">
                                    <button type="button" class="remove-image" onclick="removeCurrentImage()">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                            <div class="image-preview-container" onclick="document.getElementById('image').click()" <?= $blog['thumbnail'] ? 'style="display:none"' : '' ?>>
                                <i class="bi bi-cloud-upload image-upload-icon"></i>
                                <p class="mb-0">Klik untuk memilih gambar</p>
                                <small class="form-text">Format: JPG, PNG, GIF (Max. 2MB)</small>
                            </div>
                            <input type="file" class="d-none" id="image" name="image" accept="image/*" onchange="previewImage(this)">
                            <input type="hidden" name="current_thumbnail" value="<?= htmlspecialchars($blog['thumbnail']) ?>">
                            <input type="hidden" name="remove_thumbnail" id="remove_thumbnail" value="0">
                            <img id="imagePreview" class="image-preview" alt="Preview" style="display: none;">
                        </div>

                        <!-- Status -->
                        <div class="mb-4">
                            <label class="form-label">Status Artikel</label>
                            <div class="d-flex gap-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="draft" value="draft" <?= $blog['status'] == 'draft' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="draft">
                                        <span class="status-badge draft">Draft</span>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="published" value="published" <?= $blog['status'] == 'published' ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="published">
                                        <span class="status-badge published">Published</span>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                            <a href="dashboard/users/blog_management.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Batal
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize TinyMCE
        tinymce.init({
            selector: '#content',
            plugins: 'anchor autolink charmap codesample emoticons image link lists media searchreplace table visualblocks wordcount',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | link image media table | align lineheight | numlist bullist indent outdent | emoticons charmap | removeformat',
            height: 500,
            images_upload_url: 'upload_image.php',
            automatic_uploads: true,
            file_picker_types: 'image',
            images_reuse_filename: true,
            images_upload_handler: function (blobInfo, success, failure) {
                var xhr, formData;
                xhr = new XMLHttpRequest();
                xhr.withCredentials = false;
                xhr.open('POST', 'upload_image.php');
                xhr.onload = function() {
                    var json;
                    if (xhr.status != 200) {
                        failure('HTTP Error: ' + xhr.status);
                        return;
                    }
                    json = JSON.parse(xhr.responseText);
                    if (!json || typeof json.location != 'string') {
                        failure('Invalid JSON: ' + xhr.responseText);
                        return;
                    }
                    success(json.location);
                };
                formData = new FormData();
                formData.append('file', blobInfo.blob(), blobInfo.filename());
                xhr.send(formData);
            }
        });

        // Image Preview
        function previewImage(input) {
            const preview = document.getElementById('imagePreview');
            const container = document.querySelector('.image-preview-container');
            const currentImage = document.querySelector('.current-image');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                    container.style.display = 'none';
                    if (currentImage) {
                        currentImage.style.display = 'none';
                    }
                }
                
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Remove Current Image
        function removeCurrentImage() {
            const currentImage = document.querySelector('.current-image');
            const container = document.querySelector('.image-preview-container');
            const preview = document.getElementById('imagePreview');
            
            if (currentImage) {
                currentImage.style.display = 'none';
            }
            container.style.display = 'block';
            preview.style.display = 'none';
            document.getElementById('remove_thumbnail').value = '1';
        }

        // Form Validation
        document.getElementById('blogForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const title = document.getElementById('title').value.trim();
            const content = tinymce.get('content').getContent().trim();
            const category = document.getElementById('category').value;
            
            if (!title || !content || !category) {
                alert('Mohon lengkapi semua field yang diperlukan');
                return;
            }
            
            // If validation passes, submit the form
            this.submit();
        });
    </script>
</body>
</html>
