<?php

$phrases = array("Welcome to your stuff",
                 "This is your warehouse",
                 "Can I save anything for you?",
                 "Let me hold that for you",
                 "I'll take this from here!",
                 "Of course I can keep a secret",
                 "Need a hand?",
                 "Stop mailing files to yourself");

$phrase = $phrases[array_rand($phrases)];

$colors = array("#636fa4", "#23c086", "#3494e6", "#ec6ead");
$color = $colors[array_rand($colors)];

function getRandomUiGradient() {
    $gradientsJson = file_get_contents("backend/gradients.json");
    $uiGradients = json_decode($gradientsJson);

    $gradient = $uiGradients[array_rand($uiGradients)];
    $color = $gradient->colors;

    return $color;
}

$color = getRandomUiGradient()[0];

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, user-scalable=no">

    <title>fileees</title>

    <link href="https://fonts.googleapis.com/css?family=Pacifico" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet" />

    <style>
        .logo {
            color: <?php echo $color ?>;
        }

        .button {
            color: <?php echo $color ?>;
            border-color: <?php echo $color ?>;
        }

        .button:hover {
            background: <?php echo $color ?>;
        }

        .banner {
            background: <?php echo $color ?>;
        }

    </style>

</head>
<body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<div class="banner">
    <div class="bannerWrap">
        <div id="urlIcon" class="bannerProp">filee.es/</div>
        <span id="ampersandIcon">&</span>
        <form action="folder.php" method="get"><div class="input" id="token" contenteditable="true"></div></form>
        <div id="goIcon" class="bannerProp">Go</div>
    </div>
</div>

<div class="centerwrap">

    <div class="middle">

        <div class="title big"><?php echo $phrase ?></div>

        <a href="new_folder.php"><div class="button big">Create a folder</div></a>

    </div>

</div>

<div class="footer"><span class="logo">filee.es</span> · All files are stored using AES-128 encryption.</div>

<script>

    var tokenDiv = $("#token");
    var ampersandDiv = $("#ampersandIcon");
    var urlDiv = $("#urlIcon");
    var goDiv = $("#goIcon");

    tokenDiv.keypress(function(e) {
        if (e.which == 13) {
            goToFolder();
            e.preventDefault();
        }
    });

    tokenDiv.hover(function(e) {
        if (!tokenDiv.is(":focus")) {
            peekProps();
        }
    }, function (e) {
        if (!tokenDiv.is(":focus")) {
            hideProps();
        }
    });

    tokenDiv.focus(function(e) {
        showProps();
    });

    tokenDiv.blur(function(e) {
        hideProps();
    });

    goDiv.click(function(e){
        goToFolder();
    });

    function peekProps() {
        urlDiv.css("opacity", .2);
        goDiv.css("opacity", .2);
    }

    function showProps() {
        urlDiv.css("opacity", .7);
        goDiv.css("opacity", .7);
    }

    function hideProps() {
        urlDiv.css("opacity", 0);
        goDiv.css("opacity", 0);
    }

    function goToFolder() {
        window.location.href = "folder.php?folder=" + tokenDiv.html().replace(/\s+/g, '');
    }


</script>

</body>
</html>
