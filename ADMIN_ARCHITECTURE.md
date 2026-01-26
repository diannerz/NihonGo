# Admin System - Visual Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        NihonGo Admin System                       │
└─────────────────────────────────────────────────────────────────┘

                            ┌──────────────┐
                            │ Admin Login  │
                            │  (existing)  │
                            └──────┬───────┘
                                   │
                                   ▼
                    ┌──────────────────────────┐
                    │   Admin Dashboard        │
                    │  (donation reports)      │
                    └──────────────────────────┘
                              │
                    ┌─────────┼─────────┐
                    │         │         │
                    ▼         ▼         ▼
            ┌─────────────┐ ┌────────────────┐ ┌────────────┐
            │   Kana      │ │                │ │  Manga     │
            │  Charts     │ │                │ │ Manager    │
            └──────┬──────┘ │ SIDEBAR MENU   │ └──────┬─────┘
                   │        │                │        │
                   ▼        │                │        ▼
            ┌─────────────────────┐   ┌────────────────────────┐
            │ Kana Flashcard      │   │ Manga CRUD             │
            │ Editor              │   │ - Create manga         │
            │ - Mnemonic          │   │ - Upload cover         │
            │ - Description       │   │ - Add pages            │
            │ - Vocabulary        │   │ - Upload panel images  │
            │ - Prev/Next nav     │   │ - Add 3 language text  │
            └─────────────────────┘   │ - Delete pages         │
                                      │ - Delete manga         │
                                      └────────────────────────┘
```

## File Structure

```
/admin/
│
├── dashboard.php                 ← MAIN ADMIN HOME
│   ├── Shows donation analytics
│   ├── Summary cards
│   └── Detailed donation table
│
├── kana-charts.php              ← KANA GRID VIEWER
│   ├── Hiragana chart (6×grid)
│   ├── Katakana chart (6×grid)
│   └── Click → kana-flashcards.php
│
├── kana-flashcards.php          ← KANA EDITOR (NEW)
│   ├── Display kana character
│   ├── Edit mnemonic
│   ├── Edit description
│   ├── Edit vocabulary (JP/Romaji/EN)
│   ├── Save via AJAX
│   └── Previous/Next navigation
│
├── manga.php                    ← MANGA MANAGER (REWRITE)
│   ├── List all manga
│   ├── Create new manga modal
│   │   ├── Title
│   │   ├── Description
│   │   └── Cover image upload
│   ├── Edit manga modal
│   │   ├── Pages grid
│   │   ├── Add page modal
│   │   │   ├── Page number
│   │   │   ├── Panel image upload
│   │   │   ├── English text
│   │   │   ├── Japanese text
│   │   │   └── Romaji text
│   │   └── Delete page buttons
│   └── Delete manga
│
├── sidebar.php                  ← REUSABLE NAVIGATION
│   ├── Dashboard link
│   ├── Kana Charts link
│   ├── Manga Manager link
│   └── Active page highlighting
│
├── admin-functions.php          ← HELPER FUNCTIONS
│   ├── is_admin()
│   ├── require_admin()
│   ├── get_donation_reports()
│   ├── get_all_kana()
│   ├── get_kana_by_id()  ← NEW
│   ├── get_all_manga()
│   ├── create_manga()
│   ├── add_manga_page()
│   ├── delete_manga_page()
│   └── handle_file_upload()
│
└── admin-style.css              ← COMPLETE STYLING
    ├── Topbar layout
    ├── Sidebar styling
    ├── Panel & card styles
    ├── Form styling
    ├── Modal styling
    ├── Kana grid styling
    └── Manga grid styling

/uploads/
├── manga/          ← Cover images
│   ├── 1735240156_abc123.png
│   └── ...
└── manga-pages/    ← Panel images
    ├── 1735240158_def456.png
    └── ...
```

## Data Flow Diagram

```
Admin User
    │
    ├─────────► Dashboard
    │              │
    │              └─► Load donations from DB
    │                  └─► Display reports & analytics
    │
    ├─────────► Kana Charts
    │              │
    │              ├─► Load all kana from DB
    │              ├─► Display hiragana grid
    │              ├─► Display katakana grid
    │              │
    │              └─► Click Kana
    │                  │
    │                  └──────────────┐
    │                                  │
    │                                  ▼
    │                     ┌──────────────────────┐
    │                     │ Kana Flashcard       │
    │                     │ Editor               │
    │                     │                      │
    │                     │ 1. Load kana by ID   │
    │                     │ 2. Display fields    │
    │                     │ 3. Edit form         │
    │                     │ 4. Save via AJAX     │
    │                     │ 5. Prev/Next nav     │
    │                     └──────────────────────┘
    │
    └─────────► Manga Manager
                   │
                   ├─► View all manga
                   │   │
                   │   └─► Create New
                   │       ├─ Title input
                   │       ├─ Description
                   │       ├─ Cover upload
                   │       └─ Create in DB
                   │
                   └─► Edit Manga
                       │
                       ├─ View pages grid
                       │
                       ├─► Add Page
                       │   ├─ Page number
                       │   ├─ Image upload
                       │   ├─ EN text input
                       │   ├─ JP text input
                       │   ├─ Romaji input
                       │   └─ Save to DB
                       │
                       └─► Delete Page
                           └─ Remove from DB

```

## Database Schema

```
Database: nihongo_db

