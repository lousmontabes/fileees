<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 11/4/18
 * Time: 11:31
 */

require_once ("backend/connection.php");

session_start();

function getRandomWelcomePhrase() {
    $phrases = file("backend/welcome_phrases.txt");
    return $phrases[array_rand($phrases)];
}

$phrase = htmlentities(getRandomWelcomePhrase());

// Check if user is properly logged in. Otherwise, redirect to index.php
$loggedIn = (isset($_SESSION['pbkdf2']) && isset($_SESSION['user_id']));
if (!$loggedIn) header("Location: ./index.php");

// Get current user username from database
$query = "SELECT name FROM users WHERE id = {$_SESSION['user_id']}";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_array($result);

// Sanitize username before embedding into html
$username = htmlentities($row['name']);

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

        body {
            background: whitesmoke;
        }

        .splash {
            position: relative;
            color: white;
            width: 100%;
            background: #4AC29A;
            font-family: "Open Sans";
            padding-top: 5em;
            padding-bottom: 2.5em;
        }

        .splash .area {
            background: transparent;
            margin: 0 5em;
            padding: 0;
            width: auto;
        }

        .splash a {
            color: white;
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
            border: none;
            border-bottom: 2px dotted transparent;
            max-width: 85%;
            cursor: pointer;
        }

        .header.preview .title:hover {
            border-bottom: 2px dotted black;
        }

        .header.preview .title:active, .header.preview .title:focus {
            margin-bottom: 3px;
        }

        .header.preview .subtitle {
            width: auto;
        }

        .logo {
            color: white;
            position: absolute;
            top: -2em;
            font-weight: 100;
        }

        .folderArea {
            background: white;
            margin: .5em;
            padding: 3em;
        }

        .folderArea .grid {
            margin-bottom: 0;
        }

        .folderAreaSizer,
        .folderAreaWrap {
            width: 50%;
            min-width: 480px;
        }

        .userFoldersWrap {
            width: 100%;
        }

        #userFolders {
            margin: .5em;
            /*margin: .5em 1em;*/
        }

        .panel {
            position: fixed;
            right: 0;
            width: 12em;
            height: 20em;
            margin: 1.5em;
            background: white;
            z-index: 1000;
            display: none;
        }

        .panel .navigation {
            margin: 2em;
        }

        #emptyState {
            display: none;
            width: 25em;
            padding: 4em;
        }

    </style>

</head>

<body>

    <div class="splash">

        <div class="area centered">

            <div class="logo">filee.es</div>

            <div class="header">Hello, <?php echo $username ?></div>
            <div class="tagline"><?php echo $phrase ?></div>
            <br>
            <div class="splash-banner">
                <a href="setup_folder.php?c=4AC29A">
                    <button class="button green">
                        Create new folder
                    </button>
                </a>
                <!--<button class="button green">
                    Check out existing folder
                </button>-->
                <a href="backend/log_out.php">
                    <button class="button green">
                        Log out
                    </button>
                </a>
            </div>

        </div>

    </div>

    <div class="panel">
        <div class="navigation">
            Hi
        </div>
    </div>

    <div class="userFoldersWrap">
        <div id="userFolders">

            <!-- Folder previews load via AJAX -->

            <div class="folderAreaSizer"></div>
            <div id="emptyState" style="opacity:.5"><div  class="content">Start by creating a folder. All your folders and their contents will show up here.</div></div>

        </div>
    </div>

    <div class="blackout" id="moreInfoWrap" onclick="hideFileInfo()">
        <div class="hover-view" id="moreInfoView">

            <!-- File info loads via AJAX -->

        </div>
    </div>

</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="https://unpkg.com/masonry-layout@4/dist/masonry.pkgd.min.js"></script>

<script src="libraries/crypto/aes.js"></script>
<script src="libraries/crypto/pbkdf2.js"></script>
<script src="libraries/crypto/ecc-min.js"></script>
<script src="libraries/crypto/encryption.js"></script>
<script src="libraries/dropzone.js"></script>

<script>

    var masonryContainer = $("#userFolders");

    // Initialize Masonry
    $masonryDiv = masonryContainer.masonry({
        // options
        itemSelector: '.folderAreaWrap',
        columnWidth: '.folderAreaSizer',
        hiddenStyle: { opacity: 0, transform: "translateY(15px)" }
    });

