<?php

require_once("functions.php");

if(session_status()!==PHP_SESSION_ACTIVE){
    session_start();
}

$db = connectToDb();

// Kontrollera inloggning
isLoggedIn();


$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if($post_id <= 0 || $comment === ''){
    redirectWithMessage("post.php","Comment cannot be empty","comment");
}

// Spara kommentar
$stmt = $db->prepare("INSERT INTO blogg_comments (user_id, post_id, comment, created_at) VALUES (?,?,?,NOW())");
$stmt->bind_param("iis", $_SESSION["userId"], $post_id, $comment);
if(!$stmt->execute()){

    redirectWithMessage("post.php","Could not save comment. Database error.","comment");
}


header("Location: members.php");
exit();
?>