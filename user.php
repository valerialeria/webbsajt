<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$profile_id   = (int)($_GET['id'] ?? 0);
$profile_user = getUserById($db, $profile_id);

if (!$profile_user) { header("Location: members.php"); exit(); }
if ($profile_id === $_SESSION['userId']) { header("Location: profile.php"); exit(); }

$is_following    = isFollowing($db, $_SESSION['userId'], $profile_id);
$follower_count  = getFollowerCount($db, $profile_id);
$following_count = getFollowingCount($db, $profile_id);
$posts           = getUserPosts($db, $profile_id);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($profile_user['username']) ?></title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">tun<span>dlr</span></div>
    <nav>
        <a href="profile.php">Profil</a>
        <a href="members.php">Flödet</a>
        <a href="following.php">Följer</a>
        <a class="logout-btn" href="logout.php">Logga ut</a>
    </nav>
    <a href="post.php" class="new-post-btn">✎ Nytt inlägg</a>
</div>

<div class="main-content">
    <div class="feed">

        <div class="profile-header">
            <div class="profile-banner" style="background-image:url('uploads/<?= htmlspecialchars($profile_user['banner_image'] ?? 'default-banner.jpg') ?>')"></div>
            <div class="profile-content">
                <img class="profile-avatar" src="uploads/<?= htmlspecialchars($profile_user['profile_picture'] ?? 'default.png') ?>">
                <h1><?= htmlspecialchars($profile_user['username']) ?></h1>
                <p class="profile-bio"><?= nl2br(htmlspecialchars($profile_user['bio'] ?? 'Ingen bio ännu.')) ?></p>
                <div class="profile-stats">
                    <div class="stat"><span class="stat-number"><?= $follower_count ?></span><span class="stat-label">följare</span></div>
                    <div class="stat"><span class="stat-number"><?= $following_count ?></span><span class="stat-label">följer</span></div>
                </div>
                <a href="follow.php?id=<?= $profile_id ?>" class="follow-btn <?= $is_following ? 'following' : '' ?>">
                    <?= $is_following ? '✓ Följer' : '+ Följ' ?>
                </a>
            </div>
        </div>

        <?php while ($row = $posts->fetch_assoc()):
            $likes    = getLikeCount($db, $row['id']);
            $liked    = hasLiked($db, $_SESSION['userId'], $row['id']);
            $tags     = getTagsForPost($db, $row['id']);
            $tag_list = [];
            while ($t = $tags->fetch_assoc()) $tag_list[] = $t['tag'];
        ?>
        <div class="post">
            <div class="post-header">
                <img class="small-avatar" src="uploads/<?= htmlspecialchars($profile_user['profile_picture'] ?? 'default.png') ?>">
                <div>
                    <div class="post-user"><?= htmlspecialchars($profile_user['username']) ?></div>
                    <div class="post-date"><?= $row['created_at'] ?></div>
                </div>
            </div>
            <div class="post-title"><?= htmlspecialchars($row['title']) ?></div>
            <div class="post-content"><?= nl2br(htmlspecialchars($row['content'])) ?></div>
            <?php if ($row['image']): ?>
                <div class="post-image"><img src="uploads/<?= htmlspecialchars($row['image']) ?>"></div>
            <?php endif ?>
            <?php if ($tag_list): ?>
                <div class="post-tags">
                    <?php foreach ($tag_list as $tag): ?>
                        <a href="tag.php?tag=<?= urlencode($tag) ?>" class="tag">#<?= htmlspecialchars($tag) ?></a>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
            <div class="post-actions">
                <a class="like-btn <?= $liked ? 'liked' : '' ?>" href="like.php?id=<?= $row['id'] ?>">
                    ♥ <span><?= $likes ?></span>
                </a>
            </div>
        </div>
        <?php endwhile ?>

    </div>
</div>
</body>
</html>