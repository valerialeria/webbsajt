<?php
session_start();


function connectToDb() {
    $dbHost = 'ostrawebb.se';
    $dbUser = 'wsp2526_valmon';
    $dbPassword = 'lefynijo49';
    $dbDatabase = 'wsp2526_valmon';
    $db = new mysqli($dbHost, $dbUser, $dbPassword, $dbDatabase);
    if($db->connect_error){ die("Connection failed: ".$db->connect_error);}
    return $db;
}


function getUserByUsername($db, $username){
    $stmt = $db->prepare("SELECT * FROM blogg_users WHERE username=?");
    $stmt->bind_param("s",$username);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}

function createUser($db, $username, $password, $email){
    $hash = password_hash($password,PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO blogg_users(username,password,email) VALUES(?,?,?)");
    $stmt->bind_param("sss",$username,$hash,$email);
    $stmt->execute();
}


function isLoggedIn(){
    if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"]!==true){
        redirectWithMessage("index.php","Du måste logga in först","login");
    }
}


function redirectWithMessage($url,$msg,$key){
    $_SESSION["message"][$key]=$msg;
    header("Location:$url");
    exit();
}

function writeMessage($key){
    if(isset($_SESSION["message"][$key])){
        echo '<p style="color:red;margin:10px 0;">'.$_SESSION["message"][$key].'</p>';
        unset($_SESSION["message"][$key]);
    }
}


function getPosts($db){
    return $db->query("SELECT blogg_posts.*, blogg_users.username FROM blogg_posts JOIN blogg_users ON blogg_posts.user_id = blogg_users.id ORDER BY blogg_posts.created_at DESC");
}

function savePost($db,$user_id,$title,$content){
    $stmt = $db->prepare("INSERT INTO blogg_posts(user_id,title,content,created_at) VALUES(?,?,?,NOW())");
    $stmt->bind_param("iss",$user_id,$title,$content);
    $stmt->execute();
}

// Kommentarer
function saveComment($db,$user_id,$post_id,$comment){
    $stmt = $db->prepare("INSERT INTO blogg_comments(user_id,post_id,comment,created_at) VALUES(?,?,?,NOW())");
    $stmt->bind_param("iis",$user_id,$post_id,$comment);
    $stmt->execute();
}

function getComments($db,$post_id){
    $stmt = $db->prepare("SELECT blogg_comments.*, blogg_users.username FROM blogg_comments JOIN blogg_users ON blogg_comments.user_id=blogg_users.id WHERE blogg_comments.post_id=? ORDER BY blogg_comments.created_at ASC");
    $stmt->bind_param("i",$post_id);
    $stmt->execute();
    return $stmt->get_result();
}

// Ta bort inlägg 
function deletePost($db,$post_id,$user_id){
    $stmt = $db->prepare("DELETE FROM blogg_posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii",$post_id,$user_id);
    $stmt->execute();
}
?>