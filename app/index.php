<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $user = findUserByUsername($username);
    if (!$user || !password_verify($password, $user['password'])) {
    $error = "Invalid username or password.";
    } elseif (!empty($user['disabled']) && $user['disabled'] === true) {
    $error = "Your account has been banned.";
    } else {
        $_SESSION['user'] = $user;
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
        $location_info = @file_get_contents("http://ip-api.com/json/$ip");
        $location_data = json_decode($location_info, true);
        $location = $location_data['city'] ?? 'Unknown';
        
        $timezone = new DateTimeZone('');
        $now = new DateTime('now', $timezone);
        $timestamp = $now->format('Y-m-d H:i:s');
        $current_week = $now->format('W');
        $current_year = $now->format('Y');
        
            // Prepare log entry
            $logData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'ip' => $ip,
            'device' => $userAgent,
            'location' => $location,
            'time' => $timestamp
            ];

            // Save to JSON file
            $logFile = __DIR__ . '/data/login_logs.json';
            $existingLogs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
            $existingLogs[] = $logData;
            file_put_contents($logFile, json_encode($existingLogs, JSON_PRETTY_PRINT));

        header("Location: home.php");
        exit;
    }

if (isset($_POST['remember'])) {
        $token = bin2hex(random_bytes(16));
        setcookie('remember_token', $token, time() + (86400 * 30), "/"); // 30 days
    
        // Save token in user's data (e.g., in users.json)
        $user['remember_token'] = $token;
    
        // Update the stored user record
        $users = loadUsers();
        foreach ($users as &$u) {
            if ($u['id'] === $user['id']) {
                $u['remember_token'] = $token;
                break;
            }
        }
        saveUsers($users);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - FriendNest</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#007bff">
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
    background-color: #f3f6fc;
    font-family: 'Segoe UI', sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
}

.auth-box {
    background: #78b9ff;
    padding: 30px 25px;
    border-radius: 15px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    max-width: 400px;
    width: 100%;
    text-align: center;
}

.auth-box img.logo {
    height: 50px;
    margin-bottom: 15px;
}

.auth-box h1 {
    color: rgb(255, 255, 255);
}

.auth-box input {
    width: 100%;
    padding: 10px;
    border-radius: 10px;
    border: 1px solid #ccc;
    font-size: 15px;
    text-align: center;
}

.auth-box button {
    width: 100%;
    padding: 10px;
    background: #4460ff;
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 10px;
}

.auth-box button:hover {
    background: #2e47d3;
}

.auth-box p {
    font-size: 14px;
}

.auth-box p a {
    color:rgb(255, 255, 255);
    text-decoration: none;
}

.auth-box p a:hover {
    color:rgb(255, 255, 255);
    text-decoration: underline;
}

.auth-box .error {
    color: red;
}

.remember-container {
    display: inline-flex;
    align-items:center;
    gap: 10px;
    margin: 1px;
  }

  .remember-container label {
  white-space: nowrap;
  font-size: 16px;
  }
    </style>
</head>
<body>

<div class="page-container">
    <div class="auth-box">
        
        <img src="assets/images/logo.png" alt="FriendNest Logo" class="logo">
        <h1>LOGIN</h1>
        <div class="formIndex">
        <form method="post">
        <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
        <p style=" color: #155724; text-align: center;">Your account has been deleted.</p><?php endif; ?>
            <input type="text" name="username" placeholder="Username">
            <input type="password" name="password" placeholder="Password">
            <div class="remember-container">
                <input type="checkbox" name="remember" id="remember">
                    <label for="remember"><i>Remember Me?</i></label></div>
            <button type="submit">Login</button>
        </form>
        </div>
        <p style="color:white">No account? <b><a href="register.php">Register here</a></b></p>
    </div>
    </div>
    <script>
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/service-worker.js')
      .then(reg => console.log('✅ Service Worker registered', reg))
      .catch(err => console.error('❌ Service Worker registration failed:', err));
  });
}
</script>
</body>
<?php include __DIR__ . '/includes/footer.php'; ?>
</html>
