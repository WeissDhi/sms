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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f8f9fa; }
        .container { max-width: 100%; padding: 30px; }
        .card { padding: 20px; margin-bottom: 30px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
        .btn-green { background-color: #28a745; color: white; }
        .btn-green:hover { background-color: #218838; }
        #thumbnailPreview {
            max-width: 100%;
            height: auto;
            display: none;
        }
    </style>
    <script>
        // TinyMCE Title
        tinymce.init({
            selector: '#title',
            menubar: false,
            toolbar: 'undo redo | fontselect fontsizeselect | bold italic underline strikethrough | alignleft aligncenter alignright | removeformat',
            plugins: [],
            height: 100,
            branding: false,
            statusbar: false,
            forced_root_block: false,
            convert_urls: false,
            entity_encoding: 'raw',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:20px; font-weight:bold; margin:0; padding:0 }',
            setup: function(editor) {
                editor.on('change', function() {
                    // Remove any paragraph tags when saving
                    var content = editor.getContent();
                    content = content.replace(/<p>/g, '').replace(/<\/p>/g, '');
                    editor.setContent(content);
                });
            }
        });

        // TinyMCE Content
        tinymce.init({
            selector: '#content',
            plugins: ['advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media', 'table', 'help', 'wordcount', 'emoticons', 'codesample'],
            toolbar: 'undo redo | formatselect fontselect fontsizeselect | bold italic underline strikethrough superscript subscript | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media anchor codesample charmap emoticons | insertdatetime table | removeformat code fullscreen preview | help',
            toolbar_mode: 'wrap',
            menubar: 'file edit view insert format tools table help',
            image_caption: true,
            height: 500,
            automatic_uploads: true,
            images_upload_url: 'upload_image.php',
            file_picker_types: 'image media',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }'
        });

        let cropper;
        let croppedImageData = null;

        function previewThumbnail(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const image = document.getElementById('thumbnailPreview');
                    image.src = e.target.result;
                    image.style.display = "block";

                    if (cropper) cropper.destroy();

                    cropper = new Cropper(image, {
                        aspectRatio: 16 / 9,
                        viewMode: 1,
                        autoCropArea: 0.65,
                        crop: function(event) {
                            // Get the cropped canvas
                            const canvas = cropper.getCroppedCanvas({
                                width: 800,  // Set desired output width
                                height: 450  // Set desired output height (16:9 ratio)
                            });
                            
                            // Convert canvas to blob
                            canvas.toBlob(function(blob) {
                                // Create a new File object from the blob
                                croppedImageData = new File([blob], file.name, {
                                    type: 'image/jpeg',
                                    lastModified: new Date().getTime()
                                });
                            }, 'image/jpeg', 0.9);
                        }
                    });
                };
                reader.readAsDataURL(file);
            }
        }

        // Modify form submission to include cropped image
        document.querySelector('form').addEventListener('submit', function(e) {
            if (croppedImageData) {
                e.preventDefault();
                
                // Create a new FormData object
                const formData = new FormData(this);
                
                // Replace the original file with the cropped one
                formData.set('image', croppedImageData);
                
                // Submit the form with the cropped image
                fetch(this.action, {
                    method: 'POST',
                    body: formData
                }).then(response => {
                    if (response.ok) {
                        window.location.href = 'dashboard/admin/blogs_management.php';
                    }
                });
            }
        });

        $(document).ready(function() {
            $('#editBlogForm').on('submit', function(e) {
                e.preventDefault();
                
                // Validate form
                const title = $('#title').val().trim();
                const content = $('#content').val().trim();
                const category = $('#category').val();
                
                if (!title || !content || !category) {
                    alert('Semua field harus diisi!');
                    return;
                }
                
                // Submit form
                this.submit();
            });
        });
    </script>
    
    <?php if(isset($_SESSION['success'])): ?>
    <script>
        alert('<?php echo $_SESSION['success']; ?>');
    </script>
    <?php unset($_SESSION['success']); endif; ?>

    <?php if(isset($_SESSION['error'])): ?>
    <script>
        alert('<?php echo $_SESSION['error']; ?>');
    </script>
    <?php unset($_SESSION['error']); endif; ?>
</head>
<body>
    <div class="container">
        <div class="card">
            <h2 class="text-center mb-4">Edit Blog</h2>
            <form action="update_blog.php" method="POST" enctype="multipart/form-data" id="editBlogForm">
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
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewThumbnail(event)">
                    <?php if ($blog['image']): ?>
                        <img src="uploads/<?= htmlspecialchars($blog['image']) ?>" alt="Current Thumbnail" class="img-fluid mt-2" style="max-width: 200px;">
                    <?php endif; ?>
                    <div class="mt-2">
                        <img id="thumbnailPreview" src="#" alt="Preview Thumbnail">
                    </div>
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
