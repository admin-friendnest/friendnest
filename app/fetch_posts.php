<?php
require_once 'includes/auth.php';
require_once 'includes/post_functions.php';
require_once 'includes/functions.php';

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

$latestPostId = !empty($displayedPosts) ? $displayedPosts[0]['id'] : null;

$filter = $_GET['feed'] ?? 'suggested';
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$postsPerPage = 7;

$allPosts = array_reverse(loadPosts());

$posts = array_filter($allPosts, function($post) use ($filter) {
    $user = getUserById($post['user_id']);
    if (!$user || isUserDisabled($user)) return false;

    $audience = $post['audience'] ?? 'public';

    if ($filter === 'suggested') {
        return $audience === 'public';
    }

    if ($filter === 'followed') {
        if (!isset($_SESSION['user'])) return false;
        $currentUsername = $_SESSION['user']['username'];
        $followers = json_decode(file_get_contents('data/followers.json'), true);
        $following = $followers[$currentUsername] ?? [];
        return $audience === 'followers' && (
            in_array($user['username'], $following) || $user['username'] === $currentUsername
        );
    }

    return true;
});

$totalPosts = count($posts);
$totalPages = ceil($totalPosts / $postsPerPage);
$startIndex = ($currentPage - 1) * $postsPerPage;
$displayedPosts = array_slice($posts, $startIndex, $postsPerPage);

foreach ($displayedPosts as $post):
    $postUser = getUserById($post['user_id']);
    $avatar = htmlspecialchars($postUser['avatar'] ?? 'default.png');
    $username = htmlspecialchars($post['username']);
    $audience = $post['audience'] ?? 'public';
    $audienceLabel = $audience === 'followers' ? '👥 Followers Only' : '🌐 Public';
    $timestamp = $post['timestamp'];
    $content = nl2br(htmlspecialchars($post['content']));
    $postId = $post['id'];
    $likes = is_array($post['likes']) ? $post['likes'] : [];
    $likesCount = count($likes);

    $currentUser = $_SESSION['user']['username'] ?? '';
    $youLiked = in_array($currentUser, $likes);
    $firstLiker = '';
    foreach ($likes as $liker) {
        if ($liker !== $currentUser) {
            $firstLiker = $liker;
            break;
        }
    }

    $activeLikers = array_filter($likes, function($likerUsername) {
        $user = findUserByUsername($likerUsername);
        return $user && !isUserDisabled($user);
    });

    $popupData = "<span class='likers-popup-trigger' data-likers='" . 
        htmlspecialchars(json_encode(array_values($activeLikers))) . "'>";

    if ($youLiked && $firstLiker) {
        if ($likesCount === 2) {
            $likeText = "Liked by You and @$firstLiker";
        } elseif ($likesCount === 3) {
            $likeText = "Liked by You, @$firstLiker and {$popupData}the other</span>";
        } else {
            $likeText = "Liked by You, @$firstLiker and {$popupData}the others</span>";
        }
    } elseif ($firstLiker) {
        if ($likesCount === 1) {
            $likeText = "Liked by @$firstLiker";
        } elseif ($likesCount === 2) {
            $likeText = "Liked by @$firstLiker and 1 {$popupData}other</span>";
        } elseif ($likesCount === 3) {
            $likeText = "Liked by @$firstLiker and {$popupData}the other</span>";
        } else {
            $likeText = "Liked by @$firstLiker and {$popupData}the others</span>";
        }
    } elseif ($youLiked) {
        $likeText = "Liked by You";
    } else {
        $likeText = "Be the first to like this";
    }

    if (isset($_GET['checkNewOnly']) && $_GET['checkNewOnly'] == '1') {
    $filteredPosts = array_values(array_filter($allPosts, function($post) use ($filter) {
        $user = getUserById($post['user_id']);
        if (!$user || isUserDisabled($user)) return false;

        $audience = $post['audience'] ?? 'public';

        if ($filter === 'suggested') {
            return $audience === 'public';
        }

        if ($filter === 'followed') {
            if (!isset($_SESSION['user'])) return false;
            $currentUsername = $_SESSION['user']['username'];
            $followers = json_decode(file_get_contents('data/followers.json'), true);
            $following = $followers[$currentUsername] ?? [];
            return $audience === 'followers' && (
                in_array($user['username'], $following) || $user['username'] === $currentUsername
            );
        }

        return true;
    }));

    $latestPostId = count($filteredPosts) > 0 ? $filteredPosts[0]['id'] : null;

    echo json_encode(['latestPostId' => $latestPostId]);
    exit;
}

    ?>

    

    <div id="post-<?= $postId ?>" class="post-item" data-id="<?= $postId ?>" style="background:#fff;padding:15px;border-radius:10px;margin-bottom:15px;box-shadow:0 2px 5px rgba(0,0,0,0.05);">
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 5px;">
            <img src="assets/images/<?= $avatar ?>?v=<?= time() ?>"
                class="user-trigger"
                data-username="<?= $username ?>"
                style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:5px;cursor:pointer;">
            <p class="user-trigger" 
                data-username="<?= $username ?>"
                style="margin:0;font-weight:bold;color:#007bff;text-align:center;cursor:pointer;">
                @<?= $username ?>
            </p>
            <p style="font-size:12px;color:gray;"><?= $timestamp ?> <span style="color:#007bff;">• <?= $audienceLabel ?></span></p>
            <p><?= $content ?></p>
        </div>

        <div style="display:flex; gap:10px; align-items:center; margin-top:5px;">
            <form action="like.php" method="GET">
                <input type="hidden" name="post" value="<?= $postId ?>">
                <input type="hidden" name="feed" value="<?= htmlspecialchars($filter) ?>">
                <input type="hidden" name="page" value="<?= $currentPage ?>">
                <button type="submit" style="padding:6px 10px; font-size:14px; background:#007bff; color:white; border:none; border-radius:6px; cursor:pointer;">
                    <b>Like ❤︎</b> (<span class="like-count" id="like-count-<?= $postId ?>"><?= $likesCount ?></span>)
                </button>
            </form>

            <?php if ($currentUser === $username): ?>
                <form action="delete_post.php" method="POST">
                    <input type="hidden" name="post_id" value="<?= $postId ?>">
                    <input type="hidden" name="feed" value="<?= htmlspecialchars($filter) ?>">
                    <input type="hidden" name="page" value="<?= $currentPage ?>">
                    <button type="submit" class="small-button" title="Delete Post"
                        style="background: #ff3a3a; color:white; border:none; border-radius:6px; padding:6px 10px; font-size:14px; cursor:pointer;">
                        Delete ✖ 
                    </button>
                </form>
            <?php endif; ?>
        </div>
          
        <p style="margin-top: 5px; font-size: 11px; color: #555;">
            <?= $likeText ?>
        </p>
    </div>

