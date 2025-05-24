<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $user = findUserByUsername($username);
    
        if (
            !$user ||
            !password_verify($password, $user['password']) ||
            !isset($user['role']) ||
            strtolower($user['role']) !== 'admin'
        ) {
            $error = "Invalid admin credentials.";
        } else {
            $_SESSION['user'] = $user;

        // Log info
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';
        $location_info = @file_get_contents("http://ip-api.com/json/$ip");
        $location_data = json_decode($location_info, true);
        $location = $location_data['city'] ?? 'Unknown';
        
        $timezone = new DateTimeZone('Asia/Manila');
        $now = new DateTime('now', $timezone);
        $timestamp = $now->format('Y-m-d H:i:s');
        $current_week = $now->format('W');
        $current_year = $now->format('Y');

        $logData = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'ip' => $ip,
            'device' => $userAgent,
            'location' => $location,
            'time' => $timestamp
        ];

        $logFile = __DIR__ . '/../data/login_logs.json';
        $existingLogs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
        $existingLogs[] = $logData;
        file_put_contents($logFile, json_encode($existingLogs, JSON_PRETTY_PRINT));

        header("Location: dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Login - FriendNest</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f3f6fc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        form {
            align-items: center;
            padding: 10px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .auth-box {
            background: #78b9ff;
            padding: 30px 25px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            max-width: 400px;
            width: 90%;
            text-align: center;
        }

        .auth-box img.logo {
            height: 50px;
            margin-bottom: 15px;
        }

        .auth-box h1 {
            color: #fff;

        }

        .auth-box input {
            width: 100%;
            padding: 12px;
            margin: 5px 0 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
            text-align: center;
        }

        .auth-box button {
            width: 100%;
            padding: 12px;
            background: #4460ff;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            margin-top: 5px;
        }

        .auth-box button:hover {
            background: #2e47d3;
        }

        .auth-box p {
            margin-top: 15px;
            font-size: 14px;
            color: white;
        }

        .auth-box .error {
            color: #ffdddd;
            background-color:rgb(194, 45, 45);
            padding: 10px;
            border-radius: 10px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="auth-box">
        <img src="../assets/images/logo.png" alt="Admin Logo" class="logo">
        <h1>ADMIN LOGIN</h1>
        <form method="post">
            <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>
            <input type="text" name="username" placeholder="Admin Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p><i>Only for authorized personnel</i></p>
    </div>
</body>
</html>