</script>

<script>

    /* FILE UPLOAD */

    /**
     * Class that defines a file and its corresponding FileReader to manage file uploads.
     */
    class FileUpload {

        constructor(file, publicKey, folderToken) {

            // File to be uploaded.
            this.file = file;

            // FileReader to read file data.
            this.fileReader = new FileReader();

            // Function to be ran once FileReader is done reading file data.
            this.fileReader.onload = function () {

                console.log(file.name);

                // Get resulting byte ArrayBuffer
                var arrayBuffer = this.result;

                // Encrypt base64-encoded string
                var encrypted = encryptFile(arrayBuffer, publicKey);

                console.log("Encrypted");

                // Make AJAX call to server script to upload the encrypted data
                // and corresponding encrypted symmetric key onto server
                folders[folderToken].uploadFile(file.name, arrayBuffer.byteLength, encrypted.data, encrypted.key);

            };
        }

        proceed() {
            this.fileReader.readAsArrayBuffer(this.file);
        }

    }

    /**
     * Encrypt file using encryption.js using globally defined public key for current folder.
     * @param bytes:     Byte array or ArrayBuffer containing data to be encrypted.
     * @param publicKey: Public EC key to encrypt the file with.
     * @returns:         Encrypted data and key.
     */
    function encryptFile(bytes, publicKey) {

        // Encode byte array into base64 string
        var binaryString = arrayBufferToBase64(bytes);

        var encrypted = fullEncrypt(binaryString, publicKey);
        return {data: encrypted.data.toString(), key: encrypted.key};
    }

    /**
     * Convert a byte array to a base-64 encoded string.
     * @param buffer
     * @returns {string}
     */
    function arrayBufferToBase64(buffer) {
        var binary = '';
        var bytes = new Uint8Array(buffer);
        var len = bytes.byteLength;
        for (var i = 0; i < len; i++) {
            binary += String.fromCharCode(bytes[i]);
        }
        return window.btoa(binary);
    }

    /* FILE DOWNLOAD */

    /**
     * Get version data from server for a specified ID and hash.
     * @param id          ID of the version to retrieve
     * @param hash        Hash of the version to retrieve
     * @param name        Name of the file the version belongs to
     * @param folderToken Token of the folder file belongs to
     */
    function retrieveVersion(id, hash, name, folderToken) {
        folders[folderToken].retrieveVersion(id, hash, name);
    }

    /**
     * Convert a base-64 formatted string to a byte array
     * @param base64
     * @returns {Uint8Array}
     */
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

    /**
     * Write a file with the specified bytes and store in the client's disk with the given name
     * @param name
     * @param bytes
     */
    function saveFile(name, bytes) {
        var blob = new Blob([bytes]);
        var link = document.createElement('a');
        link.href = window.URL.createObjectURL(blob);
        var fileName = name;
        link.download = fileName;
        link.click();
    }

    /* FILE INFO */

    var moreInfoWrapDiv = $("#moreInfoWrap");
    var moreInfoDiv = $("#moreInfoView");

    function hideFileInfo() {

        moreInfoWrapDiv.css("opacity", 0);
        setTimeout('moreInfoWrapDiv.removeClass("displaying")', 200);
        moreInfoDiv.removeClass("displaying");

        setTimeout('moreInfoDiv.html("Loading...")', 200);

    }

    moreInfoDiv.click(function(e){
        e.stopPropagation();
    });

    function moreInfoClicked(e, id, token) {
        folders[token].showFileInfo(id);
        e.stopPropagation();
    }

</script>

