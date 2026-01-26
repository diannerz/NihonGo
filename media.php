<?php
require "php/check_auth.php";
require "php/db.php";

// Redirect if not logged in
if (!$user) {
    header("Location: login.html");
    exit;
}

// Get manga ID from URL
$manga_id = isset($_GET['manga_id']) ? (int)$_GET['manga_id'] : null;

// Get all manga for navigation
try {
    $stmt = $pdo->query('SELECT id, title, description, cover_image FROM manga ORDER BY created_at DESC LIMIT 10');
    $all_manga = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $all_manga = [];
}

$current_manga = null;
$pages = [];

// Get current manga and its pages
if ($manga_id) {
    try {
        $stmt = $pdo->prepare('SELECT * FROM manga WHERE id = :id');
        $stmt->execute([':id' => $manga_id]);
        $current_manga = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($current_manga) {
            $stmt = $pdo->prepare('SELECT * FROM manga_pages WHERE manga_id = :id ORDER BY page_number ASC');
            $stmt->execute([':id' => $manga_id]);
            $pages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (Exception $e) {
        // Handle error gracefully
    }
}

// Track progress
if ($current_manga) {
    $uid = (int) $_SESSION['user_id'];
    try {
        $pdo->prepare('
            INSERT IGNORE INTO daily_progress (user_id, day_date, manga_views)
            VALUES (:uid, CURDATE(), 1)
            ON DUPLICATE KEY UPDATE manga_views = manga_views + 1
        ')->execute([':uid' => $uid]);
    } catch (Exception $e) {
        // Ignore progress tracking errors
    }
}

// Get current page index from URL
$page_index = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$page_index = max(0, min($page_index, count($pages) - 1));
$current_page = !empty($pages) ? $pages[$page_index] : null;

// Get prev/next manga IDs
$current_index = array_search($manga_id, array_column($all_manga, 'id'));
$prev_manga = $current_index > 0 ? $all_manga[$current_index - 1] : null;
$next_manga = $current_index < count($all_manga) - 1 ? $all_manga[$current_index + 1] : null;
?>
<!DOCTYPE html>
<html lang="en" class="manga-page">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Manga — NihonGo</title>
  <link rel="stylesheet" href="media.css" />
</head>

<script>
// Track manga view
window.addEventListener("DOMContentLoaded", () => {
  // Already tracked via PHP, but you could add additional tracking here
});
</script>

<body>

  <!-- HEADER -->
  <header class="top-bar">
    <div class="top-left">
      <a href="dashboard.php" class="home-link">
        <img src="images/home.png" alt="home">
      </a>
      <div class="header-text-inline">
        <h1 class="title">Manga</h1>
        <p class="subtitle">Read a collection of immersive Japanese stories!</p>
      </div>
    </div>
    <div class="top-right">
      <a href="php/logout.php" title="Logout">
        <img src="images/exit.png" alt="logout">
      </a>
      <a href="settings.php" title="Settings">
        <img src="images/setting.png" alt="settings">
      </a>
      <a href="donation.php" title="Profile">
        <img src="images/profile.png" alt="profile">
      </a>
    </div>
  </header>

  <!-- STORY WRAPPER -->
  <main class="media-wrapper">
    <?php if ($current_manga && $current_page): ?>
    <div class="outer-panel">
      <!-- Story Navigation -->
      <div class="story-header">
        <?php if ($prev_manga): ?>
          <a href="?manga_id=<?= $prev_manga['id'] ?>" class="outer-arrow left" title="Previous story">&lt;</a>
        <?php else: ?>
          <div class="outer-arrow left" style="opacity: 0.3;">&lt;</div>
        <?php endif; ?>
        
        <h2 class="story-title"><?= htmlspecialchars($current_manga['title']) ?></h2>
        
        <div class="progress-dots" aria-hidden="true">
          <?php for ($i = 0; $i < count($pages); $i++): ?>
            <?= ($i === $page_index) ? '●' : '○'; ?>
          <?php endfor; ?>
        </div>
        
        <?php if ($next_manga): ?>
          <a href="?manga_id=<?= $next_manga['id'] ?>" class="outer-arrow right" title="Next story">&gt;</a>
        <?php else: ?>
          <div class="outer-arrow right" style="opacity: 0.3;">&gt;</div>
        <?php endif; ?>
      </div>

      <div class="story-panel">
        <!-- Left: Text -->
        <div class="story-left">
          <div class="story-text">
            <p class="en"><?= htmlspecialchars($current_page['en_text'] ?? '') ?></p>
            <p class="jp"><?= htmlspecialchars($current_page['jp_text'] ?? '') ?></p>
            <p class="romaji"><?= htmlspecialchars($current_page['romaji_text'] ?? '') ?></p>
          </div>
        </div>

        <!-- Right: Image Slider -->
        <div class="story-right">
          <div class="slider">
            <?php if ($page_index > 0): ?>
              <a href="?manga_id=<?= $manga_id ?>&page=<?= $page_index - 1 ?>" class="slide-btn prev" aria-label="Previous panel">&#10094;</a>
            <?php else: ?>
              <button class="slide-btn prev" disabled>&#10094;</button>
            <?php endif; ?>

            <div class="slides" aria-live="polite">
              <?php if ($current_page && $current_page['page_image']): ?>
                <img src="uploads/manga-pages/<?= htmlspecialchars($current_page['page_image']) ?>" 
                     alt="Page <?= ($page_index + 1) ?>"
                     class="active"
                     style="display: block; width: 100%; height: auto; border-radius: 8px;">
              <?php else: ?>
                <div style="width: 100%; height: 400px; background: #555; display: flex; align-items: center; justify-content: center; color: #999; border-radius: 8px;">No image available</div>
              <?php endif; ?>
            </div>

            <?php if ($page_index < count($pages) - 1): ?>
              <a href="?manga_id=<?= $manga_id ?>&page=<?= $page_index + 1 ?>" class="slide-btn next" aria-label="Next panel">&#10095;</a>
            <?php else: ?>
              <button class="slide-btn next" disabled>&#10095;</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="outer-panel" style="text-align: center; padding: 80px 40px;">
      <h2 style="color: white; font-size: 2rem; margin-bottom: 20px;">Select a Manga to Read</h2>
      <p style="color: #e7fbf7; font-size: 1.1rem; margin-bottom: 40px;">Choose from our collection of stories below:</p>
      
      <?php if (!empty($all_manga)): ?>
      <div class="manga-browser">
        <?php foreach ($all_manga as $manga): ?>
        <div class="manga-card-user">
          <a href="?manga_id=<?= $manga['id'] ?>" class="manga-card-link">
            <div class="manga-card-cover">
              <?php 
                // Show admin's cover image first, fall back to first page if not available
                if ($manga['cover_image']): ?>
                <img src="uploads/manga/<?= htmlspecialchars($manga['cover_image']) ?>" alt="<?= htmlspecialchars($manga['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else:
                // Get first page as fallback
                $stmt = $pdo->prepare('SELECT page_image FROM manga_pages WHERE manga_id = :id ORDER BY page_number ASC LIMIT 1');
                $stmt->execute([':id' => $manga['id']]);
                $first_page = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($first_page && $first_page['page_image']): ?>
                <img src="uploads/manga-pages/<?= htmlspecialchars($first_page['page_image']) ?>" alt="<?= htmlspecialchars($manga['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else: ?>
                <div style="width: 100%; height: 100%; background: #446a70; display: flex; align-items: center; justify-content: center; color: #999;">No Cover</div>
              <?php endif; endif; ?>
            </div>
            <div class="manga-card-info">
              <h3 class="manga-card-title"><?= htmlspecialchars($manga['title']) ?></h3>
              <p class="manga-card-description">
                <?php 
                  if ($manga['description']) {
                    echo htmlspecialchars(substr($manga['description'], 0, 80)) . '...';
                  } else {
                    echo 'Tap to read';
                  }
                ?>
              </p>
            </div>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
      <p style="color: #e7fbf7; font-size: 1.1rem;">No manga available yet. Please check back later!</p>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </main>

  <script>
    // LOGOUT
    document.querySelectorAll('.top-right a')[0].addEventListener('click', (e) => {
      if (!confirm('Log out?')) {
        e.preventDefault();
      }
    });
  </script>

</body>
</html>
