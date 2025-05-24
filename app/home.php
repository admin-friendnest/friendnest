<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/post_functions.php';
require_once __DIR__ . '/includes/functions.php'; 
require_once __DIR__ . '/includes/fetch_news.php';



header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

if (isset($_SESSION['user'])) {
    $freshUserData = findUserByUsername($_SESSION['user']['username']);

    if (isUserDisabled($freshUserData)) {
        session_destroy();
        header('Location: index.php?user=banned');
        $error = "Your account has been banned.";
        exit;
    } else {
        $_SESSION['user'] = $freshUserData;
    }
}
$newsHeadlines = getCachedNews();
$allPosts = array_reverse(loadPosts()); // newest first
$filter = $_GET['feed'] ?? 'suggested';

// Filter out posts from disabled users
$posts = array_filter($allPosts, function($post) use ($filter) {
    $user = getUserById($post['user_id']);
    if (!$user || isUserDisabled($user)) return false;

    $audience = $post['audience'] ?? 'public'; // existing posts default to public

    if ($filter === 'suggested') {
        return $audience === 'public';
    }

    if ($filter === 'followed') {
    if (!isset($_SESSION['user'])) return false;
    $currentUsername = $_SESSION['user']['username'];
    $followers = json_decode(file_get_contents('data/followers.json'), true);
    $following = $followers[$currentUsername] ?? [];

    // Show posts from followed users or own followers-only posts
    return $audience === 'followers' && (
        in_array($user['username'], $following) || $user['username'] === $currentUsername
    );
}

    return true;
});


// Pagination variables
$postsPerPage = 7;
$totalPosts = count($posts);
$totalPages = ceil($totalPosts / $postsPerPage);

// Get current page from query param
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Calculate which posts to show
$startIndex = ($currentPage - 1) * $postsPerPage;
$displayedPosts = array_slice($posts, $startIndex, $postsPerPage);

?>
<?php require_once 'includes/auth.php';?>
<!DOCTYPE html>
<html>
<head>
    <title>Home - FriendNest</title>
    <link rel="icon" type="image/png" href="assets/images/favicon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#007bff">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/style.css">
    <?php require_once 'includes/header.php'; ?>
</head>
<body>
<div class="main-content">
    <div class="sidebar" style="">
    <?php if (isset($_SESSION['user'])): ?> 
    <?php 
        $user = $_SESSION['user']; 
        require_once 'includes/functions.php'; // wherever countFollowers() is defined
        $followerCount = countFollowers($user['username']);
    ?>
    <div style="display: flex; align-items: center; background:white; padding: 10px; border-radius: 10px;">
        <img src="assets/images/<?= htmlspecialchars($user['avatar'] ?? 'default.png') ?>?v=<?= time() ?>"
             alt="User Avatar"
             style="margin: 10px; width: 70px; height: 70px; border-radius: 50%; object-fit: cover;">
        <div style="flex-grow: 1;">
            <p style="color:#007bff; font-size: 14px; font-weight: bold;">@<?= htmlspecialchars($user['username']) ?></p>
            <p style="margin: 0; font-size: 13px; color: gray;"><?= $followerCount ?> follower<?= $followerCount === 1 ? '' : 's' ?></p>
            <form action="profile.php" method="get">
                <button type="submit"  style="padding:6px 10px; font-size:14px; background:#007bff; color:white; border:none; border-radius:6px; cursor:pointer;">Edit Profile ✎</button>
            </form>
        </div>
    </div>
