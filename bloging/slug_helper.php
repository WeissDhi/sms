<?php
/**
 * Helper function untuk generate slug dari title
 */
function generateSlug($title, $conn, $exclude_id = null) {
    // Hapus tag HTML
    $title = strip_tags($title);
    
    // Convert ke lowercase
    $slug = strtolower($title);
    
    // Replace karakter khusus dengan dash
    $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
    $slug = preg_replace('/[\s-]+/', '-', $slug);
    $slug = trim($slug, '-');
    
    // Jika slug kosong, gunakan 'post'
    if (empty($slug)) {
        $slug = 'post';
    }
    
    // Cek apakah slug sudah ada
    $original_slug = $slug;
    $counter = 1;
    
    while (true) {
        $sql = "SELECT id FROM blogs WHERE slug = ?";
        if ($exclude_id) {
            $sql .= " AND id != ?";
        }
        
        $stmt = $conn->prepare($sql);
        
        if ($exclude_id) {
            $stmt->bind_param("si", $slug, $exclude_id);
        } else {
            $stmt->bind_param("s", $slug);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            break; // Slug unik, keluar dari loop
        }
        
        // Jika slug sudah ada, tambahkan angka
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Function untuk membuat URL slug yang SEO-friendly
 */
function createSlugUrl($slug) {
    return "view_detail.php?slug=" . urlencode($slug);
}
?> 