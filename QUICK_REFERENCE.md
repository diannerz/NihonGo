# Quick Reference: Admin-to-User Content Flow

## The Complete System Now Works Like This:

### 1️⃣ ADMIN CREATES MANGA
**Go to**: `http://localhost/NihonGo/admin/dashboard.php`
- Click "Manga Manager" in sidebar
- Click "Create New Manga"
- Fill in: Title, Description
- Upload a cover image
- Save manga

### 2️⃣ ADMIN ADDS PAGES TO MANGA
**In Manga Manager**:
- Find your manga in the list
- Click "Edit" or "View Pages"
- Click "Add Page"
- Upload page image
- Fill in translations:
  - **English (en_text)**: English translation of the page
  - **Japanese (jp_text)**: Original Japanese text
  - **Romaji (romaji_text)**: Phonetic romanization
- Save page

### 3️⃣ USER SEES THE MANGA
**User navigates to**: `http://localhost/NihonGo/media.php`
- User sees your manga title and description
- User can read the story page by page
- Each page shows:
  - **Left side**: English text (large), Japanese text (green), Romaji text (gray)
  - **Right side**: Page image
  - **Navigation**: < > buttons to move between pages
  - **Progress dots**: Shows current page position (● = current, ○ = other)

### 4️⃣ USER NAVIGATION
- **< > buttons (outer)**: Switch between different manga stories
- **< > buttons (inner)**: Navigate pages within current manga
- **Progress dots**: Visual indication of where user is

---

## EXAMPLE WORKFLOW

### Admin Steps:
```
1. Admin logs in → admin/dashboard.php
2. Click "Manga Manager"
3. Click "Create New Manga"
4. Title: "An egg's tale"
5. Description: "A heartwarming story"
6. Upload cover.png
7. Click "Add Page"
   - Upload page1.png
   - English: "One day, an egg had fallen."
   - Japanese: "ある日、卵が落ちていました。"
   - Romaji: "Aru hi, tamago ga ochite imashita."
8. Save → Repeat for pages 2, 3, etc.
```

### User Sees:
```
media.php loads:
- Story: "An egg's tale"
- Page 1 of 3:
  Left:  "One day, an egg had fallen."
         "ある日、卵が落ちていました。"
         "Aru hi, tamago ga ochite imashita."
  Right: [page1.png image]
```

---

## KEY POINTS

✅ **Database-driven**: Everything stored in manga and manga_pages tables
✅ **Real-time sync**: Changes appear to users immediately
✅ **Responsive**: Works on desktop, tablet, mobile
✅ **Multi-language**: English, Japanese, and Romaji all displayed
✅ **Progress tracking**: User viewing statistics recorded

---

## TROUBLESHOOTING

| Issue | Solution |
|-------|----------|
| Manga not appearing | Check kana_charts.php shows characters - if empty, restart XAMPP |
| Images not loading | Verify images in `uploads/manga-pages/` directory exist |
| Text not displaying | Check en_text, jp_text, romaji_text columns filled in |
| Navigation broken | Clear browser cache (Ctrl+Shift+Del) |

---

## DATABASE TABLES

### manga
```
id              → Unique manga ID
title           → Story title
description     → Story description
cover_image     → Path to cover image
created_at      → Creation date/time
```

### manga_pages
```
id              → Unique page ID
manga_id        → Link to manga
page_number     → Page sequence (1, 2, 3, ...)
page_image      → Path to page image
en_text         → English translation
jp_text         → Japanese text
romaji_text     → Romaji transliteration
```

### kana_flashcards
```
id              → Unique kana ID
kana_char       → The kana character (ひ, カ, etc)
romaji          → Romanized version (hi, ka, etc)
kana_type       → hiragana or katakana
mnemonic        → Memory aid
description     → Detailed explanation
vocab_jp        → Japanese vocabulary example
vocab_romaji    → Romanized vocabulary
vocab_eng       → English translation of vocab
```

---

## ADMIN PAGES

| Page | URL | Purpose |
|------|-----|---------|
| Dashboard | `/admin/dashboard.php` | View stats, donations |
| Kana Charts | `/admin/kana-charts.php` | Edit hiragana/katakana |
| Kana Flashcards | `/admin/kana-flashcards.php?id=X` | Edit single kana details |
| Manga Manager | `/admin/manga.php` | Create/edit stories |

---

## USER PAGES

| Page | URL | Purpose |
|------|-----|---------|
| Media (Manga) | `/media.php` | Read manga stories |
| Dashboard | `/dashboard.php` | User home |
| Settings | `/settings.php` | User preferences |

---

**Last Updated**: 2024
**Status**: Production Ready ✅
