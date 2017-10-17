<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 14/10/17
 * Time: 19:03
 */

require_once("backend/connection.php");

$nounsFile = file("backend/nouns.txt");
$adjFile = file("backend/adjectives.txt");

$nRows = -1;
$attempts = 0;
$MAX_ATTEMPTS = 20;

while ($nRows != 0 && $attempts < $MAX_ATTEMPTS) {

    $randomNoun = ucfirst(trim($nounsFile[array_rand($nounsFile)]));
    $randomAdj = ucfirst(trim($adjFile[array_rand($adjFile)]));

    $name = $randomAdj." ".$randomNoun;
    $token = $randomAdj.$randomNoun;
    echo $token;

    $query = "SELECT * FROM `folders` WHERE `token` = '{$token}'";
    $result = mysqli_query($con, $query);

    $nRows = mysqli_num_rows($result);
    $attempts++;

}

if ($attempts >= $MAX_ATTEMPTS) {

    // Token coincided 20 times, something went wrong
    header("Location: index.php?source=error");

} else {

    $query = "INSERT INTO `folders`(`name`, `token`) VALUES ('{$name}', '{$token}')";
    mysqli_query($con, $query);

    $folderId = mysqli_insert_id($con);
    header("Location: folder.php?folder={$token}");

}

?>