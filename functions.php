<?php
session_start();

function connectToDb() {
    $dbHost = 'ostrawebb.se';
    $dbUser = 'wsp2526_isawes';
    $dbPassword = 'jupigedi69';
    $dbDatabase = 'wsp2526_isawes';
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

function savePost($db, $user_id, $title, $content, $image = null) {
    $stmt = $db->prepare("INSERT INTO blogg_posts(user_id, title, content, image, created_at) VALUES(?,?,?,?,NOW())");
    $stmt->bind_param("isss", $user_id, $title, $content, $image);
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

function updateProfile(
    $db,
    $user_id,
    $bio,
    $profile_picture,
    $banner_image
){

    $stmt = $db->prepare(
        "UPDATE blogg_users
         SET bio=?,
             profile_picture=?,
             banner_image=?
         WHERE id=?"
    );

    $stmt->bind_param(
        "sssi",
        $bio,
        $profile_picture,
        $banner_image,
        $user_id
    );

    $stmt->execute();
}

function getUserPosts($db, $user_id){

    $stmt = $db->prepare(
        "SELECT * FROM blogg_posts WHERE user_id=? ORDER BY created_at DESC"
    );

    $stmt->bind_param("i", $user_id);

    $stmt->execute();

    return $stmt->get_result();
}

function likePost($db, $user_id, $post_id) {
    // Gillar eller ogillar ett inlägg
    $check = $db->prepare("SELECT id FROM blogg_likes WHERE user_id=? AND post_id=?");
    $check->bind_param("ii", $user_id, $post_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $stmt = $db->prepare("DELETE FROM blogg_likes WHERE user_id=? AND post_id=?");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
    } else {
        $stmt = $db->prepare("INSERT INTO blogg_likes (user_id, post_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $user_id, $post_id);
        $stmt->execute();
    }
}

function getLikeCount($db, $post_id) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM blogg_likes WHERE post_id=?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()["count"];
}

function hasLiked($db, $user_id, $post_id) {
    $stmt = $db->prepare("SELECT id FROM blogg_likes WHERE user_id=? AND post_id=?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function saveTags($db, $post_id, $tags_string) {
    // Rensa gamla taggar för detta inlägg
    $stmt = $db->prepare("DELETE FROM blogg_tags WHERE post_id=?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();

    // Dela upp och spara nya taggar
    $tags = explode(",", $tags_string);
    foreach ($tags as $tag) {
        $tag = strtolower(trim(str_replace("#", "", $tag)));
        if (!empty($tag)) {
            $stmt = $db->prepare("INSERT INTO blogg_tags (post_id, tag) VALUES (?, ?)");
            $stmt->bind_param("is", $post_id, $tag);
            $stmt->execute();
        }
    }
}

function getTagsForPost($db, $post_id) {
    $stmt = $db->prepare("SELECT tag FROM blogg_tags WHERE post_id=?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getPostsByTag($db, $tag) {
    $stmt = $db->prepare("
        SELECT blogg_posts.*, blogg_users.username 
        FROM blogg_posts 
        JOIN blogg_users ON blogg_posts.user_id = blogg_users.id
        JOIN blogg_tags ON blogg_posts.id = blogg_tags.post_id
        WHERE blogg_tags.tag = ?
        ORDER BY blogg_posts.created_at DESC
    ");
    $stmt->bind_param("s", $tag);
    $stmt->execute();
    return $stmt->get_result();
}






function getUserById($db, $user_id) {
    $stmt = $db->prepare("SELECT * FROM blogg_users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function followUser($db, $follower_id, $following_id) {
    $check = $db->prepare("SELECT id FROM blogg_follows WHERE follower_id=? AND following_id=?");
    $check->bind_param("ii", $follower_id, $following_id);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $stmt = $db->prepare("DELETE FROM blogg_follows WHERE follower_id=? AND following_id=?");
    } else {
        $stmt = $db->prepare("INSERT INTO blogg_follows (follower_id, following_id) VALUES (?,?)");
    }
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
}

function isFollowing($db, $follower_id, $following_id) {
    $stmt = $db->prepare("SELECT id FROM blogg_follows WHERE follower_id=? AND following_id=?");
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

function getFollowerCount($db, $user_id) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM blogg_follows WHERE following_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()["count"];
}

function getFollowingCount($db, $user_id) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM blogg_follows WHERE follower_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()["count"];
}

function getFollowingFeed($db, $user_id) {
    $stmt = $db->prepare("
        SELECT blogg_posts.*, blogg_users.username
        FROM blogg_posts
        JOIN blogg_users ON blogg_posts.user_id = blogg_users.id
        JOIN blogg_follows ON blogg_posts.user_id = blogg_follows.following_id
        WHERE blogg_follows.follower_id = ?
        ORDER BY blogg_posts.created_at DESC
    ");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

?>