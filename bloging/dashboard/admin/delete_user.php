<?php
session_start();
include '../../config.php';

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("DELETE FROM pengguna WHERE id = ?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        $_SESSION['success'] = 'Pengguna berhasil dihapus!';
    } else {
        $_SESSION['error'] = 'Gagal menghapus pengguna!';
    }
    $stmt->close();
}
header('Location: user_management.php');
exit; 