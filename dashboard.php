
<?php
require __DIR__ . '/php/check_auth.php';
if (!$user) {
    header('Location: login.html');
    exit;
}
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

  <!-- SIDEBAR - only ONE Dashboard menu item (no duplicated logo) -->
  <aside class="sidebar">
    <!-- removed the extra top logo image so there's only one Dashboard entry below -->
    <nav class="side-menu">
      <div class="menu-item">
        <img src="images/home.png" alt="home">
        <span>Dashboard</span>
      </div>
<a class="menu-item" href="kana-charts.html">
  <img src="images/kana charts.png" alt="kana">
  <span>Kana Charts and Flashcards</span>
</a>

      <div class="menu-item">
        <img src="images/kana writing.png" alt="writing">
        <span>Kana Character Writing</span>
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
      <img src="images/exit.png" alt="exit">
      <img src="images/setting.png" alt="gear">
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
          <!-- challenge-list unchanged -->
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

      <!-- My progress (unchanged) -->
      <h2 class="myprogress-title">My progress</h2>

      <div class="progress-band">
        <div class="progress-inner">
          <div class="col">
            <div class="pill">Hiragana</div>
            <div class="big-count">0/46</div>
            <div class="percent">0 %</div>
          </div>

          <div class="col">
            <div class="pill">Katakana</div>
            <div class="big-count">0/46</div>
            <div class="percent">0 %</div>
          </div>
        </div>
      </div>
    </div>
  </main>

<script>
  (function() {
    const exitImg = document.querySelector('.topbar img[alt="exit"]');
    if (!exitImg) return;

    exitImg.addEventListener('click', () => {
      if (!confirm('Log out and return to login?')) return;
      window.location.href = 'php/logout.php';
    });
  })();
</script>



<script>
  (function() {
    const settingsImg = document.querySelector('.topbar img[alt="gear"]');
    if (!settingsImg) return;
    settingsImg.addEventListener('click', () => {
      window.location.href = 'settings.html';
    });
  })();
</script>



</body>
</html>
