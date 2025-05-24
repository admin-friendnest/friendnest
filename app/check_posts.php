<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/post_functions.php';
require_once __DIR__ . '/includes/functions.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

$allPosts = array_reverse(loadPosts()); // newest first
$filter = $_GET['feed'] ?? 'suggested';

$posts = array_filter($allPosts, function($post) use ($filter) {
    $user = getUserById($post['user_id']);
    if (!$user || isUserDisabled($user)) return false;

    $audience = $post['audience'] ?? 'public';

    if ($filter === 'suggested') {
        return $audience === 'public';
    }

    if ($filter === 'followed') {
        if (!isset($_SESSION['user'])) return false;
        $currentUsername = $_SESSION['user']['username'];
        $followers = json_decode(file_get_contents('data/followers.json'), true);
        $following = $followers[$currentUsername] ?? [];

        return $audience === 'followers' && (
            in_array($user['username'], $following) || $user['username'] === $currentUsername
        );
    }

    return true;
});

// Return just the post IDs
$postIds = array_map(fn($p) => $p['id'], $posts);
header('Content-Type: application/json');
echo json_encode($postIds);
