<?php
include 'config.php';

// Get all blogs
$result = $conn->query("SELECT id, content FROM blogs WHERE content LIKE '%uploads/%'");

while ($row = $result->fetch_assoc()) {
    // Extract image paths from content
    preg_match_all('/src="uploads\/([^"]+)"/', $row['content'], $matches);
    
    if (!empty($matches[1])) {
        $content = $row['content'];
        foreach ($matches[1] as $filename) {
            // Replace full path with just the filename
            $content = str_replace('uploads/uploads/' . $filename, 'uploads/' . $filename, $content);
        }
        
        // Update the content
        $stmt = $conn->prepare("UPDATE blogs SET content = ? WHERE id = ?");
        $stmt->bind_param("si", $content, $row['id']);
        $stmt->execute();
        $stmt->close();
    }
}

echo "Image paths have been fixed in the database.";
$conn->close();
?> 