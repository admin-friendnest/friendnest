<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';

if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
    die("Unauthorized");
}

if (isset($_GET['index'])) {
    $posts = json_decode(file_get_contents(__DIR__ . '/../data/posts.json'), true);
    unset($posts[$_GET['index']]);
    $posts = array_values($posts);
    file_put_contents(__DIR__ . '/../data/posts.json', json_encode($posts, JSON_PRETTY_PRINT));
}

header("Location: posts.php");
exit;
