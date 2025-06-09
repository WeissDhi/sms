<?php
session_start();
include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['author_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Invalid action'];

switch ($action) {
    case 'add':
        if (!isset($_POST['blog_id']) || !isset($_POST['comment'])) {
            $response = ['status' => 'error', 'message' => 'Missing required fields'];
            break;
        }

        $blog_id = intval($_POST['blog_id']);
        $comment = trim($_POST['comment']);
        $parent_id = isset($_POST['parent_id']) ? intval($_POST['parent_id']) : null;
        $user_id = $_SESSION['author_id'];
        $author_type = $_SESSION['author_type'];

        if (empty($comment)) {
            $response = ['status' => 'error', 'message' => 'Comment cannot be empty'];
            break;
        }

        $stmt = $conn->prepare("INSERT INTO comment (comment, user_id, blog_id, parent_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("siii", $comment, $user_id, $blog_id, $parent_id);
        
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
        if (!isset($_POST['comment_id'])) {
            $response = ['status' => 'error', 'message' => 'Missing comment ID'];
            break;
        }

        $comment_id = intval($_POST['comment_id']);
        $is_admin = $_SESSION['author_type'] === 'admin';

        // Get comment details
        $stmt = $conn->prepare("SELECT user_id FROM comment WHERE comment_id = ?");
        $stmt->bind_param("i", $comment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $comment = $result->fetch_assoc();

        if (!$comment) {
            $response = ['status' => 'error', 'message' => 'Comment not found'];
            break;
        }

        // Check if user is authorized to delete
        if (!$is_admin && $comment['user_id'] != $_SESSION['author_id']) {
            $response = ['status' => 'error', 'message' => 'Unauthorized to delete this comment'];
            break;
        }

        // Soft delete for regular users, hard delete for admin
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
            SELECT c.*, u.fname as username, 
                   CASE WHEN a.id IS NOT NULL THEN a.first_name ELSE u.fname END as author_name
            FROM comment c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN admin a ON c.user_id = a.id
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