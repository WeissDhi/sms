<!DOCTYPE html>
<html>
<head>
    <title>Upload Dokumen</title>
</head>
<body>
    <h2>Form Upload Dokumen</h2>
    <form action="upload.php" method="post" enctype="multipart/form-data">
        Pilih File: <input type="file" name="document" required>
        <button type="submit" name="upload">Upload</button>
    </form>

    <hr>

    <h3>Daftar Dokumen:</h3>
    <ul>
        <?php
        include "db.php";
        $result = $conn->query("SELECT * FROM documents ORDER BY uploaded_at DESC");
        while ($row = $result->fetch_assoc()) {
            echo "<li><a href='uploads/{$row['file_name']}' target='_blank'>{$row['file_name']}</a> - {$row['file_type']} - {$row['uploaded_at']}</li>";
        }
        ?>
    </ul>
</body>
</html>
