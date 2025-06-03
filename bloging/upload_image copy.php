<?php
if ($_FILES['file']['name']) {
    $file_name = './uploads/' . time() . '_' . $_FILES['file']['name'];
    move_uploaded_file($_FILES['file']['tmp_name'], $file_name);
    echo json_encode(['location' => $file_name]);
}
?>
