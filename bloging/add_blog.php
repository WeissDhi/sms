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

        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.875rem;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .form-control:focus,
        .form-select:focus {
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
            background: linear-gradient(120deg,
                    transparent,
                    rgba(255, 255, 255, 0.2),
                    transparent);
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
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
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .form-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
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
        .form-control,
        .form-select {
            border: 2px solid #e9ecef;
            border-radius: 15px;
            padding: 1.25rem;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus,
        .form-select:focus {
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
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-top: 1.5rem;
            transition: transform 0.3s ease;
        }

        #thumbnailPreview:hover {
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
            background: linear-gradient(120deg,
                    transparent,
                    rgba(255, 255, 255, 0.3),
                    transparent);
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

        .document-section {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 20px;
            padding: 2rem;
            margin-top: 2rem;
            border: 2px dashed #dee2e6;
            transition: all 0.3s ease;
        }

        .document-section:hover {
            border-color: var(--secondary-color);
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
        }

        #documentPreview {
            margin-top: 1rem;
        }

        #documentPreview .alert {
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0;
        }

        #documentPreview .btn-close {
            padding: 0.5rem;
            margin: -0.5rem;
        }

        #documentName {
            font-size: 1rem;
            margin-bottom: 0.25rem;
        }

        #documentSize {
            font-size: 0.875rem;
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

        /* Hide original textareas when TinyMCE is loaded */
        .tox-tinymce ~ textarea {
            display: none !important;
        }

        /* Fallback styling for when TinyMCE fails to load */
        .fallback-textarea {
            display: block !important;
            min-height: 200px;
            resize: vertical;
        }
    </style>
    <script>
        window.authorType = "<?= $_SESSION['author_type'] ?>";
        
        // TinyMCE Title
        tinymce.init({
            selector: '#title',
            menubar: false,
            toolbar: 'undo redo | fontselect fontsizeselect | bold italic underline strikethrough | alignleft aligncenter alignright | removeformat',
            plugins: [],
            height: 100,
            branding: false,
            statusbar: false,
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:20px; font-weight:bold }',
            setup: function(editor) {
                editor.on('change keyup', function() {
                    // Update hidden validation input
                    const content = editor.getContent().trim();
                    document.getElementById('title_validation').value = content;
                    
                    // Remove required attribute from original textarea to prevent validation errors
                    document.getElementById('title').removeAttribute('required');
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
            setup: function(editor) {
                editor.on('change keyup', function() {
                    // Update hidden validation input
                    const content = editor.getContent().trim();
                    document.getElementById('content_validation').value = content;
                    
                    // Remove required attribute from original textarea to prevent validation errors
                    document.getElementById('content').removeAttribute('required');
                });
            },
            images_upload_handler: function(blobInfo, progress) {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', 'upload_image.php');

                    xhr.upload.onprogress = (e) => {
                        progress(e.loaded / e.total * 100);
                    };

                    xhr.onload = function() {
                        if (xhr.status === 403) {
                            reject({
                                message: 'HTTP Error: ' + xhr.status,
                                remove: true
                            });
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

                    xhr.onerror = function() {
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

        // Fungsi updateCropData
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

        document.addEventListener('DOMContentLoaded', function() {
            // Check if TinyMCE is available
            setTimeout(function() {
                if (typeof tinymce === 'undefined') {
                    console.warn('TinyMCE not available, using fallback textarea');
                    const titleTextarea = document.getElementById('title');
                    const contentTextarea = document.getElementById('content');
                    
                    if (titleTextarea) {
                        titleTextarea.style.display = 'block';
                        titleTextarea.classList.add('fallback-textarea');
                        titleTextarea.setAttribute('required', 'required');
                    }
                    
                    if (contentTextarea) {
                        contentTextarea.style.display = 'block';
                        contentTextarea.classList.add('fallback-textarea');
                        contentTextarea.setAttribute('required', 'required');
                    }
                }
            }, 3000); // Wait 3 seconds for TinyMCE to load

            // Validasi input file gambar
            const imageInput = document.getElementById('image');
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    if (!validateImage(file)) {
                        this.value = '';
                        return;
                    }
                    
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

            // Form submit handler
            document.getElementById('addBlogForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Validasi form manual menggunakan hidden inputs
                const titleValidation = document.getElementById('title_validation');
                const contentValidation = document.getElementById('content_validation');
                const imageInput = document.getElementById('image');
                const parentSelect = document.getElementById('category_parent');
                const childSelect = document.getElementById('category_child');
                
                // Update validation inputs dengan content dari TinyMCE atau textarea fallback
                let titleContent, contentContent;
                
                if (typeof tinymce !== 'undefined' && tinymce.get('title')) {
                    titleContent = tinymce.get('title').getContent().trim();
                    contentContent = tinymce.get('content').getContent().trim();
                } else {
                    // Fallback: use textarea values directly
                    titleContent = document.getElementById('title').value.trim();
                    contentContent = document.getElementById('content').value.trim();
                }
                
                titleValidation.value = titleContent;
                contentValidation.value = contentContent;
                
                // Validasi judul
                if (!titleContent || titleContent === '<p></p>' || titleContent === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Judul Belum Diisi',
                        text: 'Silakan isi judul blog terlebih dahulu',
                        confirmButtonColor: '#3498db'
                    });
                    return;
                }
                
                // Validasi konten
                if (!contentContent || contentContent === '<p></p>' || contentContent === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Konten Belum Diisi',
                        text: 'Silakan isi konten blog terlebih dahulu',
                        confirmButtonColor: '#3498db'
                    });
                    return;
                }
                
                // Validasi gambar
                if (!imageInput.files[0]) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gambar Belum Dipilih',
                        text: 'Silakan pilih gambar thumbnail terlebih dahulu',
                        confirmButtonColor: '#3498db'
                    });
                    return;
                }
                
                // Debug: Check TinyMCE content
                console.log('Title content:', titleContent);
                console.log('Content length:', contentContent.length);
                
                // Debug: Check category selection
                console.log('Parent category value:', parentSelect.value);
                console.log('Child category value:', childSelect.value);
                console.log('Child category display:', childSelect.style.display);
                
                // Ensure correct category is selected
                if (childSelect.style.display !== 'none' && childSelect.value) {
                    childSelect.setAttribute('name', 'category');
                    parentSelect.removeAttribute('name');
                    console.log('Using child category:', childSelect.value);
                } else {
                    parentSelect.setAttribute('name', 'category');
                    childSelect.removeAttribute('name');
                    console.log('Using parent category:', parentSelect.value);
                }
                
                const croppedImage = document.getElementById('cropped_image');
                if (!croppedImage || !croppedImage.value) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gambar Belum Di-crop',
                        text: 'Silakan crop gambar terlebih dahulu sebelum menyimpan',
                        confirmButtonColor: '#3498db'
                    });
                    return;
                }
                
                // Convert base64 to blob
                fetch(croppedImage.value)
                    .then(res => res.blob())
                    .then(blob => {
                        // Create new FormData
                        const formData = new FormData();
                        
                        // Manually add all form fields
                        formData.append('title', titleContent);
                        formData.append('content', contentContent);
                        
                        // Handle category selection properly
                        let categoryValue = '';
                        
                        if (childSelect.style.display !== 'none' && childSelect.value) {
                            categoryValue = childSelect.value;
                        } else {
                            categoryValue = parentSelect.value;
                        }
                        
                        console.log('Selected category value:', categoryValue);
                        formData.append('category', categoryValue);
                        
                        // Validate category selection
                        if (!categoryValue) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Kategori Belum Dipilih',
                                text: 'Silakan pilih kategori terlebih dahulu',
                                confirmButtonColor: '#3498db'
                            });
                            return;
                        }
                        
                        formData.append('status', document.getElementById('status').value);
                        formData.append('author_id', document.querySelector('input[name="author_id"]').value);
                        formData.append('author_type', document.querySelector('input[name="author_type"]').value);
                        
                        // Add documents if any
                        const documentsInput = document.getElementById('documents');
                        if (documentsInput.files.length > 0) {
                            for (let i = 0; i < documentsInput.files.length; i++) {
                                formData.append('documents[]', documentsInput.files[i]);
                            }
                        }
                        
                        // Debug: Log form data
                        console.log('Form data before modification:');
                        for (let [key, value] of formData.entries()) {
                            console.log(key, value);
                        }
                        
                        // Add cropped image
                        formData.append('image', blob, 'cropped_image.jpg');
                        
                        // Debug: Log form data after modification
                        console.log('Form data after modification:');
                        for (let [key, value] of formData.entries()) {
                            console.log(key, value);
                        }
                        
                        // Submit form
                        fetch('save_blog.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                return response.text();
                            })
                            .then(result => {
                                console.log('Response from save_blog.php:', result);
                                console.log('Response length:', result.length);
                                console.log('Response trimmed:', result.trim());
                                
                                if (result.trim() === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil!',
                                        text: 'Blog berhasil disimpan',
                                        confirmButtonColor: '#3498db'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            if (window.authorType === 'admin') {
                                                window.location.href = './dashboard/admin/blogs_management.php';
                                            } else {
                                                window.location.href = './dashboard/users/blog_management.php';
                                            }
                                        }
                                    });
                                } else {
                                    throw new Error(`Gagal menyimpan blog. Response: ${result}`);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: `Terjadi kesalahan saat menyimpan blog: ${error.message}`,
                                    confirmButtonColor: '#3498db'
                                });
                            });
                    });
            });
        });

        // Handle TinyMCE loading errors
        window.addEventListener('error', function(e) {
            if (e.target.src && e.target.src.includes('tinymce')) {
                console.warn('TinyMCE failed to load, using fallback textarea');
                // Fallback: show original textareas if TinyMCE fails
                const titleTextarea = document.getElementById('title');
                const contentTextarea = document.getElementById('content');
                
                if (titleTextarea) {
                    titleTextarea.style.display = 'block';
                    titleTextarea.classList.add('fallback-textarea');
                }
                
                if (contentTextarea) {
                    contentTextarea.style.display = 'block';
                    contentTextarea.classList.add('fallback-textarea');
                }
            }
        });

        // SweetAlert untuk notifikasi sukses setelah redirect
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('add') === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Blog berhasil disimpan',
                    confirmButtonColor: '#3498db'
                });
                // Hapus parameter dari URL agar tidak muncul lagi saat reload
                if (window.history.replaceState) {
                    const url = window.location.origin + window.location.pathname;
                    window.history.replaceState({}, document.title, url);
                }
            }
        });
    </script>
