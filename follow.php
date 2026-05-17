<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$following_id = (int)($_GET["id"] ?? 0);

if ($following_id > 0 && $following_id !== $_SESSION["userId"]) {
    followUser($db, $_SESSION["userId"], $following_id);
}

// Gå tillbaka till sidan man kom från
$referer = $_SERVER["HTTP_REFERER"] ?? "members.php";
header("Location: " . $referer);
exit();
?>