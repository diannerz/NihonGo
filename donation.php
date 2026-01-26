<?php
require "php/check_auth.php";
require "php/db.php";

// Redirect if not logged in
if (!$user) {
    header("Location: login.html");
    exit;
}

$userEmail = $user['email'] ?? '';
$userEmailJSON = json_encode($userEmail);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Donation - NihonGo</title>
  <link rel="stylesheet" href="donation-style.css">
  <link href="https://fonts.googleapis.com/css2?family=Kosugi+Maru&display=swap" rel="stylesheet">
  <style>
    /* Modal styling */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .modal-content {
      background-color: #cce7e8;
      margin: 5% auto;
      padding: 30px;
      border-radius: 12px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
      animation: slideIn 0.3s ease;
      font-family: 'Kosugi Maru', sans-serif;
    }

    @keyframes slideIn {
      from {
        transform: translateY(-50px);
        opacity: 0;
      }
      to {
        transform: translateY(0);
        opacity: 1;
      }
    }

    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      border-bottom: 3px solid #4d7d86;
      padding-bottom: 15px;
    }

    .modal-header h2 {
      margin: 0;
      color: #1e2f30;
      font-size: 1.5rem;
      font-family: 'Kosugi Maru', sans-serif;
    }

    .close-btn {
      background: none;
      border: none;
      font-size: 28px;
      cursor: pointer;
      color: #76939b;
      transition: color 0.3s;
    }

    .close-btn:hover {
      color: #2c4f55;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      font-weight: 600;
      margin-bottom: 6px;
      color: #1e2f30;
      font-size: 0.95rem;
      font-family: 'Kosugi Maru', sans-serif;
    }

    .form-group input {
      width: 100%;
      padding: 10px 12px;
      border: 2px solid #76939b;
      border-radius: 6px;
      font-size: 1rem;
      font-family: 'Kosugi Maru', sans-serif;
      box-sizing: border-box;
      transition: border-color 0.3s, box-shadow 0.3s;
      background-color: #ffffff;
      color: #1e2f30;
    }

    .form-group input:focus {
      outline: none;
      border-color: #4d7d86;
      box-shadow: 0 0 0 3px rgba(77, 125, 134, 0.2);
    }

    .form-group input.error {
      border-color: #d32f2f;
      background-color: #fff5f5;
    }

    .error-message {
      color: #d32f2f;
      font-size: 0.85rem;
      margin-top: 4px;
      display: none;
    }

    .error-message.show {
      display: block;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }

    .form-group.full {
      grid-column: 1 / -1;
    }

    .donation-info {
      background: #d8eae9;
      border-left: 4px solid #4d7d86;
      padding: 12px 15px;
      border-radius: 4px;
      margin-bottom: 20px;
      font-size: 0.95rem;
      color: #1e2f30;
      font-family: 'Kosugi Maru', sans-serif;
      font-weight: 500;
    }

    .donation-info strong {
      font-weight: 700;
    }

    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 25px;
    }

    .btn {
      flex: 1;
      padding: 12px 20px;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      font-weight: 600;
      font-family: 'Kosugi Maru', sans-serif;
    }

    .btn-donate {
      background: #4d7d86;
      color: white;
    }

    .btn-donate:hover:not(:disabled) {
      background: #2c4f55;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(44, 79, 85, 0.3);
    }

    .btn-donate:disabled {
      background: #9ca3a4;
      cursor: not-allowed;
    }

    .btn-cancel {
      background: #d8eae9;
      color: #1e2f30;
      border: 2px solid #76939b;
    }

    .btn-cancel:hover {
      background: #c0dce0;
      border-color: #4d7d86;
    }

    /* Donation options section */
    .donation-options {
      display: flex;
      flex-direction: column;
      gap: 20px;
      margin: 30px 0;
    }

    /* Donation option styling */
    .donation-option {
      position: relative;
      background: #76939b;
      padding: 20px;
      border-radius: 8px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      cursor: help;
      transition: background 0.3s;
    }

    .donation-option:hover {
      background: #6b8e95;
    }

    .donation-option span {
      color: white;
      font-family: 'Kosugi Maru', sans-serif;
      font-weight: 600;
    }

    .donation-option span:first-child {
      font-size: 1.3rem;
      min-width: 60px;
    }

    .donation-option span:nth-child(2) {
      flex: 1;
      margin-left: 15px;
      margin-right: 15px;
      font-size: 1rem;
    }

    .donation-option button {
      background: #4d7d86;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-family: 'Kosugi Maru', sans-serif;
      font-weight: 600;
      font-size: 0.95rem;
      transition: background 0.3s, transform 0.2s;
      white-space: nowrap;
    }

    .donation-option button:hover {
      background: #2c4f55;
      transform: translateY(-2px);
    }

    /* Tooltip styling */
    .donation-tooltip {
      visibility: hidden;
      width: 280px;
      background-color: #2c4f55;
      color: #fff;
      text-align: center;
      border-radius: 6px;
      padding: 10px;
      position: absolute;
      z-index: 100;
      bottom: 125%;
      left: 50%;
      margin-left: -140px;
      opacity: 0;
      transition: opacity 0.3s;
      font-size: 0.85rem;
      line-height: 1.4;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.15);
      font-family: 'Kosugi Maru', sans-serif;
    }

    .donation-tooltip::after {
      content: "";
      position: absolute;
      top: 100%;
      left: 50%;
      margin-left: -5px;
      border-width: 5px;
      border-style: solid;
      border-color: #2c4f55 transparent transparent transparent;
    }

    /* Show tooltip on hover of option or button */
    .donation-option:hover .donation-tooltip,
    .donation-option button:hover ~ .donation-tooltip {
      visibility: visible;
      opacity: 1;
    }

    .success-message {
      background: #d8eae9;
      color: #2c4f55;
      padding: 15px;
      border-radius: 6px;
      margin-bottom: 15px;
      display: none;
      text-align: center;
      font-weight: 600;
      font-family: 'Kosugi Maru', sans-serif;
      border-left: 4px solid #4d7d86;
    }

    .success-message.show {
      display: block;
    }

    body {
      font-family: 'Kosugi Maru', sans-serif;
      background-color: #cce7e8;
      color: #1e2f30;
      margin: 0;
      padding: 0;
    }

    .error-message {
      color: #d32f2f;
      font-size: 0.85rem;
      margin-top: 4px;
      display: none;
      font-family: 'Kosugi Maru', sans-serif;
    }

    .error-message.show {
      display: block;
    }
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
        <p class="subtitle">Thank you for supporting us for our future resources!</p>
      </div>
    </div>
    <div class="top-right">
      <a href="php/logout.php" title="Logout">
        <img src="images/exit.png" alt="logout">
      </a>
      <a href="settings.php" title="Settings">
        <img src="images/profile.png" alt="settings">
      </a>
      <a href="donation.php" title="Donation">
        <img src="images/donations.png" alt="donation">
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
          <div class="donation-tooltip">
            Supports video tutorials, detailed character explanations, and improved flashcard mnemonics for better learning.
          </div>
          <button onclick="openDonationModal(event, 10, 'Quality Content')">Donate</button>
        </div>

        <div class="donation-option" data-amount="15">
          <span>$15</span>
          <span>Expanded Resources</span>
          <div class="donation-tooltip">
            Helps us create new manga stories, add more vocabulary, expand the dictionary, and develop advanced learning modules.
          </div>
          <button onclick="openDonationModal(event, 15, 'Expanded Resources')">Donate</button>
        </div>

        <div class="donation-option" data-amount="20">
          <span>$20</span>
          <span>Community Growth</span>
          <div class="donation-tooltip">
            Funds server maintenance, app improvements, user support, community features, and making NihonGo accessible to more learners worldwide.
          </div>
          <button onclick="openDonationModal(event, 20, 'Community Growth')">Donate</button>
        </div>
      </div>
    </div>
  </div>

  <!-- DONATION MODAL -->
  <div id="donationModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Complete Your Donation</h2>
        <button class="close-btn" onclick="closeDonationModal()">&times;</button>
      </div>

      <div class="success-message" id="successMessage">
        Thank you for your donation! Your contribution has been recorded.
      </div>

      <div class="donation-info" id="donationInfo">
        You are donating <strong id="donationAmount">$0</strong> for <strong id="donationFeature">Feature</strong>
      </div>

      <form id="donationForm" onsubmit="processDonation(event)">
        <!-- Email -->
        <div class="form-group full">
          <label for="email">Email Address *</label>
          <input 
            type="email" 
            id="email" 
            name="email" 
            placeholder="Enter your email"
            value="<?= htmlspecialchars($userEmail) ?>"
            required
          >
          <div class="error-message" id="emailError"></div>
        </div>

        <!-- Full Name -->
        <div class="form-group full">
          <label for="fullName">Full Name *</label>
          <input 
            type="text" 
            id="fullName" 
            name="fullName" 
            placeholder="Enter your full name"
            required
          >
          <div class="error-message" id="fullNameError"></div>
        </div>

        <!-- Card Number -->
        <div class="form-group full">
          <label for="cardNumber">Card Number *</label>
          <input 
            type="text" 
            id="cardNumber" 
            name="cardNumber" 
            placeholder="1234 5678 9012 3456"
            maxlength="19"
            required
          >
          <div class="error-message" id="cardNumberError"></div>
        </div>

        <!-- Expiry and CVV Row -->
        <div class="form-row">
          <div class="form-group">
            <label for="expiry">Expiry Date *</label>
            <input 
              type="text" 
              id="expiry" 
              name="expiry" 
              placeholder="MM/YY"
              maxlength="5"
              required
            >
            <div class="error-message" id="expiryError"></div>
          </div>

          <div class="form-group">
            <label for="cvv">CVV *</label>
            <input 
              type="text" 
              id="cvv" 
              name="cvv" 
              placeholder="123"
              maxlength="4"
              required
            >
            <div class="error-message" id="cvvError"></div>
          </div>
        </div>

        <!-- Buttons -->
        <div class="button-group">
          <button type="button" class="btn btn-cancel" onclick="closeDonationModal()">Cancel</button>
          <button type="submit" class="btn btn-donate" id="submitBtn">Confirm Donation</button>
        </div>
      </form>
    </div>
  </div>

  <footer>
    <p>NihonGo - One Kana at a Time</p>
  </footer>

  <script>
    let currentDonationAmount = 0;
    let currentDonationFeature = '';

    // Open donation modal
    function openDonationModal(event, amount, feature) {
      event.preventDefault();
      currentDonationAmount = amount;
      currentDonationFeature = feature;
      
      document.getElementById('donationAmount').textContent = `$${amount}`;
      document.getElementById('donationFeature').textContent = feature;
      document.getElementById('donationModal').style.display = 'block';
      
      // Reset form
      document.getElementById('donationForm').reset();
      document.getElementById('donationForm').style.display = 'block';
      document.getElementById('successMessage').classList.remove('show');
      clearAllErrors();
    }

    // Close donation modal
    function closeDonationModal() {
      document.getElementById('donationModal').style.display = 'none';
      document.getElementById('donationForm').reset();
      clearAllErrors();
    }

    // Close modal when clicking outside of it
    window.onclick = function(event) {
      const modal = document.getElementById('donationModal');
      if (event.target == modal) {
        closeDonationModal();
      }
    }

    // Clear all error messages
    function clearAllErrors() {
      document.querySelectorAll('.error-message').forEach(el => {
        el.classList.remove('show');
        el.textContent = '';
      });
      document.querySelectorAll('input').forEach(el => {
        el.classList.remove('error');
      });
    }

    // Show error message
    function showError(fieldId, message) {
      const input = document.getElementById(fieldId);
      const errorEl = document.getElementById(fieldId + 'Error');
      input.classList.add('error');
      errorEl.textContent = message;
      errorEl.classList.add('show');
    }

    // Validate form
    function validateForm() {
      clearAllErrors();
      let isValid = true;

      // Email validation - just check it's not empty (it's pre-filled from server)
      const email = document.getElementById('email').value.trim();
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      
      if (!email) {
        showError('email', 'Email is required');
        isValid = false;
      } else if (!emailRegex.test(email)) {
        showError('email', 'Invalid email format');
        isValid = false;
      }
      // Note: Server-side validation will check if email matches the authenticated user

      // Full name validation
      const fullName = document.getElementById('fullName').value.trim();
      if (!fullName) {
        showError('fullName', 'Full name is required');
        isValid = false;
      } else if (fullName.length < 2) {
        showError('fullName', 'Name must be at least 2 characters');
        isValid = false;
      } else if (!/^[a-zA-Z\s\-\']+$/.test(fullName)) {
        showError('fullName', 'Name can only contain letters, spaces, hyphens, and apostrophes');
        isValid = false;
      }

      // Card number validation (basic Luhn check)
      const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
      if (!cardNumber) {
        showError('cardNumber', 'Card number is required');
        isValid = false;
      } else if (!/^\d{13,19}$/.test(cardNumber)) {
        showError('cardNumber', 'Card number must be 13-19 digits');
        isValid = false;
      } else if (!luhnCheck(cardNumber)) {
        showError('cardNumber', 'Invalid card number');
        isValid = false;
      }

      // Expiry validation
      const expiry = document.getElementById('expiry').value.trim();
      const expiryRegex = /^(0[1-9]|1[0-2])\/\d{2}$/;
      if (!expiry) {
        showError('expiry', 'Expiry date is required');
        isValid = false;
      } else if (!expiryRegex.test(expiry)) {
        showError('expiry', 'Format must be MM/YY');
        isValid = false;
      } else {
        // Check if card is expired
        const [month, year] = expiry.split('/');
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear() % 100;
        const currentMonth = currentDate.getMonth() + 1;
        
        if (parseInt(year) < currentYear || (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
          showError('expiry', 'Card has expired');
          isValid = false;
        }
      }

      // CVV validation
      const cvv = document.getElementById('cvv').value.trim();
      if (!cvv) {
        showError('cvv', 'CVV is required');
        isValid = false;
      } else if (!/^\d{3,4}$/.test(cvv)) {
        showError('cvv', 'CVV must be 3-4 digits');
        isValid = false;
      }

      return isValid;
    }

    // Luhn algorithm for card validation
    function luhnCheck(cardNumber) {
      let sum = 0;
      let isEven = false;
      
      for (let i = cardNumber.length - 1; i >= 0; i--) {
        let digit = parseInt(cardNumber.charAt(i), 10);
        
        if (isEven) {
          digit *= 2;
          if (digit > 9) {
            digit -= 9;
          }
        }
        
        sum += digit;
        isEven = !isEven;
      }
      
      return (sum % 10) === 0;
    }

    // Format card number with spaces
    document.getElementById('cardNumber').addEventListener('input', function() {
      let value = this.value.replace(/\s/g, '');
      let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
      this.value = formattedValue;
    });

    // Format expiry date
    document.getElementById('expiry').addEventListener('input', function() {
      let value = this.value.replace(/\D/g, '');
      if (value.length >= 2) {
        value = value.slice(0, 2) + '/' + value.slice(2, 4);
      }
      this.value = value;
    });

    // Only allow numbers in CVV
    document.getElementById('cvv').addEventListener('input', function() {
      this.value = this.value.replace(/\D/g, '');
    });

    // Process donation
    function processDonation(event) {
      event.preventDefault();

      if (!validateForm()) {
        return;
      }

      // Disable submit button
      const submitBtn = document.getElementById('submitBtn');
      submitBtn.disabled = true;
      submitBtn.textContent = 'Processing...';

      const formData = {
        amount: currentDonationAmount,
        feature_name: currentDonationFeature,
        email: document.getElementById('email').value.trim(),
        fullName: document.getElementById('fullName').value.trim(),
        cardNumber: document.getElementById('cardNumber').value.replace(/\s/g, '').slice(-4),
        expiry: document.getElementById('expiry').value.trim()
      };

      fetch('php/process_donation.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
      })
      .then(response => response.json())
      .then(data => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Confirm Donation';

        if (data.success) {
          document.getElementById('successMessage').classList.add('show');
          document.getElementById('donationForm').style.display = 'none';
          
          setTimeout(() => {
            closeDonationModal();
            window.location.href = 'dashboard.php';
          }, 2000);
        } else {
          console.error('Backend error:', data);
          showError('email', data.message || 'Error processing donation');
        }
      })
      .catch(error => {
        console.error('Fetch error:', error);
        submitBtn.disabled = false;

        submitBtn.textContent = 'Confirm Donation';
        showError('email', 'An error occurred. Please try again.');
      });
    }
  </script>

</body>
</html>
