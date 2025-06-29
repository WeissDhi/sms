<?php
include 'config.php';

echo "Testing database connection...\n";

if ($conn->ping()) {
    echo "Database connection successful\n";
} else {
    echo "Database connection failed: " . $conn->error . "\n";
}

// Check if blogs table exists
$result = $conn->query("SHOW TABLES LIKE 'blogs'");
if ($result->num_rows > 0) {
    echo "Blogs table exists\n";
    
    // Check table structure
    $result = $conn->query("DESCRIBE blogs");
    echo "Blogs table structure:\n";
    while ($row = $result->fetch_assoc()) {
        echo "- " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
} else {
    echo "Blogs table does not exist\n";
}

// Check if category table exists
$result = $conn->query("SHOW TABLES LIKE 'category'");
if ($result->num_rows > 0) {
    echo "Category table exists\n";
} else {
    echo "Category table does not exist\n";
}

$conn->close();
?> 