<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/post_functions.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo "Missing post ID.";
    exit;
}

$postId = $_GET['id'];
$posts = loadPosts();

foreach ($posts as $post) {
    if ($post['id'] === $postId) {
        $user = getUserById($post['user_id']);
        ?>
        <div id="post-<?= $post['id'] ?>" class="post-item" data-id="<?= $post['id'] ?>" style="background:#fff;padding:15px;border-radius:10px;margin-bottom:15px;box-shadow:0 2px 5px rgba(0,0,0,0.05);">
            <div style="display:flex; flex-direction:column; align-items:center;">
            <img src="assets/images/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>"
 style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
                <p style="margin:5px 0;font-weight:bold;color:#007bff;">@<?= htmlspecialchars($post['username']) ?></p>
                <?php $audienceLabel = ($post['audience'] ?? 'public') === 'followers' ? '👥 Followers Only' : '🌐 Public';
?><p style="font-size:12px;color:gray;"><?= $post['timestamp'] ?> <span style="color:#007bff;">• <?= $audienceLabel ?></span></p>
            </div>
            <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>
    
            <div style="display:flex;gap:10px;align-items:center;margin-top:5px;">
                <form action="like.php" method="GET">
                    <input type="hidden" name="post" value="<?= $post['id'] ?>">
                    <button type="submit" style="padding:6px 10px; font-size:14px; background:#007bff; color:white; border:none; border-radius:6px; cursor:pointer;">
                        <b>Like ❤︎</b> (<span class="like-count" id="like-count-<?= $post['id'] ?>"><?= count($post['likes']) ?></span>)
                    </button>
                </form>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['username'] === $post['username']): ?>
                    <form action="delete_post.php" method="POST">
                        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                         <input type="hidden" name="feed" value="<?= htmlspecialchars($filter) ?>">
                        <input type="hidden" name="page" value="<?= $currentPage ?>">
                        <button type="submit" style="background:#ff3a3a;color:white;border:none;border-radius:6px;padding:6px 10px;font-size:14px;cursor:pointer;">
                            Delete ✖
                        </button>
                    </form>
                <?php endif; ?>
            </div>
            <?php
$currentUser = $_SESSION['user']['username'];
$likers = is_array($post['likes']) ? array_values($post['likes']) : [];

$youLiked = in_array($currentUser, $likers);
$likersCount = count($likers);

$firstLiker = '';
foreach ($likers as $liker) {
    if ($liker !== $currentUser) {
        $firstLiker = $liker;
        break;
    }
}

$otherLikers = array_filter($likers, fn($u) => $u !== $currentUser && $u !== $firstLiker);
$popupData = " <span class='likers-popup-trigger' data-likers='" . 
             htmlspecialchars(json_encode($likers)) . "'>";

if ($youLiked && $firstLiker) {
    if ($likersCount === 2) {
        $likeText = "Liked by You and @$firstLiker";
    } elseif ($likersCount === 3) {
        $likeText = "Liked by You, @$firstLiker and" . $popupData . "the other</span>";
    } else {
        $likeText = "Liked by You, @$firstLiker and" . $popupData . "the others</span>";
    }
} elseif ($firstLiker) {
    if ($likersCount === 1) {
        $likeText = "Liked by @$firstLiker";
    } elseif ($likersCount === 2) {
        $likeText = "Liked by @$firstLiker and 1" . $popupData . "other</span>";
    } elseif ($likersCount === 3) {
        $likeText = "Liked by @$firstLiker and" . $popupData . "the other</span>";
    } else {
        $likeText = "Liked by @$firstLiker and" . $popupData . "the others</span>";
    }
} elseif ($youLiked) {
    $likeText = "Liked by You";
} else {
    $likeText = "Be the first to like this";
}
?>
<p style="margin-top: 5px; font-size: 11px; color: #555;">
    <?= $likeText ?>
</p>
    <div id="likersPopup" style="display:none;position:absolute;z-index:1000;background:#fff;padding:10px;border:1px solid #ccc;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.2);max-width:250px;">
    <strong>Liked by:</strong>
    <ul id="likersList" style="list-style:none;padding:0;margin:5px 0 0 0;"></ul>
</div>
        </div>
        <?php
        exit;
    }
}

http_response_code(404);
echo "Post not found.";

