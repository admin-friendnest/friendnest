<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/post_functions.php';

if (isset($_GET['post'])) {
    $postId = $_GET['post'];
    likePost($postId, $_SESSION['user']['username']);
}

// Get current feed and page, default if missing
$feed = $_GET['feed'] ?? 'suggested';
$page = $_GET['page'] ?? 1;

// Redirect back to the correct tab and page
header("Location: home.php?feed=$feed&page=$page");
exit;