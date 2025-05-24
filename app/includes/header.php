<?php if (!isset($_SESSION)) session_start(); ?>
    <link rel="stylesheet" href="assets/css/style.css">
<header>
     <div class="header-left">
  <a href="./home.php">
    <img src="assets/images/logo.png" class="logo" alt="FriendNest" style="height: 40px;">
  </a>

  <!-- Hamburger -->
  <button class="hamburger" id="hamburger">☰</button>
</div>

  <!-- Navigation -->
  <nav class="main-nav" id="mainNav">
    <a href="home.php">Home 🏠︎</a>
    <a href="profile.php">Profile ☻</a>
    <a href="settings.php">Settings ⛯</a>
    <a class="logout" href="logout.php">Logout ➜</a>
  </nav>
</header>

<!-- Add at the bottom of body or just after header -->
<script>
  document.getElementById("hamburger").addEventListener("click", function () {
    document.getElementById("mainNav").classList.toggle("open");
  });
</script>