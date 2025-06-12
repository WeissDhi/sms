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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/nj9l4dp2auxgapch64yc16dhhguiiat5xsafdy8dj0g2zsm7/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #2ecc71;
            --background-color: #f8f9fa;
            --card-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            --gradient-primary: linear-gradient(135deg, #2c3e50, #3498db);
            --gradient-success: linear-gradient(135deg, #2ecc71, #27ae60);
            --gradient-warning: linear-gradient(135deg, #f1c40f, #f39c12);
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-color);
            color: var(--primary-color);
            line-height: 1.6;
            min-height: 100vh;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 0% 0%, rgba(52, 152, 219, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(46, 204, 113, 0.1) 0%, transparent 50%);
            z-index: -1;
        }

        .container {
            max-width: 1200px;
            padding: 2rem;
            margin: 0 auto;
        }

        .page-header {
            background: var(--gradient-primary);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.5;
        }

        .page-header h2 {
            margin: 0;
            font-weight: 600;
            font-size: 2.2rem;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
        }

        .page-header h2 i {
            margin-right: 0.5rem;
            color: var(--accent-color);
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 2.5rem;
            margin-bottom: 2rem;
            box-shadow: var(--card-shadow);
            border: none;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .form-label {
            font-weight: 500;
            color: var(--primary-color);
            margin-bottom: 0.75rem;
            font-size: 1.1rem;
        }

        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.875rem;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.15);
        }

        .btn-green {
            background: var(--gradient-success);
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 1.1rem;
            position: relative;
            overflow: hidden;
        }

        .btn-green::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                120deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: 0.5s;
        }

        .btn-green:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(46, 204, 113, 0.3);
        }

        .btn-green:hover::before {
            left: 100%;
        }

        .thumbnail-container {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
        }

        .thumbnail-container:hover {
            border-color: var(--secondary-color);
            background: #f1f8ff;
        }

        #thumbnailPreview {
            max-width: 100%;
            height: auto;
            display: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
        }

        .form-section {
            margin-bottom: 2.5rem;
            padding-bottom: 2.5rem;
            border-bottom: 2px solid #e9ecef;
            position: relative;
        }

        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .section-title {
            color: var(--primary-color);
            font-size: 1.4rem;
            font-weight: 600;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--secondary-color);
        }

        .section-title i {
            color: var(--secondary-color);
            font-size: 1.6rem;
            background: rgba(52, 152, 219, 0.1);
            padding: 0.5rem;
            border-radius: 10px;
        }

        .tox-tinymce {
            border-radius: 12px !important;
            border: 2px solid #e9ecef !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
        }

        .tox .tox-toolbar__group {
            border: none !important;
            padding: 0 0.5rem !important;
        }

        .preview-label {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .preview-label::before {
            content: 'ℹ️';
            font-size: 1.1rem;
        }

        .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%232c3e50' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            background-size: 16px 12px;
            padding-right: 2.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            .card {
                padding: 1.5rem;
            }

            .page-header {
                padding: 1.5rem;
            }

            .page-header h2 {
                font-size: 1.8rem;
            }

            .section-title {
                font-size: 1.2rem;
            }

            .btn-green {
                padding: 0.875rem 1.5rem;
                font-size: 1rem;
            }
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--secondary-color);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-color);
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
            automatic_uploads: false,
            images_upload_url: 'upload_image.php',
            file_picker_types: 'image media',
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            images_reuse_filename: true,
            images_upload_handler: function (blobInfo, progress) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', 'upload_image.php');

                    xhr.upload.onprogress = (e) => {
                        progress(e.loaded / e.total * 100);
                    };

                    xhr.onload = function() {
                        if (xhr.status === 403) {
                            reject({ message: 'HTTP Error: ' + xhr.status, remove: true });
                            return;
                        }

                        if (xhr.status < 200 || xhr.status >= 300) {
                            reject('HTTP Error: ' + xhr.status);
                            return;
                        }

                        const json = JSON.parse(xhr.responseText);

                        if (!json || typeof json.location != 'string') {
                            reject('Invalid JSON: ' + xhr.responseText);
                            return;
                        }

                        // Extract just the filename from the path
                        const filename = json.location.split('/').pop();
                        resolve('uploads/' + filename);
                    };

                    xhr.onerror = function () {
                        reject('Image upload failed due to a XHR Transport error');
                    };

                    const formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    xhr.send(formData);
                });
            }
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
        <div class="page-header">
            <h2><i class="fas fa-plus-circle me-2"></i>Tambah Blog Baru</h2>
        </div>
        
        <div class="card">
            <form action="save_blog.php" method="POST" enctype="multipart/form-data">
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-heading"></i>
                        Informasi Utama
                    </div>
                    <div class="mb-4">
                        <label for="title" class="form-label">Judul Blog</label>
                        <textarea id="title" name="title" class="form-control"></textarea>
                    </div>

                    <div class="mb-4">
                        <label for="content" class="form-label">Konten Blog</label>
                        <textarea id="content" name="content" class="form-control"></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-image"></i>
                        Thumbnail Blog
                    </div>
                    <div class="mb-4">
                        <label for="image" class="form-label">Upload Gambar Thumbnail</label>
                        <input type="file" class="form-control" id="image" name="image" accept="image/*" onchange="previewThumbnail(event)">
                        <div class="preview-label">Rasio yang disarankan: 16:9</div>
                    </div>

                    <div class="thumbnail-container">
                        <img id="thumbnailPreview" src="#" alt="Preview Thumbnail">
                    </div>
                </div>

                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-tags"></i>
                        Kategori & Status
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="category" class="form-label">Kategori</label>
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

                        <div class="col-md-6 mb-4">
                            <label for="status" class="form-label">Status Publikasi</label>
                            <select class="form-select" name="status" id="status" required>
                                <option value="draft">Simpan sebagai Draft</option>
                                <option value="published">Publikasikan Sekarang</option>
                            </select>
                        </div>
                    </div>
                </div>

                <input type="hidden" name="author_type" value="<?= $_SESSION['author_type'] ?>">
                <input type="hidden" name="author_id" value="<?= $_SESSION['author_id'] ?>">

                <button type="submit" class="btn btn-green w-100">
                    <i class="fas fa-save me-2"></i>Simpan Blog
                </button>
            </form>
        </div>
    </div>
</body>

</html>