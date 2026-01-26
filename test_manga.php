<?php
require "php/db.php";

// Insert test manga
try {
    $stmt = $pdo->prepare('INSERT INTO manga (title, description, cover_image, created_at) VALUES (:title, :desc, :cover, NOW())');
    $stmt->execute([
        ':title' => 'An egg\'s tale',
        ':desc' => 'A heartwarming story about a little egg\'s journey',
        ':cover' => 'default-cover.png'
    ]);
    $manga_id = $pdo->lastInsertId();
    echo "Inserted manga with ID: $manga_id\n";
    
    // Insert test pages
    $pages = [
        ['page' => 1, 'en' => 'One day, an egg had fallen.', 'jp' => 'ある日、卵が落ちていました。', 'romaji' => 'Aru hi, tamago ga ochite imashita.'],
        ['page' => 2, 'en' => 'It was alone in the grass.', 'jp' => '草の中にひとり取り残されていました。', 'romaji' => 'Kusa no naka ni hitori torinokoreasete imashita.'],
        ['page' => 3, 'en' => 'The egg waited for help.', 'jp' => '卵は助けを待っていました。', 'romaji' => 'Tamago wa tasuke wo matte imashita.'],
    ];
    
    foreach ($pages as $p) {
        $stmt = $pdo->prepare('INSERT INTO manga_pages (manga_id, page_number, page_image, en_text, jp_text, romaji_text) VALUES (:manga_id, :page, :img, :en, :jp, :romaji)');
        $stmt->execute([
            ':manga_id' => $manga_id,
            ':page' => $p['page'],
            ':img' => 'eggtale' . $p['page'] . '.png',
            ':en' => $p['en'],
            ':jp' => $p['jp'],
            ':romaji' => $p['romaji']
        ]);
    }
    
    echo "Inserted 3 test pages\n";
    echo "You can now view: http://localhost/NihonGo/media.php\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
