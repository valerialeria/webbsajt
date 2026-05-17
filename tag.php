<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$tag   = trim($_GET["tag"] ?? "");
$posts = getPostsByTag($db, $tag);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<title>#<?php echo htmlspecialchars($tag); ?></title>
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
            <h1>#<?php echo htmlspecialchars($tag); ?></h1>
        </div>

        <?php while($row = $posts->fetch_assoc()):
            $post_author = getUserByUsername($db, $row["username"]);
            $like_count  = getLikeCount($db, $row["id"]);
            $user_liked  = hasLiked($db, $_SESSION["userId"], $row["id"]);
            $tags        = getTagsForPost($db, $row["id"]);
            $tag_list    = [];
            while ($t = $tags->fetch_assoc()) $tag_list[] = $t["tag"];
        ?>

        <div class="post">
            <div class="post-header">
                <img class="small-avatar"
                     src="uploads/<?php echo htmlspecialchars($post_author['profile_picture'] ?? 'default.png'); ?>">
                <div>
                    <div class="post-user"><?php echo htmlspecialchars($row["username"]); ?></div>
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
                    <?php foreach ($tag_list as $t): ?>
                        <a href="tag.php?tag=<?php echo urlencode($t); ?>" class="tag">
                            #<?php echo htmlspecialchars($t); ?>
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

    </div>
</div>

</body>
</html>