<?php endforeach; ?>

<!-- Profile Popup -->
<div id="profilePopup" style="display:none; position:absolute; z-index:9999; background:white; border:1px solid #ccc; border-radius:10px; padding:15px; width:250px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
    <img id="popupAvatar" src="" style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:5px;">
    <p id="popupUsername" style="margin:0;font-weight:bold;color:#007bff;"></p>
    <p id="popupFullName" style="margin:0;font-size:13px;color:#555;"></p>
    <p id="popupBirthday" style="margin:0;font-size:13px;color:#555;"></p>
    <p id="popupGender" style="margin:0;font-size:13px;color:#555;"></p>
    <p id="popupBio" style="margin:0;margin-top:5px;font-size:13px;color:#555;"></p>
    <form id="editProfileForm" action="profile.php" method="get" style="display:none; margin-top: 10px;">
        <button type="submit" style="padding:6px 10px;font-size:13px;background:#007bff;color:white;border:none;border-radius:6px;cursor:pointer;">Edit Profile ✎</button>
    </form>
    <input type="hidden" id="targetUser" value="">
    <button id="followBtn" style="margin-top:10px;padding:6px 10px;background:#28a745;color:white;border:none;border-radius:6px;font-size:13px;display:none;">Follow</button>
</div>

<!-- Likers Popup -->
<div id="likersPopup" style="display:none; position:absolute; z-index:9999; background:white; border:1px solid #ccc; border-radius:10px; padding:10px; width:220px; box-shadow:0 2px 10px rgba(0,0,0,0.1);">
    <h4 style="margin:0 0 10px; font-size:14px; color:#007bff;">Liked by</h4>
    <ul id="likersList" style="padding-left:15px; font-size:13px; color:#333;"></ul>
