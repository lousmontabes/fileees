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
    <title>Files</title>

    <link href="https://fonts.googleapis.com/css?family=Pacifico" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet" />

    <style>

        html, body {
            height: 100%;
            margin: 0;
        }

        .middle {
            height: 300px;
        }

        .footer {
            position:fixed;
            bottom:0;
            height:1.5em;
            padding:1.9em 1em;
            width: 100vw;
            color: grey;
            font-family: "Open Sans";
            font-weight: 100;
            font-size: 12px;
        }

        .logo {
            color: <?php echo $color ?>;
            font-family: "Pacifico";
            font-size: 18px;
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
            padding: .5em;
            color: white;
            font-family: "open sans";
            text-align: center;
            font-weight: 100;
        }

        form {
            display: inline;
        }

        .input {
            display: inline-block;
            transition: 0.3s;
            background: transparent;
            border: none;
            border-bottom: 1px solid white;
            color: white;
            font-family: "open sans";
            font-size: 16px;
            text-align: center;
            min-width: 7em;
            font-weight: 400;
            opacity: 0.75;
            padding: .1em .5em;
        }

        .input:focus {
            outline: none;
            opacity: 1;
        }

    </style>

</head>
<body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<div class="banner">Enter token: <form action="folder.php" method="get"><div class="input" id="token" contenteditable="true"></div></form> </div>

<div class="centerwrap">

    <div class="middle">

        <div class="title big"><?php echo $phrase ?></div>

        <a href="new_folder.php"><div class="button big">Create a folder</div></a>

    </div>

</div>

<div class="footer"><span class="logo">filee.es</span> · All files are stored using AES-128 encryption.</div>

<script>

    var tokenDiv = $("#token");

    tokenDiv.keypress(function(e) {
        if (e.which == 13) {
            window.location.href = "folder.php?folder=" + tokenDiv.html().replace(/\s+/g, '');
            e.preventDefault();
        }
    });

</script>

</body>
</html>