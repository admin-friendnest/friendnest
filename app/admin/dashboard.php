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

?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
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
    <h2>Welcome to the Admin Panel</h2>
    <p style="text-align:center;">Use the navigation above to manage the system.</p>
</body>
</html>
