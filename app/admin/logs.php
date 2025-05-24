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

$logs = array_reverse(json_decode(file_get_contents(__DIR__ . '/../data/login_logs.json'), true));
?>
<link rel="stylesheet" href="../assets/css/style.css">
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
<h2>Login Logs</h2>
<div class="log-container">
<table id="log-table">
    <tr><th>User ID</th><th>Username</th><th>IP</th><th>Device</th><th>Location</th><th>Time</th></tr>
    <?php foreach ($logs as $log): ?>
    <tr>
        <td><?= htmlspecialchars($log['user_id']) ?></td>
        <td><?= htmlspecialchars($log['username']) ?></td>
        <td><?= htmlspecialchars($log['ip']) ?></td>
        <td><?= htmlspecialchars($log['device']) ?></td>
        <td><?= htmlspecialchars($log['location']) ?></td>
        <td><?= htmlspecialchars($log['time']) ?></td>
    </tr>
    <?php endforeach; ?>
    <script>
let lastLogCount = <?= count($logs) ?>;

setInterval(() => {
    fetch('check_logs.php')
        .then(response => response.json())
        .then(data => {
            if (data.count > lastLogCount) {
                location.reload(); // reload page if there's a new log
            }
        })
        .catch(err => console.error('Check failed:', err));
}, 2000); // check every 2 seconds
</script>

</table>
    </div>
