<?php
require __DIR__ . '/admin-functions.php';
require_admin();

$donations = get_donation_reports();
$summary = get_donation_summary();
$total_donations = array_sum(array_column($summary, 'total'));
$total_donors = count(array_unique(array_column($donations, 'user_id')));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard - NihonGo</title>
  <link rel="stylesheet" href="../styles.css">
  <link rel="stylesheet" href="admin-style.css">
</head>
<body>
  <!-- SIDEBAR -->
  <?php include 'sidebar.php'; ?>

  <!-- MAIN -->
  <main class="main">
    <div class="topbar">
      <div class="topbar-left">
        <h1 class="topbar-title">Admin Dashboard</h1>
        <p class="topbar-subtitle">Monitor donations and manage content</p>
      </div>
      <div class="topbar-right">
        <img src="../images/exit.png" id="exitBtn" alt="Exit" title="Logout">
        <img src="../images/setting.png" id="settingsBtn" alt="Settings" title="Settings">
        <img src="../images/profile.png" id="profileBtn" alt="Profile" title="Profile">
      </div>
    </div>

    <div class="content">
      <!-- Summary Cards -->
      <div class="summary-grid">
        <div class="summary-card">
          <h3>Total Donations</h3>
          <div class="amount">$<?= number_format($total_donations, 2) ?></div>
        </div>
        <div class="summary-card">
          <h3>Total Donors</h3>
          <div class="count"><?= $total_donors ?></div>
        </div>
        <div class="summary-card">
          <h3>Donations Made</h3>
          <div class="count"><?= count($donations) ?></div>
        </div>
      </div>

      <!-- Donation Summary by Feature -->
      <div class="admin-panel">
        <h2>Donations by Feature</h2>
        <div class="summary-grid">
          <?php foreach ($summary as $item): ?>
          <div class="summary-card">
            <h3><?= htmlspecialchars($item['feature_name']) ?></h3>
            <div class="amount">$<?= number_format($item['total'], 2) ?></div>
            <div class="count"><?= $item['count'] ?> donation<?= $item['count'] != 1 ? 's' : '' ?></div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Detailed Donation Report -->
      <div class="admin-panel">
        <h2>Detailed Donation Report</h2>
        <div class="donation-report">
          <table class="donation-table">
            <thead>
              <tr>
                <th>User</th>
                <th>Feature</th>
                <th>Amount</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($donations as $donation): ?>
              <tr>
                <td>
                  <strong><?= htmlspecialchars($donation['username']) ?></strong>
                  <?php if ($donation['display_name']): ?>
                    <br><small><?= htmlspecialchars($donation['display_name']) ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="donation-feature"><?= htmlspecialchars($donation['feature_name']) ?></span>
                </td>
                <td class="donation-amount">$<?= number_format($donation['amount'], 2) ?></td>
                <td><?= date('M d, Y', strtotime($donation['donation_date'])) ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <script>
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
