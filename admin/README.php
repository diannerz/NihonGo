<?php
/**
 * ADMIN SYSTEM OVERVIEW
 * 
 * This system provides complete admin functionality for NihonGo application.
 * 
 * FEATURES:
 * 1. Dashboard - View donation reports and analytics
 * 2. Kana Management - Edit flashcard descriptions
 * 3. Manga Management - Full CRUD operations
 * 
 * ACCESSING ADMIN PAGES:
 * - /admin/dashboard.php  → Dashboard with donation reports
 * - /admin/kana-charts.php → Edit kana descriptions
 * - /admin/manga.php → Manage manga
 * 
 * ADMIN USER:
 * - Username: afino
 * - Role: admin (already set in database)
 * 
 * SECURITY:
 * - All admin pages require admin role verification (require_admin() function)
 * - All inputs are escaped to prevent XSS attacks
 * - All database queries use prepared statements
 * - File uploads are validated and sanitized
 * 
 * DATABASE CHANGES:
 * 1. users.role → Added to track admin vs user
 * 2. donations.feature_name → Added to track donation source
 * 3. kana_flashcards → New table for flashcard management
 * 4. manga → New table for manga storage
 * 5. manga_pages → New table for individual manga pages
 * 
 * FILE STRUCTURE:
 * /admin/
 *   ├── dashboard.php          → Admin dashboard with reports
 *   ├── kana-charts.php        → Kana description management
 *   ├── manga.php              → Manga CRUD system
 *   ├── admin-functions.php    → Helper functions
 *   └── admin-style.css        → Admin styling
 * 
 * /uploads/
 *   ├── manga/                 → Manga cover images
 *   └── manga-pages/           → Manga page images
 * 
 * USAGE:
 * 1. Login with admin account (afino)
 * 2. Access /admin/dashboard.php to start
 * 3. Use sidebar links for other admin features
 * 
 * FUNCTIONS AVAILABLE IN admin-functions.php:
 * - is_admin()                           → Check if user is admin
 * - require_admin()                      → Redirect if not admin
 * - get_donation_reports()               → All donations with user info
 * - get_donation_summary()               → Donations grouped by feature
 * - get_all_kana()                       → All kana flashcards
 * - get_kana_by_type()                   → Filter by hiragana/katakana
 * - update_kana_description()            → Edit kana description
 * - get_all_manga()                      → All manga records
 * - get_manga_by_id()                    → Get single manga
 * - get_manga_pages()                    → Get manga pages
 * - create_manga()                       → Create new manga
 * - update_manga()                       → Edit manga
 * - delete_manga()                       → Remove manga
 * - add_manga_page()                     → Add page to manga
 * - delete_manga_page()                  → Remove page from manga
 * - handle_file_upload()                 → Sanitize and save uploads
 */

// If you're viewing this file in the browser, it means you've accessed
// the root of the admin system documentation. Visit /admin/dashboard.php instead!

if (php_sapi_name() !== 'cli') {
    header('Location: admin/dashboard.php');
    exit;
}
?>
