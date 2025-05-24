<?php
require_once 'includes/functions.php';
header('Content-Type: application/json');
header('Cache-Control: no-store');

$posts = loadPosts();
usort($posts, fn($a, $b) => strtotime($b['timestamp']) - strtotime($a['timestamp']));

echo json_encode(array_map(fn($post) => ['id' => $post['id']], $posts));