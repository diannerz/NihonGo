<?php
require __DIR__ . '/php/db.php';

echo "=== Database Debug ===\n\n";

// Check if manga table exists
try {
    $result = $pdo->query('DESC manga');
    echo "✓ manga table EXISTS\n";
} catch (Exception $e) {
    echo "✗ manga table MISSING\n";
    echo "  Error: " . $e->getMessage() . "\n";
}

// Check if manga_pages table exists
try {
    $result = $pdo->query('DESC manga_pages');
    $columns = $result->fetchAll();
    echo "✓ manga_pages table EXISTS\n";
    echo "  Columns:\n";
    foreach ($columns as $col) {
        echo "    - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
} catch (Exception $e) {
    echo "✗ manga_pages table MISSING\n";
    echo "  Error: " . $e->getMessage() . "\n";
    echo "\n  Need to create it? Run this SQL:\n";
    echo "  ```sql\n";
    echo "  CREATE TABLE manga_pages (\n";
    echo "    id INT PRIMARY KEY AUTO_INCREMENT,\n";
    echo "    manga_id INT NOT NULL,\n";
    echo "    page_number INT,\n";
    echo "    page_image VARCHAR(255),\n";
    echo "    en_text TEXT,\n";
    echo "    jp_text TEXT,\n";
    echo "    romaji_text TEXT,\n";
    echo "    FOREIGN KEY (manga_id) REFERENCES manga(id) ON DELETE CASCADE\n";
    echo "  );\n";
    echo "  ```\n";
}

// Check manga records
echo "\n=== Manga Records ===\n";
try {
    $stmt = $pdo->query('SELECT * FROM manga');
    $manga_list = $stmt->fetchAll();
    echo "Found " . count($manga_list) . " manga(s)\n";
    foreach ($manga_list as $manga) {
        echo "  - ID: " . $manga['id'] . ", Title: " . $manga['title'] . "\n";
    }
} catch (Exception $e) {
    echo "Error querying manga: " . $e->getMessage() . "\n";
}

echo "\nDone.\n";
?>