</head>

<body>
    <div class="container-fluid px-4">
        <div class="page-header">
            <h2><i class="fas fa-plus-circle"></i>Tambah Blog Baru</h2>
            <a href="<?= $_SESSION['author_type'] === 'admin' ? './dashboard/admin/blogs_management.php' : './dashboard/users/blog_management.php' ?>" class="back-button">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php unset($_SESSION['success']);
        endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php unset($_SESSION['error']);
        endif; ?>

        <form action="save_blog.php" method="POST" enctype="multipart/form-data" id="addBlogForm" class="needs-validation">
            <div class="blog-editor">
                <div class="editor-main">
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-heading"></i>
                            Informasi Utama
                        </div>
                        <div class="mb-4">
                            <label for="title" class="form-label">Judul Blog</label>
                            <textarea id="title" name="title" class="form-control" required placeholder="Masukkan judul blog Anda di sini..."></textarea>
                            <!-- Hidden input untuk validasi form -->
                            <input type="hidden" id="title_validation" name="title_validation" required>
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                Judul blog harus diisi
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="content" class="form-label">Konten Blog</label>
                            <textarea id="content" name="content" class="form-control" required></textarea>
                            <!-- Hidden input untuk validasi form -->
                            <input type="hidden" id="content_validation" name="content_validation" required>
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
                            <div class="custom-file-upload">
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <div id="cropper-container" style="display: none;">
                                    <img id="cropper-image" src="" alt="Preview">
                                </div>
                            </div>
                            <input type="hidden" name="cropped_image" id="cropped_image">
                            <div class="preview-label">
                                <i class="fas fa-info-circle"></i>
                                Format: JPG, PNG, GIF, WEBP (Max. 5MB)
                            </div>
                        </div>
                    </div>
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-file-alt"></i>
                            Materi Tambahan
                        </div>
                        <div class="document-section">
                            <div class="custom-file-upload">
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
                            <?php
                            // Ambil semua kategori dan subkategori
                            $categories = [];
                            $res = $conn->query("SELECT id, category, parent_id FROM category ORDER BY category ASC");
                            while ($row = $res->fetch_assoc()) {
                                $categories[] = $row;
                            }
                            // Pisahkan parent dan child
                            $parentCategories = array_filter($categories, function ($cat) {
                                return $cat['parent_id'] === null;
                            });
                            $categoriesByParent = [];
                            foreach ($categories as $cat) {
                                $categoriesByParent[$cat['parent_id']][] = $cat;
                            }
                            ?>
                            <select class="form-select" name="category_parent" id="category_parent" required>
                                <option value="">-- Pilih Kategori Utama --</option>
                                <?php foreach ($parentCategories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['category']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-circle"></i>
                                Kategori harus dipilih
                            </div>
                            <select class="form-select mt-3" name="category" id="category_child" style="display:none;">
                                <option value="">-- Pilih Subkategori --</option>
                            </select>
                            <div id="category-tags" class="mt-3"></div>
                            <script>
                                // Data kategori dari PHP ke JS
                                const categories = <?= json_encode($categories) ?>;
                                const categoriesByParent = {};
                                categories.forEach(cat => {
                                    if (!categoriesByParent[cat.parent_id]) categoriesByParent[cat.parent_id] = [];
                                    categoriesByParent[cat.parent_id].push(cat);
                                });
                                const parentSelect = document.getElementById('category_parent');
                                const childSelect = document.getElementById('category_child');
                                const tagsDiv = document.getElementById('category-tags');
                                parentSelect.addEventListener('change', function() {
                                    const parentId = this.value;
                                    childSelect.innerHTML = '<option value="">-- Pilih Subkategori --</option>';
                                    tagsDiv.innerHTML = '';
                                    if (categoriesByParent[parentId]) {
                                        childSelect.style.display = '';
                                        categoriesByParent[parentId].forEach(cat => {
                                            childSelect.innerHTML += `<option value="${cat.id}">${cat.category}</option>`;
                                        });
                                    } else {
                                        childSelect.style.display = 'none';
                                    }
                                    // Tag visual
                                    if (parentId) {
                                        const parentCat = categories.find(c => c.id == parentId);
                                        tagsDiv.innerHTML = `<span class='badge bg-success me-1'>#${parentCat.category}</span>`;
                                    }
                                });
                                childSelect.addEventListener('change', function() {
                                    const parentId = parentSelect.value;
                                    const childId = this.value;
                                    tagsDiv.innerHTML = '';
                                    if (parentId) {
                                        const parentCat = categories.find(c => c.id == parentId);
                                        tagsDiv.innerHTML += `<span class='badge bg-success me-1'>#${parentCat.category}</span>`;
                                    }
                                    if (childId) {
                                        const childCat = categories.find(c => c.id == childId);
                                        tagsDiv.innerHTML += `<span class='badge bg-info text-dark'>#${childCat.category}</span>`;
                                    }
                                });
                            </script>
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label">Status Publikasi</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="draft">Draft</option>
                                <option value="published">Published</option>
                            </select>
                        </div>

                        <input type="hidden" name="author_id" value="<?= $_SESSION['author_id'] ?>">
                        <input type="hidden" name="author_type" value="<?= $_SESSION['author_type'] ?>">

                        <button type="submit" class="btn btn-green">
                            <i class="fas fa-save"></i>Simpan Blog
                        </button>
                    </div>


                </div>
            </div>
        </form>
    </div>

    <!-- Add SweetAlert2 for better alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('documents').addEventListener('change', function(event) {
            const files = event.target.files;
            const preview = document.getElementById('documentsPreview');
            preview.innerHTML = '';
            if (files.length > 0) {
                preview.style.display = 'block';
                for (let i = 0; i < files.length; i++) {
                    const li = document.createElement('li');
                    li.textContent = files[i].name + ' (' + (files[i].size / 1024 / 1024).toFixed(2) + ' MB)';
                    preview.appendChild(li);
                }
            } else {
                preview.style.display = 'none';
            }
        });
    </script>
</body>

</html>