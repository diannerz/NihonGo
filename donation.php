<main class="main">
  <div class="content">
    <section class="donation-panel">
<?php

require "php/check_auth.php";
require "php/db.php";

// Redirect if not logged in
if (!$user) {
    header("Location: login.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Donation - NihonGo</title>
<link rel="stylesheet" href="donation-style.css">
  <style>
    /* Include CSS changes here (the CSS code from Step 1) */
  </style>
</head>

<body>

  <!-- HEADER -->
  <header class="topbar">
    <div class="top-left">
      <a href="dashboard.php" class="home-link">
        <img src="images/home.png" alt="home">
      </a>
      <div class="header-text-inline">
        <h1 class="title">Growing Our Learning Community</h1>
        <p class="subtitle">Donate today to help us enhance our learning resources!</p>
      </div>
    </div>
    <div class="top-right">
      <a href="php/logout.php" title="Logout">
        <img src="images/exit.png" alt="logout">
      </a>
      <a href="settings.php" title="Settings">
        <img src="images/setting.png" alt="settings">
      </a>
      <a href="dashboard.php" title="Profile">
        <img src="images/profile.png" alt="profile">
      </a>
    </div>
  </header>

  <!-- DONATION CONTENT AREA -->
  <div class="content">
    <div class="donation-panel">
      <h2>Donate Now to Support Our Community!</h2>

      <div class="donation-options">
        <div class="donation-option" data-amount="10">
          <span>$10</span>
          <span>Quality Content</span>
          <button onclick="confirmDonation(event, 10)">Donate</button>
        </div>
        <div class="donation-option" data-amount="15">
          <span>$15 </span>
          <span> Expanded Resources</span>
          <button onclick="confirmDonation(event, 15)">Donate</button>
        </div>
        <div class="donation-option" data-amount="20">
          <span>$20</span>
          <span>Community Growth</span>
          <button onclick="confirmDonation(event, 20)">Donate</button>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <p>NihonGo - One Kana at a Time</p>
  </footer>

  <script>
    // Confirm donation function
    function confirmDonation(event, amount) {
      event.preventDefault();
      
      const featureNames = {
        10: 'Quality Content',
        15: 'Expanded Resources',
        20: 'Community Growth'
      };
      
      const featureName = featureNames[amount] || 'General';
      
      const confirmation = confirm(`Are you sure you want to donate $${amount} for ${featureName}?`);
      if (!confirmation) {
        return;
      }
      
      // Send donation to backend
      fetch('php/process_donation.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          amount: amount,
          feature_name: featureName
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          alert(`Thank you for donating $${amount}! Your contribution has been recorded.`);
          // Optionally redirect or refresh
          window.location.href = 'dashboard.php';
        } else {
          alert('Error processing donation: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your donation.');
      });
    }
  </script>

     </section>
  </div>
</main>

</body>
</html>
