<?php

// Require database connection
require_once("connection.php");

// Get the list of nouns and adjectives
$nounsFile = file("nouns.txt");
$adjFile = file("adjectives.txt");

$nRows = -1;
$attempts = 0;
$MAX_ATTEMPTS = 20;

// Try to generate a unique adj + noun combination
while ($nRows != 0 && $attempts < $MAX_ATTEMPTS) {

    $randomNoun = ucfirst(trim($nounsFile[array_rand($nounsFile)]));
    $randomAdj = ucfirst(trim($adjFile[array_rand($adjFile)]));

    $name = $randomAdj." ".$randomNoun;
    $token = $randomAdj.$randomNoun;

    $query = "SELECT * FROM `folders` WHERE `token` = '{$token}'";
    $result = mysqli_query($con, $query);

    $nRows = mysqli_num_rows($result);
    $attempts++;

}

$publickey = $_POST['publicKey'];

// Couldn't generate a unique adj + name combination
// OR publickey is empty
// OR privatekey is empty
if ($attempts >= $MAX_ATTEMPTS || $publickey == "") {

    // Something went wrong
    $response = array(
        'success' => false
    );

} else {

    // Everything went OK
    $query = "INSERT INTO `folders`(`name`, `token`, `public_key`) VALUES ('{$name}', '{$token}', '{$publickey}')";
    mysqli_query($con, $query);

    $response = array(
        'success' => true,
        'token' => $token,
        'name' => $name
    );

}

echo json_encode($response);
exit;

?>