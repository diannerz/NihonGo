<?php
require "php/check_auth.php";
require "php/db.php";

if (!$user) {
    header("Location: login.html");
    exit;
}

$stmt = $pdo->prepare("
    SELECT username, display_name, bio, avatar_url
    FROM users
    WHERE id = :id
");
$stmt->execute([':id' => $user['id']]);
$profile = $stmt->fetch(PDO::FETCH_ASSOC);

$displayName = $profile['display_name'] ?: $profile['username'];
$bio = $profile['bio'] ?? '';
$avatar = $profile['avatar_url'] ?? '';

// Get donation stats
$donationStmt = $pdo->prepare("
    SELECT 
        COUNT(*) as donation_count,
        SUM(amount) as total_donated,
        MAX(donation_date) as last_donation
    FROM donations
    WHERE user_id = :id
");
$donationStmt->execute([':id' => $user['id']]);
$donations = $donationStmt->fetch(PDO::FETCH_ASSOC);

$donationCount = $donations['donation_count'] ?? 0;
$totalDonated = $donations['total_donated'] ? floatval($donations['total_donated']) : 0;
$lastDonation = $donations['last_donation'] ?? null;
$isAdmin = $user && $user['role'] === 'admin';
?>

<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>NihonGo — Profile</title>

  <link rel="stylesheet" href="styles.css">

  <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&display=swap" rel="stylesheet">

  <style>
    body {
      background-color: #cce7e8;
      font-family: 'Kosugi Maru', sans-serif;
      color: #1e2f30;
      margin: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* TOPBAR */
    .topbar {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 24px;
      background: #5d8d8a;
      color: white;
      border-bottom: 4px solid #4d7d86;
      width: 100%;
      box-sizing: border-box;
    }

    .topbar .left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .topbar .left img.home {
      height: 40px;
      width: auto;
      cursor: pointer;
    }

    .topbar h1 {
      margin: 0;
      font-size: 1.4rem;
      font-weight: 700;
      letter-spacing: 1px;
    }

    .topbar .icons {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .topbar .icons img {
      height: 34px;
      width: auto;
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .topbar .icons img:hover {
      transform: scale(1.1);
    }

    /* MAIN CONTENT AREA */
    main {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 40px;
      box-sizing: border-box;
      gap: 30px;
    }

    /* DONATION SUMMARY PANEL */
    .donation-summary-panel {
      width: 90%;
      max-width: 1100px;
      background: #d8eae9;
      border-left: 4px solid #4d7d86;
      padding: 20px 25px;
      border-radius: 4px;
      box-sizing: border-box;
      font-family: 'Kosugi Maru', sans-serif;
    }

    .profile-panel {
      width: 90%;
      max-width: 1100px;
      background: #76939b;
      border-radius: 8px;
      padding: 30px 40px;
      display: flex;
      gap: 50px;
      align-items: flex-start;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      box-sizing: border-box;
    }

    /* LEFT COLUMN - PROFILE */
    .left-col {
      flex: 2;
      display: flex;
      flex-direction: column;
    }

    .panel-title {
      color: #1e2f30;
      font-family: 'Kosugi Maru', sans-serif;
      font-size: 1.1rem;
      font-weight: 700;
      margin-bottom: 12px;
      display: inline-block;
      background: rgba(255,255,255,0.15);
      padding: 6px 12px;
      border-radius: 4px;
    }

    .name-box {
      background: #4d7d86;
      border-radius: 6px;
      padding: 12px 16px;
      font-size: 1.6rem;
      font-weight: 900;
      color: white;
      margin-bottom: 10px;
      width: fit-content;
      font-family: 'Kosugi Maru', sans-serif;
    }

    .small-actions {
      margin-bottom: 20px;
    }

    .small-actions .edit-btn,
    .small-actions .save-btn {
      margin-right: 10px;
      cursor: pointer;
      color: #d8eae9;
      text-decoration: underline;
      font-size: 0.9rem;
      font-family: 'Kosugi Maru', sans-serif;
    }

    .small-actions .save-btn {
      color: #b0c4c8;
      text-decoration: none;
    }

    .bio-label {
      display: block;
      font-weight: 600;
      color: #d8eae9;
      margin-bottom: 8px;
      font-size: 0.95rem;
      font-family: 'Kosugi Maru', sans-serif;
    }

    .bio-box {
      background: #4d7d86;
      border-radius: 6px;
      padding: 12px 16px;
      font-size: 0.95rem;
      font-weight: 500;
      color: #d8eae9;
      line-height: 1.5;
      max-width: 600px;
      font-family: 'Kosugi Maru', sans-serif;
    }

    /* RIGHT COLUMN - AVATAR */
    .right-col {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 15px;
    }

    /* DONATION PANEL STYLES */
    .donation-summary-title {
      color: #1e2f30;
      font-family: 'Kosugi Maru', sans-serif;
      font-size: 1.1rem;
      font-weight: 700;
      margin: 0 0 15px 0;
    }

    .donation-summary-content {
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
      align-items: center;
    }

    .donation-stat {
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }

    .donation-stat-label {
      color: #4d7d86;
      font-family: 'Kosugi Maru', sans-serif;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 3px;
    }

    .donation-stat-value {
      color: #1e2f30;
      font-family: 'Kosugi Maru', sans-serif;
      font-size: 1.5rem;
      font-weight: 700;
    }

    .donation-stat-subtext {
      color: #76939b;
      font-family: 'Kosugi Maru', sans-serif;
      font-size: 0.8rem;
      margin-top: 2px;
    }

    .no-donations {
      color: #1e2f30;
      font-family: 'Kosugi Maru', sans-serif;
      font-size: 0.95rem;
    }

    .no-donations a {
      color: #4d7d86;
      text-decoration: underline;
    }

    .no-donations a:hover {
      color: #2c4f55;
    }

    /* AVATAR SECTION */
    .avatar-wrap {
      background: #4d7d86;
      padding: 12px;
      border-radius: 6px;
      display: inline-block;
    }

    #avatarImg {
      width: 150px;
      height: 150px;
      border-radius: 4px;
      object-fit: cover;
      border: 3px solid #76939b;
      display: block;
    }

    .change-avatar {
      display: block;
      margin-top: 8px;
      color: #d8eae9;
      text-decoration: underline;
      font-weight: 600;
      cursor: pointer;
      font-size: 0.9rem;
      text-align: center;
      font-family: 'Kosugi Maru', sans-serif;
    }

    .change-avatar:hover {
      color: white;
    }

    @media (max-width: 900px) {
      .profile-panel {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }
      .left-col, .right-col {
        width: 100%;
      }
      .name-box {
        font-size: 1.4rem;
      }
    }
  </style>
</head>
<body>

  <!-- ✅ FIXED TOPBAR -->
  <div class="topbar">
    <div class="left">
      <img src="images/home.png" alt="home" class="home" id="homeBtn">
      <h1>Profile Settings</h1>
    </div>

    <div class="icons">
      <img src="images/exit.png" alt="exit" id="exitIcon">
      <img src="images/profile.png" alt="settings" id="settingsIcon">
      <?php if (!$isAdmin): ?>
      <img src="images/donations.png" alt="profile" id="profileIcon">
      <?php endif; ?>
    </div>
  </div>

  <!-- ✅ FIXED CENTERED PANEL -->
 <main>
  <!-- DONATION SUMMARY PANEL (TOP) - HIDDEN FOR ADMINS -->
  <?php if (!$isAdmin): ?>
  <div class="donation-summary-panel">
    <h2 class="donation-summary-title">Your Donation Support</h2>
    <div class="donation-summary-content">
      <?php if ($donationCount > 0): ?>
        <div class="donation-stat">
          <span class="donation-stat-label">Total Donated</span>
          <span class="donation-stat-value">$<?= number_format($totalDonated, 2) ?></span>
        </div>
        <div class="donation-stat">
          <span class="donation-stat-label">Number of Donations</span>
          <span class="donation-stat-value"><?= $donationCount ?></span>
        </div>
        <div class="donation-stat">
          <span class="donation-stat-label">Last Donation</span>
          <span class="donation-stat-value"><?= date('M d, Y', strtotime($lastDonation)) ?></span>
          <span class="donation-stat-subtext"><?= date('g:i A', strtotime($lastDonation)) ?></span>
        </div>
      <?php else: ?>
        <span class="no-donations">You haven't made any donations yet. <a href="donation.php">Support us today!</a></span>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <!-- PROFILE PANEL (BOTTOM) -->
  <div class="profile-panel">

    <!-- LEFT COLUMN - PROFILE -->
    <div class="left-col">

      <!-- DISPLAY NAME -->
      <div class="panel-title">Display Name</div>
      <div class="name-box" id="displayName" contenteditable="false">
        <?= htmlspecialchars($displayName) ?>
      </div>
      <div class="small-actions">
        <span class="edit-btn" id="editName">Edit</span>
        <span class="save-btn" id="saveName">Save</span>
      </div>

      <!-- PROFILE INFO -->
      <div class="panel-title" style="margin-top:20px;">Profile Info</div>
      <label class="bio-label">Bio</label>

      <div class="bio-box" id="bioText" contenteditable="false">
        <?= htmlspecialchars($bio) ?>
      </div>

      <div class="small-actions">
        <span class="edit-btn" id="editBio">Edit</span>
        <span class="save-btn" id="saveBio">Save</span>
      </div>

    </div>

    <!-- RIGHT COLUMN - AVATAR -->
    <div class="right-col">

      <!-- AVATAR SECTION -->
      <div class="panel-title">Avatar</div>

      <div class="avatar-wrap">
        <div id="avatarBox">
          <?php if ($avatar): ?>
            <img id="avatarImg" src="<?= htmlspecialchars($avatar) ?>" alt="avatar">
          <?php else: ?>
            <img id="avatarImg" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='150' height='150'%3E%3Crect fill='%234d7d86' width='150' height='150'/%3E%3C/svg%3E" alt="avatar">
          <?php endif; ?>
        </div>
      </div>

      <a class="change-avatar" id="changeAvatar">
        Change avatar
      </a>

    </div>

  </div>
</main>

 <script>
const nameBox = document.getElementById('displayName');
const bioBox = document.getElementById('bioText');

/* ---------------- DISPLAY NAME ---------------- */
document.getElementById('editName').onclick = () => {
  nameBox.contentEditable = true;
  nameBox.focus();
};

document.getElementById('saveName').onclick = async () => {
  nameBox.contentEditable = false;

  await fetch('php/update_profile.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      display_name: nameBox.innerText.trim()
    })
  });
};

/* ---------------- BIO ---------------- */
document.getElementById('editBio').onclick = () => {
  bioBox.contentEditable = true;
  bioBox.focus();
};

document.getElementById('saveBio').onclick = async () => {
  bioBox.contentEditable = false;

  await fetch('php/update_profile.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      bio: bioBox.innerText.trim()
    })
  });
};

/* ---------------- AVATAR ---------------- */
document.getElementById('changeAvatar').onclick = async () => {
  const url = prompt('Enter avatar image URL');
  if (!url) return;

  await fetch('php/update_profile.php', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({
      avatar_url: url
    })
  });

  location.reload();
};
</script>

<script>
/* ---------- TOPBAR NAVIGATION FIX ---------- */
document.getElementById('homeBtn')?.addEventListener('click', () => {
  window.location.href = '/NihonGo/dashboard.php';
});

document.getElementById('exitIcon')?.addEventListener('click', () => {
  if (!confirm('Log out?')) return;
  window.location.href = '/NihonGo/php/logout.php';
});

document.getElementById('settingsIcon')?.addEventListener('click', () => {
  window.location.href = '/NihonGo/settings.php';
});

// Profile icon only shown for non-admins
if (document.getElementById('profileIcon')) {
  document.getElementById('profileIcon').addEventListener('click', () => {
    window.location.href = '/NihonGo/donation.php';
  });
}
</script>


</body>
</html>