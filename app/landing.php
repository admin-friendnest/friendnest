<?php
session_start();

if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <link rel="icon" type="image/png" href="assets/images/favicon.png">
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Friendnest | Reignite Genuine Social Connection</title>
  <link rel="stylesheet" href="assets/css/style.css" />
  <style>
    .hero {
      margin-top: 30px;
      max-width: 1000px;
      padding: 40px 20px;
      text-align: center;
      background-color: #fff;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.05);
      margin-bottom: 10px;
    }
    .hero h1 {
      font-size: 2.5rem;
      color: #007bff;
      margin-bottom: 20px;
    }
    .hero p {
      font-size: 18px;
      color: #007bff;
      margin-bottom: 30px;
    }
    .features {
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 20px;
      max-width: 1000px;
      margin: 10px auto 10px;

    }
    .feature-box {
      flex: 1 1 300px;
      background-color: #78b9ff;
      color: #fff;
      border-radius: 10px;
      padding: 20px;
      text-align: center;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .feature-box button {
      margin: 10px auto 10px;
      background-color: #fff;
      color: #78b9ff;
      font-weight: bold;
    }

    .feature-box h3 {
      margin-bottom: 10px;
    }
    .cta {
      max-width: 1000px;
      padding: 40px 20px;
      background-color: #007bff;
      color: #fff;
      text-align: center;
      border-radius: 10px;
      margin: 10px auto 10px;
    }
    .cta h2 {
      color: #fff;
      margin-bottom: 15px;
    }
    .cta p {
      color: #e6e6e6;
      margin-bottom: 20px;
    }
    .cta button {
      background-color: #fff;
      color: #007bff;
      font-weight: bold;
    }
    footer {
      margin-top: 10px;
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <a href="index.php"><img src="assets/images/logo.png" alt="Friendnest Logo" class="logo" /></a>
    </div>
    <nav class="main-nav">
      <a href="index.php">Home</a>
      <a href="#features">Features</a>
      <a href="#custom">Customizability</a>
      <a href="#get-started">Get Started</a>
    </nav>
  </header>

  <section class="hero">
    <h1>Reignite Genuine Social Connection</h1>
    <p>Friendnest combines the approach of Twitter and Facebook — a place where your thoughts, feelings, hobbies, and status updates matter again. Built with heart, powered by people.</p>
    <button onclick="location.href='#get-started'">Join the Community</button>
  </section>

  <section class="features" id="features">
    <div class="feature-box">
      <h3>Express Freely</h3>
      <p>Share what you're thinking, feeling, and passionate about. Status updates are front and center, just like the early days of social media.</p>
    </div>
    <div class="feature-box">
      <h3>Efficient & Thoughtfully Organized Data</h3>
      <p> We structure data in a lightweight, well-organized format designed for reliability and performance. Personal information is safeguarded using industry-standard practices, including encryption and secure password hashing.</p>
    </div>
    <div class="feature-box">
      <h3>AI-Assisted, Human-Crafted</h3>
      <p>Design and development are handled by real people. We leverage AI to enhance—not replace—the human touch.</p>
    </div>
  </section>

  <section class="features" id="custom">
    <div class="feature-box">
      <h3>Mobile Responsive</h3>
      <p>Designed to look great and function flawlessly on all screen sizes, from desktops to phones. Your feed, always in your hand.</p>
    </div>
    <div class="feature-box">
      <h3>Open Source, Fully Yours</h3>
      <p>Friendnest is open-source and flexible. Adapt and improve it to fit your community’s needs—whether you’re a niche group or a global movement.</p>
      <button onclick="location.href='https://github.com/view/repo'">View Code on Github</button>
    </div>
    <div class="feature-box">
      <h3>Backed by Community</h3>
      <p>We believe in building together. Friendnest evolves through collective insight—feedback and contributions are always welcome.</p>
    </div>
  </section>

  <section class="cta" id="get-started">
    <h2>Get Started with Friendnest Today</h2>
    <p>No noise. No distractions. Just real social presence.</p>
    <button onclick="location.href='register.php'">Sign Up Now</button>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?> <script>
const legalTexts = {
    privacy: `<?php echo addslashes(file_get_contents('assets/legal/privacy.html')); ?>`,
    terms: `<?php echo addslashes(file_get_contents('assets/legal/terms.html')); ?>`,
    cookies: `<?php echo addslashes(file_get_contents('assets/legal/cookies.html')); ?>`
};

function openModal(type) {
    document.getElementById('modalText').innerHTML = legalTexts[type];
    document.getElementById('legalModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('legalModal').style.display = 'none';
}
</script>
</body>
</html>
