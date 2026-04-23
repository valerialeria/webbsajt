<?php
require_once("functions.php");
$db = connectToDb();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$user = getUserByUsername($db, $username);

if(!$user || !password_verify($password, $user['password'])){
    redirectWithMessage("index.php","Fel användarnamn eller lösenord","login");
}

$_SESSION["loggedin"] = true;
$_SESSION["userId"] = $user["id"];
$_SESSION["username"] = $user["username"];

header("Location: members.php");
exit();
?>