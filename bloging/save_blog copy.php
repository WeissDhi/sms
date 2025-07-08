<?php
include 'config.php';
session_start();

// Set proper headers
header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Debug: Log received data
        error_log("POST data received: " . print_r($_POST, true));
        error_log("FILES data received: " . print_r($_FILES, true));
        
        // Check if required fields exist
        if (!isset($_POST["title"])) {
            throw new Exception("Field 'title' is missing from POST data");
        }
        if (!isset($_POST["content"])) {
            throw new Exception("Field 'content' is missing from POST data");
        }
        if (!isset($_POST["category"])) {
            throw new Exception("Field 'category' is missing from POST data");
        }
        if (!isset($_POST["status"])) {
            throw new Exception("Field 'status' is missing from POST data");
        }
        
        $title = trim($_POST["title"]);
        $content = trim($_POST["content"]);
        $category_id = intval($_POST["category"]);
        $status = trim($_POST["status"]);

        // Validate required fields
        if (empty($title)) {
            throw new Exception("Judul blog tidak boleh kosong");
        }
        if (empty($content)) {
            throw new Exception("Konten blog tidak boleh kosong");
        }
        if (empty($category_id)) {
            throw new Exception("Kategori harus dipilih");
        }
        if (!in_array($status, ['draft', 'published'])) {
            throw new Exception("Status tidak valid");
        }

        // Ambil dari session
        $author_id = $_SESSION['author_id'] ?? null;
        $author_type = $_SESSION['author_type'] ?? null;

        if (!$author_id || !$author_type) {
            die("Author tidak terdeteksi. Silakan login ulang.");
        }

        $image = "";

        // Handle image upload
        if (!empty($_FILES["image"]["name"])) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            $image = time() . '_' . basename($_FILES["image"]["name"]);
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_dir . $image)) {
                throw new Exception("Gagal mengupload gambar");
            }
        }

        function slugify($text)
        {
            $text = strtolower(trim($text));
            $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
            $text = preg_replace('/-+/', '-', $text);
            $text = trim($text, '-');
            return $text;
        }
        
        $title_clean = strip_tags($title);
        $slug = slugify($title_clean);
        
        // Pastikan slug unik
        $base_slug = $slug;
        $counter = 1;
        while ($conn->query("SELECT id FROM blogs WHERE slug='$slug'")->num_rows > 0) {
            $slug = $base_slug . '-' . $counter;
            $counter++;
        }

        // Modify the SQL query to include document field
        $stmt = $conn->prepare("INSERT INTO blogs (title, slug, content, image, category_id, author_id, author_type, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("ssssiiss", $title, $slug, $content, $image, $category_id, $author_id, $author_type, $status);

        if ($stmt->execute()) {
            $blog_id = $conn->insert_id;
            
            // Proses upload dokumen jika ada
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
                        $stmtDoc->bind_param("iss", $blog_id, $safeName, $fileType);
                        $stmtDoc->execute();
                        $stmtDoc->close();
                    }
                }
            }
            
            $stmt->close();
            $conn->close();
            echo "success";
        } else {
            throw new Exception("Database error: " . $stmt->error);
        }
    } catch (Exception $e) {
        error_log("Blog save error: " . $e->getMessage());
        echo "error: " . $e->getMessage();
    }
} else {
    echo "error: Invalid request method";
}
