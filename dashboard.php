<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 11/4/18
 * Time: 11:31
 */

session_start();
$loggedIn = (isset($_SESSION['pbkdf2']) && isset($_SESSION['user_id']));

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, user-scalable=no">

    <title>Filee.es / Your dashboard</title>

    <link href="https://fonts.googleapis.com/css?family=Pacifico" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet" />
    <link href="css/animations.css" rel="stylesheet" />
    <link href="libraries/animate.css" rel="stylesheet" />

    <style>

        .splash {
            position: relative;
            color: white;
            width: 100vw;
            background: #4AC29A;
            font-family: "Open sans";
            padding-top: 5em;
            padding-bottom: 2.5em;
            margin-bottom: 4em;
        }

        .splash .header {
            font-size: 32px;
            font-weight: 100;
        }

        .splash .tagline {
            font-size: 32px;
            font-weight: 400;
        }

        .button {
            color: inherit;
            cursor: pointer;
            transition: .1s;
            padding: 1.25em;
            margin-right: .2em;
            background: transparent;
        }

        .button.ghost {
            border-color: transparent;
        }

        .button.green:hover {
        }

        .button.red:hover {
            background: red;
        }

        .button:active, .button:focus {
            outline: none;
        }

        .emptymessage {
            font-family: "Open Sans";
            font-weight: 100;
            color: grey;
            padding: 1em;
        }

        .header.preview .title {
            font-size: 32px;
        }

        .header.preview .subtitle {
            width: auto;
        }

        .logo {
            color: white;
            position: absolute;
            top: 2em;
            font-weight: 100;
        }

    </style>

</head>

<body>

    <div class="splash">

        <div class="area centered">

            <div class="logo">filee.es</div>

            <div class="header">Hello, Lluís</div>
            <div class="tagline">Welcome to your stuff</div>
            <br>
            <div class="splash-banner">
                <button class="button green">
                    Create new folder
                </button>
                <button class="button green">
                    Check out existing folder
                </button>
            </div>

        </div>

    </div>

    <div id="userFolders">

        <!-- Folder previews load via AJAX -->

    </div>

</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="libraries/crypto/aes.js"></script>
<script src="libraries/crypto/pbkdf2.js"></script>
<script src="libraries/crypto/ecc-min.js"></script>
<script src="libraries/crypto/encryption.js"></script>
<script src="libraries/dropzone.js"></script>

<script>

    var pbkdf2 = "";

    <?php if ($loggedIn) { ?>
    pbkdf2 = "<?php echo $_SESSION['pbkdf2'] ?>";
    <?php } ?>

    var folderPreviewDummy = $("#folderPreviewDummy");

    <?php if ($loggedIn) { ?>
    retrieveUserFolders();
    <?php } ?>

    function retrieveUserFolders() {

        var folders;

        $.ajax("backend/get_session_user_folders.php", {success: function(response) {

            var key;
            folders = jQuery.parseJSON(response);

            if (folders.length > 0) {

                folders.forEach(function (folder) {
                    key = decryptKey(folder.encrypted_key);
                    addFolderPreview(folder.name, folder.token, key);
                });

            } else {

                // User has no folders yet
                showEmptyStateView();

            }

        }});

    }

    function addFolderPreview(name, token, key) {
        
        $.get("modules/folder_preview.php", {folder: token}).done(function(response) {
            var finalHtml = "<a href='folder.php?folder=" + token + "#" + key + "'>" + response + "</a>";
            $("#userFolders").append(finalHtml);
        });

    }

    function decryptKey(encrypted) {
        return decryptAES(encrypted, pbkdf2).toString(CryptoJS.enc.Utf8);
    }

</script>