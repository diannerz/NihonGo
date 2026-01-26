<?php
require __DIR__ . '/admin-functions.php';
require_admin();

$kana_id = isset($_GET['id']) ? (int)$_GET['id'] : null;
$kana_type = isset($_GET['type']) ? $_GET['type'] : 'hiragana';

if (!$kana_id) {
    header('Location: kana-charts.php');
    exit;
}

$kana = get_kana_by_id($kana_id);
if (!$kana) {
    header('Location: kana-charts.php');
    exit;
}

// Handle AJAX save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_flashcard') {
        $result = $pdo->prepare('
            UPDATE kana_flashcards 
            SET mnemonic = :mnemonic, vocab_jp = :vocab_jp, 
                vocab_romaji = :vocab_romaji, vocab_eng = :vocab_eng
            WHERE id = :id
        ')->execute([
            ':mnemonic' => $_POST['mnemonic'] ?? '',
            ':vocab_jp' => $_POST['vocab_jp'] ?? '',
            ':vocab_romaji' => $_POST['vocab_romaji'] ?? '',
            ':vocab_eng' => $_POST['vocab_eng'] ?? '',
            ':id' => $kana_id
        ]);
        echo json_encode(['success' => $result]);
        exit;
    }
}

// Get adjacent kana for navigation
$all_kana = array_values(array_filter(get_all_kana(), fn($k) => $k['kana_type'] === $kana_type));
$current_index = array_search($kana['id'], array_column($all_kana, 'id'));
$prev_kana = $current_index > 0 ? $all_kana[$current_index - 1] : null;
$next_kana = $current_index < count($all_kana) - 1 ? $all_kana[$current_index + 1] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Flashcard - Admin</title>
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin-style.css">
  <style>
    .content {
      padding: 28px;
    }

    .flashcard-editor {
      background: var(--panel-bg);
      border-radius: 48px;
      padding: 36px 42px;
      max-width: 900px;
      margin: 0 auto;
    }

    .flashcard-header {
      text-align: center;
      margin-bottom: 32px;
      border-bottom: 2px solid var(--accent-mint);
      padding-bottom: 20px;
    }

    .kana-display {
      font-size: 4rem;
      color: var(--accent-mint);
      font-weight: 900;
      margin: 0;
    }

    .kana-romaji {
      font-size: 1.2rem;
      color: #e7fbf7;
      margin-top: 8px;
    }

    .form-section {
      margin-bottom: 28px;
    }

    .form-section label {
      display: block;
      font-size: 0.95rem;
      font-weight: 700;
      color: #1c535e;
      margin-bottom: 8px;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .form-section input,
    .form-section textarea {
      width: 100%;
      padding: 12px 16px;
      background: var(--panel-inner);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      color: white;
      font-family: inherit;
      font-size: 1rem;
      box-sizing: border-box;
    }

    .form-section textarea {
      min-height: 100px;
      resize: vertical;
    }

    .form-section input:focus,
    .form-section textarea:focus {
      outline: none;
      border-color: var(--accent-mint);
      box-shadow: 0 0 0 3px rgba(175, 230, 210, 0.2);
    }

    .vocab-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 16px;
    }

    .button-group {
      display: flex;
      gap: 12px;
      justify-content: center;
      margin-top: 28px;
    }

    .btn {
      padding: 12px 28px;
      border-radius: 8px;
      border: none;
      font-weight: 700;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-save {
      background: var(--accent-mint);
      color: #244850;
    }

    .btn-save:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(175, 230, 210, 0.3);
    }

    .btn-cancel {
      background: rgba(255, 255, 255, 0.1);
      color: white;
    }

    .btn-cancel:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .nav-buttons {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 28px;
      padding-top: 28px;
      border-top: 1px solid rgba(255, 255, 255, 0.2);
    }

    .nav-btn {
      padding: 10px 20px;
      background: var(--panel-inner);
      color: white;
      border: 1px solid var(--accent-mint);
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .nav-btn:hover:not(:disabled) {
      background: var(--accent-mint);
      color: #244850;
    }

    .nav-btn:disabled {
      opacity: 0.3;
      cursor: not-allowed;
    }

    .success-message {
      display: none;
      background: #2ecc71;
      color: white;
      padding: 12px 16px;
      border-radius: 8px;
      margin-bottom: 16px;
      text-align: center;
      font-weight: 600;
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
        <h1 class="topbar-title">Edit Flashcard</h1>
        <p class="topbar-subtitle">Customize this kana's learning content</p>
      </div>
      <div class="topbar-right">
        <img src="../images/exit.png" id="exitBtn" alt="Exit" title="Logout">
        <img src="../images/setting.png" id="settingsBtn" alt="Settings" title="Settings">
        <img src="../images/profile.png" id="profileBtn" alt="Profile" title="Profile">
      </div>
    </div>

    <div class="content">
      <div class="flashcard-editor">
        <div class="flashcard-header">
          <h2 class="kana-display"><?= htmlspecialchars($kana['kana_char']) ?></h2>
          <p class="kana-romaji"><?= htmlspecialchars($kana['romaji'] ?? '') ?></p>
        </div>

        <div id="successMessage" class="success-message">Changes saved successfully!</div>

        <form id="flashcardForm">
          <!-- Mnemonic -->
          <div class="form-section">
            <label for="mnemonic">Mnemonic (Memory Aid)</label>
            <textarea id="mnemonic" name="mnemonic" placeholder="Enter a memorable way to remember this character..."><?= htmlspecialchars($kana['mnemonic'] ?? '') ?></textarea>
          </div>

          <!-- Vocabulary -->
          <div class="form-section">
            <label>Example Vocabulary</label>
            <div class="vocab-grid">
              <div>
                <label style="font-size: 0.85rem;">Japanese</label>
                <input type="text" id="vocab_jp" name="vocab_jp" placeholder="Japanese word" value="<?= htmlspecialchars($kana['vocab_jp'] ?? '') ?>">
              </div>
              <div>
                <label style="font-size: 0.85rem;">Romaji</label>
                <input type="text" id="vocab_romaji" name="vocab_romaji" placeholder="Romaji" value="<?= htmlspecialchars($kana['vocab_romaji'] ?? '') ?>">
              </div>
              <div>
                <label style="font-size: 0.85rem;">English</label>
                <input type="text" id="vocab_eng" name="vocab_eng" placeholder="English meaning" value="<?= htmlspecialchars($kana['vocab_eng'] ?? '') ?>">
              </div>
            </div>
          </div>

          <!-- Buttons -->
          <div class="button-group">
            <button type="button" class="btn btn-save" onclick="saveFlashcard()">Save Changes</button>
            <button type="button" class="btn btn-cancel" onclick="goBack()">Cancel</button>
          </div>
        </form>

        <!-- Navigation -->
        <div class="nav-buttons">
          <button class="nav-btn" onclick="navPrev()" <?php echo $prev_kana ? '' : 'disabled'; ?>>← Previous Kana</button>
          <span style="color: #000000; font-weight: 600;">
            <?php echo ($current_index + 1) . ' of ' . count($all_kana); ?>
          </span>
          <button class="nav-btn" onclick="navNext()" <?php echo $next_kana ? '' : 'disabled'; ?>>Next Kana →</button>
        </div>
      </div>
    </div>
  </main>

  <script>
    function saveFlashcard() {
      const formData = new FormData(document.getElementById('flashcardForm'));
      formData.append('action', 'save_flashcard');

      fetch('kana-flashcards.php?id=<?= $kana_id ?>', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const msg = document.getElementById('successMessage');
          msg.style.display = 'block';
          setTimeout(() => {
            msg.style.display = 'none';
          }, 3000);
        }
      })
      .catch(err => console.error('Save failed:', err));
    }

    function goBack() {
      window.location.href = 'kana-charts.php';
    }

    function navPrev() {
      <?php if ($prev_kana): ?>
      window.location.href = `kana-flashcards.php?id=<?= $prev_kana['id'] ?>&kana=<?= urlencode($prev_kana['kana_char']) ?>&type=<?= $kana_type ?>`;
      <?php endif; ?>
    }

    function navNext() {
      <?php if ($next_kana): ?>
      window.location.href = `kana-flashcards.php?id=<?= $next_kana['id'] ?>&kana=<?= urlencode($next_kana['kana_char']) ?>&type=<?= $kana_type ?>`;
      <?php endif; ?>
    }

    document.getElementById('exitBtn').onclick = function() {
      if (confirm('Log out?')) {
        location.href = '../php/logout.php';
      }
    };

    document.getElementById('settingsBtn').onclick = function() {
      location.href = '../settings.php';
    };

    document.getElementById('profileBtn').onclick = function() {
      location.href = '../dashboard.php';
    };
  </script>
</body>
</html>
