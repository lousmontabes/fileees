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
    <title>fileees</title>

    <link href="https://fonts.googleapis.com/css?family=Pacifico" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" />

    <style>

        html, body {
            height: 100%;
            margin: 0;
        }

        .middle {
            padding-bottom: 5vh;
            margin: 5em;
        }

        .logo {
            color: <?php echo $color ?>;
        }

        .button {
            transition: 0.3s;
            padding: 2em;
            color: <?php echo $color ?>;
            font-family: open sans;
            text-align: center;
            border-radius: 6px;
            font-weight: 400;
            border: 2px solid <?php echo $color ?>;
            border-bottom-width: 4px;
        }

        .button:hover {
            transform: scale(1.07);
            background: <?php echo $color ?>;
            color: white;
        }

        .button:active {
            transform: scale(0.97);
        }

        .grandeur {
            background: #000046;  /* fallback for old browsers */
            background: -webkit-linear-gradient(to bottom, #38ef7d, #11998e);  /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to bottom, #38ef7d, #11998e); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
        }

        .rose {
            background: #E8CBC0;  /* fallback for old browsers */
            background: -webkit-linear-gradient(to right, #636FA4, #E8CBC0);  /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to right, #636FA4, #E8CBC0); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
        }

        .vicecity {
            background: #3494E6;  /* fallback for old browsers */
            background: -webkit-linear-gradient(to right, #EC6EAD, #3494E6);  /* Chrome 10-25, Safari 5.1-6 */
            background: linear-gradient(to right, #EC6EAD, #3494E6); /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
        }

        .banner {
            position: fixed;
            top: 0;
            width: 100vw;
            background: <?php echo $color ?>;
            padding: .5em 0;
            color: white;
            font-family: "open sans";
            text-align: center;
            font-weight: 100;
        }

        .bannerWrap {
            display: inline-block;
            position: relative;
            margin-left: auto;
            margin-right: auto;
        }

        form {
            display: inline;
        }

        .input {
            display: inline-block;
            transition: 0.3s;
            border: none;
            color: white;
            font-family: "Open Sans";
            font-size: 16px;
            text-align: center;
            min-width: 7em;
            font-weight: 400;
            opacity: 0.75;
            background: rgba(255, 255, 255, 0.5);
            border-bottom: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 3px;
            padding: .1em 20px;
            padding-right: .6em;
        }

        .input:hover, .input:focus{
            min-width: 9em;
        }

        .input:focus {
            min-width: 12em;
            outline: none;
            opacity: 1;
        }

        #ampersandIcon {
            height: 16px;
            position: absolute;
            opacity: .7;
            margin: 2px 5px;
            font-weight: 500;
        }

        .bannerProp {
            display: inline-block;
            transition: .2s;
            opacity: 0;
            margin: 2px 5px;
            font-weight: 500;
            width: 60px;
            float: left;
            overflow: hidden;
            text-align: left;
            cursor: default;
        }

        #urlIcon {
            float: left;
        }

        #goIcon {
            float: right;
            background: rgba(255,255,255,0.5);
            border-radius: 6px;
            text-align: center;
            padding: .1em;
            width: 30px;
            margin: 1px 5px;
            margin-right: 27px;
            cursor: pointer;
        }

        #goIcon:hover {
            transform: scale(1.1);
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
