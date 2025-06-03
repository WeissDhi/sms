<?php
session_start();

if (!isset($_SESSION['author_type'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['author_type'] === 'admin') {
    header("Location: ./admin/index.php");
} else {
    header("Location: ./users/index.php");
}
exit;
