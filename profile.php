<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$user_id = $_SESSION["userId"];
$username = $_SESSION["username"];

$user = getUserByUsername($db, $username);

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $bio = trim($_POST["bio"]);

    $profile_picture = $user["profile_picture"] ?? "default.png";
    $banner_image = $user["banner_image"] ?? "default-banner.jpg";

    // PROFILE PICTURE UPLOAD
    if(isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {

        $fileName = time() . "_" . basename($_FILES["profile_picture"]["name"]);

        move_uploaded_file(
            $_FILES["profile_picture"]["tmp_name"],
            "uploads/" . $fileName
        );

        $profile_picture = $fileName;
    }

    // BANNER UPLOAD
    if(isset($_FILES["banner_image"]) && $_FILES["banner_image"]["error"] == 0) {

        $bannerName = time() . "_banner_" . basename($_FILES["banner_image"]["name"]);

        move_uploaded_file(
            $_FILES["banner_image"]["tmp_name"],
            "uploads/" . $bannerName
        );

        $banner_image = $bannerName;
    }

    updateProfile(
        $db,
        $user_id,
        $bio,
        $profile_picture,
        $banner_image
    );

    header("Location: profile.php");
    exit();
}

$posts = getUserPosts($db, $user_id);
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
        <a href="members.php">Dashboard</a>
        <a class="logout-btn" href="index.php">Logga ut</a>
    </nav>

    <a href="post.php" class="new-post-btn">✎ Nytt inlägg</a>
</div>

<div class="main-content">

    <div class="feed">

        <!-- PROFILE HEADER -->
        <div class="profile-header">

            <div class="profile-banner"
                style="background-image:url('uploads/<?php echo htmlspecialchars($user['banner_image'] ?? 'default-banner.jpg'); ?>');">
            </div>

            <div class="profile-content">

                <img class="profile-avatar"
                     src="uploads/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default.png'); ?>"
                     alt="profilbild">

                <h1><?php echo htmlspecialchars($user["username"]); ?></h1>

                <p class="profile-bio">
                    <?php echo nl2br(htmlspecialchars($user["bio"] ?? "Ingen bio ännu.")); ?>
                </p>

                <!-- EDIT BUTTON -->
                <div class="edit-profile-wrapper">
                    <button class="edit-profile-btn" onclick="toggleEditProfile()">
                        ✎
                    </button>
                </div>

            </div>
        </div>

        <!-- EDIT PROFILE PANEL -->
        <div id="editProfileCard" class="profile-edit-card hidden">

            <h2>Redigera profil</h2>

            <form method="post" enctype="multipart/form-data">

                <label>Profilbild</label>
                <input type="file" name="profile_picture">

                <label>Bannerbild</label>
                <input type="file" name="banner_image">

                <label>Bio</label>
                <textarea name="bio"><?php echo htmlspecialchars($user["bio"] ?? ""); ?></textarea>

                <input type="submit" value="Spara profil">

            </form>

        </div>

        <!-- POSTS -->
        <h2>Mina inlägg</h2>

        <?php while($row = $posts->fetch_assoc()): ?>

        <div class="post">

            <div class="post-header">
                <img class="small-avatar"
                    src="uploads/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default.png'); ?>">
                <div>
                    <div class="post-user">
                        <?php echo htmlspecialchars($row["title"]); ?>
                    </div>
                    <div class="post-date">
                        <?php echo $row["created_at"]; ?>
                    </div>
                </div>
            </div>

            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($row["content"])); ?>
            </div>

            <?php if (!empty($row["image"])): ?>
                <div class="post-image">
                    <img src="uploads/<?php echo htmlspecialchars($row["image"]); ?>" alt="postbild">
                </div>
            <?php endif; ?>

        </div>

        <?php endwhile; ?>

    </div>
</div>

<script>
function toggleEditProfile() {
    document.getElementById("editProfileCard").classList.toggle("hidden");
}
</script>

</body>
</html>