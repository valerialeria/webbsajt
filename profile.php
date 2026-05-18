<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$user_id = $_SESSION['userId'];
$user    = getUserByUsername($db, $_SESSION['username']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $bio             = trim($_POST['bio']);
    $profile_picture = $user['profile_picture'] ?? 'default.png';
    $banner_image    = $user['banner_image'] ?? 'default-banner.jpg';

    if ($_FILES['profile_picture']['error'] == 0) {
        $profile_picture = time() . "_" . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], "uploads/" . $profile_picture);
    }
    if ($_FILES['banner_image']['error'] == 0) {
        $banner_image = time() . "_banner_" . basename($_FILES['banner_image']['name']);
        move_uploaded_file($_FILES['banner_image']['tmp_name'], "uploads/" . $banner_image);
    }

    updateProfile($db, $user_id, $bio, $profile_picture, $banner_image);
    header("Location: profile.php");
    exit();
}

$posts           = getUserPosts($db, $user_id);
$follower_count  = getFollowerCount($db, $user_id);
$following_count = getFollowingCount($db, $user_id);
?>
<!DOCTYPE html>
<html lang="sv">
<head>
    <meta charset="UTF-8">
    <title>Profil</title>
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

        <div class="profile-header">
            <div class="profile-banner" style="background-image:url('uploads/<?= htmlspecialchars($user['banner_image'] ?? 'default-banner.jpg') ?>')"></div>
            <div class="profile-content">
                <img class="profile-avatar" src="uploads/<?= htmlspecialchars($user['profile_picture'] ?? 'default.png') ?>">
                <h1><?= htmlspecialchars($user['username']) ?></h1>
                <p class="profile-bio"><?= nl2br(htmlspecialchars($user['bio'] ?? 'Ingen bio ännu.')) ?></p>
                <div class="profile-stats">
                    <div class="stat"><span class="stat-number"><?= $follower_count ?></span><span class="stat-label">följare</span></div>
                    <div class="stat"><span class="stat-number"><?= $following_count ?></span><span class="stat-label">följer</span></div>
                </div>
                <button class="edit-profile-btn" onclick="document.getElementById('editCard').classList.toggle('hidden')">✎</button>
            </div>
        </div>

        <div id="editCard" class="profile-edit-card hidden">
            <h2>Redigera profil</h2>
            <form method="post" enctype="multipart/form-data">
                <label>Profilbild</label>
                <input type="file" name="profile_picture">
                <label>Bannerbild</label>
                <input type="file" name="banner_image">
                <label>Bio</label>
                <textarea name="bio"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                <input type="submit" value="Spara profil">
            </form>
        </div>

        <h2>Mina inlägg</h2>

        <?php while ($row = $posts->fetch_assoc()): ?>
        <div class="post">
            <div class="post-header">
                <img class="small-avatar" src="uploads/<?= htmlspecialchars($user['profile_picture'] ?? 'default.png') ?>">
                <div>
                    <div class="post-user"><?= htmlspecialchars($row['title']) ?></div>
                    <div class="post-date"><?= $row['created_at'] ?></div>
                </div>
            </div>
            <div class="post-content"><?= nl2br(htmlspecialchars($row['content'])) ?></div>
            <?php if ($row['image']): ?>
                <div class="post-image"><img src="uploads/<?= htmlspecialchars($row['image']) ?>"></div>
            <?php endif ?>
        </div>
        <?php endwhile ?>

    </div>
</div>
</body>
</html>