<?php
require_once 'includes/auth.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/post_functions.php';

$user = $_SESSION['user'];
$users = loadUsers();
$success = '';
$error = '';

//Disabled Account instant logout
if (isset($_SESSION['user'])) {
    $freshUserData = findUserByUsername($_SESSION['user']['username']);

    if (isUserDisabled($freshUserData)) {
        session_destroy();
        header('Location: index.php?user=banned');
        $error = "Your account has been banned.";
        exit;
    } else {
        // Update session with fresh user data to reflect any changes
        $_SESSION['user'] = $freshUserData;
    }
}

// Email Update
if (isset($_POST['change_email'])) {
    $newEmail = trim($_POST['email']);
    if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        foreach ($users as &$u) {
            if ($u['id'] === $user['id']) {
                $u['email'] = $newEmail;
                $_SESSION['user'] = $u;
                $success = "Email updated successfully.";
                break;
            }
        }
        saveUsers($users); // ✅ make sure this is called OUTSIDE the loop
    }
}

// Password Update
if (isset($_POST['change_password'])) {
    $newPass = $_POST['password'];
    if (strlen($newPass) < 6) {
        $error = "Password too short.";
    } else {
        foreach ($users as &$u) {
            if ($u['id'] === $user['id']) {
                $u['password'] = password_hash($newPass, PASSWORD_DEFAULT);
                $_SESSION['user'] = $u;
                $success = "Password updated.";
                break;
            }
        }
        saveUsers($users);
    }
}


if (isset($_POST['delete_account'])) {
    // Delete user
    $users = array_filter($users, fn($u) => $u['id'] !== $user['id']);
    saveUsers($users);

    // Delete posts by this user
    $posts = loadPosts();
    $posts = array_filter($posts, fn($p) => $p['user_id'] !== $user['id']);
    savePosts(array_values($posts));

    // Optional: remove avatar
    $avatarPath = 'assets/images/' . $user['avatar'];
    if ($user['avatar'] !== 'default.png' && file_exists($avatarPath)) {
        unlink($avatarPath);
    }

    session_destroy();
    header("Location: index.php?status=deleted");
    exit;
}

?>
<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings - FriendNest</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once 'includes/header.php'; ?>
    <style>
        .edit-container {
            max-width: 500px;
            margin: 10px 20px;
            margin-bottom: 0px;
            background: #78b9ff;
            padding: 20px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            font-family: 'Segoe UI', sans-serif;
            align-items: center;
            text-align: center;
        }

        .edit-container img {
            display: block;
            margin: 0 auto 0px;
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            align-items:center; 
        }
    </style>
</head>
<body>

<div class="edit-container" style="max-width:600px;margin:20px auto;margin-bottom:0px;">
    <h2 style="color:white;">Account Settings</h2>
    <img src="assets/images/<?= htmlspecialchars($user['avatar']) ?>?v=<?= time() ?>" alt="Avatar">
        <h4 style="color:white;text-align:center; font-weight:bold; margin-bottom: 10px;">
        @<?= htmlspecialchars($user['username']) ?>
    </h4>
    <?php if ($success): ?><p style="color:green"><?= $success ?></p><?php endif; ?>
    <?php if ($error): ?><p style="color:red"><?= $error ?></p><?php endif; ?>
        <div class="settingsForm">
    <form method="post">
        <h3>Change Email</h3>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>">
        <button name="change_email" type="submit">Update Email</button>
    </form>
    </div>
            <br>
        <div class="settingsForm">
    <form method="post">
        <h3>Change Password</h3>
        <input type="password" name="password" placeholder="New Password">
        <button name="change_password" type="submit">Update Password</button>
    </form>
    </div>
            <br>
        <div class="settingsForm">
    <form method="post" onsubmit="return confirm('Are you sure? This cannot be undone.');">
        <h3>Delete Account</h3>
        <button name="delete_account" type="submit" style="background:#e74c3c;">Delete My Account</button>
    </form>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>

