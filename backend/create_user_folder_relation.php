<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 4/4/18
 * Time: 16:28
 */

session_start();
require_once("connection.php");

$user_id = $_SESSION['user_id'];
$encrypted_key = $_POST['encrypted_key'];
$token = $_POST['token'];

$query = "SELECT id FROM folders WHERE token = '{$token}'";
$result = mysqli_query($con, $query);
$row = mysqli_fetch_array($result);

$folder_id = $row['id'];

$query = "INSERT INTO `users_folders`(`user_id`, `folder_id`, `encrypted_key`) VALUES ({$user_id}, {$folder_id}, '{$encrypted_key}')";
$result = mysqli_query($con, $query);

if ($result) echo "success";