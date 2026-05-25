<?php

/**
 * @file functions.php
 * Innehåller alla hjälpfunktioner för Tundlr-applikationen.
 * Hanterar databasanslutning, användare, inlägg, kommentarer,
 * taggar, likes och följare.
 */

session_start();

/**
 * Skapar och returnerar en databasanslutning via MySQLi.
 *
 * @return mysqli En aktiv databasanslutning.
 */
function connectToDb(): mysqli {
    $dbHost     = 'ostrawebb.se';
    $dbUser     = 'wsp2526_isawes';
    $dbPassword = 'jupigedi69';
    $dbDatabase = 'wsp2526_isawes';

    $db = new mysqli($dbHost, $dbUser, $dbPassword, $dbDatabase);

    if ($db->connect_error) {
        die("Connection failed: " . $db->connect_error);
    }

    return $db;
}

/**
 * Hämtar en användare från databasen baserat på användarnamn.
 *
 * @param mysqli $db       Aktiv databasanslutning.
 * @param string $username Användarnamnet att söka efter.
 * @return array|null      Associativ array med användardata, eller null om ingen hittas.
 */
function getUserByUsername(mysqli $db, string $username): ?array {
    $stmt = $db->prepare("SELECT * FROM blogg_users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    return $res->fetch_assoc();
}

/**
 * Skapar ett nytt användarkonto med hashat lösenord.
 *
 * @param mysqli $db       Aktiv databasanslutning.
 * @param string $username Önskat användarnamn.
 * @param string $password Lösenord i klartext (hashas innan lagring).
 * @param string $email    Användarens e-postadress.
 * @return void
 */
function createUser(mysqli $db, string $username, string $password, string $email): void {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO blogg_users(username,password,email) VALUES(?,?,?)");
    $stmt->bind_param("sss", $username, $hash, $email);
    $stmt->execute();
}

/**
 * Kontrollerar om användaren är inloggad.
 * Omdirigerar till index.php med ett felmeddelande om inte.
 *
 * @return void
 */
function isLoggedIn(): void {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        redirectWithMessage("index.php", "Du måste logga in först", "login");
    }
}

/**
 * Sparar ett meddelande i sessionen och omdirigerar användaren.
 *
 * @param string $url URL att omdirigera till.
 * @param string $msg Meddelandet som ska visas.
 * @param string $key Sessionsnyckeln meddelandet lagras under.
 * @return void
 */
function redirectWithMessage(string $url, string $msg, string $key): void {
    $_SESSION["message"][$key] = $msg;
    header("Location:$url");
    exit();
}

/**
 * Skriver ut ett sessionmeddelande och tar sedan bort det.
 *
 * @param string $key Sessionsnyckeln för meddelandet.
 * @return void
 */
function writeMessage(string $key): void {
    if (isset($_SESSION["message"][$key])) {
        echo '<p style="color:red;margin:10px 0;">' . $_SESSION["message"][$key] . '</p>';
        unset($_SESSION["message"][$key]);
    }
}

/**
 * Hämtar alla inlägg sorterade efter datum (nyaste först),
 * med tillhörande användarnamn via JOIN.
 *
 * @param mysqli $db Aktiv databasanslutning.
 * @return mysqli_result Resultset med alla inlägg.
 */
function getPosts(mysqli $db): mysqli_result {
    return $db->query(
        "SELECT blogg_posts.*, blogg_users.username
         FROM blogg_posts
         JOIN blogg_users ON blogg_posts.user_id = blogg_users.id
         ORDER BY blogg_posts.created_at DESC"
    );
}

/**
 * Sparar ett nytt inlägg i databasen.
 *
 * @param mysqli      $db      Aktiv databasanslutning.
 * @param int         $user_id ID för den inloggade användaren.
 * @param string      $title   Inläggets rubrik.
 * @param string      $content Inläggets textinnehåll.
 * @param string|null $image   Filnamn på uppladdad bild, eller null om ingen bild.
 * @return void
 */
function savePost(mysqli $db, int $user_id, string $title, string $content, ?string $image = null): void {
    $stmt = $db->prepare("INSERT INTO blogg_posts(user_id, title, content, image, created_at) VALUES(?,?,?,?,NOW())");
    $stmt->bind_param("isss", $user_id, $title, $content, $image);
    $stmt->execute();
}