</div>

<script>
// User Profile Popup
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.user-trigger').forEach(el => {
        el.addEventListener('click', async (e) => {
            e.stopPropagation();
            const username = e.currentTarget.getAttribute('data-username');
            const popup = document.getElementById('profilePopup');

            try {
                const res = await fetch(`get_user.php?username=${encodeURIComponent(username)}`);
                const user = await res.json();

                if (!user || !user.username) return;

                const loggedInUser = <?= isset($_SESSION['user']['username']) ? json_encode($_SESSION['user']['username']) : 'null' ?>;
                const followBtn = document.getElementById('followBtn');
                const editForm = document.getElementById('editProfileForm');
                const targetUser = user.username;

                document.getElementById('targetUser').value = targetUser;
                document.getElementById('popupAvatar').src = `assets/images/${user.avatar}`;
                document.getElementById('popupUsername').innerText = '@' + user.username;
                document.getElementById('popupFullName').innerText = user.first_name + ' ' + user.last_name;
                document.getElementById('popupBirthday').innerText = '🎂 ' + user.birthday;
                document.getElementById('popupGender').innerText = '👤 ' + user.gender;
                document.getElementById('popupBio').innerText = user.bio ? '📝 ' + user.bio : '';
                

                const rect = e.target.getBoundingClientRect();
                let top = window.scrollY + rect.bottom + 10;
                let left = rect.left + rect.width / 2 - 125;

                if (left + 250 > window.innerWidth) left = window.innerWidth - 260;
                if (left < 10) left = 10;

                popup.style.top = `${top}px`;
                popup.style.left = `${left}px`;
                popup.style.display = 'block';

                if (targetUser === loggedInUser) {
                    followBtn.style.display = 'none';
                    editForm.style.display = 'block';
                } else {
                    editForm.style.display = 'none';
                    followBtn.style.display = 'block';
                    const followStatus = await fetch(`follow_status.php?target=${targetUser}`);
                    const isFollowing = await followStatus.json();
                    followBtn.innerText = isFollowing ? 'Unfollow ✖' : 'Follow ➕';

                    followBtn.onclick = async () => {
                        const action = isFollowing ? 'unfollow' : 'follow';
                        await fetch('follow_action.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ target: targetUser, action })
                        });
                        popup.style.display = 'none';
                    };
                }
            } catch (err) {
                console.error('Failed to fetch user profile:', err);
            }
        });
    });

    // Likers Popup
    document.querySelectorAll('.likers-popup-trigger').forEach(el => {
        el.addEventListener('click', e => {
            e.stopPropagation();
            const likers = JSON.parse(el.getAttribute('data-likers') || '[]');
            const list = document.getElementById('likersList');
            list.innerHTML = '';

            likers.forEach(user => {
                const li = document.createElement('li');
                li.textContent = '@' + user;
                list.appendChild(li);
            });

            const rect = el.getBoundingClientRect();
            let top = window.scrollY + rect.bottom + 10;
            let left = rect.left + rect.width / 2 - 110;

            if (left + 220 > window.innerWidth) left = window.innerWidth - 230;
            if (left < 10) left = 10;

            const popup = document.getElementById('likersPopup');
            popup.style.top = `${top}px`;
            popup.style.left = `${left}px`;
            popup.style.display = 'block';
        });
    });

    // Close popups on outside click
    document.addEventListener('click', e => {
        const profilePopup = document.getElementById('profilePopup');
        const likersPopup = document.getElementById('likersPopup');

        if (!profilePopup.contains(e.target)) profilePopup.style.display = 'none';
        if (!likersPopup.contains(e.target)) likersPopup.style.display = 'none';
    });

    // Optional: hide on scroll
    window.addEventListener('scroll', () => {
        document.getElementById('profilePopup').style.display = 'none';
        document.getElementById('likersPopup').style.display = 'none';
    });
});
</script>