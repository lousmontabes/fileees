<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 16/4/18
 * Time: 17:57
 */

require_once("../backend/connection.php");

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

        $filenames = array_column($files, 'name');

    } else {

        // Folder with specified could not be found
        // Return to homepage
        returnToIndex(true);

    }

}

?>

<style>

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

<div class="centered area">

    <div class="titlewrap big" id="titlewrap">

        <div id="title" class="title big" style="margin-left: 10px" contenteditable="true" spellcheck="false"><?php echo $folderName ?></div>
        <div class="subtitle" id="encryptedMessage"><img src="img/padlock.svg" width="11px"> Encrypted</div>

    </div>

    <div class="spacer">
        &nbsp;
    </div>

    <div class="grid" id="mainGrid">
        <?php include ("./grid_preview.php") ?>
    </div>

</div>

<div class="blackout" id="moreInfoWrap" onclick="hideFileInfo()">
    <div class="hover-view" id="moreInfoView">
    </div>
</div>

<div id="dummyItem">
    <div class="item" id="lastItem">
        <div class="view jpg">

            <div class="preview">
                <div class="previewContent">
                    <img src="../img/fileicon.svg" class="fileicon">
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