<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$user_id = $_SESSION["userId"];
$title = $_POST["title"] ?? '';
$content = $_POST["content"] ?? '';

if(empty($title) || empty($content)){
    redirectWithMessage("post.php","Title or content cannot be empty","empty");
}

savePost($db,$user_id,$title,$content);
header("Location: post.php");
exit();
?>