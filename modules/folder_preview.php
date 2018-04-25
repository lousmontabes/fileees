<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 11/4/18
 * Time: 11:43
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

<div class="folderAreaWrap" id="folderPreview<?php echo $folderToken?>">
    <div class="folderArea">

        <div class="removeButton" onClick="removeRelation(<?php echo $folderId ?>, '<?php echo $folderToken ?>')"><img src="img/delete.svg" height="10px"></div>

        <div class="header preview" id="titlewrap">

            <div id="title" class="title big" style="margin-left: 10px" onclick="openFolder('<?php echo $folderToken?>')"><?php echo $folderName ?></div>
            <div class="subtitle" id="encryptedMessage">&<?php echo $folderToken ?> | <img src="img/padlock.svg" width="11px"> Encrypted</div>

        </div>

        <div class="spacer">
            &nbsp;
        </div>

        <div class="grid" id="mainGrid<?php echo $folderToken?>">
            <?php include("grid_preview.php") ?>
        </div>

    </div>
</div>

<div class="blackout" id="moreInfoWrap" onclick="hideFileInfo()">
    <div class="hover-view" id="moreInfoView">
    </div>
</div>

<div id="dummyItem<?php echo $folderToken ?>" style="display:none">
    <div class="item" id="lastItem">
        <div class="view jpg">

            <div class="preview">
                <div class="previewContent">
                    <img src="./img/fileicon.svg" class="fileicon">
                    <div class="extension"></div>
                </div>
            </div>

            <div class="overlay">
                <div class="overlayContent">
                    <br>
                </div>
            </div>

        </div>
        <div class="name">Saving...</div>
    </div>
</div>

<script>

    var nFiles = <?php echo $i ?>;
    folders['<?php echo $folderToken?>'].nextItemId = nFiles;

    /**
     * Dropzone script to detect file drag-and-drop
     */
    var dropzone = new Dropzone("div#mainGrid<?php echo $folderToken?>", {
        url: "backend/dummy_upload.php",
        init: function() {
            this.on("sending", function(file, xhr, formData){

                fileUpload = new FileUpload(file, "<?php echo $publickey ?>", "<?php echo $folderToken ?>");
                fileUpload.proceed();

            });
        }
    });

    /**
     * Dropzone events
     */
    dropzone.on("addedfile", function(file) {

        // If no files had been added (empty-state screen was showing) hide empty-state screen.
        if (folders['<?php echo $folderToken?>'].nextItemId == 0) $("#emptystate").css("display", "none");

        // Check if a file with the same name exists
        var index = $.inArray(file.name, filenames);

        if (index != -1) {

            /*
            var updatedItemDiv = $("#item" + index);
            var updatedItemNameDiv = $("#item" + index + " .name");

            // Change UI to show that file is updating
            updatedItemNameDiv.html("Saving...");
            */

        } else {

            // Add dummy item representing new file.
            folders['<?php echo $folderToken ?>'].addDummyItem();

        }

    });

</script>