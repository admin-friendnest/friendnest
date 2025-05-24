<?php
require_once 'includes/auth.php';
require_once 'includes/post_functions.php';

header('Content-Type: application/json');

if (!isset($_GET['post_id'])) {
    echo json_encode(['error' => 'No post ID']);
    exit;
}

$postId = (int)$_GET['post_id'];
$posts = loadPosts();

foreach ($posts as $post) {
    if ($post['id'] == $postId) {
        $likerIds = $post['likes'] ?? [];
        $likers = [];

        foreach ($likerIds as $uid) {
            $user = getUserById($uid);
            if ($user) {
                $likers[] = [
                    'username' => $user['username'],
                    'avatar' => $user['avatar'] ?? 'default.png'
                ];
            }
        }

        echo json_encode([
            'content' => $post['content'],
            'likers' => $likers
        ]);
        exit;
    }
}

echo json_encode(['error' => 'Post not found']);