┌──────────────────────────┐
│    kana_flashcards       │
├──────────────────────────┤
│ id (PK)                  │
│ kana_char                │ ← "あ"
│ romaji                   │ ← "a"
│ kana_type                │ ← "hiragana"
│ mnemonic      (NEW)      │ ← "Fish stabbed..."
│ description   (NEW)      │ ← "First hiragana..."
│ vocab_jp      (NEW)      │ ← "あめ"
│ vocab_romaji  (NEW)      │ ← "ame"
│ vocab_eng     (NEW)      │ ← "candy/rain"
│ created_at                │
│ updated_at                │
└──────────────────────────┘

┌──────────────────────────┐
│       manga              │ (NEW TABLE)
├──────────────────────────┤
│ id (PK)                  │
│ title                    │ ← "An Egg's Tale"
│ description              │ ← "Whimsical story..."
│ cover_image              │ ← "cover_123.png"
│ created_at               │
└──────────────────────────┘
            │
            │ 1:Many
            │
            ▼
┌──────────────────────────┐
│    manga_pages           │ (NEW TABLE)
├──────────────────────────┤
│ id (PK)                  │
│ manga_id (FK)            │ ← Links to manga
│ page_number              │ ← 1, 2, 3...
│ page_image               │ ← "page_123.png"
│ en_text                  │ ← "One day..."
│ jp_text                  │ ← "あるひ..."
│ romaji_text              │ ← "Aru hi..."
└──────────────────────────┘
```

## User Interactions

### Kana Editing Flow
```
Click "Kana Charts"
        │
        ▼
View Hiragana/Katakana grids
        │
        ├─► Click "あ"
        │       │
        │       ▼
        │   Edit modal opens
        │   ├─ Mnemonic field
        │   ├─ Description field
        │   ├─ Vocab fields
        │   │
        │   ├─► Edit field
        │   │   │
        │   │   ▼
        │   │ Click "Save Changes"
        │   │   │
        │   │   ▼
        │   │ "Changes saved!" message
        │   │
        │   └─► Click "Next Kana"
        │       │
        │       ▼
        │   Edit "い"
        │   (loop)
        │
        └─► Click "Cancel"
            │
            ▼
        Return to charts
```

### Manga Management Flow
```
Click "Manga Manager"
        │
        ▼
View all manga
        │
        ├─► Click "+ Create New"
        │       │
        │       ▼
        │   Modal: Enter title, description, upload cover
        │       │
        │       ▼
        │   Click "Create Manga"
        │       │
        │       ▼
        │   New manga in list
        │
        └─► Click "Edit"
            │
            ▼
        Modal: View pages + Add Page button
            │
            ├─► Click "+ Add Page"
            │       │
            │       ▼
            │   Modal: Enter page data
            │   ├─ Page number
            │   ├─ Upload image
            │   ├─ EN text
            │   ├─ JP text
            │   ├─ Romaji text
            │   │
            │   ▼
            │   Click "Add Page"
            │       │
            │       ▼
            │   Page in grid below
            │       │
            │       ▼
            │   Repeat for pages 2, 3, etc.
            │
            └─► Click delete on page
                    │
                    ▼
                Page removed
```

## API Endpoints

### Admin Pages (Protected with `require_admin()`)
- `GET /admin/dashboard.php` - Dashboard
- `GET /admin/kana-charts.php` - Kana charts
- `GET /admin/kana-flashcards.php?id=1&type=hiragana` - Edit kana
- `GET /admin/manga.php` - Manga manager

### AJAX Requests (POST)
```javascript
// Save flashcard
POST /admin/kana-flashcards.php
Body: {
  action: "save_flashcard",
  mnemonic: "...",
  description: "...",
  vocab_jp: "...",
  vocab_romaji: "...",
  vocab_eng: "..."
}

// Create manga
POST /admin/manga.php
Body: {
  action: "create_manga",
  title: "...",
  description: "...",
  cover_image: [File]
}

// Add page
POST /admin/manga.php
Body: {
  action: "add_page",
  manga_id: 1,
  page_number: 1,
  en_text: "...",
  jp_text: "...",
  romaji_text: "...",
  page_image: [File]
}

// Delete manga
POST /admin/manga.php
Body: {
  action: "delete_manga",
  manga_id: 1
}
```

## Security Measures

```
┌─────────────────────────────────────────┐
│ SECURITY CHECKS                         │
├─────────────────────────────────────────┤
│ 1. require_admin()                      │
│    └─ Check $_SESSION['role'] == 'admin'│
│    └─ Redirect to dashboard if not admin│
│                                         │
│ 2. HTML Escaping                        │
│    └─ htmlspecialchars() on outputs     │
│    └─ Prevents XSS attacks              │
│                                         │
│ 3. Prepared Statements                  │
│    └─ PDO prepared statements           │
│    └─ Prevents SQL injection            │
│                                         │
│ 4. File Uploads                         │
│    └─ Validate MIME types               │
│    └─ Sanitize filenames                │
│    └─ Use uniqid() for random names     │
│    └─ Store outside webroot             │
│                                         │
│ 5. Input Validation                     │
│    └─ Type checking (int, string)       │
│    └─ Length validation                 │
│    └─ Required field checks              │
└─────────────────────────────────────────┘
```

---

This is your complete admin system architecture!
