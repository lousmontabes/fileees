<?php

require_once("./backend/connection.php");

function returnToIndex($error = false) {
    if ($error) $suffix = "#error";
    else $suffix = "";
    header("Location: index.php" . $suffix);
}

if (!isset ($_GET['folder'])) {

    // ERROR: The 'folder' GET parameter is not set.
    // Return to homepage
    returnToIndex(true);

} else {

    $folderToken = mysqli_real_escape_string($con, $_GET['folder']);
    $result = mysqli_query($con, "SELECT * FROM folders WHERE token = '{$folderToken}'");

    if (mysqli_num_rows($result) > 0) {

        // A folder with the specified token was found

        $row = mysqli_fetch_array($result);

        $folderId = $row['id'];
        $folderName = $row['name'];
        $publickey = preg_replace( "/\r|\n/", "", $row['public_key']);

        $files = [];

        $result = mysqli_query($con, "SELECT * FROM files WHERE folder = '{$folderId}'");
        while ($row = mysqli_fetch_array($result)) {
            array_push($files, $row);
        }

    } else {

        // Folder with specified could not be found
        // Return to homepage
        returnToIndex(true);

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

        .spacer {
            height: 130px;
        }

        @media (max-width: 500px) {

            /* Mobile tweaks */

            /* Let items be smaller */
            .item {
                min-width: 140px;
            }

            /* Make previews take less empty space inside item */
            .item .previewContent {
                padding: 1em;
            }

            /* Make title take less empty space at the top of the page */
            .titlewrap {
                padding-top: 20px;
                padding-bottom: 0;
            }
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

<div class="centered area">

    <div class="titlewrap big" id="titlewrap">

        <div id="title" class="title big" style="margin-left: 10px" contenteditable="true" spellcheck="false"><?php echo $folderName ?></div>
        <div class="subtitle" id="encryptedMessage"><img src="img/padlock.svg" width="11px"> Encrypted</div>

    </div>

    <div class="spacer">
        &nbsp;
    </div>

    <div class="grid" id="mainGrid">
        <?php include ("modules/grid.php") ?>
    </div>

</div>

<div class="blackout" id="moreInfoWrap" onclick="hideFileInfo()">
    <div class="hover-view" id="moreInfoView">
    </div>
</div>

<div id="dummyItem">
    <div class="animated item" id="lastItem">
        <div class="view jpg">

            <div class="preview">
                <div class="previewContent">
                    <img src="img/fileicon.svg" class="fileicon">
                    <div class="extension"></div>
                </div>
            </div>

            <div class="overlay">
                <div class="overlayContent">
                    <br></div>
            </div>

        </div>
        <div class="name">Saving...</div>
    </div>
</div>

<?php include ("modules/footer.php") ?>

<script>

    var folderName = "<?php echo $folderName ?>";
    var folderId = <?php echo $folderId ?>;
    var titleWrapDiv = $("#titlewrap");
    var titleDiv = $("#title");
    var mainGridDiv = $("#mainGrid");
    var nextItemId = <?php echo $i ?>;
    var lastItem = $("#item<?php echo $i - 1 ?>");
    var dummyItem = $("#dummyItem");
    var encryptedMessageDiv = $("#encryptedMessage");
    var moreInfoDiv = $("#moreInfoView");
    var moreInfoWrapDiv = $("#moreInfoWrap");
    var encryptedMessageHtml = encryptedMessageDiv.html();
    var publicKey = "<?php echo $publickey ?>";
    var privateKey = getPrivateKeyFromUrl();

    $(window).scroll(function() {

        if ($(document).scrollTop() > 30) {
            titleWrapDiv.addClass("retracted");
            titleDiv.addClass("retracted");
        } else {
            titleWrapDiv.removeClass("retracted");
            titleDiv.removeClass("retracted");
        }
    });

    /**
     * Class that defines a file and its corresponding FileReader to manage file uploads.
     */
    class FileUpload {

        constructor(file) {

            // File to be uploaded.
            this.file = file;

            // FileReader to read file data.
            this.fileReader = new FileReader();

            // Function to be ran once FileReader is done reading file data.
            this.fileReader.onload = function(){

                console.log(file.name);

                // Get resulting byte ArrayBuffer
                var arrayBuffer = this.result;

                // Encrypt base64-encoded string
                var encrypted = encryptFile(arrayBuffer);

                // Make AJAX call to server script to upload the encrypted data
                // and corresponding encrypted symmetric key onto server
                uploadFile(file.name, arrayBuffer.byteLength, encrypted.data, encrypted.key);

            };
        }

        proceed() {
            this.fileReader.readAsArrayBuffer(this.file);
        }

    }

    /**
     * Dropzone script to detect file drag-and-drop
     */
    var dropzone = new Dropzone("div#mainGrid", {
        url: "backend/dummy_upload.php",
        init: function() {
            this.on("sending", function(file, xhr, formData){

                fileUpload = new FileUpload(file);
                fileUpload.proceed();

            });
        }
    });

    /**
     * Encrypt file using encryption.js using globally defined public key for current folder.
     * @param bytes:    Byte array or ArrayBuffer containing data to be encrypted.
     * @returns:        Encrypted data and key.
     */
    function encryptFile(bytes) {

        // Encode byte array into base64 string
        var binaryString = arrayBufferToBase64(bytes);

        var encrypted = fullEncrypt(binaryString, publicKey);
        return {data: encrypted.data.toString(), key: encrypted.key};
    }

    /**
     * Make AJAX call to server script to upload encrypted file and key.
     * @param name:     Name to associate the file with.
     * @param size:     Size (in bytes) of the file.
     * @param data:     Encrypted data to be uploaded as a file onto the server.
     * @param key:      Encrypted symmetric key data has been encrypted with.
     */
    function uploadFile(name, size, data, key) {

        $.ajax({
            type: 'POST',
            url: 'backend/upload_file.php',
            data: {
                folderId: folderId,
                name: name,
                size: size,
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

    /**
     * Dropzone events
     */

    dropzone.on("addedfile", function(file) {

        // If no files had been added (empty-state screen was showing) hide empty-state screen.
        if (nextItemId == 0) $("#emptystate").css("display", "none");

        // Add dummy item represending new file.
        addDummyItem();
    });

    dropzone.on("success", function(file, response) {
        // Delegated to uploadFile(), inside .done()
    });

    dropzone.on("dragover", function() {

        // Update UI to show reaction to user dragging file.
        mainGridDiv.addClass("dragging");

    });

    dropzone.on("dragleave", function () {

        // Update UI to undo reaction to user dragging file.
        mainGridDiv.removeClass("dragging");

    });

    /**
     * Update grid area with specified html.
     * @param newHtml:  New html to display in grid area.
     */
    function updateMainGrid(newHtml) {
        mainGridDiv.html(newHtml);
    }

    /**
     * Add dummy file div to UI.
     */
    function addDummyItem() {

        dummyItem.children().attr('id', 'item' + nextItemId);
        lastItem = $("#item" + nextItemId);

        mainGridDiv.append(dummyItem.html());

        nextItemId++;

    }

    /**
     * Runs when user presses Enter key while editing title div.
     */
    titleDiv.keypress(function(e) {
        if (e.which == 13) {
            titleDiv.blur();
            document.getSelection().removeAllRanges();
            e.preventDefault();
        }
    });

    /**
     * Runs when user clicks on title div.
     */
    titleDiv.focus(function() {
        window.getSelection().selectAllChildren(document.getElementById("title"));
        titleDiv.addClass("greyed");
    });

    /**
     * Runs when user clicks away from title div or is done editing.
     */
    titleDiv.blur(function() {
        enterNewTitle();
    });

    /**
     * Change title of the folder.
     */
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

    /**
     * Get file data from server for a specified ID and hash.
     * @param id    ID of the file to retrieve
     * @param hash  Hash of the file to retrieve
     */
    function retrieveFile(id, hash) {

        if (privateKey == "") {

            // No private key has been specified: file cannot be decrypted.
            alert("Couldn't decrypt: no key provided.");

        } else {

            // Make AJAX call to retrieve encrypted file data.
            $.ajax({
                type: 'POST',
                url: 'backend/retrieve_file.php',
                data: {id: id, hash: hash}
            }).done(function(response) {

                var json = jQuery.parseJSON(response);
                var name = json.name;
                var data = json.data;
                var key = json.key;
                
                // Attempt to decrypt key & file with specified private key.
                try {

                    // Decrypt file. Throws exception if decryption isn't possible.
                    var decrypted = fullDecrypt(data, key, privateKey);

                    // Decode base64 encoded string into byte ArrayBuffer.
                    var bytes = base64ToArrayBuffer(decrypted);

                    // Download decoded file onto user's device.
                    saveFile(name, bytes);

                } catch (ex) {

                    // File couldn't be decrypted using the provided key.
                    alert("Couldn't decrypt: incorrect key.");

                }

            });
        }

    }

    /**
     * Get version data from server for a specified ID and hash.
     * @param id    ID of the version to retrieve
     * @param hash  Hash of the version to retrieve
     * @param name  Name of the file the version belongs to
     */
    function retrieveVersion(id, hash, name) {

        if (privateKey == "") {

            // No private key has been specified: file cannot be decrypted.
            alert("Couldn't decrypt: no key provided.");

        } else {

            // Make AJAX call to retrieve encrypted file data.
            $.ajax({
                type: 'POST',
                url: 'backend/retrieve_version.php',
                data: {id: id, hash: hash}
            }).done(function(response) {

                console.log(response);

                var json = jQuery.parseJSON(response);
                var data = json.data;
                var key = json.key;

                // Attempt to decrypt key & file with specified private key.
                try {

                    // Decrypt file. Throws exception if decryption isn't possible.
                    var decrypted = fullDecrypt(data, key, privateKey);

                    // Decode base64 encoded string into byte ArrayBuffer.
                    var bytes = base64ToArrayBuffer(decrypted);

                    // Download decoded file onto user's device.
                    saveFile(name, bytes);

                } catch (ex) {

                    // File couldn't be decrypted using the provided key.
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

    function arrayBufferToBase64(buffer) {
        var binary = '';
        var bytes = new Uint8Array(buffer);
        var len = bytes.byteLength;
        for (var i = 0; i < len; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
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

    function saveFile(name, bytes) {
        var blob = new Blob([bytes]);
        var link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        var fileName = name;
        link.download = fileName;
        link.click();
    }

    function showFileInfo(id) {

        moreInfoWrapDiv.addClass("displaying");
        setTimeout('moreInfoDiv.addClass("displaying")', 1);

        $.post("modules/file-info.php", {id: id} ).done(function(response) {
            moreInfoDiv.html(response);
        });

    }

    function hideFileInfo() {

        moreInfoWrapDiv.removeClass("displaying");
        moreInfoDiv.removeClass("displaying");
        moreInfoDiv.html("Loading...");

    }

    moreInfoDiv.click(function(e){
       e.stopPropagation();
    });

    function moreInfoClicked(e, id) {
        showFileInfo(id);
        e.stopPropagation();
    }

</script>

</body>
</html>