<?php
require "php/check_auth.php";
require "php/db.php";

// Redirect if not logged in
if (!$user) {
    header("Location: login.html");
    exit;
}

// REDIRECT ADMIN TO ADMIN DASHBOARD
if ($user && $user['role'] === 'admin') {
    header("Location: admin/dashboard.php");
    exit;
}

$uid = (int) $_SESSION['user_id'];

// fetch today's daily progress
$today = (new DateTime('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d');

$dailyStmt = $pdo->prepare("
    SELECT 
        COALESCE(flashcard_views,0) AS flashcard_views,
        COALESCE(manga_views,0) AS manga_views,
        COALESCE(vocab_quiz_completed,0) AS vocab_quiz_completed
    FROM daily_progress
    WHERE user_id = :uid
      AND day_date = :day
    LIMIT 1
");
$dailyStmt->execute([
    ':uid' => $_SESSION['user_id'],
    ':day' => $today
]);

$daily = $dailyStmt->fetch(PDO::FETCH_ASSOC) ?: [
    'flashcard_views' => 0,
    'manga_views' => 0,
    'vocab_quiz_completed' => 0
];


$view5_target = min(100, round(($daily['flashcard_views'] / 5) * 100));
$manga_target = min(100, round(($daily['manga_views'] / 1) * 100));
$quiz_target  = min(100, round(($daily['vocab_quiz_completed'] / 1) * 100));



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

    <!-- Kana Charts & Flashcards -->
    <a class="menu-item" href="kana-charts.php">
      <img src="images/kana charts.png" alt="kana">
      <span>Kana Charts & Flashcards</span>
    </a>

    <!-- Vocabulary Quiz -->
    <a class="menu-item" href="kana-quiz.php">
      <img src="images/kana writing.png" alt="quiz">
      <span>Kana Quiz</span>
    </a>

    <!-- Manga -->
    <a class="menu-item" href="media.php">
      <img src="images/comics.png" alt="manga">
      <span>Manga</span>
    </a>

<a class="menu-item" href="dictionary.php">
  <img src="images/dictionary.png" alt="dictionary">
  <span>Japanese Dictionary</span>
</a>


  </nav>
</aside>



  <!-- MAIN -->
  <main class="main">
    <div class="topbar">
      <img src="images/exit.png" alt="exit" id="exitBtn">
      <img src="images/profile.png" alt="gear" id="settingsBtn">
 <a href="donation.php">
    <img src="images/donations.png" alt="Donate" title="Donate" id="profileBtn">
</a>

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

      <!-- My Progress -->
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
window.location.href = "/NihonGo/settings.php";


    });
  </script>


  <script src="script.js" defer></script>

</body>
</html>

