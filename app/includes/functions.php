
<?php

function loadUsers() {
    $usersFile = __DIR__ . '/../data/users.json';
    if (!file_exists($usersFile)) {
        file_put_contents($usersFile, '[]');
    }
    $json = file_get_contents($usersFile);
    return json_decode($json, true);
}

function saveUsers($users) {
    $usersFile = __DIR__ . '/../data/users.json';
    file_put_contents($usersFile, json_encode($users, JSON_PRETTY_PRINT));
}

function findUserByUsername($username) {
    $users = loadUsers();
    foreach ($users as $user) {
        if ($user['username'] === $username) return $user;
    }
    return null;
}

function findUserByEmail($email) {
    $users = loadUsers();
    foreach ($users as $user) {
        if ($user['email'] === $email) return $user;
    }
    return null;
}

function getUserById($id) {
    $users = loadUsers();
    foreach ($users as $user) {
        if ($user['id'] === $id) return $user;
    }
    return null;
}

function isUserDisabled($user) {
    return isset($user['disabled']) && $user['disabled'] === true;
}

function loadAllUsers() {
    $usersDir = __DIR__ . '/data/';
    $users = [];

    foreach (glob($usersDir . '*.json') as $file) {
        $users[] = json_decode(file_get_contents($file), true);
    }

    return $users;
}

function countFollowers($username) {
    $jsonPath = __DIR__ . '/../data/followers.json'; // Adjust path if needed
    if (!file_exists($jsonPath)) return 0;

    $data = json_decode(file_get_contents($jsonPath), true);
    $count = 0;

    foreach ($data as $follower => $followingList) {
        // Normalize: associative or indexed array
        $userFollowings = is_array($followingList) ? array_values($followingList) : [];

        if (in_array($username, $userFollowings)) {
            $count++;
        }
    }

    return $count;
}
?>