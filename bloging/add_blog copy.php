<?php
include 'config.php';
session_start();

// Pastikan user login
if (!isset($_SESSION['author_id']) || !isset($_SESSION['author_type'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Blog</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tiny.cloud/1/nj9l4dp2auxgapch64yc16dhhguiiat5xsafdy8dj0g2zsm7/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            max-width: 100%;
            padding: 30px;
        }

        .card {
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-green {
            background-color: #28a745;
            color: white;
        }

        .btn-green:hover {
            background-color: #218838;
        }

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
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:20px; font-weight:bold }'
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
                    });
                };
                reader.readAsDataURL(file);
            }
        }
    </script>
</head>

<body>
    <div class="container">
        <div class="card">
            <h2 class="text-center mb-4">Tambah Blog</h2>
            <form action="save_blog.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="title" class="form-label">Judul:</label>
                    <textarea id="title" name="title"></textarea>
                </div>

                <div class="mb-3">
                    <label for="content" class="form-label">Konten:</label>
                    <textarea id="content" name="content"></textarea>
                </div>

                <div class="mb-3">
                    <label for="image" class="form-label">Gambar Thumbnail:</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewThumbnail(event)">
                </div>

                <div class="mb-3">
                    <label for="category" class="form-label">Kategori:</label>
                    <select class="form-select" name="category" id="category" required>
                        <option value="">-- Pilih Kategori --</option>
                        <?php
                        function getCategories($conn, $parent_id = null, $prefix = '')
                        {
                            $stmt = $conn->prepare("SELECT id, category FROM category WHERE parent_id " .
                                ($parent_id === null ? "IS NULL" : "= ?") . " ORDER BY category ASC");

                            if ($parent_id !== null) {
                                $stmt->bind_param("i", $parent_id);
                            }

                            $stmt->execute();
                            $result = $stmt->get_result();

                            while ($row = $result->fetch_assoc()) {
                                // Gunakan &nbsp; untuk spasi agar indentasi tampil rapi di HTML
                                echo '<option value="' . $row['id'] . '">' . $prefix . htmlspecialchars($row['category']) . '</option>';
                                getCategories($conn, $row['id'], $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;↳ ');
                            }

                            $stmt->close();
                        }


                        getCategories($conn);
                        ?>
                    </select>
                </div>

                <!-- Hidden author info -->
                <input type="hidden" name="author_type" value="<?= $_SESSION['author_type'] ?>">
                <input type="hidden" name="author_id" value="<?= $_SESSION['author_id'] ?>">

                <div class="mb-3">
                    <img id="thumbnailPreview" src="#" alt="Preview Thumbnail">
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status Publikasi:</label>
                    <select class="form-select" name="status" id="status" required>
                        <option value="draft">Simpan sebagai Draft</option>
                        <option value="published">Publikasikan Sekarang</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-green w-100">Simpan</button>
            </form>
        </div>
    </div>
</body>

</html>