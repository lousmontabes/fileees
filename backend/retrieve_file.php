<?php

// Require connection to the database
require_once ("connection.php");

// Retrieve file info from database
$result = mysqli_query($con, "SELECT `name`, `type`, `format`, `extension`, `size`, `hash`, `skey`, `iv` FROM files WHERE id = {$_POST['id']}");
$file = mysqli_fetch_array($result);

// If the retrieved hash coincides with the requested hash, allow download
if ($_POST['hash'] == $file['hash']) {

    // Get s3 link to file
    $link = "https://s3.eu-west-2.amazonaws.com/files-app/".$file['hash'];

    // Get encrypted contents of file
    $contents = file_get_contents($link);

    // Get encrypted symmetric key and initialization vector
    $skey = $file['skey'];
    $iv = $file['iv'];

    // Get file name
    $filename = $file['name'];

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