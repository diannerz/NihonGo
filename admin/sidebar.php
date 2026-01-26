<?php
// Admin Sidebar - Include in all admin pages
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
  <nav class="side-menu">
    <!-- Dashboard -->
    <a class="menu-item <?php echo ($current_page === 'dashboard.php') ? 'active' : ''; ?>" href="dashboard.php">
      <img src="../images/home.png" alt="Dashboard">
      <span>Dashboard</span>
    </a>

    <!-- Kana Charts & Flashcards -->
    <a class="menu-item <?php echo ($current_page === 'kana-charts.php' || $current_page === 'kana-flashcards.php') ? 'active' : ''; ?>" href="kana-charts.php">
      <img src="../images/kana charts.png" alt="Kana">
      <span>Kana Charts & Flashcards</span>
    </a>

    <!-- Manga -->
    <a class="menu-item <?php echo ($current_page === 'manga.php') ? 'active' : ''; ?>" href="manga.php">
      <img src="../images/manga.png" alt="Manga">
      <span>Manga Manager</span>
    </a>
  </nav>
</aside>
