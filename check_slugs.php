<?php
include 'bloging/config.php';

echo "<h2>Pengecekan dan Perbaikan Slug</h2>";

// Cek artikel yang tidak punya slug
$result = $conn->query("SELECT id, title, slug FROM blogs WHERE slug IS NULL OR slug = ''");

if ($result->num_rows > 0) {
    echo "<p>Ditemukan " . $result->num_rows . " artikel tanpa slug:</p>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<p>ID: " . $row['id'] . " - Title: " . $row['title'] . " - Slug: " . ($row['slug'] ?: 'KOSONG') . "</p>";
    }
    
    echo "<p><a href='update_existing_slugs.php'>Klik di sini untuk memperbaiki slug</a></p>";
} else {
    echo "<p>✓ Semua artikel sudah memiliki slug!</p>";
}

// Cek artikel dengan slug duplikat
$duplicate_result = $conn->query("
    SELECT slug, COUNT(*) as count 
    FROM blogs 
    WHERE slug IS NOT NULL AND slug != '' 
    GROUP BY slug 
    HAVING COUNT(*) > 1
");

if ($duplicate_result->num_rows > 0) {
    echo "<p>⚠️ Ditemukan slug duplikat:</p>";
    while ($row = $duplicate_result->fetch_assoc()) {
        echo "<p>Slug: " . $row['slug'] . " - Count: " . $row['count'] . "</p>";
    }
} else {
    echo "<p>✓ Tidak ada slug duplikat!</p>";
}

// Test koneksi database
if ($conn->ping()) {
    echo "<p>✓ Koneksi database OK!</p>";
} else {
    echo "<p>✗ Error koneksi database!</p>";
}

$conn->close();
?> 