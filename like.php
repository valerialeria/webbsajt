<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$post_id = (int)($_GET['id'] ?? 0);

if ($post_id > 0) {
    likePost($db, $_SESSION["userId"], $post_id);
}

header("Location: members.php");
exit();
?>