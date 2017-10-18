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

        $result = mysqli_query($con, "SELECT * FROM files WHERE folder = '$folderId'");
        while ($row = mysqli_fetch_array($result)) {
            array_push($files, $row);
        }

    }

}

$i = 0;

if (empty($files)) {

    ?>

    <div class="emptystate" id="emptystate">

        <div class="centerwrap">

            <div>There's nothing here. Drag and drop to upload something amazing!</div>

        </div>

    </div>

    <?php

} else {

    foreach ($files as $file) {

        $link = "https://s3.eu-west-2.amazonaws.com/files-app/".$file['hash'].".".$file['extension'];

        ?>

        <a href="<?php echo $link ?>" download="<?php echo $file['name'] ?>" title="<?php echo $file['name'] ?>">

            <div class="item" id="item<?php echo $i ?>">
                <div class="view jpg">

                    <div class="preview">
                        <div class="previewContent">
                            <img src="img/fileicon.svg" id="fileicon">
                            <div class="extension"><?php echo $file['extension'] ?></div>
                        </div>
                    </div>

                    <div class="overlay">
                        <div class="overlayContent">
                            <div class="filesize">
                            <?php

                            if ($file['size'] > 1000000) {
                                echo round($file['size'] / 1000000, 2) . " MB";
                            } else {
                                echo round($file['size'] / 1000, 2) . "KB";
                            }

                            ?>
                            </div>

                            <?php

                            $timezone = new DateTimeZone('Europe/Amsterdam');
                            $date = DateTime::createFromFormat("Y-m-d H:i:s", $file['date'], $timezone);

                            if (strtotime($file['date']) > strtotime('-1 day')) {
                                echo "Today, " . $date->format("H:i");
                            }
                            else if (strtotime($file['date']) > strtotime('-2 day')) {
                                echo "Yesterday, " . $date->format("H:i");
                            }
                            else if (strtotime($file['date']) > strtotime('-7 day')) {
                                echo $date->format("l, H:i");
                            } else {
                                echo $date->format('l d M, Y H:i');
                            }

                            ?>
                        </div>
                    </div>

                </div>
                <div class="name"><?php echo $file['name'] ?></div>
            </div>

        </a>

        <?php

        $i++;

    }

}

?>