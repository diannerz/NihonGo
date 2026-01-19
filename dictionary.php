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
<title>NihonGo — Dictionary</title>
<link rel="stylesheet" href="styles.css">
</head>

<body>

<!-- SIDEBAR (UNCHANGED) -->
<aside class="sidebar">
  <nav class="side-menu">

    <a class="menu-item" href="kana-charts.php">
      <img src="images/kana charts.png">
      <span>Kana Charts & Flashcards</span>
    </a>

    <a class="menu-item" href="kana-quiz.php">
      <img src="images/kana writing.png">
      <span>Kana Quiz</span>
    </a>

    <a class="menu-item" href="media.php">
      <img src="images/comics.png">
      <span>Manga</span>
    </a>

    <a class="menu-item" href="dictionary.php">
      <img src="images/dictionary.png">
      <span>Japanese Dictionary</span>
    </a>

  </nav>
</aside>

<!-- MAIN -->
<main class="main">

  <!-- TOP BAR (RESTORED) -->
  <div class="top-bar">
  <div class="top-left">
    <a href="dashboard.php">
      <img src="images/home.png" alt="home">
    </a>
    <div class="header-text-inline">
      <div class="title">Dictionary</div>
      <div class="subtitle">
        Click the sound button beside each word to hear how a word is pronounced.
      </div>
    </div>
  </div>

  <div class="top-right">
    <img src="images/exit.png">
    <img src="images/setting.png">
    <img src="images/profile.png">
  </div>
</div>

<script>
    document.getElementById("exitBtn").addEventListener("click", () => {
      if (!confirm("Log out?")) return;
      window.location.href = "php/logout.php";
    });

    document.getElementById("settingsBtn").addEventListener("click", () => {
      window.location.href = "settings.html";
    });
  </script>
  
  <!-- CONTENT -->
  <div class="content dictionary-page">

    <div class="dictionary-header">
      <h1>Dictionary</h1>
      <p>Click the sound button beside each word to hear how a word is pronounced.</p>

      <form method="get" class="dictionary-search">
        <input type="text" name="q" placeholder="Enter a keyword"
          value="<?= htmlspecialchars($search) ?>">
        <button type="submit">🔍</button>
      </form>
    </div>

    <!-- GRID -->
    <div class="dictionary-grid">
      <?php foreach ($words as $w): ?>
        <div class="dict-card">
          <img src="/NihonGo/images/<?= htmlspecialchars($w['image_file']) ?>">
          <h2><?= htmlspecialchars($w['jp_word']) ?></h2>
          <div class="romaji"><?= htmlspecialchars($w['romaji']) ?></div>
          <div class="pos"><?= htmlspecialchars($w['part_of_speech']) ?></div>
          <div class="meaning"><?= htmlspecialchars($w['english']) ?></div>

          <button class="sound-btn"
            data-audio="/NihonGo/sounds/dictionary/<?= htmlspecialchars($w['audio_file']) ?>">
            🔊
          </button>
        </div>
      <?php endforeach; ?>
    </div>

    <!-- PAGINATION -->
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
</main>

<script>
const audio = new Audio();
document.querySelectorAll(".sound-btn").forEach(btn => {
  btn.addEventListener("click", e => {
    e.stopPropagation();
    audio.src = btn.dataset.audio;
    audio.play();
  });
});

document.getElementById("exitBtn").onclick = () => {
  if (confirm("Log out?")) location.href = "php/logout.php";
};
document.getElementById("settingsBtn").onclick = () => {
  location.href = "settings.html";
};
</script>

</body>
</html>
