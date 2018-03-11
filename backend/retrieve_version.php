<?php

// Require connection to the database
require_once ("connection.php");

// Retrieve file info from database
$result = mysqli_query($con, "SELECT * FROM versions WHERE id = {$_POST['id']}");
$version = mysqli_fetch_array($result);

// If the retrieved hash coincides with the requested hash, allow download
if ($_POST['hash'] == $version['hash']) {

    // Get s3 link to file
    $link = "https://s3.eu-west-2.amazonaws.com/files-app/".$version['hash'];

    // Get encrypted contents of file
    $contents = file_get_contents($link);

    // Get encrypted symmetric key and initialization vector
    $skey = $version['skey'];
    
    $response = array(
        'data' => $contents,
        'key' => $skey
    );

    echo json_encode($response);
    exit;

} else {

    echo "Incorrect credentials";
    exit;

}

?>