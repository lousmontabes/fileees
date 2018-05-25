<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 3/5/18
 * Time: 15:58
 */

require_once ("./connection.php");

$boardId = $_GET["boardId"];

$result = mysqli_query($con, "SELECT * FROM boards_folders JOIN folders ON (boards_folders.folder_id = folders.id) WHERE board_id = {$boardId}");

$folderTokens = [];

while ($row = mysqli_fetch_array($result)) {
    array_push($folderTokens, $row["token"]);
}

echo json_encode($folderTokens);

?>