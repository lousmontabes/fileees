<?php

if (!isset($files)) {

    require_once("../backend/connection.php");

    if (!isset ($_POST['folder']) || !is_numeric($_POST['folder'])) {
        // ERROR: The 'folder' GET parameter is not set or not numeric.
    } else {

        $folderId = mysqli_real_escape_string($con, $_POST['folder']);

        $result = mysqli_query($con, "SELECT * FROM folders WHERE id = '$folderId'");
        $row = mysqli_fetch_array($result);

        $folderName = $row['name'];

        $files = [];

        $result = mysqli_query($con, "SELECT `id`, `name`, `type`, `format`, `extension`, `uploader`, `folder` FROM files WHERE folder = '$folderId'");
        while ($row = mysqli_fetch_array($result)) {
            array_push($files, $row);
        }

        $filenames = array_column($files, 'name');

    }

}

$i = 0;

if (empty($files)) {

    ?>

    <div class="emptystate" id="emptystate">

        <div class="centerwrap">

            <div id="emptymessage">There's nothing here. Drag and drop to upload something amazing!</div>

        </div>

    </div>

    <?php

} else {

    foreach ($files as $file) {

        // Retrieve last version of the file
        $result = mysqli_query($con, "SELECT * FROM versions WHERE file_id = {$file['id']} ORDER BY id DESC LIMIT 1");
        $version = mysqli_fetch_array($result);

        ?>

        <div class="item" id="item<?php echo $i ?>" onclick="retrieveVersion(<?php echo $version['id'] ?>, '<?php echo $version['hash'] ?>', '<?php echo $file['name']?>')">
            <div class="view jpg">

                <div class="preview">
                    <div class="previewContent">
                        <img src="img/fileicon.svg" class="fileicon">
                        <div class="extension"><?php echo $file['extension'] ?></div>
                    </div>
                </div>

                <div class="overlay">
                    <div class="overlayContent">

                        <div class="filesize">
                        <?php

                        if ($version['size'] > 1000000) {
                            echo round($version['size'] / 1000000, 2) . " MB";
                        } else {
                            echo round($version['size'] / 1000, 2) . " KB";
                        }

                        ?>
                        </div>

                        <div class="date">
                        <?php

                        // TODO: Fix this timezone mess.

                        $timezone = new DateTimeZone('Europe/London');
                        $date = DateTime::createFromFormat("Y-m-d H:i:s", $version['date'], $timezone);
                        $date->add(DateInterval::createFromDateString("+2 hours"));

                        if ($date->getTimestamp() > strtotime('-1 day')) {
                            echo "Today, " . $date->format("H:i");
                        }
                        else if ($date->getTimestamp() > strtotime('-2 day')) {
                            echo "Yesterday, " . $date->format("H:i");
                        }
                        else if ($date->getTimestamp() > strtotime('-7 day')) {
                            echo $date->format("l, H:i");
                        } else {
                            echo $date->format('l d M, Y H:i');
                        }

                        ?>
                        </div>

                        <div class="more-info" onclick="moreInfoClicked(event, <?php echo $file['id'] ?>)">Show all versions</div>

                    </div>
                </div>

            </div>
            <div class="name"><?php echo $file['name'] ?></div>
        </div>

        <?php

        $i++;

    }

}

?>

<script>

    var filenames;

    function updateFilenames(newFilenames) {
        filenames = newFilenames;
    }

    updateFilenames(<?php echo json_encode($filenames) ?>);
    
</script>
