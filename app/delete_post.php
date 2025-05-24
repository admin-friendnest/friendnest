<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$postId = $_POST['post_id'] ?? null;

// Capture current feed and page to redirect back correctly
$feed = $_POST['feed'] ?? 'suggested';
$page = $_POST['page'] ?? 1;

if (!$postId) {
    header("Location: home.php?feed=$feed&page=$page");
    exit;
}

// Load posts
$postsFile = __DIR__ . '/data/posts.json';
$posts = file_exists($postsFile) ? json_decode(file_get_contents($postsFile), true) : [];

// Find and remove the post
$updatedPosts = [];
$found = false;

foreach ($posts as $post) {
    if ($post['id'] == $postId) {
        if ($post['user_id'] == $user['id']) {
            $found = true; // Authorized deletion
            continue; // Skip this post to delete
        } else {
            die("Unauthorized");
        }
    }
    $updatedPosts[] = $post;
}

if ($found) {
    file_put_contents($postsFile, json_encode($updatedPosts, JSON_PRETTY_PRINT));
}

// Redirect back to the correct feed and page
header("Location: home.php?feed=$feed&page=$page");
exit;
?>
