# NihonGo Platform: User vs. Admin Features

---

## 📊 Feature Comparison Overview

| Feature Category | Regular User | Admin |
|---|:---:|:---:|
| **Learning Activities** | ✓ | ✓ |
| **Progress Tracking** | ✓ | ✗ |
| **Donations** | ✓ | ✗ |
| **Profile Management** | ✓ | ✗ |
| **Content Creation/Editing** | ✗ | ✓ |
| **Analytics & Reporting** | ✗ | ✓ |
| **User Management** | ✗ | ✓ |

---

# 🎓 REGULAR USER FEATURES

## 1. **Dashboard & Progress Tracking**
**Location**: `/dashboard.php`

### Overview
The user dashboard provides a comprehensive summary of daily learning progress and long-term mastery achievements.

### Key Components:
- **Daily Activity Summary**
  - Tracks flashcard views (goal: 5 per day)
  - Monitors manga reading sessions (goal: 1 per day)
  - Records quiz completions (goal: 1 per day)
  - Visual progress bars showing percentage toward daily goals

- **Kana Mastery Progress**
  - Hiragana mastery: Visual progress indicator (0-46 characters)
  - Katakana mastery: Visual progress indicator (0-46 characters)
  - Only characters with mastery level 2 (fully mastered) are counted
  - Percentage-based display for easy visualization

- **Quick Navigation**
  - One-click access to all learning modules
  - Sidebar menu with intuitive icons and labels

---

## 2. **Kana Charts & Flashcards**
**Location**: `/kana-charts.php`

### Hiragana & Katakana Learning
Users can master the Japanese writing systems through interactive flashcards.

### Features:
- **Visual Character Display**
  - Complete grid of all hiragana characters (46 characters)
  - Complete grid of all katakana characters (46 characters)
  - Color-coded by mastery level:
    - Unmastered: Default color
    - Learning: Intermediate highlight
    - Mastered: Completed highlight

- **Interactive Flashcard System**
  - Click any character to view detailed flashcard
  - Displays:
    - The Japanese character (kana)
    - Romaji pronunciation
    - Example vocabulary words in Japanese and English
    - Memory mnemonic/story (created by admin)
  - Visual mnemonic descriptions to aid memory

- **Progress Tracking**
  - Current mastery level for each character
  - Ability to mark progress (track learning journey)
  - View example vocabulary in context

---

## 3. **Kana Quiz**
**Location**: `/kana-quiz.php`

### Interactive Learning through Quizzes
Test and reinforce kana knowledge through daily quiz challenges.

### Features:
- **Daily Quiz Generation**
  - Fresh quiz questions generated daily
  - Questions randomly selected from kana database
  - Includes both hiragana and katakana

- **Quiz Interface**
  - Multiple choice or input-based questions
  - Immediate feedback on answers
  - Score tracking for the day
  - Progress counting toward daily goal

- **Learning Reinforcement**
  - Tracks completed quizzes in daily progress
  - Helps users achieve daily learning targets
  - Identifies weak areas for focused study

---

## 4. **Manga Reading Library**
**Location**: `/media.php`

### Learn Japanese Through Stories
Immersive reading experience with multi-language support.

### Features:
- **Story Selection**
  - Browse available manga stories created by admin
  - One story displayed at a time with navigation between stories
  - Story titles clearly displayed

- **Page-by-Page Navigation**
  - View individual manga pages
  - Page progress indicators (dots) showing current position
  - Previous/Next page buttons for easy navigation
  - Previous/Next story buttons for browsing other manga

- **Multi-Language Text Support**
  - **English Translation**: Full English text for comprehension
  - **Japanese Text**: Original Japanese (hiragana/kanji/katakana mix)
  - **Romaji Text**: Phonetic romanization for pronunciation learning

- **Visual Learning**
  - Manga page images displayed in full context
  - Text positioned alongside images for reference
  - Responsive layout for desktop and mobile viewing

- **Progress Tracking**
  - Manga views tracked in daily progress
  - Helps users achieve daily reading goals
  - Encourages consistent engagement with content

---

## 5. **User Profile & Settings**
**Location**: `/settings.php`

### Personal Account Management

### Profile Management Features:
- **Profile Information**
  - Display name customization
  - Bio/About section for personal description
  - Avatar/Profile picture upload support
  - Username display

- **Donation Statistics**
  - Total donations made (lifetime count)
  - Total amount donated (currency display)
  - Last donation date and timestamp
  - Visual summary of support provided

- **Account Settings**
  - Update profile information
  - Change personal details
  - View account preferences
  - Edit bio and display settings

---

