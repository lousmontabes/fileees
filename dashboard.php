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
                <a href="setup_folder.php?c=4AC29A">
                    <button class="button green">
                        Create new folder
                    </button>
                </a>
                <button class="button green">
                    Check out existing folder
                </button>
                <button class="button green">
                    ⋮
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

    /* FILE UPLOAD */

    /**
     * Class that defines a file and its corresponding FileReader to manage file uploads.
     */
    class FileUpload {

        constructor(file, publicKey, folderId) {

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
                uploadFile(file.name, arrayBuffer.byteLength, encrypted.data, encrypted.key, folderId);

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
     * Make AJAX call to server script to upload encrypted file and key.
     * @param name:     Name to associate the file with.
     * @param size:     Size (in bytes) of the file.
     * @param data:     Encrypted data to be uploaded as a file onto the server.
     * @param key:      Encrypted symmetric key data has been encrypted with.
     */
    function uploadFile(name, size, data, key, folderId) {

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

            var index = $.inArray(name, filenames);
            updateGrid(index);
        });

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

        var privateKey = keys[folderToken];

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

</script>

<script>

    /* FOLDER PARSING AND PRIVATE KEY RETRIEVAL */

    var pbkdf2 = "";
    var keys = {};

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
                    keys[folder.token] = key;
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
            $("#userFolders").append(response);
        });

    }

    function decryptKey(encrypted) {
        return decryptAES(encrypted, pbkdf2).toString(CryptoJS.enc.Utf8);
    }

</script>