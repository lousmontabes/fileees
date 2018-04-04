<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 4/4/18
 * Time: 14:29
 */

require_once("connection.php");

session_start();
$user_id = $_SESSION['user_id'];

$query = "SELECT token, name, encrypted_key FROM users_folders INNER JOIN folders ON users_folders.folder_id = folders.id WHERE user_id = {$user_id}";
$result = mysqli_query($con, $query);

$folders = [];

while ($row = mysqli_fetch_array($result)) {
    array_push($folders, $row);
}

echo json_encode($folders);