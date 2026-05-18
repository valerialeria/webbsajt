<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$tag   = trim($_GET['tag'] ?? '');
$posts = getPostsByTag($db, $tag);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>#<?= htmlspecialchars($tag) ?></title>
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
        <h1>#<?= htmlspecialchars($tag) ?></h1>

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
                <img class="small-avatar" src="uploads/<?= htmlspecialchars($author['profile_picture'] ?? 'default.png') ?>">
                <div>
                    <div class="post-user"><?= htmlspecialchars($row['username']) ?></div>
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