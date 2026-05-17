<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$username = $_SESSION["username"];
$user = getUserByUsername($db,$username);
$posts = getPosts($db);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<title>Dashboard</title>
<link rel="stylesheet" href="css/main.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">tun<span>dlr</span></div>

    <nav>
        <a href="profile.php">Profil</a>
        <a href="members.php">Dashboard</a>
        <a href="following.php">Följer</a>
        <a class="logout-btn" href="index.php">Logga ut</a>
    </nav>

    <a href="post.php" class="new-post-btn">✎ Nytt inlägg</a>
</div>

<div class="main-content">

    <div class="feed">

        <div style="margin-bottom:25px;">
            <h1>Hej <?php echo htmlspecialchars($user["username"]); ?>!</h1>
        </div>

        <?php while($row = $posts->fetch_assoc()): 
            $post_author = getUserByUsername($db, $row["username"]);
            $like_count  = getLikeCount($db, $row["id"]);
            $user_liked  = hasLiked($db, $_SESSION["userId"], $row["id"]);
        ?>

        <div class="post">
            <div class="post-header">

                <img class="small-avatar"
                    src="uploads/<?php echo htmlspecialchars($post_author['profile_picture'] ?? 'default.png'); ?>"
                    alt="<?php echo htmlspecialchars($row['username']); ?>">

                <div>
                <div class="post-user">
                    <a href="user.php?id=<?php echo $post_author['id']; ?>" class="username-link">
                        <?php echo htmlspecialchars($row["username"]); ?>
                    </a>
                </div>
                    <div class="post-date">
                        <?php echo $row["created_at"]; ?>
                    </div>
                </div>

            </div>

            <div class="post-title">
                <?php echo htmlspecialchars($row["title"]); ?>
            </div>

            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($row["content"])); ?>
            </div>

            <?php
                $tags = getTagsForPost($db, $row["id"]);
                $tag_list = [];
                while ($t = $tags->fetch_assoc()) {
                    $tag_list[] = $t["tag"];
                }
                if (!empty($tag_list)): ?>
                    <div class="post-tags">
                        <?php foreach ($tag_list as $tag): ?>
                            <a href="tag.php?tag=<?php echo urlencode($tag); ?>" class="tag">
                                #<?php echo htmlspecialchars($tag); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
            <?php endif; ?>

            <?php if (!empty($row["image"])): ?>
                <div class="post-image">
                    <img src="uploads/<?php echo htmlspecialchars($row["image"]); ?>" alt="postbild">
                </div>
            <?php endif; ?>

            <div class="post-actions">
                <a class="like-btn <?php echo $user_liked ? 'liked' : ''; ?>"
                href="like.php?id=<?php echo $row['id']; ?>">
                    ♥ <span><?php echo $like_count; ?></span>
                </a>

                <?php if($row["user_id"] == $_SESSION["userId"]): ?>
                    <a class="delete-btn" href="post_delete.php?id=<?php echo $row["id"]; ?>">
                        Ta bort
                    </a>
                <?php endif; ?>
            </div>

            <?php
            $comments    = getComments($db, $row["id"]);
            $comment_count = $comments->num_rows;
            ?>

            <div class="comments-section" id="comments-<?php echo $row['id']; ?>">

                <button class="toggle-comments-btn" onclick="toggleComments(<?php echo $row['id']; ?>)">
                    💬 <?php echo $comment_count; ?> kommentar<?php echo $comment_count !== 1 ? 'er' : ''; ?>
                </button>

                <div class="comments-list hidden" id="comments-list-<?php echo $row['id']; ?>">

                    <?php while($comment = $comments->fetch_assoc()): ?>
                        <div class="comment">
                            <?php
                            $comment_author = getUserByUsername($db, $comment["username"]);
                            ?>
                            <img class="comment-avatar"
                                src="uploads/<?php echo htmlspecialchars($comment_author['profile_picture'] ?? 'default.png'); ?>"
                                alt="">
                            <div class="comment-body">
                                <span class="comment-user"><?php echo htmlspecialchars($comment["username"]); ?></span>
                                <span class="comment-text"><?php echo htmlspecialchars($comment["comment"]); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <form class="comment-form" action="comment_save.php" method="post">
                        <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                        <input type="text" name="comment" placeholder="Skriv en kommentar..." required>
                        <input type="submit" value="Skicka">
                    </form>

                </div>

            </div>

        </div>

        <?php endwhile; ?>
    </div>

</div>

    <script>
    function toggleComments(postId) {
        const list = document.getElementById('comments-list-' + postId);
        list.classList.toggle('hidden');
    }
    </script>

</body>
</html>