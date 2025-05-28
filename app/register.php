<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/config.php';

if (isset($_SESSION['user'])) {
    header('Location: home.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $birthday = trim($_POST['birthday']);
    $gender = trim($_POST['gender']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    $hcaptchaSecret = ''; // SECRET KEY HERE
    $hcaptchaResponse = $_POST['h-captcha-response'] ?? '';
    $remoteIp = $_SERVER['REMOTE_ADDR'];

    $data = [
        'secret' => $hcaptchaSecret,
        'response' => $hcaptchaResponse,
        'remoteip' => $remoteIp,
    ];

    $verifyUrl = 'https://hcaptcha.com/siteverify';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $verifyUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $hcaptchaResult = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($hcaptchaResult);

    if (!$hcaptchaResult || !$responseData || empty($responseData->success)) {
        $error = 'Captcha verification failed. Please try again.';
    } else {
        // CAPTCHA passed — now validate form
        if (!$username || !$email || !$password || !$firstName || !$lastName || !$birthday || !$gender) {
            $error = "All fields are required.";
        } elseif (findUserByUsername($username) || findUserByEmail($email)) {
            $error = "Username or email already exists.";
        } else {
            $users = loadUsers();
            $newUser = [
                "id" => uniqid("user"),
                "first_name" => $firstName,
                "last_name" => $lastName,
                "birthday" => $birthday,
                "gender" => $gender,
                "username" => $username,
                "email" => $email,
                "password" => password_hash($password, PASSWORD_DEFAULT),
                "disabled" => false,
                "avatar" => "default.png",
                "bio" => "",
                "role" => "user",
            ];
            $users[] = $newUser;
            saveUsers($users);
            $_SESSION['user'] = $newUser;
            header("Location: index.php");
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - FriendNest</title>
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

.auth-box input
.auth-box input[type="date"],
.auth-box select {
    width: 100%;
    padding: 10px;
    margin: 8px 0;
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
    margin-top: 5px;
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
    margin-bottom: 5px;
    color: red;
}

        .name-row {
        display: flex;
        gap: 5px;
        margin-bottom: px;
        }

        .name-row .input-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .name-row input[type="date"],
        .name-row select {
            margin-top: 5px;
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border-radius: 10px;
            border: 1px solid #ccc;
            font-size: 15px;
            text-align: center;
        }

        .name-row label {
            color: grey; /* optional, adjusts color */
        }
    </style>
</head>
<body>
<div class="page-container">
    <div class="auth-box">
        <a href="/"><img src="assets/images/logo.png" alt="FriendNest Logo" class="logo"></a>
        <h1>REGISTER</h1>
        <div class="formIndex">
        <form method="post">
        <?php if ($error): ?><p class="error"><?= $error ?></p><?php endif; ?>
            <div class="name-row">
                <div class="input-group">
            <input type="text" name="first_name" placeholder="First Name" autocomplete="on">
            </div>
                <div class="input-group">
            <input type="text" name="last_name" placeholder="Last Name" autocomplete="on">
            </div>
        </div>
        <div class="name-row">
            <div class="input-group">
            <label><i>Birthday</i></label> <input type="date" name="birthday" placeholder="Birthday">
        </div>
            <div class="input-group">
            <label><i>Gender</i></label> <select name="gender">
                    <option value="">Select Here</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
        </div>
            <input type="text" name="username" placeholder="Username" autocomplete="on">
            <input type="email" name="email" placeholder="Email" autocomplete="on">
            <input type="password" name="password" placeholder="Password" autocomplete="on">
            <script src='https://js.hcaptcha.com/1/api.js' async defer></script>
            <div class="h-captcha" data-sitekey="9b38923a-de2a-4702-86a0-f2aa30b46c68"></div>
            <button type="submit">Register</button>
        </form>
        </div>
        <p style="color:white">Already have an account? <b><a href="index.php">Login</a></b></p>
    </div>
        </div>
        
</body>
<?php include __DIR__ . '/includes/footer.php'; ?>
</html>
