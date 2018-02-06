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

    </style>

</head>
<body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
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

<div id="ajax">

</div>

<script>

    var keypair = generateKeypair();

    $.ajax({
        type: "POST",
        url: "backend/create_folder.php",
        data: {publicKey: keypair.public}

    }).done(function(data) {

        var json = jQuery.parseJSON(data);

        if (json.success) {

            // Folder was created successfully
            // Retrieve folder data and perform AJAX

            $.ajax({
                type: 'GET',
                url: 'folder.php',
                data: {folder: json.token}

            }).done(function(data) {
                window.history.pushState("", "Folder", "folder.php#" + keypair.private);
                $('head title').text(json.name + ' on Filee.es');
                $("#ajax").html(data);
                $("#curtain").addClass("bounceOutUp");

            });

        } else {

            // There was an error creating the folder
            // Go back to index

            window.location = "index.php";

        }

    });

</script>

</body>
</html>