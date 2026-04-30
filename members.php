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
<title>Members</title>
<style>
body {
    font-family: Arial, Helvetica, sans-serif;
    background: #fff; 
    margin: 0;
    padding: 0;
    color: #333;
}

header {
    width: 100%;
    padding: 20px 40px;
    background: #fff;
    border-bottom: 1px solid #f5c6d1;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-sizing: border-box;
}

header h1 {
    margin: 0;
    font-size: 22px;
    color: #d94f7a;
}

.header-nav {
    display: flex;      
    gap: 20px;         
}

.header-nav a {
    display: inline-block; 
    text-decoration: none;
    color: #d94f7a;
    font-weight: 500;
    padding: 5px 10px;   
    transition: all 0.2s;
}

.header-nav a:hover {
    text-decoration: underline;
}


.container {
    max-width: 800px;
    margin: 40px auto;
    padding: 0 15px;
}

form, .post, .post-form, .login-form {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    border: 1px solid #f5c6d1;
    margin-bottom: 20px;
}

input, textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #f5c6d1;
    border-radius: 6px;
    font-size: 14px;
    box-sizing: border-box;
}

input[type="submit"] {
    background: #d94f7a;
    color: #fff;
    border: none;
    cursor: pointer;
    border-radius: 6px;
    padding: 8px 12px;
    transition: background 0.2s;
}

input[type="submit"]:hover {
    background: #f277a4;
}

.post .title {
    font-weight: 600;
    color: #d94f7a;
    margin-bottom: 5px;
}

.post .author {
    font-size: 13px;
    color: #777;
    margin-bottom: 10px;
}

.post .post-content {
    font-size: 14px;
    line-height: 1.5;
}


.comment {
    margin-top: 8px;
    padding: 8px;
    background: #fff0f5;
    border-radius: 6px;
    font-size: 13px;
}

.comment-form input[type="submit"] {
    background: #d94f7a;
    color: #fff;
    border: none;
    cursor: pointer;
    border-radius: 6px;
    padding: 6px 10px;
}

.comment-form input[type="submit"]:hover {
    background: #f277a4;
}


.button.delete-btn {
    color: #ff6b81;
    text-decoration: none;
    font-size: 13px;
}

.button.delete-btn:hover {
    color: #ff3b5c;
}


.logout {
    display: inline-block;
    margin-top: 15px;
    color: #d94f7a;
    text-decoration: none;
    font-weight: 500;
}

.logout:hover {
    text-decoration: underline;
}
</style>
</head>
<body>

<header>
    <h1>Min Webbplats</h1>
    <nav class="header-nav">
        <a href="post.php">Posts</a>
        <a href="members.php">Startsida</a>
    </nav>
</header>

<div class="container">
<h1 class="welcome">Välkommen <?php echo htmlspecialchars($user["username"]); ?> ! </h1>
<div class="divider"></div>

<?php while($row = $posts->fetch_assoc()): ?>
<div class="post">
    <div class="title"><?php echo htmlspecialchars($row["title"]); ?></div>
    <div class="author"><?php echo htmlspecialchars($row["username"]) . " ┃ " . $row["created_at"]; ?></div>
    <div class="post-content"><?php echo nl2br(htmlspecialchars($row["content"])); ?></div>
    <?php if($row["user_id"]==$_SESSION["userId"]): ?>
        <a href="post_delete.php?id=<?php echo $row["id"]; ?>" class="button delete-btn">Radera</a>
    <?php endif; ?>
</div>
<?php endwhile; ?>

<a href="logout.php" class="logout">Logga ut</a>
</div>
</body>
</html>