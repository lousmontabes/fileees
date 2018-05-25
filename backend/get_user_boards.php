<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 8/5/18
 * Time: 16:45
 */

require_once ("./connection.php");

$userId = $_GET["userId"];

$result = mysqli_query($con, "SELECT * FROM users_boards JOIN boards ON (users_boards.board_id = boards.id) WHERE users_boards.user_id = {$userId}");

$boards = [];

while ($row = mysqli_fetch_array($result)) {
    $boards[$row["id"]] = $row["name"];
}

echo json_encode($boards);

?>