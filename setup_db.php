<?php
/**
 * Database Setup - Create missing tables and ensure schema is correct
 * 
 * This script will:
 * 1. Create manga table (if not exists)
 * 2. Create manga_pages table (if not exists)
 * 3. Verify kana_flashcards has all required columns
 * 
 * Run this ONCE to set up the database properly
 */

require __DIR__ . '/php/db.php';

echo "=== NihonGo Database Setup ===\n\n";

// ===== 1. Create manga table =====
echo "1. Checking manga table...\n";
try {
    $pdo->query('DESC manga');
    echo "   ✓ manga table already exists\n";
} catch (Exception $e) {
    echo "   Creating manga table...\n";
    try {
        $pdo->exec('
            CREATE TABLE manga (
                id INT PRIMARY KEY AUTO_INCREMENT,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                cover_image VARCHAR(255),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ');
        echo "   ✓ manga table created successfully\n";
    } catch (Exception $e2) {
        echo "   ✗ Error creating manga table: " . $e2->getMessage() . "\n";
    }
}

// ===== 2. Create manga_pages table =====
echo "\n2. Checking manga_pages table...\n";
try {
    $result = $pdo->query('DESC manga_pages');
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    $column_names = array_column($columns, 'Field');
    
    echo "   ✓ manga_pages table exists\n";
    
    // Check for required text columns
    $required_cols = ['en_text', 'jp_text', 'romaji_text'];
    foreach ($required_cols as $col) {
        if (!in_array($col, $column_names)) {
            echo "   + Adding column '$col'...\n";
            try {
                $pdo->exec("ALTER TABLE manga_pages ADD COLUMN $col TEXT");
                echo "     ✓ Column '$col' added\n";
            } catch (Exception $e2) {
                echo "     ✗ Error: " . $e2->getMessage() . "\n";
            }
        } else {
            echo "   ✓ Column '$col' exists\n";
        }
    }
} catch (Exception $e) {
    echo "   Creating manga_pages table...\n";
    try {
        $pdo->exec('
            CREATE TABLE manga_pages (
                id INT PRIMARY KEY AUTO_INCREMENT,
                manga_id INT NOT NULL,
                page_number INT,
                page_image VARCHAR(255),
                en_text TEXT,
                jp_text TEXT,
                romaji_text TEXT,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (manga_id) REFERENCES manga(id) ON DELETE CASCADE
            )
        ');
        echo "   ✓ manga_pages table created successfully\n";
    } catch (Exception $e2) {
        echo "   ✗ Error creating manga_pages table: " . $e2->getMessage() . "\n";
    }
}

// ===== 3. Check kana_flashcards columns =====
echo "\n3. Checking kana_flashcards table...\n";
try {
    $result = $pdo->query('DESC kana_flashcards');
    $columns = $result->fetchAll(PDO::FETCH_ASSOC);
    $column_names = array_column($columns, 'Field');
    
    $required_columns = [
        'mnemonic' => 'TEXT',
        'description' => 'TEXT',
        'vocab_jp' => 'VARCHAR(255)',
        'vocab_romaji' => 'VARCHAR(255)',
        'vocab_eng' => 'VARCHAR(255)'
    ];
    
    echo "   Found " . count($columns) . " columns\n";
    
    foreach ($required_columns as $col_name => $col_type) {
        if (in_array($col_name, $column_names)) {
            echo "   ✓ Column '$col_name' exists\n";
        } else {
            echo "   + Adding column '$col_name'...\n";
            try {
                $pdo->exec("ALTER TABLE kana_flashcards ADD COLUMN $col_name $col_type");
                echo "     ✓ Column '$col_name' added\n";
            } catch (Exception $e2) {
                echo "     ✗ Error: " . $e2->getMessage() . "\n";
            }
        }
    }
} catch (Exception $e) {
    echo "   ✗ Error checking kana_flashcards: " . $e->getMessage() . "\n";
}

// ===== Summary =====
echo "\n=== Setup Complete ===\n\n";
echo "Your database is now ready for:\n";
echo "  ✓ Admin dashboard (donations)\n";
echo "  ✓ Kana flashcard management\n";
echo "  ✓ Manga story creation with multiple pages\n\n";
echo "You can now safely delete this file (setup_db.php)\n";
echo "Visit: http://localhost/NihonGo/admin/dashboard.php\n";
?>