## 6. **Donation System**
**Location**: `/handle_donation.php` & `/process_donation.php`

### Support the Project
Users can contribute financially to support different features.

### Donation Features:
- **Feature-Based Donations**
  - Donate to support specific features
  - Choose which aspect of NihonGo to support:
    - Kana charts and flashcards
    - Manga library
    - Quiz system
    - Or general support

- **Donation Tracking**
  - All donations recorded with timestamp
  - Feature name associated with each donation
  - Amount tracked for personal records
  - Visible in user profile/settings

- **Donation History**
  - Users can see their donation history
  - Track total contributions
  - View donation dates and amounts

---

## 7. **Authentication & Account Security**
**Location**: `/php/login.php`, `/php/signup.php`, `/php/forgot_request.php`

### Secure Access Control

### Account Features:
- **User Registration**
  - Create new account with username and password
  - Email validation
  - Password strength requirements
  - Account activation (if configured)

- **Secure Login**
  - Login with username/email and password
  - Session management
  - Remember login option
  - Secure authentication tokens

- **Password Recovery**
  - Forgot password functionality
  - Email-based password reset
  - Secure reset token generation
  - Password reset form with validation

- **Session Management**
  - Persistent login sessions
  - Logout functionality
  - Session timeout for security
  - Multi-device session support

---

# 👨‍💼 ADMIN FEATURES

## 1. **Admin Dashboard**
**Location**: `/admin/dashboard.php`

### Central Admin Control Center
Comprehensive overview of platform activity and financial metrics.

### Key Features:
- **Donation Analytics**
  - **Total Donations**: Sum of all monetary contributions
  - **Total Donors**: Count of unique users who have donated
  - **Total Donation Count**: Total number of donation transactions

- **Feature-Based Donation Breakdown**
  - Revenue grouped by feature
  - Donation count per feature
  - Total amount raised per feature
  - Identifies which features are most valued by users
  - Helps prioritize development efforts

- **Detailed Donation Report**
  - Complete donation transaction history
  - Shows:
    - Username and display name of donor
    - Which feature was donated to
    - Donation amount
    - Donation date
  - Sortable and searchable table format
  - Chronologically ordered (newest first)

- **Dashboard Analytics**
  - Visualize funding trends
  - Track donor base growth
  - Monitor feature popularity via donations
  - Data-driven decision making

---

## 2. **Kana Flashcard Management**
**Location**: `/admin/kana-flashcards.php`

### Create & Edit Learning Content
Manage the foundational Japanese character learning system.

### Management Capabilities:
- **Create New Flashcards**
  - Add new hiragana characters
  - Add new katakana characters
  - Input character details:
    - Kana character itself
    - Romaji pronunciation
    - Example vocabulary words (Japanese)
    - English translations
    - Mnemonic descriptions (memory aids)

- **Edit Existing Flashcards**
  - Modify character information
  - Update memory mnemonics
  - Change example vocabulary
  - Update translations
  - Correct or improve descriptions

- **Organize by Type**
  - Separate hiragana content
  - Separate katakana content
  - Bulk operations on character sets
  - Type-specific management tools

- **Database Integration**
  - All changes immediately available to users
  - Content versioning with `updated_at` timestamps
  - Edit tracking for content management

---

## 3. **Kana Charts Admin View**
**Location**: `/admin/kana-charts.php`

### Visual Flashcard Management
See and edit all kana characters in grid format.

### Features:
- **Character Grid Display**
  - Hiragana characters in organized 6-column grid
  - Katakana characters in organized 6-column grid
  - All 46 characters per type displayed

- **Quick Edit Access**
  - Click any character to open editor
  - Modal or form-based editing
  - Direct access to flashcard editor
  - Visual selection of characters to modify

- **Content Overview**
  - See all available content at a glance
  - Identify missing or incomplete cards
  - Verify content is correctly stored
  - Bulk management capabilities

---

## 4. **Manga Content Management**
**Location**: `/admin/manga.php`

### Create & Manage Learning Stories
Build the manga library that users read daily.

### Manga CRUD Operations:
- **Create New Manga Stories**
  - Add story title
  - Set story description
  - Create initial metadata
  - Timestamp recording (created_at)

- **Edit Story Information**
  - Update story titles
  - Modify descriptions
  - Change story metadata
  - Update timestamps (updated_at)

- **Delete Stories**
  - Remove manga stories
  - Clean up associated pages
  - Archive or permanently delete

- **Page Management**
  - Add pages to manga stories
  - Set page number/sequence
  - Upload/attach manga page images
  - Assign page content