<?php endif; ?>
                    <div id="followSuggestions" style="text-align: center; margin-top: 20px; background: #f9f9f9; padding: 10px; border-radius: 10px;">
            <h4 style="color:#007bff; font-size: 14px; margin-bottom: 10px;">👥 Follow Suggestions</h4>
            <div id="suggestionContainer">
                <!-- Suggestions will load here via JavaScript -->
                <p style="text-align:center; color:gray;">Loading suggestions...</p>
            </div>
                        <script>
        let currentSuggestions = [];

        async function loadSuggestions() {
            try {
                const res = await fetch('get_suggestions.php');
                const data = await res.json();

                currentSuggestions = data;
                renderSuggestions();
            } catch (err) {
                console.error('Failed to load suggestions:', err);
                document.getElementById('suggestionContainer').innerHTML = '<p style="color:red;">Failed to load.</p>';
            }
        }

        function renderSuggestions() {
            const container = document.getElementById('suggestionContainer');
            container.innerHTML = ''; // Clear previous suggestions

            if (currentSuggestions.length === 0) {
                container.innerHTML = '<p style="text-align:center; color:gray;">No more suggestions for now.</p>';
                return;
            }

            const suggestionsToShow = currentSuggestions.slice(0, 4); // Show up to 4 suggestions without modifying the original array

            suggestionsToShow.forEach(user => {
                const suggestionHTML = `
                <div style="text-align:center; margin-bottom: 10px; padding: 10px; color: white; border: 1px solid #ccc; border-radius: 10px;">
                    <img src="assets/images/${user.avatar || 'default.png'}" 
                        style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:5px;">
                    <p style="margin:0;font-weight:bold;color:#007bff;">@${user.username}</p>
                    <p style="margin:4px 0; font-size:13px; color:gray;">👥 ${user.follower_count} follower${user.follower_count === 1 ? '' : 's'}</p>
                    <div style="margin-top: 10px; display: flex; justify-content: center; gap: 10px;">
                        <button onclick="handleFollow('${user.username}')" 
                            style="padding:6px 12px; background:#007bff; color:white; border:none; border-radius:6px; cursor:pointer;">
                            Follow
                        </button>
                        <button onclick="handleSkip('${user.username}')" 
                            style="padding:6px 12px; background:#ccc; color:black; border:none; border-radius:6px; cursor:pointer;">
                            Skip
                        </button>
                    </div>
                </div>
            `;
                container.innerHTML += suggestionHTML;
            });
        }

        function handleFollow(username) {
            fetch('follow_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ target: username, action: 'follow' })
            }).then(() => {
                currentSuggestions = currentSuggestions.filter(user => user.username !== username);
                renderSuggestions();
            });
        }

        function handleSkip(username) {
            currentSuggestions = currentSuggestions.filter(user => user.username !== username);
            renderSuggestions();
        }

        loadSuggestions();
        </script>
        </div>

        <div id="friendNestAd" style="max-width: 320px; width: 100%; background: #fff; border-radius: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.1); position: relative; overflow: hidden; margin: 20px 20px 0px 0px;">
    
    <!-- Header with close link -->
    <div style="padding: 4px 10px; background-color: #f1f1f1; font-size: 12px; color: #666; display: flex; justify-content: space-between; align-items: center;">
        <span>👁️‍🗨️ Ads by FriendNest</span>
        <a href="javascript:void(0);" onclick="document.getElementById('friendNestAd').style.display='none';" 
           style="text-decoration: none; font-size: 16px; color: #999;">✖</a>
    </div>

    <!-- Ad image container -->
    <div style="width: 100%; height: 120px; display: flex; align-items: center; justify-content: center; background-color: #fafafa;">
        <img src="" alt="Ad" style="max-width: 100%; max-height: 100%; object-fit: contain;">
    </div>
</div>
    
    </div>
    
    
<div class="post-box">
<div style=""> <div class="postForm">
    <form id="postForm" action="post.php" method="POST" onsubmit="return validatePost()">
            <input type="hidden" name="feed" value="<?= htmlspecialchars($filter) ?>">
    <textarea name="content" id="postContent" placeholder="What's on your mind? 💭" style="width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;height:80px;"></textarea>
    <label for="audience" style="margin-top: 10px;"></label>
<select name="audience" id="audience" style="padding: 6px; margin-left: 2px; margin-top: 10px; border-radius: 6px; border: 1px solid #ccc;">
    <option value="public">🌍 Public</option>
    <option value="followers">👥 Followers Only</option>
</select>
    <button type="submit" style="margin-top:10px;">Post ✎</button>
</form>

<script>
function validatePost() {
    const content = document.getElementById('postContent').value.trim();

    if (content === '') {
        alert('❌ Error: Post cannot be empty.');
        return false; // Prevent form submission
    }

    return true; // Allow form to submit
}
</script>
</div>

</div>

 <!-- Profile Popup -->
 <div id="profilePopup" style="
    display: none;
    position: absolute;
    z-index: 9999;
    background: white;
    border: 1px solid #ccc;
    border-radius: 10px;
    padding: 15px;
    width: 250px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    text-align: center;
