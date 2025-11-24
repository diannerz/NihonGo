<?php
require "php/check_auth.php";
require "php/db.php";

// Redirect if not logged in
if (!$user) {
    header("Location: login.html");
    exit;
}

$uid = (int) $_SESSION['user_id'];

// fetch today's daily progress
$today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
$dpStmt = $pdo->prepare("
    SELECT COALESCE(flashcard_views,0) AS flashcard_views,
           COALESCE(manga_views,0) AS manga_views,
           COALESCE(vocab_quiz_completed,0) AS vocab_quiz_completed
    FROM daily_progress
    WHERE user_id = :uid AND day_date = :day
    LIMIT 1
");
$dpStmt->execute([':uid'=>$uid, ':day'=>$today]);
$dp = $dpStmt->fetch(PDO::FETCH_ASSOC) ?: ['flashcard_views'=>0,'manga_views'=>0,'vocab_quiz_completed'=>0];

// prepare percentages or targets for the UI (the front-end animation uses data-target as percent)
// I'll assume each challenge's maximum for the progress bar is 100% and that the track expects percent.
// For your "View 5 kana flashcards" challenge we treat 5 views as 100% (so compute based on 5).
$view5_target = min(100, round(($dp['flashcard_views'] / 5) * 100));
$manga_target = min(100, round(($dp['manga_views'] / 1) * 100)); // if reading manga once equals 100%
$quiz_target = min(100, round(($dp['vocab_quiz_completed'] / 1) * 100)); // adjust maxima as you wish


// count distinct mastered kana per type (mastery_level >= 2)
$hiraCount = (int)$pdo->query("
    SELECT COUNT(DISTINCT kana_char)
    FROM kana_progress
    WHERE user_id = $uid
      AND kana_type = 'hiragana'
      AND mastery_level = 2
")->fetchColumn();

$kataCount = (int)$pdo->query("
    SELECT COUNT(DISTINCT kana_char)
    FROM kana_progress
    WHERE user_id = $uid
      AND kana_type = 'katakana'
      AND mastery_level = 2
")->fetchColumn();



// enforce upper limit of 46
$hiraCount = min($hiraCount, 46);
$kataCount = min($kataCount, 46);
$hiraPct = round(($hiraCount / 46) * 100);
$kataPct = round(($kataCount / 46) * 100);

?>
<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>NihonGo — Dashboard</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>

  <!-- SIDEBAR -->
  <aside class="sidebar">
    <nav class="side-menu">
      <div class="menu-item">
        <img src="images/home.png" alt="home">
        <span>Dashboard</span>
      </div>

      <!-- FIXED LINK: now points to kana-charts.php -->
      <a class="menu-item" href="kana-charts.php">
        <img src="images/kana charts.png" alt="kana">
        <span>Kana Charts and Flashcards</span>
      </a>

      <div class="menu-item">
        <a class="menu-item" href="kana-quiz.php">
        <img src="images/kana writing.png" alt="writing">
        <span>Vocabulary Quiz</span>
      </div>

      <a href="media.php" class="menu-item">
        <img src="images/comics.png" alt="Media">
        <span>Manga</span>
      </a>

      <div class="menu-item">
        <img src="images/dictionary.png" alt="dict">
        <span>Japanese Dictionary</span>
      </div>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="main">
    <div class="topbar">
      <img src="images/exit.png" alt="exit" id="exitBtn">
      <img src="images/setting.png" alt="gear" id="settingsBtn">
      <img src="images/profile.png" alt="profile">
    </div>

    <div class="content">

      <!-- Daily Challenges (unchanged) -->
      <div class="challenges-panel">
        <div class="onigiri">
          <img src="images/daily challenge.png" alt="mascot">
        </div>
        <div class="ch-right">
          <h2 class="ch-title">Daily Challenges</h2>

          <div class="challenge-list">
<div class="challenge-row">
  <div class="challenge-label">Read any manga in the media page</div>
  <div class="progress-area">
    <div class="progress-track" data-target="<?= $manga_target ?>">
      <div class="progress-inner-fill" style="width:0%"></div>
    </div>
    <div class="progress-percent"><?= $manga_target ?>%</div>
  </div>
</div>

<div class="challenge-row">
  <div class="challenge-label">Take daily vocabulary quiz</div>
  <div class="progress-area">
    <div class="progress-track" data-target="<?= $quiz_target ?>">
      <div class="progress-inner-fill" style="width:0%"></div>
    </div>
    <div class="progress-percent"><?= $quiz_target ?>%</div>
  </div>
</div>

<div class="challenge-row">
  <div class="challenge-label">View 5 kana flashcards</div>
  <div class="progress-area">
    <div class="progress-track" data-target="<?= $view5_target ?>">
      <div class="progress-inner-fill" style="width:0%"></div>
    </div>
    <div class="progress-percent"><?= $view5_target ?>%</div>
  </div>
</div>


</div>
        </div>
      </div>

      <!-- My Progress (same UI but numbers are now dynamic) -->
      <h2 class="myprogress-title">My progress</h2>

      <div class="progress-band">
        <div class="progress-inner">
          <div class="col">
            <div class="pill">Hiragana</div>
            <div class="big-count"><?= $hiraCount ?>/46</div>
            <div class="percent"><?= $hiraPct ?>%</div>
          </div>

          <div class="col">
            <div class="pill">Katakana</div>
            <div class="big-count"><?= $kataCount ?>/46</div>
            <div class="percent"><?= $kataPct ?>%</div>
          </div>
        </div>
      </div>

    </div>
  </main>

  <script>
    document.getElementById("exitBtn").addEventListener("click", () => {
      if (!confirm("Log out?")) return;
      window.location.href = "php/logout.php";
    });

    document.getElementById("settingsBtn").addEventListener("click", () => {
      window.location.href = "settings.html";
    });
  </script>

  <!-- ADD THIS -->
  <script src="script.js" defer></script>

</body>
</html>

