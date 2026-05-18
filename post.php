<?php
require_once("functions.php");
$db = connectToDb();
isLoggedIn();

$user = getUserByUsername($db, $_SESSION["username"]);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title   = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $tags    = trim($_POST["tags"] ?? "");
    $image   = null;

    if (!empty($title) && !empty($content)) {

        if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0 && $_FILES["image"]["size"] > 0) {
            $allowed_exts  = ["jpg", "jpeg", "png", "webp"];
            $allowed_mimes = ["image/jpeg", "image/png", "image/webp"];

            $ext  = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $mime = mime_content_type($_FILES["image"]["tmp_name"]);

            if (in_array($ext, $allowed_exts) && in_array($mime, $allowed_mimes)) {
                $fileName = time() . "_" . uniqid() . "." . $ext;
                move_uploaded_file($_FILES["image"]["tmp_name"], "uploads/" . $fileName);
                $image = $fileName;
            }
        }

        savePost($db, $_SESSION["userId"], $title, $content, $image);

        // Hämta det nya inläggets ID och spara taggar
        $new_post_id = $db->insert_id;
        if (!empty($tags)) {
            saveTags($db, $new_post_id, $tags);
        }

        header("Location: members.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="sv">
<head>
<meta charset="UTF-8">
<title>Nytt inlägg</title>
<link rel="stylesheet" href="css/main.css">
</head>
<body>

<div class="sidebar">
    <div class="logo">tun<span>dlr</span></div>

    <nav>
        <a href="profile.php">Profil</a>
        <a href="members.php">Flödet</a>
        <a href="following.php">Följer</a>
        <a class="logout-btn" href="index.php">Logga ut</a>
    </nav>

    <a href="post.php" class="new-post-btn">✎ Nytt inlägg</a>
</div>

<div class="main-content">
    <div class="feed">
        <div class="create-post-card">

            <div class="create-post-header">
                <img class="small-avatar"
                     src="uploads/<?php echo htmlspecialchars($user['profile_picture'] ?? 'default.png'); ?>"
                     alt="profilbild">
                <div>
                    <h1>Skapa nytt inlägg</h1>
                    <p>Dela något med världen.</p>
                </div>
            </div>

            <form method="post" enctype="multipart/form-data">

                <input type="text" name="title" placeholder="Rubrik" required>

                <textarea name="content" placeholder="Vad tänker du på?" required></textarea>
                <input type="text" name="tags" placeholder="Taggar: foto, konst, känslor (kommaseparerade)">

                <div class="image-upload-area" id="uploadArea" onclick="document.getElementById('imageInput').click()">
                    <div class="image-upload-placeholder" id="uploadPlaceholder">
                        <span class="upload-icon">🖼</span>
                        <p>Klicka för att lägga till bild</p>
                        <p class="upload-hint">JPG, PNG, WEBP</p>
                    </div>
                    <img id="imagePreview" class="image-preview hidden" alt="Förhandsgranskning">
                </div>

                <input type="file" id="imageInput" name="image" accept="image/*" style="display:none">

                <div class="upload-actions hidden" id="uploadActions">
                    <button type="button" class="remove-image-btn" onclick="removeImage()">✕ Ta bort bild</button>
                </div>

                <input type="submit" value="Publicera">

            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('imageInput').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        document.getElementById('uploadPlaceholder').classList.add('hidden');
        const preview = document.getElementById('imagePreview');
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        document.getElementById('uploadActions').classList.remove('hidden');
    };
    reader.readAsDataURL(file);
});

function removeImage() {
    document.getElementById('imageInput').value = '';
    document.getElementById('imagePreview').src = '';
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('uploadPlaceholder').classList.remove('hidden');
    document.getElementById('uploadActions').classList.add('hidden');
}
</script>

</body>
</html>