/**
 * Sparar en kommentar kopplad till ett specifikt inlägg.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $user_id ID för kommentarens författare.
 * @param int    $post_id ID för inlägget kommentaren tillhör.
 * @param string $comment Kommentarens textinnehåll.
 * @return void
 */
function saveComment(mysqli $db, int $user_id, int $post_id, string $comment): void {
    $stmt = $db->prepare("INSERT INTO blogg_comments(user_id,post_id,comment,created_at) VALUES(?,?,?,NOW())");
    $stmt->bind_param("iis", $user_id, $post_id, $comment);
    $stmt->execute();
}

/**
 * Hämtar alla kommentarer för ett inlägg, sorterade kronologiskt.
 * Inkluderar användarnamn via JOIN.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $post_id ID för inlägget vars kommentarer hämtas.
 * @return mysqli_result Resultset med kommentarerna.
 */
function getComments(mysqli $db, int $post_id): mysqli_result {
    $stmt = $db->prepare(
        "SELECT blogg_comments.*, blogg_users.username
         FROM blogg_comments
         JOIN blogg_users ON blogg_comments.user_id = blogg_users.id
         WHERE blogg_comments.post_id = ?
         ORDER BY blogg_comments.created_at ASC"
    );
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Tar bort ett inlägg, men endast om det tillhör den angivna användaren.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $post_id ID för inlägget som ska tas bort.
 * @param int    $user_id ID för den inloggade användaren (ägarskyddskontroll).
 * @return void
 */
function deletePost(mysqli $db, int $post_id, int $user_id): void {
    $stmt = $db->prepare("DELETE FROM blogg_posts WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
}

/**
 * Uppdaterar en användares profilinformation.
 *
 * @param mysqli $db              Aktiv databasanslutning.
 * @param int    $user_id         ID för användaren vars profil uppdateras.
 * @param string $bio             Användarens biografi.
 * @param string $profile_picture Filnamn på profilbilden.
 * @param string $banner_image    Filnamn på bannerbilden.
 * @return void
 */
function updateProfile(
    mysqli $db,
    int $user_id,
    string $bio,
    string $profile_picture,
    string $banner_image
): void {
    $stmt = $db->prepare(
        "UPDATE blogg_users
         SET bio=?,
             profile_picture=?,
             banner_image=?
         WHERE id=?"
    );
    $stmt->bind_param("sssi", $bio, $profile_picture, $banner_image, $user_id);
    $stmt->execute();
}

/**
 * Hämtar alla inlägg skapade av en specifik användare, nyaste först.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $user_id ID för den användare vars inlägg hämtas.
 * @return mysqli_result Resultset med användarens inlägg.
 */
function getUserPosts(mysqli $db, int $user_id): mysqli_result {
    $stmt = $db->prepare(
        "SELECT * FROM blogg_posts WHERE user_id=? ORDER BY created_at DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Lägger till eller tar bort en like på ett inlägg (toggle).
 * Om användaren redan gillat inlägget tas liken bort, annars läggs den till.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $user_id ID för användaren som gillar/ogillar.
 * @param int    $post_id ID för inlägget.
 * @return void
 */
function likePost(mysqli $db, int $user_id, int $post_id): void {
    $check = $db->prepare("SELECT id FROM blogg_likes WHERE user_id=? AND post_id=?");
    $check->bind_param("ii", $user_id, $post_id);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        $stmt = $db->prepare("DELETE FROM blogg_likes WHERE user_id=? AND post_id=?");
    } else {
        $stmt = $db->prepare("INSERT INTO blogg_likes (user_id, post_id) VALUES (?, ?)");
    }

    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
}

/**
 * Returnerar antalet likes ett inlägg har fått.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $post_id ID för inlägget.
 * @return int Antal likes.
 */
function getLikeCount(mysqli $db, int $post_id): int {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM blogg_likes WHERE post_id=?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()["count"];
}

/**
 * Kontrollerar om en specifik användare har gillat ett inlägg.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $user_id ID för användaren.
 * @param int    $post_id ID för inlägget.
 * @return bool True om användaren har gillat inlägget, annars false.
 */
function hasLiked(mysqli $db, int $user_id, int $post_id): bool {
    $stmt = $db->prepare("SELECT id FROM blogg_likes WHERE user_id=? AND post_id=?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Sparar taggar kopplade till ett inlägg.
 * Tar bort eventuella befintliga taggar för inlägget innan de nya sparas.
 * Taggar rensas från #-tecken, görs till gemener och trimmas.
 *
 * @param mysqli $db          Aktiv databasanslutning.
 * @param int    $post_id     ID för inlägget.
 * @param string $tags_string Kommaseparerad sträng med taggar, t.ex. "foto, konst, #natur".
 * @return void
 */
function saveTags(mysqli $db, int $post_id, string $tags_string): void {
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

/**
 * Hämtar alla taggar kopplade till ett specifikt inlägg.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $post_id ID för inlägget.
 * @return mysqli_result Resultset med taggar.
 */
function getTagsForPost(mysqli $db, int $post_id): mysqli_result {
    $stmt = $db->prepare("SELECT tag FROM blogg_tags WHERE post_id=?");
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Hämtar alla inlägg som är märkta med en specifik tagg.
 * Inkluderar användarnamn och sorteras med nyaste inlägg först.
 *
 * @param mysqli $db  Aktiv databasanslutning.
 * @param string $tag Taggen att filtrera på (utan #).
 * @return mysqli_result Resultset med matchande inlägg.
 */
function getPostsByTag(mysqli $db, string $tag): mysqli_result {
    $stmt = $db->prepare(
        "SELECT blogg_posts.*, blogg_users.username
         FROM blogg_posts
         JOIN blogg_users ON blogg_posts.user_id = blogg_users.id
         JOIN blogg_tags ON blogg_posts.id = blogg_tags.post_id
         WHERE blogg_tags.tag = ?
         ORDER BY blogg_posts.created_at DESC"
    );
    $stmt->bind_param("s", $tag);
    $stmt->execute();
    return $stmt->get_result();
}

/**
 * Hämtar en användare från databasen baserat på användar-ID.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $user_id ID för användaren.
 * @return array|null Associativ array med användardata, eller null om ingen hittas.
 */
function getUserById(mysqli $db, int $user_id): ?array {
    $stmt = $db->prepare("SELECT * FROM blogg_users WHERE id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

/**
 * Låter en användare följa eller sluta följa en annan (toggle).
 * Om relationen redan finns tas den bort, annars skapas den.
 *
 * @param mysqli $db           Aktiv databasanslutning.
 * @param int    $follower_id  ID för den användare som följer.
 * @param int    $following_id ID för den användare som följs.
 * @return void
 */
function followUser(mysqli $db, int $follower_id, int $following_id): void {
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

/**
 * Kontrollerar om en användare följer en annan.
 *
 * @param mysqli $db           Aktiv databasanslutning.
 * @param int    $follower_id  ID för den potentiella följaren.
 * @param int    $following_id ID för den potentiellt följda användaren.
 * @return bool True om följarrelationen finns, annars false.
 */
function isFollowing(mysqli $db, int $follower_id, int $following_id): bool {
    $stmt = $db->prepare("SELECT id FROM blogg_follows WHERE follower_id=? AND following_id=?");
    $stmt->bind_param("ii", $follower_id, $following_id);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

/**
 * Returnerar antalet följare en användare har.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $user_id ID för användaren.
 * @return int Antal följare.
 */
function getFollowerCount(mysqli $db, int $user_id): int {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM blogg_follows WHERE following_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()["count"];
}

/**
 * Returnerar antalet användare som en specifik användare följer.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $user_id ID för användaren.
 * @return int Antal följda användare.
 */
function getFollowingCount(mysqli $db, int $user_id): int {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM blogg_follows WHERE follower_id=?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return (int) $stmt->get_result()->fetch_assoc()["count"];
}

/**
 * Hämtar ett flöde med inlägg från alla användare som den inloggade följer.
 * Sorteras med nyaste inlägg först.
 *
 * @param mysqli $db      Aktiv databasanslutning.
 * @param int    $user_id ID för den inloggade användaren.
 * @return mysqli_result Resultset med inlägg från följda användare.
 */
function getFollowingFeed(mysqli $db, int $user_id): mysqli_result {
    $stmt = $db->prepare(
        "SELECT blogg_posts.*, blogg_users.username
         FROM blogg_posts
         JOIN blogg_users ON blogg_posts.user_id = blogg_users.id
         JOIN blogg_follows ON blogg_posts.user_id = blogg_follows.following_id
         WHERE blogg_follows.follower_id = ?
         ORDER BY blogg_posts.created_at DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    return $stmt->get_result();
}