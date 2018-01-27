<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 14/10/17
 * Time: 23:38
 */

require_once ("connection.php");

$folderId = mysqli_real_escape_string($con, $_POST['id']);
$newName = mysqli_real_escape_string($con, $_POST['name']);
$newName = htmlentities($newName);

$query = "UPDATE `folders` SET `name`='{$newName}' WHERE id = {$folderId}";

mysqli_query($con, $query);

?>