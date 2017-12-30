<?php

require_once("./backend/connection.php");

if (!isset ($_GET['folder'])) {
    // ERROR: The 'folder' GET parameter is not set.
} else {
    
    $folderToken = mysqli_real_escape_string($con, $_GET['folder']);

    $result = mysqli_query($con, "SELECT * FROM folders WHERE token = '{$folderToken}'");
    $row = mysqli_fetch_array($result);

    $folderId = $row['id'];
    $folderName = $row['name'];

    $files = [];

    $result = mysqli_query($con, "SELECT * FROM files WHERE folder = '$folderId'");
    while ($row = mysqli_fetch_array($result)) {
        array_push($files, $row);
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $folderName?> on Fileees</title>

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
    </style>

</head>
<body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="libraries/dropzone.js"></script>

<div class="centered area">

    <div class="titlewrap big">

        <div id="title" class="title big" style="margin-left: 10px" contenteditable="true" spellcheck="false"><?php echo $folderName ?></div>

    </div>

    <div class="grid" id="mainGrid">

        <?php include ("modules/grid.php") ?>

    </div>

</div>

<div id="dummyItem">
    <div class="animated item" id="lastItem">
        <div class="view jpg">

            <div class="preview">
                <div class="previewContent">
                    <img src="img/fileicon.svg" id="fileicon">
                    <div class="extension"></div>
                </div>
            </div>

            <div class="overlay">
                <div class="overlayContent">
                    <br></div>
            </div>

        </div>
        <div class="name">Uploading...</div>
    </div>
</div>

<div class="footer"><a href="./"><span class="logo">filee.es</span></a> · All files are stored using AES-128 encryption.</div>

<script>

    var folderName = "<?php echo $folderName ?>";
    var titleDiv = $("#title");
    var mainGridDiv = $("#mainGrid");
    var nextItemId = <?php echo $i ?>;
    var lastItem = $("#item<?php echo $i - 1 ?>");
    var dummyItem = $("#dummyItem");

    var dropzone = new Dropzone("div#mainGrid", {
        url: "backend/s3upload.php",
        init: function() {
            this.on("sending", function(file, xhr, formData){
                formData.append("folderId", "<?php echo $folderId ?>");
            });
        }
    });

    dropzone.on("addedfile", function(file) {
        if (nextItemId == 0) $("#emptystate").css("display", "none");
        addDummyItem();
    });
    
    dropzone.on("success", function(file, response) {

        $.post("modules/grid.php", {folder: <?php echo $folderId ?>} ).done(function(response) {
            mainGridDiv.removeClass("dragging");
            updateMainGrid(response);
        });
        
    });

    dropzone.on("dragover", function() {
       mainGridDiv.addClass("dragging");
    });

    dropzone.on("dragleave", function () {
        mainGridDiv.removeClass("dragging");
    });

    function updateMainGrid(newHtml) {
        mainGridDiv.html(newHtml);
    }

    function addDummyItem() {

        dummyItem.children().attr('id', 'item' + nextItemId);
        lastItem = $("#item" + nextItemId);

        mainGridDiv.append(dummyItem.html());

        nextItemId++;

    }

    titleDiv.keypress(function(e) {
        if (e.which == 13) {
            titleDiv.blur();
            document.getSelection().removeAllRanges();
            e.preventDefault();
        }
    });

    titleDiv.focus(function() {
        window.getSelection().selectAllChildren(document.getElementById("title"));
        titleDiv.addClass("greyed");
    });

    titleDiv.blur(function() {
        enterNewTitle();
    });

    function enterNewTitle() {

        var newName = titleDiv.html();

        if (newName.trim().length != 0) {
            folderName = newName;
            $.post("backend/change_folder_name.php", {id: <?php echo $folderId ?>, name: newName}).done(function(){
               titleDiv.removeClass("greyed");
            });
            updateTitle(newName);
        } else {
            titleDiv.html(folderName);
        }

    }

    /*$("div#mainGrid").dropzone({
        url: "backend/simple_upload.php"

    });*/

    function getFile(id) {
        $("#downloadform" + id).submit();
    }

    function updateTitle(newName) {
        $('head title', window.parent.document).text(newName + ' on Fileees');
    }

</script>

</body>
</html>