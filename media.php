<?php
require "php/check_auth.php";
require "php/db.php";

// Redirect if not logged in
if (!$user) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="manga-page">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Manga — An egg’s tale</title>
  <link rel="stylesheet" href="media.css" />
</head>

<script>
// ---- Send manga progress ONCE when page loads ----
window.addEventListener("DOMContentLoaded", () => {
  fetch("php/save_progress.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ action: "manga_view" })
  }).catch(()=>{});
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
      <img src="images/exit.png" alt="door" id="exitBtn">
      <img src="images/setting.png" alt="gear">
      <img src="images/profile.png" alt="onigiri">
    </div>
  </header>

  <!-- STORY WRAPPER -->
  <main class="media-wrapper">
    <div class="outer-panel">
      <div class="story-header">
        <button class="outer-arrow left" aria-label="Previous story">&lt;</button>
        <h2 class="story-title">An egg’s tale</h2>
        <div class="progress-dots" aria-hidden="true"></div>
        <button class="outer-arrow right" aria-label="Next story">&gt;</button>
      </div>

      <div class="story-panel">
        <div class="story-left">
          <div class="story-text">
            <p class="en">“One day, an egg had fallen.”</p>
            <p class="jp">あるひ、たまごがおちていました。</p>
            <p class="romaji">Aru hi, tamago ga ochite imashita</p>
          </div>
        </div>

        <div class="story-right">
          <!-- SLIDER -->
          <div class="slider">
            <button class="slide-btn prev" aria-label="Previous panel">&#10094;</button>

            <div class="slides" aria-live="polite">
              <img src="images/eggtale1.png" class="active" alt="Panel 1">
              <img src="images/eggtale2.png" alt="Panel 2">
              <img src="images/eggtale3.png" alt="Panel 3">
              <img src="images/eggtale4.png" alt="Panel 4">
              <img src="images/eggtale5.png" alt="Panel 5">
              <img src="images/eggtale6.png" alt="Panel 6">
            </div>

            <button class="slide-btn next" aria-label="Next panel">&#10095;</button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="script.js" defer></script>

<script>
// LOGOUT
document.getElementById("exitBtn").addEventListener("click", () => {
  if (!confirm("Log out?")) return;
  window.location.href = "php/logout.php";
});
</script>

</body>
</html>
