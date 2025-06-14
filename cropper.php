<?php
require_once 'config/database.php';

// Fetch all images
$stmt = $pdo->query("SELECT * FROM images ORDER BY created_at DESC");
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Cropper Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <style>
        .image-preview {
            width: 100%;
            height: 0;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            position: relative;
            overflow: hidden;
        }
        .image-preview img {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cropper-container {
            max-height: 400px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Image Cropper Dashboard</a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                    Upload New Image
                </button>
            </div>
        </div>

        <div class="row">
            <?php foreach ($images as $image): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <a href="view.php?id=<?php echo $image['id']; ?>" class="text-decoration-none">
                        <div class="image-preview">
                            <img src="<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>">
                        </div>
                    </a>
                    <div class="card-body">
                        <h5 class="card-title">
                            <a href="view.php?id=<?php echo $image['id']; ?>" class="text-decoration-none text-dark">
                                <?php echo htmlspecialchars($image['title']); ?>
                            </a>
                        </h5>
                        <p class="card-text"><?php echo htmlspecialchars($image['description']); ?></p>
                        <div class="btn-group">
                            <a href="view.php?id=<?php echo $image['id']; ?>" class="btn btn-info btn-sm">View</a>
                            <a href="edit.php?id=<?php echo $image['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="delete.php?id=<?php echo $image['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this image?')">Delete</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload New Image</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="upload.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Image</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        </div>
                        <div class="mb-3">
                            <div id="cropper-container" style="display: none;">
                                <img id="cropper-image" src="" alt="Preview">
                            </div>
                        </div>
                        <input type="hidden" name="cropped_image" id="cropped_image">
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script>
        let cropper;
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
                        aspectRatio: 16/9,
                        viewMode: 1,
                        ready: function() {
                            // Set initial crop data
                            updateCropData();
                        },
                        crop: function() {
                            // Update crop data on every crop event
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
</body>
</html> 