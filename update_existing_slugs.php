<?php
include 'bloging/config.php';

function slugify($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = preg_replace('/-+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

// Ambil semua blog yang belum punya slug
$result = $conn->query("SELECT id, title FROM blogs WHERE slug IS NULL OR slug = ''");

if ($result->num_rows > 0) {
    echo "Mengupdate slug untuk " . $result->num_rows . " artikel...<br>";
    
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $title = $row['title'];
        
        $title_clean = strip_tags($title);
        $slug = slugify($title_clean);
        
        // Pastikan slug unik
        $base_slug = $slug;
        $counter = 1;
        while ($conn->query("SELECT id FROM blogs WHERE slug='$slug' AND id != $id")->num_rows > 0) {
            $slug = $base_slug . '-' . $counter;
            $counter++;
        }
        
        // Update slug
        $stmt = $conn->prepare("UPDATE blogs SET slug = ? WHERE id = ?");
        $stmt->bind_param("si", $slug, $id);
        
        if ($stmt->execute()) {
            echo "✓ Artikel ID $id: '$title' → slug: '$slug'<br>";
        } else {
            echo "✗ Gagal update artikel ID $id<br>";
        }
        
        $stmt->close();
    }
    
    echo "<br>Selesai mengupdate slug!";
} else {
    echo "Semua artikel sudah memiliki slug.";
}

$conn->close();
?> 