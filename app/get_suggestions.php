<?php
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user'])) {
    echo json_encode([]);
    exit;
}

$currentUser = $_SESSION['user']['username'];
$users = loadUsers(); // all registered users
$followers = json_decode(file_get_contents('data/followers.json'), true);
$alreadyFollowing = $followers[$currentUser] ?? [];

$suggestions = [];

foreach ($users as $user) {
    $username = $user['username'];

    if ($username === $currentUser) continue;
    if (isUserDisabled($user)) continue;
    if (in_array($username, $alreadyFollowing)) continue;

    // Count followers for this user
    $followerCount = 0;
    foreach ($followers as $followerList) {
        if (in_array($username, $followerList)) {
            $followerCount++;
        }
    }

    $user['follower_count'] = $followerCount;

    if ($username === 'dansalcedo') {
        array_unshift($suggestions, $user);
        continue;
    }

    $suggestions[] = $user;
}

$suggestions = array_slice($suggestions, 0, 4);

header('Content-Type: application/json');
echo json_encode($suggestions);