- **Multi-Language Content Creation**
  - English translation text
  - Japanese (native) text
  - Romaji phonetic version
  - All three languages stored per page

- **Page Image Management**
  - Upload manga page images
  - Images stored in `uploads/manga-pages/` directory
  - Image association with specific pages
  - Visual content for reading experience

- **Content Organization**
  - Order pages sequentially
  - Organize stories logically
  - Manage content structure
  - Track content metadata

---

## 5. **Admin Dashboard Navigation**
**Location**: `/admin/sidebar.php`

### Quick Access to Admin Tools
Intuitive navigation for all administrative functions.

### Navigation Features:
- **Main Admin Menu**
  - Dashboard (donations and analytics)
  - Kana Flashcards (create/edit kana)
  - Kana Charts (visual character management)
  - Manga Management (story and page creation)
  - Settings and Profile options

- **User Account Management**
  - Access admin profile
  - Admin settings
  - Account preferences
  - Logout functionality

---

## 6. **Admin Access Control**
**Location**: `/admin/admin-functions.php`

### Security & Permission Management

### Authorization System:
- **Role-Based Access Control**
  - `role = 'admin'` in user database
  - Users without admin role redirected to normal dashboard
  - Admin-only page protection with `require_admin()` function

- **Route Protection**
  - Admin pages check for admin role
  - Non-admin users cannot access admin panel
  - Automatic redirect to user dashboard for non-admins
  - Session-based authentication enforcement

- **Admin Authentication**
  - Admins must be logged in
  - Same login system as regular users
  - Admin status determined by user role in database
  - Session tokens validated on each admin page

---

# 🔐 ACCESS & SECURITY COMPARISON

## User Access Rights

| Action | User | Admin |
|--------|:----:|:-----:|
| View personal dashboard | ✓ | ✗ |
| Study kana with flashcards | ✓ | ✓ |
| Take daily quizzes | ✓ | ✗ |
| Read manga stories | ✓ | ✗ |
| Make donations | ✓ | ✗ |
| Manage own profile | ✓ | ✓ |
| Create flashcard content | ✗ | ✓ |
| Create manga stories | ✗ | ✓ |
| View all donations | ✗ | ✓ |
| Analyze platform metrics | ✗ | ✓ |
| User management | ✗ | ✓ |

---

# 📱 USER INTERFACE DIFFERENCES

## Regular User Interface
- **Color Scheme**: Teal/mint green theme (#5d8d8a, #cce7e8)
- **Layout**: Sidebar with learning modules
- **Focus**: Content consumption and progress tracking
- **Accessibility**: Simplified navigation, personal dashboard

## Admin Interface
- **Color Scheme**: Dark blue theme with mint accents
- **Layout**: Admin sidebar with management tools
- **Focus**: Content creation and analytics
- **Accessibility**: Database management, bulk operations

---

# 🎯 PRIMARY USE CASES

## For Regular Users:
1. **Learn Japanese Kana** through interactive flashcards and quizzes
2. **Improve Reading** by engaging with manga stories in multiple languages
3. **Track Progress** with daily goals and mastery levels
4. **Support Development** by making optional donations
5. **Maintain Profile** with personalized learning information

## For Admins:
1. **Create Learning Content** (flashcards and manga stories)
2. **Monitor Platform Health** through donation analytics
3. **Understand User Engagement** via feature usage data
4. **Manage Content Library** with full CRUD operations
5. **Track Financial Support** with detailed donation reports

---

# 💡 KEY INSIGHTS

## User Benefits
- **Structured Learning Path**: Dashboard guides daily learning
- **Multiple Learning Modalities**: Flashcards, quizzes, and manga reading
- **Progress Visibility**: Clear tracking of mastery levels
- **Support Option**: Can contribute to project's sustainability

## Admin Benefits
- **Content Control**: Full ability to create and modify learning materials
- **Financial Transparency**: Complete donation tracking and analytics
- **Engagement Metrics**: Data on which features users value most
- **Scalability**: Easy to add new content without technical intervention

---

# 🔄 DATA FLOW SUMMARY

## User Learning Journey:
```
Login → Dashboard (view progress) → Choose Activity:
├── Kana Charts (study characters)
├── Kana Quiz (test knowledge)
├── Manga Reading (immerse in content)
└── Settings (manage profile & donations)
```

## Admin Content Management:
```
Login → Admin Dashboard (view analytics) → Manage Content:
├── Flashcards (create/edit kana)
├── Kana Charts (visual management)
├── Manga Stories (create/edit)
└── Settings (account management)
```

---

*Last Updated: January 27, 2026*
*NihonGo Platform - User & Admin Feature Documentation*
