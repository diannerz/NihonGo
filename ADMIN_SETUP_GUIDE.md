# 🎌 NihonGo Admin Side - Complete Setup Guide

## ✅ Everything Created Successfully!

You now have a **complete admin system** for your NihonGo application.

---

## 📁 Files Created

1. **php/check_auth.php** (MODIFIED)
   - Now fetches `role` field from users table
   - Includes role in user object throughout the app

2. **admin/dashboard.php**
   - Admin home page with donation reports
   - Shows total donations, donors, and donations by feature
   - Displays detailed donation table

3. **admin/kana-charts.php**
   - Manage kana flashcard descriptions
   - Click any kana to edit its description
   - Changes auto-sync to database

4. **admin/manga.php**
   - Full CRUD for manga management
   - Create: Add new manga with cover image
   - Read: View all manga in grid layout
   - Update: Edit title, description, cover image
   - Delete: Remove manga from database

5. **admin/admin-functions.php**
   - Helper functions for all admin operations
   - Database queries for donations, kana, manga
   - File upload handling

6. **admin/admin-style.css**
   - Admin-specific styling
   - Matches main design language
   - Responsive tables, modals, grids

7. **uploads/manga/** & **uploads/manga-pages/**
   - Directories for manga cover images and pages

---

## 🔑 Login Credentials (Admin)

Use this account to test admin features:

```
Username: afino
Password: afino123
Email: afino@gmail.com
```

**This user is already set as admin in the database!**

---

## 🧭 How to Access Admin Pages

When **admin logs in**, the sidebar will show:

1. **Kana Charts** - Edit flashcard descriptions
2. **Manga** - Manage manga (Create, Edit, Delete)

When **regular users login**, they see the normal sidebar:

1. Kana Charts (view-only)
2. Manga (view-only)
3. Dictionary
4. Daily Challenges
5. My Progress
6. Donation

---

## 📊 Admin Features Explained

### **1. Dashboard** (`admin/dashboard.php`)
- **Summary Cards**: Total donations ($), total donors (#), total donations made (#)
- **Donations by Feature**: Shows breakdown of donations by category
- **Detailed Report**: Full table of all donations with username, feature, amount, date

### **2. Kana Management** (`admin/kana-charts.php`)
- **Click any kana** (hiragana or katakana) to open edit modal
- **Edit the description** field for each kana
- **Save** - updates database immediately
- Changes appear to all users next time they visit kana-charts

### **3. Manga Management** (`admin/manga.php`)
- **Add New Manga**: 
  - Title (required)
  - Description (optional)
  - Cover Image (required)
  - Automatically uploaded to `/uploads/manga/`

- **Edit Manga**:
  - Change title and description
  - Replace cover image
  - All changes save immediately

- **Delete Manga**:
  - Confirmation dialog prevents accidental deletion
  - Cascades delete (removes all manga pages too)

---

## 🗄️ Database Structure

### **Users Table (NEW COLUMN)**
```sql
ALTER TABLE `users` ADD COLUMN `role` VARCHAR(20) DEFAULT 'user';
-- 'user' or 'admin'
```

### **Donations Table (NEW COLUMN)**
```sql
ALTER TABLE `donations` ADD COLUMN `feature_name` VARCHAR(100) DEFAULT 'General';
-- Tracks which feature was donated to
```

### **Kana Flashcards Table (NEW)**
```sql
CREATE TABLE `kana_flashcards` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kana_type` VARCHAR(20),           -- 'hiragana' or 'katakana'
  `kana_char` VARCHAR(10),           -- The kana character
  `romaji` VARCHAR(50),              -- Romanization
  `description` TEXT,                -- Editable description
  `image_file` VARCHAR(255),         -- Not used in current version
  `audio_file` VARCHAR(255),         -- Not used in current version
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_kana` (`kana_type`, `kana_char`)
);
```

### **Manga Table (NEW)**
```sql
CREATE TABLE `manga` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `cover_image` VARCHAR(255),        -- Filename stored in /uploads/manga/
  `chapter_count` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### **Manga Pages Table (NEW)**
```sql
CREATE TABLE `manga_pages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `manga_id` INT NOT NULL,
  `page_number` INT,
  `page_image` VARCHAR(255),         -- Filename in /uploads/manga-pages/
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`manga_id`) REFERENCES `manga` (`id`) ON DELETE CASCADE,
  UNIQUE KEY `unique_manga_page` (`manga_id`, `page_number`)
);
```

---

## 🧪 Testing Checklist

- [ ] **Login as admin** (afino account)
- [ ] **Visit admin/dashboard.php** - See donation reports
- [ ] **Visit admin/kana-charts.php** - Click a kana to edit description
- [ ] **Save description** - Verify it updates
- [ ] **Visit admin/manga.php** - Create new manga with cover image
- [ ] **Edit manga** - Change title and description
- [ ] **Delete manga** - Remove a manga
- [ ] **Login as regular user** - Verify sidebar is different
- [ ] **User sees kana changes** - Check if kana description updates appear
- [ ] **User sees new manga** - Verify manga appears on user side

---

## 🎯 Next Steps (Optional Enhancements)

1. **Manga Pages Management**: Add UI to upload individual manga pages
2. **Statistics Dashboard**: Show more detailed analytics (user growth, feature popularity)
3. **Content Moderation**: Ability to view/moderate user-generated content
4. **Backup System**: Database backup functionality
5. **Email Notifications**: Send emails when donations are received
6. **User Management**: Admin panel to manage user accounts (ban, promote, etc.)

---

## 📝 File Locations

```
NihonGo/
├── admin/
│   ├── dashboard.php          ✅ CREATED
│   ├── kana-charts.php        ✅ CREATED
│   ├── manga.php              ✅ CREATED
│   ├── admin-functions.php    ✅ CREATED
│   └── admin-style.css        ✅ CREATED
├── php/
│   └── check_auth.php         ✅ MODIFIED (added role)
├── uploads/
│   ├── manga/                 ✅ CREATED (for covers)
│   └── manga-pages/           ✅ CREATED (for pages)
├── styles.css                 (existing, no changes)
├── dashboard.php              (existing, no changes)
└── ... other files
```

---

## ⚠️ Important Notes

1. **File Uploads**: Make sure `/uploads/manga/` and `/uploads/manga-pages/` have write permissions
   ```bash
   chmod 755 uploads/manga/
   chmod 755 uploads/manga-pages/
   ```

2. **Session Management**: Admin role is stored in `$_SESSION['role']` and persists across pages

3. **Security**: All inputs are escaped with `htmlspecialchars()` to prevent XSS attacks

4. **Cascading Deletes**: Deleting a manga automatically deletes all its pages (via foreign key)

---

**Your admin system is now fully operational!** 🚀

Test it out by logging in with the admin account and exploring all the features.
