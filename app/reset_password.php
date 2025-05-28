<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

$error = '';
$success = '';
$step = 1;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $hcaptchaResponse = $_POST['h-captcha-response'] ?? '';
    $remoteIp = $_SERVER['REMOTE_ADDR'];

    // Step 1: Verify CAPTCHA and email
    if (isset($_POST['verify'])) {
        $hcaptchaSecret = ''; // your hCaptcha secret
        $verifyData = [
            'secret' => $hcaptchaSecret,
            'response' => $hcaptchaResponse,
            'remoteip' => $remoteIp,
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://hcaptcha.com/siteverify');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($verifyData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $hcaptchaResult = curl_exec($ch);
        curl_close($ch);

        $hcaptchaSuccess = json_decode($hcaptchaResult)->success ?? false;

        if (!$hcaptchaSuccess) {
            $error = 'CAPTCHA failed. Please try again.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format.';
        } elseif (!($user = findUserByEmail($email))) {
            $error = 'No account found with that email.';
        } else {
            $_SESSION['reset_email'] = $email;
            $step = 2;
        }
    }

    // Step 2: Reset password
    if (isset($_POST['reset']) && isset($_SESSION['reset_email'])) {
        $newPassword = $_POST['new_password'];
        if (strlen($newPassword) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            $email = $_SESSION['reset_email'];
            $users = loadUsers();
            foreach ($users as &$u) {
                if ($u['email'] === $email) {
                    $u['password'] = password_hash($newPassword, PASSWORD_DEFAULT);
                    break;
                }
            }
            saveUsers($users);
            unset($_SESSION['reset_email']);
            $success = "Password successfully updated. <br> You can now login again.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Reset Password - FriendNest</title>
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
            color: white;
        }

        .auth-box input {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
            text-align: center;
            margin-bottom: 10px;
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
        }

        .auth-box button:hover {
            background: #2e47d3;
        }

        .auth-box .error {
            color: red;
            margin-bottom: 10px;
        }

        .auth-box .success {
            color: green;
            margin-bottom: 10px;
        }

        .formIndex {
            background-color: white;
            border-radius: 10px;
            margin: 10px 10px 10px 0px;
        }

        .h-captcha {
            text-align: center; 
        }

        .captcha-container {
        width: 100%;
        max-width: 320px; /* or whatever works for your layout */
        display: flex;
        justify-content: center;
        align-items: center;
        transform: scale(1);     /* scale down if needed */
        transform-origin: 0 0;
        transform-origin: center;
        }

        @media (max-width: 480px) {
        .captcha-container {
            transform: scale(0.85);   /* smaller scale on very small screens */
            transform-origin: center;
        }
        }

        .auth-box p a {
            color: white;
            text-decoration: none;
        }
    </style>
    <script src="https://js.hcaptcha.com/1/api.js" async defer></script>
</head>
<body>
<div class="auth-box">
    <a href="/"><img src="assets/images/logo.png" alt="FriendNest Logo" class="logo"></a>
    <h1>RESET PASSWORD</h1>
    <div class="formIndex">
    <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p class="success"><?= $success ?></p><?php endif; ?>

    <?php if ($step === 1): ?>
        <form method="post">
            <input type="email" name="email" placeholder="Enter your email" required value="<?= htmlspecialchars($email) ?>">
            <div class="captcha-container"><div class="h-captcha" data-sitekey="9b38923a-de2a-4702-86a0-f2aa30b46c68"></div></div>
            <button style="margin-top: 5px;" type="submit" name="verify">Verify Email</button>
        </form>
    <?php elseif ($step === 2): ?>
        <form method="post">
            <input type="password" name="new_password" placeholder="New Password" required>
            <button style="margin-top: 5px;" type="submit" name="reset">Reset Password</button>
        </form>
    <?php endif; ?>
    </div>

    <p style="color:white"><a href="index.php"><b>⬅️ Back</b></a></p>
</div>
</body>
<?php include __DIR__ . '/includes/footer.php'; ?>
</html>
