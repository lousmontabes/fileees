<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 12/4/18
 * Time: 9:27
 */

session_start();
require_once("connection.php");

$user_id = $_SESSION['user_id'];
$folder_id = $_POST['folder_id'];

$query = "DELETE FROM `users_folders` WHERE user_id = {$user_id} AND folder_id = {$folder_id}";
$result = mysqli_query($con, $query);

if ($result) echo "success";