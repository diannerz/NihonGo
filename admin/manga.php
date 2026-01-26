<?php
require __DIR__ . '/admin-functions.php';
require_admin();

$manga_list = get_all_manga();

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'create_manga') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $cover_image = null;

        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/manga/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $cover_image = handle_file_upload($_FILES['cover_image'], $upload_dir);
        }

        if ($title && $cover_image) {
            $manga_id = create_manga($title, $description, $cover_image);
            echo json_encode(['success' => true, 'manga_id' => $manga_id]);
            exit;
        }
        echo json_encode(['success' => false, 'error' => 'Missing required fields']);
        exit;
    }

    if ($action === 'delete_manga') {
        $manga_id = (int)($_POST['manga_id'] ?? 0);
        if ($manga_id && delete_manga($manga_id)) {
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }

    if ($action === 'add_page') {
        $manga_id = (int)($_POST['manga_id'] ?? 0);
        $page_number = (int)($_POST['page_number'] ?? 0);
        $en_text = $_POST['en_text'] ?? '';
        $jp_text = $_POST['jp_text'] ?? '';
        $romaji_text = $_POST['romaji_text'] ?? '';
        $page_image = null;

        if (isset($_FILES['page_image']) && $_FILES['page_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/manga-pages/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $page_image = handle_file_upload($_FILES['page_image'], $upload_dir);
        }

        if (!$manga_id) {
            echo json_encode(['success' => false, 'error' => 'No manga ID provided']);
            exit;
        }
        if (!$page_image) {
            echo json_encode(['success' => false, 'error' => 'Failed to upload image or no image provided']);
            exit;
        }
        if (!$en_text) {
            echo json_encode(['success' => false, 'error' => 'English text is required']);
            exit;
        }

        try {
            // Insert page with all text fields
            $stmt = $pdo->prepare('
                INSERT INTO manga_pages (manga_id, page_number, page_image, en_text, jp_text, romaji_text)
                VALUES (:manga_id, :page_num, :image, :en, :jp, :romaji)
            ');
            $result = $stmt->execute([
                ':manga_id' => $manga_id,
                ':page_num' => $page_number,
                ':image' => $page_image,
                ':en' => $en_text,
                ':jp' => $jp_text,
                ':romaji' => $romaji_text
            ]);
            if ($result) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Database insert failed']);
            }
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'delete_page') {
        $page_id = (int)($_POST['page_id'] ?? 0);
        if ($page_id && delete_manga_page($page_id)) {
            echo json_encode(['success' => true]);
            exit;
        }
        echo json_encode(['success' => false]);
        exit;
    }

    if ($action === 'update_manga') {
        $manga_id = (int)($_POST['manga_id'] ?? 0);
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        $cover_image = null;

        if (!$manga_id || !$title) {
            echo json_encode(['success' => false, 'error' => 'Missing required fields']);
            exit;
        }

        // Handle cover image if provided
        if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = __DIR__ . '/../uploads/manga/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $cover_image = handle_file_upload($_FILES['cover_image'], $upload_dir);
        }

        try {
            update_manga($manga_id, $title, $description, $cover_image);
            echo json_encode(['success' => true]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get_manga_pages') {
        $manga_id = (int)($_POST['manga_id'] ?? 0);
        if (!$manga_id) {
            echo json_encode(['success' => false, 'error' => 'No manga ID']);
            exit;
        }
        
        try {
            $pages = get_manga_pages($manga_id);
            echo json_encode(['success' => true, 'pages' => $pages]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'update_page') {
        $page_id = (int)($_POST['page_id'] ?? 0);
        $en_text = $_POST['en_text'] ?? '';
        $jp_text = $_POST['jp_text'] ?? '';
        $romaji_text = $_POST['romaji_text'] ?? '';

        if (!$page_id) {
            echo json_encode(['success' => false, 'error' => 'No page ID']);
            exit;
        }

        try {
            $stmt = $pdo->prepare('
                UPDATE manga_pages
                SET en_text = :en, jp_text = :jp, romaji_text = :romaji
                WHERE id = :id
            ');
            $result = $stmt->execute([
                ':en' => $en_text,
                ':jp' => $jp_text,
                ':romaji' => $romaji_text,
                ':id' => $page_id
            ]);
            echo json_encode(['success' => $result]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    if ($action === 'get_page_details') {
        $page_id = (int)($_POST['page_id'] ?? 0);
        if (!$page_id) {
            echo json_encode(['success' => false, 'error' => 'No page ID']);
            exit;
        }

        try {
            $stmt = $pdo->prepare('SELECT * FROM manga_pages WHERE id = :id');
            $stmt->execute([':id' => $page_id]);
            $page = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($page) {
                echo json_encode(['success' => true, 'page' => $page]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Page not found']);
            }
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manga Manager - Admin</title>
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin-style.css">
  <style>
    .content {
      padding: 28px;
    }

    .manga-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 28px;
    }

    .manga-item {
      background: var(--panel-bg);
      border-radius: 20px;
      overflow: hidden;
      cursor: pointer;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      border: 2px solid transparent;
    }

    .manga-item:hover {
      transform: translateY(-8px);
      box-shadow: 0 12px 30px rgba(0, 0, 0, 0.2);
      border-color: var(--accent-mint);
    }

    .manga-cover {
      width: 100%;
      height: 240px;
      background: var(--panel-inner);
      display: flex;
      align-items: center;
      justify-content: center;
      overflow: hidden;
    }

    .manga-cover img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .manga-info {
      padding: 16px;
    }

    .manga-title {
      font-size: 1.1rem;
      font-weight: 900;
      color: white;
      margin-bottom: 8px;
    }

    .manga-actions {
      display: flex;
      gap: 8px;
      margin-top: 12px;
    }

    .btn-icon {
      flex: 1;
      padding: 8px 12px;
      border: none;
      border-radius: 6px;
      color: white;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.85rem;
      transition: all 0.3s ease;
    }

    .btn-edit {
      background: var(--accent-mint);
      color: #244850;
    }

    .btn-edit:hover {
      transform: scale(1.05);
    }

    .btn-delete {
      background: #e74c3c;
    }

    .btn-delete:hover {
      background: #c0392b;
    }

    .add-manga-btn {
      background: var(--accent-mint);
      color: #244850;
      padding: 16px 32px;
      border: none;
      border-radius: 12px;
      font-size: 1.1rem;
      font-weight: 900;
      cursor: pointer;
      margin-bottom: 28px;
      transition: all 0.3s ease;
    }

    .add-manga-btn:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(175, 230, 210, 0.3);
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.7);
      z-index: 2000;
      align-items: center;
      justify-content: center;
    }

    .modal.active {
      display: flex;
    }

    .modal-content {
      background: var(--panel-bg);
      border-radius: 24px;
      padding: 32px;
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
    }

    .modal-content h2 {
      color: white;
      font-size: 1.8rem;
      margin-bottom: 24px;
      border-bottom: 2px solid var(--accent-mint);
      padding-bottom: 16px;
    }

    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-weight: 700;
      color: #e7fbf7;
      margin-bottom: 8px;
      font-size: 0.95rem;
    }

    .form-group input,
    .form-group textarea,
    .form-group select {
      width: 100%;
      padding: 12px;
      background: var(--panel-inner);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      color: white;
      font-family: inherit;
      box-sizing: border-box;
    }

    .form-group textarea {
      min-height: 100px;
      resize: vertical;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus {
      outline: none;
      border-color: var(--accent-mint);
      box-shadow: 0 0 0 3px rgba(175, 230, 210, 0.2);
    }

    .modal-buttons {
      display: flex;
      gap: 12px;
      justify-content: flex-end;
      margin-top: 24px;
    }

    .btn-submit {
      background: var(--accent-mint);
      color: #244850;
      padding: 12px 28px;
      border: none;
      border-radius: 8px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(175, 230, 210, 0.3);
    }

    .btn-cancel {
      background: rgba(255, 255, 255, 0.1);
      color: white;
      padding: 12px 28px;
      border: none;
      border-radius: 8px;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-cancel:hover {
      background: rgba(255, 255, 255, 0.2);
    }

    .pages-section {
      margin-top: 24px;
      padding-top: 24px;
      border-top: 2px solid rgba(255, 255, 255, 0.1);
    }

    .pages-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 12px;
      margin-top: 16px;
    }

    .page-item {
      background: var(--panel-inner);
      border-radius: 12px;
      padding: 12px;
      text-align: center;
      position: relative;
    }

    .page-image {
      width: 100%;
      height: 120px;
      background: rgba(0, 0, 0, 0.3);
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 8px;
      overflow: hidden;
    }

    .page-image img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .page-num {
      font-size: 0.85rem;
      color: #e7fbf7;
      margin-bottom: 8px;
    }

    .btn-remove-page {
      background: #e74c3c;
      color: white;
      border: none;
      padding: 6px 10px;
      border-radius: 4px;
      font-size: 0.75rem;
      cursor: pointer;
      width: 100%;
    }

    .btn-remove-page:hover {
      background: #c0392b;
    }

    .add-page-btn {
      background: var(--accent-mint);
      color: #244850;
      border: none;
      padding: 12px;
      border-radius: 8px;
      font-weight: 700;
      cursor: pointer;
      width: 100%;
      margin-top: 12px;
    }

    .add-page-btn:hover {
      opacity: 0.9;
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
        <h1 class="topbar-title">Manga Manager</h1>
        <p class="topbar-subtitle">Create and edit manga stories with full page content</p>
      </div>
      <div class="topbar-right">
        <img src="../images/exit.png" id="exitBtn" alt="Exit" title="Logout">
        <img src="../images/setting.png" id="settingsBtn" alt="Settings" title="Settings">
        <img src="../images/profile.png" id="profileBtn" alt="Profile" title="Profile">
      </div>
    </div>

    <div class="content">
      <button class="add-manga-btn" onclick="openCreateMangaModal()">+ Create New Manga</button>

      <div id="successMessage" class="success-message">Operation completed successfully!</div>

      <div class="manga-list">
        <?php foreach ($manga_list as $manga): ?>
        <div class="manga-item" data-manga-id="<?= $manga['id'] ?>">
          <div class="manga-cover">
            <?php if ($manga['cover_image']): ?>
              <img src="../uploads/manga/<?= htmlspecialchars($manga['cover_image']) ?>" alt="<?= htmlspecialchars($manga['title']) ?>">
            <?php else: ?>
              <div style="color: #999;">No Cover</div>
            <?php endif; ?>
          </div>
          <div class="manga-info">
            <div class="manga-title"><?= htmlspecialchars($manga['title']) ?></div>
            <div style="display: none;" data-description="<?= htmlspecialchars($manga['description'] ?? '') ?>"></div>
            <div class="manga-actions">
              <button class="btn-icon btn-edit" onclick="openEditModal(<?= $manga['id'] ?>, '<?= htmlspecialchars(addslashes($manga['title'])) ?>')">Edit</button>
              <button class="btn-icon btn-delete" onclick="deleteManga(<?= $manga['id'] ?>)">Delete</button>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </main>

  <!-- Create Manga Modal -->
  <div id="createModal" class="modal">
    <div class="modal-content">
      <h2>Create New Manga</h2>
      <div class="form-group">
        <label for="newMangaTitle">Title</label>
        <input type="text" id="newMangaTitle" placeholder="Enter manga title">
      </div>
      <div class="form-group">
        <label for="newMangaDescription">Description</label>
        <textarea id="newMangaDescription" placeholder="Enter manga description"></textarea>
      </div>
      <div class="form-group">
        <label for="newMangaCover">Cover Image</label>
        <input type="file" id="newMangaCover" accept="image/*">
      </div>
      <div class="modal-buttons">
        <button class="btn-cancel" onclick="closeCreateModal()">Cancel</button>
        <button class="btn-submit" onclick="createManga()">Create Manga</button>
      </div>
    </div>
  </div>

  <!-- Edit Manga Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <h2>Edit Manga & Pages</h2>
      <div class="form-group">
        <label for="editMangaTitle">Title</label>
        <input type="text" id="editMangaTitle" placeholder="Manga title">
      </div>
      <div class="form-group">
        <label for="editMangaDescription">Description</label>
        <textarea id="editMangaDescription" placeholder="Manga description"></textarea>
      </div>
      <div class="form-group">
        <label for="editMangaCover">Cover Image</label>
        <div id="currentCoverPreview" style="margin-bottom: 12px; border-radius: 8px; overflow: hidden; background: var(--panel-inner); height: 160px; display: flex; align-items: center; justify-content: center;">
          <img id="coverImg" src="" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">
        </div>
        <input type="file" id="editMangaCover" accept="image/*">
      </div>

      <div class="pages-section">
        <h3 style="color: white; margin-top: 0;">Manga Pages</h3>
        <div id="pagesContainer" class="pages-grid"></div>
        <button class="add-page-btn" onclick="openAddPageModal()">+ Add Page</button>
      </div>

      <div class="modal-buttons">
        <button class="btn-cancel" onclick="closeEditModal()">Close</button>
        <button class="btn-submit" onclick="saveEditedManga()">Save Changes</button>
      </div>
    </div>
  </div>

  <!-- Add Page Modal -->
  <div id="addPageModal" class="modal">
    <div class="modal-content">
      <h2>Add New Page</h2>
      <div class="form-group">
        <label for="pageNumber">Page Number</label>
        <input type="number" id="pageNumber" min="1" placeholder="1">
      </div>
      <div class="form-group">
        <label for="pageImage">Page Image</label>
        <input type="file" id="pageImage" accept="image/*">
      </div>
      <div class="form-group">
        <label for="pageEnglish">English Text</label>
        <textarea id="pageEnglish" placeholder="English translation of the page"></textarea>
      </div>
      <div class="form-group">
        <label for="pageJapanese">Japanese Text</label>
        <input type="text" id="pageJapanese" placeholder="Japanese (こんにちは)">
      </div>
      <div class="form-group">
        <label for="pageRomaji">Romaji Text</label>
        <input type="text" id="pageRomaji" placeholder="Romaji (Konnichiha)">
      </div>
      <div class="modal-buttons">
        <button class="btn-cancel" onclick="closeAddPageModal()">Cancel</button>
        <button class="btn-submit" onclick="addPage()">Add Page</button>
      </div>
    </div>
  </div>

  <!-- Edit Page Modal -->
  <div id="editPageModal" class="modal">
    <div class="modal-content">
      <h2>Edit Page Content</h2>
      <div class="form-group">
        <label for="editPageNumber">Page Number (Read-Only)</label>
        <input type="text" id="editPageNumber" readonly style="opacity: 0.6;">
      </div>
      <div class="form-group">
        <label>Page Image Preview</label>
        <div id="editPageImagePreview" style="width:100%; height:180px; background:#333; border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; margin-bottom:12px;">
          <img id="editPageImg" src="" alt="Page" style="max-width:100%; max-height:100%; object-fit:contain;">
        </div>
      </div>
      <div class="form-group">
        <label for="editPageEnglish">English Text</label>
        <textarea id="editPageEnglish" placeholder="English translation"></textarea>
      </div>
      <div class="form-group">
        <label for="editPageJapanese">Japanese Text</label>
        <input type="text" id="editPageJapanese" placeholder="Japanese text">
      </div>
      <div class="form-group">
        <label for="editPageRomaji">Romaji Text</label>
        <input type="text" id="editPageRomaji" placeholder="Romaji">
      </div>
      <div class="modal-buttons">
        <button class="btn-cancel" onclick="closeEditPageModal()">Cancel</button>
        <button class="btn-submit" onclick="savePageChanges()">Save Changes</button>
      </div>
    </div>
  </div>

  <script>
    let currentMangaId = null;

    function showSuccess() {
      const msg = document.getElementById('successMessage');
      msg.style.display = 'block';
      setTimeout(() => {
        msg.style.display = 'none';
      }, 3000);
    }

    function openCreateMangaModal() {
      document.getElementById('createModal').classList.add('active');
    }

    function closeCreateModal() {
      document.getElementById('createModal').classList.remove('active');
      document.getElementById('newMangaTitle').value = '';
      document.getElementById('newMangaDescription').value = '';
      document.getElementById('newMangaCover').value = '';
    }

    function createManga() {
      const title = document.getElementById('newMangaTitle').value.trim();
      const description = document.getElementById('newMangaDescription').value.trim();
      const coverFile = document.getElementById('newMangaCover').files[0];

      if (!title || !coverFile) {
        alert('Please enter title and select cover image');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'create_manga');
      formData.append('title', title);
      formData.append('description', description);
      formData.append('cover_image', coverFile);

      fetch('manga.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          closeCreateModal();
          showSuccess();
          setTimeout(() => location.reload(), 1000);
        } else {
          alert('Failed to create manga');
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Error creating manga');
      });
    }

    function openEditModal(mangaId, title) {
      currentMangaId = mangaId;
      document.getElementById('editMangaTitle').value = title;
      document.getElementById('editMangaDescription').value = '';
      document.getElementById('editMangaCover').value = '';
      document.getElementById('editModal').classList.add('active');
      loadMangaPages(mangaId);
      loadMangaDetails(mangaId);
    }

    function closeEditModal() {
      document.getElementById('editModal').classList.remove('active');
      currentMangaId = null;
    }

    function loadMangaDetails(mangaId) {
      // Fetch manga details to populate fields
      fetch('manga.php', {
        method: 'POST',
        body: new URLSearchParams({
          'action': 'get_manga_pages',
          'manga_id': mangaId
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          // Get the manga item from the DOM to extract details
          const mangaItem = document.querySelector(`[data-manga-id="${mangaId}"]`);
          if (mangaItem) {
            const titleEl = mangaItem.querySelector('.manga-title');
            const descEl = mangaItem.querySelector('[data-description]');
            const coverImg = mangaItem.querySelector('.manga-cover img');
            
            if (titleEl) {
              document.getElementById('editMangaTitle').value = titleEl.textContent;
            }
            if (descEl) {
              document.getElementById('editMangaDescription').value = descEl.getAttribute('data-description');
            }
            if (coverImg) {
              document.getElementById('coverImg').src = coverImg.src;
            }
          }
        }
      })
      .catch(err => console.error('Error loading manga:', err));
    }

    function loadMangaPages(mangaId) {
      fetch('manga.php', {
        method: 'POST',
        body: new URLSearchParams({
          'action': 'get_manga_pages',
          'manga_id': mangaId
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success && data.pages && data.pages.length > 0) {
          const container = document.getElementById('pagesContainer');
          container.innerHTML = data.pages.map(page => `
            <div class="page-item">
              <div class="page-image">
                <img src="../uploads/manga-pages/${encodeURIComponent(page.page_image)}" alt="Page ${page.page_number}">
              </div>
              <div class="page-num">Page ${page.page_number}</div>
              <button class="btn-remove-page" onclick="editPage(${page.id})">Edit</button>
              <button class="btn-remove-page" style="background:#e74c3c; margin-top:4px;" onclick="deletePageConfirm(${page.id}, ${mangaId})">Delete</button>
            </div>
          `).join('');
        } else {
          document.getElementById('pagesContainer').innerHTML = '<p style="color: #e7fbf7; grid-column: 1/-1;">No pages yet. Add one!</p>';
        }
      })
      .catch(err => {
        console.error('Error loading pages:', err);
        document.getElementById('pagesContainer').innerHTML = '<p style="color: #e7fbf7; grid-column: 1/-1;">Error loading pages</p>';
      });
    }

    function saveEditedManga() {
      const title = document.getElementById('editMangaTitle').value.trim();
      const description = document.getElementById('editMangaDescription').value.trim();
      const coverFile = document.getElementById('editMangaCover').files[0];

      if (!title) {
        alert('Please enter a title');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'update_manga');
      formData.append('manga_id', currentMangaId);
      formData.append('title', title);
      formData.append('description', description);
      if (coverFile) {
        formData.append('cover_image', coverFile);
      }

      fetch('manga.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          closeEditModal();
          showSuccess();
          setTimeout(() => location.reload(), 1000);
        } else {
          alert('Failed to update manga: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Error updating manga');
      });
    }

    function deletePageConfirm(pageId, mangaId) {
      if (!confirm('Delete this page?')) return;

      const formData = new FormData();
      formData.append('action', 'delete_page');
      formData.append('page_id', pageId);

      fetch('manga.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          loadMangaPages(mangaId);
        } else {
          alert('Failed to delete page');
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Error deleting page');
      });
    }

    let currentPageId = null;

    function editPage(pageId) {
      currentPageId = pageId;
      
      fetch('manga.php', {
        method: 'POST',
        body: new URLSearchParams({
          'action': 'get_page_details',
          'page_id': pageId
        })
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          const page = data.page;
          document.getElementById('editPageNumber').value = `Page ${page.page_number}`;
          document.getElementById('editPageImg').src = `../uploads/manga-pages/${encodeURIComponent(page.page_image)}`;
          document.getElementById('editPageEnglish').value = page.en_text || '';
          document.getElementById('editPageJapanese').value = page.jp_text || '';
          document.getElementById('editPageRomaji').value = page.romaji_text || '';
          document.getElementById('editPageModal').classList.add('active');
        } else {
          alert('Failed to load page details');
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Error loading page');
      });
    }

    function closeEditPageModal() {
      document.getElementById('editPageModal').classList.remove('active');
      currentPageId = null;
    }

    function savePageChanges() {
      const en = document.getElementById('editPageEnglish').value.trim();
      const jp = document.getElementById('editPageJapanese').value.trim();
      const romaji = document.getElementById('editPageRomaji').value.trim();

      if (!en) {
        alert('English text is required');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'update_page');
      formData.append('page_id', currentPageId);
      formData.append('en_text', en);
      formData.append('jp_text', jp);
      formData.append('romaji_text', romaji);

      fetch('manga.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          closeEditPageModal();
          showSuccess();
          loadMangaPages(currentMangaId);
        } else {
          alert('Failed to save changes: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Error saving page');
      });
    }

    function openAddPageModal() {
      if (!currentMangaId) {
        alert('Select a manga first');
        return;
      }
      document.getElementById('addPageModal').classList.add('active');
    }

    function closeAddPageModal() {
      document.getElementById('addPageModal').classList.remove('active');
      document.getElementById('pageNumber').value = '';
      document.getElementById('pageImage').value = '';
      document.getElementById('pageEnglish').value = '';
      document.getElementById('pageJapanese').value = '';
      document.getElementById('pageRomaji').value = '';
    }

    function addPage() {
      const pageNum = document.getElementById('pageNumber').value.trim();
      const pageFile = document.getElementById('pageImage').files[0];
      const enText = document.getElementById('pageEnglish').value.trim();
      const jpText = document.getElementById('pageJapanese').value.trim();
      const romajiText = document.getElementById('pageRomaji').value.trim();

      if (!pageNum || !pageFile || !enText) {
        alert('Please fill all required fields');
        return;
      }

      const formData = new FormData();
      formData.append('action', 'add_page');
      formData.append('manga_id', currentMangaId);
      formData.append('page_number', pageNum);
      formData.append('en_text', enText);
      formData.append('jp_text', jpText);
      formData.append('romaji_text', romajiText);
      formData.append('page_image', pageFile);

      fetch('manga.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          closeAddPageModal();
          showSuccess();
          loadMangaPages(currentMangaId);
        } else {
          alert('Failed to add page: ' + (data.error || 'Unknown error'));
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Error adding page');
      });
    }

    function deleteManga(mangaId) {
      if (!confirm('Delete this manga and all its pages?')) return;

      const formData = new FormData();
      formData.append('action', 'delete_manga');
      formData.append('manga_id', mangaId);

      fetch('manga.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          showSuccess();
          setTimeout(() => location.reload(), 1000);
        } else {
          alert('Failed to delete manga');
        }
      })
      .catch(err => {
        console.error('Error:', err);
        alert('Error deleting manga');
      });
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