">
    <img id="popupAvatar" src="" style="
        display: block;
        margin: 0 auto 10px auto;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        object-fit: cover;
    ">
    <p id="popupUsername" style="margin:0;font-weight:bold;color:#007bff;"></p>
    <p id="popupFullName" style="margin:0;font-size:13px;color:#555;"></p>
    <p id="popupBirthday" style="margin:0;font-size:13px;color:#555;"></p>
    <p id="popupGender" style="margin:0;font-size:13px;color:#555;"></p>
    <p id="popupBio" style="margin:0;margin-top:5px;font-size:13px;color:#555;"></p>
    <form id="editProfileForm" action="profile.php" method="get" style="display:none; margin-top: 10px;">
        <button type="submit" style="padding:6px 10px;font-size:13px;background:#007bff;color:white;border:none;border-radius:6px;cursor:pointer;">Edit Profile ✎</button>
    </form>
    <input type="hidden" id="targetUser" value="">
    <button id="followBtn" style="margin-top:10px;padding:6px 10px;background:#007bff;color:white;border:none;border-radius:6px;font-size:13px;display:none;">Follow</button>
    <script>
document.addEventListener('DOMContentLoaded', () => {
    const popup = document.getElementById('profilePopup');

    document.querySelectorAll('.user-trigger').forEach(el => {
        el.addEventListener('click', async (e) => {
            e.stopPropagation(); // Prevent click from closing immediately

            const username = e.currentTarget.getAttribute('data-username');

            try {
                const res = await fetch(`get_user.php?username=${encodeURIComponent(username)}`);
                const user = await res.json();
                if (!user || !user.username) return;

                const loggedInUser = <?= isset($_SESSION['user']['username']) ? json_encode($_SESSION['user']['username']) : 'null' ?>;
                const followBtn = document.getElementById('followBtn');
                const editForm = document.getElementById('editProfileForm');
                const targetUser = user.username;

                // Fill in content
                document.getElementById('targetUser').value = targetUser;
                document.getElementById('popupAvatar').src = `assets/images/${user.avatar}`;
                document.getElementById('popupUsername').innerText = '@' + user.username;
                document.getElementById('popupFullName').innerText = user.first_name + ' ' + user.last_name;
                document.getElementById('popupBirthday').innerText = '🎂 ' + user.birthday;
                document.getElementById('popupGender').innerText = '👤 ' + user.gender;
                document.getElementById('popupBio').innerText = user.bio ? '📝 ' + user.bio : '';

                // Position popup
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

    // Close popup on outside click
    document.addEventListener('click', function (e) {
        if (!popup.contains(e.target) && !e.target.classList.contains('user-trigger')) {
            popup.style.display = 'none';
        }
    });

    // Close popup on scroll
    window.addEventListener('scroll', () => {
        if (popup.style.display === 'block') {
            popup.style.display = 'none';
        }
    });

});
});
</script>
</div>


        <div class="feed-toggle">
        <a href="?feed=suggested" class="feed-tab <?= ($filter === 'suggested') ? 'active' : '' ?>">Suggested</a>
        <a href="?feed=followed" class="feed-tab <?= ($filter === 'followed') ? 'active' : '' ?>">Following</a>
        <hr style="margin-top:0px; border: 1px solid #78b9ff"> 
    </div>
    <div id="postsContainer">
    <?php foreach ($displayedPosts as $post): ?>
    <div id="post-<?= $post['id'] ?>" class="post-item" data-id="<?= $post['id'] ?>" style="background:#fff;padding:15px;border-radius:10px;margin-bottom:15px;box-shadow:0 2px 5px rgba(0,0,0,0.05);">
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 5px;">
            <?php $postUser = getUserById($post['user_id']); ?>
    <img src="assets/images/<?= htmlspecialchars($postUser['avatar'] ?? 'default.png') ?>?v=<?= time() ?>"
        class="user-trigger"
        data-username="<?= htmlspecialchars($postUser['username']) ?>"
        style="width:60px;height:60px;border-radius:50%;object-fit:cover;margin-bottom:5px;cursor:pointer;">
    <p class="user-trigger" 
   data-username="<?= htmlspecialchars($postUser['username']) ?>"
   style="margin:0;font-weight:bold;color:#007bff;text-align:center;cursor:pointer;">
   @<?= htmlspecialchars($post['username']) ?>
</p><?php
$audienceLabel = ($post['audience'] ?? 'public') === 'followers' ? '👥 Followers Only' : '🌐 Public';
?>
<p style="font-size:12px;color:gray;"><?= $post['timestamp'] ?> <span style="color:#007bff;">• <?= $audienceLabel ?></span></p>
        <p><?= nl2br($post['content']) ?></p>
        </div>

        <div style="display:flex; gap:10px; align-items:center; margin-top:5px;">
            <form action="like.php" method="GET">
                <input type="hidden" name="post" value="<?= $post['id'] ?>">
                    <input type="hidden" name="feed" value="<?= htmlspecialchars($filter) ?>">
                    <input type="hidden" name="page" value="<?= $currentPage ?>">
                <button type="submit" style="padding:6px 10px; font-size:14px; background:#007bff; color:white; border:none; border-radius:6px; cursor:pointer;">
                    <b>Like ❤︎</b>  (<span class="like-count" id="like-count-<?= $post['id'] ?>"><?= count($post['likes']) ?></span>) 
                </button>
            </form>

            <?php if (isset($_SESSION['user']) && $_SESSION['user']['username'] === $post['username']): ?>
                <form action="delete_post.php" method="POST">
                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                    <input type="hidden" name="feed" value="<?= htmlspecialchars($filter) ?>">
                    <input type="hidden" name="page" value="<?= $currentPage ?>">
                    <button type="submit" class="small-button" title="Delete Post"
                        style="background: #ff3a3a; color:white; border:none; border-radius:6px; padding:6px 10px; font-size:14px; cursor:pointer;">
                        Delete ✖ 
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <div id="likersPopup" style="display:none;position:absolute;z-index:9999;background:white;border:1px solid #ccc;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.2);padding:10px;width:250px;">
    <strong>Liked by:</strong>
    <p id="likersList" style="list-style:none;padding:0;margin:5px 0 0 0;"></p>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.body.addEventListener('click', function (e) {
        const trigger = e.target.closest('.likers-popup-trigger');
        const popup = document.getElementById('likersPopup');
        const list = document.getElementById('likersList');

        if (trigger) {
            e.stopPropagation(); // prevent outside click from hiding immediately

            // Get likers from data attribute
            const likers = JSON.parse(trigger.getAttribute('data-likers') || '[]');

            // Populate popup list
            list.innerHTML = '';
            if (likers.length === 0) {
                list.innerHTML = '<p style="color:gray;">No one yet.</p>';
            } else {
                likers.forEach(user => {
                    list.innerHTML += `<p>@${user}</p>`;
                });
            }

            // Position the popup near the clicked element
            const rect = trigger.getBoundingClientRect();
            const popupWidth = 250;
const spacing = 5;
let top = window.scrollY + rect.bottom + spacing;
let left = rect.left;

const rightEdge = left + popupWidth;
const viewportWidth = window.innerWidth;

// Shift left if overflowing
if (rightEdge > viewportWidth) {
    left = viewportWidth - popupWidth - 10; // 10px padding from right
}
if (left < 10) left = 10; // Don't go off the left side

popup.style.top = `${top}px`;
popup.style.left = `${left}px`;
            popup.style.display = 'block';
        } else if (!popup.contains(e.target)) {
            popup.style.display = 'none'; // close if click outside
        }
    });

    // Hide on scroll
    window.addEventListener('scroll', () => {
        document.getElementById('likersPopup').style.display = 'none';
    });
});
</script>

    </div>
<?php endforeach; ?>
            </div>

            <!-- Display News as posts -->
         <div style="margin-top: 20px;">
    <?php if (!empty($newsHeadlines)): ?>
        <?php foreach ($newsHeadlines as $news): ?>
        <div class="post-item news-post" style="background:#eef6ff;padding:15px;border-radius:10px; margin-bottom: 20px;box-shadow:0 2px 5px rgba(0,0,0,0.05);">
            <div style="display:flex; flex-direction: column; align-items: flex-start;">
                <p style="font-weight:bold;color:#007bff;margin-bottom:5px;">📰 News by <i>FriendNest</i></p>
                <a href="<?= htmlspecialchars($news['link']) ?>" target="_blank" rel="noopener noreferrer" style="text-decoration:none; color:#333; font-size:16px; font-weight:bold;">
                    <?= htmlspecialchars($news['title']) ?>
                </a>
                <p style="font-size:12px;color:gray;margin-top:5px;"><?= date('M d, Y H:i', strtotime($news['pubDate'])) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?></div>

                <div style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
    <div style="flex: 1;">
        <?php if ($currentPage > 1): ?>
            <a href="?feed=<?= htmlspecialchars($filter) ?>&page=<?= $currentPage - 1 ?>">
                <button style="width: 100%;" class="pagination-btn">← Prev</button>
            </a>
        <?php else: ?>
            <button disabled style="width: 100%; opacity: 0;" class="pagination-btn" title="Previous"></button>
        <?php endif; ?>
    </div>
               <?php if ($totalPosts > 1): ?>
    <button id="backToTop" style="width: 100%;" class="pagination-btn" title="Back to Top">Back to ↑</button>
        <?php endif; ?>
    <div style="flex: 1;">
        <?php if ($currentPage < $totalPages): ?>
            <a href="?feed=<?= htmlspecialchars($filter) ?>&page=<?= $currentPage + 1 ?>">
                <button style="width: 100%;" class="pagination-btn">Next →</button>
            </a>
        <?php else: ?>
            <button disabled style="width: 100%; opacity: 0;" class="pagination-btn" title="Next"></button>
        <?php endif; ?>
    </div>
</div>

    <script>
    // Show the button when scrolling down
    window.onscroll = function() {
        const btn = document.getElementById("backToTop");
        btn.style.display = window.pageYOffset > 200 ? "block" : "none";
    };

    // Scroll to top smoothly
    document.getElementById("backToTop").onclick = function() {
        window.scrollTo({ top: 0, behavior: 'smooth' });
    };
    </script>

<script>
let lastKnownPostId = null;
const currentPage = <?= $currentPage ?>; // PHP outputs current page number

async function checkForNewPosts() {
    try {
        const res = await fetch(`fetch_posts.php?feed=<?= $filter ?>&page=1&checkNewOnly=1`);
        const data = await res.json();
        console.log("Latest on server:", data.latestPostId, " | Known on client:", lastKnownPostId);

        if (!lastKnownPostId) {
            // Set on first run
            lastKnownPostId = data.latestPostId;
            return;
        }

        if (data.latestPostId && data.latestPostId !== lastKnownPostId) {
            console.log('New post detected, reloading...');
            await reloadPosts(); // Only reload if different
        }
    } catch (err) {
        console.error('Error checking for new posts:', err);
    }
}

async function reloadPosts() {
    try {
        const res = await fetch(`fetch_posts.php?feed=<?= $filter ?>&page=${currentPage}`);
        const html = await res.text();
        document.getElementById('postsContainer').innerHTML = html;

        attachProfilePopupHandlers(); // <--- ADD THIS

        // Update lastKnownPostId to match latest loaded post
        const firstPost = document.querySelector('.post-item');
        if (firstPost) {
            lastKnownPostId = firstPost.dataset.id;
        }
    } catch (err) {
        console.error('Failed to reload posts:', err);
    }
}

// Initial load
reloadPosts();

// ✅ Only poll for new posts if you're on the first page
if (currentPage === 1) {
    setInterval(checkForNewPosts, 2000);
}
</script>

<script>
    let lastLikeCounts = {};

    async function checkLikesUpdate() {
        try {
            const response = await fetch('check_likes.php');
            const currentLikes = await response.json();

            for (let postId in currentLikes) {
                const newCount = currentLikes[postId];
                const countElement = document.getElementById(`like-count-${postId}`);

                if (countElement) {
                    const currentCount = parseInt(countElement.textContent);
                    if (currentCount !== newCount) {
                        countElement.textContent = newCount;
                    }
                }
            }

            lastLikeCounts = currentLikes;

        } catch (err) {
            console.error("Error fetching like counts:", err);
        }
    }

    // Initial load
    checkLikesUpdate();

    // Check every 2 seconds
    setInterval(checkLikesUpdate, 2000);
</script>

<script>
function attachProfilePopupHandlers() {
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

    // Hide popup on click outside
    document.addEventListener('click', function (event) {
        const popup = document.getElementById('profilePopup');
        if (!popup.contains(event.target)) {
            popup.style.display = 'none';
        }
    });
}

// Attach once initially
attachProfilePopupHandlers();
</script>

</div>

</div>
<?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
