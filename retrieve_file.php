<?php
/**
 * Created by PhpStorm.
 * User: lluismontabes
 * Date: 22/10/17
 * Time: 19:10
 */

// Require connection to the database
require_once ("backend/connection.php");

// Retrieve file info from database
$result = mysqli_query($con, "SELECT `name`, `type`, `format`, `extension`, `size`, `hash`, `skey`, `iv` FROM files WHERE id = {$_POST['id']}");
$file = mysqli_fetch_array($result);

// If the retrieved hash coincides with the requested hash, allow download
if ($_POST['hash'] == $file['hash']) {

    // Get s3 link to file
    $link = "https://s3.eu-west-2.amazonaws.com/files-app/".$file['hash'];

    // Get encrypted contents of file
    $contents = file_get_contents($link);

    // Get symmetric key and initialization vector
    $skey = $file['skey'];
    $iv = $file['iv'];

    // TODO: Implement asymmetric encryption of symmetric key (PGP-like)
    //$privatekey = $file['private_key'];

    // Decrypt symmetrically envrypted file
    $algorithm = "AES-128-CBC";
    $filename = $file['name'];

    $decrypted = openssl_decrypt($contents, $algorithm, $skey, $raw_input = false, $iv);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename='.basename($filename));
    echo $decrypted;

} else {

    echo "Incorrect credentials";

}

?>