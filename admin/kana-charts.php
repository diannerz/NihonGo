<?php
require __DIR__ . '/admin-functions.php';
require_admin();

$all_kana = get_all_kana();
$hiragana = array_filter($all_kana, fn($k) => $k['kana_type'] === 'hiragana');
$katakana = array_filter($all_kana, fn($k) => $k['kana_type'] === 'katakana');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kana Charts - Admin</title>
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin-style.css">
  <style>
    .content {
      padding: 28px;
    }

    .chart-container {
      background: var(--panel-bg);
      border-radius: 48px;
      padding: 36px 42px;
      margin-bottom: 28px;
    }

    .chart-container h3 {
      color: white;
      font-size: 1.8rem;
      font-weight: 900;
      margin-bottom: 24px;
      letter-spacing: 1px;
    }

    .kana-chart {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 12px;
      background: var(--panel-inner);
      border-radius: 32px;
      padding: 24px;
    }

    .kana-cell {
      background: rgba(255, 255, 255, 0.1);
      border-radius: 16px;
      padding: 16px;
      text-align: center;
      cursor: pointer;
      transition: all 0.25s ease;
      border: 2px solid transparent;
      position: relative;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .kana-cell:hover {
      background: rgba(255, 255, 255, 0.2);
      transform: translateY(-4px);
      border-color: var(--accent-mint);
    }

    .kana-cell .char {
      font-size: 2.4rem;
      color: white;
      font-weight: 900;
      margin-bottom: 8px;
    }

    .kana-cell .romaji {
      font-size: 0.85rem;
      color: #e7fbf7;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .kana-cell .edit-btn {
      display: none;
      padding: 6px 12px;
      background: var(--accent-mint);
      color: var(--main-bg);
      border: none;
      border-radius: 6px;
      font-size: 0.75rem;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s ease;
      white-space: nowrap;
    }

    .kana-cell:hover .edit-btn {
      display: block;
    }

    .kana-cell .edit-btn:hover {
      color: var(--text-main);
      transform: scale(1.05);
      box-shadow: 0 4px 12px rgba(175, 230, 210, 0.3);
    }
  </style>
</head>
<body>
  <!-- SIDEBAR -->
  <?php include 'sidebar.php'; ?>

  <!-- MAIN -->
  <main class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h1 class="topbar-title">Kana Charts & Flashcards</h1>
        <p class="topbar-subtitle">Click on a kana to edit its flashcard</p>
      </div>
      <div class="topbar-right">
        <img src="../images/exit.png" id="exitBtn" alt="Exit" title="Logout">
        <img src="../images/setting.png" id="settingsBtn" alt="Settings" title="Settings">
        <img src="../images/profile.png" id="profileBtn" alt="Profile" title="Profile">
      </div>
    </div>

    <div class="content">
      <!-- Hiragana Chart -->
      <div class="chart-container">
        <h3>ひらがな (Hiragana)</h3>
        <div class="kana-chart">
          <?php if (!empty($hiragana)): ?>
            <?php foreach ($hiragana as $kana): ?>
            <div class="kana-cell">
              <div class="char"><?= htmlspecialchars($kana['kana_char']) ?></div>
              <div class="romaji"><?= htmlspecialchars($kana['romaji'] ?? '') ?></div>
              <button class="edit-btn" title="Edit" onclick="editKana(<?= $kana['id'] ?>, '<?= htmlspecialchars($kana['kana_char']) ?>', '<?= htmlspecialchars($kana['romaji'] ?? '') ?>', 'hiragana')">Edit</button>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; color: #e7fbf7; padding: 40px;">No hiragana found</div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Katakana Chart -->
      <div class="chart-container">
        <h3>カタカナ (Katakana)</h3>
        <div class="kana-chart">
          <?php if (!empty($katakana)): ?>
            <?php foreach ($katakana as $kana): ?>
            <div class="kana-cell">
              <div class="char"><?= htmlspecialchars($kana['kana_char']) ?></div>
              <div class="romaji"><?= htmlspecialchars($kana['romaji'] ?? '') ?></div>
              <button class="edit-btn" title="Edit this flashcard" onclick="editKana(<?= $kana['id'] ?>, '<?= htmlspecialchars($kana['kana_char']) ?>', '<?= htmlspecialchars($kana['romaji'] ?? '') ?>', 'katakana')">Edit</button>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div style="grid-column: 1/-1; text-align: center; color: #e7fbf7; padding: 40px;">No katakana found</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <script>
    function editKana(id, kana, romaji, type) {
      // Navigate to flashcard editor
      window.location.href = `kana-flashcards.php?id=${id}&kana=${encodeURIComponent(kana)}&type=${type}`;
    }

    document.getElementById('exitBtn').onclick = function() {
      if (confirm('Log out?')) {
        location.href = '../php/logout.php';
      }
    };

    document.getElementById('settingsBtn').onclick = function() {
      location.href = '../settings.php';
    };

    // Profile button functionality
    document.getElementById('profileBtn').onclick = function() {
      location.href = 'donation.php';
    };
  </script>
</body>
</html>
