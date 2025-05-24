<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/post_functions.php';

if (!isset($_SESSION['user']) || strtolower($_SESSION['user']['role'] ?? '') !== 'admin') {
    die("Unauthorized");
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $users = loadUsers();

    foreach ($users as &$user) {
        if ($user['id'] === $id) {
            // Toggle disabled status
            $user['disabled'] = !(!empty($user['disabled']) && $user['disabled'] === true);

            // If disabling, soft-hide user's posts
            if ($user['disabled'] === true) {
                $posts = loadPosts();
                foreach ($posts as &$post) {
                    if ($post['user_id'] === $id) {
                        $post['hidden'] = true; // soft-delete
                    }
                }
                savePosts($posts);
            }

            // If enabling, unhide user's posts
            else {
                $posts = loadPosts();
                foreach ($posts as &$post) {
                    if ($post['user_id'] === $id && isset($post['hidden']) && $post['hidden'] === true) {
                        unset($post['hidden']);
                    }
                }
                savePosts($posts);
            }

            break;
        }
    }

    saveUsers($users);
}

header("Location: users.php");
exit;