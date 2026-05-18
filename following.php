<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$posts           = getFollowingFeed($db, $_SESSION['userId']);
$following_count = getFollowingCount($db, $_SESSION['userId']);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Följer</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">tun<span>dlr</span></div>
    <nav>
        <a href="profile.php">Profil</a>
        <a href="members.php">Dashboard</a>
        <a href="following.php">Följer</a>
        <a class="logout-btn" href="logout.php">Logga ut</a>
    </nav>
    <a href="post.php" class="new-post-btn">✎ Nytt inlägg</a>
</div>

<div class="main-content">
    <div class="feed">
        <h1>Följer</h1>
        <p style="color:white;">Inlägg från användare du följer</p>

        <?php if ($following_count === 0): ?>
            <div class="empty-feed">
                <p>Du följer ingen ännu.</p>
                <a href="members.php">Utforska inlägg →</a>
            </div>
        <?php elseif ($posts->num_rows === 0): ?>
            <div class="empty-feed"><p>Inga inlägg från användare du följer än.</p></div>
        <?php else: ?>
            <?php while ($row = $posts->fetch_assoc()):
                $author   = getUserByUsername($db, $row['username']);
                $likes    = getLikeCount($db, $row['id']);
                $liked    = hasLiked($db, $_SESSION['userId'], $row['id']);
                $tags     = getTagsForPost($db, $row['id']);
                $tag_list = [];
                while ($t = $tags->fetch_assoc()) $tag_list[] = $t['tag'];
            ?>
            <div class="post">
                <div class="post-header">
                    <a href="user.php?id=<?= $author['id'] ?>">
                        <img class="small-avatar" src="uploads/<?= htmlspecialchars($author['profile_picture'] ?? 'default.png') ?>">
                    </a>
                    <div>
                        <div class="post-user">
                            <a href="user.php?id=<?= $author['id'] ?>" class="username-link"><?= htmlspecialchars($row['username']) ?></a>
                        </div>
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
        <?php endif ?>

    </div>
</div>
</body>
</html>