<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$user_id = $_SESSION["userId"];
$username = $_SESSION["username"];

$user = getUserByUsername($db, $username);

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $bio = trim($_POST["bio"]);

    $profile_picture = $user["profile_picture"];

    if(isset($_FILES["profile_picture"]) && $_FILES["profile_picture"]["error"] == 0) {

        $fileName = time() . "_" . basename($_FILES["profile_picture"]["name"]);

        $target = "uploads/" . $fileName;

        move_uploaded_file(
            $_FILES["profile_picture"]["tmp_name"],
            $target
        );

        $profile_picture = $fileName;
    }

    updateProfile(
        $db,
        $user_id,
        $bio,
        $profile_picture
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
<title>Min profil</title>

<link rel="stylesheet" href="css/main.css">

</head>
<body>

<div class="sidebar">

    <div class="logo">
        tun<span>dlr</span>
    </div>

    <nav>
        <a href="profile.php">Profil</a>
        <a href="members.php">Dashboard</a>
        <a href="post.php">Nytt inlägg</a>
        <a class="logout-btn" href="logout.php">Logga ut</a>
    </nav>

</div>

<div class="main-content">

    <div class="feed">

        <div class="profile-header">

            <div class="profile-banner"></div>

            <div class="profile-content">

                <img
                    class="profile-avatar"
                    src="uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                    alt="Profilbild"
                >

                <h1>
                    <?php echo htmlspecialchars($user['username']); ?>
                </h1>

                <p class="profile-bio">
                    <?php echo nl2br(htmlspecialchars($user['bio'] ?? 'Ingen bio ännu.')); ?>
                </p>

            </div>

        </div>

        <div class="profile-edit-card">

            <h2>Redigera profil</h2>

            <form method="post" enctype="multipart/form-data">

                <label>Profilbild</label>

                <input
                    type="file"
                    name="profile_picture"
                >

                <label>Bio</label>

                <textarea
                    name="bio"
                    placeholder="Skriv något om dig själv..."
                ><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>

                <input
                    type="submit"
                    value="Spara profil"
                >

            </form>

        </div>

        <h2 class="profile-post-title">Mina inlägg</h2>

        <?php while($row = $posts->fetch_assoc()): ?>

        <div class="post">

            <div class="post-header">

                <img
                    class="small-avatar"
                    src="uploads/<?php echo htmlspecialchars($user['profile_picture']); ?>"
                    alt="avatar"
                >

                <div>

                    <div class="post-user">
                        <?php echo htmlspecialchars($row['title']); ?>
                    </div>

                    <div class="post-date">
                        <?php echo $row['created_at']; ?>
                    </div>

                </div>

            </div>

            <div class="post-content">
                <?php echo nl2br(htmlspecialchars($row['content'])); ?>
            </div>

        </div>

        <?php endwhile; ?>

    </div>

</div>

</body>
</html>
