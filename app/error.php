<?php
$errorCode = $_GET['code'] ?? '404';
$errorTitles = [
  '401' => 'Unauthorized',
  '403' => 'Forbidden',
  '404' => 'Page Not Found',
  '503' => 'Service Unavailable'
];

$errorMessages = [
  '401' => 'You must be logged in or provide proper credentials to access this page.',
  '403' => 'You do not have permission to view this page or perform this action.',
  '404' => 'Sorry, the page you are looking for does not exist or has been moved.',
  '503' => 'The server is temporarily unable to handle the request due to maintenance or overload.'
];

$title = $errorTitles[$errorCode] ?? 'Unknown Error';
$message = $errorMessages[$errorCode] ?? 'Something went wrong. Please try again later.';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error <?php echo htmlspecialchars($errorCode); ?> | Friendnest</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .error-container {
      margin-top: 50px;
      max-width: 800px;
      margin-left: auto;
      margin-right: auto;
      text-align: center;
      background: #fff;
      border-radius: 10px;
      padding: 50px 20px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .error-code {
      font-size: 120px;
      font-weight: bold;
      color: #007bff;
      margin: 0;
    }
    .error-title {
      font-size: 32px;
      margin: 20px 0 10px;
    }
    .error-message {
      font-size: 18px;
      color: #555;
      margin-bottom: 30px;
    }
    .error-actions button {
      display: inline-block;
      margin: 10px auto 10px;
      padding: 10px 20px;
      background: #007bff;
      color: #fff;
      text-decoration: none;
      border-radius: 5px;
    }
    .error-actions button:hover {
      background: #0056b3;
    }
  </style>
</head>
<body>
  <header>
    <div class="header-left">
      <a href="/"><img src="assets/images/logo.png" alt="Friendnest Logo" class="logo" /></a>
    </div>
  </header>

  <div class="error-container">
    <div class="error-code"><?php echo htmlspecialchars($errorCode); ?></div>
    <div class="error-title"><?php echo htmlspecialchars($title); ?></div>
    <div class="error-message"><?php echo htmlspecialchars($message); ?></div>
    <div class="error-actions">
      <button onclick="location.href='/'">Return Home</button>
      <button onclick="location.href='javascript:history.back()''">Go Back</button>
    </div>
  </div>

  <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
