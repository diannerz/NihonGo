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
?>

<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>NihonGo — Profile Settings</title>

  <link rel="stylesheet" href="styles.css">

  <style>
    body {
      background-color: #d7f9f6;
      font-family: 'Poppins', sans-serif;
      color: #1d2f2f;
      margin: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .avatar-box {
  width: 180px;
  height: 180px;
  border-radius: 14px;
  background: rgba(255,255,255,0.25);
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  overflow: hidden;
}

.avatar-box img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}


    /* ✅ FIX 1: topbar matches dashboard, icons properly sized */
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

    /* ✅ FIX 2: Center panel both vertically & horizontally */
    main {
      flex: 1;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 40px;
      box-sizing: border-box;
    }

    .profile-panel {
      width: 90%;
      max-width: 1100px;
      background: #7a9b9a;
      border-radius: 22px;
      padding: 40px 50px;
      display: flex;
      gap: 50px;
      align-items: flex-start;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
      box-sizing: border-box;
    }

    .left-col {
      flex: 2;
    }

    .panel-title {
      background: #35666a;
      color: #eaf7f6;
      padding: 8px 12px;
      border-radius: 10px;
      display: inline-block;
      font-size: 1.3rem;
      font-weight: 700;
      margin-bottom: 14px;
    }

    .name-box {
      background: #5d7e81;
      border-radius: 16px;
      padding: 16px 22px;
      font-size: 2rem;
      font-weight: 900;
      color: #0c1d1d;
      margin-bottom: 8px;
      width: fit-content;
    }

    .small-actions {
      margin-bottom: 22px;
    }

    .small-actions .edit-btn,
    .small-actions .save-btn {
      margin-right: 10px;
      cursor: pointer;
      color: #153a3b;
      text-decoration: underline;
      font-size: 0.9rem;
    }

    .small-actions .save-btn {
      color: #b6cac9;
      text-decoration: none;
    }

    .bio-label {
      display: block;
      font-weight: 700;
      color: #e4f6f5;
      margin-bottom: 8px;
      font-size: 1rem;
    }

    .bio-box {
      background: #5d7e81;
      border-radius: 16px;
      padding: 20px;
      font-size: 1.05rem;
      font-weight: 700;
      color: #0c1d1d;
      line-height: 1.4;
      max-width: 720px;
    }

    .right-col {
      flex: 1;
      text-align: center;
    }

    .avatar-wrap {
      background: #5d7e81;
      padding: 16px;
      border-radius: 16px;
      display: inline-block;
    }

    #avatarImg {
      width: 180px;
      height: 180px;
      border-radius: 14px;
      object-fit: cover;
      border: 5px solid #5b7f7c;
      display: block;
      margin: 0 auto 10px auto;
    }

    .change-avatar {
      display: block;
      margin-top: 8px;
      color: #0e2d2e;
      text-decoration: underline;
      font-weight: 700;
      cursor: pointer;
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
        font-size: 1.6rem;
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
      <img src="images/setting.png" alt="settings" id="settingsIcon">
      <img src="images/profile.png" alt="profile" id="profileIcon">
    </div>
  </div>

  <!-- ✅ FIXED CENTERED PANEL -->
  <main>
  <div class="profile-panel">

    <!-- LEFT -->
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

      <!-- BIO -->
<div class="panel-title" style="margin-top:20px;">Profile Info</div>
<label class="bio-label">Bio</label>

<div class="bio-box" id="bioText" contenteditable="false">
  <?= htmlspecialchars($bio) ?>
</div>

<div class="small-actions">
  <span class="edit-btn" id="editBio">Edit</span>
  <span class="save-btn" id="saveBio">Save</span>
</div>


    <!-- AVATAR -->
   <div class="right-col">
  <div class="panel-title">Avatar</div>

  <div class="avatar-wrap">
    <div class="avatar-box" id="avatarBox">
      <?php if ($avatar): ?>
        <img id="avatarImg" src="<?= htmlspecialchars($avatar) ?>" alt="avatar">
      <?php else: ?>
        <span>No Avatar</span>
      <?php endif; ?>
    </div>
  </div>

  <a class="change-avatar" id="changeAvatar">Click to change your avatar</a>
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
  // already here, but safe
  window.location.href = '/NihonGo/settings.php';
});

document.getElementById('profileIcon')?.addEventListener('click', () => {
  alert('Profile page coming soon.');
});
</script>


</body>
</html>