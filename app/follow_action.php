<?php
session_start();
$data = json_decode(file_get_contents("php://input"), true);
$target = $data['target'];
$action = $data['action'];
$currentUser = $_SESSION['user']['username'];

$followers = json_decode(file_get_contents('data/followers.json'), true);

if (!isset($followers[$currentUser])) $followers[$currentUser] = [];

if ($action === 'follow' && !in_array($target, $followers[$currentUser])) {
    $followers[$currentUser][] = $target;
}
if ($action === 'unfollow') {
    $followers[$currentUser] = array_filter($followers[$currentUser], fn($u) => $u !== $target);
}

file_put_contents('data/followers.json', json_encode($followers, JSON_PRETTY_PRINT));
echo json_encode(['success' => true]);
