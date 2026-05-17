<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$post_id = (int)($_GET['id'] ?? 0);

deletePost($db,$post_id,$_SESSION["userId"]);
header("Location: post.php");
exit();
?>