<?php

session_start();

// Check if user is logged in. If so, redirect to dashboard.
$loggedIn = (isset($_SESSION['pbkdf2']) && isset($_SESSION['user_id']));
if ($loggedIn) header("Location: ./dashboard.php");

function getRandomWelcomePhrase() {
    $phrases = file("backend/welcome_phrases.txt");
    return $phrases[array_rand($phrases)];
}

$phrase = getRandomWelcomePhrase();

function getRandomColor() {
    $colors = file("backend/colors.txt");
    return $colors[array_rand($colors)];
}

$color = getRandomColor();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, user-scalable=no">

    <title>Filee.es</title>

    <link href="https://fonts.googleapis.com/css?family=Pacifico" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400" rel="stylesheet">

    <link href="css/style.css" rel="stylesheet" />

    <style>

        ::selection {
            background: <?php echo $color ?>;
        }

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

        .register-login {
            color: <?php echo $color ?>;
        }

        .register-login:hover {
            border-bottom: 1px solid <?php echo $color ?>;
        }

        .form-input {
            color: <?php echo $color ?>;
        }

        .form-button {
            color: <?php echo $color ?>;
            border-color: <?php echo $color ?>;
        }

        .registerBenefitsMessage {
            text-align: center;
            font-family: Europa, sans-serif;
            color: grey;
            font-weight: 100;
            margin: 1em 0;
        }

        .folderPreview .url, .folderPreview .left:hover .title {
            color: <?php echo $color ?>;
        }

        .folderPreview .right:hover {
            transform: scale(1.1);
            background: <?php echo $color ?>;
        }

        .folderPreview .right:active {
            transform: scale(.85);
        }

        .logOutButton:hover {
            background: <?php echo $color ?>;
        }

        #errorBanner {
            transition: .5s;
            transition-delay: 1s;
            display: block;
            opacity: 1;
            z-index: 200;
            padding: .7em;
            background: red;
        }

    </style>

</head>
<body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>

<div class="banner" id="errorBanner" style="display:none">
    <div class="bannerWrap">
        The specified folder could not be found.
    </div>
</div>

<div class="banner">
    <div class="bannerWrap">
        <div id="urlIcon" class="bannerProp">filee.es/</div>
        <span id="ampersandIcon">&</span>
        <form action="folder.php" method="get"><div class="input" id="token" contenteditable="true"></div></form>
        <div id="goIcon" class="bannerProp">Go</div>
    </div>
</div>

<div class="splashWrapper">

    <div class="middle" id="splashZone">

        <div class="title big"><?php echo $phrase ?></div>
        <a href="setup_folder.php?c=<?php echo substr($color, 1) ?>"><div class="button big">Create a folder</div></a>

    </div>

</div>

<div class="userWrapper">

    <div id="userZone">

        <?php include("modules/user_zone.php") ?>

    </div>

</div>

<?php include("modules/footer.php") ?>

<div class="blackout" id="loginWrap" onclick="hideLogin()">
    <div class="hover-view" id="loginView">
    </div>
</div>

<script>

    var tokenDiv = $("#token");
    var ampersandDiv = $("#ampersandIcon");
    var urlDiv = $("#urlIcon");
    var goDiv = $("#goIcon");
    var errorBannerDiv = $("#errorBanner");
    var error = (getHashFromUrl() == "error");

    if (error) showErrorBanner();
    else hideErrorBanner();

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

    function getHashFromUrl() {
        return location.hash.replace('#', '');
    }

    function removeHash () {
        history.pushState("", document.title, window.location.pathname + window.location.search);
    }

    function showErrorBanner() {
        errorBannerDiv.css("display", "block");
        setTimeout('errorBannerDiv.css("top", "-100px")', 1);
        removeHash();
    }

    function hideErrorBanner() {
        errorBannerDiv.css("display", "none");
    }

    var loginView = $("#loginView");
    var loginWrap = $("#loginWrap");

    function showLogin(isLogin) {

        loginWrap.addClass("displaying");
        var source = "";

        if (isLogin) {
            source = "modules/login.php";
        } else {
            source = "modules/register.php";
        }

        $.ajax(source).done(function(response) {
            loginView.html(response);
            loginWrap.css("opacity", 1);
            loginView.addClass("displaying");
        });

    }

    function hideLogin() {

        loginWrap.css("opacity", 0);
        setTimeout('loginWrap.removeClass("displaying")', 200);
        loginView.removeClass("displaying");

        setTimeout('loginView.html("Loading...")', 200);

    }

    loginView.click(function(e){
        e.stopPropagation();
    });

</script>

</body>
</html>
