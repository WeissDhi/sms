<?php
if ($_FILES['file']['name']) {
    $target_dir = 'uploads/';
    $file_name = time() . '_' . basename($_FILES['file']['name']);
    $file_path = $target_dir . $file_name;

    if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
        echo json_encode(['location' => $file_path]); // penting: ini path harus bisa diakses browser
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Upload failed.']);
    }
}
?>
