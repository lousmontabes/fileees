<?php

require_once("./backend/connection.php");

if (!isset ($_GET['folder'])) {
    // ERROR: The 'folder' GET parameter is not set.

    $query = array(
        'source' => 'error'
    );

    // Return to homepage
    header("Location: index.php?" . http_build_query($query));

} else {

    $folderToken = mysqli_real_escape_string($con, $_GET['folder']);

    $result = mysqli_query($con, "SELECT * FROM folders WHERE token = '{$folderToken}'");
    $row = mysqli_fetch_array($result);

    $folderId = $row['id'];
    $folderName = $row['name'];
    $publickey = preg_replace( "/\r|\n/", "", $row['public_key']);

    $files = [];

    $result = mysqli_query($con, "SELECT * FROM files WHERE folder = '{$folderId}'");
    while ($row = mysqli_fetch_array($result)) {
        array_push($files, $row);
    }

}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
	<meta name="viewport" content="initial-scale=1, maximum-scale=1, minimum-scale=1, width=device-width, user-scalable=no">

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

        @media (max-width: 750px) {

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

    </style>

</head>
<body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/3.1.2/rollups/aes.js"></script>
<script src="libraries/dropzone.js"></script>
<script src="libraries/crypto/ecc-min.js"></script>
<script src="libraries/crypto/encryption.js"></script>

<div class="centered area">

    <div class="titlewrap big">

        <div id="title" class="title big" style="margin-left: 10px" contenteditable="true" spellcheck="false"><?php echo $folderName ?></div>
        <div class="subtitle" id="encryptedMessage"><img src="img/padlock.svg" width="11px"> Encrypted</div>

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
                    <div class="extension">Encrypting...</div>
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

<?php include ("modules/footer.php") ?>

<script>

    var folderName = "<?php echo $folderName ?>";
    var folderId = <?php echo $folderId ?>;
    var titleDiv = $("#title");
    var mainGridDiv = $("#mainGrid");
    var nextItemId = <?php echo $i ?>;
    var lastItem = $("#item<?php echo $i - 1 ?>");
    var dummyItem = $("#dummyItem");
    var encryptedMessageDiv = $("#encryptedMessage");
    var encryptedMessageHtml = encryptedMessageDiv.html();
    var publicKey = "<?php echo $publickey ?>";
    var privateKey = getPrivateKeyFromUrl();
    var fileReaderUploader = new FileReader();
    var decoder = new TextDecoder();
    var encoder = new TextEncoder();

    fileReaderUploader.onload = function() {

        console.log("Trying to read file");

        var arrayBuffer = this.result,
            array = new Uint8Array(arrayBuffer),
            binaryString = decoder.decode(array);

        console.log(array);

        var encrypted = encryptFile(binaryString);
        uploadFile(encrypted.data, encrypted.key);
    };

    var dropzone = new Dropzone("div#mainGrid", {
        url: "backend/dummy_upload.php",
        init: function() {
            this.on("sending", function(file, xhr, formData){

                fileReaderUploader.readAsArrayBuffer(file);
                formData.append("folderId", folderId);

            });
        }
    });

    function encryptFile(bytes) {
        var encrypted = fullEncrypt(bytes, publicKey);
        return {data: encrypted.data.toString(), key: encrypted.key};
    }

    function uploadFile(data, key) {

        $.ajax({
            type: 'POST',
            url: 'backend/upload_file.php',
            data: {
                folderId: folderId,
                name: "test.txt",
                size: 1970,
                bytes: data,
                key: key
            }
        }).done(function(response) {
            console.log(response);

            $.post("modules/grid.php", {folder: folderId, key: privateKey} ).done(function(response) {
                mainGridDiv.removeClass("dragging");
                updateMainGrid(response);
            });

        });

    }

    dropzone.on("addedfile", function(file) {
        if (nextItemId == 0) $("#emptystate").css("display", "none");
        addDummyItem();
    });
    
    dropzone.on("success", function(file, response) {
        // Delegated to uploadFile(), inside .done()
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
            $.post("backend/change_folder_name.php", {id: folderId, name: newName}).done(function(){
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

    function getFile(id, hash) {

        if (privateKey == "") {

            alert("Couldn't decrypt: no key provided.");

        } else {
            $.ajax({
                type: 'POST',
                url: 'backend/retrieve_file.php',
                data: {id: id, hash: hash}
            }).done(function(response) {

                var json = jQuery.parseJSON(response);
                var data = json.data;
                var key = json.key;

                try {
                    var decrypted = fullDecrypt(data, key, privateKey);
                    var bytes = encoder.encode(decrypted);

                    console.log(bytes);

                    saveFile("test", "txt", bytes);
                } catch (ex) {
                    alert("Couldn't decrypt: incorrect key.");
                }

            });
        }

    }

    function updateTitle(newName) {
        $('head title', window.parent.document).text(newName + ' on Fileees');
    }

    encryptedMessageDiv.hover(function(e) {
        encryptedMessageDiv.html('<img src="img/padlock.svg" width="11px"> Public key: ' + publicKey);
    }, function(e) {
        setTimeout('encryptedMessageDiv.html(encryptedMessageHtml)', 120);
    });

    function getPrivateKeyFromUrl() {
        var hash = location.hash.replace('#', '');
        return br2nl(htmlEntitiesDecode(hash));
    }

    function br2nl(str) {
        return str.replace(/<br\s*\/?>/mg,"\n");
    }

    function htmlEntitiesDecode(str) {
        return String(str).replace(/&amp;/g, '&').replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&quot;/g, '"');
    }

    function htmlEntities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function base64ToArrayBuffer(base64) {
        var binaryString = window.atob(base64);
        var binaryLen = binaryString.length;
        var bytes = new Uint8Array(binaryLen);
        for (var i = 0; i < binaryLen; i++) {
            var ascii = binaryString.charCodeAt(i);
            bytes[i] = ascii;
        }
        return bytes;
    }

    function saveFile(name, extension, bytes) {
        var blob = new Blob([bytes]);
        var link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        var fileName = name + "." + extension;
        link.download = fileName;
        link.click();
    }

</script>

</body>
</html>