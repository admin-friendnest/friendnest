<?php
require_once 'includes/auth.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$user = $_SESSION['user'];
$users = loadUsers();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio = htmlspecialchars($_POST['bio']);
    $avatar = $_FILES['avatar'];
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $birthday = trim($_POST['birthday']);
    $gender = trim($_POST['gender']);

    foreach ($users as &$u) {
        if ($u['id'] === $user['id']) {
            $u['bio'] = $bio;
            $u['first_name'] = $firstName;
            $u['last_name'] = $lastName;
            $u['birthday'] = $birthday;
            $u['gender'] = $gender;

            if ($avatar && $avatar['tmp_name']) {
                $ext = pathinfo($avatar['name'], PATHINFO_EXTENSION);
                $filename = 'avatar_' . $user['id'] . '.' . $ext;
                move_uploaded_file($avatar['tmp_name'], 'assets/images/' . $filename);
                $u['avatar'] = $filename;
            }

            $_SESSION['user'] = $u;
            $_SESSION['success_message'] = "Profile updated!";
            break;
        }
    }

    saveUsers($users);

     // ✅ Redirect to home page
    header("Location: profile.php");
    exit;
}

if (isset($_SESSION['user'])) {
    $freshUserData = findUserByUsername($_SESSION['user']['username']);

    if (isUserDisabled($freshUserData)) {
        session_destroy();
        header('Location: index.php?error=disabled');
        exit;
    } else {
        // Update session with fresh user data to reflect any changes
        $_SESSION['user'] = $freshUserData;
    }
}
?>
<?php require_once 'includes/auth.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Profile - FriendNest</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#007bff">
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php require_once 'includes/header.php'; ?>
    <style>
        .edit-container {
            max-width: 500px;
            margin-top: 10px;
            margin-left: auto;
            margin-right: auto;
            margin-bottom: 0px;
            background: #78b9ff;
            padding: 20px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            font-family: 'Segoe UI', sans-serif;
            align-items: center;
            text-align: center;
        }
        .edit-container h2 {
            text-align: center;
            color: rgb(255, 255, 255);
            margin-bottom: 10px;
        }
        .edit-container label {
            margin-top: 10px;
            font-weight: 300;
            color: #555;
        }

        .edit-container input,
        .edit-container input[type="date"],
        .edit-container select,
        .edit-container textarea {
            width: 100%;
            max-width: 100%;
            margin-top: 10px;
            margin-bottom: 10px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-sizing: border-box;
            text-align: center;
        }

        .name-row {
        display: flex;
        gap: 5px;
        margin-bottom: 5px;
        }

        .name-row .input-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .name-row label {
            margin-bottom: 1px;
            font-weight: 300;
            color: #333; /* optional, adjusts color */
        }

        .edit-container img {
            display: block;
            margin: 0 auto 15px;
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
            align-items:center; 
        }
        .edit-container button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 12px 20px;
            width: 100%;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.3s ease;
        }
        .edit-container button:hover {
            background-color: #0056b3;
        }

        .success-message {
            color: green;
            text-align: center;
            margin-bottom: 10px;
            font-weight: bold;
        }

    </style>
</head>
<body>

<div class="edit-container">
    <h2>Edit Profile</h2>
    <div class="editProfileForm">
    <form method="post" enctype="multipart/form-data">
        <?php if (!empty($_SESSION['success_message'])): ?>
    <p class="success-message"><?= $_SESSION['success_message'] ?></p>
    <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>
        <br><img src="assets/images/<?= htmlspecialchars($user['avatar']) ?>?v=<?= time() ?>" alt="Avatar">
        <h4 style="color:#007bff;text-align:center; font-weight:bold; margin-top:10px; margin-bottom: 10px;">
        @<?= htmlspecialchars($user['username']) ?>
    </h4>

        <label>Change Avatar Pic</label>
        <input type="file" name="avatar"><br>

    <div class="name-row">
        <div class="input-group">
        <label>First Name</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>">
        </div>
        <div class="input-group">
        <label>Last Name</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
        </div>
    </div>

    <div class="name-row">
        <div class="input-group">
        <label>Birthday</label>
        <input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday'] ?? '') ?>">
        </div>
        
        <div class="input-group">
        <label>Gender</label>
        <select name="gender">
            <option value="">Select Gender</option>
            <option value="Male" <?= ($user['gender'] ?? '') === 'Male' ? 'selected' : '' ?>>Male</option>
            <option value="Female" <?= ($user['gender'] ?? '') === 'Female' ? 'selected' : '' ?>>Female</option>
            <option value="Other" <?= ($user['gender'] ?? '') === 'Other' ? 'selected' : '' ?>>Other</option>
        </select>
        </div>
    </div>

        <label>Bio</label>
        <textarea name="bio"><?= htmlspecialchars($user['bio']) ?></textarea>

        <button type="submit">Save</button>
    </form>
        </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>