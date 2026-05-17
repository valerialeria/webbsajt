<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$profile_user_id = (int)($_GET["id"] ?? 0);
$profile_user    = getUserById($db, $profile_user_id);

if (!$profile_user) {
    header("Location: members.php");
    exit();
}

$is_own_profile = ($profile_user_id === $_SESSION["userId"]);
if ($is_own_profile) {
    header("Location: profile.php");
    exit();
}

$is_following    = isFollowing($db, $_SESSION["userId"], $profile_user_id);
$follower_count  = getFollowerCount($db, $profile_user_id);
$following_count = getFollowingCount($db, $profile_user_id);
$posts           = getUserPosts($db, $profile_user_id);
?>

<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($profile_user["username"]); ?></title>
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

        <!-- PROFILHUVUD -->
        <div class="profile-header">

            <div class="profile-banner"
                 style="background-image: url('uploads/<?php echo htmlspecialchars($profile_user["banner_image"] ?? "default-banner.jpg"); ?>');">
            </div>

            <div class="profile-content">

                <img class="profile-avatar"
                     src="uploads/<?php echo htmlspecialchars($profile_user["profile_picture"] ?? "default.png"); ?>"
                     alt="profilbild">

                <h1><?php echo htmlspecialchars($profile_user["username"]); ?></h1>

                <p class="profile-bio">
                    <?php echo nl2br(htmlspecialchars($profile_user["bio"] ?? "Ingen bio ännu.")); ?>
                </p>

                <div class="profile-stats">
                    <div class="stat">
                        <span class="stat-number"><?php echo $follower_count; ?></span>
                        <span class="stat-label">följare</span>
                    </div>
                    <div class="stat">
                        <span class="stat-number"><?php echo $following_count; ?></span>
                        <span class="stat-label">följer</span>
                    </div>
                </div>

                <a href="follow.php?id=<?php echo $profile_user_id; ?>"
                   class="follow-btn <?php echo $is_following ? 'following' : ''; ?>">
                    <?php echo $is_following ? "✓ Följer" : "+ Följ"; ?>
                </a>

            </div>
        </div>

        <!-- INLÄGG -->
        <?php while ($row = $posts->fetch_assoc()):
            $like_count = getLikeCount($db, $row["id"]);
            $user_liked = hasLiked($db, $_SESSION["userId"], $row["id"]);
            $tags       = getTagsForPost($db, $row["id"]);
            $tag_list   = [];
            while ($t = $tags->fetch_assoc()) $tag_list[] = $t["tag"];
        ?>

        <div class="post">
            <div class="post-header">
                <img class="small-avatar"
                     src="uploads/<?php echo htmlspecialchars($profile_user["profile_picture"] ?? "default.png"); ?>">
                <div>
                    <div class="post-user"><?php echo htmlspecialchars($profile_user["username"]); ?></div>
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

    </div>
</div>

</body>
</html>