<?php
require "php/check_auth.php";
require "php/db.php";

// Redirect if not logged in
if (!$user) {
    header("Location: login.html");
    exit;
}

$uid = (int) $_SESSION['user_id'];

// count distinct learned kana per type
$hiraCount = (int) $pdo->query("SELECT COUNT(DISTINCT kana_char) FROM kana_progress WHERE user_id=$uid AND kana_type='hiragana'")->fetchColumn();
$kataCount = (int) $pdo->query("SELECT COUNT(DISTINCT kana_char) FROM kana_progress WHERE user_id=$uid AND kana_type='katakana'")->fetchColumn();

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
        <img src="images/kana writing.png" alt="writing">
        <span>Vocabulary Quiz</span>
      </div>

      <a href="media.html" class="menu-item">
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
                <div class="progress-track" data-target="20">
                  <div class="progress-inner-fill" style="width:0%"></div>
                </div>
                <div class="progress-percent">0%</div>
              </div>
            </div>

            <div class="challenge-row">
              <div class="challenge-label">Take daily vocabulary quiz</div>
              <div class="progress-area">
                <div class="progress-track" data-target="40">
                  <div class="progress-inner-fill" style="width:0%"></div>
                </div>
                <div class="progress-percent">0%</div>
              </div>
            </div>

            <div class="challenge-row">
              <div class="challenge-label">View 5 kana flashcards</div>
              <div class="progress-area">
                <div class="progress-track" data-target="60">
                  <div class="progress-inner-fill" style="width:0%"></div>
                </div>
                <div class="progress-percent">0%</div>
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
  // LOGOUT
  document.getElementById("exitBtn").addEventListener("click", () => {
    if (!confirm("Log out?")) return;
    window.location.href = "php/logout.php";
  });

  // SETTINGS
  document.getElementById("settingsBtn").addEventListener("click", () => {
    window.location.href = "settings.html";
  });
</script>

</body>
</html>
