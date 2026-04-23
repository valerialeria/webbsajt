<?php
require_once("functions.php");
$db = connectToDb();

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';
$email = $_POST['email'] ?? '';

if($username && $password && $email){
    createUser($db,$username,$password,$email);
}

header("Location: index.php");
exit();
?>