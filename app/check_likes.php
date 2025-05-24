<?php
require_once 'includes/post_functions.php';

$latestLikeCounts = [];

$posts = loadPosts();

foreach ($posts as $post) {
    $latestLikeCounts[$post['id']] = count($post['likes']);
}

header('Content-Type: application/json');
echo json_encode($latestLikeCounts);
