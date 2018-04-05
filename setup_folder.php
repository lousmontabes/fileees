<?php
session_start();
require_once("backend/connection.php");

// If user is logged in and pbkdf2 of password is available,
// store relation and encrypted private key in database.
$loggedIn = isset($_SESSION['user_id']) && isset($_SESSION['pbkdf2']);

if ($loggedIn) {

    // Retrieve salt for current user
    $result = mysqli_query($con, "SELECT salt FROM users WHERE id = {$_SESSION['user_id']}");
    $row = mysqli_fetch_array($result);
    $salt = $row['salt'];

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, user-scalable=no">

    <title>Creating new folder on Filee.es</title>

    <link href="https://fonts.googleapis.com/css?family=Pacifico" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet" />
    <link href="libraries/animate.css" rel="stylesheet" />

    <style>

        .logo {
            transition: 0.2s;
        }

        .logo:hover {
            opacity: 0.5;
        }

        @media (max-width: 500px) {

            /* Mobile tweaks */
            .item {
                min-width: 140px;
            }

            .item .previewContent {
                padding: 1em;
            }

            .titlewrap {
                margin-top: 10px;
                margin-bottom: 0;
            }

        }

        #curtain {
            background: #<?php echo $_GET['c'] ?>;
            position: fixed;
            left: 0;
            top: -5vh;
            width: 100vw;
            height: 110vh;
            margin: 0;
            overflow: hidden;
            z-index: 1000;
        }

        .keyurl {
            transition: .3s;
            margin: .9em 0;
            padding: .1em 0;
            color: #11998e;
            font-weight: bold;
            display: block;
            width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        #keyurlFade {
            transition: .3s;
            width: 100%;
            height: 1.5em;
            position: absolute;
            left: 0;
            background: rgba(255,255,255,0.95);
            z-index: 10;
            text-align: center;
            opacity: 0;
            cursor: pointer;
            color: #11998e;
            font-weight: 500;
        }

        #keyurlFade:hover {
            opacity: 1;
        }

        #keyurlFade:active #keyurlFadeText {
            transform: scale(.9);
        }

        #keyurlFadeText {
            transition: .2s;
        }

        .small {
            font-size: 16px;
            max-width: 100%;
        }

        .emptystate h2 {
            margin-top: 0;
        }

    </style>

</head>
<body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js"></script>
<script src="libraries/crypto/aes.js"></script>
<script src="libraries/crypto/pbkdf2.js"></script>
<script src="libraries/crypto/ecc-min.js"></script>
<script src="libraries/crypto/encryption.js"></script>
<script src="libraries/dropzone.js"></script>

<div class="emptystate animated" id="curtain">
    <div class="centerwrap">
        <img src="img/ball.svg" width="30">
        <div style="color:white">Getting everything ready...</div>
    </div>
</div>

<div id="keymessage" style="display:none">
    <h2>This is your unique download link:</h2>
    <div class="clipboard" id="keyurlFade" data-clipboard-text="">
        <div id="keyurlFadeText" style="border-bottom:2px solid #11998e; display:inline-block;">Copy</div>
    </div>
    <div class="keyurl" id="keyurl"></div>
    Only share this link with people you want to be able to download files from this folder.<br>
    <b>This link can not be retrieved if lost</b> - make sure to keep hold of it!<br>
    <br>
    Drag and drop to upload something amazing!<br>
    <span style="font-size:12px"><i>This message will only be shown once</i></span>
</div>

<div id="ajax">
    <!-- This will be replaced by folder html -->
</div>

<script>

    var clipboard = new Clipboard('.clipboard');
    var keypair = generateKeypair();
    var loggedIn = false;
    var pbkdf2 = "<?php echo $_SESSION['pbkdf2'] ?>";

    var encryptedPrivateKey = encryptAES(keypair.private, pbkdf2).toString();

    <?php if ($loggedIn) { ?>
    loggedIn = true;
    <?php } ?>

    $.ajax({
        type: "POST",
        url: "backend/create_folder.php",
        data: {publicKey: keypair.public}

    }).done(function(data) {

        var json = jQuery.parseJSON(data);

        if (json.success) {

            // Folder was created successfully

            // If user is logged in, generate user-folder relation
            if (loggedIn) {

                $.ajax({
                    type: "POST",
                    url: "backend/create_user_folder_relation.php",
                    data: {token: json.token, encrypted_key: encryptedPrivateKey}
                }).done(function(response) {
                    console.log(response);
                });

            }

            // Generate key url to display to user
            var keyurl = "filee.es/&" + json.token + "#" + keypair.private;
            $("#keyurlFade").attr("data-clipboard-text", keyurl);
            $("#keyurl").html(keyurl);

            var keymessage = $("#keymessage").html();

            // Retrieve folder html from folder data and perform AJAX
            $.ajax({
                type: 'GET',
                url: 'folder.php',
                data: {folder: json.token}

            }).done(function(data) {
                window.history.replaceState("", json.name + " on Filee.es", "folder.php?folder=" + json.token + "#" + keypair.private);
                $('head title').text(json.name + ' on Filee.es');
                $("#ajax").html(data);
                $("#emptymessage").html(keymessage);
                $("#keymessage").html("");
                $("#emptymessage").addClass("small");
                $("#curtain").addClass("bounceOutUp");

            });

        } else {

            // There was an error creating the folder
            // Go back to index

            window.location = "index.php";

        }

    });

    clipboard.on('success', function(e) {
        $("#keyurlFade").css("opacity", 1);
        $("#keyurlFadeText").css("border-bottom", "none");
        $("#keyurlFadeText").html("Copied to clipboard <img src='./img/check.svg' height='14'>");
    });

</script>

</body>
</html>