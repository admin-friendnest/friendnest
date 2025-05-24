<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (
    !isset($_SESSION['user']) ||
    !isset($_SESSION['user']['role']) ||
    strtolower($_SESSION['user']['role']) !== 'admin'
) {
    header('Location: index.php');
    exit;
}


$users = loadUsers();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <img src="../assets/images/logo.png" class="logo" alt="Logo">
        <nav class="main-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="users.php">Users ☻</a>
            <a href="posts.php">Posts ✎</a>
            <a href="logs.php">Login Logs ℹ</a>
            <a href="../logout.php" class="logout">Logout ➜</a>
        </nav>
    </header>
    <h2>All Users</h2>
    <div class="log-container">
        <table>
            <thead>
                <tr><th>ID</th><th>Username</th><th>Email</th><th>Action</th></tr>
            </thead>
                <tbody>
    <?php foreach ($users as $user): ?>
        <tr>
            <td><?= htmlspecialchars($user['id']) ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= htmlspecialchars($user['email']) ?></td>
            <td>
                <?php if (isset($user['role']) && strtolower($user['role']) === 'admin'): ?>
                    <b>Admin</b>
                <?php else: ?>
                    <a href="toggle_user.php?id=<?= $user['id'] ?>">
                        <?= !empty($user['disabled']) && $user['disabled'] === true ? 'Unban' : 'Ban' ?>
                    </a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
        </table>
    </div>
</body>
</html>
