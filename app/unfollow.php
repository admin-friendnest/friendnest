<?php
require_once 'includes/auth.php';

if (!isset($_SESSION['user']) || !isset($_GET['target'])) {
    http_response_code(400);
    exit('Invalid request.');
}

$currentUser = $_SESSION['user']['username'];
$target = $_GET['target'];

$path = 'data/followers.json';
$followers = file_exists($path) ? json_decode(file_get_contents($path), true) : [];

if (isset($followers[$currentUser])) {
    $followers[$currentUser] = array_values(array_filter(
        $followers[$currentUser], fn($u) => $u !== $target
    ));
}

file_put_contents($path, json_encode($followers));
header("Location: profile.php?user=$target");
