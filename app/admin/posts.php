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

$posts = json_decode(file_get_contents(__DIR__ . '/../data/posts.json'), true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Posts</title>
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
    <h2>All Posts</h2>
    <div class="log-container">
        <table>
            <thead>
                <tr><th>ID</th><th>Username</th><th>Content</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $index => $post): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($post['username']) ?></td>
                        <td><?= htmlspecialchars($post['content']) ?></td>
                        <td><a href="delete_post.php?index=<?= $index ?>" onclick="return confirm('Delete this post ?')">Delete</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
