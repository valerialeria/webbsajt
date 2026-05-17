<?php
require_once("functions.php");
$db = connectToDb();
?>

<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<title>Tundlr</title>
<link rel="stylesheet" href="css/main.css">
</head>
<body>

<header>
<h1>Tundlr</h1>
</header>

<body>

<div class="sidebar">
    <div class="logo">tun<span>dlr</span></div>

    <nav>
        <a href="index.php">Hem</a>
    </nav>
</div>

<div class="main-content">

    <div class="auth-container">

        <form class="auth-box" action="register.php" method="post">
            <h2>Skapa konto</h2>

            <input type="text" name="username" placeholder="Användarnamn" required>

            <input type="password" name="password" placeholder="Lösenord" required>

            <input type="email" name="email" placeholder="E-post" required>

            <input type="submit" value="Skapa konto">
        </form>

        <form class="auth-box" action="login.php" method="post">
            <h2>Logga in</h2>

            <input type="text" name="username" placeholder="Användarnamn" required>

            <input type="password" name="password" placeholder="Lösenord" required>

            <input type="submit" value="Logga in">

            <?php writeMessage("login"); ?>
        </form>

    </div>

</div>

</body>

</body>
</html>