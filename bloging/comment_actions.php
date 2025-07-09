<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Invalid action'];

switch ($action) {
    case 'add':
        if (!isset($_SESSION['author_id']) || !isset($_SESSION['author_type'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        if (!isset($_POST['blog_id']) || !isset($_POST['comment'])) {
            $response = ['status' => 'error', 'message' => 'Missing required fields'];
            break;
        }

        $blog_id = intval($_POST['blog_id']);
        $comment = trim($_POST['comment']);
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $author_id = $_SESSION['author_id'];
        $author_type = $_SESSION['author_type'];

        if (empty($comment)) {
            $response = ['status' => 'error', 'message' => 'Comment cannot be empty'];
            break;
        }

        // Tentukan kolom id sesuai author_type
        $penulis_id = null;
        $pengguna_id = null;
        if ($author_type === 'penulis' || $author_type === 'admin') {
            $penulis_id = $author_id;
        } else if ($author_type === 'pengguna') {
            $pengguna_id = $author_id;
        } else {
            $response = ['status' => 'error', 'message' => 'Unknown author type'];
            break;
        }

        $stmt = $conn->prepare("INSERT INTO comment (comment, penulis_id, pengguna_id, blog_id, parent_id, author_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
            "siiiss",
            $comment,
            $penulis_id,
            $pengguna_id,
            $blog_id,
            $parent_id,
            $author_type
        );
        
        if ($stmt->execute()) {
            $comment_id = $conn->insert_id;
            $response = [
                'status' => 'success',
                'message' => 'Comment added successfully',
                'comment_id' => $comment_id
            ];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to add comment'];
        }
        break;

    case 'delete':
        if (!isset($_SESSION['author_id']) || !isset($_SESSION['author_type'])) {
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            exit;
        }
        if (!isset($_POST['comment_id'])) {
            $response = ['status' => 'error', 'message' => 'Missing comment ID'];
            break;
        }

        $comment_id = intval($_POST['comment_id']);
        $is_admin = $_SESSION['author_type'] === 'admin';
        $author_id = $_SESSION['author_id'];
        $author_type = $_SESSION['author_type'];

        // Get comment details
        $stmt = $conn->prepare("SELECT penulis_id, pengguna_id, author_type FROM comment WHERE comment_id = ?");
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comment = $result->fetch_assoc();

        if (!$comment) {
            $response = ['status' => 'error', 'message' => 'Comment not found'];
            break;
        }

        // Check if user is authorized to delete
        $canDelete = false;
        if ($is_admin) {
            $canDelete = true;
        } else if ($author_type === 'penulis' && $comment['author_type'] === 'penulis' && $author_id == $comment['penulis_id']) {
            $canDelete = true;
        } else if ($author_type === 'pengguna' && $comment['author_type'] === 'pengguna' && $author_id == $comment['pengguna_id']) {
            $canDelete = true;
        }
        if (!$canDelete) {
            $response = ['status' => 'error', 'message' => 'Unauthorized to delete this comment'];
            break;
        }

        // Soft delete for non-admin, hard delete for admin
        if ($is_admin) {
            $stmt = $conn->prepare("DELETE FROM comment WHERE comment_id = ?");
        } else {
            $stmt = $conn->prepare("UPDATE comment SET status = 'deleted' WHERE comment_id = ?");
        }
        $stmt->bind_param("i", $comment_id);
        
        if ($stmt->execute()) {
            $response = ['status' => 'success', 'message' => 'Comment deleted successfully'];
        } else {
            $response = ['status' => 'error', 'message' => 'Failed to delete comment'];
        }
        break;

    case 'get_replies':
        if (!isset($_POST['comment_id'])) {
            $response = ['status' => 'error', 'message' => 'Missing comment ID'];
            break;
        }

        $comment_id = intval($_POST['comment_id']);
        $stmt = $conn->prepare("
            SELECT c.*, 
                CASE 
                    WHEN c.author_type = 'admin' THEN a.first_name
                    WHEN c.author_type = 'penulis' THEN p.fname
                    WHEN c.author_type = 'pengguna' THEN g.nama
                    ELSE 'Unknown'
                END as author_name
            FROM comment c
            LEFT JOIN admin a ON c.author_type = 'admin' AND c.penulis_id = a.id
            LEFT JOIN penulis p ON c.author_type = 'penulis' AND c.penulis_id = p.id
            LEFT JOIN pengguna g ON c.author_type = 'pengguna' AND c.pengguna_id = g.id
            WHERE c.parent_id = ? AND c.status = 'active'
            ORDER BY c.created_at ASC
        ");
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $replies = [];
        while ($reply = $result->fetch_assoc()) {
            $replies[] = $reply;
        }
        $response = ['status' => 'success', 'replies' => $replies];
        break;
}

echo json_encode($response); 