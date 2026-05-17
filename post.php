<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

if($_SERVER["REQUEST_METHOD"] == "POST") {

    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if(!empty($title) && !empty($content)) {

        savePost(
            $db,
            $_SESSION["userId"],
            $title,
            $content
        );

        header("Location: members.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<title>Nytt inlägg</title>

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

        <div class="create-post-card">

            <div class="create-post-header">

                <div class="avatar"></div>

                <div>
                    <h1>Skapa nytt inlägg</h1>
                    <p>Dela något med världen.</p>
                </div>

            </div>

            <form method="post">

                <input
                    type="text"
                    name="title"
                    placeholder="Rubrik"
                    required
                >

                <textarea
                    name="content"
                    placeholder="Vad tänker du på?"
                    required
                ></textarea>

                <input
                    type="submit"
                    value="Publicera"
                >

            </form>

        </div>

    </div>

</div>

</body>
</html>
