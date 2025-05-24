<?php
require_once 'includes/functions.php';

if (!isset($_GET['username'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing username"]);
    exit;
}

$username = $_GET['username'];
$users = loadUsers();

// Load followers data from a JSON file
$followersFile = '../data/followers.json';
$followersData = file_exists($followersFile) ? json_decode(file_get_contents($followersFile), true) : [];
$followersData = [];
if (file_exists($followersFile)) {
    $json = file_get_contents($followersFile);
    $followersData = json_decode($json, true);
    if (!is_array($followersData)) {
        error_log("followers.json is malformed or not an array.");
        $followersData = [];
    }
}
$followerCount = isset($followersData[$username]) ? count($followersData[$username]) : 0;

foreach ($users as $user) {
    if ($user['username'] === $username) {
        header('Content-Type: application/json');
        echo json_encode([
            'username' => $user['username'],
            'avatar' => $user['avatar'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'birthday' => $user['birthday'],
            'gender' => $user['gender'],
            'bio' => $user['bio'] ?? '',
            'followers' => $followerCount
        ]);
        exit;
    }
}

http_response_code(404);
echo json_encode(["error" => "User not found"]);
