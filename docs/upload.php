<?php
include "db.php";

if (isset($_POST['upload'])) {
    $file = $_FILES['document'];

    $fileName = basename($file['name']);
    $fileType = $file['type'];
    $fileTmp = $file['tmp_name'];

    $uploadDir = "uploads/";
    $uploadPath = $uploadDir . $fileName;

    $allowedTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    ];

    if (in_array($fileType, $allowedTypes)) {
        if (move_uploaded_file($fileTmp, $uploadPath)) {
            $stmt = $conn->prepare("INSERT INTO documents (file_name, file_type) VALUES (?, ?)");
            $stmt->bind_param("ss", $fileName, $fileType);
            $stmt->execute();
            echo "File berhasil diupload. <a href='index.php'>Kembali</a>";
        } else {
            echo "Gagal menyimpan file.";
        }
    } else {
        echo "Tipe file tidak diizinkan.";
    }
}
?>
