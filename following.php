<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$posts           = getFollowingFeed($db, $_SESSION["userId"]);
$following_count = getFollowingCount($db, $_SESSION["userId"]);
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

        <div class="tag-header">
            <h1>Följer</h1>
            <p style="color: gray; margin-top: 6px;">Inlägg från användare du följer</p>
        </div>

        <?php if ($following_count === 0): ?>
            <div class="empty-feed">
                <p>Du följer ingen ännu.</p>
                <a href="members.php">Utforska inlägg →</a>
            </div>

        <?php elseif ($posts->num_rows === 0): ?>
            <div class="empty-feed">
                <p>Inga inlägg från användare du följer än.</p>
            </div>

        <?php else: ?>
            <?php while ($row = $posts->fetch_assoc()):
                $post_author = getUserByUsername($db, $row["username"]);
                $like_count  = getLikeCount($db, $row["id"]);
                $user_liked  = hasLiked($db, $_SESSION["userId"], $row["id"]);
                $tags        = getTagsForPost($db, $row["id"]);
                $tag_list    = [];
                while ($t = $tags->fetch_assoc()) $tag_list[] = $t["tag"];
            ?>

            <div class="post">
                <div class="post-header">
                    <a href="user.php?id=<?php echo $post_author['id']; ?>">
                        <img class="small-avatar"
                             src="uploads/<?php echo htmlspecialchars($post_author['profile_picture'] ?? 'default.png'); ?>">
                    </a>
                    <div>
                        <div class="post-user">
                            <a href="user.php?id=<?php echo $post_author['id']; ?>" class="username-link">
                                <?php echo htmlspecialchars($row["username"]); ?>
                            </a>
                        </div>
                        <div class="post-date"><?php echo $row["created_at"]; ?></div>
                    </div>
                </div>

                <div class="post-title"><?php echo htmlspecialchars($row["title"]); ?></div>

                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($row["content"])); ?>
                </div>

                <?php if (!empty($row["image"])): ?>
                    <div class="post-image">
                        <img src="uploads/<?php echo htmlspecialchars($row["image"]); ?>">
                    </div>
                <?php endif; ?>

                <?php if (!empty($tag_list)): ?>
                    <div class="post-tags">
                        <?php foreach ($tag_list as $tag): ?>
                            <a href="tag.php?tag=<?php echo urlencode($tag); ?>" class="tag">
                                #<?php echo htmlspecialchars($tag); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <div class="post-actions">
                    <a class="like-btn <?php echo $user_liked ? 'liked' : ''; ?>"
                       href="like.php?id=<?php echo $row['id']; ?>">
                        ♥ <span><?php echo $like_count; ?></span>
                    </a>
                </div>
            </div>

            <?php endwhile; ?>
        <?php endif; ?>

    </div>
</div>

</body>
</html>