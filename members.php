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
<title>Tundlr</title>
<link rel="stylesheet" href="css/main.css">
</head>
<body>

<div class="sidebar">

    <div class="logo">tun<span>dlr</span></div>

    <nav>
        <a href="profile.php">Profil</a>
        <a href="members.php">Dashboard</a>
        <a href="post.php">Nytt inlägg</a>
        <a class="logout-btn" href="index.php">Logga ut</a>
    </nav>

</div>

<div class="main-content">

    <div class="feed">

        <div style="margin-bottom:25px;">
            <h1>Hej <?php echo htmlspecialchars($user["username"]); ?>!</h1>
        </div>

        <?php while($row = $posts->fetch_assoc()): ?>

        <div class="post">

            <div class="post-header">

                <div class="avatar"></div>

                <div>
                    <div class="post-user">
                        <?php echo htmlspecialchars($row["username"]); ?>
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

            <?php if($row["user_id"] == $_SESSION["userId"]): ?>

                <a class="delete-btn"
                   href="post_delete.php?id=<?php echo $row["id"]; ?>">
                    Ta bort
                </a>

            <?php endif; ?>

        </div>

        <?php endwhile; ?>

    </div>

</div>

</body>
</html>