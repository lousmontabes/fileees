<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 9/3/18
 * Time: 18:56
 */

require_once("../backend/connection.php");

$fileId = $_POST['id'];

$result = mysqli_query($con, "SELECT * FROM files WHERE id = {$fileId}");
$file = mysqli_fetch_array($result);

?>

<div class="versions-view">

    <div class="name"><?php echo $file['name'] ?></div>

<table class="versions">
    <tr>
        <th>Version</th>
        <th>Date</th>
        <th>Size</th>
        <th>Hash</th>
    </tr>
<?php

$result = mysqli_query($con, "SELECT * FROM versions WHERE file_id = {$fileId} ORDER BY id DESC");

$i = mysqli_num_rows($result);

while ($version = mysqli_fetch_array($result)) {
    ?>

    <tr class="version">
        <td class="version-property id">
            <?php echo $i ?>
        </td>
        <td class="version-property date">
            <?php

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
        </td>
        <td class="version-property size">
            <?php

            if ($file['size'] > 1000000) {
                echo round($version['size'] / 1000000, 2) . " MB";
            } else {
                echo round($version['size'] / 1000, 2) . " KB";
            }

            ?>
        </td>
        <td class="version-property hash">
            <?php echo $version['hash'] ?>
        </td>
        <td class="version-property download" onclick="retrieveVersion(<?php echo $version['id'] ?>, '<?php echo $version['hash'] ?>')">
            Download
        </td>
    </tr>

    <?php

    $i--;
}

?>

</table>

</div>