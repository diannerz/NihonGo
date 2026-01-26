<?php
// admin/admin-functions.php
require __DIR__ . '/../php/db.php';
require __DIR__ . '/../php/check_auth.php';

// Check if user is admin
function is_admin() {
    global $user;
    return $user && $user['role'] === 'admin';
}

// Redirect non-admins to dashboard
function require_admin() {
    if (!is_admin()) {
        header('Location: ../dashboard.php');
        exit;
    }
}

// Get all donations with user info
function get_donation_reports() {
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT 
            d.id,
            u.id as user_id,
            u.username,
            u.display_name,
            d.amount,
            d.feature_name,
            d.donation_date
        FROM donations d
        JOIN users u ON d.user_id = u.id
        ORDER BY d.donation_date DESC
    ');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get donation summary by feature
function get_donation_summary() {
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT 
            feature_name,
            COUNT(*) as count,
            SUM(amount) as total
        FROM donations
        GROUP BY feature_name
        ORDER BY total DESC
    ');
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get all kana flashcards
function get_all_kana() {
    global $pdo;
    $stmt = $pdo->query('
        SELECT * FROM kana_flashcards
        ORDER BY kana_type, id
    ');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get kana by type
function get_kana_by_type($type) {
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT * FROM kana_flashcards
        WHERE kana_type = :type
        ORDER BY id
    ');
    $stmt->execute([':type' => $type]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get kana by ID
function get_kana_by_id($kana_id) {
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT * FROM kana_flashcards
        WHERE id = :id
    ');
    $stmt->execute([':id' => $kana_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Update kana description
function update_kana_description($kana_id, $description) {
    global $pdo;
    $stmt = $pdo->prepare('
        UPDATE kana_flashcards
        SET description = :desc, updated_at = NOW()
        WHERE id = :id
    ');
    return $stmt->execute([':desc' => $description, ':id' => $kana_id]);
}

// Get all manga
function get_all_manga() {
    global $pdo;
    $stmt = $pdo->query('
        SELECT * FROM manga
        ORDER BY created_at DESC
    ');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Get manga by ID with pages
function get_manga_by_id($manga_id) {
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT * FROM manga WHERE id = :id
    ');
    $stmt->execute([':id' => $manga_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get manga pages
function get_manga_pages($manga_id) {
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT * FROM manga_pages
        WHERE manga_id = :manga_id
        ORDER BY page_number
    ');
    $stmt->execute([':manga_id' => $manga_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Create new manga
function create_manga($title, $description, $cover_image) {
    global $pdo;
    $stmt = $pdo->prepare('
        INSERT INTO manga (title, description, cover_image)
        VALUES (:title, :desc, :cover)
    ');
    $stmt->execute([
        ':title' => $title,
        ':desc' => $description,
        ':cover' => $cover_image
    ]);
    return $pdo->lastInsertId();
}

// Update manga
function update_manga($manga_id, $title, $description, $cover_image = null) {
    global $pdo;
    if ($cover_image) {
        $stmt = $pdo->prepare('
            UPDATE manga
            SET title = :title, description = :desc, cover_image = :cover, updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            ':title' => $title,
            ':desc' => $description,
            ':cover' => $cover_image,
            ':id' => $manga_id
        ]);
    } else {
        $stmt = $pdo->prepare('
            UPDATE manga
            SET title = :title, description = :desc, updated_at = NOW()
            WHERE id = :id
        ');
        $stmt->execute([
            ':title' => $title,
            ':desc' => $description,
            ':id' => $manga_id
        ]);
    }
    return true;
}

// Delete manga (cascade deletes pages)
function delete_manga($manga_id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM manga WHERE id = :id');
    return $stmt->execute([':id' => $manga_id]);
}

// Add manga page
function add_manga_page($manga_id, $page_number, $page_image) {
    global $pdo;
    $stmt = $pdo->prepare('
        INSERT INTO manga_pages (manga_id, page_number, page_image)
        VALUES (:manga_id, :page_num, :image)
    ');
    return $stmt->execute([
        ':manga_id' => $manga_id,
        ':page_num' => $page_number,
        ':image' => $page_image
    ]);
}

// Delete manga page
function delete_manga_page($page_id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM manga_pages WHERE id = :id');
    return $stmt->execute([':id' => $page_id]);
}

// Handle file upload
function handle_file_upload($file, $upload_dir) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    $file_name = basename($file['name']);
    $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
    
    // Sanitize filename
    $safe_name = time() . '_' . uniqid() . '.' . $file_ext;
    $upload_path = $upload_dir . $safe_name;
    
    if (move_uploaded_file($file['tmp_name'], $upload_path)) {
        return $safe_name;
    }
    
    return null;
}
?>
