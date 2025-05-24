<?php
session_start();
$followers = json_decode(file_get_contents('data/followers.json'), true);
$currentUser = $_SESSION['user']['username'];
$target = $_GET['target'];

echo json_encode(in_array($target, $followers[$currentUser] ?? []));