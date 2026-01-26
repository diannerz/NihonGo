<?php
require "php/check_auth.php";
require "php/db.php";
if (!$user) {
  header("Location: login.html");
  exit;
}

$search = $_GET['q'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 6;
$offset = ($page - 1) * $limit;
$params = [];
$where = "";
if ($search !== "") {
  $where = "WHERE jp_word LIKE :q OR romaji LIKE :q OR english LIKE :q";
  $params[':q'] = "%$search%";
}
$stmt = $pdo->prepare("
  SELECT * FROM dictionary
  $where
  ORDER BY jp_word
  LIMIT $limit OFFSET $offset
");
$stmt->execute($params);
$words = $stmt->fetchAll(PDO::FETCH_ASSOC);
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM dictionary $where");
$countStmt->execute($params);
$total = $countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $limit));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Japanese Dictionary</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    /* ---------------- :root Variables ---------------- */
    :root {
      --main-bg: #dffbf7;
      --text-main: #244850;
      --white: #ffffff;
      --panel-bg: #8ea6ab;
      --accent-mint: #e7fbf7;
      --shadow-soft: rgba(0, 0, 0, 0.06);
      --gap: 20px;
    }

    /* ---------------- Reset & Base Styles ---------------- */
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: "Yu Gothic", "Segoe UI", Roboto, Arial, sans-serif;
      background: var(--main-bg);
      color: var(--text-main);
      display: flex;
      flex-direction: column;
      height: 100vh;
      width: 100vw;
      margin: 0;
      overflow: hidden;
    }

    /* ---------------- Top Bar Styling ---------------- */
    .topbar {
      height: 72px;
      background: #7aa5a8;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 24px;
      padding: 8px 36px;
      border-bottom: 4px solid #4d7d86;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      z-index: 1000;
      box-sizing: border-box;
    }

    .topbar img {
      width: 64px;
      height: auto;
      cursor: pointer;
      flex-shrink: 0;
    }

    .topbar-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .header-text {
      display: flex;
      flex-direction: column;
      line-height: 1.1;
    }

    .header-text .title {
      font-size: 1.6rem;
      font-weight: 800;
      color: #fff;
    }

    .header-text .subtitle {
      font-size: 0.85rem;
      color: #eaf5f6;
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 14px;
      justify-content: flex-end;
    }

    .topbar-right img {
      width: 64px;
      height: auto;
      cursor: pointer;
      flex-shrink: 0;
      margin-left: 12px;
    }

    /* ---------------- Content Area Styling ---------------- */
    .content {
      padding: 20px 20px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      overflow-y: auto;
      flex: 1;
      width: 100%; /* Full width for content */
      height: calc(100vh - 72px); /* Full height minus topbar */
    }

    /* ---------------- Dictionary Grid Styling ---------------- */
    .dictionary-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      width: 100%;
      grid-auto-rows: 280px;
    }

    .dict-card {
      background: #8ea6ab;
      border-radius: 48px;
      padding: 24px;
      color: white;
      position: relative;
      min-height: 280px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      box-shadow: 0 12px 30px rgba(0,0,0,0.08);
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .dict-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 16px 40px rgba(0,0,0,0.12);
    }

    .dict-card img {
      width: 160px;
      position: absolute;
      top: 24px;
      right: 24px;
    }

    .dict-card h2 {
      font-size: 2.9rem;
      margin-bottom: 8px;
      font-weight: 900;
    }

    .dict-card .romaji {
      font-size: 1.2rem;
      color: #e7fbf7;
      margin-bottom: 6px;
      font-weight: 700;
    }

    .dict-card .pos {
      font-weight: 800;
      text-decoration: underline;
      font-size: 0.9rem;
      margin-bottom: 8px;
    }

    .dict-card .meaning {
      margin-top: 12px;
      font-size: 1.7rem;
      line-height: 1.4;
    }

    .dict-card .sound-btn {
      position: absolute;
      bottom: 48px;
      right: 6px;
      background: transparent;
      border: none;
      width: 65px;
      height: 65px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
    }

    .dict-card .sound-btn img {
      width: 70px;
      height: 70px;
    }

    /* ---------------- Search Bar Styling ---------------- */
    .search-bar {
      display: flex;
      justify-content: center;
      gap: 12px;
      margin-bottom: 28px;
    }

    .search-bar input {
      padding: 14px 20px;
      width: 360px;
      border-radius: 12px;
      border: none;
      font-size: 1.1rem;
      background: rgba(255,255,255,0.95);
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .search-bar input::placeholder {
      color: #999;
    }

    .search-bar button {
      background-color: #7aa5a8;
      border: none;
      padding: 8px 14px;
      cursor: pointer;
      color: white;
      border-radius: 12px;
      font-size: 1.2rem;
      font-weight: 700;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      transition: background-color 0.2s ease, transform 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .search-bar button img {
      width: 24px;
      height: 24px;
    }

    .search-bar button:hover {
      background-color: #6a9598;
      transform: translateY(-2px);
    }

    /* ---------------- Pagination Styling ---------------- */
    .pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 32px;
      margin-top: 48px;
      padding: 24px;
    }

    .pagination a {
      text-decoration: none;
      color: white;
      font-weight: 900;
      font-size: 1.2rem;
      background: #7aa5a8;
      padding: 12px 28px;
      border-radius: 12px;
      transition: background-color 0.2s ease, transform 0.2s ease;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
    }

    .pagination a:hover {
      background-color: #6a9598;
      transform: translateY(-2px);
    }

    .pagination span {
      font-size: 1.3rem;
      font-weight: 800;
      color: var(--text-main);
    }

    /* ---------------- Remove Sidebar Space ---------------- */
    .main {
      flex: 1;
      display: flex;
      flex-direction: column;
      height: 100vh;
      padding-top: 72px;
      width: 100%;
      overflow: hidden;      margin-left: 0 !important;    }

    /* Responsive Tweaks */
    @media (max-width: 900px) {
      .dictionary-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    .content h1 {
      display: none;
    }
  </style>
</head>
<body class="dictionary-page">
  <!-- Main Content Area -->
  <div class="main">
    <!-- Top Bar with same icons as Kana Quiz -->
    <div class="topbar">
      <div class="top-left">
        <a href="dashboard.php"><img src="images/home.png" alt="Home"></a>
        <div class="header-text">
          <span class="title">Japanese Dictionary</span>
          <span class="subtitle">Click the sound button beside each word to hear how a word is pronounced.</span>
        </div>
      </div>
      <div class="top-right">
        <img src="images/exit.png" id="exitBtn" alt="Exit">
        <img src="images/setting.png" id="settingsBtn" alt="Settings">
        <img src="images/profile.png" id="profileBtn" alt="Profile">
      </div>
    </div>

    <!-- Content Area for the Dictionary -->
    <div class="content">
      <h1>Dictionary</h1>

      <!-- Search Bar -->
      <form method="get" class="search-bar">
        <input type="text" name="q" placeholder="Enter a keyword" value="<?= htmlspecialchars($search) ?>">
        <button type="submit"><img src="/NihonGo/images/search.png" alt="Search"></button>
      </form>

      <!-- Dictionary Grid with 6 words per page -->
      <div class="dictionary-grid">
        <?php foreach ($words as $w): ?>
        <div class="dict-card">
          <img src="/NihonGo/images/<?= htmlspecialchars($w['image_file']) ?>" alt="Image of <?= htmlspecialchars($w['jp_word']) ?>">
          <h2><?= htmlspecialchars($w['jp_word']) ?></h2>
          <div class="romaji"><?= htmlspecialchars($w['romaji']) ?></div>
          <div class="pos"><?= htmlspecialchars($w['part_of_speech']) ?></div>
          <div class="meaning"><?= htmlspecialchars($w['english']) ?></div>
          <button class="sound-btn" data-audio="/NihonGo/sounds/dictionary/<?= htmlspecialchars(pathinfo($w['image_file'], PATHINFO_FILENAME)) ?>.mp3"><img src="/NihonGo/images/sound.png" alt="Sound"></button>
        </div>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <div class="pagination">
        <?php if ($page > 1): ?>
          <a href="?q=<?= urlencode($search) ?>&page=<?= $page - 1 ?>">◀ Prev</a>
        <?php endif; ?>
        <span>Page <?= $page ?> / <?= $totalPages ?></span>
        <?php if ($page < $totalPages): ?>
          <a href="?q=<?= urlencode($search) ?>&page=<?= $page + 1 ?>">Next ▶</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <script>
    // Sound button functionality
    const audio = new Audio();
    document.querySelectorAll(".sound-btn").forEach(btn => {
      btn.addEventListener("click", () => {
        audio.src = btn.dataset.audio;
        audio.currentTime = 0;
        audio.play();
      });
    });

    // Exit button functionality
    document.getElementById('exitBtn').onclick = function() {
      if (confirm('Log out?')) {
        location.href = 'php/logout.php';
      }
    };

    // Settings button functionality
    document.getElementById('settingsBtn').onclick = function() {
      location.href = 'settings.php';
    };

    // Profile button functionality
    document.getElementById('profileBtn').onclick = function() {
location.href = 'donation.php';
    };
  </script>
</body>
</html>
