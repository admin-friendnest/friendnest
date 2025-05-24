<?php
require_once __DIR__ . '/functions.php';

function loadPosts() {
    clearstatcache(); // Force PHP to get fresh file info
    $posts = json_decode(file_get_contents(__DIR__ . '/../data/posts.json'), true) ?? [];

    foreach ($posts as &$post) {
        if (!isset($post['audience'])) {
            $post['audience'] = 'public'; // default
        }
    }
    return $posts;
}

function savePosts($posts) {
    file_put_contents('data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));
}

function savePost($newPost) {
    $posts = loadPosts();
    $posts[] = $newPost;
    file_put_contents('data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));
}

function addPost($userId, $content) {
    require_once __DIR__ . '/functions.php'; // Make sure this is at the top
    $users = loadUsers();
    $username = '';

    foreach ($users as $u) {
        if ($u['id'] === $userId) {
            $username = $u['username'];
            break;
        }
    }

    $posts = loadPosts();
    $timezone = new DateTimeZone('Asia/Manila');
        $now = new DateTime('now', $timezone);
        $timestamp = $now->format('Y-m-d H:i:s');
        $current_week = $now->format('W');
        $current_year = $now->format('Y');
    $posts[] = [
        "id" => uniqid("post"),
        "user_id" => $userId,
        "username" => $username, // ✅ This is what was missing
        "content" => htmlspecialchars($content),
        "timestamp" => $timestamp,
        "likes" => []
    ];
    savePosts($posts);
}


function likePost($postId, $userId) {
    $posts = loadPosts();
    foreach ($posts as &$post) {
        if ($post['id'] === $postId) {
            if (!in_array($userId, $post['likes'])) {
                $post['likes'][] = $userId;
            } else {
                $post['likes'] = array_diff($post['likes'], [$userId]);
            }
            break;
        }
    }
    savePosts($posts);
}

function getUserByUsername($username) {
    $users = loadUsers(); // assumes function exists
    foreach ($users as $user) {
        if ($user['username'] === $username) return $user;
    }
    return null;
}

function loadFollowedPosts($username) {
    $posts = loadPosts();
    $followersFile = __DIR__ . '/../data/followers.json';

    if (!file_exists($followersFile)) return [];

    $followers = json_decode(file_get_contents($followersFile), true);
    $followedUsers = $followers[$username] ?? [];

    $filteredPosts = array_filter($posts, function($post) use ($followedUsers) {
        return in_array($post['username'], $followedUsers);
    });

    return array_reverse($filteredPosts);
}

?>
