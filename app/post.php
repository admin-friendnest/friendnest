<?php
session_start();
require_once 'includes/functions.php';
require_once 'includes/post_functions.php';

$content = trim($_POST['content']);
$audience = $_POST['audience'] ?? 'public'; // default to public
$username = $_SESSION['user']['username'];
$userId = $_SESSION['user']['id'];

if ($content !== '') {
    $post = [
        'id' => uniqid('post_'),
        'user_id' => $userId,
        'username' => $username,
        'content' => $content,
        'timestamp' => date('Y-m-d H:i'),
        'likes' => [],
        'audience' => $audience, // save audience setting
    ];

    savePost($post);

    // Redirect based on audience
    $redirectFeed = ($audience === 'public') ? 'suggested' : 'followed';
    header("Location: home.php?feed=$redirectFeed");
    exit;
}
header("Location: home.php?error=empty");
