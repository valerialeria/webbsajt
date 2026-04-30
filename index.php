<?php
require_once("functions.php");
$db = connectToDb();
?>

<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<title>Min Webbplats</title>
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
}

header h1 {
    margin: 0;
    font-size: 22px;
    color: #d94f7a;
}

.header-nav a {
    text-decoration: none;
    color: #d94f7a;
    margin-left: 20px;
    font-weight: 500;
    transition: all 0.2s;
}

.header-nav a:hover {
    text-decoration: underline;
}


.container {
    max-width: 900px;
    margin: 40px auto;
    padding: 0 15px;
    display: flex;
    gap: 20px;
    align-items: flex-start;
}

.container form {
    flex: 1;
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
</header>

<div class="container">

    <form action="register.php" method="post">
        <h2>Skapa konto</h2>
        <input type="text" name="username" placeholder="Användarnamn" required>
        <input type="password" name="password" placeholder="Lösenord" required>
        <input type="email" name="email" placeholder="E-post" required>
        <input type="submit" value="Skapa konto">
    </form>

    <form action="login.php" method="post">
        <h2>Logga in</h2>
        <input type="text" name="username" placeholder="Användarnamn" required>
        <input type="password" name="password" placeholder="Lösenord" required>
        <input type="submit" value="Logga in">
        <?php writeMessage("login"); ?>
    </form>
</div>

</body>
</html>