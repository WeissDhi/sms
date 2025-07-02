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

// Ambil dokumen terkait blog
$documents = [];
$doc_stmt = $conn->prepare("SELECT * FROM documents WHERE blog_id = ?");
$doc_stmt->bind_param("i", $blog['id']);
$doc_stmt->execute();
$doc_result = $doc_stmt->get_result();
while ($doc = $doc_result->fetch_assoc()) {
    $documents[] = $doc;
}
$doc_stmt->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Blog</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../img/sms.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tiny.cloud/1/nj9l4dp2auxgapch64yc16dhhguiiat5xsafdy8dj0g2zsm7/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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

        .back-button {
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            transition: all 0.3s ease;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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

        .current-thumbnail {
            max-width: 200px;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            margin-top: 1rem;
            transition: transform 0.3s ease;
        }

        .current-thumbnail:hover {
            transform: scale(1.05);
        }

        #cropper-container {
            max-width: 100%;
            max-height: 1500px;
            margin: 0 auto;
            overflow: hidden;
        }
        #cropper-image {
            max-width: 100%;
            max-height: 1500px;
            display: block;
            margin: 0 auto;
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

        .alert {
            border-radius: 12px;
            border: none;
            box-shadow: var(--card-shadow);
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .alert i {
            font-size: 1.25rem;
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
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .page-header h2 {
                font-size: 1.8rem;
            }

            .back-button {
                width: 100%;
                justify-content: center;
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

        /* Modern Layout Styles */
        .blog-editor {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin: 2rem 0;
            max-width: 1800px;
            margin-left: auto;
            margin-right: auto;
        }

        .editor-main {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .editor-sidebar {
            position: sticky;
            top: 2rem;
            align-self: start;
        }

        @media (max-width: 1400px) {
            .blog-editor {
                grid-template-columns: 1fr;
            }
            
            .editor-sidebar {
                position: static;
            }
        }

        /* Form Sections */
        .form-section {
            background: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }

        /* Section Titles */
        .section-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--secondary-color);
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 150px;
            height: 2px;
            background: linear-gradient(90deg, var(--secondary-color), transparent);
        }

        .section-title i {
            font-size: 1.75rem;
            color: var(--secondary-color);
            background: rgba(52, 152, 219, 0.1);
            padding: 1rem;
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .form-section:hover .section-title i {
            transform: scale(1.1) rotate(5deg);
            background: var(--gradient-primary);
            color: white;
        }

        /* Form Controls */
        .form-control, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1.25rem;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            background: white;
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.15);
        }

        /* Title Input */
        #title {
            font-size: 1.5rem;
            font-weight: 600;
            min-height: 80px;
            resize: none;
        }

        /* TinyMCE Container */
        .tox-tinymce {
            border-radius: 15px !important;
            border: 2px solid #e9ecef !important;
            margin-top: 1rem !important;
        }

        .tox .tox-edit-area__iframe {
            background: white !important;
        }

        /* Thumbnail Section */
        .thumbnail-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
        }

        .thumbnail-section:hover {
            border-color: var(--secondary-color);
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        }

        .custom-file-upload {
            position: relative;
            display: block;
            width: 100%;
        }

        .file-upload-label {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.8);
            border: 2px dashed #dee2e6;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-upload-label:hover {
            background: white;
            border-color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .file-upload-label i {
            font-size: 2rem;
            color: var(--secondary-color);
        }

        .file-upload-label span {
            font-size: 1.1rem;
            color: #495057;
        }

        .preview-label {
            background: rgba(52, 152, 219, 0.1);
            color: var(--secondary-color);
            padding: 1rem 1.5rem;
            border-radius: 12px;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .preview-label i {
            font-size: 1.25rem;
        }

        #thumbnailPreview {
            max-width: 100%;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-top: 1.5rem;
            transition: transform 0.3s ease;
        }

        #thumbnailPreview:hover {
            transform: scale(1.02);
        }

        .current-thumbnail {
            max-width: 100%;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-top: 1.5rem;
            transition: transform 0.3s ease;
        }

        .current-thumbnail:hover {
            transform: scale(1.02);
        }

        /* Submit Button */
        .btn-green {
            background: var(--gradient-success);
            color: white;
            padding: 1.5rem 2rem;
            border-radius: 15px;
            border: none;
            font-weight: 600;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            width: 100%;
            margin-top: 2rem;
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
                rgba(255, 255, 255, 0.3),
                transparent
            );
            transition: 0.5s;
        }

        .btn-green:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(46, 204, 113, 0.3);
        }

        .btn-green:hover::before {
            left: 100%;
        }

        .btn-green i {
            margin-right: 1rem;
            font-size: 1.3rem;
        }

        /* Validation Styles */
        .invalid-feedback {
            color: #dc3545;
            font-size: 0.95rem;
            margin-top: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 10px;
        }

        .invalid-feedback i {
            font-size: 1.1rem;
        }

        .was-validated .form-control:invalid {
            border-color: #dc3545;
            background-image: none;
        }

        .was-validated .form-control:valid {
            border-color: #198754;
            background-image: none;
        }

        /* Back Button */
        .back-button {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 2rem;
            border-radius: 15px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .back-button:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            color: white;
        }

        .back-button i {
            font-size: 1.3rem;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding: 1rem 0;
        }

        .page-header h2 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-header h2 i {
            font-size: 2.2rem;
            color: var(--secondary-color);
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
        let croppedImageData = null;

        // Add image validation
        function validateImage(file) {
            const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            const maxSize = 5 * 1024 * 1024; // 5MB

            if (!validTypes.includes(file.type)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format File Tidak Valid',
                    text: 'Gunakan format JPG, PNG, GIF, atau WEBP.',
                    confirmButtonColor: '#3498db'
                });
                return false;
            }

            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran File Terlalu Besar',
                    text: 'Maksimal ukuran file adalah 5MB.',
                    confirmButtonColor: '#3498db'
                });
                return false;
            }

            return true;
        }

        function previewThumbnail(event) {
            const file = event.target.files[0];
            if (file) {
                if (!validateImage(file)) {
                    event.target.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    const image = document.getElementById('cropper-image');
                    image.src = e.target.result;
                    image.style.display = "block";

                    if (cropper) cropper.destroy();

                    cropper = new Cropper(image, {
                        aspectRatio: 16 / 9,
                        viewMode: 1,
                        autoCropArea: 0.65,
                        crop: function(event) {
                            const canvas = cropper.getCroppedCanvas({
                                width: 800,
                                height: 450
                            });
                            
                            canvas.toBlob(function(blob) {
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

        // Form validation
        (function () {
            'use strict'
            var forms = document.querySelectorAll('.needs-validation')
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()

        // Document handling functions
        function validateDocument(event) {
            const file = event.target.files[0];
            if (!file) return;

            const validTypes = ['.pdf', '.doc', '.docx', '.txt', '.ppt', '.pptx'];
            const maxSize = 10 * 1024 * 1024; // 10MB
            const fileExt = '.' + file.name.split('.').pop().toLowerCase();

            if (!validTypes.includes(fileExt)) {
                Swal.fire({
                    icon: 'error',
                    title: 'Format File Tidak Valid',
                    text: 'Gunakan format PDF, DOC, DOCX, TXT, PPT, atau PPTX.',
                    confirmButtonColor: '#3498db'
                });
                event.target.value = '';
                return;
            }

            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'Ukuran File Terlalu Besar',
                    text: 'Maksimal ukuran file adalah 10MB.',
                    confirmButtonColor: '#3498db'
                });
                event.target.value = '';
                return;
            }

            // Show preview
            const preview = document.getElementById('documentPreview');
            const nameElement = document.getElementById('documentName');
            const sizeElement = document.getElementById('documentSize');

            nameElement.textContent = file.name;
            sizeElement.textContent = formatFileSize(file.size);
            preview.style.display = 'block';
        }

        function removeDocument() {
            const input = document.getElementById('document');
            const preview = document.getElementById('documentPreview');
            input.value = '';
            preview.style.display = 'none';
        }

        function removeCurrentDocument() {
            if (confirm('Apakah Anda yakin ingin menghapus lampiran saat ini?')) {
                // Add a hidden input to indicate document removal
                const form = document.getElementById('editBlogForm');
                let hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'remove_document';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);
                
                // Hide the current document preview
                const currentDoc = document.querySelector('.document-section .alert');
                if (currentDoc) {
                    currentDoc.style.display = 'none';
                }
            }
        }

        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        document.getElementById('documents').addEventListener('change', function(event) {
            const files = event.target.files;
            const preview = document.getElementById('documentsPreview');
            preview.innerHTML = '';
            if (files.length > 0) {
                preview.style.display = 'block';
                for (let i = 0; i < files.length; i++) {
                    const li = document.createElement('li');
                    li.textContent = files[i].name + ' (' + (files[i].size/1024/1024).toFixed(2) + ' MB)';
                    preview.appendChild(li);
                }
            } else {
                preview.style.display = 'none';
            }
        });

        function removeDocument(docId, btn) {
            // Remove from UI
            const li = btn.closest('li');
            li.parentNode.removeChild(li);
            // Remove hidden input so it won't be kept
            const input = li.querySelector('input[name="keep_documents[]"]');
            if (input) input.remove();
            // Add hidden input to mark for deletion
            const delInput = document.createElement('input');
            delInput.type = 'hidden';
            delInput.name = 'delete_documents[]';
            delInput.value = docId;
            document.getElementById('editBlogForm').appendChild(delInput);
        }

        document.getElementById('image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const image = document.getElementById('cropper-image');
                    image.src = e.target.result;
                    document.getElementById('cropper-container').style.display = 'block';
                    if (cropper) {
                        cropper.destroy();
                    }
                    cropper = new Cropper(image, {
                        aspectRatio: 16 / 9,
                        viewMode: 1,
                        ready: function() {
                            updateCropData();
                        },
                        crop: function() {
                            updateCropData();
                        }
                    });
                };
                reader.readAsDataURL(file);
            }
        });

        function updateCropData() {
            if (cropper) {
                const canvas = cropper.getCroppedCanvas({
                    maxWidth: 800,
                    maxHeight: 800,
                    fillColor: '#fff',
                    imageSmoothingEnabled: true,
                    imageSmoothingQuality: 'high'
                });
                if (canvas) {
                    document.getElementById('cropped_image').value = canvas.toDataURL('image/png');
                }
            }
        }
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
    <div class="container-fluid px-4">
        <div class="page-header">
            <h2><i class="fas fa-edit"></i>Edit Blog</h2>
            <a href="<?= $_SESSION['author_type'] === 'admin' ? './dashboard/admin/blogs_management.php' : './dashboard/user/blog_management.php' ?>" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success']); endif; ?>

        <?php if(isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['error']); endif; ?>

        <form action="update_blog.php" method="POST" enctype="multipart/form-data" id="editBlogForm" class="needs-validation" novalidate>
            <input type="hidden" name="id" value="<?= $blog['id'] ?>">
            <input type="hidden" name="old_image" value="<?= htmlspecialchars($blog['image']) ?>">

            <div class="blog-editor">
                <div class="editor-main">
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-heading"></i>
                            Informasi Utama
                        </div>
                        <div class="mb-4">
                            <label for="title" class="form-label">Judul Blog</label>
                            <textarea id="title" name="title" class="form-control" required placeholder="Masukkan judul blog Anda di sini..."><?= htmlspecialchars($blog['title']) ?></textarea>
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                Judul blog harus diisi
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label">Konten Blog</label>
                            <textarea id="content" name="content" class="form-control" required><?= htmlspecialchars($blog['content']) ?></textarea>
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                Konten blog harus diisi
                            </div>
                        </div>
                    </div>
                </div>

                <div class="editor-sidebar">
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-image"></i>
                            Thumbnail Blog
                        </div>
                        <div class="thumbnail-section">
                            <?php if ($blog['image']): ?>
                            <div class="mb-4">
                                <label class="form-label">Thumbnail Saat Ini:</label>
                                <img src="uploads/<?= htmlspecialchars($blog['image']) ?>" alt="Current Thumbnail" class="current-thumbnail">
                            </div>
                            <?php endif; ?>

                            <div class="custom-file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                    <span>Pilih atau seret gambar ke sini</span>
                                </label>
                                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif,image/webp" onchange="previewThumbnail(event)">
                            </div>
                            <div class="preview-label">
                                <i class="fas fa-info-circle"></i>
                                Format: JPG, PNG, GIF, WEBP (Max. 5MB)
                            </div>
                            <img id="cropper-image" src="#" alt="Preview Thumbnail" style="display: none;">
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-file-alt"></i>
                            Lampiran
                        </div>
                        <div class="document-section">
                            <?php if (count($documents) > 0): ?>
                                <div class="mb-4">
                                    <label class="form-label">Lampiran Saat Ini:</label>
                                    <ul class="list-group mb-2">
                                        <?php foreach ($documents as $doc): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span>
                                                    <i class="fas fa-file-alt me-2"></i>
                                                    <?= htmlspecialchars($doc['file_name']) ?>
                                                </span>
                                                <span>
                                                    <a href="uploads/documents/<?= htmlspecialchars($doc['file_name']) ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                                        <i class="fas fa-download"></i> Unduh
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeDocument(<?= $doc['id'] ?>, this)"><i class="fas fa-trash"></i> Hapus</button>
                                                    <input type="hidden" name="keep_documents[]" value="<?= $doc['id'] ?>">
                                                </span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <div class="custom-file-upload">
                                <label class="file-upload-label">
                                    <i class="fas fa-file-upload"></i>
                                    <span>Upload File Lampiran (PDF, DOC, DOCX, TXT, PPT, PPTX)</span>
                                </label>
                                <input type="file" id="documents" name="documents[]" accept=".pdf,.doc,.docx,.txt,.ppt,.pptx" multiple>
                            </div>
                            <div class="preview-label">
                                <i class="fas fa-info-circle"></i>
                                Format: PDF, DOC, DOCX, TXT, PPT, PPTX (Max. 10MB per file)
                            </div>
                            <ul id="documentsPreview" class="mt-3" style="display: none;"></ul>
                        </div>
                    </div>

                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-tags"></i>
                            Kategori & Status
                        </div>
                        <div class="mb-4">
                            <label for="category" class="form-label">Kategori</label>
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
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                Kategori harus dipilih
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label">Status Publikasi</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="draft" <?= $blog['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                                <option value="published" <?= $blog['status'] === 'published' ? 'selected' : '' ?>>Published</option>
                            </select>
                        </div>

                        <input type="hidden" name="author_id" value="<?= $_SESSION['author_id'] ?>">
                        <input type="hidden" name="author_type" value="<?= $_SESSION['author_type'] ?>">

                        <button type="submit" class="btn btn-green" id="updateBlogBtn">
                            <i class="fas fa-save"></i>Update Blog
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Add SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    document.getElementById('editBlogForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return;
        }
        Swal.fire({
            title: 'Update Blog?',
            text: 'Apakah Anda yakin ingin menyimpan perubahan blog ini?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
    </script>
</body>
</html>
