<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 4/4/18
 * Time: 12:05
 */

if (!isset($_SESSION['user_id'])) {

    // User is not logged in.
    // Show log in / register buttons.

    ?>

    <div class="registerBenefitsMessage">

        Register for one click access to your folders.

        <div style="text-align: center; color: <?php echo $color ?>">
            <div class="register-login clickable" onclick="showLogin(true)">Log in</div>
            |
            <div class="register-login clickable" onclick="showLogin(false)">Sign up</div>
        </div>

    </div>

    <?

} else {

    // User is logged in
    // Return folder data to decrypt client-side

    ?>

    <div class="userFolders" id="userFolders">
        <div id="emptyState" style="display:none">
            <div class="title">Nothing here yet</div>
            <div class="content">Click on the button above to create your first folder. You will be able to access your folders from here without needing to save any decryption keys.</div>
        </div>
        <!-- folder previews load via ajax -->
    </div>

    <div id="folderPreviewDummy" style="display: none">
        <div class="folderPreview">
            
            <a id="link">
            <div class="left">
                <div class="title"></div>
                <div class="url"></div>
            </div>
            </a>

            <div class="right clipboard" data-clipboard-text="">
                <img src="./img/share.svg" height="15px">
            </div>

        </div>
    </div>

    <?

}

?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js"></script>
<script src="./libraries/crypto/aes.js"></script>
<script src="./libraries/crypto/encryption.js"></script>
<script>

    var pbkdf2 = "<?php echo $_SESSION['pbkdf2'] ?>";
    var folderPreviewDummy = $("#folderPreviewDummy");
    var clipboard = new Clipboard('.clipboard');

    retrieveUserFolders();

    function retrieveUserFolders() {

        var folders;

        $.ajax("./backend/get_session_user_folders.php", {success: function(response) {

            var key;
            folders = jQuery.parseJSON(response);

            console.log(folders);

            if (folders.length > 0) {

                folders.forEach(function (folder) {
                    key = decryptKey(folder.encrypted_key);
                    addFolderPreview(folder.name, folder.token, key);
                });

            } else {

                // User has no folders yet
                showEmptyStateView();

            }

        }});

    }

    function addFolderPreview(name, token, key) {

        console.log(name);
        folderPreviewDummy.find("a").attr("href", "./folder.php?folder=" + token + "#" + key);
        folderPreviewDummy.find(".title").html(name);
        folderPreviewDummy.find(".url").html("&" + token);
        folderPreviewDummy.find(".right").attr("data-clipboard-text", "filee.es/&" + token);

        $("#userFolders").append(folderPreviewDummy.html());

    }

    function showEmptyStateView() {
        $("#emptyState").css("display", "block");
    }

    function decryptKey(encrypted) {
        return decryptAES(encrypted, pbkdf2).toString(CryptoJS.enc.Utf8);
    }

</script>
