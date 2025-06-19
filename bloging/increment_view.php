<?php
include 'config.php';

// Set content type to JSON for better response handling
header('Content-Type: application/json');

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    if ($id > 0) {
        try {
            // Update view count
            $stmt = $conn->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
            if ($stmt) {
                $stmt->bind_param("i", $id);
                $result = $stmt->execute();
                
                if ($result) {
                    // Get the updated view count
                    $select_stmt = $conn->prepare("SELECT views FROM blogs WHERE id = ?");
                    $select_stmt->bind_param("i", $id);
                    $select_stmt->execute();
                    $result = $select_stmt->get_result();
                    $row = $result->fetch_assoc();
                    
                    echo json_encode([
                        'status' => 'success',
                        'message' => 'View count updated successfully',
                        'views' => $row['views'],
                        'blog_id' => $id
                    ]);
                    
                    $select_stmt->close();
                } else {
                    echo json_encode([
                        'status' => 'error',
                        'message' => 'Failed to update view count',
                        'error' => $stmt->error
                    ]);
                }
                
                $stmt->close();
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Failed to prepare statement',
                    'error' => $conn->error
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Exception occurred',
                'error' => $e->getMessage()
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid blog ID'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No blog ID provided'
    ]);
}

$conn->close();
?> 