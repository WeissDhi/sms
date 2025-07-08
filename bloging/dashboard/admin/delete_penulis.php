<?php
session_start();
include '../../config.php';

if (!isset($_SESSION['author_id']) || $_SESSION['author_type'] !== 'admin') {
    header("Location: ../../login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM penulis WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: penulis_management.php");
    exit;
}

header("Location: penulis_management.php");
exit;
?>
