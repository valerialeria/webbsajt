<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$user  = getUserByUsername($db, $_SESSION['username']);
$posts = getPosts($db);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Flödet</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">tun<span>dlr</span></div>
    <nav>
        <a href="profile.php">Profil</a>
        <a href="members.php">Flödet</a>
        <a href="following.php">Följer</a>
        <a class="logout-btn" href="index.php">Logga ut</a>
    </nav>
    <a href="post.php" class="new-post-btn">✎ Nytt inlägg</a>
</div>

<div class="main-content">
    <div class="feed">
        <h1>Hej <?= htmlspecialchars($user['username']) ?>!</h1>

        <?php while ($row = $posts->fetch_assoc()):
            $author   = getUserByUsername($db, $row['username']);
            $likes    = getLikeCount($db, $row['id']);
            $liked    = hasLiked($db, $_SESSION['userId'], $row['id']);
            $comments = getComments($db, $row['id']);
            $tags     = getTagsForPost($db, $row['id']);
            $tag_list = [];
            while ($t = $tags->fetch_assoc()) $tag_list[] = $t['tag'];
        ?>

        <div class="post">
            <div class="post-header">
                <img class="small-avatar" src="uploads/<?= htmlspecialchars($author['profile_picture'] ?? 'default.png') ?>">
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
                <?php if ($row['user_id'] == $_SESSION['userId']): ?>
                    <a class="delete-btn" href="post_delete.php?id=<?= $row['id'] ?>">Ta bort</a>
                <?php endif ?>
            </div>

            <div class="comments-section">
                <button class="toggle-comments-btn" onclick="toggleComments(<?= $row['id'] ?>)">
                    💬 <?= $comments->num_rows ?> kommentar<?= $comments->num_rows !== 1 ? 'er' : '' ?>
                </button>
                <div class="comments-list hidden" id="comments-list-<?= $row['id'] ?>">
                    <?php while ($c = $comments->fetch_assoc()):
                        $ca = getUserByUsername($db, $c['username']); ?>
                        <div class="comment">
                            <img class="comment-avatar" src="uploads/<?= htmlspecialchars($ca['profile_picture'] ?? 'default.png') ?>">
                            <div class="comment-body">
                                <span class="comment-user"><?= htmlspecialchars($c['username']) ?></span>
                                <span class="comment-text"><?= htmlspecialchars($c['comment']) ?></span>
                            </div>
                        </div>
                    <?php endwhile ?>
                    <form action="comment_save.php" method="post">
                        <input type="hidden" name="post_id" value="<?= $row['id'] ?>">
                        <input type="text" name="comment" placeholder="Skriv en kommentar..." required>
                        <input type="submit" value="Skicka">
                    </form>
                </div>
            </div>
        </div>

        <?php endwhile ?>
    </div>
</div>

<script>
function toggleComments(id) {
    document.getElementById('comments-list-' + id).classList.toggle('hidden');
}
</script>
</body>
</html>