<script>

    /* FOLDER PARSING AND PRIVATE KEY RETRIEVAL */

    var folders = {};
    var pbkdf2 = "";
    var keys = {};

    <?php if ($loggedIn) { ?>
    pbkdf2 = "<?php echo $_SESSION['pbkdf2'] ?>";
    <?php } ?>

    <?php if ($loggedIn) { ?>
    retrieveUserFolders();
    <?php } ?>

    /**
     * Retrieve all folders related to the user via AJAX
     */
    function retrieveUserFolders() {

        var retrievedFolders;

        $.ajax("backend/get_session_user_folders.php", {success: function(response) {

            var key;
            retrievedFolders = jQuery.parseJSON(response);

            if (retrievedFolders.length > 0) {

                retrievedFolders.forEach(function (f) {

                    key = decryptKey(f.encrypted_key);

                    var folder = new Folder(f.name, f.token, key);
                    folders[folder.token] = folder;

                    folder.displayPreview(masonryContainer);

                });

            } else {

                // User has no folders yet
                showEmptyStateView();

            }

        }});

    }

    function decryptKey(encrypted) {
        return decryptAES(encrypted, pbkdf2).toString(CryptoJS.enc.Utf8);
    }

    function showEmptyStateView() {
        $("#emptyState").css("display", "block");
    }

    /* FOLDER MANAGEMENT */

    /**
     * Removes the relation between the current user and the specified folder
     * @param folderId
     * @param folderToken
     */
    function removeRelation(folderId, folderToken) {

        $.post("./backend/remove_user_folder_relation.php", {folder_id: folderId}).done(function (response) {

            if (response == 'success') {

                // Update masonry layout
                $masonryDiv.masonry('remove', folders[folderToken].getPreviewDiv());
                $masonryDiv.masonry('layout');
            }

        });

    }

</script>

<script>

    /* FOLDER INFO & DISPLAY */

    class Folder {

        constructor(name, token, key) {
            this.name = name;
            this.token = token;
            this.key = key;
            this.nextItemId = 0;
            this.lastItem = null;
            this.filenames = [];
        }

        /**
         * Adds folder's corresponding preview html to the specified container div.
         * @param container Parent div to preview. Must be a Masonry grid.
         */
        displayPreview(container) {

            // Request folder preview html via AJAX
            $.get("modules/folder_preview.php", {folder: this.token}).done(function(response) {
                var $response = $(response);
                container.append($response).masonry('appended', $response);
            });

        }

        /**
         * Returns a handle to the corresponding preview div or null if none exists.
         */
        getPreviewDiv() {
            
            var div = $("#folderPreview" + this.token);
            var response = null;
            
            if (div.length > 0) {
                response = div;
            }
            
            return response;
            
        }

        updateGrid() {

            var that = this;

            $.post("modules/grid_preview.php", {token: this.token}).done(function(response) {
                that.getGridDiv().html(response);
            });

            $masonryDiv.masonry("reload-items");

        }

        /**
         * Returns a handle to the corresponding grid div or null if none exists.
         */
        getGridDiv () {
            return this.getPreviewDiv().find(".grid");
        }

        /**
         * Make AJAX call to server script to upload encrypted file and key.
         * @param name:     Name to associate the file with.
         * @param size:     Size (in bytes) of the file.
         * @param data:     Encrypted data to be uploaded as a file onto the server.
         * @param key:      Encrypted symmetric key data has been encrypted with.
         */
        uploadFile(name, size, data, key) {

            var that = this;

            $.ajax({
                type: 'POST',
                url: 'backend/upload_file.php',
                data: {
                    folderToken: this.token,
                    name: name,
                    size: size,
                    bytes: data,
                    key: key
                }
            }).done(function(response) {
                console.log(response);

                var index = $.inArray(name, filenames);
                that.updateGrid();
            });

        }

        /**
         * Retrieve and save a version with the specified id and hash.
         * @param id    Id of version
         * @param hash  Hash of version
         * @param name  Name of file to be saved
         */
        retrieveVersion(id, hash, name) {

            var privateKey = this.key;

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

        /**
         * Displays dummy item representing an upload
         */
        addDummyItem() {

            console.log(this.token);

            var dummyItem = $("#dummyItem" + this.token);

            dummyItem.children().attr('id', 'item' + this.nextItemId);
            this.lastItem = $("#item" + this.nextItemId);

            this.getGridDiv().append(dummyItem.html());

            this.nextItemId++;
        }

        /**
         * Displays file info for the specified file
         * @param id
         */
        showFileInfo(id) {

            moreInfoWrapDiv.addClass("displaying");

            $.post("modules/file-info.php", {id: id}).done(function (response) {
                moreInfoDiv.html(response);
                moreInfoWrapDiv.css("opacity", 1);
                moreInfoDiv.addClass("displaying");
            });

        }

        /**
         * Opens this folder
         */
        open() {
            window.location.href = "folder.php?folder=" + this.token + "#" + this.key;
        }

    }

    function openFolder(token) {
        folders[token].open();
    }

</script>